<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Cargadocumento;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 

class CargadocumentoController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'cargadocumento';
	static $per_page	= '10';
	
	public function __construct() 
	{
		parent::__construct();
		$this->model = new Cargadocumento();
		
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'cargadocumento',
			'pageUrl'			=>  url('cargadocumento'),
			'return' 			=> 	self::returnUrl()	
		);		
				
	} 
	public function getPrueba() {
		echo "hola";

		$lstrText = "Hola pa ver que es lo que hay que hacer aquí INDIVIDUALIZACIÓN DEL SOLICITANTE";

		$lstrTextProcess = strrpos($lstrText,"INDIVIDUALIZACIÓN DEL SOLICITANTE"); //Usamos como etiqueta clave el encabezado de la linea

		echo $lstrTextProcess;

	}
	public function postIndex(){

	  $destinationPath = "uploads/documents/";
      
      $file = Input::file("FileDataEmployee");
      $filename = $file->getClientOriginalName();
	  $extension =$file->getClientOriginalExtension();
      $rand = rand(1000,100000000);
	  $newfilename = strtotime(date('Y-m-d H:i:s')).'-'.$rand.'.'.$extension;
	  $uploadSuccess = $file->move($destinationPath, $newfilename);

      $lstrPeso = $_FILES['FileDataEmployee']['size'];
	  $lstrTipo = $_FILES['FileDataEmployee']['type'];

      $lstrNombreFull = $destinationPath.$newfilename;
      

      /*
      $lstrNombreFull = $lstrDirectorio."1.3 F30-1.pdf";
      $lstrPeso = 24;
	  $lstrTipo = "application/pdf";
	  */

      $lbloFile = addslashes(fread(fopen($lstrNombreFull, "rb"), filesize($lstrNombreFull)));

      $parser = new \Smalot\PdfParser\Parser();
	  $pdf    = $parser->parseFile($lstrNombreFull);

      $lbloArchivoTexto = $pdf->getText();
      $lbloArchivoTexto = str_replace("\n"," ",$lbloArchivoTexto); //remplaza los fines de lineas
      $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados
      $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados
      $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados
      $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados

	  $data = array("IdTipoDocumento" => '1',
				    "Archivo" => $lbloFile,
				    "ArchivoUrl" => $newfilename,
				    "ArchivoTexto" => $lbloArchivoTexto,
				    "ArchivoTipo" => $lstrTipo,
				    "ArchivoPeso" => $lstrPeso,
				    "createdOn" => '',
				    "entry_by" => \Session::get('uid'),
				    "IdEstatus" => 1);

	  $id = $this->model->insertRow($data , '');

      if ($id) {
	    $larrResult = $this->ProcessDocument($id,$data);
	    echo json_encode($larrResult);
	  }else{
        echo json_encode(array("code"=>"0","result"=>"Error insert into documents"));
	  }

	}

	private function ProcessDocument($pintIdDocumento, $pobjData) {
		//En esta función tomamos el archivo y lo procesamos dependiendo del tipo de documento que hayamos cargado

		$larrResult = array();

		if ($pobjData['IdTipoDocumento']==1){ //Si el tipo es 1 es un archivo F30-1 y es tratado de esa manera. 
			$larrResult = \MySourcing::parserFthirty($pobjData['ArchivoTexto']);
			$lintCode = isset($larrResult["code"])?$larrResult["code"]:0;
			if ($lintCode==1) {
				//Enviamos a guardar el Archivo
				$larrResult = \MySourcing::parserFthirtySave($larrResult['result'],$pobjData);
			}
		}

		$lintCode = isset($larrResult["code"])?$larrResult["code"]:0;
		$lstrMessage = isset($larrResult["message"])?$larrResult["message"]:0;
		$pobjData["IdEstatus"] = $lintCode;
		$pobjData["Resultado"] = $lstrMessage;
		$id = $this->model->insertRow($pobjData , $pintIdDocumento);
		return $larrResult;
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
		$pagination->setPath('cargadocumento/data');
		
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
		return view('cargadocumento.table',$this->data);

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
			$this->data['row'] 		= $this->model->getColumnTable('tbl_carga_documento'); 
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		
		$this->data['id'] = $id;

		return view('cargadocumento.form',$this->data);
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
			return view('cargadocumento.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));		
		}		
	}	


	function postCopy( Request $request)
	{
		
	    foreach(\DB::select("SHOW COLUMNS FROM tbl_carga_documento ") as $column)
        {
			if( $column->Field != 'IdCargaDocumento')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));
			
					
			$sql = "INSERT INTO tbl_carga_documento (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_carga_documento WHERE IdCargaDocumento IN (".$toCopy.")";
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
			$data = $this->validatePost('tbl_carga_documento');
			
			$id = $this->model->insertRow($data , $request->input('IdCargaDocumento'));
			
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
		$model  = new Cargadocumento();
		$info = $model::makeInfo('cargadocumento');

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
				return view('cargadocumento.public.view',$data);
			} 

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdCargaDocumento' ,
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
			return view('cargadocumento.public.index',$data);			
		}


	}

	function postSavepublic( Request $request)
	{
		
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_carga_documento');		
			 $this->model->insertRow($data , $request->input('IdCargaDocumento'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}	
	
	}	
				

}