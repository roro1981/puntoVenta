<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submenu extends Model
{
    use HasFactory;

    protected $table = 'submenus';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'menu_id',
        'submenu_name',
        'submenu_route',
        'created_at',
        'updated_at'  
     ];

     //Relaciones con otras tablas
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'menu_roles', 'submenu_id', 'role_id');
    }

    public function menuRoles()
    {
        return $this->hasMany(MenuRole::class);
    }
}
