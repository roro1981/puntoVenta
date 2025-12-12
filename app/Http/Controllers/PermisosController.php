<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermisoRole;
use App\Models\Permiso;
use App\Models\Role;

class PermisosController extends Controller
{
    /**
     * Mostrar vista de gestión de permisos
     */
    public function index()
    {

        $roles = Role::where('role_name', '!=', 'SuperAdministrador')
            ->with(['permisos' => function($query) {
                $query->where('activo', true);
            }])
            ->get();

        return view('users.permisos-roles', compact('roles'));
    }

    /**
     * Obtener todos los permisos disponibles
     */
    public function permisosDisponibles()
    {
        $permisos = Permiso::where('activo', true)->get();
        return response()->json($permisos);
    }

    /**
     * Obtener permisos de un rol específico
     */
    public function permisosPorRole($roleId)
    {
        $permisos = PermisoRole::where('role_id', $roleId)
            ->where('activo', true)
            ->get();

        return response()->json($permisos);
    }

    /**
     * Asignar múltiples permisos a un rol
     */
    public function asignarMultiples(Request $request)
    { 
        // Obtener los datos del request
        $roleId = $request->input('role_id');
        $permisos = $request->input('permisos', []);
        
        // Validar
        if (!$roleId) {
            return response()->json([
                'success' => false,
                'message' => 'El campo role_id es requerido'
            ], 422);
        }

        if (!Role::where('id', $roleId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El rol seleccionado no existe'
            ], 422);
        }

        // Validar cada permiso
        if (!empty($permisos)) {
            foreach ($permisos as $index => $permiso) {
                if (empty($permiso['codigo'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "El código del permiso en la posición {$index} es requerido"
                    ], 422);
                }
            }
        }

        try {
            // Eliminar todos los permisos existentes del rol
            PermisoRole::where('role_id', $roleId)->delete();
            
            $creados = 0;
            
            // Asignar los nuevos permisos
            if (!empty($permisos)) {
                foreach ($permisos as $permisoData) {
                    PermisoRole::create([
                        'role_id' => $roleId,
                        'codigo_permiso' => $permisoData['codigo'],
                        'descripcion' => $permisoData['descripcion'] ?? null,
                        'activo' => true
                    ]);
                    $creados++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => $creados > 0 
                    ? "$creados permisos asignados correctamente" 
                    : "Todos los permisos han sido removidos del rol"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar permisos: ' . $e->getMessage()
            ], 500);
        }
    }
}

