<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class contratoItemizadoUnidadesMedida extends Model
{
    //

    protected $table = 'tbl_contrato_itemizado_unidades_medida';
	protected $primaryKey = 'medida_id';
    
    public function contratoItemizadoSublineas(){

        return $this->belongsTo('App\Models\ContratoItemizadoSublinea','unidadMedidad_id','medida_id');
    
    }
}
