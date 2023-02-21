<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Detalledotacion;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect, DB ;


class DetalledotacionController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'detalledotacion';
	static $per_page	= '10';

	public function __construct()
	{

		parent::__construct();
		
		$this->model = new Detalledotacion();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'detalledotacion',
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

			//CALCULO FTE:
			//8 horas diarias x 5 días a la semana x 4 semanas al mes = 160 horas al mes
			//(8 horas diarias x 5 días a la semana x 52 semanas al año) / 12 meses = 173,33 horas al mes
			// El segundo caso permite promediar los casos de meses con 31 días y Febrero y considera todos los días del año.

			//$tablas[0] = Acumulado Plan
			//$tablas[1] = Acumulado Real
			//$tablas[2] = Mensual Plan (Mes en curso)
			//$tablas[3] = Mensual Real (Mes en curso)
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
																				CASE
	                                          WHEN
	                                            '.$data['year'].' = 0
	                                          THEN
	                                            a.mes <= CURDATE()
	                                          ELSE
	                                            CASE
	                                                WHEN
	                                                  '.$data['mes'].' = 0
	                                                THEN
	                                                  (MONTH(a.mes) <= 12 AND YEAR(a.mes) = '.$data['year'].')
	                                                ELSE
	                                                  (MONTH(a.mes) <= '.$data['mes'].' AND YEAR(a.mes) = '.$data['year'].')
	                                            END
	                                      END
																				AND a.contrato_id = '.$data['id'].'
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
																				CASE
                                            WHEN
                                              '.$data['year'].' = 0
                                            THEN
                                              tbl_documentos.createdOn <= CURDATE()
                                            ELSE
                                            CASE
                                                WHEN
                                                  '.$data['mes'].' = 0
                                                THEN
                                                  (MONTH(tbl_documentos.createdOn) <= 12 AND YEAR(tbl_documentos.createdOn) = '.$data['year'].')
                                                ELSE
                                                  (MONTH(tbl_documentos.createdOn) <= '.$data['mes'].' AND YEAR(tbl_documentos.createdOn) = '.$data['year'].')
                                            END
                                        END
																				AND tbl_documentos.contrato_id = '.$data['id'].'
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
																						CASE
																								WHEN
																									'.$data['year'].' = 0
																								THEN
																									(MONTH(a.mes) = MONTH(CURDATE()) AND YEAR(a.mes) = YEAR(CURDATE()))
																								ELSE
																								CASE
																										WHEN
																											'.$data['mes'].' = 0
																										THEN
																											(MONTH(a.mes) = MONTH(CURDATE()) AND YEAR(a.mes) = '.$data['year'].')
																										ELSE
																											(MONTH(a.mes) = '.$data['mes'].' AND YEAR(a.mes) = '.$data['year'].')
																								END
																						END
																				AND a.contrato_id = '.$data['id'].'
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
																				CASE
																						WHEN
																							'.$data['year'].' = 0
																						THEN
																							(MONTH(tbl_documentos.createdOn) = MONTH(CURDATE()) AND YEAR(tbl_documentos.createdOn) = YEAR(CURDATE()))
																						ELSE
																						CASE
																								WHEN
																									'.$data['mes'].' = 0
																								THEN
																									(MONTH(tbl_documentos.createdOn) = MONTH(CURDATE()) AND YEAR(tbl_documentos.createdOn) = '.$data['year'].')
																								ELSE
																									(MONTH(tbl_documentos.createdOn) = '.$data['mes'].' AND YEAR(tbl_documentos.createdOn) = '.$data['year'].')
																						END
																				END
																				AND tbl_documentos.contrato_id = '.$data['id']));

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
									AND
											CASE
													WHEN
														'.$data['year'].' = 0
													THEN
														tbl_documentos.createdOn <= CURDATE()
													ELSE
													CASE
															WHEN
																'.$data['mes'].' = 0
															THEN
																(MONTH(tbl_documentos.createdOn) <= 12 AND YEAR(tbl_documentos.createdOn) = '.$data['year'].')
															ELSE
																(MONTH(tbl_documentos.createdOn) <= '.$data['mes'].' AND YEAR(tbl_documentos.createdOn) = '.$data['year'].')
													END
											END
										AND tbl_documentos.contrato_id = '.$data['id'].'
										GROUP BY tbl_documentos.createdOn'));

					$grafico_ppto = DB::select(DB::raw('SELECT a.mes, a.faena*160/173.33 as faena
										FROM tbl_e200_mensual AS a
										WHERE contrato_id = '.$data['id'].'
										AND
										CASE
												WHEN
													'.$data['year'].' = 0
												THEN
													a.mes <= CURDATE()
												ELSE
													CASE
															WHEN
																'.$data['mes'].' = 0
															THEN
																(MONTH(a.mes) <= 12 AND YEAR(a.mes) = '.$data['year'].')
															ELSE
																(MONTH(a.mes) <= '.$data['mes'].' AND YEAR(a.mes) = '.$data['year'].')
													END
										END
										GROUP BY a.mes ASC'));

					$ppto = DB::select(DB::raw('SELECT DISTINCT COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor IN (121, 122) THEN c.valor ELSE 0 END), 0) as HH_fte,
																			COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor = 121 THEN c.valor ELSE 0 END), 0) as HHH,
																			COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor = 122 THEN c.valor ELSE 0 END), 0) as HHM,
																			COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor = 123 THEN c.valor ELSE 0 END), 0) as QHT,
																			COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor = 124 THEN c.valor ELSE 0 END), 0) as QMT,
																			COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor IN (121, 122) THEN c.valor ELSE 0 END), 0) as Q_fte,
																			COALESCE(SUM(CASE WHEN b.IdTipoDocumentoValor IN (123, 124) THEN c.valor ELSE 0 END), 0) as faena_fte

																			FROM tbl_tipos_documentos AS a
																			LEFT JOIN tbl_tipo_documento_valor AS b ON a.IdTipoDocumento = b.IdTipoDocumento
																			LEFT JOIN tbl_documento_valor AS c ON b.IdTipoDocumentoValor = c.IdTipoDocumentoValor
																			LEFT JOIN tbl_documentos AS d ON d.IdDocumento = c.IdDocumento
																			LEFT JOIN tbl_contrato AS e ON e.contrato_id = d.contrato_id

																			WHERE b.IdTipoDocumento = 12
																			AND
																			CASE
																					WHEN
																						'.$data['year'].' = 0
																					THEN
																						d.createdOn <= CURDATE()
																					ELSE
																					CASE
																							WHEN
																								'.$data['mes'].' = 0
																							THEN
																								(MONTH(d.createdOn) <= 12 AND YEAR(d.createdOn) = '.$data['year'].')
																							ELSE
																								(MONTH(d.createdOn) <= '.$data['mes'].' AND YEAR(d.createdOn) = '.$data['year'].')
																					END
																			END
																			AND e.contrato_id = '.$data['id'].'
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
																								AND e.contrato_id = '.$data['id'].'
																								AND
																										CASE
																												WHEN
																													'.$data['year'].' = 0
																												THEN
																													(MONTH(d.createdOn) = MONTH(CURDATE()) AND YEAR(d.createdOn) = YEAR(CURDATE()))
																												ELSE
																												CASE
																														WHEN
																															'.$data['mes'].' = 0
																														THEN
																															(MONTH(d.createdOn) = MONTH(CURDATE()) AND YEAR(d.createdOn) = '.$data['year'].')
																														ELSE
																															(MONTH(d.createdOn) = '.$data['mes'].' AND YEAR(d.createdOn) = '.$data['year'].')
																												END
																										END
																				GROUP BY e.contrato_id'));

					$ctto = DB::select(DB::raw('SELECT *
											FROM tbl_contrato as a
											WHERE a.contrato_id = '.$data['id'].''));

			return view('detalledotacion.index',$this->data)->with('ppto', $ppto)->with('cttos', $cttos)->with('grafico', $grafico)->with('grafico_ppto', $grafico_ppto)->with('tablas', $tablas)->with('ctto', $ctto);
		}
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		return view('detalledotacion.index',$this->data);
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
		return view('detalledotacion.form',$this->data);
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$this->data['access']		= $this->access;
		return view('detalledotacion.view',$this->data);
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
