<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\cargaacreditacion;
use App\Models\Cargaf;
use App\Models\Tiposcontratospersonas;
use App\Library\MyDocuments;
use App\Library\MyDocumentsContractPerson;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class cargaacreditacionController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'cargaacreditacion';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new cargaacreditacion();
		$this->modelupload = new Cargaf();
		$this->modelview = new  \App\Models\Documentovalor();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'cargaacreditacion',
			'pageUrl'			=>  url('cargaacreditacion'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('cargaacreditacion.index',$this->data);
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
                        $filter = " AND tbl_tipos_documentos.IdTipoDocumento IN ( SELECT idTipoDocumento FROM tbl_tipo_documento_perfil WHERE IdPerfil = '".$lintGroupUser."') AND tbl_tipos_documentos.Acreditacion = 1 "



                        ;
		}else{
			$filter = " and tbl_tipos_documentos.Acreditacion = 1 ";
		}

        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
		$filter .= ' AND ( (tbl_documentos.contrato_id IN ('.$lobjFiltro['contratos'].') AND tbl_documentos.IdContratista Is Null ) OR ( tbl_documentos.entidad = 1 AND tbl_documentos.IdEntidad IN ('.$lobjFiltro['contratistas'].') ) OR ( tbl_documentos.contrato_id IN ('.$lobjFiltro['contratos'].') AND tbl_documentos.IdContratista IN ('.$lobjFiltro['contratistas'].') ) OR ( tbl_documentos.IdContratista IN ('.$lobjFiltro['contratistas'].')  ) ) ';

       	$lintIdEstatus = 1;
		if (!is_null($request->input('IdEstatus'))){
			$lintIdEstatus=$request->input('IdEstatus');
		}


		if($lintIdEstatus==0){
		  $filter .= ' AND ( tbl_documentos.IdEstatus in (1,3) or ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 ) ';
		}else{
			//Cambiando forma de identificar Vencidos
			if($lintIdEstatus==8){ //solo se muestran los vencidos con marca de vencimiento
                $filter .= " AND ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 and tbl_documentos.Vencimiento=1 ";
			}else{
                $filter .= " AND tbl_documentos.IdEstatus='".$request->input('IdEstatus')."' AND ifnull(tbl_documentos.IdEstatusDocumento,1) != 2 ";
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
                    $docRel = \DB::table('tbl_documentos')->where('IdDocumentoRelacion', $id)->pluck('IdDocumento');

                if (!(intval($tipo)==3 || intval($tipo)==6 || intval($tipo)==21 || intval($tipo)==31)){
                    if ($estatusDoc==2){
                        if ($docRel)
                            $larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('cargaacreditacion/update/'.$docRel[0]).'" onclick="SximoModal(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="Renovar"><i class="fa  fa-calendar-times-o"></i></a></div>';
                        else
                            $larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('cargaacreditacion/update/'."-".$id).'" onclick="SximoModal(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="Renovar"><i class="fa  fa-calendar-times-o"></i></a></div>';
                    }
                    else{
                        $now = new \DateTime();
                        $FechaVen = new \DateTime($Fvencimiento);
                        $diff = $FechaVen->diff($now);
                        if ($diff->days <= $vencimiento){
                            if ($docRel)
                                $larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('cargaacreditacion/update/'.$docRel).'" onclick="SximoModal(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="Renovar"><i class="fa  fa-calendar-times-o"></i></a></div>';
                            else
                                $larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('cargaacreditacion/update/'."-".$id).'" onclick="SximoModal(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="Renovar"><i class="fa  fa-calendar-times-o"></i></a></div>';
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
				}else{
					if($this->access['is_edit'] =='1') {
						$larrResultTemp['action'] = '<div class=" action dropup"><a href="'.\URL::to('cargaacreditacion/update/'.$id).'" onclick="SximoModal(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class="fa  fa-upload"></i></a></div>';
					}else{
						$larrResultTemp['action'] ='<div></div>';
					}
				}
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
		return view('cargaacreditacion.table',$this->data);

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

		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;

		if ($lobjDatosDocumento->TipoDocumento->IdProceso == 17) { //Multa generales
			return view('cargaacreditacion.multasgeneral',$this->data);
		}elseif ($lobjDatosDocumento->TipoDocumento->IdProceso == 77) { // Multas chile
			return view('cargaacreditacion.formmultas',$this->data);
		}elseif ($lobjDatosDocumento->TipoDocumento->IdProceso == 79) { //Situación Tributaria
			return view('cargaacreditacion.situaciontributaria',$this->data);
		}else{
	        return view('cargaacreditacion.form',$this->data);
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
			return view('cargaacreditacion.view',$this->data);

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
		$model  = new cargaacreditacion();
		$info = $model::makeInfo('cargaacreditacion');

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
				return view('cargaacreditacion.public.view',$data);
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
			return view('cargaacreditacion.public.index',$data);
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

}
