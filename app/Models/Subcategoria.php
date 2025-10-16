<?php

namespace App\Models;
use App\Models\Recurso;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subcategoria extends Model
{
    protected $table = 'subcategoria';

    protected $fillable = ['nombre', 'categoria_id'];
    public $timestamps = false;

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
    public function recursos()
    {
        return $this->hasMany(Recurso::class, 'id_subcategoria');
    }
}
