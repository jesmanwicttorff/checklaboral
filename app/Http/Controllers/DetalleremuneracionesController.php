<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Detalleremuneraciones;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect, DB ;


class DetalleremuneracionesController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'detalleremuneraciones';
	static $per_page	= '10';

	public function __construct()
	{

		parent::__construct();
		
		$this->model = new Detalleremuneraciones();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'detalleremuneraciones',
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

		$data = $request->request->all();
		if (isset($data) && $data['id'] > 0){
			$real = DB::select(DB::raw('SELECT COALESCE(sum(rea.cantidad), 0) AS Q_Real, COALESCE(sum(rea.prom_remuneracion*rea.cantidad), 0) AS Rem_Real
			FROM tbl_remuneraciones_mensual AS rea
			WHERE rea.contrato_id = '.$data['id'].'
			AND
			CASE
					WHEN
						'.$data['year'].' = 0
					THEN
						rea.periodo <= CURDATE()
					ELSE
						CASE
								WHEN
									'.$data['mes'].' = 0
								THEN
									(MONTH(rea.periodo) <= 12 AND YEAR(rea.periodo) = '.$data['year'].')
								ELSE
									(MONTH(rea.periodo) <= '.$data['mes'].' AND YEAR(rea.periodo) = '.$data['year'].')
						END
			END
			UNION ALL
			SELECT COALESCE(sum(rea.cantidad), 0) AS Q_Real, COALESCE(sum(rea.prom_remuneracion), 0) AS Rem_Real
			FROM tbl_remuneraciones_mensual AS rea
			WHERE rea.contrato_id = '.$data['id'].'
			AND
			CASE
					WHEN
						'.$data['year'].' = 0
					THEN
						(MONTH(rea.periodo) = MONTH(CURDATE()) AND YEAR(rea.periodo) = YEAR(CURDATE()))
					ELSE
						CASE
								WHEN
									'.$data['mes'].' = 0
								THEN
									(MONTH(rea.periodo) = 12 AND YEAR(rea.periodo) = '.$data['year'].')
								ELSE
									(MONTH(rea.periodo) = '.$data['mes'].' AND YEAR(rea.periodo) = '.$data['year'].')
						END
			END'));

		$ppto = DB::select(DB::raw('SELECT
          COALESCE(SUM(ppto.cantidad)*MONTH(CURDATE()), 0) AS Q_Ppto,
          COALESCE(SUM(ppto.prom_remuneracion * ppto.cantidad * MONTH(CURDATE())),
                  0) Rem_Ppto
      FROM
          tbl_remuneraciones_ppto AS ppto
              LEFT JOIN
          tbl_contrato AS cnt ON cnt.contrato_id LIKE ppto.contrato_id
      WHERE
          cnt.contrato_id = '.$data['id'].'

      UNION ALL
      SELECT
          COALESCE(SUM(ppto.cantidad), 0) AS Q_Ppto,
          COALESCE(SUM(ppto.prom_remuneracion * ppto.cantidad),
                  0) Rem_Ppto
      FROM
          tbl_remuneraciones_ppto AS ppto
      WHERE
          ppto.contrato_id = '.$data['id']));

		$evo_real = DB::select(DB::raw('SELECT a.contrato_id,
				a.cont_fechaInicio,
				a.cont_fechaFin,
				b.periodo,
				COALESCE(SUM(b.cantidad), 0) AS cantidad_real,
				COALESCE(SUM(b.prom_remuneracion * b.cantidad)/SUM(b.cantidad), 0) AS media_real

				FROM tbl_contrato AS a
				LEFT JOIN tbl_remuneraciones_mensual AS b ON b.contrato_id = a.contrato_id
				AND a.contrato_id = '.$data['id'].'
				AND
				CASE
						WHEN
							'.$data['year'].' = 0
						THEN
							b.periodo <= CURDATE()
						ELSE
							CASE
									WHEN
										'.$data['mes'].' = 0
									THEN
										(MONTH(b.periodo) <= 12 AND YEAR(b.periodo) = '.$data['year'].')
									ELSE
										(MONTH(b.periodo) <= '.$data['mes'].' AND YEAR(b.periodo) = '.$data['year'].')
							END
				END
				GROUP BY periodo'));

		$evo_ppto = DB::select(DB::raw('SELECT sum(b.cantidad) as cantidad, sum(b.prom_remuneracion * b.cantidad)/sum(b.cantidad) as media FROM tbl_remuneraciones_ppto  as b
				LEFT JOIN tbl_contrato as a on a.contrato_id = b.contrato_id
				WHERE a.contrato_id = '.$data['id'].''));

		$ctto = DB::select(DB::raw('SELECT *
								FROM tbl_contrato as a
								WHERE a.contrato_id = '.$data['id'].''));

		$cargos = DB::select(DB::raw('SELECT DISTINCT
          a.cargo,
          COALESCE(SUM(a.cantidad * a.prom_remuneracion) / SUM(a.cantidad),
                  0) AS media_remuneracion,
          COALESCE(c.prom_remuneracion, 0) as prom_remuneracion,
          COALESCE(((SUM(a.cantidad * a.prom_remuneracion) / SUM(a.cantidad)) - c.prom_remuneracion) * 100 / c.prom_remuneracion,
                  0) AS dif_remuneracion,
      	COALESCE(SUM(a.cantidad)/COUNT(DISTINCT a.periodo), 0) AS real_dotacion,
          COALESCE(c.cantidad, 0) AS ppto_dotacion,
          COALESCE((SUM(a.cantidad)/COUNT(DISTINCT a.periodo) - c.cantidad)*100/c.cantidad, 0) AS dif_dotacion
      FROM
          tbl_remuneraciones_mensual AS a
              LEFT JOIN
          tbl_roles AS b ON a.cargo = b.Descripción
              LEFT JOIN
          tbl_remuneraciones_ppto AS c ON b.IdRol = c.IdRol AND c.contrato_id = a.contrato_id
      WHERE
          a.contrato_id = '.$data['id'].'
					AND
					CASE
							WHEN
								'.$data['year'].' = 0
							THEN
								a.periodo <= CURDATE()
							ELSE
								CASE
										WHEN
											'.$data['mes'].' = 0
										THEN
											(MONTH(a.periodo) <= MONTH(CURDATE()) AND YEAR(a.periodo) = '.$data['year'].')
										ELSE
											(MONTH(a.periodo) = '.$data['mes'].' AND YEAR(a.periodo) = '.$data['year'].')
								END
					END
      GROUP BY a.cargo
      ORDER BY media_remuneracion DESC'));

		return view('detalleremuneraciones.index',$this->data)->with('real', $real)->with('ppto', $ppto)->with('evo_real', $evo_real)->with('evo_ppto', $evo_ppto)->with('ctto', $ctto)->with('cargos', $cargos);
		}
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		return view('detalleremuneraciones.index',$this->data);
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
		return view('detalleremuneraciones.form',$this->data);
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$this->data['access']		= $this->access;
		return view('detalleremuneraciones.view',$this->data);
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
