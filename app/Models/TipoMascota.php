<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoMascota extends Model
{
    use SoftDeletes;

    protected $table = 'tipo_mascotas';
    
    protected $fillable = [
        'nombre'
    ];

    public $timestamps = false;
}
