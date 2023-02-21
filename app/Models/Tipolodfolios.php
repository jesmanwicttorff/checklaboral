<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class tipolodfolios extends Sximo  {
	
	protected $table = 'tbl_tickets_tipos';
	protected $primaryKey = 'IdTicketTipo';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		return "  SELECT tbl_tickets_tipos.IdTicketTipo,
        tbl_tickets_tipos.Descripcion,
        tbl_tickets_tipos.Especial,
        case when tbl_tickets_tipos.Especial = 1 then 'si' else 'no' end as EspecialDescripcion,
case when tbl_tickets_tipos.IdEstatus = 1 then 'Activo' else 'Inactivo' end as Estatus,       
 IdEstatus, 
        createdOn,
       entry_by
FROM tbl_tickets_tipos  ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tickets_tipos.IdTicketTipo IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
