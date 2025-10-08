<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenteDetalle extends Model
{
    public function incidente()
{
    return $this->belongsTo(Incidente::class, 'id_incidente');
}

    protected $table = 'incidente_detalle';

    protected $fillable = [
        'id_incidente',
        'id_serie',
        'descripcion',
    ];

    public $timestamps = false; // si tu tabla no tiene created_at / updated_at
}
