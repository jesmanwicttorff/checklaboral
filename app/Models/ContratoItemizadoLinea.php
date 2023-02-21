<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoItemizadoLinea extends Model
{
    //
    protected $table = 'tbl_contrato_itemizado_lineas';
	protected $primaryKey = 'linea_id';

    public function contratoItemizado(){

        return $this->belongsTo('App\Models\ContratoItemizado', 'itemizado_id', 'itemizado_id');
    
    }

    public function itemizadoSublineas(){
        
        return $this->hasMany('App\Models\ContratoItemizadoSublinea', 'linea_id', 'linea_id');

    }
}
