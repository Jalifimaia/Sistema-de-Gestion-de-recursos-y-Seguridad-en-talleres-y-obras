<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TareaDiaria extends Model
{
    protected $table = 'tareas_diarias';

    protected $fillable = [
        'trabajador_id',
        'fecha',
        'requiere_altura',
        'asignado_por',
        'observaciones',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Usuario::class, 'trabajador_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Usuario::class, 'asignado_por');
    }
}
