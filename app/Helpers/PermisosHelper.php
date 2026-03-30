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

if (!function_exists('puedeEliminarProductos')) {
    /**
     * Verifica si el usuario puede eliminar productos y categorías
     * 
     * @return bool
     */
    function puedeEliminarProductos()
    {
        return tienePermiso(\App\Models\Permiso::PERMISO_ELIMINAR_PRODUCTOS);
    }
}

if (!function_exists('puedeModificarPrecios')) {
    /**
     * Verifica si el usuario puede modificar precios de productos y rangos de precios
     * 
     * @return bool
     */
    function puedeModificarPrecios()
    {
        return tienePermiso(\App\Models\Permiso::PERMISO_MODIFICAR_PRECIOS);
    }
}

if (!function_exists('puedeVerDashboardGerencial')) {
    /**
     * Verifica si el usuario puede ver el dashboard gerencial
     * 
     * @return bool
     */
    function puedeVerDashboardGerencial()
    {
        return tienePermiso(\App\Models\Permiso::PERMISO_DASHBOARD_GERENCIAL);
    }
}

if (!function_exists('puedeVerDashboardAdministrador')) {
    /**
     * Verifica si el usuario puede ver el dashboard de administrador
     * 
     * @return bool
     */
    function puedeVerDashboardAdministrador()
    {
        return tienePermiso(\App\Models\Permiso::PERMISO_DASHBOARD_ADMINISTRADOR);
    }
}

if (!function_exists('puedeVerDashboardUsuario')) {
    /**
     * Verifica si el usuario puede ver el dashboard de usuario
     * 
     * @return bool
     */
    function puedeVerDashboardUsuario()
    {
        return tienePermiso(\App\Models\Permiso::PERMISO_DASHBOARD_USUARIO);
    }
}

