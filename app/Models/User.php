<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'usuario'; // Laravel buscará esta tabla en lugar de 'users'
//public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_rol',
        'usuario_creacion',
        'usuario_modificacion',
        'ultimo_acceso',
        'id_estado',
        'fecha_nacimiento',
        'dni',
        'telefono',
        'nro_legajo',
        'auth_key',
        'access_token',
    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'ultimo_acceso' => 'datetime', // ← esto es lo que faltaba
    ];
}


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
    return $this->hasMany(Incidente::class, 'id_usuario_creacion', 'id'); // ✅ correcto
}


public function incidentesModificados()
{
    return $this->hasMany(Incidente::class, 'id_usuario_modificacion', 'id'); // ✅ correcto
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
    return $this->hasMany(Recurso::class, 'id_usuario_creacion', 'id'); // ✅ correcto
}


public function recursosModificados()
{
    return $this->hasMany(Recurso::class, 'id_usuario_modificacion', 'id');
}




}
