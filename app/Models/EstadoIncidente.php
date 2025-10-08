<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoIncidente extends Model
{
    protected $table = 'estado_incidente';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];
}
