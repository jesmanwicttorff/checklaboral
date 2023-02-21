<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contratoscentrocosto extends Model
{
    protected $table = 'tbl_contratos_centrocosto';

    protected $fillable = ['contrato_id', 'centrocosto_id',];

}
