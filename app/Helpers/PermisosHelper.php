<?php

if (!function_exists('tienePermiso')) {
    /**
     * Verifica si el usuario autenticado tiene un permiso específico
     * 
     * @param string $codigoPermiso Código del permiso a verificar
     * @return bool
     */
    function tienePermiso($codigoPermiso)
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        return $user->tienePermiso($codigoPermiso);
    }
}

if (!function_exists('puedeVerTodosCierres')) {
    /**
     * Verifica si el usuario puede ver todos los cierres de caja
     * 
     * @return bool
     */
    function puedeVerTodosCierres()
    {
        return tienePermiso(\App\Models\Permiso::PERMISO_CIERRES_CAJA);
    }
}

if (!function_exists('puedeVerTodasVentas')) {
    /**
     * Verifica si el usuario puede ver todas las ventas
     * 
     * @return bool
     */
    function puedeVerTodasVentas()
    {
        return tienePermiso(\App\Models\Permiso::PERMISO_VER_TODAS_VENTAS);
    }
}

if (!function_exists('puedeAnularTickets')) {
    /**
     * Verifica si el usuario puede anular tickets de otros usuarios
     * 
     * @return bool
     */
    function puedeAnularTickets()
    {
        return tienePermiso(\App\Models\Permiso::PERMISO_ANULAR_TICKETS);
    }
}
