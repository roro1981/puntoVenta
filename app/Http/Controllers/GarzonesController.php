<?php

namespace App\Http\Controllers;

use App\Models\Garzon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GarzonesController extends Controller
{
    public function index()
    {
        return view('restaurant.garzones');
    }

    public function obtener()
    {
        try {
            $garzones = Garzon::orderBy('nombre')->orderBy('apellido')->get();

            return response()->json([
                'success' => true,
                'garzones' => $garzones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los garzones: ' . $e->getMessage()
            ], 500);
        }
    }

    public function crear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'   => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'rut'      => 'required|string|max:20|unique:garzones,rut',
            'telefono' => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:100',
            'estado'   => 'required|in:Activo,Inactivo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $garzon = Garzon::create([
                'nombre'   => $request->nombre,
                'apellido' => $request->apellido,
                'rut'      => $request->rut,
                'telefono' => $request->telefono,
                'email'    => $request->email,
                'estado'   => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Garzón creado correctamente',
                'garzon'  => $garzon
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el garzón: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre'   => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'rut'      => 'required|string|max:20|unique:garzones,rut,' . $id,
            'telefono' => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:100',
            'estado'   => 'required|in:Activo,Inactivo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $garzon = Garzon::findOrFail($id);

            $garzon->update([
                'nombre'   => $request->nombre,
                'apellido' => $request->apellido,
                'rut'      => $request->rut,
                'telefono' => $request->telefono,
                'email'    => $request->email,
                'estado'   => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Garzón actualizado correctamente',
                'garzon'  => $garzon
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el garzón: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            $garzon = Garzon::findOrFail($id);

            // Verificar si tiene comandas asignadas
            if ($garzon->comandas()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el garzón porque tiene comandas asociadas. Puede desactivarlo en su lugar.'
                ], 409);
            }

            $garzon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Garzón eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el garzón: ' . $e->getMessage()
            ], 500);
        }
    }
}
