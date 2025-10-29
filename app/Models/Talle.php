<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Talle extends Model
{
    protected $table = 'talle';

    protected $fillable = [
        'tipo',
        'nombre',
    ];

    public $timestamps = true;

    public function series(): HasMany
    {
        return $this->hasMany(SerieRecurso::class, 'id_talle');
    }
}
