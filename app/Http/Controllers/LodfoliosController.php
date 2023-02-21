<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Http\Controllers\LodComunicacionesController;
use App\Models\Lodfolios;
use App\Models\subtipostemaslo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect;
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\FontMetrics; 

class LodfoliosController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'lodfolios';
	static $per_page	= '10';
	
	public function __construct() 
	{
		parent::__construct();
		$this->modellod = new  \App\Models\Lod();
		$this->model = new Lodfolios();
		$this->modelview = new  \App\Models\Lodcomunicaciones();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'lodfolios',
			'pageUrl'			=>  url('lodfolios'),
			'return' 			=> 	self::returnUrl()	
		);		
				
	} 
	
	public function getIndex( Request $request)
	{
		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
				
		$this->data['access']		= $this->access;
		$this->data['id']           = $request->input('id');
		return view('lodfolios.index',$this->data);
	}	

	public function postData( Request $request)
	{
		$this->data['setting']      = $this->info['setting'];
        $this->data['tableGrid']    = $this->info['config']['grid'];
        $this->data['access']       = $this->access;
        $this->data['id']           = $request->input('id');

        if ($request->input('id')){
	        //Buscamos el contrato
	        $filter = " AND tbl_contrato.contrato_id = ".$request->input('id');
	        $params = array(
				'params'	=> $filter,
				'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
			);
			$results = $this->modellod->getRows( $params );
			if (!empty($results['rows'][0])) {
				$this->data['numero'] = $results['rows'][0]->cont_numero;
				$this->data['contratista'] = $results['rows'][0]->Contratista;
				$this->data['nombre'] = $results['rows'][0]->cont_nombre;
				$this->data['cont_estado'] = $results['rows'][0]->cont_estado;
			}
			return view('lodfolios.table',$this->data);
		}else{
        	return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
        }
	}

	public function Registeractivity($pintIdTicket,$pintIdActivity){
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    	$lintIdUser = \Session::get('uid');
    	$lstrResultado = \DB::table("tbl_tickets_acciones")
    	     ->insert(array("IdTicket"=>$pintIdTicket,"IdAccion"=>$pintIdActivity,"entry_by"=>$lintIdUser));
    	return response()->json(array(
				'status'=>'success',
				'result'=>$lstrResultado,
				'message'=> \Lang::get('core.note_success')
				));	
	}
	public function Changestatus($pintIdTicket, $pintIdEstatus ){
		$lstrResultado = \DB::table("tbl_tickets")->where("IdTicket","=",$pintIdTicket)->update(array("IdEstatus"=>$pintIdEstatus,"updatedOn" => date('Y-m-d H:i:s')) );
		if ($lstrResultado){
			if ($pintIdEstatus==1){
				$lintIdAccion = 3;
			}else{
				$lintIdAccion = 4;
			}
			self::Registeractivity($pintIdTicket,$lintIdAccion);
			if ($pintIdEstatus=="1"){
				$lintIdModificacion = "3";	
			}elseif ($pintIdEstatus=="2"){
				$lintIdModificacion = "2";	
			}
			$this->EmailNotificacion($pintIdTicket,"lod_nuevocomentario",$lintIdModificacion);
		}
		return $lstrResultado;
	}

	public function postChangestatus(Request $request){
		$lintIdTicket = $request->input("idticket");
		$lintIdEstatus = $request->input("idestatus");
		$lstrResultado = self::Changestatus($lintIdTicket, $lintIdEstatus);
		return response()->json(array(
				'status'=>'success',
				'result'=>$lstrResultado,
				'message'=> \Lang::get('core.note_success')
				));	

	}

	public function Changepriority($pintIdTicket, $pintIdPrioridad ){
		$lstrResultado = \DB::table("tbl_tickets")->where("IdTicket","=",$pintIdTicket)->update(array("IdPrioridad"=>$pintIdPrioridad,"updatedOn" => date('Y-m-d H:i:s')));
		if ($lstrResultado){
			if ($pintIdPrioridad==1){
				$lintIdAccion = 5;
			}elseif ($pintIdPrioridad==2){
				$lintIdAccion = 6;
			}elseif ($pintIdPrioridad==3){
				$lintIdAccion = 7;
			}
			self::Registeractivity($pintIdTicket,$lintIdAccion);
			$this->EmailNotificacion($pintIdTicket,"lod_nuevocomentario","4");
		}
		return $lstrResultado;
	}

	public function postChangepriority(Request $request){
		$lintIdTicket = $request->input("idticket");
		$lintIdPrioridad = $request->input("idprioridad");
		$lstrResultado = self::Changepriority($lintIdTicket, $lintIdPrioridad);
		return response()->json(array(
				'status'=>'success',
				'result'=>$lstrResultado,
				'message'=> \Lang::get('core.note_success')
				));	

	}

	public function getShowlist(Request $request){

		 // Get Query
       $sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
		$order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
		// End Filter sort and order for query
		// Filter Search for query
		$lintIdContrato = $request->input('contrato_id');

		$filter = '';
		if(!is_null($request->input('search')))
		{
			$search = 	$this->buildSearch('maps');
			$filter = $search['param'];
			$this->data['search_map'] = $search['maps'];
		}
		$filter .= " AND tbl_tickets.contrato_id = ".$lintIdContrato;

		$lintIdEstatus = $request->input('IdEstatus');
		if ( $lintIdEstatus == "" ){
			$lintIdEstatus = 1;
		}
		if ($lintIdEstatus != 0 ){
			$filter .= " AND tbl_tickets.IdEstatus = ".$lintIdEstatus." ";
		}
		$params = array(
			'page'		=> '',
			'limit'		=> '',
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

		foreach ($results['rows'] as $row) {

			$id = $row->IdTicket;

			$larrResultTemp = array('id'=> ++$i,
								    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$row->IdTicket.'" /> '
								    );
			foreach ($this->info['config']['grid'] as $field) {
				if($field['view'] =='1') {
					$limited = isset($field['limited']) ? $field['limited'] :'';
					if ($field['field']=="Notificacion"){
						    if ($row->{$field['field']}){
						        $checked = 'checked="checked" ';
							}else{
					    	    $checked = '';
							}
							$value = '<input type="checkbox" class="idsnotification" name="idsnotification[]" '.$checked.'value="'.$row->IdTicket.'" data-id="'.$row->contrato_id.'" /> ';
							$larrResultTemp[$field['field']] = $value;
					}else{
					    $value = \SiteHelpers::formatRows($row->{$field['field']}, $field , $row);
					    $larrResultTemp[$field['field']] = $value;
					}
				}
			}
			$lstrBoton = '<a href="'.url('lodfolios/update/'.$id).'" onclick="ajaxViewDetail(\'#lodfolios\',this.href); return false;"  class="btn btn-xs btn-white tips" title="Comunicaciones"><i class="fa fa-comments"></i></a>';
			$larrResultTemp['action'] = $lstrBoton;
			$larrResult[] = $larrResultTemp;

		}

		echo json_encode(array("data"=>$larrResult));

	}
	function postUpdatetimelineaction(Request $request, $id = null) {
		$id = $request->input('id');

		$lobjAcciones = \DB::table("tbl_tickets_acciones")
		                     ->select("tbl_tickets_acciones.createdOn", "tb_users.first_name", "tb_users.last_name", "tb_users.avatar", "tbl_acciones.nombre", "tbl_acciones.descripcion")
		                     ->join("tbl_acciones","tbl_tickets_acciones.IdAccion", "=", "tbl_acciones.IdAccion")
		                     ->join("tb_users","tb_users.id", "=", "tbl_tickets_acciones.entry_by")
		                     ->where("tbl_tickets_acciones.IdTicket","=",$id)
		                     ->orderBy("tbl_tickets_acciones.IdTicketAccion",'desc')
		                     ->get();

		$lstrResultado = '';
		foreach ($lobjAcciones as $rows) {
			$lstrResultado .= '<div class="stream">';
			$lstrResultado .= ' <div class="stream-badge">';
            $lstrResultado .= ' <i class="fa fa-pencil"></i>';
            $lstrResultado .= ' </div>';
            $lstrResultado .= ' <div class="stream-panel">';
            $lstrResultado .= ' <div class="stream-info">';
            $lstrResultado .= ' <a href="#">';
            if ($rows->avatar) {
			$lstrResultado .= '		<img src="'.url('uploads/users/'.$rows->avatar).'">'.PHP_EOL;
			}else{
			$lstrResultado .= '		<img src="'.url('sximo/images/no-image.png').'">'.PHP_EOL;
			}
			$lstrResultado .= ' <span>'.$rows->first_name.' '.$rows->last_name.'</span>';
            $lstrResultado .= ' <span class="date">'.$rows->createdOn.'</span>';
            $lstrResultado .= ' </a>';
            $lstrResultado .= ' </div>';
            $lstrResultado .= $rows->nombre.'';
            $lstrResultado .= ' </div>';
            $lstrResultado .= ' </div>';
		}
		$lstrResultado .= '';

		return response()->json(array(
				'status'=>'success',
				'result'=>$lstrResultado,
				'message'=> \Lang::get('core.note_success')
				));	
	}
	public function getPrint(Request $request){
		
		$id = $request->input("id");

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

		$params = array(
			'params'	=> " AND tbl_tickets.IdTicket = ".$id,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		$row = $this->model->getRows( $params );

		if(isset($row['rows'][0]))
		{
			$this->data['row'] 		=  $row['rows'][0];
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_tickets'); 
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;
        

		$lstrHtmlView =  view('lodfolios.print',$this->data);	
		
		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isPhpEnabled', true);
		$options->set('isRemoteEnabled', true);
		$options->set('isJavascriptEnabled', true);
		$options->set('defaultFont', 'arial');
		$options->set('defaultPaperSize','letter');
		$options->set('defaultPaperOrientation', 'landscape');

		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($lstrHtmlView);
		$dompdf->render();
        $lstrHtmlView = $dompdf->output();

        header('Set-Cookie: fileDownload=true; path=/');
		header('Cache-Control: max-age=60, must-revalidate');
		header("Content-type: application/pdf");
		header('Content-Disposition: attachment; filename="LO_'.$this->data['row']->cont_numero.'_'.date('Ymd_His').'.pdf"');
		echo $lstrHtmlView;

	}
	function postUpdatetimeline(Request $request, $id = null) {

		$id = $request->input('id');
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;
		
		//
		$lstrResultado = "";
		foreach ($this->data['subform']['rowData'] as $rows) {

			$lstrResultado .= '<div class="feed-element" ';
			if (!$rows->IdTicketVista) {
			$lstrResultado .= 'style="background-color:cornsilk;"';
			}
			$lstrResultado .= '>'.PHP_EOL;
            $lstrResultado .= '    <a href="#" class="pull-left">'.PHP_EOL;
            if ($rows->avatar) {
			$lstrResultado .= '		<img src="'.url('uploads/users/'.$rows->avatar).'" class="img-circle" alt="image">'.PHP_EOL;
			}else{
			$lstrResultado .= '		<img src="'.url('sximo/images/no-image.png').'" class="img-circle" alt="image">'.PHP_EOL;
			}
            $lstrResultado .= '    </a>'.PHP_EOL;
            $lstrResultado .= '    <div class="media-body ">'.PHP_EOL;
            $lstrResultado .= '        <small class="pull-right"></small>'.PHP_EOL;
            $lstrResultado .= '        '.$rows->groupname.' <strong>'.$rows->entry_by_name.'</strong><br>'.PHP_EOL;
            $lstrResultado .= '        <small class="text-muted">'.$rows->createdOn.'</small>'.PHP_EOL;
            $lstrResultado .= '        <div class="well">'.PHP_EOL;
			$lstrResultado .= $rows->Mensaje.PHP_EOL;
            $lstrResultado .= '        </div>'.PHP_EOL;

            //Buscamos documentos adjuntos
            $lobjAdjuntos = \DB::table("tbl_tickets_adjuntos")
		       ->where("IdTicketThread","=",$rows->IdTicketThread)->get();
		    if ($lobjAdjuntos){
		    	$lstrResultado .= " - <small>adjuntos</small> <br/> <br/>";
		    	foreach ($lobjAdjuntos as $larrAdjuntos) {
		    		$lstrResultado .= '<i style="color:#999999;" class="fa fa-file"></i> <a download href="'.url('uploads/documents/')."/".$larrAdjuntos->DocumentoURL.'" >'.$larrAdjuntos->Nombre.' </a>  <br/>'.PHP_EOL;
		    	}
		    }

            $lstrResultado .= '    </div>'.PHP_EOL;
            $lstrResultado .= '</div>'.PHP_EOL;

		}

		return response()->json(array(
				'status'=>'success',
				'result'=>$lstrResultado,
				'message'=> \Lang::get('core.note_success')
				));	

	}
	function CreateMessage(Request $request, $id = null){
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$lintIdTicket = $id;
			$data = array(  "IdTicket" => $lintIdTicket,
							"Mensaje" => $request->input('Mensaje'),
							"createdOn" => $request->input('createdOn'),
							"entry_by" => $request->input('entry_by'),
						);

			$id = $this->modelview->insertRow($data , $request->input('IdTicketThread'));
			
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
							              "Peso" => $larrDocumentos->weight[0]);
						\DB::table('tbl_tickets_adjuntos')
						    ->insert($larrData);
					}
				}
				//Por ser un nuevo mensaje se agrega ya visto a la persona que lo está creando: 
				$larrInsertTicket = array("IdTicketThread"=>$id,
					                      "entry_by"=>\Session::get('uid'));
				\DB::table('tbl_tickets_vistas')->insert($larrInsertTicket);

				//$this->EmailNotificacion($data['IdTicket'],"lod_nuevocomentario");
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
	function getCreate(Request $request, $id = null)
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
	
		$this->data['row'] 		= $this->model->getColumnTable('tbl_tickets');

		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['larrTipos'] = \DB::table("tbl_tickets_tipos")->where("tbl_tickets_tipos.IdEstatus","=","1")->get();
		$this->data['larrSubTipos'] = subtipostemaslo::Activos()->get();
		$this->data['id'] = $id;

		return view('lodfolios.create',$this->data);
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
				
		$row = Lodfolios::find($id);
		$this->data['lobjLodfolios'] = $row;

		$params = array(
			'params'	=> " AND tbl_tickets.IdTicket = ".$id,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		$row = $this->model->getRows( $params );

		if(isset($row['rows'][0]))
		{
			$this->data['row'] 		=  $row['rows'][0];
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_tickets'); 
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		//$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;

		//Enviamos a actualizar todas las notificaciones como vistas
		$fetchMode = \DB::getFetchMode();
        \DB::setFetchMode(\PDO::FETCH_ASSOC);
		$lobjInsertTicket = \DB::table('tbl_tickets_thread')
		->select('tbl_tickets_thread.IdTicketThread',\DB::raw("'".\Session::get('uid')."' as entry_by"))
		->WhereNotExists(function($query)
            {
                $query->select(\DB::raw(1))
                      ->from('tbl_tickets_vistas')
                      ->whereRaw('tbl_tickets_vistas.IdTicketThread = tbl_tickets_thread.IdTicketThread')
                      ->whereRaw('tbl_tickets_vistas.entry_by = '.\Session::get('uid'));
            })
		->get();
	 	\DB::setFetchMode($fetchMode);
		\DB::table('tbl_tickets_vistas')->insert($lobjInsertTicket);

		$this->data['larrSubTipos'] = subtipostemaslo::Activos()->get();

		return view('lodfolios.form',$this->data);
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
			return view('lodfolios.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));		
		}		
	}	


	function postCopy( Request $request)
	{
		
	    foreach(\DB::select("SHOW COLUMNS FROM tbl_tickets ") as $column)
        {
			if( $column->Field != 'IdTicket')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));
			
					
			$sql = "INSERT INTO tbl_tickets (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_tickets WHERE IdTicket IN (".$toCopy.")";
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
								   ->leftJoin('tbl_tickets_subtipos', 'tbl_tickets_subtipos.id', '=', 'tbl_tickets.IdSubTipo')
		                           ->where("tbl_tickets_notificacion.IdTicket","=",$pintIdTicket)
		                           ->distinct()
		                           ->where("tbl_tickets_notificacion.IdEstatus","=","1")
		                           ->select("tb_users.email","tb_users.first_name", "tb_users.last_name", "tbl_tickets.Titulo", "tbl_contrato.cont_numero", "tbl_contrato.cont_nombre", "tbl_contratistas.RUT", "tbl_contratistas.RazonSocial", "vw_ticket_last_thread_join.Mensaje", "vw_ticket_last_thread_join.entry_by_name","tbl_tickets.IdPrioridad",\DB::raw("tbl_tickets_tipos.Descripcion as Tipo"), "tbl_tickets_subtipos.nombre as Categoria")
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
	function Email($email, $to, $subject, $data ){
		$e = "";
		$this->to = $to;
		$this->subject = $subject;
		if(CNF_MAIL =='swift')
		{ 
	        $errLevel = error_reporting(E_ALL ^ E_NOTICE);  //
			$lstrResultado = \Mail::send("user.emails.".$email, $data, function ($message) {
	            $message->to($this->to)->subject($this->subject);
	        });
	        error_reporting($errLevel);  //
		}  else {
			$message = view("user.emails.".$email, $data);
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: '.CNF_APPNAME.' <'.CNF_EMAIL.'>' . "\r\n";
			mail($to, $subject, $message, $headers);	
		}
	}

	function postSave( Request $request, $id =0)
	{
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    	$lintIdUser = \Session::get('uid');
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_tickets');
			
			$data['contrato_id'] = $request->input('contrato_id');
			
			if (!$request->input('IdTicket')){
			  $lintNuevo = 1;
			}else{
			  $lintNuevo = 0;
			}
			$id = $this->model->insertRow($data , $request->input('IdTicket'));
			if ($lintNuevo){ //Es un nuevo registro

				//
				$this->CreateMessage($request, $id);

				//Insertamos una notifiacion para los actores principales del lod
				$larrData = \DB::table("tbl_contrato")
				                 ->where("contrato_id","=",$data['contrato_id'])
				                 ->first();
				//Se incluye al administrador de contrato y al usuario contratista
				$larrDataInsert[] = array("entry_by"=> $larrData->entry_by_access, "IdTicket"=>$id);
				$larrDataInsert[] = array("entry_by"=> $larrData->admin_id, "IdTicket"=>$id);
				//Si el usuario que está creando el tema no es contratista, ni administrador de contrato, lo agrego a las notificaciones.
				if ($lintIdUser!=$larrData->entry_by_access && $lintIdUser!=$larrData->admin_id){ 
					$larrDataInsert[] = array("entry_by"=> $lintIdUser, "IdTicket"=>$id);
				}
				//Guardamos las subscripcion a las notificaciones para el nuevo tema
				$larrDataInsert = \DB::table("tbl_tickets_notificacion")
				                        ->insert($larrDataInsert);
				//Enviamos las notificaciones por email a las personas subscrita de que se creó un nuevo tema
				$this->EmailNotificacion($id,"lod_nuevotema");
				//Registro la acción de creación de actividad
				self::Registeractivity($id,1);
			}
			$this->detailviewsave( $this->modelview , $request->all() ,$this->info['config']['subform'] , $id) ;
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
			\DB::table('tbl_tickets_thread')->whereIn('IdTicket',$request->input('ids'))->delete();
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
		$model  = new Lodfolios();
		$info = $model::makeInfo('lodfolios');

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
				return view('lodfolios.public.view',$data);
			} 

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdTicket' ,
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
			return view('lodfolios.public.index',$data);			
		}


	}

	function postSavepublic( Request $request)
	{
		
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_tickets');		
			 $this->model->insertRow($data , $request->input('IdTicket'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}	
	
	}	
				

}