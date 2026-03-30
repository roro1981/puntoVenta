<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Inserta el submenú "Configurar Garzones" en el menú Configuración (menu_id = 6)
     * y lo asigna a los roles Administrador (1) y Superadministrador (3).
     */
    public function up(): void
    {
        // Si la tabla de submenus está vacía, migrate:fresh está corriendo.
        // Los seeders se encargarán de insertar los datos.
        if (DB::table('submenus')->count() === 0) {
            return;
        }

        // Insertar submenu (usa updateOrInsert para ser idempotente)
        DB::table('submenus')->updateOrInsert(
            ['submenu_route' => '/restaurant/config-garzones'],
            [
                'menu_id'      => 6,
                'submenu_name' => 'Configurar Garzones',
                'created_at'   => Carbon::now()->toDateTimeString(),
            ]
        );

        $submenu = DB::table('submenus')
            ->where('submenu_route', '/restaurant/config-garzones')
            ->first();

        if (!$submenu) {
            return;
        }

        // Buscar roles por nombre (soft-fail si no existen: migrate:fresh corre antes que seeders)
        $adminId      = DB::table('roles')->where('role_name', 'Administrador')->value('id');
        $superAdminId = DB::table('roles')->where('role_name', 'SuperAdministrador')->value('id');

        foreach (array_filter([$adminId, $superAdminId]) as $roleId) {
            DB::table('menu_roles')->updateOrInsert(
                ['role_id' => $roleId, 'submenu_id' => $submenu->id],
                ['created_at' => Carbon::now()->toDateTimeString()]
            );
        }
    }

    public function down(): void
    {
        $submenu = DB::table('submenus')
            ->where('menu_id', 6)
            ->where('submenu_route', '/restaurant/config-garzones')
            ->first();

        if ($submenu) {
            DB::table('menu_roles')->where('submenu_id', $submenu->id)->delete();
            DB::table('submenus')->where('id', $submenu->id)->delete();
        }
    }
};

