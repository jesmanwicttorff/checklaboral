<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Coreusers;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class CoreusersController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'coreusers';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Coreusers();

		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'coreusers',
			'pageUrl'			=>  url('coreusers'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		$IdUser = \Session::get('uid');
		$LevelUser = \MySourcing::LevelUser($IdUser);
		$ValidaGroupUser = \MySourcing::AssocGUser($IdUser);

		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		if ($LevelUser>1 && $ValidaGroupUser==0){

			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
		}

		$this->data['access']		= $this->access;
		return view('coreusers.index',$this->data);
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
		$pagination->setPath('coreusers/data');

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
		return view('coreusers.table',$this->data);

	}


	function getUpdate(Request $request, $id = null)
	{
		$lintIdUser = \Session::get('uid');
    	$lintLevelUser = \MySourcing::LevelUser($lintIdUser);
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


			$this->data['listadoContratD']=  \DB::table('tb_assocc')
				->join('tbl_contrato', 'tb_assocc.contrato_id', '=', 'tbl_contrato.contrato_id')
				->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
				->select('tbl_contrato.contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')
				->where('user_id',$row["id"])->get();

		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tb_users');

		}
        switch ($lintLevelUser) {
            case '1':
                $this->data['listadoContrat']=  \DB::table('tbl_contrato')
                    ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                    ->select('contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')->get();
                break;
            case '4':
                $this->data['listadoContrat']=  \DB::table('tbl_contrato')
                    ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                    ->select('contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')
                    ->where('admin_id',$lintIdUser)->get();
                break;
            case '6':
                $this->data['listadoContrat']=  \DB::table('tbl_contrato')
                    ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                    ->select('contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')
                    ->where('tbl_contrato.entry_by_access',$lintIdUser)->get();
                break;
            default:
                $this->data['listadoContrat']=  \DB::table('tb_assocc')
                    ->join('tbl_contrato', 'tb_assocc.contrato_id', '=', 'tbl_contrato.contrato_id')
                    ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                    ->select('tbl_contrato.contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')
                    ->where('tb_assocc.user_id',$lintIdUser)->get();
                break;
        }

		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);

		$this->data['id'] = $id;

		return view('coreusers.form',$this->data);
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
			return view('coreusers.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}

	public function getShowlist( Request $request )
	{
		// Get Query
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
	    $lintIdUser = \Session::get('uid');
	    $lintLevelUser = \MySourcing::LevelUser($lintIdUser);

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

			$id = $row->id;

			$larrResultTemp = array('id'=> ++$i,
								    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$row->id.'" /> '
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
			$larrResultTemp['action'] = \AjaxHelpers::buttonAction('coreusers',$this->access,$id ,$this->info['setting']).\AjaxHelpers::buttonActionInline($row->id,'id');
			$larrResult[] = $larrResultTemp;

		}

		echo json_encode(array("data"=>$larrResult));
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tb_users ") as $column)
        {
			if( $column->Field != 'id')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tb_users (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tb_users WHERE id IN (".$toCopy.")";
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

		$contratos =  $request->to;
		$group = $request->group_id;
		$subgroup = $request->subgroup_id;
		$user = $request->entry_by_access;
		$pass = $request->password;
		$cpass = $request->password_confirmation;
		$idN = $request->id;


		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tb_users');

			$data['entry_by'] = $user;
			$data['entry_by_access'] = $user;

			if (strlen($pass)>0)
				$data['password'] = \Hash::make($pass);

			if (strlen($idN)>0 && strlen($pass)==0 && strlen($cpass)==0){
				unset($data['password']);
				unset($data['password_confirmation']);
			}

			$id = $this->model->insertRow($data , $request->input('id'));

			$Idassoccgroup = \DB::table('tb_assoccgroup')->insertGetId(
							  	  ['group_id' => $group,
							  	   'subgroup_id' => $subgroup,
							  	   'entry_by' => $user]);

			$permisos =  \DB::table('tb_assocc')
			->select('contrato_id')
			->where('user_id', '=', $id)
			->get();

			if (count($permisos)>0 ){
		// Almaceno el resulrado de la consulta en un vector
				foreach ($permisos as $value){
					$array[] = $value->contrato_id;
				}

				if (!(empty($contratos))){
					foreach ($array as $valor) {
						if (!(in_array($valor, $contratos)))
							\DB::table('tb_assocc')->where('contrato_id', '=', $valor)->where('user_id', '=', $id)->delete();
					}
				}
				else{
                    \DB::table('tb_assocc')->where('user_id', '=', $id)->delete();
                }

			}
			if (!(empty($contratos))){
				foreach ($contratos as $valor) {
					if (count($permisos)>0 ){
						if (!(in_array($valor, $array))){
							$contratista =  \DB::table('tbl_contrato')
							->select('IdContratista')
							->where('contrato_id', '=', $valor)
							->get();

							\DB::table('tb_assocc')->insertGetId(
										  	  ['idAssoccGroup' => $Idassoccgroup,
										  	   'user_id' => $id,
										  	   'contrato_id' => $valor,
										  	   'contratista_id' => $contratista[0]->IdContratista,
										  	   'entry_by' => $user]);

						}
					}
					else{
						$contratista =  \DB::table('tbl_contrato')
							->select('IdContratista')
							->where('contrato_id', '=', $valor)
							->get();

							\DB::table('tb_assocc')->insertGetId(
										  	  ['idAssoccGroup' => $Idassoccgroup,
										  	   'user_id' => $id,
										  	   'contrato_id' => $valor,
										  	   'contratista_id' => $contratista[0]->IdContratista,
										  	   'entry_by' => $user]);
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
		$model  = new Coreusers();
		$info = $model::makeInfo('coreusers');

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
				return view('coreusers.public.view',$data);
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
			return view('coreusers.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tb_users');
			 $this->model->insertRow($data , $request->input('id'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}

	public function postInfocontrato(Request $request){
	   
		$IdUser = $request->user;
		$IdUserIn = $request->userIn;
		$lintLevelUser = \MySourcing::LevelUser($IdUser);
		$GroupUser = \MySourcing::AssocGUser($IdUser);

        if (strlen($IdUser)==0){
           $contratos =  \DB::table('tbl_contrato')
                ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                ->select('contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')->get();

            $grupos = \DB::table('tb_groups')->select('group_id','name')->get();

        }
        else{
            if (strlen($IdUserIn)>0){
                $contratosAsig = \DB::table('tb_assocc')
                    ->select('contrato_id')
                    ->where('user_id',$IdUserIn)->get();

                $larrContratosAsig = array();
                foreach ($contratosAsig as $value){
                    $larrContratosAsig[] = $value->contrato_id;
                }

                switch ($lintLevelUser) {
                    case '1':
                        $contratos =  \DB::table('tbl_contrato')
                            ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                            ->select('contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')
                            ->whereNotIn('tbl_contrato.contrato_id', $larrContratosAsig)
                            ->get();
                        break;
                    case '4':
                        $contratos =  \DB::table('tbl_contrato')
                            ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                            ->select('contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')
                            ->where('admin_id',$IdUser)
                            ->whereNotIn('tbl_contrato.contrato_id', $larrContratosAsig)
                            ->get();
                        break;
                    case '6':
                        $contratos =  \DB::table('tbl_contrato')
                            ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                            ->select('contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')
                            ->where('tbl_contrato.entry_by_access',$IdUser)
                            ->whereNotIn('tbl_contrato.contrato_id', $larrContratosAsig)
                            ->get();
                        break;
                    default:
                        $contratos =  \DB::table('tb_assocc')
                            ->join('tbl_contrato', 'tb_assocc.contrato_id', '=', 'tbl_contrato.contrato_id')
                            ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                            ->select('tbl_contrato.contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')
                            ->where('tb_assocc.user_id',$IdUser)
                            ->whereNotIn('tbl_contrato.contrato_id', $larrContratosAsig)
                            ->get();
                        break;
                }
            }
            else{
                switch ($lintLevelUser) {
                    case '1':
                        $contratos =  \DB::table('tbl_contrato')
                            ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                            ->select('contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')->get();
                        break;
                    case '4':
                        $contratos =  \DB::table('tbl_contrato')
                            ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                            ->select('contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')
                            ->where('admin_id',$IdUser)->get();
                        break;
                    case '6':
                        $contratos =  \DB::table('tbl_contrato')
                            ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                            ->select('contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')
                            ->where('tbl_contrato.entry_by_access',$IdUser)->get();
                        break;
                    default:
                        $contratos =  \DB::table('tb_assocc')
                            ->join('tbl_contrato', 'tb_assocc.contrato_id', '=', 'tbl_contrato.contrato_id')
                            ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
                            ->select('tbl_contrato.contrato_id','cont_numero', 'cont_nombre','RUT','cont_proveedor')
                            ->where('tb_assocc.user_id',$IdUser)->get();
                        break;
                }
            }


            $grupos = \DB::table('vw_users_group')
                ->select('group_id','name')
                ->where('group_id_filtro',$GroupUser)->get();

        }

		return response()->json(array(
			'status'=>'sucess',
			'valores'=>$contratos,
			'grupos'=>$grupos,
			'message'=>\Lang::get('core.note_sucess')
			));
	}

	public  function postCompruebacorreo(Request $request)
	{
		$email = $request->email;
		$IdUser = $request->user;

		if (strlen($IdUser)>0){
			$usuario = \DB::table('tb_users')
							->select('id','email')
							->where('email', '=', $email)
							->where('id','!=',$IdUser)
							->get();
		}
		else{
			$usuario = \DB::table('tb_users')
							->select('id','email')
							->where('email', '=', $email)
							->get();
		}


		return response()->json(array(
			'status'=>'sucess',
			'valores'=>$usuario,
			'message'=>\Lang::get('core.note_sucess')
			));

	}


}
