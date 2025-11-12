<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario'; // 👈 importante: apunta a tu tabla real
    public $timestamps = false;   // tu tabla no usa created_at/updated_at

    protected $fillable = [
        'id_rol',
        'nombre_usuario',
        'email_usuario',
        'password_usuario',
        'fecha_creacion',
        'fecha_modificacion',
        'usuario_creacion',
        'usuario_modificacion',
        'id_estado',
    ];

    // =========================
    // Relaciones principales
    // =========================

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoUsuario::class, 'id_estado', 'id');
    }

    // =========================
    // Relaciones con Checklist
    // =========================
    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'trabajador_id', 'id');
    }

    // =========================
    // Relaciones con Incidentes
    // =========================
    public function incidentes()
    {
        return $this->hasMany(Incidente::class, 'id_trabajador', 'id');
    }

    // =========================
    // Relaciones con Préstamos
    // =========================
    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'id_usuario', 'id');
    }

    // Si querés también saber quién creó o modificó préstamos:
    public function prestamosCreados()
    {
        return $this->hasMany(Prestamo::class, 'id_usuario_creacion', 'id');
    }

    public function prestamosModificados()
    {
        return $this->hasMany(Prestamo::class, 'id_usuario_modificacion', 'id');
    }

    // =========================
    // Recursos asignados
    // =========================
    public function usuarioRecursos()
    {
        return $this->hasMany(UsuarioRecurso::class, 'id_usuario', 'id');
    }
}
