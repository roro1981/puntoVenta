<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Insertar los 3 permisos en el catálogo
        $permisos = [
            [
                'codigo'      => 'PERMISO_DASHBOARD_GERENCIAL',
                'nombre'      => 'Dashboard Gerencial',
                'descripcion' => 'Acceso al dashboard gerencial con métricas de alto nivel del negocio',
                'modulo'      => 'Dashboard',
                'activo'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'codigo'      => 'PERMISO_DASHBOARD_ADMINISTRADOR',
                'nombre'      => 'Dashboard Administrador',
                'descripcion' => 'Acceso al dashboard de administrador con información operativa detallada',
                'modulo'      => 'Dashboard',
                'activo'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'codigo'      => 'PERMISO_DASHBOARD_USUARIO',
                'nombre'      => 'Dashboard Usuario',
                'descripcion' => 'Acceso al dashboard básico de usuario',
                'modulo'      => 'Dashboard',
                'activo'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permisos')->updateOrInsert(
                ['codigo' => $permiso['codigo']],
                $permiso
            );
        }

        // Obtener IDs de roles
        $superAdminId     = DB::table('roles')->where('role_name', 'SuperAdministrador')->value('id');
        $administradorId  = DB::table('roles')->where('role_name', 'Administrador')->value('id');
        $usuarioId        = DB::table('roles')->where('role_name', 'Usuario')->value('id');

        $asignaciones = [];

        // SuperAdministrador: todos los dashboards
        if ($superAdminId) {
            foreach ($permisos as $permiso) {
                $asignaciones[] = [
                    'role_id'        => $superAdminId,
                    'codigo_permiso' => $permiso['codigo'],
                    'descripcion'    => $permiso['descripcion'],
                    'activo'         => 1,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }

        // Administrador: dashboard administrador
        if ($administradorId) {
            $asignaciones[] = [
                'role_id'        => $administradorId,
                'codigo_permiso' => 'PERMISO_DASHBOARD_ADMINISTRADOR',
                'descripcion'    => 'Acceso al dashboard de administrador con información operativa detallada',
                'activo'         => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        // Usuario: dashboard usuario
        if ($usuarioId) {
            $asignaciones[] = [
                'role_id'        => $usuarioId,
                'codigo_permiso' => 'PERMISO_DASHBOARD_USUARIO',
                'descripcion'    => 'Acceso al dashboard básico de usuario',
                'activo'         => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        foreach ($asignaciones as $asignacion) {
            DB::table('permisos_roles')->updateOrInsert(
                [
                    'role_id'        => $asignacion['role_id'],
                    'codigo_permiso' => $asignacion['codigo_permiso'],
                ],
                $asignacion
            );
        }
    }

    public function down(): void
    {
        $codigos = [
            'PERMISO_DASHBOARD_GERENCIAL',
            'PERMISO_DASHBOARD_ADMINISTRADOR',
            'PERMISO_DASHBOARD_USUARIO',
        ];

        DB::table('permisos_roles')->whereIn('codigo_permiso', $codigos)->delete();
        DB::table('permisos')->whereIn('codigo', $codigos)->delete();
    }
};
