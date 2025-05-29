<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialMovimientos extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'historial_movimientos';

    protected $fillable = [
        'producto_id',
        'cantidad',
        'stock',
        'tipo_mov',
        'fecha',
        'num_doc',
        'obs',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }

    public static function registrarMovimiento(array $data): self
    {
        return self::create([
            'producto_id' => $data['producto_id'],
            'cantidad'    => $data['cantidad'],
            'stock'       => $data['stock'],
            'tipo_mov'    => $data['tipo_mov'],
            'fecha'       => $data['fecha'],
            'num_doc'     => $data['num_doc'],
            'obs'         => $data['obs'] ?? '-'
        ]);
    }
    
}
