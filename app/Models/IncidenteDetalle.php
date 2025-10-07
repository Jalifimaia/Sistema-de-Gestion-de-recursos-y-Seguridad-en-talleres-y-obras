<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class IncidenteDetalle
 *
 * @property $id
 * @property $id_incidente
 * @property $id_serie
 * @property $descripcion
 *
 * @property Incidente $incidente
 * @property Incidente[] $incidentes
 * @property Recurso[] $recursos
 * @property SerieRecurso[] $serieRecursos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class IncidenteDetalle extends Model
{
    protected $table = 'incidente_detalle';
    protected $perPage = 20;
    public $timestamps = false;

    protected $fillable = ['id_incidente', 'id_serie_recurso', 'descripcion'];

    public function incidente()
    {
        return $this->belongsTo(Incidente::class, 'id_incidente');
    }

    public function serieRecurso()
    {
        return $this->belongsTo(SerieRecurso::class, 'id_serie_recurso');
    }
}

