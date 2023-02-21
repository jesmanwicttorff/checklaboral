<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblConfiguracion extends Model
{
    protected $table = 'tbl_configuraciones';
    protected $primaryKey = 'IdConfiguracion';
    protected $fillable = ['Nombre', 'Descripcion', 'Valor', 'entry_by'];

}
