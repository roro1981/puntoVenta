<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Globales extends Model
{
    use HasFactory;

    protected $table = 'globales';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nom_var',
        'valor_var',
        'descrip_var'
     ];

     public function updateVar($globalRequest)
     {
        $data = [
            'valor_var' => $globalRequest['valor_var']
        ];
        
        $this->update($data);
     }

}
