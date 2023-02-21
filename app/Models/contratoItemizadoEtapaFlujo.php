<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class contratoItemizadoEtapaFlujo extends Model
{
    //
    protected $table = 'tbl_contrato_itemizado_etapas_flujo';
	protected $primaryKey = 'itemizado_id';

    public function contratoItemizado(){

        return $this->belongsTo('App\Models\ContratoItemizado', 'itemizado_id', 'itemizado_id');
    
    }
}
