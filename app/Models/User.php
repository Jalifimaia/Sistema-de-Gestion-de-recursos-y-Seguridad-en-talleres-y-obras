<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario'; // Laravel buscará esta tabla en lugar de 'users'

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_rol',
        'usuario_creacion',
        'usuario_modificacion',
        'ultimo_acceso',
        'id_estado',
        'dni',
        'auth_key',
        'access_token',
        'codigo_qr',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ultimo_acceso' => 'datetime',
        ];
    }

    // Relaciones
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoUsuario::class, 'id_estado');
    }

    public function incidentesCreados()
    {
        return $this->hasMany(Incidente::class, 'id_usuario_creacion', 'id');
    }

    public function incidentesModificados()
    {
        return $this->hasMany(Incidente::class, 'id_usuario_modificacion', 'id');
    }

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'id_usuario', 'id');
    }

    public function prestamosCreados()
    {
        return $this->hasMany(Prestamo::class, 'usuario_creacion', 'id');
    }

    public function prestamosModificados()
    {
        return $this->hasMany(Prestamo::class, 'usuario_modificacion', 'id');
    }

    public function recursosCreados()
    {
        return $this->hasMany(Recurso::class, 'id_usuario_creacion', 'id');
    }

    public function recursosModificados()
    {
        return $this->hasMany(Recurso::class, 'id_usuario_modificacion', 'id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'usuario_creacion');
    }

    public function modificador()
    {
        return $this->belongsTo(User::class, 'usuario_modificacion');
    }

    public function usuarioRecursos()
{
    return $this->hasMany(\App\Models\UsuarioRecurso::class, 'id_usuario', 'id');
}

}
