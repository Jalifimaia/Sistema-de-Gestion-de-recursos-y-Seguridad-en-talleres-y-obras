<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SerieRecursoCodigo extends Model
{
    protected $table = 'serie_recurso_codigo';

    protected $fillable = [
        'id_recurso',
        'version',
        'anio',
        'lote',
    ];

    public $timestamps = true;

    protected $casts = [
        'version' => 'integer',
        'anio'    => 'integer',
        'lote'    => 'integer',
    ];

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class, 'id_recurso');
    }

    public function series(): HasMany
    {
        return $this->hasMany(SerieRecurso::class, 'id_serie_recurso_codigo');
    }
}
