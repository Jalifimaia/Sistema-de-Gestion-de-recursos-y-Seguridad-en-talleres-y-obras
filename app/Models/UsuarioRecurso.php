<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioRecurso extends Model
{
    protected $table = 'usuario_recurso';
    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function recurso()
    {
        return $this->belongsTo(Recurso::class, 'id_recurso');
    }
    
}
