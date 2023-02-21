<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblUnidad extends Model
{
    //
    protected $table = 'tbl_unidades';
	protected $primaryKey = 'IdUnidad';
	protected $fillable = ['Descripcion', 'Abreviacion'];

}
