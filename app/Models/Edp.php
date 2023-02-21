<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Edp extends Model
{
    //
    protected $table = 'tbl_contrato_edp';
	protected $primaryKey = 'edp_id';

    public function contrato(){

        return $this->belongsTo('App\Models\Contratos','edp_id','contrato_id');
    
    }

    public function edpSublineas(){
    
        return $this->HasMany('App\Models\contratoEdpSublineas','edp_id','edp_id');
    }
}
