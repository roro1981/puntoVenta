<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'role_name',
        'created_at',
        'updated_at'  
     ];

     //Relaciones con otras tablas 
     public function users()
    {
        return $this->hasMany(User::class);
    }

    public function submenus()
    {
        return $this->belongsToMany(Submenu::class, 'menu_roles', 'role_id', 'submenu_id');
    }

    public function permisos()
    {
        return $this->hasMany(PermisoRole::class);
    }

    /**
     * Verifica si el rol tiene un permiso específico
     * 
     * @param string $codigoPermiso Código del permiso
     * @return bool
     */
    public function tienePermiso($codigoPermiso)
    {
        return $this->permisos()
            ->where('codigo_permiso', $codigoPermiso)
            ->where('activo', true)
            ->exists();
    }

    /**
     * Roles base permitidos por tipo de negocio.
     */
    public static function baseRolesPorTipoNegocio(?string $tipoNegocio): array
    {
        $tipo = strtoupper(trim((string) $tipoNegocio));

        return match ($tipo) {
            'ALMACEN' => ['Administrador', 'Usuario', 'Gerencia'],
            'ALMACEN_PREVENTA' => ['Administrador', 'Usuario', 'Cajero', 'Gerencia'],
            'RESTAURANT' => ['Administrador', 'Cajero', 'Garzón', 'Gerencia'],
            default => ['Administrador', 'Usuario', 'Cajero', 'Gerencia'],
        };
    }

    public static function esRolVisiblePorTipoNegocio(string $roleName, ?string $tipoNegocio): bool
    {
        $normalizado = self::normalizarNombreRol($roleName);

        if ($normalizado === 'superadministrador') {
            return false;
        }

        if (!self::esRolSistema($roleName)) {
            return true;
        }

        return in_array(
            $normalizado,
            array_map([self::class, 'normalizarNombreRol'], self::baseRolesPorTipoNegocio($tipoNegocio)),
            true
        );
    }

    public static function esRolProtegidoPorTipoNegocio(string $roleName, ?string $tipoNegocio): bool
    {
        return in_array(
            self::normalizarNombreRol($roleName),
            array_map([self::class, 'normalizarNombreRol'], self::baseRolesPorTipoNegocio($tipoNegocio)),
            true
        );
    }

    public static function esRolSistemaPorDefecto(string $roleName): bool
    {
        return self::esRolSistema($roleName);
    }

    private static function esRolSistema(string $roleName): bool
    {
        return in_array(
            self::normalizarNombreRol($roleName),
            ['superadministrador', 'administrador', 'usuario', 'cajero', 'garzon', 'gerencia'],
            true
        );
    }

    private static function normalizarNombreRol(string $roleName): string
    {
        $valor = mb_strtolower(trim($roleName));

        return strtr($valor, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
        ]);
    }
}
