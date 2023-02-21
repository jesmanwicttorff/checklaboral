<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Encuestados;
use App\Models\Encuestadoscategorias;
use App\Models\Encuestadospreguntas;
use App\Library\MyDocuments;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use PDF;

class EncuestadosController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'encuestados';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Encuestados();

		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'encuestados',
			'pageUrl'			=>  url('encuestados'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('encuestados.index',$this->data);
	}

	public function postData( Request $request)
	{
		$sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
		$order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
		// End Filter sort and order for query
		// Filter Search for query
		$filter = '';
		if(!is_null($request->input('search')))
		{
			$search = 	$this->buildSearch('maps');
			$filter = $search['param'];
			$this->data['search_map'] = $search['maps'];
		}

		$page = $request->input('page', 1);
		$params = array(
			'page'		=> $page ,
			'limit'		=> (!is_null($request->input('rows')) ? filter_var($request->input('rows'),FILTER_VALIDATE_INT) : $this->info['setting']['perpage'] ) ,
			'sort'		=> $sort ,
			'order'		=> $order,
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		// Get Query
		$results = $this->model->getRows( $params );

		// Build pagination setting
		$page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
		$pagination = new Paginator($results['rows'], $results['total'], $params['limit']);
		$pagination->setPath('encuestados/data');

		$this->data['param']		= $params;
		$this->data['rowData']		= $results['rows'];
		// Build Pagination
		$this->data['pagination']	= $pagination;
		// Build pager number and append current param GET
		$this->data['pager'] 		= $this->injectPaginate();
		// Row grid Number
		$this->data['i']			= ($page * $params['limit'])- $params['limit'];
		// Grid Configuration
		$this->data['tableGrid'] 	= $this->info['config']['grid'];
		$this->data['tableForm'] 	= $this->info['config']['forms'];
		$this->data['colspan'] 		= \SiteHelpers::viewColSpan($this->info['config']['grid']);
		// Group users permission
		$this->data['access']		= $this->access;
		// Detail from master if any
		$this->data['setting'] 		= $this->info['setting'];

		// Master detail link if any
		$this->data['subgrid']	= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
		// Render into template
		return view('encuestados.table',$this->data);

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
			$this->data['row'] 		= $this->model->getColumnTable('tbm_encuestas');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);

		$this->data['id'] = $id;
		$this->data['categorias'] = Encuestadoscategorias::get();

		return view('encuestados.form',$this->data);
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
			return view('encuestados.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}

	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbm_encuestas ") as $column)
        {
			if( $column->Field != 'encuesta_id')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbm_encuestas (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbm_encuestas WHERE encuesta_id IN (".$toCopy.")";
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

	function postSave( Request $request, $id =0)
	{
		$rules = $this->validateForm();
		$lintIdUser = \Session::get('uid');
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {

			$periodicidad=$request->periodicidad;
			$periodo=$request->periodo;
			$ano=$request->ano;
			$observaciones = $request->observaciones;

			$idtipodocumento = $request->IdTipoDocumento;

			$encuesta = Encuestados::where('IdTipoDocumento',$idtipodocumento)->first();
			/*
			if($encuesta){
				return response()->json(array(
					'message'	=> "Debe definir otro documento para usar en la encuesta",
					'status'	=> 'error'
				));
			}
			*/
			$fecha = date('Y-m-d H:i');
			$date = date('Y-m');
			$date = $date.'-01';
			$data = $this->validatePost('tbm_encuestas');
			$data['created_at'] = $fecha;
			$data['periodicidad'] = $periodicidad;
			$data['periodo'] = $periodo;
			$data['ano'] = $ano;
			$data['entry_by'] = $lintIdUser;
			$data['observacion'] = $observaciones;
			$id = $this->model->insertRow($data , $request->input('encuesta_id'));
			$IdKpi = $request->input('IdKpi');
			$cantidadPreguntas = \DB::table('tbl_tipo_documento_valor')->where('IdTipoDocumento',$idtipodocumento)->count();
			\DB::table('tbl_kpi_encuesta')->insert(['encuesta_id'=>$id,'createOn'=>$fecha, 'kpi_id'=>$IdKpi]);
			for($i=0;$i<$cantidadPreguntas;$i++){
				$categorias = count($request->cat);
						if(isset($categorias)){
							\DB::table('tbm_relacion_encuesta_categoria_pregunta')->insert(['pregunta_id'=>$request->preg[$i],'categoriaPregunta_id'=>$request->cat[$i],'encuesta_id'=>$id,'ponderacion'=>$request->ponderacion[$i]]);
						}
			}

			$documento = \DB::table('tbl_tipos_documentos')->where('IdTipoDocumento',$idtipodocumento)->first();

			switch ($documento->Entidad) {
				case '1':
						$contratistas = \DB::table('tbl_contratistas')->get();
						foreach ($contratistas as $ct) {
							$iddocumento = \DB::table('tbl_documentos')->insertGetId(['IdTipoDocumento'=>$idtipodocumento,'Entidad'=>$documento->Entidad,'identidad'=>$ct->IdContratista,'vencimiento'=>0,'IdEstatus'=>1,'entry_by_access'=>$ct->entry_by_access,'entry_by'=>1 ,'IdContratista'=>$ct->IdContratista,'FechaEmision'=>$date,'createdOn'=>$fecha]);
							\DB::table('tbm_encuestas_documentos')->insert(['encuesta_id'=>$id,'IdDocumento'=>$iddocumento]);
						}
					break;
				case '2':
					$kpi = \DB::table('tbl_kpi')->where('kpi_id',$IdKpi)->first();

					$contratos = \DB::table('tbl_contrato')
						->join('tbl_kpi','tbl_contrato.contrato_id','=','tbl_kpi.contrato_id')
						->where('tbl_contrato.cont_estado',1)
						->where('tbl_kpi.descripcion','LIKE',$kpi->descripcion)
						->where('tbl_kpi.porcentaje',$kpi->porcentaje)
						->where(function ($query) {
							$query->where('encuesta_id', '=', 0)
										->orWhere('encuesta_id', '=', null);
						})
						->groupBy('tbl_contrato.contrato_id')
						->get();
					if(count($contratos)>0){
						foreach ($contratos as $ct) {
							$iddocumento = \DB::table('tbl_documentos')->insertGetId(['IdTipoDocumento'=>$idtipodocumento,'Entidad'=>$documento->Entidad,'identidad'=>$ct->contrato_id,'vencimiento'=>0,'IdEstatus'=>1,'entry_by_access'=>$ct->entry_by_access,'entry_by'=>1 ,'contrato_id'=>$ct->contrato_id,'IdContratista'=>$ct->IdContratista,'FechaEmision'=>$date,'createdOn'=>$fecha]);
							\DB::table('tbm_encuestas_documentos')->insert(['encuesta_id'=>$id,'IdDocumento'=>$iddocumento, 'IdTipoDocumento'=>$idtipodocumento , 'contrato_id'=>$ct->contrato_id]);
						}

						\DB::table('tbl_kpi')
							->where('descripcion','LIKE',$kpi->descripcion)
							->where('porcentaje',$kpi->porcentaje)
							->where(function ($query) {
	            	$query->where('encuesta_id', '=', 0)
	                 		->orWhere('encuesta_id', '=', null);
	            })
							->update(['encuesta_id'=>$id]);

					}

					break;
				default:
					// code...
					break;
			}


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
			$ids = $request->input('ids');

			foreach ($ids as $id) {
				\DB::table('tbm_relacion_encuesta_categoria_pregunta')->where('encuesta_id',$id)->delete();
				\DB::table('tbm_relacion_encuesta_categoria')->where('encuesta_id',$id)->delete();
				//aca falta agregar borrados
				\DB::table('tbm_encuestas_documentos')->where('encuesta_id',$id)->delete();
			}

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
		$model  = new Encuestados();
		$info = $model::makeInfo('encuestados');

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
				return view('encuestados.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'encuesta_id' ,
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
			return view('encuestados.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbm_encuestas');
			 $this->model->insertRow($data , $request->input('encuesta_id'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}

	public function postCats(Request $request){

		$idtipodocumento = $request->IdTipoDocumento;

		$preguntas = \DB::table('tbl_tipo_documento_perfil')
			->join('tbl_tipo_documento_valor','tbl_tipo_documento_perfil.IdTipoDocumento','=','tbl_tipo_documento_valor.IdTipoDocumento')
			->where('tbl_tipo_documento_valor.IdTipoDocumento',$idtipodocumento)
			->where('tbl_tipo_documento_valor.Solicitar',1)
			->select('tbl_tipo_documento_valor.Etiqueta','tbl_tipo_documento_valor.TipoValor','tbl_tipo_documento_valor.Requerido','tbl_tipo_documento_valor.IdTipoDocumentoValor')
			->groupBy('tbl_tipo_documento_valor.IdTipoDocumentoValor')
			->get();

		$categorias = Encuestadoscategorias::where('IdTipoDocumento',$idtipodocumento)->get();

		$data['preguntas'] = $preguntas;
		$data['categorias'] = $categorias;

		return $data;
	}

	public function getRespuestas(Request $request, $id = null){
		$pos = strpos($id, '=');

		if ($pos === false){
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
		}
		else{

		}

		$valor = explode('=', $id);
		$doc = $valor[1];
		$encuesta =  \DB::table('tbm_encuestas_documentos')
			->join('tbm_encuestas','tbm_encuestas_documentos.encuesta_id','=','tbm_encuestas.encuesta_id')
			->where("IdDocumento","=",$doc)->first();

		$data['encuesta'] = $encuesta;

		$categorias = \DB::table('tbm_relacion_encuesta_categoria_pregunta')
			->join('tbm_encuestas_categorias_preguntas','tbm_encuestas_categorias_preguntas.categoriaPregunta_id','=','tbm_relacion_encuesta_categoria_pregunta.categoriaPregunta_id')
			//->join('tbl_tipo_documento_valor','tbm_relacion_encuesta_categoria_pregunta.pregunta_id','=','tbl_tipo_documento_valor.IdTipoDocumentoValor')
			->where('tbm_relacion_encuesta_categoria_pregunta.encuesta_id',$encuesta->encuesta_id)->distinct()->select('tbm_relacion_encuesta_categoria_pregunta.categoriaPregunta_id','tbm_encuestas_categorias_preguntas.tituloCategoria')->get();

		$preguntas = \DB::table('tbm_relacion_encuesta_categoria_pregunta')
			->join('tbm_encuestas_categorias_preguntas','tbm_encuestas_categorias_preguntas.categoriaPregunta_id','=','tbm_relacion_encuesta_categoria_pregunta.categoriaPregunta_id')
			->join('tbl_tipo_documento_valor','tbm_relacion_encuesta_categoria_pregunta.pregunta_id','=','tbl_tipo_documento_valor.IdTipoDocumentoValor')
			->where('tbm_relacion_encuesta_categoria_pregunta.encuesta_id',$encuesta->encuesta_id)->distinct()->select('tbm_relacion_encuesta_categoria_pregunta.categoriaPregunta_id','tbm_encuestas_categorias_preguntas.tituloCategoria','tbl_tipo_documento_valor.Etiqueta','Solicitar','Requerido','TipoValor','tbl_tipo_documento_valor.IdTipoDocumentoValor')->get();

		$data['categorias'] = $categorias;
		$data['preguntas'] = $preguntas;

		$documento = \DB::table('tbl_documentos')->where('IdDocumento',$doc)->first();
		$contratista = \DB::table('tbl_contratistas')->where('IdContratista',$documento->IdContratista)->first();
		if($documento->Entidad==1){
			if($contratista){
				$contrato = \DB::table('tbl_contrato')->where('IdContratista',$documento->IdContratista)->first();
			}

		}
		if($documento->Entidad==2){
			$contrato = \DB::table('tbl_contrato')->where('contrato_id',$documento->IdEntidad)->first();
		}

		$data['contratista']=$contratista;
		$data['contrato']=$contrato;
		//\Log::info(print_r($data['row'],true));

		return view('encuestados.formpublic',$data);
	}

	public function postSaverespuestas(Request $request){

		$lintIdUser = \Session::get('uid');
		$IdDocumento = $request->IdDocumento;
		$doc = \DB::table('tbl_documentos')->where('IdDocumento',$IdDocumento)->first();
		$observaciones = $request->observaciones;
		$encuesta_id = \DB::table('tbm_encuestas_documentos')->where('IdDocumento',$IdDocumento)->value('encuesta_id');

		$idtipodocumentos = $request->bulk_IdTipoDocumentoValor;
		$respuestas = $request->bulk_Valor;
		$hoy = date('Y-m-d H:i');

		foreach ($idtipodocumentos as $key => $value) {
			if(isset($respuestas[$key])){

				if(is_array($respuestas[$key])){
					\DB::table('tbl_documento_valor')->insert(['IdDocumento'=>$IdDocumento,'IdTipoDocumentoValor'=>$value,'Valor'=>$respuestas[$key][0],'created_at'=>$hoy,'entry_by'=>$lintIdUser]);
				}else{
					\DB::table('tbl_documento_valor')->insert(['IdDocumento'=>$IdDocumento,'IdTipoDocumentoValor'=>$value,'Valor'=>$respuestas[$key],'created_at'=>$hoy,'entry_by'=>$lintIdUser]);
				}

			}
		}

		\DB::table('tbl_documento_valor_encuesta')->insert(['encuesta_id'=>$encuesta_id,'IdDocumento'=>$IdDocumento,'observacion'=>$observaciones]);

		$aprobadores = \DB::table('tbl_perfil_aprobacion')->where('IdTipoDocumento',$doc->IdTipoDocumento)->count();

		if($aprobadores>0){
			\DB::table('tbl_documentos')->where('IdDocumento',$IdDocumento)->update(['IdEstatus'=>2,'updatedOn'=>$hoy]);
		}else{
			\DB::table('tbl_documentos')->where('IdDocumento',$IdDocumento)->update(['IdEstatus'=>5,'updatedOn'=>$hoy]);
		}

		return Redirect::back();
	}

	public function encuesta($idDocumento){

		$data['fecha'] = date('Y-m-d H:i');
		$lintIdUser = \Session::get('uid');
		$donwloadBy = \DB::table('tb_users')->where('id', $lintIdUser)->first();
		$data['user'] = $donwloadBy;
		$documento = \DB::table('tbl_documentos')->where('idDocumento', $idDocumento)->first();

		$encuesta = Encuestados::where('IdTipoDocumento', $documento->IdTipoDocumento)->first();

		$data['user'] = $donwloadBy;
		$data['title'] = $encuesta->titulo;

		//$data['contrato'] = \DB::table('tbl_contrato')->where('contrato_id', $documento->contrato_id)->first();
		$data['contratista'] = \DB::table('tbl_contratistas')->where('IdContratista', $documento->IdContratista)->first();
		$data['logo'] = $this->clienteLogo();
		foreach($encuesta->categorias as $key => $categoria){
			$data['Categorias'][$key] = $categoria;
			$preguntas = \DB::table('tbm_relacion_encuesta_categoria_pregunta')
							->join('tbl_tipo_documento_valor','pregunta_id','=','IdTipoDocumentoValor')
							->join('tbl_documento_valor','tbl_tipo_documento_valor.IdTipoDocumentoValor','=','tbl_documento_valor.IdTipoDocumentoValor')
							->where('encuesta_id', $encuesta->encuesta_id)
							->where('categoriaPregunta_id', $categoria->categoriaPregunta_id)
							->where('tbl_documento_valor.IdDocumento', $idDocumento)
							->get();

			$data['Categorias'][$key]['preguntas'] = $preguntas;

		}
		//dd($data);

		$pdf = PDF::loadView('encuestados.pdf',$data)
		->setOption('page-width', '400')
		 ->setOption('page-height', '200')
		->setOption('margin-top',0)
		->setOption('margin-bottom',0)
		->setOption('margin-left',0)
		->setOption('margin-right',0);
		//muestro el pdf por pantalla
		return $pdf->download('prueba.pdf');
		return view('encuestados.pdf', $data);
	}

	function clienteLogo(){

		$cliente = \DB::table('tbl_configuraciones')->where('nombre','CNF_APPNAME')->first();
				switch ($cliente->Valor) {
					case 'CCU':
						$logo = 'images/ccu.png';
						return $logo;
						break;
					case 'Ohl Industrial':
						$logo = 'images/logoohl.png';
						return $logo;
						break;
					case 'Abastible':
						$logo = 'images/abastible.svg';
						return $logo;
						break;
					default:
						$logo = 'images/check.png';
						return $logo;
						break;
				}
	}

}
