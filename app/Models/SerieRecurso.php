<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class SerieRecurso
 *
 * @property int $id
 * @property int $id_recurso
 * @property int|null $id_serie_recurso_codigo
 * @property int|null $id_incidente_detalle
 * @property string|null $nro_serie
 * @property string|null $talle
 * @property int|null $id_talle
 * @property int|null $id_color
 * @property int|null $correlativo
 * @property string|null $fecha_adquisicion
 * @property string|null $fecha_vencimiento
 * @property int $id_estado
 * @property string|null $codigo_qr
 *
 * @property Recurso $recurso
 * @property SerieRecursoCodigo|null $codigo
 * @property IncidenteDetalle|null $incidenteDetalle
 * @property DetallePrestamo[] $detallePrestamos
 * @property Estado $estado
 * @property Color|null $color
 * @property Talle|null $talleModel
 * @property UsuarioRecurso[] $usuarioRecurso
 *
 * @package App\Models
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class SerieRecurso extends Model
{
    protected $with = ['estado'];
    protected $table = 'serie_recurso';
    protected $perPage = 20;

    protected $fillable = [
        'id_recurso',
        'id_serie_recurso_codigo',
        'id_incidente_detalle',
        'nro_serie',
        'talle',
        'id_talle',
        'id_color',
        'correlativo',
        'fecha_adquisicion',
        'fecha_vencimiento',
        'id_estado',
        'codigo_qr',
    ];

    protected $casts = [
        'id_recurso' => 'integer',
        'id_serie_recurso_codigo' => 'integer',
        'id_incidente_detalle' => 'integer',
        'id_talle' => 'integer',
        'id_color' => 'integer',
        'correlativo' => 'integer',
        'id_estado' => 'integer',
    ];

    public $timestamps = true;

    /**
     * Recurso al que pertenece esta serie
     */
    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class, 'id_recurso');
    }

    /**
     * Plantilla/metadata de la serie (versión, año, lote)
     */
    public function codigo(): BelongsTo
    {
        return $this->belongsTo(SerieRecursoCodigo::class, 'id_serie_recurso_codigo');
    }


    /**
     * Incidente detalle asociado (si aplica)
     */
    public function incidenteDetalle(): BelongsTo
    {
        return $this->belongsTo(IncidenteDetalle::class, 'id_incidente_detalle');
    }

    /**
     * Estado actual de la serie (Disponible, Prestado, etc.)
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_estado');
    }

    /**
     * Detalles de préstamos en los que participó esta serie
     */
    public function detallePrestamos(): HasMany
    {
        return $this->hasMany(DetallePrestamo::class, 'id_serie');
    }

    /**
     * Color asociado
     */
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'id_color');
    }

    /**
     * Relación con talle (si usás tabla talle)
     */
    public function talleModel(): BelongsTo
    {
        return $this->belongsTo(Talle::class, 'id_talle');
    }

    /**
     * Asignaciones de usuario (si usás usuario_recurso)
     */
    public function usuarioRecurso(): HasMany
    {
        return $this->hasMany(UsuarioRecurso::class, 'id_serie_recurso');
    }

    
}
