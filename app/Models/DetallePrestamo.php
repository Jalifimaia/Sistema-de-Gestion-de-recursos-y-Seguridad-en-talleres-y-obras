<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DetallePrestamo
 *
 * @property $id
 * @property $id_prestamo
 * @property $id_serie
 * @property $id_recurso
 * @property $id_estado_prestamo
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
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['id_prestamo', 'id_serie', 'id_recurso', 'id_estado_prestamo'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estadoPrestamo()
    {
        return $this->belongsTo(\App\Models\EstadoPrestamo::class, 'id_estado_prestamo', 'id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prestamo()
    {
        return $this->belongsTo(\App\Models\Prestamo::class, 'id_prestamo', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function serieRecurso()
    {
        return $this->belongsTo(\App\Models\SerieRecurso::class, 'id_serie', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recurso()
    {
        return $this->belongsTo(\App\Models\Recurso::class, 'id_recurso', 'id');
    }
    
}
