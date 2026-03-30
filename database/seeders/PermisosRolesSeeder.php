<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;
use App\Models\PermisoRole;
use App\Models\Role;

class PermisosRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Inserta todos los permisos disponibles en la tabla permisos (catálogo)
     */
    public function run(): void
    {
        $permisos = [
            [
                'codigo' => Permiso::PERMISO_CIERRES_CAJA,
                'nombre' => 'Ver Todos los Cierres',
                'descripcion' => 'Ver todos los cierres de caja (no solo los propios)',
                'modulo' => 'Caja'
            ],
            [
                'codigo' => Permiso::PERMISO_VER_TODAS_VENTAS,
                'nombre' => 'Ver Todas las Ventas',
                'descripcion' => 'Ver todas las ventas del sistema',
                'modulo' => 'Ventas'
            ],
            [
                'codigo' => Permiso::PERMISO_ANULAR_TICKETS,
                'nombre' => 'Anular Tickets',
                'descripcion' => 'Anular tickets de cualquier usuario',
                'modulo' => 'Ventas'
            ],
            [
                'codigo' => Permiso::PERMISO_ELIMINAR_PRODUCTOS,
                'nombre' => 'Eliminar Productos',
                'descripcion' => 'Eliminar productos del sistema',
                'modulo' => 'Productos'
            ],
            [
                'codigo' => Permiso::PERMISO_MODIFICAR_PRECIOS,
                'nombre' => 'Modificar Precios',
                'descripcion' => 'Modificar precios de productos',
                'modulo' => 'Productos'
            ],
            [
                'codigo' => Permiso::PERMISO_DASHBOARD_GERENCIAL,
                'nombre' => 'Dashboard Gerencial',
                'descripcion' => 'Acceso al dashboard gerencial con métricas de alto nivel del negocio',
                'modulo' => 'Dashboard'
            ],
            [
                'codigo' => Permiso::PERMISO_DASHBOARD_ADMINISTRADOR,
                'nombre' => 'Dashboard Administrador',
                'descripcion' => 'Acceso al dashboard de administrador con información operativa detallada',
                'modulo' => 'Dashboard'
            ],
            [
                'codigo' => Permiso::PERMISO_DASHBOARD_USUARIO,
                'nombre' => 'Dashboard Usuario',
                'descripcion' => 'Acceso al dashboard básico de usuario',
                'modulo' => 'Dashboard'
            ],
        ];

        $this->command->info("📋 Insertando permisos en la tabla permisos...");
        $this->command->newLine();
        
        foreach ($permisos as $permiso) {
            Permiso::updateOrCreate(
                ['codigo' => $permiso['codigo']],
                [
                    'nombre' => $permiso['nombre'],
                    'descripcion' => $permiso['descripcion'],
                    'modulo' => $permiso['modulo'],
                    'activo' => true
                ]
            );
            
            $this->command->info("  ✓ {$permiso['codigo']} - {$permiso['nombre']}");
        }
        
        $this->command->newLine();
        $totalPermisos = count($permisos);
        $this->command->info("✓ {$totalPermisos} permisos insertados correctamente");
        
        // Asignar todos los permisos al rol SuperAdministrador
        $this->command->newLine();
        $this->command->info("👑 Asignando permisos al rol SuperAdministrador...");
        $this->command->newLine();
        
        $superAdmin = Role::where('role_name', 'SuperAdministrador')->first();
        
        $excluirSuperAdmin = [
            Permiso::PERMISO_DASHBOARD_ADMINISTRADOR,
            Permiso::PERMISO_DASHBOARD_USUARIO,
        ];

        if ($superAdmin) {
            foreach ($permisos as $permiso) {
                if (in_array($permiso['codigo'], $excluirSuperAdmin)) {
                    continue;
                }

                PermisoRole::updateOrCreate(
                    [
                        'role_id' => $superAdmin->id,
                        'codigo_permiso' => $permiso['codigo']
                    ],
                    [
                        'descripcion' => $permiso['descripcion'],
                        'activo' => true
                    ]
                );
                
                $this->command->info("  ✓ {$permiso['codigo']} asignado a SuperAdministrador");
            }
            
            $this->command->newLine();
            $this->command->info("✓ Permisos asignados correctamente a SuperAdministrador");
        } else {
            $this->command->newLine();
            $this->command->warn("⚠ No se encontró el rol SuperAdministrador. Ejecuta RolesTableSeeder primero.");
        }

        // Asignar permisos de dashboard por rol
        $this->command->newLine();
        $this->command->info("🔧 Asignando permisos de dashboard por rol...");
        $this->command->newLine();

        $dashboardPorRol = [
            'Administrador' => [
                Permiso::PERMISO_DASHBOARD_ADMINISTRADOR,
            ],
            'Usuario' => [
                Permiso::PERMISO_DASHBOARD_USUARIO,
            ],
        ];

        foreach ($dashboardPorRol as $roleName => $codigos) {
            $rol = Role::where('role_name', $roleName)->first();
            if (!$rol) {
                $this->command->warn("  ⚠ No se encontró el rol {$roleName}");
                continue;
            }
            foreach ($codigos as $codigo) {
                $permiso = collect($permisos)->firstWhere('codigo', $codigo);
                PermisoRole::updateOrCreate(
                    [
                        'role_id' => $rol->id,
                        'codigo_permiso' => $codigo,
                    ],
                    [
                        'descripcion' => $permiso['descripcion'] ?? null,
                        'activo' => true,
                    ]
                );
                $this->command->info("  ✓ {$codigo} asignado a {$roleName}");
            }
        }

        $this->command->newLine();
        $this->command->info("✓ Permisos de dashboard asignados correctamente");
    }
}

