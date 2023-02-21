<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Tipodocumentos;
use App\Models\Tipodocumentosperfil;
use App\Models\Grupoaprobacion;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class TipodocumentosController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'tipodocumentos';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Tipodocumentos();
        $this->modelperfil = new Tipodocumentosperfil();
        $this->modelgrupoaprobacion = new Grupoaprobacion();
		$this->modelview = new  \App\Models\Tipodocumentovalor();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'tipodocumentos',
			'pageUrl'			=>  url('tipodocumentos'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('tipodocumentos.index',$this->data);
	}

	public function postData( Request $request)
	{
		$sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
		$order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
		$params = array(
			'sort'		=> $sort ,
			'order'		=> $order,
			'params'	=> '',
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		$results = $this->model->getRows( $params );
		$this->data['rowData']		= $results['rows'];
		$this->data['tableGrid'] 	= $this->info['config']['grid'];
		$this->data['access']		= $this->access;
		$this->data['setting'] 		= $this->info['setting'];

		// Master detail link if any
		$this->data['subgrid']	= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
		// Render into template
		return view('tipodocumentos.table',$this->data);

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
			$this->data['tipovalor']=  \DB::table('tbl_tipo_documento_valor')->where('IdTipoDocumento',$row['IdTipoDocumento'])->get();
			$this->data['tipodatos']=  \DB::table('tbl_tipo_documento_valor')
			                              ->leftJoin("tbl_tipo_documento_data", 'tbl_tipo_documento_valor.IdTipoDocumentoValor', '=', 'tbl_tipo_documento_data.IdTipoDocumentoValor')
			                              ->where('tbl_tipo_documento_valor.IdTipoDocumento',$row['IdTipoDocumento']) 
			                              ->orderBy("tbl_tipo_documento_valor.IdTipoDocumentoValor","asc")
			                              ->get();
                                       
         $this->data['perfiles']=  \DB::table('tbl_tipo_documento_perfil')
                                       ->Select('IdPerfil')
			                              ->where('tbl_tipo_documento_perfil.idTipoDocumento',$row['IdTipoDocumento'])
			                              ->groupBy("tbl_tipo_documento_perfil.IdPerfil")
			                              ->get();


         $this->data['larrGrupoAprobacion']=  \DB::table('tbl_perfil_aprobacion')
                                   ->Select('group_id')
		                              ->where('tbl_perfil_aprobacion.IdTipoDocumento',$row['IdTipoDocumento'])
		                              ->groupBy("tbl_perfil_aprobacion.group_id")
		                              ->get();
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_tipos_documentos');
			$this->data['tipovalor']=  array();
			$this->data['perfiles']=array();
			$this->data['larrGrupoAprobacion']= array();

		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;

		return view('tipodocumentos.form',$this->data);
	}

   public function postDataperfiles(Request $request){
   
      $datos = \DB::table('tb_groups')->select('group_id','name')->get();

      return response()->json(array(
      	'status'=>'sucess',
      	'valores'=>$datos,
      	'message'=>\Lang::get('core.note_sucess')
      	));
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
			return view('tipodocumentos.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_tipos_documentos ") as $column)
        {
			if( $column->Field != 'IdTipoDocumento')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_tipos_documentos (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_tipos_documentos WHERE IdTipoDocumento IN (".$toCopy.")";
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
   
      $perfil = $request->group_id_aux;
      $larrGrupoAprobacion = $request->group_id_aprobacion_aux;
      $usuario_aux = \Session::get('uid');
		//Transformamos el arreglo en un objeto
		$lobjTipoValor = json_decode($request->tipodocumentovalor);
		$counter =  $request->counter;

		$etiqueta = $request->bulk_Etiqueta;
		$tipoValor = $request->bulk_TipoValor;
		$requerido = $request->bulk_Requerido;
        $solicitar = $request->bulk_Solicitar;
		$DocV = $request->IdDocumentoValor;

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_tipos_documentos');
			if (!isset($data['ControlCheckLaboral'])) {
	        	$data['ControlCheckLaboral'] = 0;
			}
			if (!$data['sst']) {
	        	$data['sst'] = 0;
	        }
			$data['BloqueaAcceso'] = $request->BloqueaAcceso;
			$data['Tipo'] = $request->Tipo;
			$data['group_id'] = $request->group_id;
			$data['Formula'] = $request->Formula;

			//verificamos si la formula es correcta.
			if ($data['Vigencia']==2){
				if ($data['Formula']){
					try {
						$lstrDateFormula = eval("return ".trim($data['Formula']).";");
					}catch(\Exception $e){

					}
					$larrDateFormula = explode("-", $lstrDateFormula);
					if (count($larrDateFormula)==3){
						if (is_numeric($larrDateFormula[1]) || is_numeric($larrDateFormula[2]) || is_numeric($larrDateFormula[0])) {
							if (!checkdate ($larrDateFormula[1],$larrDateFormula[2],$larrDateFormula[0])) {
								return response()->json(array('status'=>'error',
								'message'=> "Formula incorrecta"
								));
							}
						}
					}else{
						return response()->json(array('status'=>'error',
							'message'=> "Formula incorrecta"
							));
					}
				}
			}

			$id = $this->model->insertRow($data , $request->input('IdTipoDocumento'));

			$this->modelperfil->where('idTipoDocumento', $id)->delete();
	        if (!empty($perfil)){
	            foreach ($perfil as $per) {
	              $lobjDocumentoPerfil = $this->modelperfil->firstOrNew(array("idTipoDocumento"=>$id,
							                                                  "IdPerfil" => $per
							              	                           ));
	              $lobjDocumentoPerfil->idUsuario = $usuario_aux;
	              $lobjDocumentoPerfil->idTipoDocumento = $id;
	              $lobjDocumentoPerfil->IdPerfil = $per;
	              $lobjDocumentoPerfil->save();
	            }
	         }

	        $this->modelgrupoaprobacion->where('IdTipoDocumento', $id)->delete();
	        if (!empty($larrGrupoAprobacion)){
	            foreach ($larrGrupoAprobacion as $lintGrupoAprobacion) {
	              $lobjGrupoAprobacion = $this->modelgrupoaprobacion->firstOrNew(array("idTipoDocumento"=>$id,
							                                                  "group_id" => $lintGrupoAprobacion
							              	                           ));
	              $lobjGrupoAprobacion->entry_by = $usuario_aux;
	              $lobjGrupoAprobacion->idTipoDocumento = $id;
	              $lobjGrupoAprobacion->group_id = $lintGrupoAprobacion;
	              $lobjGrupoAprobacion->save();
	            }
	         }

			$tipoV =  \DB::table('tbl_tipo_documento_valor')
			->select('IdTipoDocumentoValor')
			->where('IdTipoDocumento', '=', $request->IdTipoDocumento)
			->orderby('IdTipoDocumentoValor','asc')
			->get();

			//var_dump($DocV);

			if (count($tipoV)>0 ){
				foreach ($tipoV as $value){
					$array[] = $value->IdTipoDocumentoValor;
				}

				if (!(empty($DocV))){
					foreach ($array as $valor) {
						if (!(in_array($valor, $DocV))){
							\DB::table('tbl_tipo_documento_data')->where('IdTipoDocumentoValor', '=', $valor)->delete();
							\DB::table('tbl_tipo_documento_valor')->where('IdTipoDocumentoValor', '=', $valor)->delete();
						}
					}
				}
			}
			for($i = 0; $i<count($counter); $i++){
				$lintIdInsert = $i+1;
				if ((strlen($etiqueta[$i])!=0) && (strlen($tipoValor[$i])!=0) && (strlen($requerido[$i])!=0)){
					if (!(empty($DocV[$i]))){
						\DB::table('tbl_tipo_documento_valor')->where('IdTipoDocumentoValor', $DocV[$i])->update(['Etiqueta' => $etiqueta[$i], 'TipoValor' => $tipoValor[$i], 'Requerido' => $requerido[$i],'Solicitar' => $solicitar[$i]]);
						if ($tipoValor[$i]=="Radio" || $tipoValor[$i]=="CheckBox" || $tipoValor[$i]=="Select Option" ){
	 						if (isset($lobjTipoValor->{$lintIdInsert})) {
	 						  \DB::table('tbl_tipo_documento_data')->where('IdTipoDocumentoValor', '=', $DocV[$i])->delete();
	 						  $is = 0;
	 						  foreach ($lobjTipoValor->{$lintIdInsert}->{"valores"} as $value) {
	 						  	if ($value!="" || $lobjTipoValor->{$lintIdInsert}->{"display"}[$is] != ""){
	 						  	  $IdDocV = \DB::table('tbl_tipo_documento_data')->insertGetId(
							  	  ['IdTipoDocumentoValor' => $DocV[$i], 
							  	   'Valor' => $value,
							  	   'Display' => $lobjTipoValor->{$lintIdInsert}->{"display"}[$is] ]);	
	 						  	}
	 						  	$is += 1;
	 						  }
							}
	 					}
					}
					else{
					$IdDocV = \DB::table('tbl_tipo_documento_valor')->insertGetId(
					['IdTipoDocumento' => $id, 'Etiqueta' => $etiqueta[$i], 'TipoValor' => $tipoValor[$i], 'Requerido' => $requerido[$i],'Solicitar' => $solicitar[$i] ]);
	 					if ($tipoValor[$i]=="Radio" || $tipoValor[$i]=="CheckBox" || $tipoValor[$i]=="Select Option" ){
	 						if (isset($lobjTipoValor->{$lintIdInsert})) {
	 						  \DB::table('tbl_tipo_documento_data')->where('IdTipoDocumentoValor', '=', $IdDocV)->delete();
	 						  $is = 0;
	 						  foreach ($lobjTipoValor->{$lintIdInsert}->{"valores"} as $value) {
	 						  	if ($value!="" || $lobjTipoValor->{$lintIdInsert}->{"display"}[$is] != ""){
	 						  	  $IdDocD = \DB::table('tbl_tipo_documento_data')->insertGetId(
							  	  ['IdTipoDocumentoValor' => $IdDocV, 
							  	   'Valor' => $value,
							  	   'Display' => $lobjTipoValor->{$lintIdInsert}->{"display"}[$is] ]);
	 						  	}
	 						  	$is += 1;
	 						  }
							}
	 					}
					}
				}

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
			$this->model->destroy($request->input('ids'));
			\DB::table('tbl_tipo_documento_valor')->whereIn('IdTipoDocumento',$request->input('ids'))->delete();
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
		$model  = new Tipodocumentos();
		$info = $model::makeInfo('tipodocumentos');

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
				return view('tipodocumentos.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdTipoDocumento' ,
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
			return view('tipodocumentos.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_tipos_documentos');
			 $this->model->insertRow($data , $request->input('IdTipoDocumento'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}


}
