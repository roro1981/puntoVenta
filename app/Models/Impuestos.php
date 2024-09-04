<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impuestos extends Model
{
    use HasFactory;

    protected $table = 'impuestos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nom_imp',
        'valor_imp',
        'descrip_imp',
        'last_activity'
     ];

     public function updateImp($impuestoRequest)
     {
        $data = [
            'valor_imp' => $impuestoRequest['valor_imp'],
            'last_activity' => now(),
        ];
        
        $this->update($data);
     }
}
