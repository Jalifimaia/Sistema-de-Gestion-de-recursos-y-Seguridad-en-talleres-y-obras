<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Rol
 *
 * @property $id
 * @property $nombre_rol
 *
 * //@property Registrousuario[] $registrousuarios
 * @property Usuario[] $usuarios
 * @property Usuario[] $usuarios
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Rol extends Model
{
    
    protected $perPage = 20;
    protected $table = 'rol';


    protected $fillable = ['nombre_rol'];

    /*public function registrousuarios()
    {
        return $this->hasMany(\App\Models\Registrousuario::class, 'id_rol', 'id');
    }*/

    public function usuarios()
    {
        return $this->hasMany(\App\Models\Usuario::class, 'id_rol', 'id');
    }
    
}
