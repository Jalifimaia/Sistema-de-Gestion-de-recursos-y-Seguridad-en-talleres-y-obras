<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Incidente
 *
 * @property $id
 * @property $id_recurso
 * @property $id_supervisor
 * @property $id_incidente_detalle
 * @property $id_usuario_creacion
 * @property $id_usuario_modificacion
 * @property $descripcion
 * @property $fecha_incidente
 * @property $fecha_creacion
 * @property $fecha_modificacion
 * @property $fecha_cierre_incidente
 * @property $resolucion
 *
 * @property Recurso $recurso
 * @property IncidenteDetalle $incidenteDetalle
 * @property Usuario $usuarioC
 * @property Usuario $usuarioM
 * @property IncidenteDetalle[] $incidenteDetalles
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder

 * 
 */
class Incidente extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['id_recurso', 'id_supervisor', 'id_incidente_detalle', 'id_usuario_creacion', 'id_usuario_modificacion', 'descripcion', 'fecha_incidente', 'fecha_creacion', 'fecha_modificacion', 'fecha_cierre_incidente', 'resolucion'];
    protected $table = 'incidente';
    public $timestamps = false;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recurso()
    {
        return $this->belongsTo(\App\Models\Recurso::class, 'id_recurso', 'id');
    }

    
    public function estadoIncidente()
{
    return $this->belongsTo(EstadoIncidente::class, 'id_estado_incidente');
}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function incidenteDetalle()
    {
        return $this->belongsTo(\App\Models\IncidenteDetalle::class, 'id_incidente_detalle', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioC()
{
    return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario_creacion', 'id');
}

public function usuarioM()
{
    return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario_modificacion', 'id');
}

    public function supervisor()
{
    return $this->belongsTo(User::class, 'id_supervisor');
}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function incidenteDetalles()
    {
        return $this->hasMany(\App\Models\IncidenteDetalle::class, 'id', 'id_incidente');
    }
    
    public function detalle()
{
    return $this->hasOne(IncidenteDetalle::class, 'id_incidente');
}
}
