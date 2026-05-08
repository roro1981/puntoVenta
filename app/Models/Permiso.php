<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    use HasFactory;

    protected $table = 'permisos';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'modulo',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Constantes de códigos de permisos
    const PERMISO_CIERRES_CAJA = 'PERMISO_CIERRES_CAJA';
    const PERMISO_VER_TODAS_VENTAS = 'PERMISO_VER_TODAS_VENTAS';
    const PERMISO_ANULAR_TICKETS = 'PERMISO_ANULAR_TICKETS';
    const PERMISO_ELIMINAR_PRODUCTOS = 'PERMISO_ELIMINAR_PRODUCTOS';
    const PERMISO_MODIFICAR_PRECIOS = 'PERMISO_MODIFICAR_PRECIOS';
    const PERMISO_GESTIONAR_USUARIOS = 'PERMISO_GESTIONAR_USUARIOS';
    const PERMISO_VER_REPORTES = 'PERMISO_VER_REPORTES';
    const PERMISO_CONFIGURACION_GENERAL = 'PERMISO_CONFIGURACION_GENERAL';
    const PERMISO_DASHBOARD_GERENCIAL = 'PERMISO_DASHBOARD_GERENCIAL';
    const PERMISO_DASHBOARD_ADMINISTRADOR = 'PERMISO_DASHBOARD_ADMINISTRADOR';
    const PERMISO_DASHBOARD_USUARIO = 'PERMISO_DASHBOARD_USUARIO';

    // --- Seguridad de caja ---
    /** Solo usuarios con este permiso pueden registrar retiros de caja. */
    const PERMISO_RETIRO_CAJA = 'PERMISO_RETIRO_CAJA';
    /** Usuarios con este permiso pueden aplicar descuentos superiores al límite configurado. */
    const PERMISO_DESCUENTO_LIBRE = 'PERMISO_DESCUENTO_LIBRE';

    /**
     * Obtener permisos por módulo
     */
    public static function porModulo($modulo)
    {
        return self::where('modulo', $modulo)
            ->where('activo', true)
            ->get();
    }

    /**
     * Obtener todos los permisos activos
     */
    public static function activos()
    {
        return self::where('activo', true)->get();
    }
}
