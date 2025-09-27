<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Epp
 *
 * @property $id
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Epp extends Model
{
    
    protected $perPage = 20;
     protected $table = 'epp';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['nombre', 'descripcion'];


}
