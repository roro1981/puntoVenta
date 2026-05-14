<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuQrConfiguration extends Model
{
    use HasFactory;

    protected $table = 'menu_qr_configurations';

    protected $fillable = [
        'nombre',
        'public_token',
        'selected_categories',
        'selected_items',
        'design_theme',
        'design_tokens',
        'design_options',
        'activo',
    ];

    protected $casts = [
        'selected_categories' => 'array',
        'selected_items' => 'array',
        'design_tokens' => 'array',
        'design_options' => 'array',
        'activo' => 'boolean',
    ];
}