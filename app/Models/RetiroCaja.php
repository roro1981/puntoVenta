<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetiroCaja extends Model
{
    protected $table = 'retiros_caja';

    public $timestamps = false;

    protected $fillable = [
        'caja_id',
        'monto',
        'motivo',
        'creado_por',
    ];

    protected $casts = [
        'monto'      => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
