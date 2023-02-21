<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Dashboardcontratistas;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use DB;


class DashboardcontratistasController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'dashboardcontratistas';
	static $per_page	= '10';

	public function __construct()
	{
		
		parent::__construct();
		
		$this->model = new Dashboardcontratistas();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'dashboardcontratistas',
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


		$data = DB::select(DB::raw('SELECT COUNT(DISTINCT a.IdContratista) total_cont,
                COUNT(DISTINCT CASE WHEN b.contrato_id IS NULL AND a.riesgo = 1 THEN a.IdContratista END ) sin_alto,
                COUNT(DISTINCT CASE WHEN b.contrato_id IS NULL AND a.riesgo = 0 THEN a.IdContratista END ) sin_bajo,
                COUNT(DISTINCT CASE WHEN b.contrato_id IS NOT NULL  AND a.riesgo = 1 THEN a.IdContratista END ) con_alto,
                COUNT(DISTINCT CASE WHEN b.contrato_id IS NOT NULL AND a.riesgo = 0 THEN a.IdContratista END ) con_bajo,
                COUNT(DISTINCT CASE WHEN b.contrato_id IS NULL AND d.IdEntidad IS  NULL THEN a.IdContratista END ) sin_completo,
                COUNT(DISTINCT CASE WHEN b.contrato_id IS NULL AND d.IdEntidad IS  NOT NULL THEN a.IdContratista END ) sin_incompleto,
                COUNT(DISTINCT CASE WHEN b.contrato_id IS NOT NULL AND d.IdEntidad IS  NULL THEN a.IdContratista END ) con_completo,
                COUNT(DISTINCT CASE WHEN b.contrato_id IS NOT NULL AND d.IdEntidad IS  NOT NULL THEN a.IdContratista END ) con_incompleto,
								COUNT(DISTINCT CASE WHEN a.tamano = 0 THEN a.IdContratista END ) pyme,
								COUNT(DISTINCT CASE WHEN a.tamano = 1 THEN a.IdContratista END ) peq,
								COUNT(DISTINCT CASE WHEN a.tamano = 2 THEN a.IdContratista END ) med,
								COUNT(DISTINCT CASE WHEN a.tamano = 3 THEN a.IdContratista END ) gran


				FROM tbl_contratistas a
				LEFT JOIN tbl_contrato b ON a.IdContratista = b.IdContratista
				LEFT JOIN (SELECT IdEntidad FROM tbl_documentos WHERE Entidad = 1 AND IdEstatus != 5) d ON a.IdContratista = d.IdEntidad
				'));

		$distr = DB::select(DB::raw('SELECT r.id,
							COUNT(DISTINCT ca.IdContratista) AS count
					
							FROM dim_region r
							LEFT JOIN tbl_centro c ON r.id = c.IdRegion
							LEFT JOIN tbl_contratos_centros cc ON cc.IdCentro = c.IdCentro
							LEFT JOIN tbl_contrato co ON cc.contrato_id = co.contrato_id
							LEFT JOIN tbl_contratistas ca ON co.IdContratista = ca.IdContratista
		GROUP BY 1'));

		$impData = array();
		foreach($distr as $distr){
			array_push($impData, $distr->count);
		}
		$impData = implode(', ', $impData);

		$cat = DB::select(DB::raw('SELECT cc.id_categoria,
		COUNT(DISTINCT ca.IdContratista) AS valor

		FROM tbl_contratista_categoria cc 
		LEFT JOIN (SELECT co.contrato_id,ca.IdContratista,id_categoria FROM tbl_contrato co 
							INNER JOIN tbl_contratistas ca ON co.IdContratista = ca.IdContratista) ca 
							
							ON cc.id_categoria = ca.id_categoria
		GROUP BY 1'));

		$impData2 = array();
		foreach($cat as $cat){
			array_push($impData2, $cat->valor);
		}
		$impData2 = implode(', ', $impData2);

		return view('dashboardcontratistas.index',$this->data)->with('data', $data)->with('distr', $impData)->with('cat', $impData2);
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
		return view('dashboardcontratistas.form',$this->data);
	}	

	public function getShow( $id = null)
	{
	
		if($this->access['is_detail'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
					
		
		$this->data['access']		= $this->access;
		return view('dashboardcontratistas.view',$this->data);	
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