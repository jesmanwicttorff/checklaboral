<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoItemizadoSublinea extends Model
{
    //
    protected $table = 'tbl_contrato_itemizado_sublineas';
	protected $primaryKey = 'sublinea_id';


    public function  unidad(){

        return $this->HasOne('App\Models\contratoItemizadoUnidadesMedida','medida_id', 'unidadMedidad_id');
    
    }

    public function edpSublinea(){
        
        return $this->HasOne('App\Models\contratoEdpSublineas','itemizado_sublinea_id','sublinea_id');
    }
    
}
