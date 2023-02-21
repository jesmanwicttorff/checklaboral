<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\noconformidades;
use App\Models\Cargaf;
use App\Models\Tiposcontratospersonas;
use App\Library\MyDocuments;
use App\Library\MyDocumentsContractPerson;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PDF;
use Log;

class noconformidadesController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'noconformidades';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new noconformidades();
		$this->modelupload = new Cargaf();
		$this->modelview = new  \App\Models\Documentovalor();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'noconformidades',
			'pageUrl'			=>  url('noconformidades'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('noconformidades.index',$this->data);
	}

	public function getShowlist( Request $request){

		$lintIdUser = \Session::get('uid');
		$lintGroupUser = \MySourcing::GroupUser($lintIdUser);
		$lintLevelUser = \MySourcing::LevelUser($lintIdUser);
		$sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
		$order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);

		if ($lintLevelUser != 1 && $lintLevelUser!=7){
			//Aplicamos un filro para el control de los perfiles
			#$filter = " AND tbl_tipos_documentos.id = '".$lintGroupUser."' ";
                        $filter = " AND tbl_tipos_documentos.IdTipoDocumento IN ( SELECT idTipoDocumento FROM tbl_tipo_documento_perfil WHERE IdPerfil = '".$lintGroupUser."') AND tbl_documentos.marca = '0' ";
		}else{
			$filter = " AND tbl_documentos.marca = '0' ";
		}

        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
		$filter .= ' AND ( (tbl_documentos.contrato_id IN ('.$lobjFiltro['contratos'].') AND tbl_documentos.IdContratista Is Null ) OR ( tbl_documentos.entidad = 1 AND tbl_documentos.IdEntidad IN ('.$lobjFiltro['contratistas'].') ) OR ( tbl_documentos.contrato_id IN ('.$lobjFiltro['contratos'].') AND tbl_documentos.IdContratista IN ('.$lobjFiltro['contratistas'].') ) ) ';

       	$lintIdEstatus = 1;
		if (!is_null($request->input('IdEstatus'))){
			$lintIdEstatus=$request->input('IdEstatus');
		}


		if($lintIdEstatus==0){
		  $filter .= ' AND ( tbl_documentos.IdEstatus in (1,3) OR ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 ) ';
		}else{
			//Cambiando forma de identificar Vencidos
			if($lintIdEstatus==8){ //solo se muestran los vencidos con marca de vencimiento
                $filter .= " AND ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 and tbl_documentos.Vencimiento=1 ";
			}else{
				if($lintIdEstatus==10){

					$filter .= " AND tbl_documentos.Vencimiento = 1 AND tbl_documentos.FechaVencimiento BETWEEN NOW() - INTERVAL 1 DAY and DATE_ADD(NOW() - INTERVAL 1 DAY, INTERVAL (tbl_tipos_documentos.DiasVencimiento + 1) DAY) ";

				}else{
					$filter .= " AND tbl_documentos.IdEstatus='".$request->input('IdEstatus')."' AND ifnull(tbl_documentos.IdEstatusDocumento,1) != 2 ";
				}
			}
		}
		$filter .= " AND tbl_documentos.Entidad != 7 ";


		if(!is_null($request->input('search')))
		{
			$search = 	$this->buildSearch('maps');
			$filter = $search['param'];
			$this->data['search_map'] = $search['maps'];
		}
		$page = $request->input('page', 1);
		$params = array(
			'page'		=> $page ,
			'sort'		=> $sort ,
			'IdEstatus'	=>$lintIdEstatus,
			'order'		=> $order,
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);

		// Get Query
		$results = $this->model->getRows( $params );
		$larrResult = array();
      //  var_dump(count($results));

		$larrResultTemp = array();
		$i = 0;

		//Cargamos los tipos de documentos asociados a encuentas
		$lobjEncuestas = \DB::table('tbl_encuestas_master')
						->select('tbl_encuestas_master.IdTipoDocumento')
						->distinct()
						->get();
		$larrEncuentas = array();
		foreach ($lobjEncuestas as $arrEncuetas) {
			$larrEncuentas[$arrEncuetas->IdTipoDocumento] = 1;
		}

		$lobjEncuestasDos =\DB::table('tbm_encuestas')
		->select('tbm_encuestas.IdTipoDocumento')
		->distinct()
		->get();
		$larrEncuentasDos = array();
		foreach ($lobjEncuestasDos as $arrEncuetasDos) {
			$larrEncuentasDos[$arrEncuetasDos->IdTipoDocumento] = 1;
		}


		foreach ($results['rows'] as $row) {

			$id = $row->IdDocumento;
			$tipo = $row->IdTipoDocumento;
			$estatus = $row->IdEstatus;
            $estatusDoc = $row->IdEstatusDocumento;
            $vigencia = $row->Vigencia;
            $Fvencimiento = $row->FechaVencimiento;
			$vencimiento = $row->DiasVencimiento;
			$larrResultTemp = array('id'=> ++$i,
								    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$id.'" /> '
								    );
			foreach ($this->info['config']['grid'] as $field) {
				if($field['view'] =='1') {
					$limited = isset($field['limited']) ? $field['limited'] :'';
					if (\SiteHelpers::filterColumn($limited )){
						if ($field['field'] == 'DocumentoURL') {
                            //Preparamos la vista de Ver Documento
                            if ($row->{$field['field']}) {
                                $value = "<a onClick=\"ViewPDF('" . $row->{$field['field']} . "'," . $row->{'IdDocumento'} . "," . $row->{'IdEstatus'} . "," . $row->{'estado_carga'} . "," . $row->{'Tipo'} . ");\" class=\"btn btn-xs btn-white tips\"><i class=\"\" ></i>Ver</a>";
                            } else {
                                if (isset($larrEncuentas[$row->IdTipoDocumento])) { //Es una encuesta
                                    $value = '<div class=" action dropup"><a href="' . \URL::to('encuestas/update/doc=' . $row->{'IdDocumento'}) . '" onclick="ViewEncuesta(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="' . \Lang::get('core.btn_edit') . '"><i class=""></i>Ver</a></div>';
                                } else if ($row->{'Tipo'} == 2 && $row->{'IdEstatus'} > 1) {
                                    $value = "<a onClick=\"ViewPDF('','" . $row->{'IdDocumento'} . "');\" class=\"btn btn-xs btn-white tips\"><i class=\"\" ></i>Ver</a>";
                                } else if (isset($larrEncuentasDos[$row->IdTipoDocumento])){
																	$value = '<div class=" action dropup"><a href="' . \URL::to('encuestados/respuestas/doc=' . $row->{'IdDocumento'}) . '" onclick="ViewEncuesta(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="' . \Lang::get('core.btn_edit') . '"><i class=""></i>Ver</a></div>';
																} else {
                                    $value = '';
                                }
                            }
                        }if ($field['field'] == 'FechaVencimiento') {
						    if ($row->{'Vencimiento'}){
                                $value = \SiteHelpers::formatRows($row->{$field['field']}, $field , $row);
                            }else{
						        $value='';
                            }
						}else{
							$value = \SiteHelpers::formatRows($row->{$field['field']}, $field , $row);
						}
						$larrResultTemp[$field['field']] = $value;
					}
				}
			}

			if ($row->IdEstatus==5){
                    $docRel = \DB::table('tbl_documentos')->select('IdDocumento')->where('IdDocumentoRelacion', $id)->first();
                    if ($docRel){
                     $docRel = $docRel->IdDocumento;
                    }else{
                    	$docRel = "";
                    }
                if (!(intval($tipo)==3 || intval($tipo)==6 || intval($tipo)==21)){
                    if ($estatusDoc==2){

                            $larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('noconformidades/update/'."-".$id).'" onclick="SximoModal(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="Renovar"><i class="fa  fa-calendar-times-o"></i></a></div>';
                    }
                    else{
                        $now = new \DateTime();
                        $FechaVen = new \DateTime($Fvencimiento);
                        $diff = $FechaVen->diff($now);
                        if ($diff->days <= $vencimiento){
                                $larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('noconformidades/update/'."-".$id).'" onclick="SximoModal(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="Renovar"><i class="fa  fa-calendar-times-o"></i></a></div>';
                        }
                        else{
                            $larrResultTemp['action'] = "-";
                        }

                    }
                }
                else{
                    $larrResultTemp['action'] = "-";
                }


            }else{
				if (isset($larrEncuentas[$row->IdTipoDocumento])){
					if ($estatus==1){
						$larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('encuestas/update/doc='.$id).'" onclick="ViewEncuesta(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class="fa  fa-upload"></i></a></div>';
					}else {
						$larrResultTemp['action'] = '';
					}
				}elseif(isset($larrEncuentasDos[$row->IdTipoDocumento])){
					$larrResultTemp['action'] = '<div class=" action dropup"><a href="' . \URL::to('encuestados/respuestas/doc=' . $id) . '" onclick="ViewEncuesta(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="' . \Lang::get('core.btn_edit') . '"><i class="fa  fa-upload"></i></a></div>';
				}else{
					$larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('noconformidades/update/'.$id).'" onclick="SximoModal(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class="fa  fa-upload"></i></a></div>';
				}
			}
           if($lintIdEstatus==10){
				$larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('noconformidades/update/'."-".$id).'" onclick="SximoModal(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="Renovar"><i class="fa  fa-calendar-times-o"></i></a></div>';
		   }

			$larrResult[] = $larrResultTemp;
		}

		echo json_encode(array("data"=>$larrResult));

	}

	public function postData( Request $request)
	{
		$params = array(
			'page'		=> '' ,
			'sort'		=> '' ,
			'order'		=> '',
			'params'	=> '',
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		$params['params'] = " AND tbl_documentos.entidad = 7 ";
		$resultsNew = 		$this->model->getRows( $params );
		$this->data['rowDataDos']   = $resultsNew['rows'];
		$params = array(
			'IdEstatus'	=> (is_null($request->input('IdEstatus')) ? 1 : $request->input('IdEstatus') ),
		);
		$this->data['param']		= $params;
		$this->data['tableGrid'] 	= $this->info['config']['grid'];
		$this->data['access']		= $this->access;
		$this->data['setting'] 		= $this->info['setting'];
		return view('noconformidades.table',$this->data);

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

            $pos = strpos($id, '-');

            if ($pos !== false) {
                $porciones = explode("-", $id);

                $this->data['DocumentoRelacion'] = $porciones[1];
                $id = $porciones[1];
            }
            else{
                $this->data['DocumentoRelacion'] = NULL;
            }
		}

		$lobjDocumento = new MyDocuments($id);
		$lobjDatosDocumento = $lobjDocumento->getDatos();
		$row = $lobjDatosDocumento;

		if($row)
		{
			$this->data['entidad'] = view('entidades.viewentidad', $lobjDocumento->getDatosEntidad());
			$this->data['row'] 		=  $row;
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_documentos');
		}

        $this->data['lrowContrato'] = \DB::table('tbl_contrato')->select('autorenovacion','cont_fechaFin')->where('contrato_id',$row['contrato_id'])->first();
        $this->data['lobjTiposContratos'] =  Tiposcontratospersonas::Active()->get();
				$this->data['lobjAnotaciones'] = \DB::table('tbl_concepto_anotacion')->where('IdEstatus', '=', 1)->get();

		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;

		if ($lobjDatosDocumento->TipoDocumento->IdProceso == 17) { //Multa generales
			return view('noconformidades.multasgeneral',$this->data);
		}elseif ($lobjDatosDocumento->TipoDocumento->IdProceso == 77) { // Multas chile
			return view('noconformidades.formmultas',$this->data);
		}elseif ($lobjDatosDocumento->TipoDocumento->IdProceso == 79) { //Situación Tributaria
			return view('noconformidades.situaciontributaria',$this->data);
		}else{
	        return view('noconformidades.form',$this->data);
		}

	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

		$row = $this->model->getRow($id);
		if($row)
		{
			$this->data['row'] =  $row;

			$this->data['id'] = $id;
			$this->data['access']		= $this->access;
			$this->data['setting'] 		= $this->info['setting'];
			$this->data['fields'] 		= \AjaxHelpers::fieldLang($this->info['config']['grid']);
			$this->data['subgrid']		= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
			return view('noconformidades.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}

	function getDownloadmultas(){
		return \MyLoadbatch::DocumentsMultas();
	}

	function getDownloadultasgeneral() {
		return \MyLoadbatch::DocumentsMultasGeneral();
	}

	function getShowlistupload(){
      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = \Session::get('uid');

      $lstrDirectory = \MyLoadbatch::getDirectory();
      $lstrDirectoryResult = \MyLoadbatch::getDirectoryResult();

      $lobjLastUpload = \DB::table('tbl_carga_masiva_log')
                             ->join('tb_users','tb_users.id', '=', 'tbl_carga_masiva_log.entry_by')
                             ->leftjoin("tbl_tipos_documentos", "tbl_tipos_documentos.IdTipoDocumento","=","tbl_carga_masiva_log.IdTipoDocumento")
                             ->select(\DB::raw("concat(tb_users.first_name , ' ', tb_users.last_name) as entry_by_name"),
                                      "tbl_carga_masiva_log.createdOn",
                                      "tbl_carga_masiva_log.Cargados",
                                      "tbl_carga_masiva_log.Modificados",
                                      "tbl_carga_masiva_log.Rechazados",
                                      "tbl_carga_masiva_log.Rechazados",
                                      \DB::raw("case when tbl_carga_masiva_log.ArchivoURL != '' then concat('<a href=\"".$lstrDirectory."',tbl_carga_masiva_log.ArchivoURL, '\"><i class=\"fa fa-download\"></i> descargar</a>') else ' ' end as ArchivoURL"),
                                      \DB::raw("case when tbl_carga_masiva_log.ArchivoResultadoURL != '' then concat('<a href=\"".$lstrDirectoryResult."',tbl_carga_masiva_log.ArchivoResultadoURL, '\"><i class=\"fa fa-download\"></i> descargar</a>') else ' ' end  as ArchivoResultadoURL"),
                                      \DB::raw("tbl_tipos_documentos.Descripcion as TipoDocumento"))
                             ->orderBy("tbl_carga_masiva_log.IdCargaMasiva","DESC")
                             ->whereraw("tbl_carga_masiva_log.IdProceso IN (4,5,6,17) ");
      	if ($lintLevelUser!=1){ //Solo el superadmin puede ver lo que ha cargado todos los usuarios
        	$lobjLastUpload->where("tbl_carga_masiva_log.entry_by","=",$lintIdUser);
	    }
	    $lobjLastUpload = $lobjLastUpload->get();
	    echo json_encode(array("data"=>$lobjLastUpload));
	  }

	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_documentos ") as $column)
        {
			if( $column->Field != 'IdDocumento')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_documentos (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_documentos WHERE IdDocumento IN (".$toCopy.")";
			\DB::select($sql);
			return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success')
			));

		} else {
			return response()->json(array(
				'status'=>'success',
				'message'=> 'Please select row to copy'
			));
		}


	}

	function postMasivo(Request $request, $id =0){

	}

	function postSave( Request $request, $id =0)
	{

		$larrResult = array();
		$lobjMyDocumentos = new MyDocuments($request->input('IdDocumento'));
		$lobjArchivoDocumento = Input::file('DocumentoURL');
		$ldatFechaVencimiento = \MyFormats::FormatoFecha($request->input('FechaVencimiento'));
		$lobjDocumento = $lobjMyDocumentos::getDatos();

		if ($lobjDocumento->TipoDocumento->IdProceso == 17){
			$larrResult = \MyLoadbatch::LoadBach($lobjDocumento->TipoDocumento->IdProceso,  $lobjArchivoDocumento, $lobjDocumento->IdTipoDocumento);
		}elseif ($lobjDocumento->TipoDocumento->IdProceso == 5){
			$larrResult = \MyLoadbatch::LoadBach(4,  $lobjArchivoDocumento, $lobjDocumento->IdTipoDocumento);
		}elseif ($lobjDocumento->TipoDocumento->IdProceso == 79){
			$larrResult = \MyLoadbatch::LoadBach(6,  $lobjArchivoDocumento, $lobjDocumento->IdTipoDocumento);
		}elseif ($lobjDocumento->TipoDocumento->IdProceso == 77){
			$larrResult = \MyLoadbatch::LoadBach(5,  $lobjArchivoDocumento, $lobjDocumento->IdTipoDocumento);
		}elseif ($lobjDocumento->TipoDocumento->IdProceso == 21){
			$lobjMyDocumentosContractPerson = new MyDocumentsContractPerson($request->input('IdDocumento'));
			$lintIdTipoContrato = $request->input('IdTipoContrato');
			$larrResult = $lobjMyDocumentosContractPerson::load($lobjArchivoDocumento, $ldatFechaVencimiento, $lintIdTipoContrato);
			if ($larrResult['code']==1){
				$larrResult = $lobjMyDocumentosContractPerson::loadvalues($request->bulk_IdTipoDocumentoValor, $request->bulk_Valor);
			}
		}else{

			$larrResult = $lobjMyDocumentos::load($lobjArchivoDocumento, $ldatFechaVencimiento);
			if ($larrResult['code']==1){
				$larrResult = $lobjMyDocumentos::loadvalues($request->bulk_IdTipoDocumentoValor, $request->bulk_Valor);
				if ($lobjDocumento->TipoDocumento->IdProceso == 1 || $lobjDocumento->TipoDocumento->IdProceso == 112){
					$fetchMode = \DB::getFetchMode();
			        \DB::setFetchMode(\PDO::FETCH_ASSOC);
			        $lobjDocumento = \DB::table('tbl_documentos')->where('IdDocumento',$request->input('IdDocumento'))->first();
			        \DB::setFetchMode($fetchMode);
			        $larrResult = \MySourcing::ProccessDocument($request->input('IdDocumento'),$lobjDocumento);
				}
			}

		}

		return response()->json($larrResult);

	}

	public function postDelete( Request $request)
	{

		if($this->access['is_remove'] ==0) {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_restric')
			));

		}
		// delete multipe rows
		if(count($request->input('ids')) >=1)
		{
			$this->model->destroy($request->input('ids'));
			\DB::table('tbl_documento_valor')->whereIn('IdDocumento',$request->input('ids'))->delete();
			return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success_delete')
			));
		} else {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));

		}

	}

	public static function display( )
	{
		$mode  = isset($_GET['view']) ? 'view' : 'default' ;
		$model  = new noconformidades();
		$info = $model::makeInfo('noconformidades');

		$data = array(
			'pageTitle'	=> 	$info['title'],
			'pageNote'	=>  $info['note']

		);

		if($mode == 'view')
		{
			$id = $_GET['view'];
			$row = $model::getRow($id);
			if($row)
			{
				$data['row'] =  $row;
				$data['fields'] 		=  \SiteHelpers::fieldLang($info['config']['grid']);
				$data['id'] = $id;
				return view('noconformidades.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdDocumento' ,
				'order'		=> 'asc',
				'params'	=> '',
				'global'	=> 1
			);

			$result = $model::getRows( $params );
			$data['tableGrid'] 	= $info['config']['grid'];
			$data['rowData'] 	= $result['rows'];

			$page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
			$pagination = new Paginator($result['rows'], $result['total'], $params['limit']);
			$pagination->setPath('');
			$data['i']			= ($page * $params['limit'])- $params['limit'];
			$data['pagination'] = $pagination;
			return view('noconformidades.public.index',$data);
		}


	}

	function getDescargaarch( Request $request )
	{
		return \MyLoadbatch::ContratistasVigentes();
	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_documentos');
			 $this->model->insertRow($data , $request->input('IdDocumento'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}
	public function getDescargarDocumentoPersonas($id){

		$hoy = date('Y-m-d H:i:s');
		$datos =  \DB::table('tbl_personas')
		->select('tbl_contratistas.RUT', 'tbl_contratistas.RazonSocial', 'tbl_contrato.cont_numero', 'tbl_personas.RUT AS rutP', 'tbl_personas.Nombres', 'tbl_personas.Apellidos', 'tbl_personas.IdEstatus', 'tbl_personas.Sexo', 'tbl_personas.FechaNacimiento', 'tbl_tipos_contratos_personas.Nombre AS IdTipoContrato','tbl_contratos_personas.FechaInicioFaena')
		->join('tbl_contratos_personas', 'tbl_personas.IdPersona', '=', 'tbl_contratos_personas.IdPersona' )
		->join('tbl_contrato', 'tbl_contratos_personas.contrato_id', '=', 'tbl_contrato.contrato_id' )
		->join('tbl_contratistas', 'tbl_contratos_personas.IdContratista', '=', 'tbl_contratistas.IdContratista' )
		->join('tbl_tipos_contratos_personas', 'tbl_contratos_personas.IdTipoContrato', '=', 'tbl_tipos_contratos_personas.id' )
		->where('tbl_contratos_personas.IdContratista','=',$id)
		->get();


		// $styleArray = array(
		// 	'borders' => array(
		// 		'outline' => array(
		// 			'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
		// 			'color' => array('argb' => 'FFFF0000'),
		// 		),
		// 	),
		// );

		//$phpExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		//other

		// $inputFileName = public_path('archivos/planillaDatosTrabajador.xlsx');
		// $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
		// $sheet = $spreadsheet->getActiveSheet();
		// $spreadsheet->setActiveSheetIndex(0);





		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('Datos trabajadores');
		$spreadsheet->setActiveSheetIndex(0);

		$spreadsheet->getActiveSheet()->getStyle('A1:G3')->getFont()->setBold(true);
		$sheet->setCellValue('A1', 'Planilla Actualizacion de datos Trabajadores');
		$sheet->setCellValue('A2', 'Planilla emitida el dia: '.$hoy);
		$sheet->setCellValue('A3', 'INSTRUCCIONES DE LLENADO: ');
		$sheet->setCellValue('A4', '1) Solo si el trabajador ya no se encuentra prestando servicios al momento de rellenar la planilla, debe marcarlo como FINIQUITADO e indicar la fecha y motivo de desvinculación.');
		$sheet->setCellValue('A5', '2) Solo si el contrato es a plazo fijo, debe indicar la fecha de término de contrato.');
		$sheet->setCellValue('A6', '3) Para Trabajadores con Discapacidad, solo considere aquellos que estuvieran inscritos en el SENADIS.');
		$spreadsheet->getActiveSheet()->mergeCells('A8:C8');
		$spreadsheet->getActiveSheet()->mergeCells('D8:L8');
		$spreadsheet->getActiveSheet()->mergeCells('M8:V8');
		// $spreadsheet->getActiveSheet()->mergeCells('A3:L7');


		$sheet->getStyle('A8:V8')->getAlignment()->setHorizontal('center');
		$sheet->setCellValue('A8', 'Datos Empresa');
		$sheet->setCellValue('D8', 'Datos Personales trabajador');
		$sheet->setCellValue('M8', 'Datos Contractuales');
		$sheet->getStyle('A9:W9')->getAlignment()->setHorizontal('center');
		$spreadsheet->getActiveSheet()->getStyle('A8:V8')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2CC');
		$spreadsheet->getActiveSheet()->getStyle('A9:W9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4472c4');
		$spreadsheet->getActiveSheet()->getRowDimension('9')->setRowHeight(20);
		$spreadsheet->getActiveSheet()->getStyle('A9:W9')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

		$j=9;

		$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(40);
		$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
		$spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
		$spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);
		$spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('W')->setWidth(30);

		$spreadsheet->getActiveSheet()->setAutoFilter('A9:V9');
		$sheet->setCellValue('A'.$j, 'RUT Empresa');
		$sheet->setCellValue('B'.$j, 'Razón SociaL');
		$sheet->setCellValue('C'.$j, 'N° Contrato');
		$sheet->setCellValue('D'.$j, 'RUT Trabajador(a)');
		$sheet->setCellValue('E'.$j, 'Nombres');
		$sheet->setCellValue('F'.$j, 'Apellidos');
		$sheet->setCellValue('G'.$j, 'Estatus');
		$sheet->setCellValue('H'.$j, 'Fecha Desvinculación');
		$sheet->setCellValue('I'.$j, 'Motivo Desvinculación');
		$sheet->setCellValue('J'.$j, 'Sexo');
		$sheet->setCellValue('K'.$j, 'Fecha Nacimiento');
		$sheet->setCellValue('L'.$j, 'Trabajador(a) con Discapacidad');
		$sheet->setCellValue('M'.$j, 'Tipo de Contrato');
		$sheet->setCellValue('N'.$j, 'Fecha de Contratación');
		$sheet->setCellValue('O'.$j, 'Jornada');
		$sheet->setCellValue('P'.$j, 'Cantidad de horas semanales');
		$sheet->setCellValue('Q'.$j, 'Cargo');
		$sheet->setCellValue('R'.$j, 'Sueldo Base Actual');
		$sheet->setCellValue('S'.$j, 'AFP');
		$sheet->setCellValue('T'.$j, 'Isapre/Fonasa');
		$sheet->setCellValue('V'.$j, 'Mutualidad');
		$sheet->setCellValue('W'.$j, 'Costo Empresa');



		$j=10;
		foreach ($datos as $dato) {
			$sheet->setCellValue('A'.$j, strtoupper(strtolower($dato->RUT)));
			$sheet->setCellValue('B'.$j, strtoupper(strtolower($dato->RazonSocial)));
			$sheet->setCellValue('C'.$j, strtoupper(strtolower($dato->cont_numero)));
			$sheet->setCellValue('D'.$j, strtoupper(strtolower($dato->rutP)));
			$sheet->setCellValue('E'.$j, strtoupper(strtolower($dato->Nombres)));
			$sheet->setCellValue('F'.$j, strtoupper(strtolower($dato->Apellidos)));
			$sheet->setCellValue('G'.$j, $dato->IdEstatus = 1 ? "Activo" : "Inactivo");
			$sheet->setCellValue('J'.$j, $dato->Sexo = 1 ? "Masculino" : "Femenino");
			$sheet->setCellValue('K'.$j, strtoupper(strtolower($dato->FechaNacimiento)));
			$sheet->setCellValue('M'.$j, strtoupper(strtolower($dato->IdTipoContrato)));
			$sheet->setCellValue('N'.$j, strtoupper(strtolower($dato->FechaInicioFaena)));
			$j++;
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Planilla_datos_trabajadores'.$hoy.'.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;

	}

}
