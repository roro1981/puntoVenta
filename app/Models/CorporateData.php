<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateData extends Model
{
    use HasFactory;

    protected $table = 'corporate_data';

    protected $fillable = [
        'item',
        'description_item',
    ];

    public $timestamps = false;
}
