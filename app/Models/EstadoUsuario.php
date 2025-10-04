<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoUsuario extends Model
{
    protected $table = 'estado_usuario'; // ← nombre exacto de la tabla

    protected $fillable = [
        'nombre_estado', // ← ajustá según tus columnas reales
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_estado');
    }
}
