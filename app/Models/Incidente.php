<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EstadoIncidente;
use App\Models\Recurso;

class Incidente extends Model
{
    protected $table = 'incidente';

    protected $fillable = [
        'id_recurso',
        'id_estado_incidente',
        'id_supervisor',
        'descripcion',
        'fecha_incidente',
        'fecha_cierre_incidente',
        'resolucion',
        'id_usuario_creacion',
        'id_usuario_modificacion',
        'fecha_creacion',
        'fecha_modificacion',
    ];

    public $timestamps = false;

    public function estado()
    {
        return $this->belongsTo(EstadoIncidente::class, 'id_estado_incidente');
    }

    public function recurso()
    {
        return $this->belongsTo(Recurso::class, 'id_recurso');
    }
}
