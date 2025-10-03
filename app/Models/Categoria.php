<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Categorium
 *
 * @property $id
 * @property $nombre_categoria
 *
 * @property Recurso[] $recursos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Categoria extends Model
{
    
    protected $perPage = 20;
    protected $table = 'categoria';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['nombre_categoria'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recursos()
    {
        return $this->hasMany(\App\Models\Recurso::class, 'id', 'id_categoria');
    }
    
}
