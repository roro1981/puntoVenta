<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermisoRole;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\Globales;

class PermisosController extends Controller
{
    private const TEXTO_EXTRA_ANULACION_COMANDA = ' y también permite eliminar productos de comandas';

    /**
     * Mostrar vista de gestión de permisos
     */
    public function index()
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));

        $rolesFiltrados = Role::all()
            ->filter(function ($role) use ($tipoNegocio) {
                return Role::esRolVisiblePorTipoNegocio($role->role_name, $tipoNegocio);
            })
            ->values();

        $roles = Role::whereIn('id', $rolesFiltrados->pluck('id')->all())
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
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        $permisos = Permiso::where('activo', true)->get()->map(function ($permiso) use ($tipoNegocio) {
            $permiso->descripcion = $this->normalizarDescripcionPorTipoNegocio(
                $permiso->codigo,
                (string) $permiso->descripcion,
                $tipoNegocio
            );
            return $permiso;
        });

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
            $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
            $codigosPermisos = collect($permisos)->pluck('codigo')->filter()->values()->all();
            $descripcionesPorCodigo = Permiso::whereIn('codigo', $codigosPermisos)
                ->get(['codigo', 'descripcion'])
                ->keyBy('codigo');

            // Eliminar todos los permisos existentes del rol
            PermisoRole::where('role_id', $roleId)->delete();
            
            $creados = 0;
            
            // Asignar los nuevos permisos
            if (!empty($permisos)) {
                foreach ($permisos as $permisoData) {
                    $codigoPermiso = (string) $permisoData['codigo'];
                    $descripcionBase = (string) optional($descripcionesPorCodigo->get($codigoPermiso))->descripcion;

                    PermisoRole::create([
                        'role_id' => $roleId,
                        'codigo_permiso' => $codigoPermiso,
                        'descripcion' => $this->normalizarDescripcionPorTipoNegocio($codigoPermiso, $descripcionBase, $tipoNegocio),
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

    private function normalizarDescripcionPorTipoNegocio(string $codigo, string $descripcion, string $tipoNegocio): string
    {
        if ($codigo !== Permiso::PERMISO_ANULAR_TICKETS) {
            return $descripcion;
        }

        $descripcion = trim(str_replace(self::TEXTO_EXTRA_ANULACION_COMANDA, '', $descripcion));

        if ($tipoNegocio === 'RESTAURANT') {
            return $descripcion . self::TEXTO_EXTRA_ANULACION_COMANDA;
        }

        return $descripcion;
    }
}

