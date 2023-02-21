<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoPersonal extends Model
{
    //
    protected $table = 'tbl_movimiento_personal';
	protected $primaryKey = 'IdMovimientoPersonal';

    protected $fillable = ['IdAccion', 'contrato_id', 'IdPersona', 'entry_by', 'createdOn', 'FechaEfectiva', 'Motivo'];
    public $timestamps = false;
}
