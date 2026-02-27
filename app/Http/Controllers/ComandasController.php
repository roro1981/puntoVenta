<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Comanda;
use App\Models\DetalleComanda;
use App\Models\Producto;
use App\Models\Garzon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComandasController extends Controller
{
    public function index()
    {
        // Obtener todas las mesas activas con su comanda abierta si existe
        $mesas = Mesa::where('activa', true)
            ->with(['comandaAbierta.detalles.producto'])
            ->orderBy('orden')
            ->get();

        return view('restaurant.comandas', compact('mesas'));
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
                    return [
                        'id' => $mesa->id,
                        'nombre' => $mesa->nombre,
                        'capacidad' => $mesa->capacidad,
                        'estado' => $mesa->comandaAbierta ? 'OCUPADA' : 'LIBRE',
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
            $mesa = Mesa::with(['comandaAbierta.detalles.producto', 'comandaAbierta.user', 'comandaAbierta.garzon'])
                ->findOrFail($mesaId);

            if (!$mesa->comandaAbierta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta mesa no tiene una comanda abierta'
                ], 404);
            }

            $comanda = $mesa->comandaAbierta;
            $detalles = $comanda->detalles->map(function($detalle) {
                return [
                    'id' => $detalle->id,
                    'producto_id' => $detalle->producto_id,
                    'producto' => $detalle->producto->descripcion ?? $detalle->producto->nom_prod ?? '',
                    'codigo' => $detalle->producto->codigo ?? $detalle->producto->cod_prod ?? '',
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
            $productos = Producto::where('estado', 'Activo')
                ->select('id', 'uuid', 'codigo', 'descripcion', 'precio_venta', 'stock')
                ->get();

            return response()->json([
                'success' => true,
                'productos' => $productos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
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
                ->where('estado', 'ABIERTA')
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
            
            // Actualizar solo los campos permitidos
            $comanda->garzon_id = $request->garzon_id ?? $comanda->garzon_id;
            $comanda->comensales = $request->comensales ?? $comanda->comensales;
            $comanda->incluye_propina = filter_var($request->incluye_propina, FILTER_VALIDATE_BOOLEAN);
            $comanda->save();

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
                $producto = Producto::findOrFail($productoData['producto_id']);
                
                DetalleComanda::create([
                    'comanda_id' => $comandaId,
                    'producto_id' => $producto->id,
                    'cantidad' => $productoData['cantidad'],
                    'precio_unitario' => $producto->precio_venta,
                    'subtotal' => $producto->precio_venta * $productoData['cantidad'],
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
            if ($request->has('producto_id')) {
                $producto = Producto::findOrFail($request->producto_id);
            } else {
                $producto = Producto::where('uuid', $request->producto_uuid)->firstOrFail();
            }
            
            $detalle = DetalleComanda::create([
                'comanda_id' => $request->comanda_id,
                'producto_id' => $producto->id,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $producto->precio_venta,
                'subtotal' => $producto->precio_venta * $request->cantidad,
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
            $detalle->cantidad = $request->cantidad;
            $detalle->subtotal = $detalle->precio_unitario * $request->cantidad;
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
        
        // Calcular subtotal (suma de todos los productos)
        $subtotal = $comanda->detalles()->sum('subtotal');
        
        // Los precios ya incluyen IVA, no calcular impuestos adicionales
        $impuestos = 0;
        
        // Calcular propina solo si está habilitada
        $propina = 0;
        if ($comanda->incluye_propina) {
            $propina = round($subtotal * 0.10); // 10% de propina
        }
        
        // Total = subtotal + propina (sin impuestos adicionales)
        $total = $subtotal + $propina;

        $comanda->update([
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'propina' => $propina,
            'total' => $total
        ]);
    }

    public function imprimirComanda($comandaId)
    {
        try {
            $comanda = Comanda::with(['mesa', 'detalles.producto', 'user'])
                ->findOrFail($comandaId);

            // Aquí podrías generar un PDF o enviar a impresora
            // Por ahora solo retornamos los datos para imprimir desde el frontend

            return response()->json([
                'success' => true,
                'comanda' => $comanda
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener comanda para imprimir: ' . $e->getMessage()
            ], 500);
        }
    }
}
