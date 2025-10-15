<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidente extends Model
{
    use HasFactory;

    protected $table = 'incidente';

    protected $fillable = [
        'id_trabajador',
        'id_supervisor',
        'id_recurso',
        'id_serie_recurso',
        'id_incidente_detalle',
        'id_usuario_creacion',
        'id_usuario_modificacion',
        'descripcion',
        'fecha_incidente',
        'fecha_creacion',
        'fecha_modificacion',
        'fecha_cierre_incidente',
        'resolucion',
        'id_estado_incidente',
    ];

    public $timestamps = false; // Usás fechas manualmente

    // 🔹 Trabajador que sufrió el incidente
    public function trabajador()
    {
        return $this->belongsTo(Usuario::class, 'id_trabajador');
    }

    // 🔹 Supervisor que registró o gestionó el incidente
    public function supervisor()
    {
        return $this->belongsTo(Usuario::class, 'id_supervisor');
    }

    // 🔹 Usuario que creó el registro
    public function usuarioCreacion()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_creacion');
    }

    // 🔹 Usuario que modificó el registro
    public function usuarioModificacion()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_modificacion');
    }

    // 🔹 Recurso afectado
    public function recurso()
    {
        return $this->belongsTo(Recurso::class, 'id_recurso');
    }

    // 🔹 Serie del recurso
public function serieRecurso()
{
    return $this->belongsTo(SerieRecurso::class, 'id_serie_recurso');
}

public function recursos()
{
    return $this->belongsToMany(Recurso::class, 'incidente_recurso', 'id_incidente', 'id_recurso')
                ->withPivot('id_serie_recurso'); // 👈 sin withTimestamps()
}



    // 🔹 Estado del incidente
    public function estadoIncidente()
    {
        return $this->belongsTo(EstadoIncidente::class, 'id_estado_incidente');
    }

    // 🔹 Detalles adicionales del incidente
    public function detalles()
    {
        return $this->hasMany(IncidenteDetalle::class, 'id_incidente');
    }

    // 🔹 Categoría (a través del recurso)
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    // 🔹 Subcategoría (a través del recurso)
    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class, 'id_subcategoria');
    }
}
