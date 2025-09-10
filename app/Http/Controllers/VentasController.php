<?php

namespace App\Http\Controllers;

use App\Models\Borrador;
use App\Models\Producto;
use Illuminate\Http\Request;

class VentasController extends Controller
{
    public function indexVentas()
    {        
        return view('ventas.generar_venta');
    }

    public function searchProduct(Request $request)
    {
        $query = $request->input('q');
        $tipo = $request->input('tipo');

        $products = Producto::select('id', 'descripcion', 'precio_venta');

        if ($tipo == 1) {
            $products->where('codigo', $query);
        } else {
            $products->where(function($q) use ($query) {
                $q->where('descripcion', 'like', "%$query%")
                ->orWhere('codigo', 'like', "%$query%");
            });
        }

        $results = $products->get(); 
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
}
