<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioRecurso extends Model
{
    protected $fillable = [
    'id_usuario',
    'id_serie_recurso',
    'id_recurso',
    'fecha_asignacion',
    ];


    protected $table = 'usuario_recurso';
    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }


    public function serie()
{
    return $this->belongsTo(SerieRecurso::class, 'id_serie_recurso');
}


    public function recurso()
    {
        return $this->belongsTo(Recurso::class, 'id_recurso');
    }
    
}
