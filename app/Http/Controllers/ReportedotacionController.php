<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Reportedotacion;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect, DB ;


class ReportedotacionController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'reportedotacion';
	static $per_page	= '10';

	public function __construct()
	{

		parent::__construct();
		
		$this->model = new Reportedotacion();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportedotacion',
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
				$tablas = DB::select(DB::raw('SELECT
																					COALESCE(SUM(sub.dotacion_real_faena* 160 / 173.33),
																									0) AS fte,
																					COALESCE(SUM(sub.dotacion_real_total),
																									0)*sub.cant_meses AS dot
																			FROM
																					(SELECT
																							a.contrato_id,
																									COUNT(a.contrato_id) AS cant_meses,
																									COALESCE(SUM(a.cantidad), 0) AS dotacion_real_total,
																									COALESCE(SUM(a.faena), 0) AS dotacion_real_faena
																					FROM
																							tbl_e200_mensual AS a
																					LEFT JOIN tbl_contrato AS e ON e.contrato_id = a.contrato_id
																					WHERE
																					a.mes <= CURDATE()
																					GROUP BY a.contrato_id) AS sub
																			UNION ALL SELECT DISTINCT
																					COALESCE(SUM(CASE
																											WHEN tbl_documento_valor.IdTipoDocumentoValor IN (121 , 122) THEN tbl_documento_valor.valor
																											ELSE 0
																									END) / 173.33,
																									0) AS fte_ppto,
																					COALESCE(SUM(CASE
																											WHEN tbl_documento_valor.IdTipoDocumentoValor = 123 THEN tbl_documento_valor.valor
																											ELSE 0
																									END) + SUM(CASE
																											WHEN tbl_documento_valor.IdTipoDocumentoValor = 124 THEN tbl_documento_valor.valor
																											ELSE 0
																									END),
																									0) AS dot_ppto
																			FROM
																					tbl_tipos_documentos
																							LEFT JOIN
																					tbl_tipo_documento_valor ON tbl_tipos_documentos.IdTipoDocumento = tbl_tipo_documento_valor.IdTipoDocumento
																							LEFT JOIN
																					tbl_documento_valor ON tbl_tipo_documento_valor.IdTipoDocumentoValor = tbl_documento_valor.IdTipoDocumentoValor
																							LEFT JOIN
																					tbl_documentos ON tbl_documentos.IdDocumento = tbl_documento_valor.IdDocumento
																			WHERE
																					tbl_tipos_documentos.IdTipoDocumento = 12
																					AND tbl_documentos.createdOn <= CURDATE()
																			UNION ALL SELECT
																					COALESCE(SUM(sub.dotacion_real_faena* 160 / 173.33),
																									0) AS fte,
																					COALESCE(SUM(sub.dotacion_real_total),
																									0) AS dot
																			FROM
																					(SELECT
																							a.contrato_id,
																									COUNT(a.contrato_id) AS cant_meses,
																									COALESCE(SUM(a.cantidad), 0) AS dotacion_real_total,
																									COALESCE(SUM(a.faena), 0) AS dotacion_real_faena
																					FROM
																							tbl_e200_mensual AS a
																					LEFT JOIN tbl_contrato AS e ON e.contrato_id = a.contrato_id
																					WHERE
																						MONTH(a.mes) = MONTH(CURDATE())
																					AND
																						YEAR(a.mes) = YEAR(CURDATE())
																					GROUP BY a.contrato_id) AS sub
																			UNION ALL SELECT DISTINCT
																					COALESCE(SUM(CASE
																											WHEN tbl_documento_valor.IdTipoDocumentoValor IN (121 , 122) THEN tbl_documento_valor.valor
																											ELSE 0
																									END) / 173.33,
																									0) AS fte_ppto,
																					COALESCE(SUM(CASE
																											WHEN tbl_documento_valor.IdTipoDocumentoValor = 123 THEN tbl_documento_valor.valor
																											ELSE 0
																									END) + SUM(CASE
																											WHEN tbl_documento_valor.IdTipoDocumentoValor = 124 THEN tbl_documento_valor.valor
																											ELSE 0
																									END),
																									0) AS dot_ppto
																			FROM
																					tbl_tipos_documentos
																							LEFT JOIN
																					tbl_tipo_documento_valor ON tbl_tipos_documentos.IdTipoDocumento = tbl_tipo_documento_valor.IdTipoDocumento
																							LEFT JOIN
																					tbl_documento_valor ON tbl_tipo_documento_valor.IdTipoDocumentoValor = tbl_documento_valor.IdTipoDocumentoValor
																							LEFT JOIN
																					tbl_documentos ON tbl_documentos.IdDocumento = tbl_documento_valor.IdDocumento
																			WHERE
																					tbl_tipos_documentos.IdTipoDocumento = 12
																							AND
																								MONTH(tbl_documentos.createdOn) = MONTH(CURDATE())
																							AND
																								YEAR(tbl_documentos.createdOn) = YEAR(CURDATE())'));

				$grafico = DB::select(DB::raw('SELECT DISTINCT tbl_documentos.createdOn as mes,
								    COALESCE(SUM(CASE
								                WHEN tbl_documento_valor.IdTipoDocumentoValor IN (121 , 122) THEN tbl_documento_valor.valor
								                ELSE 0
								            END) / 173.33,
								            0) AS dotacion_real_total
								FROM
								    tbl_tipos_documentos
								        LEFT JOIN
								    tbl_tipo_documento_valor ON tbl_tipos_documentos.IdTipoDocumento = tbl_tipo_documento_valor.IdTipoDocumento
								        LEFT JOIN
								    tbl_documento_valor ON tbl_tipo_documento_valor.IdTipoDocumentoValor = tbl_documento_valor.IdTipoDocumentoValor
								        LEFT JOIN
								    tbl_documentos ON tbl_documentos.IdDocumento = tbl_documento_valor.IdDocumento
								WHERE
								    tbl_tipos_documentos.IdTipoDocumento = 12
									AND tbl_documentos.createdOn <= CURDATE()
									GROUP BY YEAR(tbl_documentos.createdOn), MONTH(tbl_documentos.createdOn) ASC'));

				$grafico_ppto = DB::select(DB::raw('SELECT mes, COALESCE(SUM(faena), 0)*160/173.33 as faena
									FROM tbl_e200_mensual
									GROUP BY YEAR(mes), MONTH(mes) ASC'));

				$ppto = DB::select(DB::raw('SELECT DISTINCT COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor IN (121, 122) THEN c.valor ELSE 0 END), 0) as HH_fte,
																		COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor = 121 THEN c.valor ELSE 0 END), 0) as HHH,
																		COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor = 122 THEN c.valor ELSE 0 END), 0) as HHM,
																		COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor = 123 THEN c.valor ELSE 0 END), 0) as QHT,
																		COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor = 124 THEN c.valor ELSE 0 END), 0) as QMT,
																		COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor IN (123, 124) THEN c.valor ELSE 0 END), 0) as Q_fte

																		FROM tbl_tipos_documentos AS a
																		LEFT JOIN tbl_tipo_documento_valor AS b ON a.IdTipoDocumento = b.IdTipoDocumento
																		LEFT JOIN tbl_documento_valor AS c ON b.IdTipoDocumentoValor = c.IdTipoDocumentoValor
																		LEFT JOIN tbl_documentos AS d ON d.IdDocumento = c.IdDocumento
																		LEFT JOIN tbl_contrato AS e ON e.contrato_id = d.contrato_id

																		WHERE b.IdTipoDocumento = 12
																		AND d.IdDocumento IS NOT NULL
																		ORDER BY b.IdTipoDocumentoValor, d.IdDocumento'));

				$cttos = DB::select(DB::raw('SELECT DISTINCT
																			    e.contrato_id,
																			    e.cont_nombre,
																			    e.cont_proveedor,
																					e.cont_numero,
																			    COALESCE(SUM(CASE
																			                WHEN b.IdTipoDocumentoValor IN (121 , 122) THEN c.valor
																			                ELSE 0
																			            END)/173.33,
																			            0) AS HH_fte,
																			    COALESCE(SUM(CASE
																			                WHEN b.IdTipoDocumentoValor IN (123 , 124) THEN c.valor
																			                ELSE 0
																			            END),
																			            0) AS Q_fte
																			FROM
																			    tbl_tipos_documentos AS a
																			        LEFT JOIN
																			    tbl_tipo_documento_valor AS b ON a.IdTipoDocumento = b.IdTipoDocumento
																			        LEFT JOIN
																			    tbl_documento_valor AS c ON b.IdTipoDocumentoValor = c.IdTipoDocumentoValor
																			        LEFT JOIN
																			    tbl_documentos AS d ON d.IdDocumento = c.IdDocumento
																			        LEFT JOIN
																			    tbl_contrato AS e ON e.contrato_id = d.contrato_id
																			WHERE
																			    b.IdTipoDocumento = 12
																			        AND d.IdDocumento IS NOT NULL
																			GROUP BY e.contrato_id'));

		return view('reportedotacion.index',$this->data)->with('ppto', $ppto)->with('cttos', $cttos)->with('grafico', $grafico)->with('grafico_ppto', $grafico_ppto)->with('tablas', $tablas);
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
		return view('reportedotacion.form',$this->data);
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$this->data['access']		= $this->access;
		return view('reportedotacion.view',$this->data);
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
