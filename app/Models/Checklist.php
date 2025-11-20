<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Checklist extends Model
{
    use HasFactory;

    // Si tu tabla se llama "checklist" y no "checklists"
    protected $table = 'checklist';

    // Clave primaria
    protected $primaryKey = 'id';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
    'trabajador_id',
    'supervisor_id',
    'anteojos',
    'botas',
    'chaleco',
    'guantes',
    'arnes',
    'es_en_altura',
    'fecha',
    'hora',
    'observaciones',
    'critico',
];


    // Cast automático de booleanos
    protected $casts = [
        'anteojos' => 'boolean',
        'botas' => 'boolean',
        'chaleco' => 'boolean',
        'guantes' => 'boolean',
        'arnes' => 'boolean',
        'es_en_altura' => 'boolean',
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'critico' => 'boolean',
    ];

    // Relaciones
    public function trabajador()
    {
        return $this->belongsTo(Usuario::class, 'trabajador_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Usuario::class, 'supervisor_id');
    }
}
