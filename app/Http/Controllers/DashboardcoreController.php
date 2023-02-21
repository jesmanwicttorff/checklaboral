<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Dashboardcore;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 
use DB;


class DashboardcoreController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'dashboardcore';
	static $per_page	= '10';

	public function __construct()
	{
		
		parent::__construct();
		
		$this->model = new Dashboardcore();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'dashboardcore',
			'return'	=> self::returnUrl()
			
		);
		\App::setLocale(CNF_LANG);
		if (defined('CNF_MULTILANG') && CNF_MULTILANG == '1') {

		$lang = (\Session::get('lang') != "" ? \Session::get('lang') : CNF_LANG);
		\App::setLocale($lang);
		}  
		
		
	}

	public function getIndex( Request $request )
	{

		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

		
		$status = array();
		for($i=1; $i <= 5; $i++){
			$aux = DB::select(DB::raw('SELECT tbl_entidades.IdEntidad AS id, tbl_entidades.Entidad AS nombreEntidad,
			COUNT(tbl_documentos.Entidad) AS COUNT
			FROM tbl_entidades
			LEFT JOIN tbl_documentos ON  tbl_entidades.IdEntidad = tbl_documentos.Entidad AND tbl_documentos.idEstatus = '.$i.'
			GROUP BY 1'));
			array_push($status, $aux);
		}

		$listas = array(
			'pap' => DB::select(DB::raw('SELECT tbl_contratistas.RazonSocial, COUNT(tbl_documentos.IdDocumento) as val
							FROM tbl_contratistas
							LEFT JOIN tbl_contrato ON tbl_contratistas.IdContratista = tbl_contrato.IdContratista
							LEFT JOIN tbl_documentos ON tbl_documentos.contrato_id = tbl_contrato.contrato_id AND tbl_documentos.IdEstatus = 2
							GROUP BY 1')),
			'tmp' => DB::select(DB::raw('SELECT tbl_contratistas.RazonSocial, COUNT(tbl_documentos.IdDocumento) as val
							FROM tbl_contratistas
							LEFT JOIN tbl_contrato ON tbl_contratistas.IdContratista = tbl_contrato.IdContratista
							LEFT JOIN tbl_documentos ON tbl_documentos.contrato_id = tbl_contrato.contrato_id AND tbl_documentos.IdEstatus = 4
							GROUP BY 1')),
			'na' => DB::select(DB::raw('SELECT tbl_contratistas.RazonSocial, COUNT(tbl_documentos.IdDocumento) as val
							FROM tbl_contratistas
							LEFT JOIN tbl_contrato ON tbl_contratistas.IdContratista = tbl_contrato.IdContratista
							LEFT JOIN tbl_documentos ON tbl_documentos.contrato_id = tbl_contrato.contrato_id AND tbl_documentos.IdEstatus = 3
							GROUP BY 1'))
		);

		$vencePorCargar = DB::select(DB::raw('SELECT tbl_entidades.IdEntidad AS id, tbl_entidades.Entidad AS nombreEntidad,
						       COUNT(CASE WHEN DATEDIFF(CURRENT_DATE,tbl_documentos.createdOn) BETWEEN 0 AND 30 THEN tbl_documentos.Entidad END) AS f1,
						       COUNT(CASE WHEN DATEDIFF(CURRENT_DATE,tbl_documentos.createdOn) BETWEEN 31 AND 90 THEN tbl_documentos.Entidad END) AS f2,
						       COUNT(CASE WHEN DATEDIFF(CURRENT_DATE,tbl_documentos.createdOn) BETWEEN 91 AND 180 THEN tbl_documentos.Entidad END) AS f3
						FROM tbl_entidades
						LEFT JOIN tbl_documentos ON  tbl_entidades.IdEntidad = tbl_documentos.Entidad AND tbl_documentos.idEstatus = 1
						GROUP BY 1'));

			$chart = DB::select(DB::raw('SELECT nombre, COUNT(IdEstatus) as val FROM tbl_documentos a
								RIGHT JOIN

								(SELECT 1 categ, "Por cargar" nombre
								UNION ALL
								SELECT 2, "Por aprobar" nombre
								UNION ALL
								SELECT 3, "No aprobado" nombre
								UNION
								SELECT 4, "Temporal" nombre
								UNION
								SELECT 5, "Aprobado" nombre
								) AS t ON a.IdEstatus = t.categ

								GROUP BY 1'));
		return view('dashboardcore.index',$this->data)->with('status', $status)->with('listas', $listas)->with('vencePorCargar', $vencePorCargar)->with('chart', $chart);
	}	



	function getUpdate(Request $request, $id = null)
	{
	
		if($id =='')
		{
			if($this->access['is_add'] ==0 )
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
		}	
		
		if($id !='')
		{
			if($this->access['is_edit'] ==0 )
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
		}				
				
		$this->data['access']		= $this->access;
		return view('dashboardcore.form',$this->data);
	}	

	public function getShow( $id = null)
	{
	
		if($this->access['is_detail'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
					
		
		$this->data['access']		= $this->access;
		return view('dashboardcore.view',$this->data);	
	}	

	function postSave( Request $request)
	{
		
	
	}	

	public function postDelete( Request $request)
	{
		
		if($this->access['is_remove'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
		
	}			


}