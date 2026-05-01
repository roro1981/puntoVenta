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
use App\Models\Comanda;
use App\Models\Globales;
use App\Models\RangoPrecio;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\StorePreventaRequest;
use App\Http\Requests\AperturaCajaRequest;
use App\Http\Requests\CierreCajaRequest;
use App\Models\HistorialEstadoVenta;
use App\Helpers\BarcodeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class VentasController extends Controller
{
    private const TIPO_CAJA_MODULO_VENTAS = 'ALMACEN';

    private function resolverPrecioUnitarioPorCantidad(?string $uuid, float $cantidad, ?float $precioBase = null): float
    {
        $precioBaseNormalizado = is_null($precioBase) ? 0.0 : (float) $precioBase;

        if (empty($uuid) || $cantidad <= 0) {
            return $precioBaseNormalizado;
        }

        $producto = Producto::where('uuid', $uuid)->first();
        if (!$producto) {
            return $precioBaseNormalizado;
        }

        $rango = RangoPrecio::where('producto_id', $producto->id)
            ->where('cantidad_minima', '<=', $cantidad)
            ->where(function ($query) use ($cantidad) {
                $query->whereNull('cantidad_maxima')
                    ->orWhere('cantidad_maxima', 0)
                    ->orWhere('cantidad_maxima', '>=', $cantidad);
            })
            ->orderByDesc('cantidad_minima')
            ->first();

        if ($rango) {
            return (float) $rango->precio_unitario;
        }

        return (float) $producto->precio_venta;
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

            // Obtener ventas de esta caja
            $ventas = Venta::where('caja_id', $caja->id)->get();
            
            // Calcular totales por forma de pago
            $totalEfectivo = 0;
            $totalTarjetaDebito = 0;
            $totalTarjetaCredito = 0;
            $totalTransferencia = 0;
            $totalCheque = 0;
            $totalMixto = 0;

            foreach ($ventas as $venta) {
                $monto = $venta->total;
                
                switch ($venta->forma_pago) {
                    case 'EFECTIVO':
                        $totalEfectivo += $monto;
                        break;
                    case 'TARJETA_DEBITO':
                        $totalTarjetaDebito += $monto;
                        break;
                    case 'TARJETA_CREDITO':
                        $totalTarjetaCredito += $monto;
                        break;
                    case 'TRANSFERENCIA':
                        $totalTransferencia += $monto;
                        break;
                    case 'CHEQUE':
                        $totalCheque += $monto;
                        break;
                    case 'MIXTO':
                        $totalMixto += $monto;
                        // Para MIXTO, sumar los detalles
                        $formasPago = FormaPagoVenta::where('venta_id', $venta->id)->get();
                        foreach ($formasPago as $fp) {
                            switch ($fp->forma_pago) {
                                case 'EFECTIVO':
                                    $totalEfectivo += $fp->monto;
                                    break;
                                case 'TARJETA_DEBITO':
                                    $totalTarjetaDebito += $fp->monto;
                                    break;
                                case 'TARJETA_CREDITO':
                                    $totalTarjetaCredito += $fp->monto;
                                    break;
                                case 'TRANSFERENCIA':
                                    $totalTransferencia += $fp->monto;
                                    break;
                                case 'CHEQUE':
                                    $totalCheque += $fp->monto;
                                    break;
                            }
                        }
                        break;
                }
            }

            $totalVentas = $ventas->sum('total');
            $cantidadVentas = $ventas->count();

            return response()->json([
                'status' => 'OK',
                'caja' => [
                    'id' => $caja->id,
                    'fecha_apertura' => $caja->fecha_apertura->format('d/m/Y H:i:s'),
                    'monto_inicial' => $caja->monto_inicial,
                    'observaciones_apertura' => $caja->observaciones,
                    'total_ventas' => $totalVentas,
                    'cantidad_ventas' => $cantidadVentas,
                    'monto_esperado' => $caja->monto_inicial + $totalVentas,
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
            $ventas = Venta::where('caja_id', $caja->id)->get();
            $totalVentas = $ventas->sum('total');
            $montoEsperado = $caja->monto_inicial + $totalVentas;
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
                ->get();

            $promotions = Promocion::select('uuid', 'codigo', 'nombre as descripcion', 'precio_venta')
                ->where('estado', 'Activo')
                ->whereNull('fec_eliminacion')
                ->where('codigo', $query)
                ->get();
        } else {
            $products = Producto::select('uuid', 'codigo', 'descripcion', 'precio_venta', 'imagen')
                ->where('estado', 'Activo')
                ->whereNull('fec_eliminacion')
                ->where('tipo', '<>', 'I')
                ->where(function($q) use ($query) {
                    $q->where('descripcion', 'like', "%$query%")
                      ->orWhere('codigo', 'like', "%$query%");
                })->get();

            $promotions = Promocion::select('uuid', 'codigo', 'nombre as descripcion', 'precio_venta')
                ->where('estado', 'Activo')
                ->whereNull('fec_eliminacion')
                ->where(function($q) use ($query) {
                    $q->where('nombre', 'like', "%$query%")
                      ->orWhere('codigo', 'like', "%$query%");
                })->get();
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

        // Obtener configuración de stock negativo
        $stockNegativo = Globales::where('nom_var', 'STOCK_NEGATIVO')->value('valor_var');
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
                ], 200);
            }
        }    
        // Si no es producto, buscar promocion
        $promocion = Promocion::where('uuid', $uuid)->first();
        if ($promocion) {
            $detalles = $promocion->detallePromocion()->get();
            $items = [];
            $hasInsufficient = false;

            foreach ($detalles as $det) {
                $prod = Producto::find($det->producto_id);
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
                ], 200);
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
                    // Verificar si es un producto
                    $producto = Producto::where('uuid', $detalle['producto_uuid'])->first();
                    
                    if ($producto) {
                        // Es un producto directo
                        if ($producto->tipo === 'P') {
                            // Producto: Descontar stock y registrar con stock
                            $producto->stock -= $detalle['cantidad'];
                            $producto->save();

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
                        // Verificar si es una promoción (buscada por UUID en tabla promociones)
                        $promocion = Promocion::where('uuid', $detalle['producto_uuid'])->first();
                        
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
                                    // Producto de promoción: Descontar stock
                                    $productoPromo->stock -= $cantidadTotal;
                                    $productoPromo->save();

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
        $caja = Caja::with(['usuario', 'ventas.formasPago'])->findOrFail($cajaId);
        
        // Verificar que la caja esté cerrada
        if ($caja->estado !== 'cerrada') {
            abort(400, 'Solo se pueden generar tickets de cajas cerradas');
        }
        
        // Calcular resumen de ventas
        $cantidadVentas = $caja->ventas->count();
        $totalVentas = $caja->ventas->sum('total');
        
        // Calcular desglose por forma de pago
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
                // Para ventas mixtas, sumar cada forma de pago individualmente
                foreach ($venta->formasPago as $formaPago) {
                    $tipo = strtolower($formaPago->tipo_forma_pago);
                    if (isset($desglose[$tipo])) {
                        $desglose[$tipo] += $formaPago->monto;
                    }
                }
                $desglose['mixto'] += $venta->total;
            } else {
                // Para ventas simples
                $tipo = strtolower($venta->forma_pago);
                if (isset($desglose[$tipo])) {
                    $desglose[$tipo] += $venta->total;
                }
            }
        }
        
        // Cargar datos corporativos
        $corporateData = CorporateData::pluck('description_item', 'item')->toArray();
        
        // Cargar la vista del ticket de cierre
        $pdf = Pdf::loadView('ventas.ticket_cierre_caja', compact('caja', 'cantidadVentas', 'totalVentas', 'desglose', 'corporateData'));
        
        // Configurar papel de 80mm de ancho (226.77 puntos = 80mm)
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
        
        // Mostrar el PDF en el navegador
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
            
            $cierres = $query->get();
            
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
            
            // Verificar permisos
            $roleName = $user->role->role_name ?? '';
            $esAdmin = in_array(strtolower($roleName), ['administrador', 'superadministrador', 'admin', 'superadmin']);
            
            if (!$esAdmin && $caja->user_id !== $user->id) {
                return response()->json([
                    'error' => 'No tienes permiso para ver este cierre'
                ], 403);
            }
            
            // Calcular resumen
            $cantidadVentas = $caja->ventas->count();
            $totalVentas = $caja->ventas->sum('total');
            
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
                    'monto_esperado' => $caja->monto_inicial + $totalVentas,
                    'monto_declarado' => $caja->monto_final_declarado,
                    'diferencia' => $caja->diferencia,
                    'observaciones' => $caja->observaciones,
                    'cantidad_ventas' => $cantidadVentas
                ],
                'desglose' => $desglose
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

        $tipoNegocio = Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var');
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
                $corporateData = CorporateData::pluck('description_item', 'item')->toArray();
                $porcentajeGlobal = Globales::where('nom_var', 'PORCENTAJE_PROPINA')->value('valor_var');
                $porcentajePropinaGlobal = is_null($porcentajeGlobal) ? 10 : (float) $porcentajeGlobal;
                $porcentajePropinaGlobal = max(0, min(100, $porcentajePropinaGlobal));
                $esTicketPago = true;

                $pdf = Pdf::loadView('restaurant.ticket_comanda', compact('comanda', 'corporateData', 'venta', 'esTicketPago', 'porcentajePropinaGlobal'));
                $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

                return $pdf->stream('ticket-comanda-' . ($comanda->numero_comanda ?? str_pad($comanda->id, 4, '0', STR_PAD_LEFT)) . '.pdf');
            }
        }
        
        // Cargar datos corporativos
        $corporateData = CorporateData::pluck('description_item', 'item')->toArray();
        
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
            $query = Venta::with(['usuario', 'detalles', 'formasPago', 'caja'])
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
            
            $ventas = $query->get();
            
            // Formatear datos para DataTable
            $data = $ventas->map(function ($venta) {
                $esTicketRestaurant = strtoupper((string) optional($venta->caja)->tipo_caja) === 'RESTAURANT';

                // Contar productos
                $cantidadProductos = $venta->detalles->count();
                
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
            
            // Formatear detalles
            $detalles = $venta->detalles->map(function($detalle) {
                // Obtener producto para verificar tipo
                $producto = Producto::where('uuid', $detalle->producto_uuid)->first();
                $tipo = $producto->tipo ?? 'simple';

                if (!$producto) {
                    $uuidReceta = str_starts_with((string) $detalle->producto_uuid, 'RECETA-')
                        ? substr((string) $detalle->producto_uuid, 7)
                        : (string) $detalle->producto_uuid;

                    $esReceta = Receta::where('uuid', $uuidReceta)->exists();
                    if ($esReceta) {
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
            
            // Verificar permisos
            $user = Auth::user();
            $roleName = $user->role->role_name ?? '';
            $esAdmin = in_array(strtolower($roleName), ['administrador', 'superadministrador']);
            
            if (!$esAdmin && $venta->user_id != $user->id) {
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
            
            // DEBUG: Log para verificar qué se está recibiendo
            Log::info('Anulación de ticket', [
                'venta_id' => $ventaId,
                'anulacion_completa_calculada' => $anulacionCompleta,
                'detalles_recibidos' => $detallesAnular,
                'count_detalles' => count($detallesAnular),
                'motivo' => $motivo
            ]);
            
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
        $ticketNum = $venta->id; // Número sin ceros a la izquierda
        
        // Primero verificar si es un producto
        $producto = Producto::where('uuid', $detalle->producto_uuid)->first();
        
        if ($producto) {
            Log::info('Devolviendo stock de producto', [
                'producto_id' => $producto->id,
                'producto_tipo' => $producto->tipo,
                'receta_id' => $producto->receta_id,
                'cantidad' => $cantidad,
                'ticket' => $ticketNum
            ]);
            
            // Verificar si es una receta (producto con receta_id)
            if (!empty($producto->receta_id)) {
                // Es una RECETA: devolver stock de los ingredientes
                $receta = Receta::with('ingredientes.producto')->find($producto->receta_id);
                if ($receta) {
                    Log::info('Procesando receta', [
                        'receta_id' => $receta->id,
                        'ingredientes_count' => $receta->ingredientes->count()
                    ]);
                    
                    foreach ($receta->ingredientes as $ingrediente) {
                        if ($ingrediente->producto && in_array($ingrediente->producto->tipo, ['P', 'I'], true)) {
                            $cantidadDevolver = $ingrediente->cantidad * $cantidad;
                            $ingrediente->producto->stock += $cantidadDevolver;
                            $ingrediente->producto->save();
                            
                            // Registrar movimiento de cada ingrediente
                            try {
                                HistorialMovimientos::registrarMovimiento([
                                    'producto_id' => $ingrediente->producto->id,
                                    'cantidad' => $cantidadDevolver,
                                    'stock' => $ingrediente->producto->stock,
                                    'tipo_mov' => 'ANULACIÓN',
                                    'fecha' => now(),
                                    'num_doc' => (string) $ticketNum,
                                    'obs' => 'Anulación de venta ticket ' . $ticketNum . ' - Ingrediente de receta: ' . $producto->descripcion . ' - Motivo: ' . $motivo . ' - Usuario: ' . $user->name
                                ]);
                                
                                Log::info('Movimiento registrado para ingrediente', [
                                    'ingrediente_id' => $ingrediente->producto->id,
                                    'cantidad' => $cantidadDevolver
                                ]);
                            } catch (\Exception $e) {
                                Log::error('Error al registrar movimiento receta', [
                                    'producto_id' => $ingrediente->producto->id,
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                            }
                        }
                    }
                }
                return;
            }
            
            // Es un PRODUCTO SIMPLE (tipo P o S, sin receta_id)
            if ($producto->tipo === 'P') {
                // Solo devolver stock si es tipo P (Producto)
                $producto->stock += $cantidad;
                $producto->save();
                
                Log::info('Stock devuelto a producto', [
                    'producto_id' => $producto->id,
                    'nuevo_stock' => $producto->stock
                ]);
            }
            
            // Registrar movimiento (para P y S)
            try {
                HistorialMovimientos::registrarMovimiento([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'stock' => $producto->tipo === 'P' ? $producto->stock : null,
                    'tipo_mov' => 'ANULACIÓN',
                    'fecha' => now(),
                    'num_doc' => (string) $ticketNum,
                    'obs' => 'Anulación de venta ticket ' . $ticketNum . ' - Motivo: ' . $motivo . ' - Usuario: ' . $user->name
                ]);
                
                Log::info('Movimiento registrado para producto simple', [
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad
                ]);
            } catch (\Exception $e) {
                Log::error('Error al registrar movimiento simple', [
                    'producto_id' => $producto->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
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
            Log::info('Devolviendo stock de receta por UUID', [
                'receta_id' => $receta->id,
                'cantidad' => $cantidad,
                'ticket' => $ticketNum,
            ]);

            foreach ($receta->ingredientes as $ingrediente) {
                if ($ingrediente->producto && in_array($ingrediente->producto->tipo, ['P', 'I'], true)) {
                    $cantidadDevolver = $ingrediente->cantidad * $cantidad;
                    $ingrediente->producto->stock += $cantidadDevolver;
                    $ingrediente->producto->save();

                    try {
                        HistorialMovimientos::registrarMovimiento([
                            'producto_id' => $ingrediente->producto->id,
                            'cantidad' => $cantidadDevolver,
                            'stock' => $ingrediente->producto->stock,
                            'tipo_mov' => 'ANULACIÓN',
                            'fecha' => now(),
                            'num_doc' => (string) $ticketNum,
                            'obs' => 'Anulación de venta ticket ' . $ticketNum . ' - Ingrediente de receta: ' . ($receta->nombre ?? 'RECETA') . ' - Motivo: ' . $motivo . ' - Usuario: ' . $user->name,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al registrar movimiento receta por UUID', [
                            'producto_id' => $ingrediente->producto->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            }

            return;
        }
        
        // Si no es producto, verificar si es una PROMOCIÓN
        $promocion = Promocion::where('uuid', $detalle->producto_uuid)->first();
        
        if ($promocion) {
            Log::info('Devolviendo stock de promoción', [
                'promocion_id' => $promocion->id,
                'cantidad' => $cantidad,
                'ticket' => $ticketNum
            ]);
            
            $detallesPromocion = PromocionDetalle::where('promo_id', $promocion->id)
                ->with('producto')
                ->get();
            
            Log::info('Productos en promoción', [
                'count' => $detallesPromocion->count()
            ]);
            
            foreach ($detallesPromocion as $detalleProm) {
                if ($detalleProm->producto && $detalleProm->producto->tipo === 'P') {
                    $cantidadDevolver = $detalleProm->cantidad * $cantidad;
                    $detalleProm->producto->stock += $cantidadDevolver;
                    $detalleProm->producto->save();
                    
                    // Registrar movimiento de cada producto de la promoción
                    try {
                        HistorialMovimientos::registrarMovimiento([
                            'producto_id' => $detalleProm->producto->id,
                            'cantidad' => $cantidadDevolver,
                            'stock' => $detalleProm->producto->stock,
                            'tipo_mov' => 'ANULACIÓN',
                            'fecha' => now(),
                            'num_doc' => (string) $ticketNum,
                            'obs' => 'Anulación de venta ticket ' . $ticketNum . ' - Producto de promoción: ' . $promocion->nombre . ' - Motivo: ' . $motivo . ' - Usuario: ' . $user->name
                        ]);
                        
                        Log::info('Movimiento registrado para producto de promoción', [
                            'producto_id' => $detalleProm->producto->id,
                            'cantidad' => $cantidadDevolver
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al registrar movimiento promoción', [
                            'producto_id' => $detalleProm->producto->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
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
        $stockNegativo = Globales::where('nom_var', 'STOCK_NEGATIVO')->value('valor_var');
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

        $corporateData  = CorporateData::pluck('description_item', 'item')->toArray();
        $numeroPreventa = str_pad($venta->id, 6, '0', STR_PAD_LEFT);
        $barcodeSvg     = BarcodeHelper::generateSvg($numeroPreventa, 2, 55);

        $pdf = Pdf::loadView('ventas.ticket_preventa', compact('venta', 'corporateData', 'numeroPreventa', 'barcodeSvg'));
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

        return $pdf->stream('preventa-' . $numeroPreventa . '.pdf');
    }

}
