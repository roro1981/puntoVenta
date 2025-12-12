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
            ]
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
        
        if ($superAdmin) {
            foreach ($permisos as $permiso) {
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
            $this->command->info("✓ Todos los permisos asignados correctamente a SuperAdministrador");
        } else {
            $this->command->newLine();
            $this->command->warn("⚠ No se encontró el rol SuperAdministrador. Ejecuta RolesTableSeeder primero.");
        }
    }
}

