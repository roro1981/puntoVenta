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
}
