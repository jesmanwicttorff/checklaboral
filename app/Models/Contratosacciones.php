<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contratosacciones extends Model
{
    protected $table = 'tbl_contratos_acciones';
    protected $fillable = ['contrato_id', 'accion_id', 'observaciones','entry_by'];
    
    public $timestamps = false;
    const CREATED_AT = 'createdOn';

}
