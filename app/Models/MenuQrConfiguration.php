<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuQrConfiguration extends Model
{
    use HasFactory;

    protected $table = 'menu_qr_configurations';

    protected $fillable = [
        'public_token',
        'selected_categories',
        'selected_items',
        'activo',
    ];

    protected $casts = [
        'selected_categories' => 'array',
        'selected_items' => 'array',
        'activo' => 'boolean',
    ];
}