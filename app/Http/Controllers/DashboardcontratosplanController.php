<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Dashboardcontratosplan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class DashboardcontratosplanController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'dashboardcontratosplan';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Dashboardcontratosplan();

		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'dashboardcontratosplan',
			'pageUrl'			=>  url('dashboardcontratosplan'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex($pintIdContrato = 0)
	{

		if (isset($_REQUEST['valor'])){
			$valor = $_REQUEST['valor'];
			if ($valor=="mes"){

	        	$ldatFechaUltimo = date('Y-m-01');
	        	 $fecha = " where tbl_contratos_items_p.mes = '$ldatFechaUltimo'";
	        }
	        elseif ($valor=="anual"){

	        	$ldatFechaUltimo = date('Y-01-01');
	        	 $fecha = " where tbl_contratos_items_p.mes >= '$ldatFechaUltimo'";
	        }
	        elseif ($valor=="acum"){

				$ldatFechaUltimo = \DB::table('tbl_contratos_items_r')
		                                ->where('contrato_id',$pintIdContrato)
	    	    	                    ->max("mes");

	    	  $fecha = " where tbl_contratos_items_p.mes <= '$ldatFechaUltimo'";
	        }
		}
else{
	if (empty($ldatFechaUltimo)) {
			$ldatFechaUltimo = \DB::table('tbl_contratos_items_r')
		                                ->where('contrato_id',$pintIdContrato)
	    	    	                    ->max("mes");

	    	  $fecha = " where tbl_contratos_items_p.mes <= '$ldatFechaUltimo'";
        }
}


		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
/*		var_dump($pintIdContrato);
		exit();*/
		$lintIdContrato = $pintIdContrato;

		$this->data['Meses'] = \DB::table("tbl_contrato")->select('cont_fechainicio','cont_fechaFin')->get();

		// se hace en otra consulta porque no se conoce el impacto que produce el traerselo en la consulta de arriba
		$this->data['comprueba'] = \DB::table("tbl_contratos_items")
		                       ->select('contrato_id')
		                        ->where("contrato_id","=",$pintIdContrato)
		                        ->get();

		$this->data['titulo'] = \DB::table("tbl_contrato")
		                        ->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista")
		                        ->where("tbl_contrato.contrato_id","=",$pintIdContrato)
		                        ->select('tbl_contrato.cont_numero','tbl_contratistas.rut', 'tbl_contratistas.RazonSocial')
		                        ->first();

		$this->data['ContratosPlan'] = \DB::table("tbl_contratos_plan")
		  									->select('tbl_contratos_plan.IdContratoPlan', 'tbl_contratos_plan.contrato_id', 'tbl_contratos_plan.Descripcion', 'tbl_contratos_plan.IdTipo', 'tbl_contratos_plan.ColorFondo', 'tbl_contratos_plan.ColorBorde', 'tbl_contratos_plan_detalle.Mes', \DB::raw('sum(tbl_contratos_plan_detalle.Cantidad*tbl_contratos_plan_detalle.Monto) as PxQ'))
		                                    ->leftJoin("tbl_contratos_plan_detalle", "tbl_contratos_plan.IdContratoPlan", "=", "tbl_contratos_plan_detalle.IdItemPlan")
		                                    ->groupBy("tbl_contratos_plan.IdContratoPlan", "tbl_contratos_plan.contrato_id", "tbl_contratos_plan.Descripcion", "tbl_contratos_plan.IdTipo", "tbl_contratos_plan.ColorFondo", "tbl_contratos_plan.ColorBorde", "tbl_contratos_plan_detalle.Mes")
		                                    ->where("tbl_contratos_plan.contrato_id", "=", $lintIdContrato)
		                                    ->get();

		$this->data['ContratosReal'] = \DB::table("tbl_contratos_items_r")
											->select('tbl_contratos_items_r.Mes', \DB::raw('sum(tbl_contratos_items_r.Cantidad*tbl_contratos_items_r.Monto) as PxQ, sum(tbl_contratos_items_r.Cantidad) as Q'))
											->groupBy("tbl_contratos_items_r.Mes")
											->where("tbl_contratos_items_r.contrato_id", "=", $lintIdContrato)
		                                    ->get();

		$lstrQuery = "select tbl_contratos_items.contrato_id,
       tbl_contratos_items.IdentificacionParent,
       ifnull(tbl_unidades.Descripcion,'Sin especificar') as Unidad,
       sum(tbl_contratos_items.CantidadPlan) as CantidadPlan,
       sum(tbl_contratos_items.TotalPlan) as TotalPlan,
       sum(tbl_contratos_items.CantidadReal) as CantidadReal,
       sum(tbl_contratos_items.TotalReal) as TotalReal
from (select a.contrato_id, case when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) = 0 and ifnull(b.Identificacion,0) != 0 then
					         concat(b.Identificacion,' ',b.descripcion)
					     when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then
					         concat(c.Identificacion,' ',c.descripcion)
					     when ifnull(d.Identificacion,0) != 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then
					         concat(d.Identificacion,' ',d.descripcion)
					   else
					       a.Identificacion
					   end as IdentificacionParent, case when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) = 0 and ifnull(b.Identificacion,0) != 0 then
					        concat(b.Identificacion,'.',a.Identificacion)
					      when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then
					          concat(c.Identificacion,'.',b.Identificacion,'.',a.Identificacion)
					            when ifnull(d.Identificacion,0) != 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then
					                concat(d.Identificacion,'.',c.Identificacion,'.',b.Identificacion,'.',a.Identificacion)
					          else
					        a.Identificacion
					          end as Identificacion,
					       a.IdContratoItem,
					       a.IdParent,
					       a.descripcion,
                           a.IdUnidad,
					       a.cantidad,
					       a.monto,
                           a.CantidadPlan,
                           a.TotalPlan,
                           a.CantidadReal,
                           a.TotalReal
from (
select c.*, p.CantidadPlan, p.TotalPlan, r.CantidadReal, r.TotalReal
from tbl_contratos_items c
left join (select a.*, b.cantidad as CantidadPlan, b.totalplan
		 from tbl_contratos_items a
		 left join (select 'plan' as tipo,
						   tbl_contratos_items_p.IdItem,
						   sum(tbl_contratos_items_p.cantidad) as cantidad,
						   sum(tbl_contratos_items_p.cantidad*tbl_contratos_items_p.monto) as TotalPlan
					from tbl_contratos_items_p group by IdItem) b on a.IdContratoItem = b.IdItem) as p on c.IdContratoItem = p.IdContratoItem
left join (select a.*, b.cantidad as CantidadReal, b.TotalReal
		 from tbl_contratos_items a
		 left join (select 'plan' as tipo,
						   tbl_contratos_items_r.IdItem,
						   sum(tbl_contratos_items_r.cantidad) as cantidad,
						   sum(tbl_contratos_items_r.cantidad*tbl_contratos_items_r.monto) as TotalReal
					from tbl_contratos_items_r group by IdItem) b on a.IdContratoItem = b.IdItem) as r on c.IdContratoItem = r.IdContratoItem
) a
left join tbl_contratos_items b on a.IdParent = b.IdContratoItem and a.contrato_id = b.contrato_id
left join tbl_contratos_items c on b.IdParent = c.IdContratoItem and b.contrato_id = c.contrato_id
left join tbl_contratos_items d on c.IdParent = d.IdContratoItem and c.contrato_id = d.contrato_id
left join tbl_unidades e on a.IdUnidad = e.IdUnidad) tbl_contratos_items left join tbl_unidades on tbl_contratos_items.IdUnidad = tbl_unidades.IdUnidad
WHERE tbl_contratos_items.IdContratoItem IS NOT NULL
AND NOT EXISTS (select 1 from tbl_contratos_items a where a.IdParent = tbl_contratos_items.IdContratoItem)
AND tbl_contratos_items.contrato_id = $lintIdContrato
GROUP BY tbl_contratos_items.contrato_id,
       tbl_contratos_items.IdentificacionParent,
       ifnull(tbl_unidades.Descripcion,'Sin especificar')";
		$this->data['ContratoDetalle'] = \DB::select($lstrQuery);

		$lstrQuery = "select tbl_contratos_items.contrato_id,
       tbl_contratos_items.IdentificacionParent,
       ifnull(tbl_unidades.Descripcion,'Sin especificar') as Unidad,
       sum(tbl_contratos_items.CantidadPlan) as CantidadPlan,
       sum(tbl_contratos_items.TotalPlan) as TotalPlan,
       sum(tbl_contratos_items.CantidadReal) as CantidadReal,
       sum(tbl_contratos_items.TotalReal) as TotalReal
from (select * from (select a.contrato_id, case when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) = 0 and ifnull(b.Identificacion,0) != 0 then
					         concat(b.Identificacion,' ',b.descripcion)
					     when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then
					         concat(c.Identificacion,' ',c.descripcion)
					     when ifnull(d.Identificacion,0) != 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then
					         concat(d.Identificacion,' ',d.descripcion)
					   else
					       a.Identificacion
					   end as IdentificacionParent, case when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) = 0 and ifnull(b.Identificacion,0) != 0 then
					        concat(b.Identificacion,'.',a.Identificacion)
					      when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then
					          concat(c.Identificacion,'.',b.Identificacion,'.',a.Identificacion)
					            when ifnull(d.Identificacion,0) != 0 and ifnull(c.Identificacion,0) != 0 and ifnull(b.Identificacion,0) != 0 then
					                concat(d.Identificacion,'.',c.Identificacion,'.',b.Identificacion,'.',a.Identificacion)
					          else
					        a.Identificacion
					          end as Identificacion,
					       a.IdContratoItem,
					       a.IdParent,
					       a.descripcion,
                           a.IdUnidad,
					       a.cantidad,
					       a.monto,
                           a.CantidadPlan,
                           a.TotalPlan,
                           a.CantidadReal,
                           a.TotalReal
from (
select c.*, p.CantidadPlan, p.TotalPlan, r.CantidadReal, r.TotalReal
from tbl_contratos_items c
left join (select a.*, b.cantidad as CantidadPlan, b.totalplan
		 from tbl_contratos_items a
		 left join (select 'plan' as tipo,
						   tbl_contratos_items_p.IdItem,
						   sum(tbl_contratos_items_p.cantidad) as cantidad,
						   sum(tbl_contratos_items_p.cantidad*tbl_contratos_items_p.monto) as TotalPlan
					from tbl_contratos_items_p ". $fecha.
					"group by IdItem) b on a.IdContratoItem = b.IdItem) as p on c.IdContratoItem = p.IdContratoItem
left join (select a.*, b.cantidad as CantidadReal, b.TotalReal
		 from tbl_contratos_items a
		 left join (select 'plan' as tipo,
						   tbl_contratos_items_r.IdItem,
						   sum(tbl_contratos_items_r.cantidad) as cantidad,
						   sum(tbl_contratos_items_r.cantidad*tbl_contratos_items_r.monto) as TotalReal
					from tbl_contratos_items_r group by IdItem) b on a.IdContratoItem = b.IdItem) as r on c.IdContratoItem = r.IdContratoItem
) a
left join tbl_contratos_items b on a.IdParent = b.IdContratoItem and a.contrato_id = b.contrato_id
left join tbl_contratos_items c on b.IdParent = c.IdContratoItem and b.contrato_id = c.contrato_id
left join tbl_contratos_items d on c.IdParent = d.IdContratoItem and c.contrato_id = d.contrato_id
left join tbl_unidades e on a.IdUnidad = e.IdUnidad) w
where NOT EXISTS (select 1 from tbl_contratos_items t where t.IdParent = w.IdContratoItem)) tbl_contratos_items left join tbl_unidades on tbl_contratos_items.IdUnidad = tbl_unidades.IdUnidad
WHERE tbl_contratos_items.IdContratoItem IS NOT NULL
AND NOT EXISTS (select 1 from tbl_contratos_items a where a.IdParent = tbl_contratos_items.IdContratoItem)
AND tbl_contratos_items.contrato_id = $lintIdContrato
GROUP BY tbl_contratos_items.contrato_id,
       tbl_contratos_items.IdentificacionParent,
       ifnull(tbl_unidades.Descripcion,'Sin especificar')";
       		$this->data['ContratoDetallePlan'] = \DB::select($lstrQuery);

		$lstrQuery = "select c.contrato_id, sum(p.TotalPlan) as TotalPlan, sum(r.TotalReal) as TotalReal, sum(pr.TotalPlanReal) as TotalPlanReal
					  from tbl_contratos_items c
				  	  left join (select a.*, b.cantidad as CantidadPlan, b.totalplan
							 from tbl_contratos_items a
							 left join (select 'plan' as tipo,
											   tbl_contratos_items_p.IdItem,
											   sum(tbl_contratos_items_p.cantidad) as cantidad,
											   sum(tbl_contratos_items_p.cantidad*tbl_contratos_items_p.monto) as TotalPlan
										from tbl_contratos_items_p
										group by IdItem) b on a.IdContratoItem = b.IdItem) as p on c.IdContratoItem = p.IdContratoItem
					  left join (select a.*, b.cantidad as CantidadReal, b.TotalReal
							 from tbl_contratos_items a
							 left join (select 'plan' as tipo,
											   tbl_contratos_items_r.IdItem,
											   sum(tbl_contratos_items_r.cantidad) as cantidad,
											   sum(tbl_contratos_items_r.cantidad*tbl_contratos_items_r.monto) as TotalReal
										from tbl_contratos_items_r
										group by IdItem) b on a.IdContratoItem = b.IdItem) as r on c.IdContratoItem = r.IdContratoItem
					  left join (select a.*, b.cantidad as CantidadPlanReal, b.TotalPlanReal
							 from tbl_contratos_items a
							 left join (select 'planreal' as tipo,
											   tbl_contratos_items_p.IdItem,
											   sum(tbl_contratos_items_p.cantidad) as cantidad,
											   sum(tbl_contratos_items_p.cantidad*tbl_contratos_items_p.monto) as TotalPlanReal
										from tbl_contratos_items_p ". $fecha.
										"group by IdItem) b on a.IdContratoItem = b.IdItem) as pr on c.IdContratoItem = pr.IdContratoItem
					  where c.contrato_id = ".$pintIdContrato."
					  group by c.contrato_id";
		$this->data['ContratoGlobal'] = \DB::select($lstrQuery);

		$this->data['access']		= $this->access;
		return view('dashboardcontratosplan.index',$this->data);
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
		$pagination->setPath('dashboardcontratosplan/data');

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
		return view('dashboardcontratosplan.table',$this->data);

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
			$this->data['row'] 		= $this->model->getColumnTable('answers');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);

		$this->data['id'] = $id;

		return view('dashboardcontratosplan.form',$this->data);
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
			return view('dashboardcontratosplan.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM answers ") as $column)
        {
			if( $column->Field != 'id')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO answers (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM answers WHERE id IN (".$toCopy.")";
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
			$data = $this->validatePost('answers');

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
		$model  = new Dashboardcontratosplan();
		$info = $model::makeInfo('dashboardcontratosplan');

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
				return view('dashboardcontratosplan.public.view',$data);
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
			return view('dashboardcontratosplan.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('answers');
			 $this->model->insertRow($data , $request->input('id'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}


}
