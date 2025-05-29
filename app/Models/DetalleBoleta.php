<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public static function grabarDetalleBoleta(object $item): self
    {
        return self::create([
            'uuid' =>  Str::uuid(),
            'num_boleta' => $item->nbol,
            'cod_prod' => $item->cod,
            'cantidad' => $item->cant,
            'precio' => $item->precio,
            'descu' => $item->descu
        ]);
    }
}
