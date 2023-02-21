<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblKpisTipo extends Model
{
    protected $primaryKey = 'IdTipo';
    protected $fillable = ['Nombre', 'RangoSuperior', 'RangoInferior', 'entry_by'];
}
