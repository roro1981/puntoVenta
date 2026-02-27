<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MesasController extends Controller
{
    public function index()
    {
        return view('restaurant.mesas');
    }

    public function obtener()
    {
        try {
            $mesas = Mesa::orderBy('orden')->get();
            
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

    public function crear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'capacidad' => 'required|integer|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ultimaOrden = Mesa::max('orden');
            
            $mesa = Mesa::create([
                'nombre' => $request->nombre,
                'capacidad' => $request->capacidad,
                'orden' => $ultimaOrden + 1,
                'activa' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mesa creada correctamente',
                'mesa' => $mesa
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'capacidad' => 'required|integer|min:1|max:20',
            'activa' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $mesa = Mesa::findOrFail($id);
            
            $mesa->update([
                'nombre' => $request->nombre,
                'capacidad' => $request->capacidad,
                'activa' => $request->activa
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mesa actualizada correctamente',
                'mesa' => $mesa
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            $mesa = Mesa::findOrFail($id);
            $mesa->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mesa eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la mesa: ' . $e->getMessage()
            ], 500);
        }
    }
}
