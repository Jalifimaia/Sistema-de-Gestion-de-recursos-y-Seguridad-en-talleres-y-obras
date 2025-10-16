<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Usuario
 *
 * @property $id
 * @property $id_rol
 * @property $nombre_usuario
 * @property $email_usuario
 * @property $password_usuario
 * @property $fecha_creacion
 * @property $fecha_modificacion
 * @property $usuario_creacion
 * @property $usuario_modificacion
 *
 * @property Rol $rol
 * @property Incidente[] $incidentes
 * @property Incidente[] $incidentes
 * @property Prestamo[] $prestamos
 * @property Prestamo[] $prestamos
 * @property Prestamo[] $prestamos
 * @property Recurso[] $recursos
 * @property Recurso[] $recursos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Usuario extends Model
{
    
    protected $perPage = 20;
    protected $table = 'usuario';
    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['id_rol', 'nombre_usuario', 'email_usuario', 'password_usuario', 'fecha_creacion', 'fecha_modificacion', 'usuario_creacion', 'usuario_modificacion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rol()
        {
            return $this->belongsTo(\App\Models\Rol::class, 'id_rol', 'id');
        }
        public function trabajador() {
        return $this->belongsTo(Usuario::class, 'trabajador_id');
    }

    public function supervisor() {
        return $this->belongsTo(Usuario::class, 'supervisor_id');
    }

    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function incidentes()
    {
        return $this->hasMany(\App\Models\Incidente::class, 'usuario_creacion', 'id_usuario_creacion');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function incidente()
    {
        return $this->hasMany(\App\Models\Incidente::class, 'usuario_modificacion', 'id_usuario_modificacion');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prestamos()
    {
        return $this->hasMany(\App\Models\Prestamo::class, 'id', 'id_usuario');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prestamo()
    {
        return $this->hasMany(\App\Models\Prestamo::class, 'usuario_creacion', 'id_usuario_creacion');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prestamo1()
    {
        return $this->hasMany(\App\Models\Prestamo::class, 'usuario_modificacion', 'id_usuario_modificacion');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recursos()
    {
        return $this->hasMany(\App\Models\Recurso::class, 'usuario_creacion', 'id_usuario_creacion');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recurso()
    {
        return $this->hasMany(\App\Models\Recurso::class, 'usuario_modificacion', 'id_usuario_modificacion');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoUsuario::class, 'id_estado');
    }


    
}
