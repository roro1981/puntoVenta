<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'menu_name',
        'menu_route',
        'menu_fa',
        'created_at',
        'updated_at'  
     ];

     //Relaciones con otras tablas

    public function submenus()
    {
        return $this->hasMany(Submenu::class);
    }
}
