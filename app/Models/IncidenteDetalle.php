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
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['id_incidente', 'id_serie', 'descripcion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function incidente()
    {
        return $this->belongsTo(\App\Models\Incidente::class, 'id_incidente', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function incidentes()
    {
        return $this->hasMany(\App\Models\Incidente::class, 'id', 'id_incidente_detalle');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recursos()
    {
        return $this->hasMany(\App\Models\Recurso::class, 'id', 'id_incidente_detalle');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function serieRecursos()
    {
        return $this->hasMany(\App\Models\SerieRecurso::class, 'id', 'id_incidente_detalle');
    }
    
}
