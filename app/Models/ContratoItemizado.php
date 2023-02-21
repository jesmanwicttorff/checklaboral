<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoItemizado extends Model
{
    //
    protected $table = 'tbl_contrato_itemizado';
	protected $primaryKey = 'itemizado_id';
    
    public function contrato(){

        return $this->belongsTo('App\Models\Contrato', 'contrato_id', 'contrato_id');
    
    }
    public function  itemizadoLineas(){

        return $this->HasMany('App\Models\ContratoItemizadoLinea','itemizado_id', 'itemizado_id');
    
    }
    public function  moneda(){

        return $this->HasOne('App\Models\contratoItemizadoMoneda','moneda_id', 'moneda_id');
    
    }
    public function  etapas(){

        return $this->HasMany('App\Models\contratoItemizadoEtapaFlujo','itemizado_id', 'itemizado_id');
    
    }
}
