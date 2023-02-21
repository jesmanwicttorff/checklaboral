<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acciones extends Model
{
    protected $table = 'tbl_acciones';

    protected $primaryKey = 'IdAccion';

    protected $fillable = ['Nombre', 'Descripcion'];

    public $timestamps = false;
}
