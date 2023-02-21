<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Contratistas;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class ContratistasController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'contratistas';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Contratistas();
		$this->modelview = new  \App\Models\Categoriadetalle();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> $this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'                    => 'contratistas',
			'pageUrl'			=>  url('contratistas'),
			'return' 			=> self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access'] = $this->access;
		return view('contratistas.index',$this->data);
	}

	public function getReportes ($id) {

		      $lobjContratistas = \DB::table('tbl_contratistas')
                      ->select('tbl_contratistas.rut', "tbl_contratistas.RazonSocial")
                      ->where('tbl_contratistas.IdContratista','=',$id)->first();

	      $this->data['rut'] = "";
	      $this->data['RazonSocial'] = "";

	      if ($lobjContratistas){
	        $this->data['rut'] = $lobjContratistas->rut;
	        $this->data['RazonSocial'] = $lobjContratistas->RazonSocial;
	      }

		$this->data['id'] = $id;
		$this->data['access']		= $this->access;
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		= \AjaxHelpers::fieldLang($this->info['config']['grid']);
		$this->data['subgrid']		= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());

		//asignamos las viriables
	    $this->data['reg'] = "";
	    $this->data['seg'] = "";
	    $this->data['area'] = "";
	    $this->data['ind'] = "";
	    $this->data['rep'] = "";
	    $this->data['year'] = "";
	    $this->data['mes'] = "";

	    $lobjMyReports = new \MyReports($this->data);
	    $larrFilters = $lobjMyReports::getFilters();
		$this->data	= array_merge($this->data,$larrFilters);

		return view('contratistas.reportes',$this->data);

	}

	public function getShowlist( Request $request ) {
		// Get Query
                $sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
		$order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
		// End Filter sort and order for query
		// Filter Search for query
		$filter = '';
		if(!is_null($request->input('search')))
		{
			$search = $this->buildSearch('maps');
			$filter = $search['param'];
			$this->data['search_map'] = $search['maps'];
		}
    $lintIdUser = \Session::get('uid');
    $lintLevelUser = \MySourcing::LevelUser($lintIdUser);

     $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
     $filter .= " AND tbl_contratistas.IdContratista IN (".$lobjFiltro['contratistas'].') ';

		$params = array(
			'page'		=> '',
			'limit'		=> '',
			'sort'		=> $sort ,
			'order'		=> $order,
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);

		$results = $this->model->getRows( $params );

		$larrResult = array();
		$larrResultTemp = array();
		$i = 0;

		foreach ($results['rows'] as $row) {

			$id = $row->IdContratista;

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
			$larrResultTemp['action'] = \AjaxHelpers::buttonAction('contratistas',$this->access,$id ,$this->info['setting']).\AjaxHelpers::buttonActionInline($id,'IdContratista');
			if(isset($this->access['is_report_view']) && $this->access['is_report_view']==1){
			$larrResultTemp['action'] .= '<a href="'.\URL::to('contratistas/reportes/'.$id).'" onclick="SximoModal(this.href,\'View Detail\'); return false;" class="btn btn-xs btn-white tips" title="Reporte"><i class="fa fa-bar-chart"></i></a>';
			}
			$larrResult[] = $larrResultTemp;
		}

		echo json_encode(array("data"=>$larrResult));

	}

    function getShowlistupload(){
      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = \Session::get('uid');

      $lstrDirectory = \MyLoadbatch::getDirectory();
      $lstrDirectoryResult = \MyLoadbatch::getDirectoryResult();

      $lobjLastUpload = \DB::table('tbl_carga_masiva_log')
                             ->join('tb_users','tb_users.id', '=', 'tbl_carga_masiva_log.entry_by')
                             ->select(\DB::raw("concat(tb_users.first_name , ' ', tb_users.last_name) as entry_by_name"),
                                      "tbl_carga_masiva_log.createdOn",
                                      "tbl_carga_masiva_log.Cargados",
                                      "tbl_carga_masiva_log.Modificados",
                                      "tbl_carga_masiva_log.Rechazados",
                                      \DB::raw("case when tbl_carga_masiva_log.ArchivoURL != '' then concat('<a href=\"".$lstrDirectory."',tbl_carga_masiva_log.ArchivoURL, '\"><i class=\"fa fa-download\"></i> descargar</a>') else ' ' end as ArchivoURL"),
                                      \DB::raw("case when tbl_carga_masiva_log.ArchivoResultadoURL != '' then concat('<a href=\"".$lstrDirectoryResult."',tbl_carga_masiva_log.ArchivoResultadoURL, '\"><i class=\"fa fa-download\"></i> descargar</a>') else ' ' end  as ArchivoResultadoURL"))
                             ->orderBy("tbl_carga_masiva_log.IdCargaMasiva","DESC")
                             ->where("tbl_carga_masiva_log.IdProceso","=","3");
      if ($lintLevelUser!=1){ //Solo el superadmin puede ver lo que ha cargado todos los usuarios
        $lobjLastUpload->where("tbl_carga_masiva_log.entry_by","=",$lintIdUser);
	    }
	    $lobjLastUpload = $lobjLastUpload->get();
	    echo json_encode(array("data"=>$lobjLastUpload));
	  }

	public function postData( Request $request)
    {
        $this->data['setting']      = $this->info['setting'];
        $this->data['tableGrid']    = $this->info['config']['grid'];
        $this->data['access']       = $this->access;
        return view('contratistas.table',$this->data);
    }

     public  function postCompruebanumerorut(Request $request)
  {
      $lintIdContratista = $request->input('idcontratista');
      $lstrRut = $request->input('rut');
      $larrResultado = array();
      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = \Session::get('uid');

       //limpiamos la base de datos
      //limpiamos la base de datos
      $lobjContratista = \DB::table('tbl_contratistas')
          ->where('tbl_contratistas.RUT', '=', $lstrRut)
          ->where('tbl_contratistas.IdContratista', '!=', $lintIdContratista)
          ->get();

      if ($lobjContratista){
         $larrResultado = array('status'=>'sucess',
                   'valores'=>'',
                   'message'=>\Lang::get('core.note_sucess'),
                   'code'=> '1'
                  );
       }else{
         $larrResultado = array('status'=>'sucess',
           'valores'=>'',
           'message'=>\Lang::get('core.note_sucess'),
           'code'=> '0'
          );
       }

       return response()->json($larrResultado);

  }

	function getUpdate(Request $request, $id = null)
	{


		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
		$lintIdUser = \Session::get('uid');

		if ($lintLevelUser == "15") { // Valida si el perfil es precontratista

			$lobjContratistas = \DB::table('tbl_contratistas')
			->where('tbl_contratistas.entry_by_access','=',$lintIdUser)
			->get(); //Consulta si el usuario tiene ya una empresa creada

			if ($lobjContratistas){ // Si tiene una empresa creada no le permitimos crear más
				return response()->json(array(
					'message'	=> 'No puede crear más de un registro contratista',
					'status'	=> 'error'
				));
			}

		}


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
		if($row){
                    $this->data['row'] 		=  $row;
                    $this->data['subContratista'] = \DB::table('tbl_subcontratistas')->Select('SubContratista')->where('IdContratista',$row['IdContratista'])->get();

                    $this->data['larrAccidentes'] = \DB::table('tbl_accidentes')
                        ->select("tbl_contrato.idcontratista", "tbl_accidentes.fecha_informe", "tbl_accidentes.n_accid", "tbl_accidentes.dias_perd", "tbl_accidentes.HH")
                        ->join("tbl_contrato", "tbl_accidentes.contrato_id", "=", "tbl_contrato.contrato_id")
                        ->where("tbl_contrato.idcontratista", "=", $id)
                        ->groupBy("tbl_contrato.idcontratista", "tbl_accidentes.fecha_informe", "tbl_accidentes.n_accid", "tbl_accidentes.dias_perd", "tbl_accidentes.HH")
                        ->orderBy("tbl_accidentes.fecha_informe", "desc")
                        ->get();

                    $this->data['larrFinanciero'] = \DB::table('tbl_eval_financiera')
                        ->select("tbl_eval_financiera.fecha", "tbl_eval_financiera.patrimonio", "tbl_eval_financiera.capital", "tbl_eval_financiera.ingreso", "tbl_eval_financiera.roa", "tbl_eval_financiera.apalancamiento", "tbl_eval_financiera.liquidez")
                        ->join("tbl_contrato", "tbl_eval_financiera.contrato_id", "=", "tbl_contrato.contrato_id")
                        ->where("tbl_contrato.idcontratista", "=", $id)
                        ->groupBy("tbl_eval_financiera.fecha", "tbl_eval_financiera.patrimonio", "tbl_eval_financiera.capital", "tbl_eval_financiera.ingreso", "tbl_eval_financiera.roa", "tbl_eval_financiera.apalancamiento", "tbl_eval_financiera.liquidez")
                        ->orderBy("tbl_eval_financiera.fecha", "desc")
                        ->get();
		} else {
                    $this->data['row'] 		= $this->model->getColumnTable('tbl_contratistas');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;
 		$this->data['tableGrid']    = $this->info['config']['grid'];

 		//Recuperamos las etiquetas de los campos
 		$this->data['Campos'] = array();
 		foreach ($this->data['tableGrid'] as $t) {
 			$this->data['Campos'][$t['field']] = \SiteHelpers::activeLang($t['field'],(isset($t['language'])? $t['language']: array()));
		}

		//Recuperamos los paises disponibles
		$this->data['selectPaises'] = \DB::table('tbl_paises')
		->where('CodigoPais','=',\app('session')->get('CNF_PAIS'))
		->first();

		if ($this->data['selectPaises']){
			$this->data['IdPais'] = $this->data['selectPaises']->IdPais;
		}

		$this->data['selectUsers'] = \DB::table('tb_users')
		->select('tb_users.id as value', \DB::raw("concat(tb_users.first_name, ' ', tb_users.last_name) as display"))
		->join('tb_groups','tb_groups.group_id','=','tb_users.group_id')
		->wherein('tb_groups.level',['6','15'])
		->whereNotExists(function ($query) use ($id) {
            $query->select(\DB::raw(1))
                  ->from('tbl_contratistas')
                  ->whereRaw('tbl_contratistas.entry_by_access = tb_users.id');
                  if ($id){
                  	$query->whereRaw('tbl_contratistas.IdContratista != '.$id);
                  }
        })
        ->orderby('display','asc')
		->get();

		//Revisamos cuales son los niveles posibles
		$this->data['existeRegion'] = \DB::table('dim_region')
		->select('tbl_paises.CodigoPais')->distinct()
		->join('tbl_paises', 'tbl_paises.IdPais', '=', 'dim_region.idPais')
		->where('tbl_paises.CodigoPais','=',\app('session')->get('CNF_PAIS'))
		->get();

		$this->data['existeProvincia'] = \DB::table('dim_provincia')
		->select('tbl_paises.CodigoPais')->distinct()
		->join('dim_region', 'dim_region.id', '=', 'dim_provincia.IdRegion')
		->join('tbl_paises', 'tbl_paises.IdPais', '=', 'dim_region.idPais')
		->where('tbl_paises.CodigoPais','=',\app('session')->get('CNF_PAIS'))
		->get();

		$this->data['existeComuna'] = \DB::table('dim_comuna')
		->select('tbl_paises.CodigoPais')->distinct()
		->join('dim_provincia', 'dim_provincia.id', '=', 'dim_comuna.IdProvincia')
		->join('dim_region', 'dim_region.id', '=', 'dim_provincia.IdRegion')
		->join('tbl_paises', 'tbl_paises.IdPais', '=', 'dim_region.idPais')
		->where('tbl_paises.CodigoPais','=',\app('session')->get('CNF_PAIS'))
		->get();

		$lstrVista = "form";
		if (CNF_TEMPLATE_CONTRATISTA != "form"){
			$lstrVista = CNF_TEMPLATE_CONTRATISTA;
		}

		return view('contratistas.'.$lstrVista,$this->data);
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
			return view('contratistas.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}

	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_contratistas ") as $column)
        {
			if( $column->Field != 'IdContratista')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_contratistas (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_contratistas WHERE IdContratista IN (".$toCopy.")";
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

        #$entry_by_aux = \Session::get('uid');

        #echo $entry_by_aux;
        #exit;

        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
		$lintIdUser = \Session::get('uid');

		if ($lintLevelUser == "15") { // Valida si el perfil es precontratista

			$lobjContratistas = \DB::table('tbl_contratistas')
			->where('tbl_contratistas.entry_by_access','=',$lintIdUser)
			->get(); //Consulta si el usuario tiene ya una empresa creada

			if ($lobjContratistas){ // Si tiene una empresa creada no le permitimos crear más
				return response()->json(array(
					'message'	=> 'No puede crear más de un registro contratista',
					'status'	=> 'error'
				));
			}

		}

	$subcontrarista = $request->IdSubContratista;
	$rules = $this->validateForm();
	$validator = Validator::make($request->all(), $rules);
	if ($validator->passes()) {
            $data = $this->validatePost('tbl_contratistas');

            if (empty($data['entry_by_access'])){
                $data['entry_by_access'] = \Session::get('uid');
            }

						if($request->has('mandante') and $request->input('mandante')==1){
							$data['is_mandante'] = 1;
						}else{
							$data['is_mandante'] = 0;
						}

            //validamos que el rut ya no exista
            if (isset($data['RUT'])){
                $lobjContrato = \DB::table('tbl_contratistas')
                  ->where('tbl_contratistas.RUT', '=', $data['RUT'])
                  ->where('tbl_contratistas.IdContratista', '!=', $request->input('IdContratista'))
                  ->get();
                if ($lobjContrato){
                    return response()->json(array(
                      'message'   => "El rut ya se encuentra asignado",
                      'status'    => 'error'
                   ));
                }
            }

            $id = $this->model->insertRow($data , $request->input('IdContratista'));

            $this->detailviewsave( $this->modelview , $request->all() ,$this->info['config']['subform'] , $id) ;
			  if (!(empty($subcontrarista))) {
			    \DB::table('tbl_subcontratistas')->where('IdContratista', '=', $id)->delete();
			    foreach ($subcontrarista as $sub) {
			    $Id = \DB::table('tbl_subcontratistas')->insertGetId(
			        ['IdContratista' => $id, 'SubContratista' => $sub]);
			    }
			  }else{
			    \DB::table('tbl_subcontratistas')->where('IdContratista', '=', $id)->delete();
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
		}
		// delete multipe rows
		if(count($request->input('ids')) >=1)
		{
			$this->model->destroy($request->input('ids'));
			\DB::table('tbl_contratista_servicio')->whereIn('IdContratista',$request->input('ids'))->delete();
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
		$model  = new Contratistas();
		$info = $model::makeInfo('contratistas');

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
				return view('contratistas.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdContratista' ,
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
			return view('contratistas.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_contratistas');
			 $this->model->insertRow($data , $request->input('IdContratista'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}

   public  function postCompruebarut(Request $request)
	{
		$rut = $request->rut;

		$personas = \DB::table('tbl_contratistas')
		->select('RUT')
		->where('RUT', '=', $rut)
		->get();
		return response()->json(array(
			'status'=>'sucess',
			'valores'=>$personas,
			'message'=>\Lang::get('core.note_sucess')
			));
	}

   public function postDatacontratista(Request $request){

		$id = $request->id;
		if (isset($id)){
		$datos = \DB::table('tbl_contratistas')->select('IdContratista','RUT','RazonSocial')->where('IdContratista', '!=', $id)->where('IdEstatus','=',1)->get();
		}
		else{
		$datos = \DB::table('tbl_contratistas')->select('IdContratista','RUT','RazonSocial')->where('IdEstatus','=',1)->get();
		}
		return response()->json(array(
			'status'=>'sucess',
			'valores'=>$datos,
			'message'=>\Lang::get('core.note_sucess')
			));
	}
	function postMasivo(Request $request, $id =0){

		//Proceso de carga masiva de personas
		$larrResult = \MyLoadbatch::LoadBach(3, Input::file("FileDataContractors"));
		return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success'),
				'result'=>$larrResult
				));

	}
}
