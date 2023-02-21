<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class lodfolios extends Sximo  {
	
	protected $table = 'tbl_tickets';
	protected $primaryKey = 'IdTicket';

	public function __construct() {
		parent::__construct();
		
	}

	public function Tipos(){
		return $this->belongsTo('App\Models\tipolodfolios', 'IdTipo', 'IdTicketTipo');
	}

	public function Subtipos(){
		return $this->belongsTo('App\Models\subtipostemaslo', 'IdSubTipo', 'id');
	}

	public static function querySelect(  ){
		
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');
		return " SELECT  tbl_tickets.*,
        tbl_tickets_estatus.Descripcion as Estatus, 
		tbl_tickets_tipos.Descripcion as Tipo,
		tbl_tickets_subtipos.nombre as Categoria,
		tbl_tickets_tipos.Especial as TipoEspecial,
		tbl_contrato.cont_nombre,
		tbl_contrato.cont_numero,
		concat(tbl_contratistas.RUT, ' ',tbl_contratistas.RazonSocial) as Contratista,
		count(distinct(tbl_tickets_thread.IdTicketThread)) as Comentarios,
		count(distinct(tbl_tickets_thread.IdTicketThread))-ifnull(tbl_tickets_vistas.ThreadViews,0) as ThreadViews,
		tbl_tickets_notificacion.IdEstatus as Notificacion,
		concat(tb_users.first_name, ' - ', tb_users.last_name) as entry_by_name,
		tb_users.avatar,
		tbl_contrato.cont_estado
FROM tbl_tickets
INNER JOIN tbl_tickets_estatus ON tbl_tickets_estatus.IdEstatus= tbl_tickets.IdEstatus
INNER JOIN tbl_tickets_tipos ON tbl_tickets_tipos.IdTicketTipo = tbl_tickets.IdTipo
LEFT JOIN tbl_tickets_subtipos ON tbl_tickets_subtipos.id =  tbl_tickets.IdSubTipo
LEFT JOIN tb_users ON tb_users.id = tbl_tickets.entry_by
INNER JOIN tbl_contrato ON tbl_contrato.contrato_id = tbl_tickets.contrato_id
INNER JOIN tbl_contratistas ON tbl_contratistas.IdContratista = tbl_contrato.IdContratista
LEFT JOIN tbl_tickets_thread ON tbl_tickets_thread.IdTicket = tbl_tickets.IdTicket
LEFT JOIN (SELECT tbl_tickets_thread.IdTicket, COUNT(tbl_tickets_vistas.IdTicketThread) as ThreadViews
		   FROM tbl_tickets_thread
		   LEFT JOIN tbl_tickets_vistas ON tbl_tickets_thread.IdTicketThread = tbl_tickets_vistas.IdTicketThread AND tbl_tickets_vistas.entry_by = ".$lintIdUser."
		   GROUP BY tbl_tickets_thread.IdTicket) AS tbl_tickets_vistas ON tbl_tickets_vistas.IdTicket = tbl_tickets.IdTicket
LEFT JOIN tbl_tickets_notificacion ON tbl_tickets_notificacion.IdTicket = tbl_tickets.IdTicket AND tbl_tickets_notificacion.IdEstatus = 1 AND tbl_tickets_notificacion.entry_by = ".$lintIdUser."
		";
	
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tickets.IdTicket IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return " GROUP BY tbl_tickets.IdTicket, 
         tbl_tickets.IdTicketGroup, 
         tbl_tickets.contrato_id, 
         tbl_tickets.IdTipo,
         tbl_tickets.Titulo, 
         tbl_tickets.IdEstatus, 
         tbl_tickets.IdPrioridad, 
         tbl_tickets.createdOn, 
         tbl_tickets.updatedOn, 
         tbl_tickets.entry_by, 
         tbl_tickets_estatus.Descripcion, 
         tbl_tickets_tipos.Descripcion, 
         tbl_contrato.cont_nombre, 
         tbl_contrato.cont_numero,
         tbl_tickets_notificacion.IdEstatus,
         tbl_tickets_vistas.ThreadViews,
         concat(tb_users.first_name, ' ', tb_users.last_name),
		 tb_users.avatar,
		tbl_contrato.cont_estado  ";
	}
	

}
