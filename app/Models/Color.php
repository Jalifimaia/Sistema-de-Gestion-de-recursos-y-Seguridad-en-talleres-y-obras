<?php
// app/Models/Color.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $table = 'color';
    protected $fillable = ['nombre'];
    public $timestamps = false;


    public function series()
    {
        return $this->hasMany(SerieRecurso::class, 'id_color');
    }
}
