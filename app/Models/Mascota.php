<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mascota extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'nombre',
        'tipo_mascota_id',
        'raza',
        'fecha_nacimiento',
        'descripcion',
        'foto'
    ];

    public $timestamps = false;

    protected $casts = [
        'fecha_nacimiento' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tipoMascota(): BelongsTo
    {
        return $this->belongsTo(TipoMascota::class);
    }
}