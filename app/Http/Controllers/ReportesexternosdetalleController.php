<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Reportesexternosdetalle;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 

class ReportesexternosdetalleController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'reportesexternosdetalle';
	static $per_page	= '10';
	
	public function __construct() 
	{
		parent::__construct();
		$this->model = new Reportesexternosdetalle();
		
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'reportesexternosdetalle',
			'pageUrl'			=>  url('reportesexternosdetalle'),
			'return' 			=> 	self::returnUrl()	
		);		
				
	} 
	
	public function getIndex()
	{
		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
				
		$this->data['access']		= $this->access;	
		return view('reportesexternosdetalle.index',$this->data);
	}	

	public function postData( Request $request)
	{ 

		$this->data['setting']      = $this->info['setting'];
        $this->data['tableGrid']    = $this->info['config']['grid'];
        $this->data['access']       = $this->access;
		return view('reportesexternosdetalle.table',$this->data);

	}

	public function postContratos( Request $request) {

		$lintIdReporte = $request->input('idreporteexterno');
		$lstrResultado = "";
		$lobjFiltro = \MySourcing::getFiltroUsuario(2,true);
  		$lobjContrato = \DB::table("tbl_contrato")
  		->select(\DB::raw('tbl_contrato.contrato_id as value'), \DB::raw("concat(tbl_contrato.cont_numero, ' ', tbl_contrato.cont_nombre) as display"))
  		->join("tbl_contratistas","tbl_contratistas.IdContratista",'=','tbl_contrato.IdContratista')
  		->join("tbl_reportes_externos","tbl_reportes_externos.idsegmento",'=','tbl_contrato.segmento_id')
  		->where('tbl_reportes_externos.id','=',$lintIdReporte)
        ->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos'])  
  		->get();
  		$lstrResultado = '<option value="0"> Seleccione </option>';
  		foreach ($lobjContrato as $larrContrato) {
  			$lstrResultado .= '<option value="'.$larrContrato->value.'"> '.$larrContrato->display.' </option>';
  		}

  		return $lstrResultado;

	}
	public function getShowlist( Request $request )
	{

		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    	$lintIdUser = \Session::get('uid');

		// Get Query
	    $sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
	    $order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
	    // End Filter sort and order for query
	    // Filter Search for query
	    $filter = '';
	    if(!is_null($request->input('search')))
	    {
	      $search =   $this->buildSearch('maps');
	      $filter = $search['param'];
	      $this->data['search_map'] = $search['maps'];
	    }
    	
    	$lintIdUser = \Session::get('uid');

    	$lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
    	if ($lobjFiltro['contratos']){
	    	$filter .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';
	    }else{
	    	$filter .= " AND 1 = 2 ";
	    }

		$params = array(
		  'page'    => '',
		  'limit'   => '',
		  'sort'    => $sort ,
		  'order'   => $order,
		  'params'  => $filter,
		  'global'  => (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		// Get Query
		$results = $this->model->getRows( $params );

	    $larrResult = array();
	    $larrResultTemp = array();
	    $i = 0;

	    foreach ($results['rows'] as $row) {

	      $id = $row->id;

	      $larrResultTemp = array('id'=> ++$i,
	                    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$id.'" /> '
	                    );
	      foreach ($this->info['config']['grid'] as $field) {
	        if($field['view'] =='1') {
	          $limited = isset($field['limited']) ? $field['limited'] :'';
	          if (\SiteHelpers::filterColumn($limited )){
	            $value = \SiteHelpers::formatRows($row->{$field['field']}, $field , $row);
	            $larrResultTemp[$field['field']] = $value;
	          }
	        }
	      }
	      $onclick = "";
	      $lstrModule = "reportesexternosdetalle";
	      $lstrHtml = "";

	      if($this->access['is_detail'] ==1) {
	        if($this->info['setting']['view-method'] != 'expand'){
	          $onclick = " onclick=\"ajaxViewDetail('#".$lstrModule."',this.href); return false; \"" ;
	          if($this->info['setting']['view-method'] =='modal') {
	            $onclick = " onclick=\"SximoModal(this.href,'View Detail'); return false; \"" ;
	          }
	          $lstrHtml .= '<a href="'.\URL::to($lstrModule.'/show/'.$id).'" '.$onclick.' class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_view').'"><i class="fa fa-search"></i></a>';
	        }
	      }
	      if($this->access['is_edit'] ==1) {
	        $onclick = " onclick=\"ajaxViewDetail('#".$lstrModule."',this.href); return false; \"" ;
	        if($this->info['setting']['form-method'] =='modal'){
	          $onclick = " onclick=\"SximoModal(this.href,'Edit Form'); return false; \"" ;
	        }
	        $lstrHtml .= ' <a href="'.\URL::to($lstrModule.'/update/'.$id).'" '.$onclick.'  class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class="fa  fa-edit"></i></a>';
	      }
	      $larrResultTemp['action'] = $lstrHtml;
	      $larrResult[] = $larrResultTemp;
	    }

	    echo json_encode(array("data"=>$larrResult));
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
			$this->data['row'] 		= $this->model->getColumnTable('tbl_reportes_externos_detalle'); 
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		
		$this->data['selectReportes'] = \DB::table("tbl_reportes_externos")
  		->select(\DB::raw('tbl_reportes_externos.id as value'), \DB::raw('tbl_reportes_externos.nombre as display'), "tbl_reportes_externos.idestatus")
  		->orderBy("tbl_reportes_externos.nombre","ASC")
  		->get();

  		if ($id){
  			$this->data['selectContratos'] = \DB::table("tbl_contrato") 
	        ->select(\DB::raw('tbl_contrato.contrato_id as value'), \DB::raw("concat(tbl_contrato.cont_numero, ' ', tbl_contrato.cont_nombre) as display")) 
	        ->join("tbl_contratistas","tbl_contratistas.IdContratista",'=','tbl_contrato.IdContratista')
		    ->get();
  		}else{
	  		$this->data['selectContratos'] = array();
	    }

		$this->data['id'] = $id;

		return view('reportesexternosdetalle.form',$this->data);
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
			return view('reportesexternosdetalle.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));		
		}		
	}	


	function postCopy( Request $request)
	{
		
	    foreach(\DB::select("SHOW COLUMNS FROM tbl_reportes_externos_detalle ") as $column)
        {
			if( $column->Field != 'id')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));
			
					
			$sql = "INSERT INTO tbl_reportes_externos_detalle (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_reportes_externos_detalle WHERE id IN (".$toCopy.")";
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

	private function FormatoFecha($pstrFecha){
	    if ($pstrFecha){
	        $larrFecha = explode("/", $pstrFecha);
	        return $larrFecha[2].'-'.$larrFecha[1].'-'.$larrFecha[0];
	    }
	}

	function postSave( Request $request, $id =0)
	{
		
		if (isset($_POST['fecha'])){
        	$_POST['fecha'] = self::FormatoFecha($_POST['fecha']);
        }

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_reportes_externos_detalle');

			$lobjReporte = \DB::table('tbl_reportes_externos_detalle')
			->where('tbl_reportes_externos_detalle.fecha','=',$data['fecha'])
			->where('tbl_reportes_externos_detalle.contrato_id','=',$data['contrato_id'])
			->where('tbl_reportes_externos_detalle.idreporteexterno','=',$data['idreporteexterno'])
			->where('tbl_reportes_externos_detalle.id','!=',$request->input('id'))
			->get();

			if ($lobjReporte){
				
				$message = "Ya existe este tipo de reporte para el contrato en la misma fecha";
				return response()->json(array(
					'message'	=> $message,
					'status'	=> 'error'
				));	
			}else{
				$id = $this->model->insertRow($data , $request->input('id'));
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
		$model  = new Reportesexternosdetalle();
		$info = $model::makeInfo('reportesexternosdetalle');

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
				return view('reportesexternosdetalle.public.view',$data);
			} 

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'id' ,
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
			return view('reportesexternosdetalle.public.index',$data);			
		}


	}

	function postSavepublic( Request $request)
	{
		
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_reportes_externos_detalle');		
			 $this->model->insertRow($data , $request->input('id'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}	
	
	}	
				

}