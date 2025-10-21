<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DetallePrestamo
 *
 * @property int $id
 * @property int $id_prestamo
 * @property int $id_serie
 * @property int $id_recurso
 * @property int $id_estado_prestamo
 *
 * @property Prestamo $prestamo
 * @property SerieRecurso $serieRecurso
 * @property Recurso $recurso
 * @property EstadoPrestamo $estadoPrestamo
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class DetallePrestamo extends Model
{
    protected $table = 'detalle_prestamo';
    public $timestamps = false; // 👈 importante, tu tabla no tiene created_at/updated_at

    protected $perPage = 20;

    protected $fillable = [
        'id_prestamo',
        'id_serie',
        'id_recurso',
        'id_estado_prestamo',
    ];

    /**
     * Estado del detalle (Asignado, Devuelto, etc.)
     */
    public function estadoPrestamo()
    {
        return $this->belongsTo(\App\Models\EstadoPrestamo::class, 'id_estado_prestamo', 'id');
    }

    /**
     * Préstamo al que pertenece este detalle
     */
    public function prestamo()
    {
        return $this->belongsTo(\App\Models\Prestamo::class, 'id_prestamo', 'id');
    }

    /**
     * Serie de recurso asociada
     */
    public function serieRecurso()
    {
        return $this->belongsTo(\App\Models\SerieRecurso::class, 'id_serie', 'id');
    }

    /**
     * Recurso asociado
     */
    public function recurso()
    {
        return $this->belongsTo(\App\Models\Recurso::class, 'id_recurso', 'id');
    }
}
