<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class lodcomunicaciones extends Sximo  {
	
	protected $table = 'tbl_tickets_thread';
	protected $primaryKey = 'IdTicketThread';

	public function __construct() {
		parent::__construct();
		
	}

	public static function querySelect(  ){
		
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');
		return "  SELECT tbl_tickets_thread.*, concat(tb_users.first_name, ' ', tb_users.last_name) as entry_by_name, tb_users.avatar, tbl_tickets_vistas.IdTicketVista, tb_groups.name as groupname
		FROM tbl_tickets_thread
		LEFT JOIN tb_users ON tbl_tickets_thread.entry_by = tb_users.id
		LEFT JOIN tb_groups ON tb_users.group_id = tb_groups.group_id
		LEFT JOIN tbl_tickets_vistas ON tbl_tickets_vistas.IdTicketThread = tbl_tickets_thread.IdTicketThread AND tbl_tickets_vistas.entry_by = ".$lintIdUser." ";
	}	

	public static function queryWhere(  ){
		
		return "  WHERE tbl_tickets_thread.IdTicketThread IS NOT NULL ";
	}
	
	public static function queryGroup(){
		return "  ";
	}
	

}
