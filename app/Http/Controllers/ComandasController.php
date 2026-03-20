<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Comanda;
use App\Models\DetalleComanda;
use App\Models\HistorialEstadoComanda;
use App\Models\Producto;
use App\Models\Garzon;
use App\Models\Receta;
use App\Models\Promocion;
use App\Models\PromocionDetalle;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\FormaPagoVenta;
use App\Models\HistorialMovimientos;
use App\Models\Caja;
use App\Models\Globales;
use App\Models\CorporateData;
use App\Models\RangoPrecio;
use App\Http\Requests\CierreCajaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ComandasController extends Controller
{
    private const TIPO_CAJA_MODULO_COMANDAS = 'RESTAURANT';
    private const LAYOUT_FILE = 'layouts/restaurant_mesas_layout.json';

    private function resolverPrecioUnitarioComanda(Producto $producto, float $cantidad): float
    {
        if ($cantidad <= 0) {
            return (float) $producto->precio_venta;
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

        return $rango ? (float) $rango->precio_unitario : (float) $producto->precio_venta;
    }

    private function resolverPrecioUnitarioDesdeItem(array $item, float $cantidad): float
    {
        if (($item['tipo_item'] ?? 'PRODUCTO') === 'RECETA') {
            $receta = $item['receta'] ?? null;
            if ($receta) {
                return (float) $receta->precio_venta;
            }

            throw new \Exception('No se pudo resolver el precio de la receta seleccionada');
        }

        $producto = $item['producto'] ?? null;
        if (!$producto) {
            throw new \Exception('No se pudo resolver el producto seleccionado');
        }

        return $this->resolverPrecioUnitarioComanda($producto, $cantidad);
    }

    private function obtenerItemDesdePayloadComanda(array $payload): array
    {
        $origen = mb_strtoupper(trim((string) ($payload['origen'] ?? 'PRODUCTO')), 'UTF-8');
        $productoId = $payload['producto_id'] ?? null;
        $productoUuid = (string) ($payload['producto_uuid'] ?? $payload['uuid'] ?? '');
        $codigo = (string) ($payload['codigo'] ?? '');
        $recetaId = null;
        $tipoItem = 'PRODUCTO';

        if ($origen === 'RECETA' || str_starts_with($productoUuid, 'RECETA-')) {
            $tipoItem = 'RECETA';
            $receta = null;

            if ($codigo === '' && str_starts_with($productoUuid, 'RECETA-')) {
                $recetaUuid = substr($productoUuid, 7);
                $receta = Receta::where('uuid', $recetaUuid)->first();
                $codigo = $receta ? (string) $receta->codigo : '';
                $recetaId = $receta?->id;
            }

            if (!$recetaId && !empty($payload['receta_id'])) {
                $receta = Receta::find($payload['receta_id']);
                if ($receta) {
                    $recetaId = $receta->id;
                    if ($codigo === '') {
                        $codigo = (string) $receta->codigo;
                    }
                }
            }

            if (!$recetaId && !empty($payload['producto_id'])) {
                $receta = Receta::find($payload['producto_id']);
                if ($receta) {
                    $recetaId = $receta->id;
                    if ($codigo === '') {
                        $codigo = (string) $receta->codigo;
                    }
                }
            }

            if ($codigo === '') {
                throw new \Exception('No se pudo resolver el código de la receta seleccionada');
            }

            $codigoNormalizado = mb_strtoupper(trim($codigo), 'UTF-8');

            if (!$recetaId) {
                $recetaPorCodigo = Receta::whereRaw('UPPER(TRIM(codigo)) = ?', [$codigoNormalizado])->first();
                $recetaId = $recetaPorCodigo?->id;
                $receta = $receta ?: $recetaPorCodigo;
            }

            if (!$receta && str_starts_with($productoUuid, 'RECETA-')) {
                $receta = Receta::where('uuid', substr($productoUuid, 7))->first();
                $recetaId = $receta?->id;
            }

            if (!$receta && $codigo !== '') {
                $receta = Receta::whereRaw('UPPER(TRIM(codigo)) = ?', [$codigoNormalizado])->first();
                $recetaId = $receta?->id;
            }

            if (!$receta || !$recetaId) {
                throw new \Exception('No se pudo resolver la receta seleccionada');
            }

            return [
                'producto' => null,
                'receta' => $receta,
                'tipo_item' => $tipoItem,
                'receta_id' => $recetaId,
            ];
        }

        if (!is_null($productoId) && $productoId !== '') {
            return [
                'producto' => Producto::findOrFail($productoId),
                'tipo_item' => $tipoItem,
                'receta_id' => null,
            ];
        }

        if ($productoUuid !== '') {
            return [
                'producto' => Producto::where('uuid', $productoUuid)->firstOrFail(),
                'tipo_item' => $tipoItem,
                'receta_id' => null,
            ];
        }

        throw new \Exception('No se recibieron datos válidos del producto a guardar');
    }

    private function obtenerPorcentajePropinaGlobal(): float
    {
        $valor = Globales::where('nom_var', 'PORCENTAJE_PROPINA')->value('valor_var');
        $porcentaje = is_null($valor) ? 10 : (float) $valor;

        if ($porcentaje < 0) {
            return 0;
        }

        if ($porcentaje > 100) {
            return 100;
        }

        return $porcentaje;
    }

    private function calcularTotalesComanda(Comanda $comanda, ?bool $incluyePropina = null, ?float $porcentajePropina = null): array
    {
        $subtotal = (float) $comanda->detalles()->sum('subtotal');
        $impuestos = 0;

        $aplicaPropina = is_null($incluyePropina) ? (bool) $comanda->incluye_propina : $incluyePropina;
        $porcentaje = is_null($porcentajePropina) ? $this->obtenerPorcentajePropinaGlobal() : $porcentajePropina;
        $porcentaje = max(0, min(100, (float) $porcentaje));

        $propina = $aplicaPropina ? round($subtotal * ($porcentaje / 100)) : 0;
        $total = $subtotal + $propina;

        return [
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'propina' => $propina,
            'total' => $total,
            'porcentaje_propina' => $porcentaje,
            'incluye_propina' => $aplicaPropina,
        ];
    }

    public function index()
    {
        // Obtener todas las mesas activas con su comanda abierta si existe
        $mesas = Mesa::where('activa', true)
            ->with(['comandaAbierta.detalles.producto'])
            ->orderBy('orden')
            ->get();

        return view('restaurant.comandas', [
            'mesas' => $mesas,
            'porcentajePropina' => $this->obtenerPorcentajePropinaGlobal(),
        ]);
    }

    public function indexCerrarComandas()
    {
        $cajaAbierta = Caja::cajaAbiertaUsuario(Auth::id(), self::TIPO_CAJA_MODULO_COMANDAS);

        return view('ventas.cerrar_comandas', [
            'cajaAbierta' => $cajaAbierta,
            'porcentajePropina' => $this->obtenerPorcentajePropinaGlobal(),
        ]);
    }

    public function obtenerComandasPendientesPago()
    {
        try {
            $porcentajeGlobal = $this->obtenerPorcentajePropinaGlobal();

            $comandas = Comanda::with(['mesa', 'garzon', 'detalles.producto'])
                ->where('estado', 'PENDIENTE DE PAGO')
                ->orderBy('updated_at', 'asc')
                ->get()
                ->map(function ($comanda) use ($porcentajeGlobal) {
                    $subtotal = (float) $comanda->subtotal;
                    $propina = (float) $comanda->propina;
                    $porcentajeActual = $subtotal > 0 ? round(($propina * 100) / $subtotal, 2) : $porcentajeGlobal;

                    return [
                        'id' => $comanda->id,
                        'numero_comanda' => $comanda->numero_comanda,
                        'mesa_id' => $comanda->mesa_id,
                        'mesa_nombre' => optional($comanda->mesa)->nombre,
                        'garzon' => optional($comanda->garzon)->nombre_completo,
                        'comensales' => $comanda->comensales ?? 0,
                        'cantidad_items' => $comanda->detalles->sum('cantidad'),
                        'subtotal' => (float) $comanda->subtotal,
                        'propina' => (float) $comanda->propina,
                        'total' => (float) $comanda->total,
                        'incluye_propina' => (bool) $comanda->incluye_propina,
                        'porcentaje_propina' => $porcentajeActual,
                        'fecha_apertura' => optional($comanda->fecha_apertura)->format('d/m/Y H:i'),
                        'tiempo' => optional($comanda->updated_at)->diffForHumans(),
                    ];
                });

            return response()->json([
                'success' => true,
                'comandas' => $comandas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener comandas pendientes: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function obtenerInfoCajaCerrarComandas()
    {
        try {
            $caja = Caja::cajaAbiertaUsuario(Auth::id(), self::TIPO_CAJA_MODULO_COMANDAS);

            if (!$caja) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'No tienes una caja RESTAURANT abierta'
                ], 404);
            }

            $ventas = Venta::where('caja_id', $caja->id)->get();

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
                    'tipo_caja' => $caja->tipo_caja,
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

    public function cerrarCajaCerrarComandas(CierreCajaRequest $request)
    {
        try {
            $caja = Caja::cajaAbiertaUsuario(Auth::id(), self::TIPO_CAJA_MODULO_COMANDAS);

            if (!$caja) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'No tienes una caja RESTAURANT abierta'
                ], 404);
            }

            $ventas = Venta::where('caja_id', $caja->id)->get();
            $totalVentas = $ventas->sum('total');
            $montoEsperado = $caja->monto_inicial + $totalVentas;
            $montoFinalDeclarado = $request->monto_final_declarado;
            $diferencia = $montoFinalDeclarado - $montoEsperado;

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
                'message' => 'Caja RESTAURANT cerrada correctamente',
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

    public function cerrarComanda(Request $request, $comandaId)
    {
        $request->validate([
            'forma_pago' => 'required|string|in:EFECTIVO,TARJETA_DEBITO,TARJETA_CREDITO,TRANSFERENCIA,CHEQUE,MIXTO',
            'incluye_propina' => 'nullable|boolean',
            'porcentaje_propina' => 'nullable|numeric|min:0|max:100',
            'formas_pago_desglose' => 'nullable|array',
            'formas_pago_desglose.*.forma' => 'required_with:formas_pago_desglose|string|in:EFECTIVO,TARJETA_DEBITO,TARJETA_CREDITO,TRANSFERENCIA,CHEQUE',
            'formas_pago_desglose.*.monto' => 'required_with:formas_pago_desglose|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {
            $comanda = Comanda::with(['detalles.producto', 'mesa'])->findOrFail($comandaId);
            $comanda->load('detalles.receta');

            if ($comanda->estado !== 'PENDIENTE DE PAGO') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cerrar comandas en estado PENDIENTE DE PAGO',
                ], 400);
            }

            if ($comanda->detalles->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La comanda no tiene productos para cerrar',
                ], 400);
            }

            $cajaAbierta = Caja::cajaAbiertaUsuario(Auth::id(), self::TIPO_CAJA_MODULO_COMANDAS);
            if (!$cajaAbierta) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes una caja abierta. Debes abrir caja antes de cerrar comandas.',
                ], 400);
            }

            $incluyePropina = $request->has('incluye_propina')
                ? filter_var($request->incluye_propina, FILTER_VALIDATE_BOOLEAN)
                : (bool) $comanda->incluye_propina;
            $porcentajePropina = $request->filled('porcentaje_propina')
                ? (float) $request->porcentaje_propina
                : $this->obtenerPorcentajePropinaGlobal();

            $totales = $this->calcularTotalesComanda($comanda, $incluyePropina, $porcentajePropina);
            $totalComanda = (float) $totales['total'];

            $comanda->update([
                'subtotal' => $totales['subtotal'],
                'impuestos' => $totales['impuestos'],
                'propina' => $totales['propina'],
                'total' => $totales['total'],
                'incluye_propina' => $totales['incluye_propina'],
            ]);

            if ($request->forma_pago === 'MIXTO') {
                $desglose = $request->formas_pago_desglose ?? [];
                if (empty($desglose)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes ingresar el desglose de pagos para forma MIXTO',
                    ], 400);
                }

                $suma = collect($desglose)->sum(function ($item) {
                    return (float) ($item['monto'] ?? 0);
                });

                if (round($suma, 2) !== round($totalComanda, 2)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La suma del pago mixto debe coincidir con el total de la comanda',
                    ], 400);
                }
            }

            $venta = Venta::create([
                'total' => $totalComanda,
                'total_descuentos' => 0,
                'user_id' => Auth::id(),
                'caja_id' => $cajaAbierta->id,
                'forma_pago' => $request->forma_pago,
                'estado' => 'completada',
                'fecha_venta' => now(),
            ]);

            foreach ($comanda->detalles as $detalleComanda) {
                $producto = $detalleComanda->producto;
                $receta = $detalleComanda->receta;

                $descripcionVenta = $producto->descripcion ?? $producto->nom_prod ?? null;
                $uuidVenta = $producto->uuid ?? null;

                if (($detalleComanda->tipo_item ?? 'PRODUCTO') === 'RECETA') {
                    $descripcionVenta = $receta->nombre ?? $descripcionVenta ?? 'Receta';
                    $uuidVenta = $receta->uuid ?? $uuidVenta;
                }

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_uuid' => $uuidVenta,
                    'descripcion_producto' => $descripcionVenta ?? 'Producto',
                    'cantidad' => $detalleComanda->cantidad,
                    'precio_unitario' => $detalleComanda->precio_unitario,
                    'descuento_porcentaje' => 0,
                    'subtotal_linea' => $detalleComanda->subtotal,
                ]);

                $this->registrarMovimientoCierreComanda($detalleComanda, $producto, (float) $detalleComanda->cantidad, $venta, $comanda);
            }

            if ($request->forma_pago === 'MIXTO') {
                foreach ($request->formas_pago_desglose as $formaPago) {
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
                    'monto' => $totalComanda,
                ]);
            }

            $estadoAnterior = $comanda->estado;
            $comanda->estado = 'CERRADA';
            $comanda->fecha_cierre = now();
            $comanda->save();

            HistorialEstadoComanda::create([
                'comanda_id' => $comanda->id,
                'mesa_id' => $comanda->mesa_id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $comanda->estado,
                'accion' => 'CERRAR_COMANDA',
                'usuario_id' => Auth::id(),
                'fecha_cambio' => now(),
                'observacion' => 'Cierre y pago de comanda desde caja',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Comanda cerrada correctamente. Mesa liberada para nuevo uso.',
                'venta_id' => $venta->id,
                'numero_venta' => $venta->numero_venta,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar comanda: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function obtenerMesas()
    {
        try {
            $mesas = Mesa::where('activa', true)
                ->with(['comandaAbierta' => function($query) {
                    $query->with(['detalles.producto', 'garzon']);
                }])
                ->orderBy('orden')
                ->get()
                ->map(function($mesa) {
                    $estadoMesa = 'LIBRE';
                    if ($mesa->comandaAbierta) {
                        $estadoMesa = $mesa->comandaAbierta->estado === 'PENDIENTE DE PAGO'
                            ? 'PENDIENTE DE PAGO'
                            : 'OCUPADA';
                    }

                    return [
                        'id' => $mesa->id,
                        'nombre' => $mesa->nombre,
                        'capacidad' => $mesa->capacidad,
                        'estado' => $estadoMesa,
                        'comanda' => $mesa->comandaAbierta ? [
                            'id' => $mesa->comandaAbierta->id,
                            'numero_comanda' => $mesa->comandaAbierta->numero_comanda,
                            'total' => number_format($mesa->comandaAbierta->total, 0, ',', '.'),
                            'cantidad_items' => $mesa->comandaAbierta->detalles->sum('cantidad'),
                            'comensales' => $mesa->comandaAbierta->comensales ?? 0,
                            'mesero' => $mesa->comandaAbierta->garzon ? $mesa->comandaAbierta->garzon->nombre . ' ' . $mesa->comandaAbierta->garzon->apellido : 'Sin asignar',
                            'tiempo' => $mesa->comandaAbierta->updated_at->diffForHumans()
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'mesas' => $mesas
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las mesas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verComanda($mesaId)
    {
        try {
            $mesa = Mesa::with(['comandaAbierta.detalles.producto', 'comandaAbierta.detalles.receta', 'comandaAbierta.user', 'comandaAbierta.garzon'])
                ->findOrFail($mesaId);

            $comanda = $mesa->comandaAbierta;

            if (!$comanda) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta mesa no tiene una comanda activa'
                ], 404);
            }
            $detalles = $comanda->detalles->map(function($detalle) {
                $esReceta = ($detalle->tipo_item ?? 'PRODUCTO') === 'RECETA';
                $uuid = $esReceta
                    ? (optional($detalle->receta)->uuid ? ('RECETA-' . optional($detalle->receta)->uuid) : null)
                    : (optional($detalle->producto)->uuid ?? null);
                $descripcion = $esReceta
                    ? (optional($detalle->receta)->nombre ?? optional($detalle->producto)->descripcion ?? '')
                    : (optional($detalle->producto)->descripcion ?? optional($detalle->producto)->nom_prod ?? '');
                $codigo = $esReceta
                    ? (optional($detalle->receta)->codigo ?? '')
                    : (optional($detalle->producto)->codigo ?? optional($detalle->producto)->cod_prod ?? '');

                return [
                    'id' => $detalle->id,
                    'producto_id' => $detalle->producto_id,
                    'uuid' => $uuid,
                    'producto' => $descripcion,
                    'codigo' => $codigo,
                    'origen' => $detalle->tipo_item ?? 'PRODUCTO',
                    'receta_id' => $detalle->receta_id,
                    'receta_uuid' => optional($detalle->receta)->uuid,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal,
                    'estado' => $detalle->estado,
                    'observaciones' => $detalle->observaciones
                ];
            });

            // Obtener hora de apertura en formato HH:MM
            $horaApertura = $comanda->fecha_apertura ? 
                \Carbon\Carbon::parse($comanda->fecha_apertura)->format('H:i') : 
                date('H:i');

            return response()->json([
                'success' => true,
                'comanda' => [
                    'id' => $comanda->id,
                    'numero_comanda' => $comanda->numero_comanda,
                    'mesa' => $mesa->nombre,
                    'mesero' => $comanda->user->name_complete ?? '',
                    'garzon_id' => $comanda->garzon_id,
                    'garzon_nombre' => $comanda->garzon ? $comanda->garzon->nombre_completo : '',
                    'comensales' => $comanda->comensales ?? $mesa->capacidad,
                    'subtotal' => $comanda->subtotal,
                    'impuestos' => $comanda->impuestos,
                    'propina' => $comanda->propina ?? 0,
                    'incluye_propina' => $comanda->incluye_propina ?? false,
                    'total' => $comanda->total,
                    'fecha_apertura' => $comanda->fecha_apertura->format('d/m/Y H:i'),
                    'hora_apertura' => $horaApertura,
                    'detalles' => $detalles
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la comanda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarComensales(Request $request, $comandaId)
    {
        try {
            $comanda = Comanda::findOrFail($comandaId);
            $comanda->comensales = $request->comensales;
            $comanda->save();

            return response()->json([
                'success' => true,
                'message' => 'Comensales actualizado correctamente',
                'comensales' => $comanda->comensales
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar comensales: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerProductos()
    {
        try {
            $query = trim((string) request('q', ''));

            $productosQuery = Producto::where('estado', 'Activo')
                ->where(function ($query) {
                    $query->whereNull('categoria_id')
                        ->orWhereHas('categoria', function ($categoriaQuery) {
                            $categoriaQuery->whereRaw("UPPER(TRIM(descripcion_categoria)) <> ?", ['INSUMOS']);
                        });
                })
                ->select('id', 'uuid', 'codigo', 'descripcion', 'precio_venta', 'stock');

            $recetasQuery = Receta::where('estado', 'Activo')
                ->select(
                    'id',
                    'uuid',
                    'codigo',
                    DB::raw('nombre as descripcion'),
                    'precio_venta'
                );

            if ($query !== '') {
                $queryUpper = mb_strtoupper($query, 'UTF-8');

                $productosPorCodigo = (clone $productosQuery)
                    ->whereRaw('UPPER(TRIM(codigo)) = ?', [$queryUpper])
                    ->get()
                    ->map(function ($producto) {
                        return [
                            'id' => $producto->id,
                            'uuid' => $producto->uuid,
                            'codigo' => $producto->codigo,
                            'descripcion' => $producto->descripcion,
                            'precio_venta' => (float) $producto->precio_venta,
                            'stock' => (float) $producto->stock,
                            'origen' => 'PRODUCTO',
                        ];
                    });
                $productosPorCodigo = collect($productosPorCodigo->all());

                if ($productosPorCodigo->isNotEmpty()) {
                    return response()->json([
                        'success' => true,
                        'productos' => $productosPorCodigo->values(),
                    ], 200);
                }

                $recetasPorCodigo = (clone $recetasQuery)
                    ->whereRaw('UPPER(TRIM(codigo)) = ?', [$queryUpper])
                    ->get()
                    ->map(function ($receta) {
                        return [
                            'id' => $receta->id,
                            'uuid' => 'RECETA-' . $receta->uuid,
                            'codigo' => $receta->codigo,
                            'descripcion' => $receta->descripcion,
                            'precio_venta' => (float) $receta->precio_venta,
                            'stock' => 0,
                            'origen' => 'RECETA',
                        ];
                    })
                    ->values();
                $recetasPorCodigo = collect($recetasPorCodigo->all());

                if ($recetasPorCodigo->isNotEmpty()) {
                    return response()->json([
                        'success' => true,
                        'productos' => $recetasPorCodigo,
                    ], 200);
                }

                $productosTexto = (clone $productosQuery)
                    ->where(function ($q) use ($query) {
                        $q->where('descripcion', 'like', "%{$query}%")
                            ->orWhere('codigo', 'like', "%{$query}%");
                    })
                    ->get()
                    ->map(function ($producto) {
                        return [
                            'id' => $producto->id,
                            'uuid' => $producto->uuid,
                            'codigo' => $producto->codigo,
                            'descripcion' => $producto->descripcion,
                            'precio_venta' => (float) $producto->precio_venta,
                            'stock' => (float) $producto->stock,
                            'origen' => 'PRODUCTO',
                        ];
                    });
                $productosTexto = collect($productosTexto->all());

                $recetasTexto = (clone $recetasQuery)
                    ->where(function ($q) use ($query) {
                        $q->where('nombre', 'like', "%{$query}%")
                            ->orWhere('codigo', 'like', "%{$query}%");
                    })
                    ->get()
                    ->map(function ($receta) {
                        return [
                            'id' => $receta->id,
                            'uuid' => 'RECETA-' . $receta->uuid,
                            'codigo' => $receta->codigo,
                            'descripcion' => $receta->descripcion,
                            'precio_venta' => (float) $receta->precio_venta,
                            'stock' => 0,
                            'origen' => 'RECETA',
                        ];
                    });
                $recetasTexto = collect($recetasTexto->all());

                $resultadoBusqueda = $productosTexto
                    ->merge($recetasTexto)
                    ->unique('uuid')
                    ->values();

                return response()->json([
                    'success' => true,
                    'productos' => $resultadoBusqueda,
                ], 200);
            }

            $productos = $productosQuery->get()->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'uuid' => $producto->uuid,
                    'codigo' => $producto->codigo,
                    'descripcion' => $producto->descripcion,
                    'precio_venta' => (float) $producto->precio_venta,
                    'stock' => (float) $producto->stock,
                    'origen' => 'PRODUCTO',
                ];
            });
            $productos = collect($productos->all());

            $recetas = $recetasQuery->get()->map(function ($receta) {
                return [
                    'id' => $receta->id,
                    'uuid' => 'RECETA-' . $receta->uuid,
                    'codigo' => $receta->codigo,
                    'descripcion' => $receta->descripcion,
                    'precio_venta' => (float) $receta->precio_venta,
                    'stock' => 0,
                    'origen' => 'RECETA',
                ];
            });
            $recetas = collect($recetas->all());

            $resultado = $productos
                ->merge($recetas)
                ->unique('uuid')
                ->values();

            return response()->json([
                'success' => true,
                'productos' => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarStockReceta(Request $request)
    {
        try {
            $uuidRecibido = (string) $request->input('uuid', '');
            $cantidad = (float) $request->input('cantidad', 1);

            if ($cantidad <= 0) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'Cantidad inválida para verificar stock de receta',
                ], 400);
            }

            $recetaUuid = str_starts_with($uuidRecibido, 'RECETA-')
                ? substr($uuidRecibido, 7)
                : $uuidRecibido;

            $receta = Receta::with('ingredientes.producto')->where('uuid', $recetaUuid)->first();

            if (!$receta) {
                return response()->json([
                    'status' => 'ERROR',
                    'code' => 'RECIPE_NOT_FOUND',
                    'message' => 'No se encontró la receta solicitada',
                ], 200);
            }

            $stockNegativo = Globales::where('nom_var', 'STOCK_NEGATIVO')->value('valor_var');
            $permitirStockNegativo = ($stockNegativo == '1');

            $faltantes = [];

            foreach ($receta->ingredientes as $ingrediente) {
                $producto = $ingrediente->producto;

                if (!$producto) {
                    $faltantes[] = [
                        'descripcion' => 'Insumo no disponible en receta',
                    ];
                    continue;
                }

                if ($permitirStockNegativo) {
                    continue;
                }

                $requerido = (float) $ingrediente->cantidad * $cantidad;
                $stockDisponible = (float) ($producto->stock ?? 0);

                if ($stockDisponible < $requerido) {
                    $faltantes[] = [
                        'descripcion' => $producto->descripcion ?: ($producto->codigo ?: 'Insumo sin nombre'),
                    ];
                }
            }

            if (!empty($faltantes)) {
                return response()->json([
                    'status' => 'ERROR',
                    'code' => 'RECIPE_INSUFFICIENT_STOCK',
                    'message' => 'Faltan insumos para preparar la receta',
                    'recipe' => [
                        'uuid' => $receta->uuid,
                        'codigo' => $receta->codigo,
                        'nombre' => $receta->nombre,
                    ],
                    'items' => collect($faltantes)->unique('descripcion')->values(),
                ], 200);
            }

            return response()->json([
                'status' => 'OK',
                'available' => true,
                'tipo' => 'receta',
                'recipe' => [
                    'uuid' => $receta->uuid,
                    'codigo' => $receta->codigo,
                    'nombre' => $receta->nombre,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Error al verificar stock de receta: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function obtenerGarzones()
    {
        try {
            $garzones = Garzon::where('estado', 'Activo')
                ->select('id', 'nombre', 'apellido')
                ->get()
                ->map(function($garzon) {
                    return [
                        'id' => $garzon->id,
                        'nombre_completo' => $garzon->nombre . ' ' . $garzon->apellido
                    ];
                });

            return response()->json([
                'success' => true,
                'garzones' => $garzones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener garzones: ' . $e->getMessage()
            ], 500);
        }
    }

    public function crearComanda(Request $request)
    {
        try {
            DB::beginTransaction();

            // Verificar si la mesa ya tiene una comanda abierta
            $comandaExistente = Comanda::where('mesa_id', $request->mesa_id)
                ->whereIn('estado', ['ABIERTA', 'EN CONSUMO', 'PENDIENTE DE PAGO'])
                ->first();

            if ($comandaExistente) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'La mesa ya tiene una comanda abierta',
                    'comanda_id' => $comandaExistente->id
                ], 400);
            }

            // Generar número de comanda
            $ultimaComanda = Comanda::max('id');
            $numeroComanda = 'CMD-' . str_pad($ultimaComanda + 1, 6, '0', STR_PAD_LEFT);

            $comanda = Comanda::create([
                'mesa_id' => $request->mesa_id,
                'user_id' => auth()->id(),
                'garzon_id' => $request->garzon_id,
                'numero_comanda' => $numeroComanda,
                'estado' => 'ABIERTA',
                'comensales' => $request->comensales ?? 0,
                'total' => 0,
                'subtotal' => 0,
                'impuestos' => 0,
                'propina' => 0,
                'incluye_propina' => filter_var($request->incluye_propina, FILTER_VALIDATE_BOOLEAN),
                'fecha_apertura' => now()
            ]);

            HistorialEstadoComanda::create([
                'comanda_id' => $comanda->id,
                'mesa_id' => $comanda->mesa_id,
                'estado_anterior' => null,
                'estado_nuevo' => $comanda->estado,
                'accion' => 'CREAR_COMANDA',
                'usuario_id' => auth()->id(),
                'fecha_cambio' => now(),
                'observacion' => 'Apertura de comanda',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Comanda creada correctamente',
                'comanda' => $comanda
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear comanda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarComanda(Request $request, $comandaId)
    {
        try {
            DB::beginTransaction();

            $comanda = Comanda::findOrFail($comandaId);
            $estadoAnterior = $comanda->estado;
            
            // Actualizar solo los campos permitidos
            $comanda->garzon_id = $request->garzon_id ?? $comanda->garzon_id;
            $comanda->comensales = $request->comensales ?? $comanda->comensales;
            $comanda->incluye_propina = filter_var($request->incluye_propina, FILTER_VALIDATE_BOOLEAN);
            $comanda->estado = 'EN CONSUMO';
            $comanda->save();

            if ($estadoAnterior !== $comanda->estado) {
                HistorialEstadoComanda::create([
                    'comanda_id' => $comanda->id,
                    'mesa_id' => $comanda->mesa_id,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $comanda->estado,
                    'accion' => 'ACTUALIZAR_COMANDA',
                    'usuario_id' => auth()->id(),
                    'fecha_cambio' => now(),
                    'observacion' => 'Cambio de estado por actualización de comanda',
                ]);
            }

            // Recalcular totales
            $this->recalcularTotales($comandaId);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Comanda actualizada correctamente',
                'comanda' => $comanda
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar comanda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cambiarMesaComanda(Request $request, $comandaId)
    {
        $request->validate([
            'mesa_id_destino' => 'required|integer|exists:mesas,id',
        ]);

        try {
            DB::beginTransaction();

            $comanda = Comanda::with('mesa')->findOrFail($comandaId);

            if (!in_array($comanda->estado, ['ABIERTA', 'EN CONSUMO', 'PENDIENTE DE PAGO'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cambiar mesas de comandas activas',
                ], 400);
            }

            $mesaDestinoId = (int) $request->mesa_id_destino;
            $mesaOrigen = $comanda->mesa;
            $mesaOrigenId = (int) $comanda->mesa_id;

            if ($mesaDestinoId === $mesaOrigenId) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'La mesa destino debe ser distinta a la mesa actual',
                ], 400);
            }

            $mesaDestino = Mesa::where('id', $mesaDestinoId)
                ->where('activa', true)
                ->first();

            if (!$mesaDestino) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'La mesa destino no existe o está inactiva',
                ], 404);
            }

            $comandaEnDestino = Comanda::where('mesa_id', $mesaDestinoId)
                ->whereIn('estado', ['ABIERTA', 'EN CONSUMO', 'PENDIENTE DE PAGO'])
                ->where('id', '<>', $comanda->id)
                ->exists();

            if ($comandaEnDestino) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'La mesa destino ya tiene una comanda activa',
                ], 400);
            }

            if ((int) $comanda->comensales > (int) $mesaDestino->capacidad) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'La capacidad de la mesa destino es menor a los comensales actuales',
                ], 400);
            }

            $comanda->mesa_id = $mesaDestino->id;
            $comanda->save();

            HistorialEstadoComanda::create([
                'comanda_id' => $comanda->id,
                'mesa_id' => $mesaDestino->id,
                'estado_anterior' => $comanda->estado,
                'estado_nuevo' => $comanda->estado,
                'accion' => 'CAMBIAR_MESA',
                'usuario_id' => auth()->id(),
                'fecha_cambio' => now(),
                'observacion' => 'Cambio de ' . ($mesaOrigen->nombre ?? ('Mesa ' . $mesaOrigenId)) . ' a ' . $mesaDestino->nombre,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Comanda cambiada a ' . $mesaDestino->nombre,
                'mesa_origen' => [
                    'id' => $mesaOrigenId,
                    'nombre' => $mesaOrigen->nombre ?? ('Mesa ' . $mesaOrigenId),
                ],
                'mesa_destino' => [
                    'id' => $mesaDestino->id,
                    'nombre' => $mesaDestino->nombre,
                    'capacidad' => $mesaDestino->capacidad,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar la mesa de la comanda: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function solicitarCuenta($comandaId)
    {
        try {
            DB::beginTransaction();

            $comanda = Comanda::findOrFail($comandaId);

            if (!$comanda->detalles()->exists()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede solicitar cuenta: la comanda no tiene productos'
                ], 400);
            }

            $estadoAnterior = $comanda->estado;
            $comanda->estado = 'PENDIENTE DE PAGO';
            $comanda->save();

            if ($estadoAnterior !== $comanda->estado) {
                HistorialEstadoComanda::create([
                    'comanda_id' => $comanda->id,
                    'mesa_id' => $comanda->mesa_id,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $comanda->estado,
                    'accion' => 'SOLICITAR_CUENTA',
                    'usuario_id' => auth()->id(),
                    'fecha_cambio' => now(),
                    'observacion' => 'Solicitud de cuenta desde modal de comandas',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cuenta solicitada correctamente',
                'estado' => $comanda->estado
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al solicitar cuenta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sincronizarProductos(Request $request, $comandaId)
    {
        try {
            DB::beginTransaction();

            $comanda = Comanda::findOrFail($comandaId);
            
            // Eliminar todos los productos actuales de la comanda
            DetalleComanda::where('comanda_id', $comandaId)->delete();

            // Agregar los nuevos productos que vienen del frontend
            $productos = $request->productos ?? [];
            
            foreach ($productos as $productoData) {
                $item = $this->obtenerItemDesdePayloadComanda((array) $productoData);
                $cantidad = (float) $productoData['cantidad'];
                $precioUnitario = $this->resolverPrecioUnitarioDesdeItem($item, $cantidad);
                
                DetalleComanda::create([
                    'comanda_id' => $comandaId,
                    'producto_id' => $item['producto']?->id,
                    'tipo_item' => $item['tipo_item'] ?? 'PRODUCTO',
                    'receta_id' => $item['receta_id'] ?? null,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $precioUnitario * $cantidad,
                    'estado' => 'PENDIENTE',
                    'observaciones' => $productoData['observaciones'] ?? null
                ]);
            }

            // Recalcular totales de la comanda
            $this->recalcularTotales($comandaId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Productos sincronizados correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function agregarProducto(Request $request)
    {
        try {
            DB::beginTransaction();

            // Aceptar tanto producto_id como producto_uuid para compatibilidad
            $item = $this->obtenerItemDesdePayloadComanda($request->all());
            $cantidad = (float) $request->cantidad;
            $precioUnitario = $this->resolverPrecioUnitarioDesdeItem($item, $cantidad);
            
            $detalle = DetalleComanda::create([
                'comanda_id' => $request->comanda_id,
                'producto_id' => $item['producto']?->id,
                'tipo_item' => $item['tipo_item'] ?? 'PRODUCTO',
                'receta_id' => $item['receta_id'] ?? null,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $precioUnitario * $cantidad,
                'estado' => 'PENDIENTE',
                'observaciones' => $request->observaciones ?? null
            ]);

            // Actualizar totales de la comanda
            $this->recalcularTotales($request->comanda_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado correctamente',
                'detalle' => $detalle
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarProducto(Request $request, $detalleId)
    {
        try {
            DB::beginTransaction();

            $detalle = DetalleComanda::findOrFail($detalleId);
            $cantidad = (float) $request->cantidad;

            if (($detalle->tipo_item ?? 'PRODUCTO') === 'RECETA') {
                $receta = !empty($detalle->receta_id) ? Receta::find($detalle->receta_id) : null;
                $precioUnitario = $receta ? (float) $receta->precio_venta : (float) $detalle->precio_unitario;
            } else {
                $producto = Producto::findOrFail($detalle->producto_id);
                $precioUnitario = $this->resolverPrecioUnitarioComanda($producto, $cantidad);
            }

            $detalle->cantidad = $cantidad;
            $detalle->precio_unitario = $precioUnitario;
            $detalle->subtotal = $precioUnitario * $cantidad;
            $detalle->save();

            // Actualizar totales de la comanda
            $this->recalcularTotales($detalle->comanda_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cantidad actualizada correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminarProducto($detalleId)
    {
        try {
            DB::beginTransaction();

            $detalle = DetalleComanda::findOrFail($detalleId);
            $comandaId = $detalle->comanda_id;
            $detalle->delete();

            // Actualizar totales de la comanda
            $this->recalcularTotales($comandaId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    private function recalcularTotales($comandaId)
    {
        $comanda = Comanda::findOrFail($comandaId);
        $totales = $this->calcularTotalesComanda($comanda);

        $comanda->update([
            'subtotal' => $totales['subtotal'],
            'impuestos' => $totales['impuestos'],
            'propina' => $totales['propina'],
            'total' => $totales['total']
        ]);
    }

    private function registrarMovimientoCierreComanda(DetalleComanda $detalleComanda, ?Producto $producto, float $cantidad, Venta $venta, Comanda $comanda): void
    {
        if ($cantidad <= 0) {
            return;
        }

        $observacionBase = 'Cierre comanda ' . $comanda->numero_comanda;

        $promocion = null;
        if (!empty($producto->uuid)) {
            $promocion = Promocion::where('uuid', $producto->uuid)->first();
        }

        if ($promocion) {
            $detallesPromocion = PromocionDetalle::where('promo_id', $promocion->id)
                ->with('producto')
                ->get();

            foreach ($detallesPromocion as $detallePromo) {
                $productoPromo = $detallePromo->producto;

                if (!$productoPromo) {
                    continue;
                }

                $cantidadTotal = (float) $detallePromo->cantidad * $cantidad;

                if ($productoPromo->tipo === 'P') {
                    $productoPromo->stock -= $cantidadTotal;
                    $productoPromo->save();
                }

                HistorialMovimientos::registrarMovimiento([
                    'producto_id' => $productoPromo->id,
                    'cantidad' => $cantidadTotal,
                    'stock' => $productoPromo->tipo === 'P' ? $productoPromo->stock : null,
                    'tipo_mov' => 'VENTA',
                    'fecha' => $venta->fecha_venta,
                    'num_doc' => (string) $venta->id,
                    'obs' => $observacionBase . ' - Producto promoción: ' . ($promocion->nombre ?? 'PROMO'),
                ]);
            }

            return;
        }

        if (($detalleComanda->tipo_item ?? 'PRODUCTO') === 'RECETA') {
            $receta = null;

            if (!empty($detalleComanda->receta_id)) {
                $receta = Receta::with('ingredientes.producto')->find($detalleComanda->receta_id);
            }

            if (!$receta && $producto && !empty($producto->codigo)) {
                $codigoNormalizado = mb_strtoupper(trim((string) $producto->codigo), 'UTF-8');
                $receta = Receta::with('ingredientes.producto')
                    ->whereRaw('UPPER(TRIM(codigo)) = ?', [$codigoNormalizado])
                    ->first();
            }

            if ($receta) {
                foreach ($receta->ingredientes as $ingrediente) {
                    $productoIngrediente = $ingrediente->producto;

                    if (!$productoIngrediente) {
                        continue;
                    }

                    $cantidadTotal = (float) $ingrediente->cantidad * $cantidad;
                    $controlaStock = in_array($productoIngrediente->tipo, ['P', 'I'], true);

                    if ($controlaStock) {
                        $productoIngrediente->stock -= $cantidadTotal;
                        $productoIngrediente->save();
                    }

                    HistorialMovimientos::registrarMovimiento([
                        'producto_id' => $productoIngrediente->id,
                        'cantidad' => $cantidadTotal,
                        'stock' => $controlaStock ? $productoIngrediente->stock : null,
                        'tipo_mov' => 'VENTA',
                        'fecha' => $venta->fecha_venta,
                        'num_doc' => (string) $venta->id,
                        'obs' => $observacionBase . ' - Ingrediente receta: ' . ($receta->nombre ?? 'RECETA'),
                    ]);
                }

                return;
            }
        }

        if (!$producto) {
            return;
        }

        if ($producto->tipo === 'P') {
            $producto->stock -= $cantidad;
            $producto->save();
        }

        HistorialMovimientos::registrarMovimiento([
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'stock' => $producto->tipo === 'P' ? $producto->stock : null,
            'tipo_mov' => 'VENTA',
            'fecha' => $venta->fecha_venta,
            'num_doc' => (string) $venta->id,
            'obs' => $observacionBase,
        ]);
    }

    private function generarLayoutDefaultMesas(): array
    {
        $mesas = Mesa::where('activa', true)
            ->orderBy('orden')
            ->get(['id', 'nombre', 'capacidad']);

        $layout = [
            'canvas' => [
                'width' => 1200,
                'height' => 700,
                'grid' => 20,
            ],
            'mesas' => [],
            'updated_at' => now()->toDateTimeString(),
            'updated_by' => optional(auth()->user())->name ?? 'SISTEMA',
        ];

        $columna = 0;
        $fila = 0;
        $maxColumnas = 6;
        $separacionX = 180;
        $separacionY = 140;

        foreach ($mesas as $mesa) {
            $layout['mesas'][] = [
                'mesa_id' => $mesa->id,
                'nombre' => $mesa->nombre,
                'capacidad' => $mesa->capacidad,
                'x' => 40 + ($columna * $separacionX),
                'y' => 40 + ($fila * $separacionY),
                'width' => 130,
                'height' => 90,
                'shape' => 'rect',
                'rotation' => 0,
            ];

            $columna++;
            if ($columna >= $maxColumnas) {
                $columna = 0;
                $fila++;
            }
        }

        return $layout;
    }

    public function obtenerLayoutMesas()
    {
        try {
            $disk = Storage::disk('local');

            if (!$disk->exists(self::LAYOUT_FILE)) {
                $layoutDefault = $this->generarLayoutDefaultMesas();
                $disk->put(self::LAYOUT_FILE, json_encode($layoutDefault, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            $contenido = $disk->get(self::LAYOUT_FILE);
            $layout = json_decode($contenido, true);

            if (!is_array($layout)) {
                $layout = $this->generarLayoutDefaultMesas();
                $disk->put(self::LAYOUT_FILE, json_encode($layout, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return response()->json([
                'success' => true,
                'layout' => $layout,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar layout de mesas: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function guardarLayoutMesas(Request $request)
    {
        $layout = $request->input('layout');

        if (!is_array($layout)) {
            return response()->json([
                'success' => false,
                'message' => 'El layout debe ser un objeto JSON válido',
            ], 422);
        }

        if (!isset($layout['mesas']) || !is_array($layout['mesas'])) {
            return response()->json([
                'success' => false,
                'message' => 'El layout debe contener la lista de mesas',
            ], 422);
        }

        try {
            $layout['updated_at'] = now()->toDateTimeString();
            $layout['updated_by'] = optional(auth()->user())->name ?? 'SISTEMA';

            Storage::disk('local')->put(
                self::LAYOUT_FILE,
                json_encode($layout, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            return response()->json([
                'success' => true,
                'message' => 'Layout guardado correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar layout: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function imprimirComanda($comandaId)
    {
        try {
            $comanda = Comanda::with(['mesa', 'detalles.producto', 'detalles.receta', 'user', 'garzon'])
                ->findOrFail($comandaId);

            $corporateData = CorporateData::pluck('description_item', 'item')->toArray();
            $porcentajePropinaGlobal = $this->obtenerPorcentajePropinaGlobal();

            $pdf = Pdf::loadView('restaurant.ticket_comanda', compact('comanda', 'corporateData', 'porcentajePropinaGlobal'));
            $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

            return $pdf->stream('comanda-' . ($comanda->numero_comanda ?? str_pad($comanda->id, 4, '0', STR_PAD_LEFT)) . '.pdf');
        } catch (\Exception $e) {
            abort(500, 'Error al generar PDF de comanda: ' . $e->getMessage());
        }
    }

    public function imprimirTicketPagoComanda($comandaId, $ventaId)
    {
        try {
            $comanda = Comanda::with(['mesa', 'detalles.producto', 'detalles.receta', 'user', 'garzon'])
                ->findOrFail($comandaId);

            $venta = Venta::with(['formasPago', 'usuario'])
                ->findOrFail($ventaId);

            $corporateData = CorporateData::pluck('description_item', 'item')->toArray();
            $porcentajePropinaGlobal = $this->obtenerPorcentajePropinaGlobal();
            $esTicketPago = true;

            $pdf = Pdf::loadView('restaurant.ticket_comanda', compact('comanda', 'corporateData', 'venta', 'esTicketPago', 'porcentajePropinaGlobal'));
            $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

            return $pdf->stream('ticket-pago-comanda-' . ($comanda->numero_comanda ?? str_pad($comanda->id, 4, '0', STR_PAD_LEFT)) . '.pdf');
        } catch (\Exception $e) {
            abort(500, 'Error al generar ticket de pago de comanda: ' . $e->getMessage());
        }
    }
}
