<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class lod extends Sximo  {
	
	protected $table = 'tbl_tickets';
	protected $primaryKey = 'IdTicket';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		$lstrResultado = "";
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');
		return " select tbl_contrato.contrato_id, 
					    tbl_contrato.IdContratista, 
					    concat(tbl_contratistas.RUT, ' ',tbl_contratistas.RazonSocial) as Contratista,
					    tbl_contrato.cont_nombre, 
					    tbl_contrato.cont_numero,
					    tbl_contrato.cont_estado,
					    tbl_contrato_estatus.nombre as cont_estado_desc,
		 			    cont_fechaInicio,
					    cont_fechaFin,
					    tbl_tickets.countAbirtos,
					    tbl_tickets.countCerrados,
					    (tbl_tickets.countAbirtos + tbl_tickets.countCerrados) as countTotal,
					    tbl_tickets_notificacion.countNotificacion,
					    ifnull(tbl_tickets.FechaActualizacion,'0000-00-00') as FechaActualizacion,
				        tbl_tickets.Thread,
				        tbl_tickets.ThreadViews,
				        tbl_tickets.Thread-tbl_tickets.ThreadViews as ThreadNotViews
				 from tbl_contrato
				 inner join tbl_contratistas on tbl_contratistas.IdContratista = tbl_contrato.IdContratista
				 inner join tbl_contrato_estatus on tbl_contrato_estatus.id = tbl_contrato.cont_estado
				 left join (select tbl_tickets.contrato_id, count(tbl_tickets_notificacion.IdTicketNotificacion) as countNotificacion
				 		    from tbl_tickets
						    left join tbl_tickets_notificacion on tbl_tickets.IdTicket = tbl_tickets_notificacion.IdTicket and tbl_tickets_notificacion.IdEstatus = 1 and tbl_tickets_notificacion.entry_by = ".$lintIdUser."
						    group by tbl_tickets.contrato_id) as tbl_tickets_notificacion on tbl_tickets_notificacion.contrato_id = tbl_contrato.contrato_id
				 left join (SELECT tbl_tickets.contrato_id,
								   COUNT(tbl_tickets_thread.IdTicketThread) as Thread, 
								   COUNT(DISTINCT(tbl_tickets_vistas.IdTicketThread)) as ThreadViews,
								   COUNT(DISTINCT(case when tbl_tickets.IdEstatus = 1 then tbl_tickets.IdTicket end)) as countAbirtos,
								   COUNT(DISTINCT(case when tbl_tickets.IdEstatus = 2 then tbl_tickets.IdTicket end)) as countCerrados,
				                   max(tbl_tickets.updatedOn) as FechaActualizacion
							FROM tbl_tickets
							LEFT JOIN tbl_tickets_thread ON tbl_tickets.IdTicket = tbl_tickets_thread.IdTicket
							LEFT JOIN tbl_tickets_vistas ON tbl_tickets_thread.IdTicketThread = tbl_tickets_vistas.IdTicketThread AND tbl_tickets_vistas.entry_by = ".$lintIdUser."
							GROUP BY tbl_tickets.contrato_id) as tbl_tickets on tbl_tickets.contrato_id = tbl_contrato.contrato_id ";

	}	

	public static function queryWhere(  ){
	    
        return "  WHERE tbl_contrato.contrato_id IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
