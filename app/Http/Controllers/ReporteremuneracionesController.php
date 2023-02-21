<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Reporteremuneraciones;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect, DB ;


class ReporteremuneracionesController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'reporteremuneraciones';
	static $per_page	= '10';

	public function __construct()
	{

		parent::__construct();
		
		$this->model = new Reporteremuneraciones();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reporteremuneraciones',
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

				$real = DB::select(DB::raw('SELECT COALESCE(sum(rea.cantidad), 0) AS Q_Real, COALESCE(sum(rea.prom_remuneracion*rea.cantidad), 0) AS Rem_Real
				FROM tbl_remuneraciones_mensual AS rea
				LEFT JOIN tbl_contrato as cnt ON cnt.contrato_id LIKE rea.contrato_id
				WHERE  YEAR(rea.periodo) = YEAR(CURDATE())
				AND MONTH(rea.periodo) <= MONTH(CURDATE())
				UNION ALL
				SELECT COALESCE(sum(rea.cantidad), 0) AS Q_Real, COALESCE(sum(rea.prom_remuneracion), 0) AS Rem_Real
				FROM tbl_remuneraciones_mensual AS rea
				LEFT JOIN tbl_contrato as cnt ON cnt.contrato_id LIKE rea.contrato_id
				WHERE YEAR(rea.periodo) = YEAR(CURDATE())
				AND MONTH(rea.periodo) = MONTH(CURDATE())'));

				$ppto = DB::select(DB::raw('SELECT
		          COALESCE(SUM(ppto.cantidad)*MONTH(CURDATE()), 0) AS Q_Ppto,
		          COALESCE(SUM(ppto.prom_remuneracion * ppto.cantidad * MONTH(CURDATE())),
		                  0) Rem_Ppto
		      FROM
		          tbl_remuneraciones_ppto AS ppto
		              LEFT JOIN
		          tbl_contrato AS cnt ON cnt.contrato_id LIKE ppto.contrato_id

		      UNION ALL
		      SELECT
		          COALESCE(SUM(ppto.cantidad), 0) AS Q_Ppto,
		          COALESCE(SUM(ppto.prom_remuneracion * ppto.cantidad),
		                  0) Rem_Ppto
		      FROM
		          tbl_remuneraciones_ppto AS ppto
		              LEFT JOIN
		          tbl_contrato AS cnt ON cnt.contrato_id LIKE ppto.contrato_id'));

				$evo_real = DB::select(DB::raw('SELECT a.contrato_id,
				a.cont_fechaInicio,
				a.cont_fechaFin,
				b.periodo,
				COALESCE(SUM(b.cantidad), 0) AS cantidad_real,
				COALESCE(SUM(b.prom_remuneracion * b.cantidad)/SUM(b.cantidad), 0) AS media_real

				FROM tbl_contrato AS a
				LEFT JOIN tbl_remuneraciones_mensual AS b ON b.contrato_id = a.contrato_id
				GROUP BY b.periodo'));

				$evo_ppto = DB::select(DB::raw('SELECT COALESCE(sum(cantidad), 0) as cantidad, COALESCE(sum(prom_remuneracion * cantidad)/sum(cantidad), 0) as media FROM tbl_remuneraciones_ppto;'));

				$cttos = DB::select(DB::raw('SELECT b.contrato_id, b.cont_nombre, b.cont_proveedor, COALESCE(SUM(a.cantidad), 0) as cant, COALESCE(SUM(a.prom_remuneracion), 0) as remun
				FROM tbl_remuneraciones_mensual AS a
				LEFT JOIN tbl_contrato AS b ON b.contrato_id = a.contrato_id
				GROUP BY b.contrato_id'));

		return view('reporteremuneraciones.index',$this->data)->with('real', $real)->with('ppto', $ppto)->with('evo_real', $evo_real)->with('cttos', $cttos)->with('evo_ppto', $evo_ppto);
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
		return view('reporteremuneraciones.form',$this->data);
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$this->data['access']		= $this->access;
		return view('reporteremuneraciones.view',$this->data);
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
