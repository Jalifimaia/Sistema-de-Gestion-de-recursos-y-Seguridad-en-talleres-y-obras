<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Prestamo
 *
 * @property $id
 * @property $id_usuario
 * @property $id_usuario_creacion
 * @property $id_usuario_modificacion
 * @property $fecha_prestamo
 * @property $fecha_devolucion
 * @property $estado
 * @property $fecha_creacion
 * @property $fecha_modificacion
 *
 * @property Usuario $usuario
 * @property Usuario $usuario
 * @property Usuario $usuario
 * @property DetallePrestamo[] $detallePrestamos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Prestamo extends Model
{
    protected $table = 'prestamo';
    protected $casts = [
    'fecha_prestamo' => 'datetime',
    'fecha_devolucion' => 'datetime',
];


    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['id_usuario', 'id_usuario_creacion', 'id_usuario_modificacion', 'fecha_prestamo', 'fecha_devolucion', 'estado', 'fecha_creacion', 'fecha_modificacion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario1()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario_creacion', 'usuario_creacion');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario2()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario_modificacion', 'usuario_modificacion');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detallePrestamos()
{
    return $this->hasMany(\App\Models\DetallePrestamo::class, 'id_prestamo', 'id');
}

    
}
