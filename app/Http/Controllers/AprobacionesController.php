<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Aprobaciones;
use App\Models\Documentos;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use App\Library\MyDocuments;
use App\Library\MyRequest;

class AprobacionesController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'aprobaciones';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Aprobaciones();

		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'aprobaciones',
			'pageUrl'			=>  url('aprobaciones'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('aprobaciones.index',$this->data);
	}

	public function getShowlist( Request $request)
	{

		$lintIdUser = \Session::get('uid');
		$lintGroupUser = \MySourcing::GroupUser($lintIdUser);
		$lintLevelUser = \MySourcing::LevelUser($lintIdUser);
		$sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
		$order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
		$filter = " ";
		$lintIdEstatus = 0;

		if (!is_null($request->input('IdEstatus'))){
			$lintIdEstatus=$request->input('IdEstatus');
		}else{
			$lintIdEstatus=2;
		}

        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
        	$filter .= " AND ( tbl_documentos.contrato_id IN (".$lobjFiltro['contratos'].') OR ( tbl_documentos.entidad = 1 AND tbl_documentos.IdEntidad IN ('.$lobjFiltro['contratistas'].')) ) ';


        if($lintIdEstatus==0){
            $filter .= " ";
        }else{
            //Cambiando forma de identificar Vencidos
            if($lintIdEstatus==8){
                $filter .= " AND ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 ";
            }else{
                $filter .= " AND tbl_documentos.IdEstatus='".$request->input('IdEstatus')."' "; // AND ifnull(tbl_documentos.IdEstatusDocumento,1) != 2
            }
        }
		if ($lintLevelUser != 1 && $lintLevelUser!=7){
         	$filter .= " AND EXISTS ( SELECT 1 FROM tbl_perfil_aprobacion WHERE tbl_documentos.IdTipoDocumento = tbl_perfil_aprobacion.IdTipoDocumento AND tbl_perfil_aprobacion.group_id = '".$lintGroupUser."') ";
		}
		if(!is_null($request->input('search')))
		{
			$search = $this->buildSearch('maps');
			$filter = $search['param'];
			$this->data['search_map'] = $search['maps'];
		}

		$page = $request->input('page', 1);
		$params = array(
			'page'		=> $page ,
			'limit'		=> (!is_null($request->input('rows')) ? filter_var($request->input('rows'),FILTER_VALIDATE_INT) : $this->info['setting']['perpage'] ) ,
			'Estatus'	=> $lintIdEstatus,
			'sort'		=> $sort ,
			'order'		=> $order,
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		// Get Query
		$results = $this->model->getRows( $params );

		$larrResult = array();
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
			$larrResultTemp = array('id'=> ++$i);

			if ($row->IdEstatus==2||$lintGroupUser==1){
				$larrResultTemp['checkbox'] = '<input type="checkbox" class="ids" name="ids[]" value="'.$id.'" /> ';
			}else{
				$larrResultTemp['checkbox'] = '';
			}

			foreach ($this->info['config']['grid'] as $field) {
				if($field['view'] =='1') {
					$limited = isset($field['limited']) ? $field['limited'] :'';
					if (\SiteHelpers::filterColumn($limited )){
						if ($field['field'] == 'DocumentoURL') {
							//Preparamos la vista de Ver Documento
							if ($row->{$field['field']}){
								$value = "<a onClick=\"ViewPDF('".$row->{$field['field']}."',".$row->{'IdDocumento'}.",".$row->{'IdEstatus'}.",".$row->{'estado_carga'}.",".$row->{'Tipo'}.");\" class=\"btn btn-xs btn-white tips\"><i class=\"\" ></i>Ver</a>";
							}else{
								if (isset($larrEncuentas[$row->IdTipoDocumento])){ //Es una encuesta
									$value = '<div class=" action dropup"><a href="'.\URL::to('encuestas/update/doc='.$row->{'IdDocumento'}).'" onclick="ViewEncuesta(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class=""></i>Ver</a></div>';
								}else if ($row->{'Tipo'} == 2 && $row->{'IdEstatus'} > 1){
									$value =  "<a onClick=\"ViewPDF('','" .$row->{'IdDocumento'}."');\" class=\"btn btn-xs btn-white tips\"><i class=\"\" ></i>Ver</a>";
								}else{
									$value = '';
								}
							}
						}else{
							$value = \SiteHelpers::formatRows($row->{$field['field']}, $field , $row);
						}
						$larrResultTemp[$field['field']] = $value;
					}
				}
			}
			//$larrResultTemp['action'] = \AjaxHelpers::buttonAction('documentos',$this->access,$id ,$this->info['setting']).\AjaxHelpers::buttonActionInline($id,'IdDocumento');
			$larrResultTemp['action'] = '<div class=" action dropup" >';

			if ($row->IdEstatus==2||$lintGroupUser==1){
				$larrResultTemp['action'] .= '<a href="#" onclick="ajaxApproveInLine(\''.$id.'\',5); return false;"  class="btn btn-xs btn-white tips" title=" Aprobar "><i class="fa  fa-check"></i></a>';
			}
			$larrResultTemp['action'] .= '
                  <a href="javascript://ajax" onclick="valores(\''.$id.'\',3); return false;"   class="btn btn-xs btn-white tips" title=" Rechazar"><i class="fa fa-ban"></i></a>';
            if ($lintLevelUser == 1 || $lintLevelUser==7){
                if (!($row->IdEstatus==5 && $row->IdEstatusDocumento!=2) && $row->IdEstatus!=9)
                 $larrResultTemp['action'] .= '

	            </div>';
            }
			$larrResult[] = $larrResultTemp;
		}

		echo json_encode(array("data"=>$larrResult));


	}

	public function postData( Request $request)
	{
		$params = array(
			'Estatus'	=> (is_null($request->input('Estatus')) ? 2 : $request->input('Estatus') ),
		);
		$this->data['param']		= $params;
		$this->data['tableGrid'] 	= $this->info['config']['grid'];
		$this->data['access']		= $this->access;
		$this->data['setting'] 		= $this->info['setting'];
        $this->data['Lmrechazo']  =  \DB::table('tbl_motivo_rechazo')->where('TipoMotivo', '=', 1)->where('IdEstatus', '=', 1)->get();
        $this->data['Lmanular']  =  \DB::table('tbl_motivo_rechazo')->where('TipoMotivo', '=', 2)->where('IdEstatus', '=', 1)->get();

		return view('aprobaciones.table',$this->data);
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

		$row = $this->model->find($id);
		if($row)
		{
			$this->data['row'] 		=  $row;
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_documentos');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);

		$this->data['id'] = $id;

		return view('aprobaciones.form',$this->data);
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
			return view('aprobaciones.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
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
			\DB::insert($sql);
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

	function postSave( Request $request, $id =0)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_documentos');

			$id = $this->model->insertRow($data , $request->input('IdDocumento'));

			return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success')
				));

		} else {

			$message = $this->validateListError(  $validator->getMessageBag()->toArray() );
			return response()->json(array(
				'message'	=> $message,
				'status'	=> 'error'
			));
		}

	}

	public function postApprovemassive( Request $request)
	{

		if($this->access['is_edit'] ==0) {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_restric')
			));
			die;

		}
		$lstrResultado = array();
		$larrIdDocumento = json_decode($request->input('ids'));
		foreach ($larrIdDocumento as $lintIdDocumento) {
			$lintIdDocumento = $lintIdDocumento->value;
			$lobjDocumentos = (new MyRequest($lintIdDocumento))->getClass();
			$lstrResultado = $lobjDocumentos::Approve();
		}

		return $lstrResultado;

	}

	public function postApprove( Request $request)
	{

		if($this->access['is_edit'] ==0) {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_restric')
			));
			die;

		}

		$lintIdDocumento = $request->input('iddocumento');
		$lobjDocumentos = (new MyRequest($lintIdDocumento))->getClass();
		$lstrResultado = $lobjDocumentos->Approve();
		return $lstrResultado;

	}

	public function postReject( Request $request){

		if($this->access['is_edit'] ==0) {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_restric')
			));
			die;

		}

		$lintIdDocumento = $request->input('iddocumento');
		$lstrObservacion = $request->input('observacion');
		$lobjDocumentos = (new MyRequest($lintIdDocumento))->getClass();
		$lstrResultado = $lobjDocumentos::Reject($lstrObservacion);
		return $lstrResultado;

	}

	public function postRejectmassive( Request $request){

		if($this->access['is_edit'] ==0) {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_restric')
			));
			die;

		}

		$lstrResultado = array();
		$larrIdDocumento = json_decode($request->input('ids'));
		$lstrObservacion = $request->input('observacion');
		foreach ($larrIdDocumento as $lintIdDocumento) {
			$lobjDocumentos = new MyDocuments($lintIdDocumento->value);
			$lstrResultado = $lobjDocumentos::Reject($lstrObservacion);
		}

		return $lstrResultado;

	}

	public function postCancel( Request $request){

		if($this->access['is_edit'] ==0) {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_restric')
			));
			die;

		}

		$lintIdDocumento = $request->input('iddocumento');
		$lstrObservacion = $request->input('observacion');
		$lobjDocumentos = (new MyRequest($lintIdDocumento))->getClass();
		$lstrResultado = $lobjDocumentos::Cancel($lstrObservacion);
		return $lstrResultado;

	}

	public function postDelete( Request $request)
	{

		if($this->access['is_remove'] ==0) {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_restric')
			));
			die;

		}
		// delete multipe rows
		if(count($request->input('ids')) >=1)
		{
			$this->model->destroy($request->input('ids'));

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
		$model  = new Aprobaciones();
		$info = $model::makeInfo('aprobaciones');

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
				return view('aprobaciones.public.view',$data);
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
			return view('aprobaciones.public.index',$data);
		}


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

	function postInfoadicional( Request $request) {

      	$lobjMyDocuments = new MyDocuments($request->input('iddocumento'));
		$lobjDocumento = $lobjMyDocuments->getDatos();
		if ($lobjDocumento){
			$larrData['lobjDocumento']= $lobjDocumento;
			$larrData['lstrDirectorio']= $lobjMyDocuments->getDirectorio();
			$larrData['entidad'] = view('entidades.viewentidad',$lobjMyDocuments->getDatosEntidad());

			$IdProceso = Documentos::join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')->select('tbl_tipos_documentos.IdProceso')->where('tbl_documentos.IdTipoDocumento',$lobjDocumento->IdTipoDocumento)->first()->IdProceso;
			$contrato_id = $lobjDocumento->contrato_id;
			$IdEntidad = $lobjDocumento->IdEntidad;

			if($IdProceso==21){
				$iddoc = \DB::table('tbl_contratos_personas')->where('contrato_id',$contrato_id)->where('IdPersona',$IdEntidad)->first();

				if($iddoc){
						$larrData['lobjDocContrato']=$iddoc;
				}
			}

			$IdProceso = Documentos::join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')->select('tbl_tipos_documentos.IdProceso')->where('tbl_documentos.IdTipoDocumento',$lobjDocumento->IdTipoDocumento)->first()->IdProceso;
			$otroAnexo = self::otrosAnexos( $request->input('iddocumento') , $IdProceso );
			if($otroAnexo){
				$larrData['otroAnexo'] = $otroAnexo;
			}else{
				$larrData['otroAnexo'] = null;
			}

			return view('aprobaciones.ver', $larrData);
		}else{
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}

	}

	public function postInfodocumento( Request $request) {

		$lobjMyDocuments = new MyDocuments($request->input('iddocumento'));
		$lobjDocumento = $lobjMyDocuments->getDatos();

		if ($lobjDocumento){
			$larrData['lobjDocumento']= $lobjDocumento;
			$larrData['lstrDirectorio']= $lobjMyDocuments->getDirectorio();
			$larrData['entidad'] = view('entidades.viewentidad',$lobjMyDocuments->getDatosEntidad());

			$IdProceso = Documentos::join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')->select('tbl_tipos_documentos.IdProceso')->where('tbl_documentos.IdTipoDocumento',$lobjDocumento->IdTipoDocumento)->first()->IdProceso;

			$contrato_id = $lobjDocumento->contrato_id;
			$fecha_emision = $lobjDocumento->FechaEmision;
			$IdEntidad = $lobjDocumento->IdEntidad;

			if($IdProceso==21){
				$iddoc = \DB::table('tbl_contratos_personas')->where('contrato_id',$contrato_id)->where('IdPersona',$IdEntidad)->first();

				if($iddoc){
						$larrData['lobjDocContrato']=$iddoc;
				}
			}

			if($IdProceso==118){ //Liquidacion de sueldo

				$DocJustificativo = Documentos::join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')->where('tbl_documentos.contrato_id',$contrato_id)->where('tbl_tipos_documentos.IdProceso',117)->where('tbl_documentos.FechaEmision',$fecha_emision)->select('tbl_documentos.IdDocumento')->get();

				if(isset($DocJustificativo) and count($DocJustificativo)>0){
					if($DocJustificativo[0]->IdDocumento>0){
						$doc = new MyDocuments($DocJustificativo[0]->IdDocumento);
						$larrData['lobjDocumentoAsistencia']=$doc->getDatos();
					}
				}

				$DocLibro = Documentos::join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')->where('tbl_documentos.contrato_id',$contrato_id)->where('tbl_tipos_documentos.IdProceso',115)->where('tbl_documentos.FechaEmision',$fecha_emision)->select('tbl_documentos.IdDocumento')->get();

				if(isset($DocLibro) and count($DocLibro)>0){
					if( $DocLibro[0]->IdDocumento>0){
						$doc = new MyDocuments($DocLibro[0]->IdDocumento);
						$larrData['lobjLibroAsistencia']=$doc->getDatos();
					}
				}

				$iddoc = \DB::table('tbl_contratos_personas')->where('contrato_id',$contrato_id)->where('IdPersona',$IdEntidad)->select('IdDocumento')->get();

				if(count($iddoc)>0){
					$DocContrato = $iddoc[0]->IdDocumento;

					if(isset($DocContrato) and $DocContrato>0){
						$doc = new MyDocuments($DocContrato);
						$larrData['lobjDocContrato']=$doc->getDatos();
					}
				}

				$Finiquito = Documentos::join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
					->where('tbl_documentos.contrato_id',$contrato_id)
					->where('tbl_tipos_documentos.IdProceso',4)
					->where('tbl_documentos.FechaEmision',$fecha_emision)
					->where('tbl_documentos.IdEntidad',$IdEntidad)
					->select('tbl_documentos.IdDocumento')->get();

				if(!empty($Finiquito) and count($Finiquito)>0){
					$idFiniquito = $Finiquito[0]->IdDocumento;
					$doc = new MyDocuments($idFiniquito);
					$larrData['lobjDocFiniquito']=$doc->getDatos();
					$larrData['existFiniquito']=1;

				}
				$larrData['IdProceso']=118;
			}

			if($IdProceso==4){//finiquito
				$concepto = \DB::table('tbl_anotaciones')->join('tbl_concepto_anotacion','tbl_anotaciones.IdConceptoAnotacion','=','tbl_concepto_anotacion.IdConceptoAnotacion')->where('tbl_anotaciones.IdPersona',$lobjDocumento->IdEntidad)->first();

				$larrData['concepto']=$concepto->Descripcion;

			}

			$otroAnexo = self::otrosAnexos( $request->input('iddocumento') , $IdProceso );
			if($otroAnexo){
				$larrData['otroAnexo'] = $otroAnexo;
			}else{
				$larrData['otroAnexo'] = null;
			}

			return view('aprobaciones.aprobacion', $larrData);
		}else{
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}

    }

	function postInfotipo( Request $request) {

      $lintIdDocumento = $request->input('iddocumento');
      $lstrQuery = " SELECT tbl_tipos_documentos.Tipo
                     FROM tbl_documentos
                     INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento
                     WHERE tbl_documentos.IdDocumento = ".$lintIdDocumento."";
	  $lobjData = \DB::select($lstrQuery);

      if ($lobjData){
        foreach ($lobjData as $p) {
        		$lintIdTipo = $p->Tipo;
  				echo json_encode( array("tipo" => $lintIdTipo));
  	    }
      }
	}
	public function otrosAnexos($idDocumento, $IdProceso){

		if( $IdProceso == 3 ){ //anexos
			$tipoValorDocumento = \DB::table('tbl_documento_valor')->where('tbl_documento_valor.IdDocumento', $idDocumento)->first();
			if(isset($tipoValorDocumento) && count($tipoValorDocumento) > 0 ){
				$tipoValor = $tipoValorDocumento->IdTipoDocumentoValor;
				$valor = $tipoValorDocumento->Valor;
			}
			$tipo =   \DB::table('tbl_tipo_documento_valor')->where('tbl_tipo_documento_valor.Etiqueta', 'Otros anexos')->first();
			if(isset($tipoValorDocumento->IdTipoDocumentoValor)){
				$tipo = $tipo->IdTipoDocumentoValor;
			}
			if(isset($tipoValor) && isset($tipo)){
				if($tipoValor == $tipo){
					$otroAnexo = \DB::table('tbl_otros_anexos')->where('tbl_otros_anexos.id', $valor)->first();
					if(isset($otroAnexo->anexo)){
						return $otroAnexo->anexo;
					}
				}
			}
			return false;
		}

	}


}
