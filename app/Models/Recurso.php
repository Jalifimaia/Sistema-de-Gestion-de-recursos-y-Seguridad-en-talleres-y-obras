<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Recurso
 *
 * @property $id
 *  * @property-read Categoria $categoria
 * @property $id_incidente_detalle
 * @property $id_usuario_creacion
 * @property $id_usuario_modificacion
 * @property $nombre
 * @property $descripcion
 * @property $costo_unitario
 * @property $fecha_creacion
 * @property $fecha_modificacion
 *
 * @property Categoria $categoria
 * @property IncidenteDetalle $incidenteDetalle
 * @property Usuario $usuario
 * @property Usuario $usuario
 * @property DetallePrestamo[] $detallePrestamos
 * @property Incidente[] $incidentes
 * @property SerieRecurso[] $serieRecursos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Recurso extends Model
{
    
    protected $perPage = 20;
    protected $table = 'recurso';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['id_subcategoria','id_incidente_detalle', 'id_usuario_creacion', 'id_usuario_modificacion', 'nombre', 'descripcion', 'costo_unitario', 'fecha_creacion', 'fecha_modificacion', 'id_estado',];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoria(): HasOneThrough
{
    return $this->hasOneThrough(
        \App\Models\Categoria::class,
        \App\Models\Subcategoria::class,
        'id',              // Subcategoria.id
        'id',              // Categoria.id
        'id_subcategoria', // Recurso.id_subcategoria
        'categoria_id'     // Subcategoria.categoria_id
    );
}

    public function subcategoria()
{
    return $this->belongsTo(Subcategoria::class, 'id_subcategoria');
}

    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function incidenteDetalle()
    {
        return $this->belongsTo(\App\Models\IncidenteDetalle::class, 'id_incidente_detalle', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioCreacion()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario_creacion', 'usuario_creacion');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuarioModificacion()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'id_usuario_modificacion', 'usuario_modificacion');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detallePrestamos()
    {
        return $this->hasMany(\App\Models\DetallePrestamo::class, 'id', 'id_recurso');
    }
    
    public function estaDaniado(): bool
{
    return $this->incidentes()
        ->where('descripcion', 'like', '%dañado%')
        ->exists();
}


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function incidentes()
    {
        return $this->hasMany(\App\Models\Incidente::class, 'id', 'id_recurso');
    }

    public function estado(): BelongsTo
{
    return $this->belongsTo(\App\Models\Estado::class, 'id_estado');
}

    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function serieRecursos()
    {
        return $this->hasMany(SerieRecurso::class, 'id_recurso');
    }

    public function usuarioRecursos()
    {
        return $this->hasMany(UsuarioRecurso::class, 'id_recurso');
    }    
    public function series()
    {
        return $this->hasMany(SerieRecurso::class, 'id_recurso');
    }


    
}
