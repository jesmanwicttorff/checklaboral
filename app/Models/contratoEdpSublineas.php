<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class contratoEdpSublineas extends Model
{
    //
    protected $table = 'tbl_contrato_edp_sublineas';
	protected $primaryKey = 'edp_sublinea_id';

    public function ItemizadoSublinea(){

        return $this->belongsTo('App\Models\ContratoItemizadoSublinea', 'sublinea_id', 'itemizado_sublinea_id');
    
    }

    public function edp(){
        
        return $this->belongsTo('App\Models\Edp','edp_id','edp_id');
    }

   
}
