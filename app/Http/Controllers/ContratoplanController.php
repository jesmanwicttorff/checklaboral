<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Contratoplan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ; 

class ContratoplanController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'contratoplan';
	static $per_page	= '10';
	
	public function __construct() 
	{
		parent::__construct();
		$this->model = new Contratoplan();
		$this->modelview = new  \App\Models\Contratoplandetalle();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'contratoplan',
			'pageUrl'			=>  url('contratoplan'),
			'return' 			=> 	self::returnUrl()	
		);		
				
	} 
	
	public function getIndex()
	{
		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
				
		$this->data['access']		= $this->access;	
		return view('contratoplan.index',$this->data);
	}	

	public function postData( Request $request)
    {

        $this->data['setting']      = $this->info['setting'];
        $this->data['tableGrid']    = $this->info['config']['grid'];
        $this->data['access']       = $this->access;
        return view('contratoplan.table',$this->data);

    }

	function getShowlist(Request $request, $id = null) {
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
	    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');
	    if ($lintLevelUser==4){
	      //Aplicamos un filtro especial para solo los contratos relaciados a ese administrador
	      $filter .= " AND tbl_contrato.contrato_id IN (select contrato_id from tbl_contrato where tbl_contrato.admin_id = ".$lintIdUser.") ";
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

	      $id = $row->contrato_id;

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
	      $larrResultTemp['action'] = \AjaxHelpers::buttonAction('contratoplan',$this->access,$id ,$this->info['setting']).\AjaxHelpers::buttonActionInline($id,'contrato_id');
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
			$this->data['row'] 		= $this->model->getColumnTable('tbl_contrato'); 
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;

		return view('contratoplan.form',$this->data);
	}	

	function getUpload(Request $request, $id = null)
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
				
		$row = $this->modelview->find($id);
		if($row)
		{
			$this->data['row'] 		=  $row;
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_contratos_plan'); 
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['id'] = $id;

		return view('contratoplan.upload',$this->data);
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
			return view('contratoplan.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));		
		}		
	}	

	function postUploadsave( Request $request ){
		
		$file = Input::file('DocumentoURL');
	 	if(!empty($file)){
			$destinationPath = 'uploads/documents/';
			$filename = $file->getClientOriginalName();
			$extension =$file->getClientOriginalExtension(); //if you need extension of the file
			$rand = rand(1000,100000000);
			$newfilename = strtotime(date('Y-m-d H:i:s')).'-'.$rand.'.'.$extension;
			$uploadSuccess = $file->move($destinationPath, $newfilename);
		}

		$this->UploadsaveFile($newfilename, $request->input('IdItemPlan'), $request->input('contrato_id'),$request->input('IdTipo'));
		return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success')
			));	

	}

	function UploadsaveFile($lobjDocument, $pintIdItemPlan, $pintIdContrato, $pintIdTipo){
		
		include '../app/Library/PHPExcel/IOFactory.php';
        include '../app/Library/PHPExcel/Cell.php';
        require_once '../app/Library/PHPExcel.php';

        //Cargamos las variables
        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUserLogin = \Session::get('uid');
        $lstrDestinationPath = "uploads/documents/";
        $lintIdContrato = $pintIdContrato;
        $lintIdTipo = 2;
        $larrItems = array();

        //Abrimos el archivo
        $lstrFile = $lstrDestinationPath.$lobjDocument;
        /*
        $lstrFile = $lstrDestinationPath."carga_masiva.xlsx";
        */
        try {
            $objPHPExcel = \PHPExcel_IOFactory::load($lstrFile);
        }catch(Exception $e) {
            die('Error loading file "'.pathinfo($lstrFile,PATHINFO_BASENAME).'": '.$e->getMessage());
        }
        $larrDataFile = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        $lintCountDataFile = count($larrDataFile);

        $lintRowInit = 3;
        $larrFormato = array("ITEM"=>"A", 
               "DESCRIPCION"=>"B",
               "UNIDAD"=>"C",
               "RESULTADO"=>"D"
                );

        for($i=$lintRowInit;$i<=$lintCountDataFile;$i++){

            $lstrResultado = "";
            $lstrItem = trim($larrDataFile[$i][$larrFormato['ITEM']]);
            $lstrDescripcion = str_replace("⅛","",str_replace("¼","",str_replace("⅜","",str_replace("½","",str_replace("¾","",str_replace("Ø","",str_replace('"','\"',trim($larrDataFile[$i][$larrFormato['DESCRIPCION']]))))))));
            $lintCantidad = isset($larrFormato['CANTIDAD'])?trim($larrDataFile[$i][$larrFormato['CANTIDAD']]):0;
            $lintMonto = isset($larrFormato['MONTO'])?trim($larrDataFile[$i][$larrFormato['MONTO']]):0;

            $larrItem = explode(".",$lstrItem);
            $lintCount = count($larrItem)-1;
            if ($lintCount>=0) {
              for ($m=0; $m <= $lintCount; $m++) { 
                if($larrItem[$m]=="0"){
                unset($larrItem[$m]);
                $lintCount -= 1;
                }
              } 
            }else{
              $larrItem[0]=$lstrItem;
              $lintCount=0;
            }

            $larrEstadoPago = array();
            if ($lintIdTipo==2) {
                $lintEstadoPago = 1;
                foreach ($larrDataFile[1] as $key => $value) {
                  if ($value==$lintEstadoPago){
                    //echo " estado: ".$lintEstadoPago;
                    $keynext = \PHPExcel_Cell::stringFromColumnIndex(\PHPExcel_Cell::columnIndexFromString($key));
                    //echo " letra: ".$keynext;
                    $ldatFechaPlan = isset($larrDataFile[1][$keynext])?$larrDataFile[1][$keynext]:"";
                    //echo " fecha: ".$ldatFechaPlan." <br/>";
                    if ($ldatFechaPlan){
                        if(\PHPExcel_Shared_Date::isDateTime($objPHPExcel->getActiveSheet()->getCell($keynext."1"))) {
                          $lstrFormat = \MySourcing::ExcelFormatToPHP($objPHPExcel->getActiveSheet()->getStyle($keynext."1")->getNumberFormat()->getFormatCode());
                          $fecha = \DateTime::createFromFormat($lstrFormat, $ldatFechaPlan);
                          $ldatFechaPlan =  $fecha->format('Y-m-d');
                        }else{
                            return response()->json(array(
                              'status'=>'error',
                              'message'=> \Lang::get('core.note_success'),
                              'result'=>$newfilename)
                            );
                        }
                    }
                    $larrEstadoPago[$lintEstadoPago] = array("fecha"=>$ldatFechaPlan,"cantidad"=>$larrDataFile[$i][$key],"monto"=>isset($larrDataFile[$i][$keynext])?$larrDataFile[$i][$keynext]:0);
                    $lintEstadoPago += 1;
                  }
                }
            }
            
            //Definimos los niveles, solo permitimos 5 niveles
            if ($lintCount==0){
              $larrItems[$larrItem[0]] = array("title"=>$lstrDescripcion,"children"=>array(),"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
            }elseif ($lintCount==1){
              $larrItems[$larrItem[0]]["children"][$larrItem[1]] = array("title"=>$lstrDescripcion,"children"=>array(),"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
            }elseif ($lintCount==2){
              $larrItems[$larrItem[0]]["children"][$larrItem[1]]["children"][$larrItem[2]] = array("title"=>$lstrDescripcion,"children"=>array(),"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
            }elseif ($lintCount==3){
              $larrItems[$larrItem[0]]["children"][$larrItem[1]]["children"][$larrItem[2]]["children"][$larrItem[3]] = array("title"=>$lstrDescripcion,"children"=>array(),"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
            }elseif ($lintCount==4){
              $larrItems[$larrItem[0]]["children"][$larrItem[1]]["children"][$larrItem[2]]["children"][$larrItem[3]]["children"][$larrItem[4]] = array("title"=>$lstrDescripcion,"children"=>array(),"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
            }

            $lstrResultado = "01|Cargado satisfactoriamente";
        }
 
        //var_dump($this->larrItems);
        //exit();

        self::UploadsaveFileSave($pintIdItemPlan,$pintIdContrato,$larrItems);

		//aqui voy con el codigo

		$larrResult["code"] = "1";
		$larrResult["message"] = "Archivo procesado satisfactoriamente";
		$larrResult["result"] = "";

		return $larrResult;
	}

	function UploadsaveFileSave($pintIdItemPlan, $pintIdContrato, $parrItemizado){

      $lintIdAnterior = "";
      foreach ($parrItemizado as $lstrIdPosiciones => $larrPosiciones) {

        //guardo las posiciones
        $lobjPosiciones = \DB::table('tbl_contratos_items')
                             ->where('Descripcion', '=', $larrPosiciones['title'])
                             ->where('contrato_id','=',$pintIdContrato)
                             ->get();

        if (!$lobjPosiciones){
          $lintIdPosicion = \DB::table('tbl_contratos_items')->insertGetId(array("contrato_id"=>$pintIdContrato,
                                                                                 "Identificacion"=>$lstrIdPosiciones,
                                                                                 "Descripcion"=>$larrPosiciones['title'],
                                                                                 "cantidad"=>$larrPosiciones['cantidad'],
                                                                                 "monto"=>$larrPosiciones['monto']));
        }else{
          $lintIdPosicion = $lobjPosiciones[0]->IdContratoItem;
        }

        foreach ($larrPosiciones['children'] as $lintIdItem => $larrItems) {

            //guardo las posiciones
            $lobjItems = \DB::table('tbl_contratos_items')
                                 ->where('Descripcion', '=', $larrItems['title'])
                                 ->where('contrato_id','=',$pintIdContrato)
                                 ->where('IdParent','=',$lintIdPosicion)
                                 ->get();
            if (!$lobjItems){
              $lintIdItems = \DB::table('tbl_contratos_items')->insertGetId(array("contrato_id"=>$pintIdContrato,
                                                                                  "Identificacion"=>$lintIdItem,
                                                                                  "IdParent"=> $lintIdPosicion,
                                                                                  "Descripcion"=>$larrItems['title'],
                                                                                  "cantidad"=>$larrItems['cantidad'],
                                                                                  "monto"=>$larrItems['monto']));
            }else{
              $lintIdItems = $lobjItems[0]->IdContratoItem;
            }

            //luego de guardar las posiciones vamos a la tabla de plan para verificar si se debe guardar
            if ($lintIdItems){

                foreach ($larrItems['plan'] as $lstrPlan => $larrPlan) {
                  $lobjItemsPlan = \DB::table('tbl_contratos_plan_detalle')
                                     ->where('IdItem', '=', $lintIdItems)
                                     ->where('IdItemPlan','=', $pintIdItemPlan)
                                     ->where('Mes','=',$larrPlan['fecha'])
                                     ->get();
                  if (!$lobjItemsPlan){
                      $lintIdPlan = \DB::table('tbl_contratos_plan_detalle')->insertGetId(array("IdItem"=> $lintIdItems,
                                                                                      "Mes"=> $larrPlan['fecha'],
                                                                                      "IdItemPlan"=>$pintIdItemPlan,
                                                                                      "contrato_id"=>$pintIdContrato,
                                                                                      "Cantidad"=> str_replace(",",".",$larrPlan['cantidad']),
                                                                                      "Monto"=> $larrPlan['monto'],
                                                                                      "SubTotal"=> $larrPlan['cantidad']*$larrPlan['monto'] ));
                  }else{
                      $lintIdPlan = \DB::table('tbl_contratos_plan_detalle')->where("IdItemPlanDetalle","=",$lobjItemsPlan[0]->IdItemPlanDetalle)
                      														->update(array("Cantidad"=> str_replace(",",".",$larrPlan['cantidad']),
                                                                              		 		"Monto"=> $larrPlan['monto'],
                                                                               				"SubTotal"=> $larrPlan['cantidad']*$larrPlan['monto'] ));
                      $lintIdPlan = $lobjItemsPlan[0]->IdItemPlanDetalle;
                  }
                }
            }

            //origen
            foreach ($larrItems['children'] as $lintIdItem2 => $larrItems2) {

                //guardo las posiciones
                $lobjItems2 = \DB::table('tbl_contratos_items')
                                     ->where('Descripcion', '=', $larrItems2['title'])
                                     ->where('contrato_id','=',$pintIdContrato)
                                     ->where('IdParent','=',$lintIdItems)
                                     ->get();
                if (!$lobjItems2){
                  $lintIdItems2 = \DB::table('tbl_contratos_items')->insertGetId(array("contrato_id"=>$pintIdContrato,
                                                                                      "Identificacion"=>$lintIdItem2,
                                                                                      "IdParent"=> $lintIdItems,
                                                                                      "Descripcion"=>$larrItems2['title'],
                                                                                      "cantidad"=>$larrItems2['cantidad'],
                                                                                      "monto"=>$larrItems2['monto']));
                }else{
                  $lintIdItems2 = $lobjItems2[0]->IdContratoItem;
                }

                //luego de guardar las posiciones vamos a la tabla de plan para verificar si se debe guardar
                if ($lintIdItems2){

                    foreach ($larrItems2['plan'] as $lstrPlan => $larrPlan) {
                      $lobjItemsPlan = \DB::table('tbl_contratos_plan_detalle')
                                         ->where('IdItem', '=', $lintIdItems2)
                                         ->where('Mes','=',$larrPlan['fecha'])
                                         ->get();
                      if (!$lobjItemsPlan){
                          $lintIdPlan = \DB::table('tbl_contratos_plan_detalle')->insertGetId(array("IdItem"=> $lintIdItems2,
                                                                                          "Mes"=> $larrPlan['fecha'],
                                                                                          "IdItemPlan"=>$pintIdItemPlan,
                                                                                          "contrato_id"=>$pintIdContrato,
                                                                                          "Cantidad"=> str_replace(",",".",$larrPlan['cantidad']),
                                                                                          "Monto"=> $larrPlan['monto'],
                                                                                          "SubTotal"=> $larrPlan['cantidad']*$larrPlan['monto'] ));
                      }else{
                          $lintIdPlan = \DB::table('tbl_contratos_plan_detalle')->where("IdItemPlanDetalle","=",$lobjItemsPlan[0]->IdItemPlanDetalle)
                                                                                ->update(array("Cantidad"=> str_replace(",",".",$larrPlan['cantidad']),
                                                                                   			   "Monto"=> $larrPlan['monto'],
                                                                                               "SubTotal"=> $larrPlan['cantidad']*$larrPlan['monto'] ));
                          $lintIdPlan = $lobjItemsPlan[0]->IdItemPlanDetalle;
                      }
                    }
                }
            
            }
        }

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
			//$data = $this->validatePost('tbl_contrato');
			$id = $request->input('contrato_id');

			//$this->detailviewsave( $this->modelview , $request->all() ,$this->info['config']['subform'] , $id) ;
			$this->subformsave( $this->modelview , $request->all() ,$this->info['config']['subform'] , $id, 'IdContratoPlan') ;
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
			\DB::table('tbl_contratos_plan')->whereIn('contrato_id',$request->input('ids'))->delete();
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
		$model  = new Contratoplan();
		$info = $model::makeInfo('contratoplan');

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
				return view('contratoplan.public.view',$data);
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
			return view('contratoplan.public.index',$data);			
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