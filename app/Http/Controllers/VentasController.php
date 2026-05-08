<?php

namespace App\Http\Controllers;

use App\Models\Borrador;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Receta;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\FormaPagoVenta;
use App\Models\HistorialMovimientos;
use App\Models\PromocionDetalle;
use App\Models\CorporateData;
use App\Models\Caja;
use App\Models\RetiroCaja;
use App\Models\Comanda;
use App\Models\Globales;
use App\Models\RangoPrecio;
use App\Models\Permiso;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\StorePreventaRequest;
use App\Http\Requests\AperturaCajaRequest;
use App\Http\Requests\CierreCajaRequest;
use App\Models\HistorialEstadoVenta;
use App\Helpers\BarcodeHelper;
use App\Services\PrecioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class VentasController extends Controller
{
    private const TIPO_CAJA_MODULO_VENTAS = 'ALMACEN';

    private function resolverPrecioUnitarioPorCantidad(?string $uuid, float $cantidad, ?float $precioBase = null): float
    {
        $precioBaseNormalizado = is_null($precioBase) ? 0.0 : (float) $precioBase;

        if (empty($uuid) || $cantidad <= 0) {
            return $precioBaseNormalizado;
        }

        // Cache 60s por uuid+cantidad (dato casi estático entre requests del mismo carrito)
        $cacheKey = 'price_range:' . $uuid . ':' . (int) round($cantidad * 1000);
        $resolved = Cache::remember($cacheKey, 60, function () use ($uuid, $cantidad) {
            $producto = Producto::where('uuid', $uuid)->first();
            if (!$producto) {
                return ['found' => false];
            }
            return ['found' => true, 'precio' => PrecioService::resolver($producto, $cantidad)];
        });

        return $resolved['found'] ? $resolved['precio'] : $precioBaseNormalizado;
    }

    public function obtenerPrecioPorCantidad(Request $request)
    {
        $request->validate([
            'uuid' => 'required|string',
            'cantidad' => 'required|numeric|min:0.01',
            'precio_base' => 'nullable|numeric|min:0',
        ]);

        try {
            $cantidad = (float) $request->cantidad;
            $precioBase = $request->filled('precio_base') ? (float) $request->precio_base : null;
            $precioUnitario = $this->resolverPrecioUnitarioPorCantidad($request->uuid, $cantidad, $precioBase);

            return response()->json([
                'status' => 'OK',
                'precio_unitario' => $precioUnitario,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Error al resolver precio por cantidad: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function indexVentas()
    {
        // Verificar si el usuario tiene una caja abierta
        $cajaAbierta = Caja::cajaAbiertaUsuario(Auth::id(), self::TIPO_CAJA_MODULO_VENTAS);
        
        return view('ventas.generar_venta', [
            'cajaAbierta' => $cajaAbierta
        ]);
    }

    /**
     * Abrir caja
     */
    public function abrirCaja(AperturaCajaRequest $request)
    {
        try {
            $tipoCaja = strtoupper((string) $request->input('tipo_caja_origen', self::TIPO_CAJA_MODULO_VENTAS));
            if (!in_array($tipoCaja, ['ALMACEN', 'RESTAURANT'])) {
                $tipoCaja = self::TIPO_CAJA_MODULO_VENTAS;
            }

            // Verificar que no tenga una caja abierta
            $cajaExistente = Caja::cajaAbiertaUsuario(Auth::id(), $tipoCaja);
            
            if ($cajaExistente) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'Ya tienes una caja abierta'
                ], 400);
            }

            // Crear nueva caja
            $caja = Caja::create([
                'user_id' => Auth::id(),
                'tipo_caja' => $tipoCaja,
                'fecha_apertura' => now(),
                'monto_inicial' => $request->monto_inicial,
                'observaciones' => "APERTURA:".$request->observaciones
            ]);

            return response()->json([
                'status' => 'OK',
                'message' => 'Caja abierta correctamente',
                'caja_id' => $caja->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Error al abrir caja: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar contraseña del usuario
     */
    public function verificarPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();
        
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Contraseña incorrecta'
            ], 401);
        }

        return response()->json([
            'status' => 'OK',
            'message' => 'Contraseña verificada'
        ]);
    }

    /**
     * Obtener información de la caja actual
     */
    public function obtenerInfoCaja()
    {
        try {
            $caja = Caja::cajaAbiertaUsuario(Auth::id(), self::TIPO_CAJA_MODULO_VENTAS);
            
            if (!$caja) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'No tienes una caja abierta'
                ], 404);
            }

            // Obtener totales de esta caja en una sola query GROUP BY (evita N+1)
            $desgloseVentas = Venta::where('caja_id', $caja->id)
                ->selectRaw('forma_pago, SUM(total) as monto, COUNT(*) as cantidad')
                ->groupBy('forma_pago')
                ->get()
                ->keyBy('forma_pago');

            $totalVentas    = (float) $desgloseVentas->sum('monto');
            $cantidadVentas = (int)   $desgloseVentas->sum('cantidad');
            $totalMixto     = (float) ($desgloseVentas->get('MIXTO')?->monto ?? 0);

            // Desglose de ventas directas por forma de pago
            $totalEfectivo      = (float) ($desgloseVentas->get('EFECTIVO')?->monto      ?? 0);
            $totalTarjetaDebito  = (float) ($desgloseVentas->get('TARJETA_DEBITO')?->monto  ?? 0);
            $totalTarjetaCredito = (float) ($desgloseVentas->get('TARJETA_CREDITO')?->monto ?? 0);
            $totalTransferencia  = (float) ($desgloseVentas->get('TRANSFERENCIA')?->monto   ?? 0);
            $totalCheque        = (float) ($desgloseVentas->get('CHEQUE')?->monto           ?? 0);

            // Para ventas MIXTO, sumar el desglose real desde FormaPagoVenta (una sola query con subquery)
            if ($totalMixto > 0) {
                $mixtoDesglose = FormaPagoVenta::whereIn(
                    'venta_id',
                    Venta::where('caja_id', $caja->id)->where('forma_pago', 'MIXTO')->select('id')
                )
                    ->selectRaw('forma_pago, SUM(monto) as monto')
                    ->groupBy('forma_pago')
                    ->pluck('monto', 'forma_pago');

                $totalEfectivo      += (float) ($mixtoDesglose->get('EFECTIVO')      ?? 0);
                $totalTarjetaDebito  += (float) ($mixtoDesglose->get('TARJETA_DEBITO')  ?? 0);
                $totalTarjetaCredito += (float) ($mixtoDesglose->get('TARJETA_CREDITO') ?? 0);
                $totalTransferencia  += (float) ($mixtoDesglose->get('TRANSFERENCIA')   ?? 0);
                $totalCheque        += (float) ($mixtoDesglose->get('CHEQUE')           ?? 0);
            }

            return response()->json([
                'status' => 'OK',
                'caja' => [
                    'id' => $caja->id,
                    'fecha_apertura' => $caja->fecha_apertura->format('d/m/Y H:i:s'),
                    'monto_inicial' => $caja->monto_inicial,
                    'observaciones_apertura' => $caja->observaciones,
                    'total_ventas' => $totalVentas,
                    'cantidad_ventas' => $cantidadVentas,
                    'retiros' => RetiroCaja::where('caja_id', $caja->id)
                        ->orderBy('created_at')
                        ->get(['id', 'monto', 'motivo', 'created_at'])
                        ->map(fn ($r) => [
                            'id'         => $r->id,
                            'monto'      => (float) $r->monto,
                            'motivo'     => $r->motivo,
                            'created_at' => $r->created_at->format('d/m/Y H:i'),
                        ])->values()->toArray(),
                    'total_retiros' => (float) RetiroCaja::where('caja_id', $caja->id)->sum('monto'),
                    'monto_esperado' => $caja->monto_inicial + $totalVentas - (float) RetiroCaja::where('caja_id', $caja->id)->sum('monto'),
                    'desglose' => [
                        'efectivo' => $totalEfectivo,
                        'tarjeta_debito' => $totalTarjetaDebito,
                        'tarjeta_credito' => $totalTarjetaCredito,
                        'transferencia' => $totalTransferencia,
                        'cheque' => $totalCheque,
                        'mixto' => $totalMixto
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Error al obtener información de caja: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cerrar caja
     */
    public function cerrarCaja(CierreCajaRequest $request)
    {
        try {
            $caja = Caja::cajaAbiertaUsuario(Auth::id(), self::TIPO_CAJA_MODULO_VENTAS);
            
            if (!$caja) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'No tienes una caja abierta'
                ], 404);
            }

            // Calcular totales
            $totalVentas = (float) Venta::where('caja_id', $caja->id)->sum('total');
            $totalRetiros = (float) RetiroCaja::where('caja_id', $caja->id)->sum('monto');
            $montoEsperado = $caja->monto_inicial + $totalVentas - $totalRetiros;
            $montoFinalDeclarado = $request->monto_final_declarado;
            $diferencia = $montoFinalDeclarado - $montoEsperado;

            // Actualizar caja
            $caja->update([
                'fecha_cierre' => now(),
                'monto_ventas' => $totalVentas,
                'monto_final_declarado' => $montoFinalDeclarado,
                'diferencia' => $diferencia,
                'estado' => 'cerrada',
                'observaciones' => $caja->observaciones . "\n\nCIERRE: " . ($request->observaciones ?? '')
            ]);

            return response()->json([
                'status' => 'OK',
                'message' => 'Caja cerrada correctamente',
                'caja_id' => $caja->id,
                'diferencia' => $diferencia
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Error al cerrar caja: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar un retiro de efectivo en la caja activa
     */
    public function registrarRetiroCaja(Request $request)
    {
        $user = Auth::user();
        $tienePropioPermiso = $user->tienePermiso(Permiso::PERMISO_RETIRO_CAJA);

        // Si no tiene el permiso propio, exigir contraseña de un supervisor que sí lo tenga
        if (!$tienePropioPermiso) {
            $passwordSupervisor = $request->input('supervisor_password');

            if (!$passwordSupervisor) {
                return response()->json([
                    'status'  => 'REQUIERE_SUPERVISOR',
                    'message' => 'No tienes permiso para retirar. Ingresa la contraseña de un supervisor.',
                ], 403);
            }

            // Buscar cualquier usuario activo con PERMISO_RETIRO_CAJA cuya contraseña coincida
            $supervisorValido = \App\Models\User::where('estado', 1)
                ->whereHas('role', function ($q) {
                    $q->whereHas('permisos', function ($q2) {
                        $q2->where('codigo_permiso', Permiso::PERMISO_RETIRO_CAJA)
                           ->where('activo', true);
                    });
                })
                ->get()
                ->first(fn ($u) => \Illuminate\Support\Facades\Hash::check($passwordSupervisor, $u->password));

            if (!$supervisorValido) {
                return response()->json([
                    'status'  => 'ERROR',
                    'message' => 'Contraseña de supervisor incorrecta.',
                ], 403);
            }
        }

        $request->validate([
            'monto'     => 'required|numeric|min:1',
            'motivo'    => 'required|string|min:3|max:255',
            'tipo_caja' => 'required|string|in:ALMACEN,RESTAURANT',
        ]);

        try {
            $tipoCaja = strtoupper($request->tipo_caja);
            $caja = Caja::cajaAbiertaUsuario(Auth::id(), $tipoCaja);

            if (!$caja) {
                return response()->json([
                    'status'  => 'ERROR',
                    'message' => 'No tienes una caja abierta',
                ], 404);
            }

            // Marcar si supera el límite alto para alertar en el dashboard
            $montoMaximoSinAlerta = 500000;
            $alertaMontoAlto = (float) $request->monto > $montoMaximoSinAlerta;

            RetiroCaja::create([
                'caja_id'    => $caja->id,
                'monto'      => $request->monto,
                'motivo'     => trim($request->motivo),
                'creado_por' => Auth::id(),
            ]);

            $totalRetiros = (float) RetiroCaja::where('caja_id', $caja->id)->sum('monto');

            return response()->json([
                'status'            => 'OK',
                'message'           => 'Retiro registrado correctamente',
                'total_retiros'     => $totalRetiros,
                'alerta_monto_alto' => $alertaMontoAlto,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'ERROR',
                'message' => 'Error al registrar retiro: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function searchProduct(Request $request)
    {
        $query = $request->input('q');
        $tipo = $request->input('tipo');

        if ($tipo == 1) {
            $products = Producto::select('uuid', 'codigo', 'descripcion', 'precio_venta', 'imagen')
                ->where('estado', 'Activo')
                ->whereNull('fec_eliminacion')
                ->where('tipo', '<>', 'I')
                ->where('codigo', $query)
                ->limit(50)
                ->get();

            $promotions = Promocion::select('uuid', 'codigo', 'nombre as descripcion', 'precio_venta')
                ->where('estado', 'Activo')
                ->whereNull('fec_eliminacion')
                ->where('codigo', $query)
                ->limit(50)
                ->get();
        } else {
            $products = Producto::select('uuid', 'codigo', 'descripcion', 'precio_venta', 'imagen')
                ->where('estado', 'Activo')
                ->whereNull('fec_eliminacion')
                ->where('tipo', '<>', 'I')
                ->where(function($q) use ($query) {
                    $q->where('descripcion', 'like', "%$query%")
                      ->orWhere('codigo', 'like', "%$query%");
                })->limit(50)->get();

            $promotions = Promocion::select('uuid', 'codigo', 'nombre as descripcion', 'precio_venta')
                ->where('estado', 'Activo')
                ->whereNull('fec_eliminacion')
                ->where(function($q) use ($query) {
                    $q->where('nombre', 'like', "%$query%")
                      ->orWhere('codigo', 'like', "%$query%");
                })->limit(50)->get();
        }

        // Build explicit arrays to guarantee consistent JSON array output
        $productsArr = $products->map(fn($p) => [
            'uuid'         => $p->uuid,
            'codigo'       => $p->codigo,
            'descripcion'  => $p->descripcion,
            'precio_venta' => $p->precio_venta,
            'imagen'       => $p->imagen ?? null,
            'es_promo'     => false,
        ])->values()->all();

        $promosArr = $promotions->map(fn($p) => [
            'uuid'         => $p->uuid,
            'codigo'       => $p->codigo,
            'descripcion'  => $p->descripcion,
            'precio_venta' => $p->precio_venta,
            'imagen'       => null,
            'es_promo'     => true,
        ])->values()->all();

        return response()->json(array_values(array_merge($productsArr, $promosArr)));
    }

    public function guardarBorrador(Request $request)
    {
        try {
            $productos = $request->input('productos');
            $uuid      = $request->input('uuid_borrador');

            Borrador::guardarBorradores($productos);

            return response()->json([
                'status' => 'OK',
                'message' => 'Borrador guardado exitosamente',
                'uuid' => $uuid
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Ocurrió un error al guardar el borrador',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function eliminarBorrador($uuid_borrador)
    {
        try {
            $deleted = Borrador::borrarBorrador($uuid_borrador);
            if ($deleted) {
                return response()->json([
                    'status' => 'OK',
                    'message' => 'Borrador eliminado correctamente'
                ]);
            } else {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'No se encontró el borrador para eliminar'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Ocurrió un error al eliminar el borrador',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function traer_borradores()
    {
        $resumen = Borrador::resumen();
        return response()->json($resumen);
    }

    public function productosPorUuid($uuid)
    {
        $productos = Borrador::obtenerProductosPorUuid($uuid);
        return response()->json($productos);
    }

    /**
     * Verifica stock para un uuid (producto o promocion).
     * Request: { uuid: string, cantidad: int }
     * Respuestas:
     * - Producto tipo S (servicio): { status: 'OK', tipo: 'servicio' }
     * - Producto tipo P (producto): si stock suficiente -> { status: 'OK', tipo: 'producto', available: true }
     *   si no -> { status:'ERROR', code: 'OUT_OF_STOCK_PRODUCT', product: { uuid, codigo, descripcion, requested, stock } }
     * - Promocion: devuelve listado de productos que componen la promo con requested y stock, y marca los faltantes
     */
    public function verificarStock(Request $request)
    {
        $uuid = $request->input('uuid');
        $cantidad = (float) $request->input('cantidad', 1);

        if (empty($uuid) || $cantidad <= 0) {
            return response()->json(['status' => 'ERROR', 'message' => 'Parámetros inválidos'], 400);
        }

        // Obtener configuración de stock negativo (cacheado 5 minutos)
        $stockNegativo = Cache::remember('global_STOCK_NEGATIVO', 300, fn () => Globales::where('nom_var', 'STOCK_NEGATIVO')->value('valor_var'));
        $permitirStockNegativo = ($stockNegativo == '1');

        // Buscar en productos
        $producto = Producto::where('uuid', $uuid)->first();
        if ($producto) {
            // Tipo servicio (S): no requiere validación de stock
            if ($producto->tipo === 'S') {
                return response()->json([
                    'status' => 'OK',
                    'available' => true,
                    'tipo' => 'servicio',
                    'product' => []
                ]);
            }
            
            // Tipo producto con stock (P)
            if ($producto->tipo === 'P') {
                $stock = (float) $producto->stock;
                
                // Si permite stock negativo, siempre está disponible
                if ($permitirStockNegativo) {
                    return response()->json([
                        'status' => 'OK',
                        'available' => true,
                        'product' => []
                    ]);
                }
                
                // Si NO permite stock negativo, verificar si hay suficiente
                if ($stock >= $cantidad) {
                    return response()->json([
                        'status' => 'OK',
                        'available' => true,
                        'product' => []
                    ]);
                }

                return response()->json([
                    'status' => 'ERROR',
                    'available' => false,
                    'code' => 'OUT_OF_STOCK_PRODUCT',
                    'message' => 'Stock insuficiente para el producto solicitado',
                    'product' => [
                        'uuid' => $producto->uuid,
                        'codigo' => $producto->codigo ?? null,
                        'descripcion' => $producto->descripcion ?? null,
                        'requested' => $cantidad,
                        'stock' => $stock
                    ]
                ], 422);
            }
        }    
        // Si no es producto, buscar promocion
        $promocion = Promocion::where('uuid', $uuid)->first();
        if ($promocion) {
            // Eager load producto para evitar N+1 por cada componente de la promoción
            $detalles = $promocion->detallePromocion()->with('producto')->get();
            $items = [];
            $hasInsufficient = false;

            foreach ($detalles as $det) {
                $prod = $det->producto;
                $requiredPerPromo = (float) $det->cantidad;
                $requiredTotal = $requiredPerPromo * $cantidad; // cantidad solicitada de la promocion
                $stock = $prod ? (float) $prod->stock : 0;

                // Los servicios (tipo S) no requieren validación de stock
                if ($prod && $prod->tipo === 'S') {
                    $items[] = [
                        'uuid' => $prod->uuid,
                        'codigo' => $prod->codigo,
                        'descripcion' => $prod->descripcion,
                        'tipo' => 'S',
                        'required_per_promo' => $requiredPerPromo,
                        'requested_promos' => $cantidad,
                        'required_total' => $requiredTotal,
                        'stock' => null,
                        'sufficient' => true
                    ];
                    continue;
                }

                // Si permite stock negativo, todos los productos P son suficientes
                $ok = $permitirStockNegativo ? true : ($stock >= $requiredTotal);
                if (!$ok) $hasInsufficient = true;

                $items[] = [
                    'uuid' => $prod ? $prod->uuid : null,
                    'codigo' => $prod ? $prod->codigo : null,
                    'descripcion' => $prod ? $prod->descripcion : ('Producto ID '.$det->producto_id),
                    'tipo' => $prod ? $prod->tipo : null,
                    'required_per_promo' => $requiredPerPromo,
                    'requested_promos' => $cantidad,
                    'required_total' => $requiredTotal,
                    'stock' => $stock,
                    'sufficient' => $ok
                ];
            }

            if ($hasInsufficient) {
                return response()->json([
                    'status' => 'ERROR',
                    'code' => 'PROMO_INSUFFICIENT_STOCK',
                    'message' => 'La promoción no tiene stock suficiente para la cantidad solicitada',
                    'promotion' => [
                        'uuid' => $promocion->uuid,
                        'codigo' => $promocion->codigo ?? null,
                        'nombre' => $promocion->nombre ?? null
                    ],
                    'items' => $items
                ], 422);
            }

            return response()->json([
                'status' => 'OK',
                'tipo' => 'promocion',
                'available' => true,
                'promotion' => [
                    'uuid' => $promocion->uuid,
                    'codigo' => $promocion->codigo ?? null,
                    'nombre' => $promocion->nombre ?? null
                ],
                'items' => $items
            ]);
        }

    }

    /**
     * Verifica stock de múltiples items en una sola llamada (batch).
     * Acepta productos, promociones y recetas (uuid empieza con 'RECETA-').
     */
    public function verificarStockBatch(Request $request)
    {
        $request->validate([
            'items'            => 'required|array|min:1|max:50',
            'items.*.uuid'     => 'required|string',
            'items.*.cantidad' => 'required|numeric|min:0.01',
        ]);

        $stockNegativo = Cache::remember(
            'global_STOCK_NEGATIVO',
            300,
            fn () => Globales::where('nom_var', 'STOCK_NEGATIVO')->value('valor_var')
        );
        $permitirStockNegativo = ($stockNegativo == '1');

        $items = collect($request->items);

        $recetaItems = $items->filter(fn ($i) => str_starts_with((string) $i['uuid'], 'RECETA-'));
        $otherItems  = $items->filter(fn ($i) => !str_starts_with((string) $i['uuid'], 'RECETA-'));

        $otherUuids         = $otherItems->pluck('uuid')->unique()->values()->all();
        $productosPorUuid   = Producto::whereIn('uuid', $otherUuids)->get()->keyBy('uuid');
        $promocionesPorUuid = Promocion::whereIn('uuid', $otherUuids)->get()->keyBy('uuid');

        $promoIds      = $promocionesPorUuid->pluck('id')->all();
        $detallesPromo = collect();
        if (!empty($promoIds)) {
            $detallesPromo = PromocionDetalle::whereIn('promo_id', $promoIds)
                ->with('producto')
                ->get()
                ->groupBy('promo_id');
        }

        $recetaUuids    = $recetaItems->map(fn ($i) => substr($i['uuid'], 7))->unique()->values()->all();
        $recetasPorUuid = collect();
        if (!empty($recetaUuids)) {
            $recetasPorUuid = Receta::whereIn('uuid', $recetaUuids)
                ->with('ingredientes.producto')
                ->get()
                ->keyBy('uuid');
        }

        $results = [];

        foreach ($request->items as $itemData) {
            $uuid     = (string) $itemData['uuid'];
            $cantidad = (float)  $itemData['cantidad'];

            // ── RECETA ────────────────────────────────────────────────────────
            if (str_starts_with($uuid, 'RECETA-')) {
                $recetaUuid = substr($uuid, 7);
                $receta     = $recetasPorUuid->get($recetaUuid);

                if (!$receta) {
                    $results[$uuid] = ['status' => 'ERROR', 'code' => 'RECIPE_NOT_FOUND', 'message' => 'No se encontró la receta'];
                    continue;
                }

                $faltantes = [];
                foreach ($receta->ingredientes as $ingrediente) {
                    $producto = $ingrediente->producto;
                    if (!$producto || $permitirStockNegativo) {
                        continue;
                    }
                    $requerido = (float) $ingrediente->cantidad * $cantidad;
                    if ((float) ($producto->stock ?? 0) < $requerido) {
                        $faltantes[] = ['descripcion' => $producto->descripcion ?: ($producto->codigo ?: 'Insumo')];
                    }
                }

                $results[$uuid] = !empty($faltantes)
                    ? ['status' => 'ERROR', 'code' => 'RECIPE_INSUFFICIENT_STOCK', 'message' => 'Faltan insumos para preparar la receta', 'items' => collect($faltantes)->unique('descripcion')->values()]
                    : ['status' => 'OK', 'available' => true, 'tipo' => 'receta'];
                continue;
            }

            // ── PRODUCTO ──────────────────────────────────────────────────────
            $producto = $productosPorUuid->get($uuid);
            if ($producto) {
                if ($producto->tipo === 'S') {
                    $results[$uuid] = ['status' => 'OK', 'available' => true, 'tipo' => 'servicio', 'product' => []];
                    continue;
                }
                if ($producto->tipo === 'P') {
                    $stock = (float) $producto->stock;
                    if ($permitirStockNegativo || $stock >= $cantidad) {
                        $results[$uuid] = ['status' => 'OK', 'available' => true, 'product' => []];
                    } else {
                        $results[$uuid] = [
                            'status' => 'ERROR', 'available' => false, 'code' => 'OUT_OF_STOCK_PRODUCT',
                            'message' => 'Stock insuficiente para el producto solicitado',
                            'product' => [
                                'uuid' => $producto->uuid, 'codigo' => $producto->codigo ?? null,
                                'descripcion' => $producto->descripcion ?? null,
                                'requested' => $cantidad, 'stock' => $stock,
                            ],
                        ];
                    }
                    continue;
                }
            }

            // ── PROMOCIÓN ─────────────────────────────────────────────────────
            $promocion = $promocionesPorUuid->get($uuid);
            if ($promocion) {
                $detalles        = $detallesPromo->get($promocion->id, collect());
                $itemsPromo      = [];
                $hasInsufficient = false;

                foreach ($detalles as $det) {
                    $prod             = $det->producto;
                    $requiredPerPromo = (float) $det->cantidad;
                    $requiredTotal    = $requiredPerPromo * $cantidad;
                    $stockProd        = $prod ? (float) $prod->stock : 0;

                    if ($prod && $prod->tipo === 'S') {
                        $itemsPromo[] = ['uuid' => $prod->uuid, 'codigo' => $prod->codigo, 'descripcion' => $prod->descripcion, 'tipo' => 'S', 'required_total' => $requiredTotal, 'stock' => null, 'sufficient' => true];
                        continue;
                    }

                    $ok = $permitirStockNegativo ? true : ($stockProd >= $requiredTotal);
                    if (!$ok) $hasInsufficient = true;

                    $itemsPromo[] = [
                        'uuid'           => $prod ? $prod->uuid : null,
                        'codigo'         => $prod ? $prod->codigo : null,
                        'descripcion'    => $prod ? $prod->descripcion : ('Producto ID ' . $det->producto_id),
                        'tipo'           => $prod ? $prod->tipo : null,
                        'required_total' => $requiredTotal,
                        'stock'          => $stockProd,
                        'sufficient'     => $ok,
                    ];
                }

                if ($hasInsufficient) {
                    $results[$uuid] = [
                        'status' => 'ERROR', 'code' => 'PROMO_INSUFFICIENT_STOCK',
                        'message' => 'La promoción no tiene stock suficiente',
                        'promotion' => ['uuid' => $promocion->uuid, 'codigo' => $promocion->codigo ?? null, 'nombre' => $promocion->nombre ?? null],
                        'items' => $itemsPromo,
                    ];
                } else {
                    $results[$uuid] = [
                        'status' => 'OK', 'available' => true, 'tipo' => 'promocion',
                        'promotion' => ['uuid' => $promocion->uuid, 'codigo' => $promocion->codigo ?? null, 'nombre' => $promocion->nombre ?? null],
                        'items' => $itemsPromo,
                    ];
                }
                continue;
            }

            $results[$uuid] = ['status' => 'ERROR', 'code' => 'NOT_FOUND', 'message' => 'No se encontró el producto o promoción'];
        }

        return response()->json(['status' => 'OK', 'results' => $results]);
    }

    /**
     * Procesa y guarda una venta
     */
    public function procesarVenta(StoreVentaRequest $request)
    {
        DB::beginTransaction();
        
        try {
            // Obtener caja abierta del usuario
            $cajaAbierta = Caja::cajaAbiertaUsuario(Auth::id(), self::TIPO_CAJA_MODULO_VENTAS);
            
            if (!$cajaAbierta) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'No tienes una caja abierta. Debes abrir caja antes de realizar ventas.'
                ], 400);
            }

            // Crear la venta
            $venta = Venta::create([
                'total' => $request->total,
                'total_descuentos' => $request->total_descuentos ?? 0,
                'user_id' => Auth::id(),
                'caja_id' => $cajaAbierta->id,
                'forma_pago' => $request->forma_pago,
                'estado' => $request->estado ?? 'completada',
                'fecha_venta' => $request->fecha_venta ?? now(),
            ]);

            // Pre-fetch de productos y promociones por UUID para evitar N+1 en el loop
            $uuidsDetalles = collect($request->detalles)
                ->filter(fn($d) => !empty($d['producto_uuid']))
                ->pluck('producto_uuid')
                ->unique()
                ->values()
                ->all();

            $productosPorUuid   = Producto::whereIn('uuid', $uuidsDetalles)->get()->keyBy('uuid');
            $promocionesPorUuid = Promocion::whereIn('uuid', $uuidsDetalles)->get()->keyBy('uuid');

            // Guardar los detalles
            foreach ($request->detalles as $detalle) {
                $detalleVenta = DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_uuid' => $detalle['producto_uuid'] ?? null,
                    'promo_id' => $detalle['promo_id'] ?? null,
                    'descripcion_producto' => $detalle['descripcion_producto'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'descuento_porcentaje' => $detalle['descuento_porcentaje'] ?? 0,
                    'subtotal_linea' => $detalle['subtotal_linea'],
                ]);

                // Registrar en historial de movimientos (productos, servicios y promociones)
                if (!empty($detalle['producto_uuid'])) {
                    // Verificar si es un producto (pre-fetched, sin query adicional)
                    $producto = $productosPorUuid->get($detalle['producto_uuid']);
                    
                    if ($producto) {
                        // Es un producto directo
                        if ($producto->tipo === 'P') {
                            // Producto: Descontar stock (atómico, sin eventos Eloquent)
                            $producto->stock -= $detalle['cantidad'];
                            DB::table('productos')->where('id', $producto->id)->decrement('stock', $detalle['cantidad']);

                            HistorialMovimientos::registrarMovimiento([
                                'producto_id' => $producto->id,
                                'cantidad' => $detalle['cantidad'],
                                'stock' => $producto->stock,
                                'tipo_mov' => 'VENTA',
                                'fecha' => $venta->fecha_venta,
                                'num_doc' => (string) $venta->id,
                                'obs' => ''
                            ]);
                        } else {
                            // Servicio: Solo registrar sin descontar stock (stock = null)
                            HistorialMovimientos::registrarMovimiento([
                                'producto_id' => $producto->id,
                                'cantidad' => $detalle['cantidad'],
                                'stock' => null,
                                'tipo_mov' => 'VENTA',
                                'fecha' => $venta->fecha_venta,
                                'num_doc' => (string) $venta->id,
                                'obs' => ''
                            ]);
                        }
                    } else {
                        // Verificar si es una promoción (pre-fetched, sin query adicional)
                        $promocion = $promocionesPorUuid->get($detalle['producto_uuid']);
                        
                        if ($promocion) {
                            // Actualizar el detalle ya creado: asignar promo_id y limpiar producto_uuid
                            $detalleVenta->update([
                                'promo_id'      => $promocion->id,
                                'producto_uuid' => null,
                            ]);

                            // Es una promoción: procesar cada producto de la promoción
                            $detallesPromocion = PromocionDetalle::where('promo_id', $promocion->id)
                                ->with('producto')
                                ->get();

                            foreach ($detallesPromocion as $detallePromo) {
                                $productoPromo = $detallePromo->producto;
                                $cantidadTotal = $detallePromo->cantidad * $detalle['cantidad'];

                                if ($productoPromo->tipo === 'P') {
                                    // Producto de promoción: Descontar stock (atómico)
                                    $productoPromo->stock -= $cantidadTotal;
                                    DB::table('productos')->where('id', $productoPromo->id)->decrement('stock', $cantidadTotal);

                                    HistorialMovimientos::registrarMovimiento([
                                        'producto_id' => $productoPromo->id,
                                        'cantidad' => $cantidadTotal,
                                        'stock' => $productoPromo->stock,
                                        'tipo_mov' => 'VENTA',
                                        'fecha' => $venta->fecha_venta,
                                        'num_doc' => (string) $venta->id,
                                        'obs' => 'Venta como parte de promoción: ' . $promocion->nombre
                                    ]);
                                } else {
                                    // Servicio de promoción: Solo registrar
                                    HistorialMovimientos::registrarMovimiento([
                                        'producto_id' => $productoPromo->id,
                                        'cantidad' => $cantidadTotal,
                                        'stock' => null,
                                        'tipo_mov' => 'VENTA',
                                        'fecha' => $venta->fecha_venta,
                                        'num_doc' => (string) $venta->id,
                                        'obs' => 'Venta como parte de promoción: ' . $promocion->nombre
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Guardar formas de pago si es MIXTO
            if ($request->forma_pago === 'MIXTO' && !empty($request->formas_pago_desglose)) {
                foreach ($request->formas_pago_desglose as $formaPago) {
                    FormaPagoVenta::create([
                        'venta_id' => $venta->id,
                        'forma_pago' => $formaPago['forma'],
                        'monto' => $formaPago['monto'],
                    ]);
                }
            } else {
                // Si no es MIXTO, registrar una única forma de pago con el total
                FormaPagoVenta::create([
                    'venta_id' => $venta->id,
                    'forma_pago' => $request->forma_pago,
                    'monto' => $request->total,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'OK',
                'message' => 'Venta procesada exitosamente',
                'venta_id' => $venta->id,
                'numero_venta' => $venta->numero_venta,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Error al procesar la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera el ticket de cierre de caja en formato PDF
     */
    public function generarTicketCierrePDF($cajaId)
    {
        $caja = Caja::with('usuario')->findOrFail($cajaId);

        if ($caja->estado !== 'cerrada') {
            abort(400, 'Solo se pueden generar tickets de cajas cerradas');
        }

        // Calcular resumen con una sola query GROUP BY (evita cargar todas las ventas en RAM)
        $desgloseVentas = Venta::where('caja_id', $caja->id)
            ->selectRaw('forma_pago, SUM(total) as monto, COUNT(*) as cantidad')
            ->groupBy('forma_pago')
            ->get()
            ->keyBy('forma_pago');

        $cantidadVentas = (int) $desgloseVentas->sum('cantidad');
        $totalVentas    = (float) $desgloseVentas->sum('monto');
        $totalMixto     = (float) ($desgloseVentas->get('MIXTO')?->monto ?? 0);

        $desglose = [
            'efectivo'       => (float) ($desgloseVentas->get('EFECTIVO')?->monto       ?? 0),
            'tarjeta_debito'  => (float) ($desgloseVentas->get('TARJETA_DEBITO')?->monto  ?? 0),
            'tarjeta_credito' => (float) ($desgloseVentas->get('TARJETA_CREDITO')?->monto ?? 0),
            'transferencia'   => (float) ($desgloseVentas->get('TRANSFERENCIA')?->monto   ?? 0),
            'cheque'         => (float) ($desgloseVentas->get('CHEQUE')?->monto          ?? 0),
            'mixto'          => $totalMixto,
        ];

        // Para ventas MIXTO, sumar el desglose real desde FormasPagoVenta
        if ($totalMixto > 0) {
            $mixtoDesglose = FormaPagoVenta::whereIn(
                'venta_id',
                Venta::where('caja_id', $caja->id)->where('forma_pago', 'MIXTO')->select('id')
            )
                ->selectRaw('forma_pago, SUM(monto) as monto')
                ->groupBy('forma_pago')
                ->pluck('monto', 'forma_pago');

            $desglose['efectivo']       += (float) ($mixtoDesglose->get('EFECTIVO')       ?? 0);
            $desglose['tarjeta_debito']  += (float) ($mixtoDesglose->get('TARJETA_DEBITO')  ?? 0);
            $desglose['tarjeta_credito'] += (float) ($mixtoDesglose->get('TARJETA_CREDITO') ?? 0);
            $desglose['transferencia']   += (float) ($mixtoDesglose->get('TRANSFERENCIA')   ?? 0);
            $desglose['cheque']         += (float) ($mixtoDesglose->get('CHEQUE')          ?? 0);
        }

        $corporateData = Cache::remember('corporate_data', 3600, fn () => CorporateData::pluck('description_item', 'item')->toArray());

        $retiros = RetiroCaja::where('caja_id', $caja->id)
            ->orderBy('created_at')
            ->get(['monto', 'motivo', 'created_at']);
        $totalRetiros = (float) $retiros->sum('monto');

        $pdf = Pdf::loadView('ventas.ticket_cierre_caja', compact('caja', 'cantidadVentas', 'totalVentas', 'desglose', 'corporateData', 'retiros', 'totalRetiros'));
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

        return $pdf->stream('cierre-caja-' . str_pad($caja->id, 4, '0', STR_PAD_LEFT) . '.pdf');
    }

    /**
     * Muestra la vista de historial de cierres de caja
     */
    public function historialCierres()
    {
        return view('ventas.historial_cierres');
    }

    /**
     * Consolida múltiples cierres de caja y devuelve JSON con resumen
     */
    public function consolidarCajas(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (!is_array($ids) || count($ids) < 2) {
                return response()->json(['error' => 'Selecciona al menos 2 cierres para consolidar.'], 422);
            }

            $ids = array_map('intval', $ids);

            $user = Auth::user();
            $puedeVerTodos = puedeVerTodosCierres();

            $query = Caja::with('usuario')->where('estado', 'cerrada')->whereIn('id', $ids);
            if (!$puedeVerTodos) {
                $query->where('user_id', $user->id);
            }

            $cajas = $query->orderBy('fecha_apertura')->get();

            if ($cajas->count() < 2) {
                return response()->json(['error' => 'No se encontraron suficientes cierres con los IDs proporcionados.'], 404);
            }

            // Calcular desglose consolidado con una sola query
            $desgloseVentas = Venta::whereIn('caja_id', $cajas->pluck('id'))
                ->selectRaw('forma_pago, SUM(total) as monto, COUNT(*) as cantidad')
                ->groupBy('forma_pago')
                ->get()
                ->keyBy('forma_pago');

            $cantidadVentas = (int) $desgloseVentas->sum('cantidad');
            $totalVentas    = (float) $desgloseVentas->sum('monto');
            $totalMixto     = (float) ($desgloseVentas->get('MIXTO')?->monto ?? 0);

            $desglose = [
                'efectivo'        => (float) ($desgloseVentas->get('EFECTIVO')?->monto        ?? 0),
                'tarjeta_debito'  => (float) ($desgloseVentas->get('TARJETA_DEBITO')?->monto  ?? 0),
                'tarjeta_credito' => (float) ($desgloseVentas->get('TARJETA_CREDITO')?->monto ?? 0),
                'transferencia'   => (float) ($desgloseVentas->get('TRANSFERENCIA')?->monto   ?? 0),
                'cheque'          => (float) ($desgloseVentas->get('CHEQUE')?->monto          ?? 0),
                'mixto'           => $totalMixto,
            ];

            if ($totalMixto > 0) {
                $mixtoDesglose = FormaPagoVenta::whereIn(
                    'venta_id',
                    Venta::whereIn('caja_id', $cajas->pluck('id'))->where('forma_pago', 'MIXTO')->select('id')
                )
                    ->selectRaw('forma_pago, SUM(monto) as monto')
                    ->groupBy('forma_pago')
                    ->pluck('monto', 'forma_pago');

                $desglose['efectivo']        += (float) ($mixtoDesglose->get('EFECTIVO')        ?? 0);
                $desglose['tarjeta_debito']  += (float) ($mixtoDesglose->get('TARJETA_DEBITO')  ?? 0);
                $desglose['tarjeta_credito'] += (float) ($mixtoDesglose->get('TARJETA_CREDITO') ?? 0);
                $desglose['transferencia']   += (float) ($mixtoDesglose->get('TRANSFERENCIA')   ?? 0);
                $desglose['cheque']          += (float) ($mixtoDesglose->get('CHEQUE')          ?? 0);
            }

            $montoInicalTotal    = (float) $cajas->sum('monto_inicial');
            $montoDeclaradoTotal = (float) $cajas->sum('monto_final_declarado');

            // Retiros consolidados de todas las cajas
            $retirosTodos = RetiroCaja::whereIn('caja_id', $cajas->pluck('id'))
                ->orderBy('created_at')
                ->get(['caja_id', 'monto', 'motivo', 'created_at']);
            $totalRetirosConsolidado = (float) $retirosTodos->sum('monto');

            $diferenciaTotal     = $montoDeclaradoTotal - ($montoInicalTotal + $totalVentas - $totalRetirosConsolidado);

            $detalleCajas = $cajas->map(fn ($c) => [
                'id'              => str_pad($c->id, 4, '0', STR_PAD_LEFT),
                'usuario'         => $c->usuario->name ?? 'N/A',
                'fecha_apertura'  => $c->fecha_apertura->format('d/m/Y H:i'),
                'fecha_cierre'    => $c->fecha_cierre->format('d/m/Y H:i'),
                'monto_inicial'   => (float) $c->monto_inicial,
                'monto_ventas'    => (float) $c->monto_ventas,
                'monto_declarado' => (float) $c->monto_final_declarado,
                'diferencia'      => (float) $c->diferencia,
            ]);

            return response()->json([
                'cajas'             => $detalleCajas,
                'ids'               => $cajas->pluck('id'),
                'fecha_desde'       => $cajas->first()->fecha_apertura->format('d/m/Y H:i'),
                'fecha_hasta'       => $cajas->last()->fecha_cierre->format('d/m/Y H:i'),
                'cantidad_ventas'   => $cantidadVentas,
                'total_ventas'      => $totalVentas,
                'monto_inicial'     => $montoInicalTotal,
                'total_retiros'     => $totalRetirosConsolidado,
                'monto_esperado'    => $montoInicalTotal + $totalVentas - $totalRetirosConsolidado,
                'monto_declarado'   => $montoDeclaradoTotal,
                'diferencia'        => $diferenciaTotal,
                'desglose'          => $desglose,
                'retiros'           => $retirosTodos->map(fn($r) => [
                    'caja_id'    => $r->caja_id,
                    'motivo'     => $r->motivo,
                    'monto'      => (float) $r->monto,
                    'created_at' => \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i'),
                ]),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al consolidar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Genera ticket PDF consolidado de múltiples cierres
     */
    public function imprimirConsolidado(Request $request)
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->query('ids', ''))));

        abort_if(count($ids) < 2, 400, 'Se requieren al menos 2 IDs.');

        $user = Auth::user();
        $puedeVerTodos = puedeVerTodosCierres();

        $query = Caja::with('usuario')->where('estado', 'cerrada')->whereIn('id', $ids);
        if (!$puedeVerTodos) {
            $query->where('user_id', $user->id);
        }

        $cajas = $query->orderBy('fecha_apertura')->get();
        abort_if($cajas->count() < 2, 404, 'No se encontraron cierres.');

        $desgloseVentas = Venta::whereIn('caja_id', $cajas->pluck('id'))
            ->selectRaw('forma_pago, SUM(total) as monto, COUNT(*) as cantidad')
            ->groupBy('forma_pago')
            ->get()
            ->keyBy('forma_pago');

        $cantidadVentas = (int) $desgloseVentas->sum('cantidad');
        $totalVentas    = (float) $desgloseVentas->sum('monto');
        $totalMixto     = (float) ($desgloseVentas->get('MIXTO')?->monto ?? 0);

        $desglose = [
            'efectivo'        => (float) ($desgloseVentas->get('EFECTIVO')?->monto        ?? 0),
            'tarjeta_debito'  => (float) ($desgloseVentas->get('TARJETA_DEBITO')?->monto  ?? 0),
            'tarjeta_credito' => (float) ($desgloseVentas->get('TARJETA_CREDITO')?->monto ?? 0),
            'transferencia'   => (float) ($desgloseVentas->get('TRANSFERENCIA')?->monto   ?? 0),
            'cheque'          => (float) ($desgloseVentas->get('CHEQUE')?->monto          ?? 0),
            'mixto'           => $totalMixto,
        ];

        if ($totalMixto > 0) {
            $mixtoDesglose = FormaPagoVenta::whereIn(
                'venta_id',
                Venta::whereIn('caja_id', $cajas->pluck('id'))->where('forma_pago', 'MIXTO')->select('id')
            )
                ->selectRaw('forma_pago, SUM(monto) as monto')
                ->groupBy('forma_pago')
                ->pluck('monto', 'forma_pago');

            $desglose['efectivo']        += (float) ($mixtoDesglose->get('EFECTIVO')        ?? 0);
            $desglose['tarjeta_debito']  += (float) ($mixtoDesglose->get('TARJETA_DEBITO')  ?? 0);
            $desglose['tarjeta_credito'] += (float) ($mixtoDesglose->get('TARJETA_CREDITO') ?? 0);
            $desglose['transferencia']   += (float) ($mixtoDesglose->get('TRANSFERENCIA')   ?? 0);
            $desglose['cheque']          += (float) ($mixtoDesglose->get('CHEQUE')          ?? 0);
        }

        $montoInicialTotal   = (float) $cajas->sum('monto_inicial');
        $montoDeclaradoTotal = (float) $cajas->sum('monto_final_declarado');

        $retiros      = RetiroCaja::whereIn('caja_id', $cajas->pluck('id'))->orderBy('created_at')->get();
        $totalRetiros = (float) $retiros->sum('monto');
        $diferenciaTotal     = $montoDeclaradoTotal - ($montoInicialTotal + $totalVentas - $totalRetiros);

        $corporateData = Cache::remember('corporate_data', 3600, fn () => CorporateData::pluck('description_item', 'item')->toArray());

        $pdf = Pdf::loadView('ventas.ticket_consolidado', compact(
            'cajas', 'cantidadVentas', 'totalVentas', 'desglose',
            'montoInicialTotal', 'montoDeclaradoTotal', 'diferenciaTotal', 'corporateData',
            'retiros', 'totalRetiros'
        ));
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

        return $pdf->stream('consolidado-cajas.pdf');
    }

    /**
     * Exporta a Excel un consolidado de múltiples cierres
     */
    public function exportarConsolidado(Request $request)
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->query('ids', ''))));
        abort_if(count($ids) < 2, 400, 'Se requieren al menos 2 IDs.');

        $fileName = 'Consolidado_Cajas_' . now()->format('d-m-Y') . '.xlsx';
        return Excel::download(new \App\Exports\ConsolidadoCajaExport($ids), $fileName);
    }

    /**
     * Obtiene los datos de cierres para DataTable
     * Administradores ven todos, usuarios normales solo los suyos
     */
    public function obtenerCierresDataTable(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Query base
            $query = Caja::with(['usuario'])
                ->where('estado', 'cerrada')
                ->orderBy('fecha_cierre', 'desc');
            
            // Si NO tiene permiso para ver todos los cierres, filtrar solo los suyos
            if (!puedeVerTodosCierres()) {
                $query->where('user_id', $user->id);
            }

            $cierres = $query->limit(500)->get();
            
            // Formatear datos para DataTable
            $data = $cierres->map(function ($caja) {
                $diferencia = $caja->diferencia;
                $claseDiferencia = '';
                $textoDiferencia = '';
                
                if ($diferencia > 0) {
                    $claseDiferencia = 'text-success';
                    $textoDiferencia = '+$' . number_format($diferencia, 0, ',', '.');
                } elseif ($diferencia < 0) {
                    $claseDiferencia = 'text-danger';
                    $textoDiferencia = '-$' . number_format(abs($diferencia), 0, ',', '.');
                } else {
                    $claseDiferencia = 'text-info';
                    $textoDiferencia = 'Exacto';
                }
                
                return [
                    'id' => str_pad($caja->id, 4, '0', STR_PAD_LEFT),
                    'usuario' => $caja->usuario->name ?? 'N/A',
                    'fecha_apertura' => $caja->fecha_apertura->format('d/m/Y H:i'),
                    'fecha_cierre' => $caja->fecha_cierre->format('d/m/Y H:i'),
                    'monto_inicial' => '$' . number_format($caja->monto_inicial, 0, ',', '.'),
                    'monto_inicial_raw' => (int) $caja->monto_inicial,
                    'monto_ventas' => '$' . number_format($caja->monto_ventas, 0, ',', '.'),
                    'monto_ventas_raw' => (int) $caja->monto_ventas,
                    'monto_esperado' => '$' . number_format($caja->monto_inicial + $caja->monto_ventas, 0, ',', '.'),
                    'monto_esperado_raw' => (int) ($caja->monto_inicial + $caja->monto_ventas),
                    'monto_declarado' => '$' . number_format($caja->monto_final_declarado, 0, ',', '.'),
                    'monto_declarado_raw' => (int) $caja->monto_final_declarado,
                    'diferencia' => '<span class="' . $claseDiferencia . ' font-weight-bold">' . $textoDiferencia . '</span>',
                    'diferencia_raw' => (int) $diferencia,
                    'actions' => '
                        <button class="btn btn-sm btn-primary ver-ticket-pdf" data-caja-id="' . $caja->id . '" data-toggle="tooltip" title="Ver ticket">
                            <i class="fa fa-print"></i>
                        </button>
                        <button class="btn btn-sm btn-info ver-detalle" data-caja-id="' . $caja->id . '" data-toggle="tooltip" title="Ver detalle">
                            <i class="fa fa-eye"></i>
                        </button>
                    '
                ];
            });
            
            return response()->json(['data' => $data]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener cierres: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el detalle de un cierre de caja
     */
    public function obtenerDetalleCierre($cajaId)
    {
        try {
            $user = Auth::user();
            $caja = Caja::with(['usuario', 'ventas.formasPago'])->findOrFail($cajaId);
            
            // Verificar permisos usando el sistema formal
            $puedeVerCierres = $user->tienePermiso(Permiso::PERMISO_CIERRES_CAJA);

            if (!$puedeVerCierres && $caja->user_id !== $user->id) {
                return response()->json([
                    'error' => 'No tienes permiso para ver este cierre'
                ], 403);
            }
            
            // Calcular resumen
            $cantidadVentas = $caja->ventas->count();
            $totalVentas = $caja->ventas->sum('total');
            
            // Retiros de caja
            $retiros = RetiroCaja::where('caja_id', $caja->id)
                ->orderBy('created_at')
                ->get(['monto', 'motivo', 'created_at']);
            $totalRetiros = (float) $retiros->sum('monto');

            // Desglose por forma de pago
            $desglose = [
                'efectivo' => 0,
                'tarjeta_debito' => 0,
                'tarjeta_credito' => 0,
                'transferencia' => 0,
                'cheque' => 0,
                'mixto' => 0,
            ];
            
            foreach ($caja->ventas as $venta) {
                if ($venta->forma_pago === 'MIXTO') {
                    foreach ($venta->formasPago as $formaPago) {
                        $tipo = strtolower($formaPago->tipo_forma_pago);
                        if (isset($desglose[$tipo])) {
                            $desglose[$tipo] += $formaPago->monto;
                        }
                    }
                    $desglose['mixto'] += $venta->total;
                } else {
                    $tipo = strtolower($venta->forma_pago);
                    if (isset($desglose[$tipo])) {
                        $desglose[$tipo] += $venta->total;
                    }
                }
            }
            
            return response()->json([
                'caja' => [
                    'id' => str_pad($caja->id, 4, '0', STR_PAD_LEFT),
                    'usuario' => $caja->usuario->name ?? 'N/A',
                    'fecha_apertura' => $caja->fecha_apertura->format('d/m/Y H:i:s'),
                    'fecha_cierre' => $caja->fecha_cierre->format('d/m/Y H:i:s'),
                    'duracion' => $caja->fecha_apertura->diffForHumans($caja->fecha_cierre, true),
                    'monto_inicial' => $caja->monto_inicial,
                    'monto_ventas' => $totalVentas,
                    'total_retiros' => $totalRetiros,
                    'monto_esperado' => $caja->monto_inicial + $totalVentas - $totalRetiros,
                    'monto_declarado' => $caja->monto_final_declarado,
                    'diferencia' => $caja->diferencia,
                    'observaciones' => $caja->observaciones,
                    'cantidad_ventas' => $cantidadVentas
                ],
                'desglose' => $desglose,
                'retiros' => $retiros->map(fn($r) => [
                    'motivo'     => $r->motivo,
                    'monto'      => (float) $r->monto,
                    'created_at' => \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i'),
                ]),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener detalle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera el ticket de venta en formato PDF
     */
    public function generarTicketPDF($ventaId)
    {
        $venta = Venta::with(['detalles', 'usuario', 'formasPago', 'caja'])->findOrFail($ventaId);

        $tipoNegocio = Cache::remember('global_TIPO_NEGOCIO', 300, fn () => Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var'));
        $esVentaRestaurant = strtolower(trim((string) $tipoNegocio)) === 'restaurant';

        if ($esVentaRestaurant) {
            $comanda = null;

            $movimientoComanda = HistorialMovimientos::where('num_doc', (string) $venta->id)
                ->where('tipo_mov', 'VENTA')
                ->where('obs', 'like', 'Cierre comanda %')
                ->orderByDesc('id')
                ->first();

            if ($movimientoComanda && preg_match('/Cierre comanda\s+(.+)$/u', (string) $movimientoComanda->obs, $coincidencia)) {
                $numeroComanda = trim($coincidencia[1]);

                $comanda = Comanda::with(['mesa', 'detalles.producto', 'user', 'garzon'])
                    ->where('numero_comanda', $numeroComanda)
                    ->first();
            }

            if ($comanda) {
                $corporateData = Cache::remember('corporate_data', 3600, fn () => CorporateData::pluck('description_item', 'item')->toArray());
                $porcentajeGlobal = Cache::remember('global_PORCENTAJE_PROPINA', 300, fn () => Globales::where('nom_var', 'PORCENTAJE_PROPINA')->value('valor_var'));
                $porcentajePropinaGlobal = is_null($porcentajeGlobal) ? 10 : (float) $porcentajeGlobal;
                $porcentajePropinaGlobal = max(0, min(100, $porcentajePropinaGlobal));
                $esTicketPago = true;

                $pdf = Pdf::loadView('restaurant.ticket_comanda', compact('comanda', 'corporateData', 'venta', 'esTicketPago', 'porcentajePropinaGlobal'));
                $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

                return $pdf->stream('ticket-comanda-' . ($comanda->numero_comanda ?? str_pad($comanda->id, 4, '0', STR_PAD_LEFT)) . '.pdf');
            }
        }
        
        // Cargar datos corporativos
        $corporateData = Cache::remember('corporate_data', 3600, fn () => CorporateData::pluck('description_item', 'item')->toArray());
        
        // Cargar la vista del ticket
        $pdf = Pdf::loadView('ventas.ticket', compact('venta', 'corporateData'));
        
        // Configurar papel de 80mm de ancho (226.77 puntos = 80mm)
        // Alto automático según contenido
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
        
        // Mostrar el PDF en el navegador
        return $pdf->stream('ticket-' . $venta->numero_venta . '.pdf');
    }

    /**
     * Vista del historial de tickets
     */
    public function historialTickets()
    {
        return view('ventas.historial_tickets');
    }

    /**
     * Obtener tickets para DataTable con filtro de fechas
     */
    public function obtenerTickets(Request $request)
    {
        try {
            $user = Auth::user();
            $fechaDesde = $request->input('fecha_desde');
            $fechaHasta = $request->input('fecha_hasta');
            
            // Query base
            $query = Venta::select(['id', 'total', 'fecha_venta', 'forma_pago', 'estado', 'user_id', 'caja_id'])
                ->with([
                    'usuario:id,name,name_complete',
                    'formasPago:venta_id,forma_pago',
                    'caja:id,tipo_caja',
                ])
                ->withCount('detalles as cantidad_productos')
                ->where('estado', 'completada');
            
            // Si el usuario NO tiene permiso para ver todas las ventas, solo mostrar las propias
            if (!puedeVerTodasVentas()) {
                $query->where('user_id', $user->id);
            }
            
            $query->orderBy('fecha_venta', 'desc');
            
            // Aplicar filtros de fecha si existen
            if ($fechaDesde) {
                $query->whereDate('fecha_venta', '>=', $fechaDesde);
            }
            if ($fechaHasta) {
                $query->whereDate('fecha_venta', '<=', $fechaHasta);
            }

            $ventas = $query->limit(1000)->get();
            
            // Formatear datos para DataTable
            $data = $ventas->map(function ($venta) {
                $esTicketRestaurant = strtoupper((string) optional($venta->caja)->tipo_caja) === 'RESTAURANT';

                // Contar productos (pre-calculado con withCount, sin cargar la colección)
                $cantidadProductos = $venta->cantidad_productos;
                
                // Determinar forma de pago
                $formaPago = $venta->forma_pago;
                if ($formaPago === 'MIXTO') {
                    $formas = $venta->formasPago->pluck('forma_pago')->map(function($fp) {
                        return str_replace('_', ' ', $fp);
                    })->implode(' + ');
                    $formaPago = $formas;
                } else {
                    $formaPago = str_replace('_', ' ', $formaPago);
                }
                
                // Botón de anular solo si tiene el permiso
                $botonesAnular = '';
                if (puedeAnularTickets()) {
                    if ($venta->estado === 'completada') {
                        $botonesAnular = '
                        <button class="btn btn-sm btn-danger anular-ticket" data-venta-id="' . $venta->id . '" data-toggle="tooltip" title="Anular ticket">
                            <i class="fa fa-ban"></i>
                        </button>';
                    } elseif ($venta->estado === 'parcialmente_anulada') {
                        $botonesAnular = '
                        <button class="btn btn-sm btn-warning anular-ticket" data-venta-id="' . $venta->id . '" data-toggle="tooltip" title="Ver/Anular productos">
                            <i class="fa fa-exclamation-triangle"></i>
                        </button>';
                    } else {
                        $botonesAnular = '
                        <button class="btn btn-sm btn-secondary anular-ticket" data-venta-id="' . $venta->id . '" data-toggle="tooltip" title="Ver detalle de anulación">
                            <i class="fa fa-info-circle"></i>
                        </button>';
                    }
                }
                
                return [
                    'id' => str_pad($venta->id, 4, '0', STR_PAD_LEFT),
                    'fecha' => $venta->fecha_venta->format('d/m/Y H:i'),
                    'vendedor' => $venta->usuario->name ?? 'N/A',
                    'total' => '$' . number_format($venta->total, 0, ',', '.'),
                    'total_raw' => (int)$venta->total,
                    'forma_pago' => $formaPago,
                    'origen_ticket' => $esTicketRestaurant ? 'RESTAURANT' : 'VENTA',
                    'productos' => $cantidadProductos . ' ' . ($cantidadProductos == 1 ? 'producto' : 'productos'),
                    'estado' => $venta->estado,
                    'actions' => '
                        <button class="btn btn-sm btn-primary ver-ticket" data-venta-id="' . $venta->id . '" data-ticket-origen="' . ($esTicketRestaurant ? 'RESTAURANT' : 'VENTA') . '" data-toggle="tooltip" title="Ver ticket">
                            <i class="fa fa-print"></i>
                        </button>
                        ' . $botonesAnular . '
                    '
                ];
            });
            
            return response()->json(['data' => $data]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de un ticket para anulación
     */
    public function obtenerDetalleTicket($ventaId)
    {
        try {
            $venta = Venta::with(['detalles.usuarioAnulacion', 'usuario', 'caja', 'formasPago'])->findOrFail($ventaId);
            
            // Verificar que el ticket pertenezca al usuario actual o sea admin
            $user = Auth::user();
            $roleName = $user->role->role_name ?? '';
            $esAdmin = in_array(strtolower($roleName), ['administrador', 'superadministrador']);
            
            if (!$esAdmin && $venta->user_id != $user->id) {
                return response()->json(['error' => 'No tienes permisos para ver este ticket'], 403);
            }
            
            // NO bloquear la vista - permitir ver información incluso si está completamente anulado
            // La validación se hace en el frontend para ocultar botones de acción

            // Pre-fetch productos y recetas para evitar N+1 por cada línea de detalle
            $todosUuids = $venta->detalles->pluck('producto_uuid')->filter()->unique()->values()->all();
            $productosPorFetch = Producto::whereIn('uuid', $todosUuids)->get()->keyBy('uuid');
            $recetaUuidsFetch = collect($todosUuids)
                ->filter(fn ($u) => str_starts_with((string) $u, 'RECETA-'))
                ->map(fn ($u) => substr($u, 7))
                ->values()->all();
            $recetasPorFetch = !empty($recetaUuidsFetch)
                ? Receta::whereIn('uuid', $recetaUuidsFetch)->get()->keyBy('uuid')
                : collect();

            // Formatear detalles
            $detalles = $venta->detalles->map(function($detalle) use ($productosPorFetch, $recetasPorFetch) {
                $producto = $productosPorFetch->get($detalle->producto_uuid);
                $tipo = $producto ? $producto->tipo : 'simple';

                if (!$producto) {
                    $uuidReceta = str_starts_with((string) $detalle->producto_uuid, 'RECETA-')
                        ? substr((string) $detalle->producto_uuid, 7)
                        : (string) $detalle->producto_uuid;

                    if ($recetasPorFetch->has($uuidReceta)) {
                        $tipo = 'RECETA';
                    }
                }
                
                return [
                    'id' => $detalle->id,
                    'descripcion' => $detalle->descripcion_producto,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal_linea,
                    'producto_uuid' => $detalle->producto_uuid,
                    'tipo' => $tipo,
                    'anulado' => $detalle->anulado,
                    'fecha_anulacion' => $detalle->fecha_anulacion ? $detalle->fecha_anulacion->format('d/m/Y H:i') : null,
                    'usuario_anulacion' => $detalle->usuarioAnulacion->name ?? null,
                    'motivo_anulacion' => $detalle->motivo_anulacion,
                ];
            });
            
            return response()->json([
                'venta' => [
                    'id' => $venta->id,
                    'fecha' => $venta->fecha_venta->format('d/m/Y H:i'),
                    'total' => $venta->total,
                    'vendedor' => $venta->usuario->name ?? 'N/A',
                    'caja_id' => $venta->caja_id,
                    'estado' => $venta->estado,
                ],
                'detalles' => $detalles
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener detalles: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Anular ticket completo o parcialmente
     */
    public function anularTicket(Request $request, $ventaId)
    {
        try {
            DB::beginTransaction();
            
            $venta = Venta::with(['detalles', 'caja'])->findOrFail($ventaId);
            
            // Verificar permisos usando el sistema formal
            $user = Auth::user();
            $puedeAnular = $user->tienePermiso(Permiso::PERMISO_ANULAR_TICKETS);

            if (!$puedeAnular && $venta->user_id != $user->id) {
                return response()->json(['error' => 'No tienes permisos para anular este ticket'], 403);
            }
            
            // Verificar estado - SOLO bloquear si está completamente anulado
            if ($venta->estado === 'anulada') {
                return response()->json(['error' => 'Este ticket ya está completamente anulado'], 400);
            }
            
            // NO verificar caja cerrada - PERMITIR anular tickets de cajas cerradas
            
            // Obtener parámetros
            $detallesAnular = $request->input('detalles', []); // Array de IDs de detalles a anular
            $motivo = $request->input('motivo', 'Anulación de ticket');
            
            // Determinar si es anulación completa basándose en si hay detalles específicos
            // Si detalles está vacío = anular todo
            // Si detalles tiene IDs = anular solo esos
            $anulacionCompleta = empty($detallesAnular);
            
            $montoAnulado = 0;
            $productosAnulados = 0;
            
            if ($anulacionCompleta) {
                // Anular todos los productos no anulados
                foreach ($venta->detalles as $detalle) {
                    if (!$detalle->anulado) {
                        $this->devolverStockDetalle($detalle, $user, $venta, $motivo);
                        $montoAnulado += $detalle->subtotal_linea;
                        $productosAnulados++;
                        
                        // Marcar detalle como anulado (no eliminar)
                        $detalle->anulado = true;
                        $detalle->fecha_anulacion = now();
                        $detalle->user_anulacion_id = $user->id;
                        $detalle->motivo_anulacion = $motivo;
                        $detalle->save();
                    }
                }
                
            } else {
                // Anulación parcial
                foreach ($detallesAnular as $detalleId) {
                    $detalle = DetalleVenta::find($detalleId);
                    if ($detalle && $detalle->venta_id == $ventaId && !$detalle->anulado) {
                        $this->devolverStockDetalle($detalle, $user, $venta, $motivo);
                        $montoAnulado += $detalle->subtotal_linea;
                        $productosAnulados++;
                        
                        // Marcar detalle como anulado (no eliminar)
                        $detalle->anulado = true;
                        $detalle->fecha_anulacion = now();
                        $detalle->user_anulacion_id = $user->id;
                        $detalle->motivo_anulacion = $motivo;
                        $detalle->save();
                    }
                }
            }
            
            // RECALCULAR ESTADO basándose en productos anulados
            $totalDetalles = $venta->detalles()->count();
            $detallesAnulados = $venta->detalles()->where('anulado', true)->count();
            
            if ($detallesAnulados === $totalDetalles) {
                // TODOS anulados
                $venta->estado = 'anulada';
                $venta->total = 0;
            } elseif ($detallesAnulados > 0) {
                // ALGUNOS anulados
                $venta->estado = 'parcialmente_anulada';
                $venta->total = $venta->detalles()->where('anulado', false)->sum('subtotal_linea');
            } else {
                // NINGUNO anulado
                $venta->estado = 'completada';
            }
            
            $venta->save();
            
            // Restar el monto anulado de la caja (SOLO si está abierta)
            if ($venta->caja && $venta->caja->estado === 'abierta') {
                $venta->caja->monto_ventas -= $montoAnulado;
                $venta->caja->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'mensaje' => $productosAnulados . ' producto(s) anulado(s) correctamente',
                'monto_anulado' => $montoAnulado,
                'estado' => $venta->estado
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al anular ticket: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Devolver stock de un detalle de venta
     * Maneja productos simples, recetas y promociones
     * Registra en historial_movimientos
     */
    private function devolverStockDetalle($detalle, $user, $venta, $motivo)
    {
        $cantidad = $detalle->cantidad;
        $ticketNum = $venta->id;

        // Primero verificar si es un producto
        $producto = Producto::where('uuid', $detalle->producto_uuid)->first();

        if ($producto) {
            // Verificar si es una receta (producto con receta_id)
            if (!empty($producto->receta_id)) {
                $receta = Receta::with('ingredientes.producto')->find($producto->receta_id);
                if ($receta) {
                    foreach ($receta->ingredientes as $ingrediente) {
                        if ($ingrediente->producto && in_array($ingrediente->producto->tipo, ['P', 'I'], true)) {
                            $cantidadDevolver = $ingrediente->cantidad * $cantidad;
                            $stockNuevo = $ingrediente->producto->stock + $cantidadDevolver;
                            DB::table('productos')->where('id', $ingrediente->producto->id)->increment('stock', $cantidadDevolver);
                            try {
                                HistorialMovimientos::registrarMovimiento([
                                    'producto_id' => $ingrediente->producto->id,
                                    'cantidad' => $cantidadDevolver,
                                    'stock' => $stockNuevo,
                                    'tipo_mov' => 'ANULACIÓN',
                                    'fecha' => now(),
                                    'num_doc' => (string) $ticketNum,
                                    'obs' => 'Anulación de venta ticket ' . $ticketNum . ' - Ingrediente de receta: ' . $producto->descripcion . ' - Motivo: ' . $motivo . ' - Usuario: ' . $user->name
                                ]);
                            } catch (\Exception $e) {
                                Log::error('Error al registrar movimiento receta', [
                                    'producto_id' => $ingrediente->producto->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    }
                }
                return;
            }

            // Es un PRODUCTO SIMPLE (tipo P o S, sin receta_id)
            if ($producto->tipo === 'P') {
                DB::table('productos')->where('id', $producto->id)->increment('stock', $cantidad);
            }

            // Registrar movimiento (para P y S)
            try {
                HistorialMovimientos::registrarMovimiento([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'stock' => $producto->tipo === 'P' ? ($producto->stock + $cantidad) : null,
                    'tipo_mov' => 'ANULACIÓN',
                    'fecha' => now(),
                    'num_doc' => (string) $ticketNum,
                    'obs' => 'Anulación de venta ticket ' . $ticketNum . ' - Motivo: ' . $motivo . ' - Usuario: ' . $user->name
                ]);
            } catch (\Exception $e) {
                Log::error('Error al registrar movimiento simple', [
                    'producto_id' => $producto->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return;
        }

        // Si no es producto, verificar si es una receta por UUID directo o con prefijo legacy
        $uuidReceta = str_starts_with((string) $detalle->producto_uuid, 'RECETA-')
            ? substr((string) $detalle->producto_uuid, 7)
            : (string) $detalle->producto_uuid;
        $receta = Receta::with('ingredientes.producto')->where('uuid', $uuidReceta)->first();

        if ($receta) {
            foreach ($receta->ingredientes as $ingrediente) {
                if ($ingrediente->producto && in_array($ingrediente->producto->tipo, ['P', 'I'], true)) {
                    $cantidadDevolver = $ingrediente->cantidad * $cantidad;
                    $stockNuevo = $ingrediente->producto->stock + $cantidadDevolver;
                    DB::table('productos')->where('id', $ingrediente->producto->id)->increment('stock', $cantidadDevolver);
                    try {
                        HistorialMovimientos::registrarMovimiento([
                            'producto_id' => $ingrediente->producto->id,
                            'cantidad' => $cantidadDevolver,
                            'stock' => $stockNuevo,
                            'tipo_mov' => 'ANULACIÓN',
                            'fecha' => now(),
                            'num_doc' => (string) $ticketNum,
                            'obs' => 'Anulación de venta ticket ' . $ticketNum . ' - Ingrediente de receta: ' . ($receta->nombre ?? 'RECETA') . ' - Motivo: ' . $motivo . ' - Usuario: ' . $user->name,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al registrar movimiento receta por UUID', [
                            'producto_id' => $ingrediente->producto->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            return;
        }

        // Si no es producto, verificar si es una PROMOCIÓN
        $promocion = Promocion::where('uuid', $detalle->producto_uuid)->first();

        if ($promocion) {
            $detallesPromocion = PromocionDetalle::where('promo_id', $promocion->id)
                ->with('producto')
                ->get();

            foreach ($detallesPromocion as $detalleProm) {
                if ($detalleProm->producto && $detalleProm->producto->tipo === 'P') {
                    $cantidadDevolver = $detalleProm->cantidad * $cantidad;
                    $stockNuevo = $detalleProm->producto->stock + $cantidadDevolver;
                    DB::table('productos')->where('id', $detalleProm->producto->id)->increment('stock', $cantidadDevolver);
                    try {
                        HistorialMovimientos::registrarMovimiento([
                            'producto_id' => $detalleProm->producto->id,
                            'cantidad' => $cantidadDevolver,
                            'stock' => $stockNuevo,
                            'tipo_mov' => 'ANULACIÓN',
                            'fecha' => now(),
                            'num_doc' => (string) $ticketNum,
                            'obs' => 'Anulación de venta ticket ' . $ticketNum . ' - Producto de promoción: ' . $promocion->nombre . ' - Motivo: ' . $motivo . ' - Usuario: ' . $user->name
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al registrar movimiento promoción', [
                            'producto_id' => $detalleProm->producto->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            return;
        }

        // Si llegamos aquí, no se encontró ni producto ni promoción
        Log::warning('UUID no encontrado en productos ni promociones', [
            'producto_uuid' => $detalle->producto_uuid,
            'detalle_id' => $detalle->id
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  MÓDULO PREVENTA (ALMACEN_PREVENTA)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Vista del módulo Generar Preventa
     */
    public function indexPreventa()
    {
        return view('ventas.generar_preventa');
    }

    /**
     * Vista del módulo Cierre Preventa
     */
    public function indexCierrePreventa()
    {
        $cajaAbierta = Caja::cajaAbiertaUsuario(Auth::id(), self::TIPO_CAJA_MODULO_VENTAS);

        return view('ventas.cierre_preventa', [
            'cajaAbierta' => $cajaAbierta,
        ]);
    }

    /**
     * Procesa y guarda una preventa.
     * No descuenta stock, no requiere caja ni forma de pago.
     */
    public function procesarPreventa(StorePreventaRequest $request)
    {
        DB::beginTransaction();

        try {
            $venta = Venta::create([
                'total'            => $request->total,
                'total_descuentos' => $request->total_descuentos ?? 0,
                'user_id'          => Auth::id(),
                'caja_id'          => null,
                'forma_pago'       => null,
                'estado'           => 'PREVENTA',
                'fecha_venta'      => $request->fecha_venta ?? now(),
            ]);

            // Registrar en bitácora de estados
            HistorialEstadoVenta::create([
                'venta_id'        => $venta->id,
                'estado_anterior' => null,
                'estado_nuevo'    => 'PREVENTA',
                'accion'          => 'GENERAR_PREVENTA',
                'usuario_id'      => Auth::id(),
                'fecha_cambio'    => now(),
                'observacion'     => 'Preventa generada desde módulo ALMACEN_PREVENTA',
            ]);

            // Guardar detalles (sin descontar stock)
            foreach ($request->detalles as $detalle) {
                $detalleVenta = DetalleVenta::create([
                    'venta_id'             => $venta->id,
                    'producto_uuid'        => $detalle['producto_uuid'] ?? null,
                    'promo_id'             => $detalle['promo_id'] ?? null,
                    'descripcion_producto' => $detalle['descripcion_producto'],
                    'cantidad'             => $detalle['cantidad'],
                    'precio_unitario'      => $detalle['precio_unitario'],
                    'descuento_porcentaje' => $detalle['descuento_porcentaje'] ?? 0,
                    'subtotal_linea'       => $detalle['subtotal_linea'],
                ]);

                // Si el UUID corresponde a una promoción, normalizar el detalle
                if (!empty($detalle['producto_uuid'])) {
                    $esProducto = Producto::where('uuid', $detalle['producto_uuid'])->exists();
                    if (!$esProducto) {
                        $promocion = Promocion::where('uuid', $detalle['producto_uuid'])->first();
                        if ($promocion) {
                            $detalleVenta->update([
                                'promo_id'      => $promocion->id,
                                'producto_uuid' => null,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status'          => 'OK',
                'message'         => 'Preventa generada exitosamente',
                'venta_id'        => $venta->id,
                'numero_preventa' => str_pad($venta->id, 6, '0', STR_PAD_LEFT),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'ERROR',
                'message' => 'Error al generar la preventa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Busca una preventa por código (compatible con scanner).
     * El código esperado es el número mostrado en ticket (ej: 000123).
     */
    public function buscarPreventaPorCodigo(Request $request)
    {
        $request->validate([
            'codigo_preventa' => 'required|string|max:100',
        ]);

        $codigoLimpio = preg_replace('/\D/', '', (string) $request->codigo_preventa);
        $id = (int) ltrim((string) $codigoLimpio, '0');

        if ($id <= 0) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Código de preventa inválido.',
            ], 422);
        }

        $venta = Venta::with(['detalles'])
            ->where('id', $id)
            ->where('estado', 'PREVENTA')
            ->first();

        if (!$venta) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'No se encontró una preventa pendiente con ese código.',
            ], 404);
        }

        return response()->json([
            'status' => 'OK',
            'preventa' => [
                'venta_id' => $venta->id,
                'numero_preventa' => str_pad((string) $venta->id, 6, '0', STR_PAD_LEFT),
                'fecha_preventa' => optional($venta->fecha_venta)->format('d/m/Y H:i:s'),
                'total' => (int) $venta->total,
                'total_descuentos' => (int) ($venta->total_descuentos ?? 0),
                'detalles' => $venta->detalles->map(function ($detalle) {
                    return [
                        'detalle_id' => $detalle->id,
                        'descripcion_producto' => $detalle->descripcion_producto,
                        'cantidad' => (float) $detalle->cantidad,
                        'precio_unitario' => (int) $detalle->precio_unitario,
                        'descuento_porcentaje' => (float) ($detalle->descuento_porcentaje ?? 0),
                        'subtotal_linea' => (int) $detalle->subtotal_linea,
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * Lista preventas pendientes (estado PREVENTA) para panel de seguimiento.
     * Filtra por usuario actual para mostrar solo sus preventas.
     */
    public function listarPreventasPendientes()
    {
        $preventas = Venta::query()
            ->where('estado', 'PREVENTA')
            ->where('user_id', Auth::id()) // Filtrar por usuario actual
            ->orderByDesc('id')
            ->get(['id', 'total', 'fecha_venta']);

        $data = $preventas->map(function ($venta) {
            return [
                'venta_id' => $venta->id,
                'numero_preventa' => str_pad((string) $venta->id, 6, '0', STR_PAD_LEFT),
                'total' => (int) $venta->total,
                'fecha_preventa' => optional($venta->fecha_venta)->format('d/m/Y H:i:s'),
            ];
        })->values();

        return response()->json([
            'status' => 'OK',
            'preventas' => $data,
            'usuario_actual' => Auth::user()->name ?? 'N/A',
        ]);
    }

    /**
     * Cierra una preventa: la pasa a completada, asigna caja/forma de pago,
     * descuenta stock y registra bitácora.
     */
    public function cerrarPreventa(Request $request)
    {
        $request->validate([
            'venta_id' => 'required|integer|exists:ventas,id',
            'forma_pago' => 'required|string|in:EFECTIVO,TARJETA_DEBITO,TARJETA_CREDITO,TRANSFERENCIA,CHEQUE,MIXTO',
            'formas_pago_desglose' => 'nullable|array',
            'formas_pago_desglose.*.forma' => 'required_with:formas_pago_desglose|string|in:EFECTIVO,TARJETA_DEBITO,TARJETA_CREDITO,TRANSFERENCIA,CHEQUE',
            'formas_pago_desglose.*.monto' => 'required_with:formas_pago_desglose|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $cajaAbierta = Caja::cajaAbiertaUsuario(Auth::id(), self::TIPO_CAJA_MODULO_VENTAS);
            if (!$cajaAbierta) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'No tienes una caja abierta. Debes abrir caja antes de cerrar preventas.',
                ], 400);
            }

            $venta = Venta::with(['detalles'])
                ->lockForUpdate()
                ->findOrFail($request->venta_id);

            if ($venta->estado !== 'PREVENTA') {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'La venta seleccionada no está en estado PREVENTA.',
                ], 400);
            }

            if ($venta->detalles->isEmpty()) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'La preventa no tiene productos para procesar.',
                ], 400);
            }

            if ($request->forma_pago === 'MIXTO') {
                $desglose = $request->formas_pago_desglose ?? [];
                if (empty($desglose)) {
                    return response()->json([
                        'status' => 'ERROR',
                        'message' => 'Debe ingresar el desglose para pago mixto.',
                    ], 422);
                }

                $sumaDesglose = (int) collect($desglose)->sum(function ($item) {
                    return (int) ($item['monto'] ?? 0);
                });

                if (abs($sumaDesglose - (int) $venta->total) > 1) {
                    return response()->json([
                        'status' => 'ERROR',
                        'message' => 'La suma del desglose no coincide con el total de la preventa.',
                    ], 422);
                }
            }

            foreach ($venta->detalles as $detalle) {
                $this->descontarStockYRegistrarMovimientoDesdeDetalle($venta, $detalle);
            }

            $estadoAnterior = $venta->estado;

            $venta->update([
                'caja_id' => $cajaAbierta->id,
                'forma_pago' => $request->forma_pago,
                'estado' => 'completada',
                'fecha_venta' => now(),
            ]);

            FormaPagoVenta::where('venta_id', $venta->id)->delete();

            if ($request->forma_pago === 'MIXTO') {
                foreach (($request->formas_pago_desglose ?? []) as $formaPago) {
                    FormaPagoVenta::create([
                        'venta_id' => $venta->id,
                        'forma_pago' => $formaPago['forma'],
                        'monto' => $formaPago['monto'],
                    ]);
                }
            } else {
                FormaPagoVenta::create([
                    'venta_id' => $venta->id,
                    'forma_pago' => $request->forma_pago,
                    'monto' => $venta->total,
                ]);
            }

            HistorialEstadoVenta::create([
                'venta_id' => $venta->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => 'completada',
                'accion' => 'CIERRE_PREVENTA',
                'usuario_id' => Auth::id(),
                'fecha_cambio' => now(),
                'observacion' => 'Preventa cerrada y convertida a venta final.',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'OK',
                'message' => 'Preventa cerrada exitosamente.',
                'venta_id' => $venta->id,
                'numero_venta' => $venta->numero_venta,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'ERROR',
                'message' => 'Error al cerrar la preventa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Descuenta stock y registra historial de movimiento de un detalle de venta existente.
     */
    private function descontarStockYRegistrarMovimientoDesdeDetalle(Venta $venta, DetalleVenta $detalle): void
    {
        $stockNegativo = Cache::remember('global_STOCK_NEGATIVO', 300, fn () => Globales::where('nom_var', 'STOCK_NEGATIVO')->value('valor_var'));
        $permitirStockNegativo = ($stockNegativo == '1');

        if (!empty($detalle->promo_id)) {
            $promocion = Promocion::find($detalle->promo_id);
            if (!$promocion) {
                throw new \Exception('No se encontró la promoción asociada al detalle: ' . $detalle->descripcion_producto);
            }

            $detallesPromocion = PromocionDetalle::where('promo_id', $promocion->id)
                ->with('producto')
                ->get();

            foreach ($detallesPromocion as $detallePromo) {
                $productoPromo = $detallePromo->producto;
                if (!$productoPromo) {
                    continue;
                }

                $cantidadTotal = (float) $detallePromo->cantidad * (float) $detalle->cantidad;

                if ($productoPromo->tipo === 'P') {
                    $productoPromo = Producto::where('id', $productoPromo->id)->lockForUpdate()->first();
                    if (!$productoPromo) {
                        throw new \Exception('No se encontró un producto de la promoción: ' . $detalle->descripcion_producto);
                    }

                    if (!$permitirStockNegativo && (float) $productoPromo->stock < $cantidadTotal) {
                        throw new \Exception('Stock insuficiente para cerrar la preventa en producto: ' . $productoPromo->descripcion);
                    }

                    $productoPromo->stock -= $cantidadTotal;
                    $productoPromo->save();

                    HistorialMovimientos::registrarMovimiento([
                        'producto_id' => $productoPromo->id,
                        'cantidad' => $cantidadTotal,
                        'stock' => $productoPromo->stock,
                        'tipo_mov' => 'VENTA',
                        'fecha' => now(),
                        'num_doc' => (string) $venta->id,
                        'obs' => 'Cierre preventa como parte de promoción: ' . $promocion->nombre,
                    ]);
                } else {
                    HistorialMovimientos::registrarMovimiento([
                        'producto_id' => $productoPromo->id,
                        'cantidad' => $cantidadTotal,
                        'stock' => null,
                        'tipo_mov' => 'VENTA',
                        'fecha' => now(),
                        'num_doc' => (string) $venta->id,
                        'obs' => 'Cierre preventa (servicio) como parte de promoción: ' . $promocion->nombre,
                    ]);
                }
            }

            return;
        }

        if (!empty($detalle->producto_uuid)) {
            $producto = Producto::where('uuid', $detalle->producto_uuid)->lockForUpdate()->first();

            if (!$producto) {
                // Compatibilidad por si quedó UUID de promoción sin normalizar
                $promocion = Promocion::where('uuid', $detalle->producto_uuid)->first();
                if ($promocion) {
                    $detalleTemporal = clone $detalle;
                    $detalleTemporal->promo_id = $promocion->id;
                    $detalleTemporal->producto_uuid = null;
                    $this->descontarStockYRegistrarMovimientoDesdeDetalle($venta, $detalleTemporal);
                    return;
                }

                throw new \Exception('No se encontró el producto del detalle: ' . $detalle->descripcion_producto);
            }

            if ($producto->tipo === 'P') {
                $cantidad = (float) $detalle->cantidad;
                if (!$permitirStockNegativo && (float) $producto->stock < $cantidad) {
                    throw new \Exception('Stock insuficiente para cerrar la preventa en producto: ' . $producto->descripcion);
                }

                $producto->stock -= $cantidad;
                $producto->save();

                HistorialMovimientos::registrarMovimiento([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'stock' => $producto->stock,
                    'tipo_mov' => 'VENTA',
                    'fecha' => now(),
                    'num_doc' => (string) $venta->id,
                    'obs' => 'Cierre preventa',
                ]);
            } else {
                HistorialMovimientos::registrarMovimiento([
                    'producto_id' => $producto->id,
                    'cantidad' => (float) $detalle->cantidad,
                    'stock' => null,
                    'tipo_mov' => 'VENTA',
                    'fecha' => now(),
                    'num_doc' => (string) $venta->id,
                    'obs' => 'Cierre preventa (servicio)',
                ]);
            }
        }
    }

    /**
     * Genera el ticket PDF de una preventa (con código de barras Code39)
     */
    public function generarTicketPreventaPDF(int $ventaId)
    {
        $venta = Venta::with(['detalles', 'usuario'])->findOrFail($ventaId);

        if ($venta->estado !== 'PREVENTA') {
            abort(400, 'El ticket solicitado no es una preventa.');
        }

        $corporateData  = Cache::remember('corporate_data', 3600, fn () => CorporateData::pluck('description_item', 'item')->toArray());
        $numeroPreventa = str_pad($venta->id, 6, '0', STR_PAD_LEFT);
        $barcodeSvg     = BarcodeHelper::generateSvg($numeroPreventa, 2, 55);

        $pdf = Pdf::loadView('ventas.ticket_preventa', compact('venta', 'corporateData', 'numeroPreventa', 'barcodeSvg'));
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

        return $pdf->stream('preventa-' . $numeroPreventa . '.pdf');
    }

}
