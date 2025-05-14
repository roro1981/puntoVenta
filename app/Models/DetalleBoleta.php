<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleBoleta extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'detalle_boleta';

    protected $fillable = [
        'uuid',
        'num_boleta',
        'cod_prod',
        'cantidad',
        'precio',
        'descu',
    ];

    public function boleta()
    {
        return $this->belongsTo(Boleta::class, 'num_boleta', 'num_boleta');
    }
}
