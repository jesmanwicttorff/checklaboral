<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblDashboard extends Model
{
    //
    protected $table = 'tbl_dashboard';

    public function Grupos() {
    	return $this->hasMany('App\Models\TbGroup','IdDashboard', 'id');
    }

    public function scopeVista($query, $lstrVista) {

		return $query->where("vista",$lstrVista);
    }
}
