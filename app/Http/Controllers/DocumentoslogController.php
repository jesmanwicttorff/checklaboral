<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Documentoslog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use Yajra\Datatables\Facades\Datatables;
use ZipArchive;

class DocumentoslogController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'documentoslog';
	static $per_page	= '10';
	
	public function __construct() 
	{
		parent::__construct();
		$this->model = new Documentoslog();
		
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'documentoslog',
			'pageUrl'			=>  url('documentoslog'),
			'return' 			=> 	self::returnUrl()	
		);		
				
	} 
	
	public function getIndex()
	{
		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
				
		$this->data['access']		= $this->access;	
		return view('documentoslog.index',$this->data);
	}	

	public function postData( Request $request)
	{
        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');
        $this->data['lintLevelUser']      = $lintLevelUser;
        $this->data['lintIdUser']      = $lintIdUser;
        $this->data['setting']      = $this->info['setting'];
        $this->data['tableGrid']    = $this->info['config']['grid'];
        $this->data['access']       = $this->access;
        $this->data['acciones']      = \DB::table('tbl_acciones')->select("IdAccion", "Nombre")->where('Descripcion','like','%documento%')
            ->where('Nombre','!=','Anulado')
            ->get();

        return view('documentoslog.table',$this->data);

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
			$this->data['row'] 		= $this->model->getColumnTable('tbl_documentos_log'); 
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		
		$this->data['id'] = $id;

		return view('documentoslog.form',$this->data);
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
			return view('documentoslog.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));		
		}		
	}

    public function postShowlist( Request $request )
    {

        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintGroupUser = \MySourcing::GroupUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');
        $larrDataFilter = $request->input("data");
        $lintIdAccion = isset($larrDataFilter[1]['filtro'])?$larrDataFilter[1]['filtro']:'';
        $lintIdUsuario = isset($larrDataFilter[2]['filtro'])?$larrDataFilter[2]['filtro']:'';
        $lintIdTipoDocumento = isset($larrDataFilter[3]['filtro'])?$larrDataFilter[3]['filtro']:'';
        $ldatIdFechaAccion = isset($larrDataFilter[4]['filtro'])?$larrDataFilter[4]['filtro']:'';


        $lobjQuery = \DB::table('tbl_documentos_log')
            ->select(\DB::raw("'' as Accion"), "tbl_documentos.IdDocumento",\DB::raw("tbl_entidades.Entidad"), \DB::raw("tbl_acciones.Nombre as Nombre"), \DB::raw("concat(tb_users.first_name , ' ', tb_users.last_name) as Usuario"),
                \DB::raw("tbl_tipos_documentos.Descripcion as TipoDocumento"), "vw_entidades_detalle.Detalle","tbl_documentos_log.createdOn", "tbl_documentos.Resultado", "tbl_documentos.DocumentoURL","tbl_documentos_log.observaciones")
            ->join("tbl_documentos","tbl_documentos.IdDocumento","=","tbl_documentos_log.IdDocumento")
            ->join("tbl_acciones","tbl_acciones.IdAccion","=","tbl_documentos_log.IdAccion")
            ->join("tb_users","tb_users.id","=","tbl_documentos_log.entry_by")
            ->join("tbl_tipos_documentos","tbl_tipos_documentos.IdTipoDocumento","=","tbl_documentos.IdTipoDocumento")
            ->join("tbl_entidades","tbl_entidades.IdEntidad","=","tbl_documentos.Entidad")
            ->leftjoin("tbl_contrato","tbl_documentos.contrato_id","=","tbl_contrato.contrato_id")
            ->join('vw_entidades_detalle', function ($join) {
                $join->on('vw_entidades_detalle.IdEntidad', '=', 'tbl_documentos.IdEntidad')
                    ->on("vw_entidades_detalle.Entidad","=","tbl_documentos.Entidad");
            });

        if ($lintIdAccion!=""){
            $lobjQuery->where("tbl_documentos_log.IdAccion","=",$lintIdAccion);
        }else if ($lintIdUsuario!=""){
            $lobjQuery->where("tbl_documentos_log.entry_by","=",$lintIdUsuario);
        }
        if ($lintIdTipoDocumento){
            $lobjQuery->where("tbl_documentos.IdTipoDocumento","=",$lintIdTipoDocumento);
        }

        if ($ldatIdFechaAccion){
            $larrAccionDate = explode("|",$ldatIdFechaAccion);
            if ($larrAccionDate[0] && $larrAccionDate[1]){
                $larrAccionDate[0] = \MyFormats::FormatoFecha($larrAccionDate[0]);
                $larrAccionDate[1] = \MyFormats::FormatoFecha($larrAccionDate[1]);
                $lobjQuery->whereBetween("tbl_documentos_log.createdOn",$larrAccionDate);
            }else{
                if ($larrAccionDate[0]){
                    $lobjQuery->where("tbl_documentos_log.createdOn",">=",\MyFormats::FormatoFecha($larrAccionDate[0]));
                }else if ($larrAccionDate[1]){
                    $lobjQuery->where("tbl_documentos_log.createdOn","<=",\MyFormats::FormatoFecha($larrAccionDate[1]));
                }
            }
        }

        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);

        $lobjQuery->OrderBy("tbl_documentos_log.createdOn","DESC");
        // Using Query Builder
       $lobjDataTable = Datatables::queryBuilder($lobjQuery)
            ->editColumn('createdOn', function ($lobjDocumentos) {
                return \MyFormats::FormatDateTime($lobjDocumentos->createdOn);
            })
            ->editColumn('Accion', function ($lobjDocumentos) {
                if ($lobjDocumentos->DocumentoURL){
                    if (strrpos($lobjDocumentos->DocumentoURL, "/")===false){
                        return "<a class=\"btn btn-white\" onclick=\"ViewPDF('".url("uploads/documents/".$lobjDocumentos->DocumentoURL)."', '".$lobjDocumentos->IdDocumento."', '".$lobjDocumentos->IdDocumento."'); return false;\" > <i class=\"fa fa-eye\"></i> </a>";
                    }else{
                        return "<a class=\"btn btn-white\" onclick=\"ViewPDF('".url($lobjDocumentos->DocumentoURL)."', '".$lobjDocumentos->IdDocumento."', '".$lobjDocumentos->IdDocumento."'); return false;\" >  <i class=\"fa fa-eye\"></i>  </a>";
                    }
                }else{
                    return '';
                }
            })
            ->editColumn('DocumentoURL', function ($lobjDocumentos) {

                if ($lobjDocumentos->DocumentoURL){
                    return "<input type=\"checkbox\" value=\"".$lobjDocumentos->IdDocumento."\" id=\"checkbox_documents\" name=\"checkbox_documents[]\" />";
                }else{
                    return "";
                }

            })
            ->make(true);
        return $lobjDataTable;

    }


    function postCopy( Request $request)
	{
		
	    foreach(\DB::select("SHOW COLUMNS FROM tbl_documentos_log ") as $column)
        {
			if( $column->Field != 'id')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));
			
					
			$sql = "INSERT INTO tbl_documentos_log (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_documentos_log WHERE id IN (".$toCopy.")";
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
			$data = $this->validatePost('tbl_documentos_log');
			
			$id = $this->model->insertRow($data , $request->input('id'));
			
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
		$model  = new Documentoslog();
		$info = $model::makeInfo('documentoslog');

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
				return view('documentoslog.public.view',$data);
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
			return view('documentoslog.public.index',$data);			
		}


	}

	function postSavepublic( Request $request)
	{
		
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_documentos_log');		
			 $this->model->insertRow($data , $request->input('id'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}	
	
	}	
				

}