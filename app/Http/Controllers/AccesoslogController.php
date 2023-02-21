<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Accesoslog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect;
use Yajra\Datatables\Facades\Datatables;

class AccesoslogController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'accesoslog';
	static $per_page	= '10';
	public $consulta;

	public function __construct()
	{
		parent::__construct();
		$this->model = new Accesoslog();

		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'accesoslog',
			'pageUrl'			=>  url('accesoslog'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('accesoslog.index',$this->data);
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
        return view('accesoslog.table',$this->data);

	}


	    public function postShowlist( Request $request )
        {

            $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
            $lintIdUser = \Session::get('uid');
            $lobjFiltro = \MySourcing::getFiltroUsuario(1, 1);
            $fechaI = '';
            $fechaF = '';

            $lobjQueryp = \DB::table('tbl_accesos_log')
                ->select(\DB::raw("tbl_accesos_log.*","acce.IdAccesoLog"),
                    \DB::raw("case when  tbl_accesos_log.IdTipoAcceso = 1 THEN 'ENTRADA' ELSE 'SALIDA' END AS Acceso"),
                    \DB::raw("case when  IdTipoEntidad = 1 THEN 'PERSONA' ELSE 'ACTIVO' END AS Tipo"),
                    "tbl_activos.Descripcion AS Activo",
                    \DB::raw("concat(tbl_activos_detalle.Etiqueta, ': ', tbl_activos_data_detalle.Valor) AS Ident"),
                    "tbl_centro.Descripcion AS Centro", "tbl_area_de_trabajo.Descripcion AS area_trabajo",
                    "tbl_contrato.cont_numero", "tbl_contratistas.RUT", "tbl_contratistas.RazonSocial")
                ->join("tbl_area_de_trabajo", "tbl_accesos_log.IdAreaTrabajo", "=", "tbl_area_de_trabajo.IdAreaTrabajo")
                ->join("tbl_centro", "tbl_area_de_trabajo.IdCentro", "=", "tbl_centro.IdCentro")
                ->join("tbl_activos", "tbl_accesos_log.IdTipoSubEntidad", "=", "tbl_activos.IdActivo")
                ->join('tbl_activos_data', function ($join) {
                    $join->on('tbl_activos.IdActivo', '=', 'tbl_activos_data.IdActivo')
                        ->on("tbl_accesos_log.IdEntidad", "=", "tbl_activos_data.IdActivoData");
                })
                ->join("tbl_contrato", "tbl_activos_data.contrato_id", "=", "tbl_contrato.contrato_id")
                ->join("tbl_contratistas", "tbl_contrato.IdContratista", "=", "tbl_contratistas.IdContratista")
                ->join("tbl_activos_detalle", "tbl_activos.IdActivo", "=", "tbl_activos_detalle.IdActivo")
                ->join('tbl_activos_data_detalle', function ($join) {
                    $join->on('tbl_activos_detalle.IdActivoDetalle', '=', 'tbl_activos_data_detalle.IdActivoDetalle')
                        ->on("tbl_activos_data.IdActivoData", "=", "tbl_activos_data_detalle.IdActivoData");
                });
            $lobjQueryp->whereraw("IdTipoEntidad= 2");
            $lobjQueryp->whereraw("tbl_activos_detalle.Unico='SI'");
            $lobjQueryp->whereraw("(tbl_contrato.contrato_id IN (" . $lobjFiltro['contratos'] . ') )');

            $lobjQuerys = \DB::table('tbl_accesos_log')
                ->select(\DB::raw("tbl_accesos_log.*"),
                    \DB::raw("case when  tbl_accesos_log.IdTipoAcceso = 1 THEN 'ENTRADA' ELSE 'SALIDA' END AS Acceso"),
                    \DB::raw("case when  IdTipoEntidad = 1 THEN 'PERSONA' ELSE 'ACTIVO' END AS Tipo"),
                    \DB::raw("case when  IdTipoSubEntidad = 0 THEN '--' ELSE '--' END AS Activo"),
                    \DB::raw("concat(tbl_personas.Rut, ' ', tbl_personas.Nombres, ' ', tbl_personas.Apellidos) AS Ident"),
                    "tbl_centro.Descripcion AS Centro", "tbl_area_de_trabajo.Descripcion AS area_trabajo",
                    "tbl_contrato.cont_numero", "tbl_contratistas.RUT", "tbl_contratistas.RazonSocial")
                ->join("tbl_area_de_trabajo", "tbl_accesos_log.IdAreaTrabajo", "=", "tbl_area_de_trabajo.IdAreaTrabajo")
                ->join("tbl_centro", "tbl_area_de_trabajo.IdCentro", "=", "tbl_centro.IdCentro")
                ->join("tbl_personas", "tbl_accesos_log.IdEntidad", "=", "tbl_personas.IdPersona")
                ->leftJoin("tbl_contratos_personas", "tbl_personas.IdPersona", "=", "tbl_contratos_personas.IdPersona")
                ->leftJoin("tbl_contrato", "tbl_contratos_personas.contrato_id", "=", "tbl_contrato.contrato_id")
                ->leftJoin("tbl_contratistas", "tbl_contrato.IdContratista", "=", "tbl_contratistas.IdContratista");
            $lobjQuerys->whereraw("IdTipoEntidad= 1");
           $lobjQuerys->whereraw("(tbl_contrato.contrato_id IN (" . $lobjFiltro['contratos'] . ') )');

            $lobjQueryT = \DB::table('tbl_accesos_log')
                ->select(\DB::raw("tbl_accesos_log.*"),
                    \DB::raw("case when  tbl_accesos_log.IdTipoAcceso = 1 THEN 'ENTRADA' ELSE 'SALIDA' END AS Acceso"),
                    \DB::raw("case when  IdTipoEntidad = 1 THEN 'PERSONA' ELSE 'ACTIVO' END AS Tipo"),
                    \DB::raw("case when  IdTipoSubEntidad = 0 THEN '--' ELSE '--' END AS Activo"),
                    \DB::raw("concat(tbl_accesos_log.data_rut, ' - ', tbl_accesos_log.data_nombres, ' ', tbl_accesos_log.data_apellidos) AS Ident"),
                    "tbl_centro.Descripcion AS Centro", "tbl_area_de_trabajo.Descripcion AS area_trabajo", \DB::raw("'' as cont_numero"), \DB::raw("'' as RUT"), \DB::raw("'' as RazonSocial"))
                ->join("tbl_area_de_trabajo", "tbl_accesos_log.IdAreaTrabajo", "=", "tbl_area_de_trabajo.IdAreaTrabajo")
                ->join("tbl_centro", "tbl_area_de_trabajo.IdCentro", "=", "tbl_centro.IdCentro");
            $lobjQueryT->whereraw("IdTipoEntidad= 1");


            $lobjQuer = $lobjQueryp->union($lobjQuerys)->union($lobjQueryT);

            $lobjQuery = \DB::table(\DB::raw("({$lobjQuer->toSql()}) as acce"))->select(\DB::raw("*"));


            if ( (!is_null($request->input('fechaI'))) && (!is_null($request->input('fechaF'))) ){
                $fechaI = \MyFormats::FormatoFecha($request->input('fechaI')).' 00:00:00';
                $fechaF = \MyFormats::FormatoFecha($request->input('fechaF')).' 00:00:00';

                $lobjQuery->whereBetween("acce.createdOn",array($fechaI,$fechaF));

            }
            else if (!is_null($request->input('fechaI'))){
                $fechaI= \MyFormats::FormatoFecha($request->input('fechaI')).' 00:00:00';

                $lobjQuery->where("acce.createdOn",">=",$fechaI);
            }
            else if (!is_null($request->input('fechaF'))){
                $fechaF= \MyFormats::FormatoFecha($request->input('fechaF')).' 00:00:00';
                $lobjQuery->where("acce.createdOn","<=",$fechaF);
            }

            $lobjQuery->groupBy("acce.IdAccesoLog");


            $lobjDataTable = Datatables::queryBuilder($lobjQuery)
                ->editColumn('createdOn', function ($lobjDocumentos) {
                    return \MyFormats::FormatDateTime($lobjDocumentos->createdOn);
                })
                ->make(true);

            return $lobjDataTable;

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
			$this->data['row'] 		= $this->model->getColumnTable('tbl_accesos_log');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);

		$this->data['id'] = $id;

		return view('accesoslog.form',$this->data);
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
			return view('accesoslog.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_accesos_log ") as $column)
        {
			if( $column->Field != 'IdAccesoLog')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_accesos_log (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_accesos_log WHERE IdAccesoLog IN (".$toCopy.")";
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
			$data = $this->validatePost('tbl_accesos_log');

			$id = $this->model->insertRow($data , $request->input('IdAccesoLog'));

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
		$model  = new Accesoslog();
		$info = $model::makeInfo('accesoslog');

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
				return view('accesoslog.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdAccesoLog' ,
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
			return view('accesoslog.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_accesos_log');
			 $this->model->insertRow($data , $request->input('IdAccesoLog'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}

    function postDescarga(Request $request )
    {

        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');
        
        $fechaI = $request->fechaI;
        $fechaF = $request->fechaF;

        $lstrResultado = "  SELECT * 
                            FROM (SELECT tbl_accesos_log.*,
                                         CASE WHEN tbl_accesos_log.IdTipoAcceso = 1 THEN 'ENTRADA' ELSE 'SALIDA' END AS Acceso ,
                                         CASE WHEN IdTipoEntidad = 1 THEN 'PERSONA' ELSE 'ACTIVO' END AS Tipo,
                                         tbl_activos.Descripcion AS Activo,
                                         CONCAT(tbl_activos_detalle.Etiqueta, ': ', tbl_activos_data_detalle.Valor) AS Ident,
                                         tbl_centro.Descripcion AS Centro,
                                         tbl_area_de_trabajo.Descripcion AS area_trabajo,tbl_contrato.cont_numero,tbl_contratistas.`RUT`,tbl_contratistas.`RazonSocial`
                                  FROM tbl_accesos_log
                                  INNER JOIN tbl_area_de_trabajo ON tbl_accesos_log.IdAreaTrabajo = tbl_area_de_trabajo.IdAreaTrabajo
                                  INNER JOIN tbl_centro ON tbl_area_de_trabajo.IdCentro=tbl_centro.IdCentro
                                  INNER JOIN tbl_activos ON tbl_accesos_log.IdTipoSubEntidad=tbl_activos.IdActivo
                                  INNER JOIN tbl_activos_data ON tbl_activos.IdActivo=tbl_activos_data.IdActivo AND tbl_accesos_log.IdEntidad=tbl_activos_data.IdActivoData
                                  INNER JOIN tbl_contrato ON tbl_activos_data.contrato_id= tbl_contrato.contrato_id
                                  INNER JOIN tbl_contratistas ON tbl_contrato.IdContratista=tbl_contratistas.IdContratista
                                  INNER JOIN tbl_activos_detalle ON tbl_activos.IdActivo=tbl_activos_detalle.IdActivo
                                  INNER JOIN tbl_activos_data_detalle ON tbl_activos_detalle.IdActivoDetalle=tbl_activos_data_detalle.IdActivoDetalle AND tbl_activos_data.IdActivoData=tbl_activos_data_detalle.IdActivoData
                                  WHERE IdTipoEntidad = 2 
                                  AND tbl_activos_detalle.Unico='SI' ";
        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
        $lstrResultado .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';

        $lstrResultado .= "UNION
        SELECT  tbl_accesos_log.*,
        CASE WHEN tbl_accesos_log.IdTipoAcceso = 1 THEN 'ENTRADA' ELSE 'SALIDA' END AS Acceso ,
        CASE WHEN IdTipoEntidad = 1 THEN 'PERSONA' ELSE 'ACTIVO' END AS Tipo,
        CASE WHEN IdTipoSubEntidad = 0 THEN '--' ELSE '--' END AS Activo,
        CONCAT(tbl_personas.Rut, ' ', tbl_personas.Nombres, ' ', tbl_personas.Apellidos) AS Ident,
        tbl_centro.Descripcion AS Centro,
        tbl_area_de_trabajo.Descripcion AS area_trabajo,
        tbl_contrato.cont_numero,
        tbl_contratistas.RUT,
        tbl_contratistas.RazonSocial
        FROM tbl_accesos_log
        INNER JOIN tbl_area_de_trabajo ON tbl_accesos_log.IdAreaTrabajo = tbl_area_de_trabajo.IdAreaTrabajo
        INNER JOIN tbl_centro ON tbl_area_de_trabajo.IdCentro=tbl_centro.IdCentro
        INNER JOIN tbl_personas ON tbl_accesos_log.IdEntidad=tbl_personas.IdPersona
        LEFT JOIN tbl_contratos_personas ON tbl_personas.IdPersona= tbl_contratos_personas.IdPersona
        LEFT JOIN tbl_contrato ON tbl_contratos_personas.contrato_id= tbl_contrato.contrato_id
        LEFT JOIN tbl_contratistas ON tbl_contrato.IdContratista=tbl_contratistas.IdContratista
        WHERE IdTipoEntidad=1 ";

        $lstrResultado .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';

        $lstrResultado .= "UNION 
        SELECT  tbl_accesos_log.*,
        CASE WHEN tbl_accesos_log.IdTipoAcceso = 1 THEN 'ENTRADA' ELSE 'SALIDA' END AS Acceso ,
        CASE WHEN IdTipoEntidad = 1 THEN 'PERSONA' ELSE 'ACTIVO' END AS Tipo,
        CASE WHEN IdTipoSubEntidad = 0 THEN '--' ELSE '--' END AS Activo,
        CONCAT(tbl_accesos_log.data_rut, ' - ', tbl_accesos_log.data_nombres, ' ', tbl_accesos_log.data_apellidos) AS Ident,
        tbl_centro.Descripcion AS Centro,
        tbl_area_de_trabajo.Descripcion AS area_trabajo,
        '' as cont_numero,
        '' as RUT,
        '' as RazonSocial
        FROM tbl_accesos_log
        INNER JOIN tbl_area_de_trabajo ON tbl_accesos_log.IdAreaTrabajo = tbl_area_de_trabajo.IdAreaTrabajo
        INNER JOIN tbl_centro ON tbl_area_de_trabajo.IdCentro=tbl_centro.IdCentro
        WHERE IdTipoEntidad=1) acce ";

        if ( (strlen($fechaI)>0) && (strlen($fechaF)>0) ){
            $fechaI = \MyFormats::FormatoFecha($request->input('fechaI')).' 00:00:00';
            $fechaF = \MyFormats::FormatoFecha($request->input('fechaF')).' 00:00:00';

            $lstrResultado .="where acce.createdOn BETWEEN '" . $fechaI ."' and '" . $fechaF ."'";
        }
        else if (strlen($fechaI)>0){
            $fechaI= \MyFormats::FormatoFecha($request->input('fechaI')).' 00:00:00';
            $lstrResultado .="where acce.createdOn >=  '" . $fechaI ."'";
        }
        else if (strlen($fechaF)>0){
            $fechaF= \MyFormats::FormatoFecha($request->input('fechaF')).' 00:00:00';
            $lstrResultado .="where acce.createdOn <= '" . $fechaF ."'";
        }

        $lstrResultado .=" group by acce.IdAccesoLog";

        $lstrResultado .= "  LIMIT 10000";
        $lobjData = \DB::select($lstrResultado);

        include '../app/Library/PHPExcel/IOFactory.php';
        include '../app/Library/PHPExcel/Cell.php';
        require_once '../app/Library/PHPExcel.php';


        $objPHPExcel = new \PHPExcel();
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle('Registros');
        $objSheet->getStyle('A1:K1')->getFont()->setBold(true)->setSize(14);
        $char = "A";
        $objSheet->getCell($char++.'1')->setValue('Fecha y Hora');
        $objSheet->getCell($char++.'1')->setValue('Acceso');
        $objSheet->getCell($char++.'1')->setValue('Tipo');
        $objSheet->getCell($char++.'1')->setValue('Activo');
        $objSheet->getCell($char++.'1')->setValue('Ident');
        $objSheet->getCell($char++.'1')->setValue('Centro');
        $objSheet->getCell($char++.'1')->setValue('Area Trabajo');
        $objSheet->getCell($char++.'1')->setValue('Cont Numero');
        $objSheet->getCell($char++.'1')->setValue('RUT');
        $objSheet->getCell($char++.'1')->setValue('Razon Social');

        $objSheet->getStyle('A1:k1000')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        foreach ($lobjData as $i => $reg) {
            $char = "A";
            $objSheet->setCellValue($char++.($i+2),\MyFormats::FormatDateTime($lobjData[$i]->createdOn));
            $objSheet->setCellValue($char++.($i+2),$lobjData[$i]->Acceso);
            $objSheet->setCellValue($char++.($i+2),$lobjData[$i]->Tipo);
           $objSheet->setCellValue($char++.($i+2),$lobjData[$i]->Activo);
            $objSheet->setCellValue($char++.($i+2),$lobjData[$i]->Ident);
            $objSheet->setCellValue($char++.($i+2),$lobjData[$i]->Centro);
            $objSheet->setCellValue($char++.($i+2),$lobjData[$i]->area_trabajo);
            $objSheet->setCellValue($char++.($i+2),$lobjData[$i]->cont_numero);
            $objSheet->setCellValue($char++.($i+2),$lobjData[$i]->RUT);
            $objSheet->setCellValue($char++.($i+2),$lobjData[$i]->RazonSocial);

        }
        $char = "A";

        $objSheet->getColumnDimension($char++)->setAutoSize(true);
        $objSheet->getColumnDimension($char++)->setAutoSize(true);
        $objSheet->getColumnDimension($char++)->setAutoSize(true);
        $objSheet->getColumnDimension($char++)->setAutoSize(true);
        $objSheet->getColumnDimension($char++)->setAutoSize(true);
        $objSheet->getColumnDimension($char++)->setAutoSize(true);
        $objSheet->getColumnDimension($char++)->setAutoSize(true);
        $objSheet->getColumnDimension($char++)->setAutoSize(true);
        $objSheet->getColumnDimension($char++)->setAutoSize(true);
        $objSheet->getColumnDimension($char++)->setAutoSize(true);



        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $lstrDestinationPath = "uploads/documents/";
        $rand = rand(1000,100000000);
        $newfilename = "ReporteAccesos-".strtotime(date('Y-m-d H:i:s')).'-'.$rand.'.xlsx';
          $objWriter->save($lstrDestinationPath.$newfilename);
        return response()->json(array(
                'status'=>'success',
                'message'=> \Lang::get('core.note_success'),
                'result'=>$newfilename)
        );
    }


}
