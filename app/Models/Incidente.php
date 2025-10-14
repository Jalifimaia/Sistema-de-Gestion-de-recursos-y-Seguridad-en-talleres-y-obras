<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidente extends Model
{
    use HasFactory;

    protected $table = 'incidente';

    protected $fillable = [
        'id_recurso',
        'id_supervisor',
        'id_incidente_detalle',
        'id_usuario_creacion',
        'id_usuario_modificacion',
        'descripcion',
        'fecha_incidente',
        'fecha_creacion',
        'fecha_modificacion',
        'fecha_cierre_incidente',
        'resolucion',
        'id_estado_incidente'
    ];

    public $timestamps = false; // Ya que usas fechas manualmente

    // 🔹 Usuario que creó el incidente
    public function usuarioCreacion()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_creacion');
    }

    // 🔹 Usuario que modificó el incidente
    public function usuarioModificacion()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_modificacion');
    }

    // 🔹 Recurso afectado
   public function recurso() {
    return $this->belongsTo(Recurso::class, 'id_recurso');
    }

    public function estadoIncidente() {
        return $this->belongsTo(EstadoIncidente::class, 'id_estado_incidente');
    }

    // 🔹 Detalles del incidente (si los hay)
    public function detalles()
    {
        return $this->hasMany(IncidenteDetalle::class, 'id_incidente');
    }
    public function categoria() {
    return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function subcategoria() {
        return $this->belongsTo(Subcategoria::class, 'id_subcategoria');
    }
        public function serieRecurso()
    {
        return $this->belongsTo(\App\Models\SerieRecurso::class, 'id_serie_recurso');
    }


   
}
