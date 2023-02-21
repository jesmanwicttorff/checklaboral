<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblContratosEstado extends Model
{
    protected $primaryKey = 'IdEstado';
    protected $fillable = ['Descripcion','entry_by'];
    
}
