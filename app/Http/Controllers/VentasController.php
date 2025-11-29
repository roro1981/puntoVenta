<?php

namespace App\Http\Controllers;

use App\Models\Borrador;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\FormaPagoVenta;
use App\Models\HistorialMovimientos;
use App\Models\PromocionDetalle;
use App\Models\CorporateData;
use App\Models\Caja;
use App\Models\User;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\AperturaCajaRequest;
use App\Http\Requests\CierreCajaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class VentasController extends Controller
{
    public function indexVentas()
    {
        // Verificar si el usuario tiene una caja abierta
        $cajaAbierta = Caja::cajaAbiertaUsuario(Auth::id());
        
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
            // Verificar que no tenga una caja abierta
            $cajaExistente = Caja::cajaAbiertaUsuario(Auth::id());
            
            if ($cajaExistente) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'Ya tienes una caja abierta'
                ], 400);
            }

            // Crear nueva caja
            $caja = Caja::create([
                'user_id' => Auth::id(),
                'fecha_apertura' => now(),
                'monto_inicial' => $request->monto_inicial,
                'observaciones' => $request->observaciones
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
        
        if (!\Hash::check($request->password, $user->password)) {
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
            $caja = Caja::cajaAbiertaUsuario(Auth::id());
            
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
            $caja = Caja::cajaAbiertaUsuario(Auth::id());
            
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
                'observaciones' => $caja->observaciones . "\n\nCierre: " . ($request->observaciones ?? '')
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
            $products = Producto::select('uuid', 'descripcion', 'precio_venta')
                ->where('codigo', $query)
                ->get();

            $promotions = Promocion::select('uuid', 'nombre as descripcion', 'precio_venta')
                ->where('codigo', $query)
                ->get();
        } else {
            $products = Producto::select('uuid', 'descripcion', 'precio_venta')
                ->where(function($q) use ($query) {
                    $q->where('descripcion', 'like', "%$query%")
                      ->orWhere('codigo', 'like', "%$query%");
                })->get();

            $promotions = Promocion::select('uuid', 'nombre as descripcion', 'precio_venta')
                ->where(function($q) use ($query) {
                    $q->where('nombre', 'like', "%$query%")
                      ->orWhere('codigo', 'like', "%$query%");
                })->get();
        }

        // Merge collections so the response format is consistent
        $results = $products->merge($promotions);

        return response()->json($results);
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

                // Solo validar stock para productos tipo P
                $ok = ($stock >= $requiredTotal);
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
            $cajaAbierta = Caja::cajaAbiertaUsuario(Auth::id());
            
            if (!$cajaAbierta) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'No tienes una caja abierta. Debes abrir caja antes de realizar ventas.'
                ], 400);
            }

            // Crear la venta
            $venta = Venta::create([
                'numero_venta' => $request->numero_venta,
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
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_uuid' => $detalle['producto_uuid'] ?? null,
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
                        // Verificar si es una promoción
                        $promocion = Promocion::where('uuid', $detalle['producto_uuid'])->first();
                        
                        if ($promocion) {
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
     * Genera el ticket de venta en formato PDF
     */
    public function generarTicketPDF($ventaId)
    {
        $venta = Venta::with(['detalles', 'usuario', 'formasPago'])->findOrFail($ventaId);
        
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

}
