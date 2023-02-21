<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Visionxpartida;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class VisionxpartidaController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'visionxpartida';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Visionxpartida();
		$this->modelview = new  \App\Models\Visionxpartidadetalle();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'visionxpartida',
			'pageUrl'			=>  url('visionxpartida'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex(Request $request)
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		$this->data['detail']		= 0;
		if (empty($id)){
			$id = $request->input('id');
		}
		$this->data['id'] = $id;
		if (empty($fecha)){
			$fecha = $request->input('fecha');
		}
		$this->data['fecha'] = $fecha;
		return view('visionxpartida.index',$this->data);
	}

	public function postData( Request $request)
	{
		if (empty($id)){
			$id = $request->input('id');
		}else{
			$id = "";
		}
		if (empty($fecha)){
			$fecha = $request->input('fecha');
		}else{
			$fecha = "";
		}

		$row = $this->model->find($id);
		if($row)
		{
			$this->data['row'] 		=  $row;
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_contrato');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		//$this->info['config']['subform']['key'] = 'a.contrato_id';
		//var_dump($this->info['config']['subform']);
		//exit();

		//$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );

		$this->data['listdate'] = \DB::table('tbl_contratos_items_r')->where('contrato_id',$id)->groupBy('contrato_id','mes')->orderBy('mes','desc')
		                             ->select("mes")
		                             ->get();


		$this->data['id'] = $id;
		$larrData = array("Ene"=>"01",
						  "Feb"=>"02",
						  "Mar"=>"03",
						  "Abr"=>"04",
						  "May"=>"05",
						  "Jun"=>"06",
						  "Jul"=>"07",
						  "Ago"=>"08",
						  "Sep"=>"09",
						  "Oct"=>"10",
						  "Nov"=>"11",
						  "Dic"=>"12",
						 );
		if (!empty($fecha)){
			$arrFecha = explode("-",$fecha);
			if (count($arrFecha)){
				$lstrFecha = $arrFecha[1]."-".$larrData[$arrFecha[0]]."-05";
			}
		}else{
			$lstrFecha = "";
		}
		$this->data['fechafiltro'] = $lstrFecha;
		// Render into template
		return view('visionxpartida.detail',$this->data);

	}


	public function getShowlist( Request $request ) {

		$id = $request->input('id');
		if (!$id){
			$id = 0;
		}
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );

		$ldatFechaUltimo = $request->input('fecha');
		if (empty($ldatFechaUltimo)) {
			$ldatFechaUltimo = \DB::table('tbl_contratos_items_r')
		                                ->where('contrato_id',$id)
	    	    	                    ->max("mes");
        }
        if (empty($ldatFechaUltimo)){
        	$ldatFechaUltimo = '0000-00-00';
        }

    	$lstrQuery = "  select *
					    from (select case when ifnull(d.Identificacion,0) = 0 and ifnull(c.Identificacion,0) = 0 and ifnull(b.Identificacion,0) != 0 then
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
					       a.cantidad,
					       a.monto,
					       e.Descripcion as Unidad,
					       e.Abreviacion as UnidadAbreviacion,
					       (a.cantidad*a.monto) as total_contrato,
					       a.planacumulado_cantidad,
					       a.planacumulado,
					       a.realacumulado_cantidad,
					       a.realacumulado,
					       (a.realacumulado_cantidad*a.realacumulado) as total_acumulado_real,
					       (a.planacumulado_cantidad*a.planacumulado) as total_acumulado_plan,
					       (1 - ( (a.realacumulado_cantidad*a.realacumulado)/(a.planacumulado_cantidad*a.planacumulado) ) ) as desviacion_acumulada,
					       a.mesrealacumulado_cantidad,
					       a.mesrealacumulado,
					       a.contrato_id,
					       a.mesplanacumulado_cantidad,
					       a.mesplanacumulado,
					       (a.mesrealacumulado_cantidad*a.mesrealacumulado) as total_mes_real,
					       (a.mesplanacumulado_cantidad*a.mesplanacumulado) as total_mes_plan,
					       (1 - ( (a.mesrealacumulado_cantidad*a.mesrealacumulado)/(a.mesplanacumulado_cantidad*a.mesplanacumulado) ) ) as desviacion_mes
					from ( SELECT
					        `tbl_contratos_items`.`IdContratoItem` AS `IdContratoItem`,
					        `tbl_contratos_items`.`IdParent` AS `IdParent`,
					        `tbl_contratos_items`.`contrato_id` AS `contrato_id`,
					        `tbl_contratos_items`.`Identificacion` AS `Identificacion`,
					        `tbl_contratos_items`.`Descripcion` AS `descripcion`,
					        `tbl_contratos_items`.`Cantidad` AS `cantidad`,
					        `tbl_contratos_items`.`Monto` AS `monto`,
					        `tbl_contratos_items`.`IdUnidad` AS `IdUnidad`,
					        (`tbl_contratos_items`.`Cantidad` * `tbl_contratos_items`.`Monto`) AS `subtotal`,
					        $ldatFechaUltimo as mes,
					        `tbl_contratos_items_p`.`Cantidad` AS `planacumulado_cantidad`,
					        `tbl_contratos_items_p`.`Monto` AS `planacumulado`,
					        `tbl_contratos_items_r`.`Cantidad` AS `realacumulado_cantidad`,
					        `tbl_contratos_items_r`.`Monto` AS `realacumulado`,
					        rm.`Cantidad` AS `mesrealacumulado_cantidad`,
					        rm.`Monto` AS `mesrealacumulado`,
					        pm.`Cantidad` AS `mesplanacumulado_cantidad`,
					        pm.`Monto` AS `mesplanacumulado`
					    FROM
					        `tbl_contratos_items`
					        LEFT JOIN (select `tbl_contratos_items_p`.`IdItem`, SUM(`tbl_contratos_items_p`.`Cantidad`) as Cantidad, MAX(`tbl_contratos_items_p`.`Monto`) as monto from `tbl_contratos_items_p` WHERE `tbl_contratos_items_p`.`Mes` <= '$ldatFechaUltimo' GROUP BY `tbl_contratos_items_p`.`IdItem`) as tbl_contratos_items_p ON `tbl_contratos_items`.`IdContratoItem` = `tbl_contratos_items_p`.`IdItem`
					        LEFT JOIN (select `tbl_contratos_items_r`.`IdItem`, SUM(`tbl_contratos_items_r`.`Cantidad`) as Cantidad, MAX(`tbl_contratos_items_r`.`Monto`) as monto from `tbl_contratos_items_r` WHERE `tbl_contratos_items_r`.`Mes` <= '$ldatFechaUltimo' GROUP BY `tbl_contratos_items_r`.`IdItem`) as tbl_contratos_items_r ON `tbl_contratos_items`.`IdContratoItem` = `tbl_contratos_items_r`.`IdItem`
					        LEFT JOIN (select `tbl_contratos_items_p`.`IdItem`, SUM(`tbl_contratos_items_p`.`Cantidad`) as Cantidad, MAX(`tbl_contratos_items_p`.`Monto`) as monto from `tbl_contratos_items_p` WHERE DATE_FORMAT(`tbl_contratos_items_p`.`Mes`,'%m-%Y') = DATE_FORMAT('$ldatFechaUltimo','%m-%Y') GROUP BY `tbl_contratos_items_p`.`IdItem`) as pm on tbl_contratos_items.IdContratoItem = pm.IdItem
					        LEFT JOIN (select `tbl_contratos_items_r`.`IdItem`, SUM(`tbl_contratos_items_r`.`Cantidad`) as Cantidad, MAX(`tbl_contratos_items_r`.`Monto`) as monto from `tbl_contratos_items_r` WHERE DATE_FORMAT(`tbl_contratos_items_r`.`Mes`,'%m-%Y') = DATE_FORMAT('$ldatFechaUltimo','%m-%Y')  GROUP BY `tbl_contratos_items_r`.`IdItem`) as rm on tbl_contratos_items.IdContratoItem = rm.IdItem
					   ) a
					left join tbl_contratos_items b on a.IdParent = b.IdContratoItem and a.contrato_id = b.contrato_id
					left join tbl_contratos_items c on b.IdParent = c.IdContratoItem and b.contrato_id = c.contrato_id
					left join tbl_contratos_items d on c.IdParent = d.IdContratoItem and c.contrato_id = d.contrato_id
					left join tbl_unidades e on a.IdUnidad = e.IdUnidad
					) as tbl_contratos_items
					 WHERE tbl_contratos_items.IdContratoItem IS NOT NULL AND tbl_contratos_items.IdParent IS NOT NULL AND tbl_contratos_items.contrato_id = $id";
    	$results = \DB::select($lstrQuery);

	    $larrResult = array();
	    $larrResultTemp = array();
	    $i = 0;

	    foreach ($results as $rows) {
	    	if (($rows->cantidad>0) || ($rows->monto>0) || (strlen($rows->Unidad)>0)){


	      	$larrResultTemp = array("IdentificacionParent" => "<span>".$rows->IdentificacionParent."</span>",
								  "Identificacion" => "<span>".$rows->Identificacion."</span>",
						          "descripcion" => "<span>".$rows->descripcion."</span>",
						          "UnidadAbreviacion" => "<span>".$rows->UnidadAbreviacion."</span>",
						          "monto" => "<span>".\MySourcing::FormatCurrency($rows->monto)."</span>",
						          "cantidad" => "<span>".$rows->cantidad."</span>",
						          "total_contrato" => "<span>".\MySourcing::FormatCurrency($rows->total_contrato)."</span>",
						          "realacumulado_cantidad" => "<span>".$rows->realacumulado_cantidad."</span>",
						          "planacumulado_cantidad" => "<span>".$rows->planacumulado_cantidad."</span>",
					          	  "total_acumulado_real" => "<span>".\MySourcing::FormatCurrency($rows->total_acumulado_real)."</span>",
					          	  "total_acumulado_plan" => "<span>".\MySourcing::FormatCurrency($rows->total_acumulado_plan)."</span>",
					              "mesrealacumulado_cantidad" => "<span>".$rows->mesrealacumulado_cantidad."</span>",
					          	  "mesplanacumulado_cantidad" => "<span>".$rows->mesplanacumulado_cantidad."</span>",
					          	  "total_mes_real" => "<span>".\MySourcing::FormatCurrency($rows->total_mes_real)."</span>",
					          	  "total_mes_plan" => "<span>".\MySourcing::FormatCurrency($rows->total_mes_plan)."</span>");

				if ($rows->desviacion_acumulada==0){
				    $larrResultTemp['desviacion_acumulada'] = '<span class="text-success">'.round(100*$rows->desviacion_acumulada, 0).'%</span>';
				}elseif ($rows->desviacion_acumulada<0){
				    $larrResultTemp['desviacion_acumulada'] = '<span class="text-success"><i class="fa fa-level-up"></i>'.abs(round(100*$rows->desviacion_acumulada, 0)).'%</span>';
				}else {
				    $larrResultTemp['desviacion_acumulada'] = '<span class="text-warning"><i class="fa fa-level-down"></i>'.abs(round(100*$rows->desviacion_acumulada, 0)).'%</span>';
				}

				if ($rows->desviacion_mes==0){
				    $larrResultTemp['desviacion_mes'] = '<span class="text-success">'.round(100*$rows->desviacion_mes, 0).'%</span></td>';
				}elseif ($rows->desviacion_mes<0){
				    $larrResultTemp['desviacion_mes'] = '<span class="text-success"><i class="fa fa-level-up"></i>'.abs(round(100*$rows->desviacion_mes, 0)).'%</span>';
				}else{
				    $larrResultTemp['desviacion_mes'] = '<span class="text-warning"><i class="fa fa-level-down"></i>'.abs(round(100*$rows->desviacion_mes, 0)).'%</span>';
				}

		        $larrResult[] = $larrResultTemp;
		        }
		    }

	    echo json_encode(array("data"=>$larrResult));

	}

	public function postView( Request $request){

		if (empty($id)){
			$id = $request->input('id');
		}

		$row = $this->model->find($id);
		if($row)
		{
			$this->data['row'] 		=  $row;
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_contrato');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		//$this->info['config']['subform']['key'] = 'a.contrato_id';
		//var_dump($this->info['config']['subform']);
		//exit();

		//$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );

		$this->data['listdate'] = \DB::table('tbl_contratos_items_r')->where('contrato_id',$id)->groupBy('contrato_id','mes')->orderBy('mes','desc')
		                             ->select("mes")
		                             ->get();


		$this->data['id'] = $id;

		return view('visionxpartida.detail',$this->data);

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
			$this->data['row'] 		= $this->model->getColumnTable('tbl_contrato');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		//$this->info['config']['subform']['key'] = 'a.contrato_id';
		//var_dump($this->info['config']['subform']);
		//exit();

		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );

		$this->data['listdate'] = \DB::table('tbl_contratos_items_r')->where('contrato_id',$id)->groupBy('contrato_id','mes')->orderBy('mes','desc')
		                             ->select("mes")
		                             ->get();


		$this->data['id'] = $id;

		return view('visionxpartida.form',$this->data);
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
			return view('visionxpartida.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_contrato ") as $column)
        {
			if( $column->Field != 'contrato_id')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_contrato (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_contrato WHERE contrato_id IN (".$toCopy.")";
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
			$data = $this->validatePost('tbl_contrato');

			$id = $this->model->insertRow($data , $request->input('contrato_id'));
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
			\DB::table('tbl_contratos_items')->whereIn('contrato_id',$request->input('ids'))->delete();
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
		$model  = new Visionxpartida();
		$info = $model::makeInfo('items');

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
				return view('visionxpartida.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'contrato_id' ,
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
			return view('visionxpartida.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_contrato');
			 $this->model->insertRow($data , $request->input('contrato_id'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}


}
