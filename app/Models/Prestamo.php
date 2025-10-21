<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Prestamo
 *
 * @property int $id
 * @property int $id_usuario
 * @property int $id_usuario_creacion
 * @property int $id_usuario_modificacion
 * @property string $fecha_prestamo
 * @property string|null $fecha_devolucion
 * @property int $estado
 * @property string $fecha_creacion
 * @property string $fecha_modificacion
 *
 * @property Usuario $usuario
 * @property Usuario $creador
 * @property Usuario $modificador
 * @property DetallePrestamo[] $detallePrestamos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Prestamo extends Model
{
    protected $table = 'prestamo';
    public $timestamps = false; // 👈 importante, porque la tabla no tiene created_at/updated_at

    protected $perPage = 20;

    protected $fillable = [
        'id_usuario',
        'id_usuario_creacion',
        'id_usuario_modificacion',
        'fecha_prestamo',
        'fecha_devolucion',
        'estado',
        'fecha_creacion',
        'fecha_modificacion',
    ];

    /**
     * Trabajador al que se asigna el préstamo
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario', 'id');
    }

    /**
     * Usuario que creó el préstamo (admin o trabajador en terminal)
     */
    public function creador()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario_creacion', 'id');
    }

    /**
     * Último usuario que modificó el préstamo
     */
    public function modificador()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario_modificacion', 'id');
    }

    /**
     * Detalles del préstamo (series asignadas)
     */
    public function detallePrestamos()
    {
        return $this->hasMany(\App\Models\DetallePrestamo::class, 'id_prestamo', 'id');
    }

    public function detalles()
{
    return $this->hasMany(\App\Models\DetallePrestamo::class, 'id_prestamo');
}

}
