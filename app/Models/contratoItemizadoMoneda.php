<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class contratoItemizadoMoneda extends Model
{
    protected $table = 'tbl_contrato_itemizado_moneda';
	protected $primaryKey = 'moneda_id';
    
    public function contratoItemizado(){

        return $this->belongsTo('App\Models\ContratoItemizado', 'moneda_id', 'moneda_id');
    
    }
}
