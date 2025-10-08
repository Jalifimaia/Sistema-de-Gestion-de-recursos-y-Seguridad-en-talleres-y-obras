<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EstadoPrestamo
 *
 * @property $id
 * @property $nombre
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class EstadoPrestamo extends Model
{
    
    protected $table = 'estado_prestamo';

    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['nombre'];


}
