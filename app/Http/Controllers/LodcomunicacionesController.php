<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Lodcomunicaciones;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect;

class LodcomunicacionesController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'lodcomunicaciones';
	static $per_page	= '10';
	
	public function __construct() 
	{
		parent::__construct();
		$this->model = new Lodcomunicaciones();
		
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'lodcomunicaciones',
			'pageUrl'			=>  url('lodcomunicaciones'),
			'return' 			=> 	self::returnUrl()	
		);		
				
	} 
	
	public function getIndex()
	{
		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
				
		$this->data['access']		= $this->access;	
		return view('lodcomunicaciones.index',$this->data);
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
		$pagination->setPath('lodcomunicaciones/data');
		
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
		return view('lodcomunicaciones.table',$this->data);

	}

	function EmailNotificacion($pintIdTicket,$pstrTipoEmail,$pintIdTipoNotificacion = "1"){
		$fetchMode = \DB::getFetchMode();
		\DB::setFetchMode(\PDO::FETCH_ASSOC);
		$lobjNotificaciones = \DB::table("tbl_tickets_notificacion")
								   ->join('tb_users', 'tb_users.id', '=', 'tbl_tickets_notificacion.entry_by')
								   ->join('tbl_tickets', 'tbl_tickets.IdTicket', '=', 'tbl_tickets_notificacion.IdTicket')
								   ->join('tbl_contrato', 'tbl_contrato.contrato_id', '=', 'tbl_tickets.contrato_id')
								   ->join('tbl_contratistas', 'tbl_contratistas.IdContratista', '=', 'tbl_contrato.IdContratista')
								   ->leftJoin('vw_ticket_last_thread_join', 'vw_ticket_last_thread_join.IdTicket', '=', 'tbl_tickets.IdTicket')
								   ->leftJoin('tbl_tickets_tipos', 'tbl_tickets_tipos.IdTicketTipo', '=', 'tbl_tickets.IdTipo')
		                           ->where("tbl_tickets_notificacion.IdTicket","=",$pintIdTicket)
		                           ->where("tbl_tickets_notificacion.IdEstatus","=","1")
		                           ->distinct()
		                           ->select("tb_users.email","tb_users.first_name", "tb_users.last_name", "tbl_tickets.Titulo", "tbl_contrato.cont_numero", "tbl_contrato.cont_nombre", "tbl_contratistas.RUT", "tbl_contratistas.RazonSocial", "vw_ticket_last_thread_join.Mensaje", "vw_ticket_last_thread_join.entry_by_name","tbl_tickets.IdPrioridad",\DB::raw("tbl_tickets_tipos.Descripcion as Tipo"))
		                           ->get();
		\DB::setFetchMode($fetchMode);
		if($pstrTipoEmail=="lod_nuevotema"){
			$lstrAsunto = "LO - Nuevo tema creado: ";
				}else{	
			$pstrTipoEmail=="lod_nuevocomentario";
			if($pintIdTipoNotificacion=="1"){
			  $lintModificacion = "1";
			  $lstrAsunto = "LO - Nuevo comentario al tema: ";
			}elseif($pintIdTipoNotificacion=="2"){
			  $lintModificacion = "2";
			  $lstrAsunto = "LO - Se cerró el tema: ";
			}elseif($pintIdTipoNotificacion=="3"){
			  $lintModificacion = "3";
			  $lstrAsunto = "LO - Se abrió el tema: ";
			}elseif($pintIdTipoNotificacion=="4"){
			  $lintModificacion = "4";
			  $lstrAsunto = "LO - Se cambió la prioridad del tema: ";
			}
		}
        foreach ($lobjNotificaciones as $larrNotificaciones) {
        	if (!empty($lintModificacion)){
        		$larrNotificaciones['Modificacion'] = $lintModificacion;
        	}
        	$this->Email($pstrTipoEmail, $larrNotificaciones['email'], $lstrAsunto.$larrNotificaciones['Titulo'], $larrNotificaciones);
        }
	}
	
	public function postUploadfile(){

	  $larrResultUpload = array();
	  $destinationPath = "uploads/documents/";
      
      $files = Input::file("DocumentoURL");
      foreach ($files as $file) {
      	  $filename = $file->getClientOriginalName();
		  $extension =$file->getClientOriginalExtension();
	      $rand = rand(1000,100000000);
		  $newfilename = strtotime(date('Y-m-d H:i:s')).'-'.$rand.'.'.$extension;
		  $uploadSuccess = $file->move($destinationPath, $newfilename);

	      $lstrPeso = $_FILES['DocumentoURL']['size'];
		  $lstrTipo = $_FILES['DocumentoURL']['type'];

	      $lstrNombreFull = $destinationPath.$newfilename;
	      
	      //Insertamo el documento de manera temporal para luego ser adjuntado 
	      $id = 1;
	      $larrResultUpload[] = array("name"=>$filename, "uploadname"=> $newfilename, "weight" => $lstrPeso, "type" => $lstrTipo, "previewId" => 0);
      }
      if ($id) {
	    echo json_encode(array("code"=>"1","result"=>$larrResultUpload, "message" => "documento cargado satisfactoriamente"));
	  }else{
        echo json_encode(array("code"=>"0","result"=>"Error insert into documents"));
	  }
	}
	function Email($email, $to, $subject, $data ){
		$this->to = $to;
		$this->subject = $subject;

		$data['to']			= $to;
		$data['subject']	= $subject;

		if(CNF_MAIL =='swift')
		{ 
		    \Mail::queue("user.emails.".$email, $data, function ($message) use ($data) {
	    		$message->to($data['to'])->subject($data['subject']);
	    	});
		}  else {
			$message = view("user.emails.".$email, $data);
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: '.CNF_APPNAME.' <'.CNF_EMAIL.'>' . "\r\n";
			mail($to, $subject, $message, $headers);	
		}
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
			$this->data['row'] 		= $this->model->getColumnTable('tbl_tickets_thread'); 
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		
		$this->data['id'] = $id;

		return view('lodcomunicaciones.form',$this->data);
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
			return view('lodcomunicaciones.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));		
		}		
	}	


	function postCopy( Request $request)
	{
		
	    foreach(\DB::select("SHOW COLUMNS FROM tbl_tickets_thread ") as $column)
        {
			if( $column->Field != 'IdTicketThread')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));
			
					
			$sql = "INSERT INTO tbl_tickets_thread (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_tickets_thread WHERE IdTicketThread IN (".$toCopy.")";
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
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_tickets_thread');
			
			$id = $this->model->insertRow($data , $request->input('IdTicketThread'));
			
			if ($id){
				//Pregunto si tiene documentos adjuntos
				$larrAttachments = $_POST['larrAttachments'];
				$larrAttachments = json_decode($_POST['larrAttachments']);
				if ($larrAttachments){
					foreach ($larrAttachments as $larrDocumentos) {
						//Insertamos los documentos en la tabla de adjuntos
						$larrData = array("IdTicketThread" => $id, 
										  "Nombre" => $larrDocumentos->name,
							              "DocumentoURL" => $larrDocumentos->uploadname, 
							              "Peso" => $larrDocumentos->weight[0]
							              );
						\DB::table('tbl_tickets_adjuntos')
						    ->insert($larrData);
					}
				}
				
				//Por ser un nuevo mensaje se agrega ya visto a la persona que lo está creando: 
				$larrInsertTicket = array("IdTicketThread"=>$id,
					                      "entry_by"=>\Session::get('uid'));
				\DB::table('tbl_tickets_vistas')->insert($larrInsertTicket);

				//Actualizamos la fecha de modificacion
				$lstrResultado = \DB::table("tbl_tickets")->where("IdTicket","=",$data['IdTicket'])->update(array("updatedOn" => date('Y-m-d H:i:s')) );

				$this->EmailNotificacion($data['IdTicket'],"lod_nuevocomentario","1");
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
		$model  = new Lodcomunicaciones();
		$info = $model::makeInfo('lodcomunicaciones');

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
				return view('lodcomunicaciones.public.view',$data);
			} 

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdTicketThread' ,
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
			return view('lodcomunicaciones.public.index',$data);			
		}


	}

	function postSavepublic( Request $request)
	{
		
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_tickets_thread');		
			 $this->model->insertRow($data , $request->input('IdTicketThread'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}	
	
	}	
				

}