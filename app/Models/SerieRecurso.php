<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SerieRecurso
 *
 * @property int $id
 * @property int $id_recurso
 * @property int|null $id_incidente_detalle
 * @property string|null $nro_serie
 * @property string|null $talle
 * @property string|null $fecha_adquisicion
 * @property string|null $fecha_vencimiento
 * @property int $id_estado
 *
 * @property Recurso $recurso
 * @property IncidenteDetalle $incidenteDetalle
 * @property DetallePrestamo[] $detallePrestamos
 * @property Estado $estado
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class SerieRecurso extends Model
{
    protected $table = 'serie_recurso';
    public $timestamps = false; // 👈 importante, tu tabla no tiene created_at/updated_at
    protected $perPage = 20;

    protected $fillable = [
        'id_recurso',
        'id_incidente_detalle',
        'nro_serie',
        'talle',
        'fecha_adquisicion',
        'fecha_vencimiento',
        'id_estado',
        'codigo_qr'
    ];

    /**
     * Recurso al que pertenece esta serie
     */
    public function recurso()
    {
        return $this->belongsTo(Recurso::class, 'id_recurso');
    }
    public function usuarioRecurso()
{
    return $this->hasOne(\App\Models\UsuarioRecurso::class, 'id_serie_recurso');
}



    /**
     * Incidente detalle asociado (si aplica)
     */
    public function incidenteDetalle()
    {
        return $this->belongsTo(\App\Models\IncidenteDetalle::class, 'id_incidente_detalle', 'id');
    }

    /**
     * Estado actual de la serie (Disponible, Prestado, etc.)
     */
    public function estado()
    {
        return $this->belongsTo(\App\Models\Estado::class, 'id_estado', 'id');
    }

    /**
     * Detalles de préstamos en los que participó esta serie
     */
    public function detallePrestamos()
    {
        return $this->hasMany(\App\Models\DetallePrestamo::class, 'id_serie', 'id');
    }
}
