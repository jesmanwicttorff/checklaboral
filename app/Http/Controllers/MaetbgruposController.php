<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Maetbgrupos;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 

class MaetbgruposController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'maetbgrupos';
	static $per_page	= '10';
	
	public function __construct() 
	{
		parent::__construct();
		$this->model = new Maetbgrupos();
		$this->modelview = new  \App\Models\Maetbsubgrupos();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'maetbgrupos',
			'pageUrl'			=>  url('maetbgrupos'),
			'return' 			=> 	self::returnUrl()	
		);		
				
	} 
	
	public function getIndex()
	{
		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
			
		$this->data['access']		= $this->access;	
		return view('maetbgrupos.index',$this->data);
	}	

	public function postData( Request $request)
	{ 

		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');

        $this->data['setting']      = $this->info['setting'];
        $this->data['tableGrid']    = $this->info['config']['grid'];
        $this->data['access']       = $this->access;

        return view('maetbgrupos.table',$this->data);

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
			$this->data['row'] 		= $this->model->getColumnTable('tb_groups'); 
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;

		$lobjGruposAsociados = \DB::table('tb_assoccgroup')
		->where('tb_assoccgroup.group_id','=',$id)
		->get();
		
		$this->data['lstrArrayExists'] = "";
		if ($lobjGruposAsociados){
			foreach ($lobjGruposAsociados as $rowsselect) {
				$this->data['lstrArrayExists'] .= '"'.$rowsselect->subgroup_id.'", ';
			}
		}
		
		$this->data['selectDashboard'] = \DB::table('tbl_dashboard')
		->select( \DB::raw("tbl_dashboard.id as value"), 
				  \DB::raw("tbl_dashboard.nombre as display"))
		->get();
		
		$this->data['selectGrupos'] = \DB::table('tb_groups')
		->select( \DB::raw("tb_groups.group_id as value"), 
			      \DB::raw("tb_groups.name as display"), 
			      \DB::raw("tb_assoccgroup.subgroup_id as grupoasignado")
			  )
		->leftjoin("tb_assoccgroup", function ($join) use ($id) {
        	$join->on('tb_assoccgroup.subgroup_id', '=', 'tb_groups.group_id');
        		if ($id){
        			$join = $join->on('tb_assoccgroup.group_id', '=', \DB::raw($id));
        		}else{
        			$join = $join->on('tb_assoccgroup.group_id', 'IS', \DB::raw('NULL'));
        		}
    		});
		if ($id){
			$this->data['selectGrupos'] = $this->data['selectGrupos']->where('tb_groups.group_id','!=',$id);
		}
		$this->data['selectGrupos'] = $this->data['selectGrupos']->orderby('tb_groups.group_id','asc')
		->get();

		$this->data['selectGruposNiveles'] = \DB::table('tb_groups_levels')
		->select(\DB::raw("tb_groups_levels.id as value"), \DB::raw("tb_groups_levels.name as display"))
		->get();
		
		return view('maetbgrupos.form',$this->data);
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
			return view('maetbgrupos.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));		
		}		
	}	

	public function getShowlist(Request $request){
		
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

			$id = $row->group_id;

			$larrResultTemp = array('id'=> ++$i,
								    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$row->group_id.'" /> '
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
			$larrResultTemp['action'] = \AjaxHelpers::buttonAction('maetbgrupos',$this->access,$id ,$this->info['setting']).\AjaxHelpers::buttonActionInline($row->group_id,'group_id');
			$larrResult[] = $larrResultTemp;
		
		}

		echo json_encode(array("data"=>$larrResult));	
	}	

	function postCopy( Request $request)
	{
		
	    foreach(\DB::select("SHOW COLUMNS FROM tb_groups ") as $column)
        {
			if( $column->Field != 'group_id')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));
			
					
			$sql = "INSERT INTO tb_groups (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tb_groups WHERE group_id IN (".$toCopy.")";
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
		
		$lintIdUser = \Session::get('uid');
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tb_groups');
			
			$id = $this->model->insertRow($data , $request->input('group_id'));

			//Agregamos las nuevas asociaciones
			$larrAssocGroups = $request->IdSubGrupoAdd;
            $larrAssocGroups = explode(",",$larrAssocGroups);
            $larrDataGroup = array();
            foreach ($larrAssocGroups as $larrAssocGroup) {
            	if ($larrAssocGroup) {
            		$larrDataGroup[] = array("group_id" => $id,
            								 "subgroup_id"=>$larrAssocGroup, 
            								 "entry_by" => $lintIdUser);
            	}
            }
            if ($larrDataGroup) {
            	\DB::table("tb_assoccgroup")->insert($larrDataGroup);
            }

            //Eliminamos las asociaciones
            $larrAssocGroupsDelete = $request->IdSubGrupoDelete;
            $larrAssocGroupsDelete = explode(",",$larrAssocGroupsDelete);
            foreach ($larrAssocGroupsDelete as $larrAssocGroup) {
            	if ($larrAssocGroup){
                	\DB::table("tb_assoccgroup")
                	->where("group_id", '=', $id)
                	->where("subgroup_id", "=", $larrAssocGroup)
                	->delete();
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
			\DB::table('tb_groups_sub')->whereIn('group_id',$request->input('ids'))->delete();
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
		$model  = new Maetbgrupos();
		$info = $model::makeInfo('maetbgrupos');

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
				return view('maetbgrupos.public.view',$data);
			} 

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'group_id' ,
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
			return view('maetbgrupos.public.index',$data);			
		}


	}

	function postSavepublic( Request $request)
	{
		
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tb_groups');		
			 $this->model->insertRow($data , $request->input('group_id'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}	
	
	}	
				

}