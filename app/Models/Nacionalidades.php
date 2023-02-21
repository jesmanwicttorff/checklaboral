<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nacionalidades extends Model
{
    protected $table = 'tbl_nacionalidad';
	protected $primaryKey = 'id_Nac';

    public function Persona()
    {
        return $this->belongsTo('App\Models\Personas', 'id_Nac', 'id_Nac');
    }
}
