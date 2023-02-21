<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Centros;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class CentrosController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'centros';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Centros();
		$this->modelview = new  \App\Models\Areasdetrabajo();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'centros',
			'pageUrl'			=>  url('centros'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('centros.index',$this->data);
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
		$pagination->setPath('centros/data');

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
		return view('centros.table',$this->data);

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
			$this->data['areat']=  \DB::table('tbl_area_de_trabajo')->where('IdCentro',$row['IdCentro'])->get();

		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_centro');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;

		//Recuperamos las etiquetas de los campos
		$this->data['tableGrid']    = $this->info['config']['grid'];
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

		return view('centros.form',$this->data);
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
			return view('centros.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_centro ") as $column)
        {
			if( $column->Field != 'IdCentro')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_centro (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_centro WHERE IdCentro IN (".$toCopy.")";
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

		$counter =  $request->counter;
		$Desc = $request->bulk_Descripcion;
		$Areas = $request->IdArea;
		$larrIdTipoAcceso = $request->bulk_IdTipoAcceso;
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_centro');

			$id = $this->model->insertRow($data , $request->input('IdCentro'));
			//var_dump($larrIdTipoAcceso);
			$docs =  \DB::table('tbl_area_de_trabajo')
			->select('IdAreaTrabajo')
			->where('IdCentro', '=', $request->IdCentro)
			->get();
			if (count($docs)>0 ){
		// Almaceno el resulrado de la consulta en un vector
				foreach ($docs as $value){
					$array[] = $value->IdAreaTrabajo;
				}

				if (!(empty($Areas))){
					foreach ($array as $valor) {
						if (!(in_array($valor, $Areas)))
							\DB::table('tbl_area_de_trabajo')->where('IdAreaTrabajo', '=', $valor)->delete();
					}
				}

			}

			for($i = 0; $i<count($counter); $i++){
				if (!(empty($Areas[$i]))){
					\DB::table('tbl_area_de_trabajo')->where('IdAreaTrabajo', $Areas[$i])->update(['Descripcion' => $Desc[$i],
						'IdTipoAcceso'=>$larrIdTipoAcceso[$i]]);
				}
				else{
				$IdAcceso = \DB::table('tbl_area_de_trabajo')->insertGetId(
				['IdCentro' => $id, 'Descripcion' => $Desc[$i],'IdTipoAcceso'=>$larrIdTipoAcceso[$i] ]);
				}


			}

			//$this->detailviewsave( $this->modelview , $request->all() ,$this->info['config']['subform'] , $id) ;
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
			\DB::table('tbl_area_de_trabajo')->whereIn('IdCentro',$request->input('ids'))->delete();
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
		$model  = new Centros();
		$info = $model::makeInfo('centros');

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
				return view('centros.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdCentro' ,
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
			return view('centros.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_centro');
			 $this->model->insertRow($data , $request->input('IdCentro'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}


}
