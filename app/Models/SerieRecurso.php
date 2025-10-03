<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SerieRecurso
 *
 * @property $id
 * @property $id_recurso
 * @property $id_incidente_detalle
 * @property $nro_serie
 * @property $talle
 * @property $fecha_adquisicion
 * @property $fecha_vencimiento
 *
 * @property Recurso $recurso
 * @property IncidenteDetalle $incidenteDetalle
 * @property DetallePrestamo[] $detallePrestamos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class SerieRecurso extends Model
{
    
    protected $perPage = 20;
    protected $table = 'serie_recurso';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['id_recurso', 'id_incidente_detalle', 'nro_serie', 'talle', 'fecha_adquisicion', 'fecha_vencimiento'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recurso()
{
    return $this->belongsTo(Recurso::class, 'id_recurso');
}

    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function incidenteDetalle()
    {
        return $this->belongsTo(\App\Models\IncidenteDetalle::class, 'id_incidente_detalle', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detallePrestamos()
    {
        return $this->hasMany(\App\Models\DetallePrestamo::class, 'id', 'id_serie');
    }
    
}
