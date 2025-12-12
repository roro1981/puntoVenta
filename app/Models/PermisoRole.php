<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisoRole extends Model
{
    use HasFactory;

    protected $table = 'permisos_roles';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'role_id',
        'codigo_permiso',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relación con Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Relación con Permiso (catálogo)
    public function permiso()
    {
        return $this->belongsTo(Permiso::class, 'codigo_permiso', 'codigo');
    }

    /**
     * Verifica si un rol tiene un permiso específico
     * 
     * @param int $roleId ID del rol
     * @param string $codigoPermiso Código del permiso
     * @return bool
     */
    public static function tienePermiso($roleId, $codigoPermiso)
    {
        return self::where('role_id', $roleId)
            ->where('codigo_permiso', $codigoPermiso)
            ->where('activo', true)
            ->exists();
    }

    /**
     * Obtiene todos los permisos de un rol
     * 
     * @param int $roleId ID del rol
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function permisosPorRole($roleId)
    {
        return self::where('role_id', $roleId)
            ->where('activo', true)
            ->pluck('codigo_permiso');
    }
}
