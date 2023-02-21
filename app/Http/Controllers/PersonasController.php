<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Library\Acreditacion;
use App\Library\MyDocuments;
use App\Models\Personas;
use App\Models\Roles;
use \MyPeoples;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Validator, Input, Redirect ;

use DateTime;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DB;
use PDF;

class PersonasController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'personas';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Personas();
		$this->modelview = new  \App\Models\Anotaciones();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'	=> 'personas',
			'pageUrl'	=>  url('personas'),
			'return' 	=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
		$this->data['access']		= $this->access;
		return view('personas.index',$this->data);
	}

	public function postCambiocontractual(Request $request, $id =0){

		$lintIdContrato = $request->input('contrato_id_change');
		$lintIdTipoContrato = $request->input('idtipocontrato_change');
		$ldatFechaVencimiento = $request->input('fechavencimiento_change');
		if ($ldatFechaVencimiento){
			$ldatFechaVencimiento = \MyFormats::FormatoFecha($ldatFechaVencimiento);
		}
		$lintIdRol = $request->input('idrol_change');
		$lintIdOtrosAnexos = $request->input('otrostipos_change');
		$lstrOtros = $request->input('otros_change');

		$ldatFechaInicioFaena = $request->input('fechainiciofaena_change');
		if ($ldatFechaInicioFaena){
			$ldatFechaInicioFaena = \MyFormats::FormatoFecha($ldatFechaInicioFaena);
		}

		$lobjPersona = new MyPeoples($id);
		$lstrResult = $lobjPersona::CambiosContractual($lintIdContrato, $lintIdTipoContrato, $ldatFechaVencimiento, $lintIdRol, $lstrOtros, $lintIdOtrosAnexos, $ldatFechaInicioFaena);

		return $lstrResult;


	}

	public function postAccionpersonas(Request $request){

		$lintIdAccion = $request->input('accion');
		$lintIdPersona = $request->input('persona');
		$lintIdContrato = $request->input('contrato');
		$lintIdContratoNuevo = $request->input('contratonuevo');
		$lintIdRol = $request->input('rol');
		$ldatFechaInicioFaena = $request->input('fechainiciofaena');
		$ldatFechaContrato = $request->input('fecha');
		$lintIdDocumento = $request->input('iddocumento');
		$lintIdAnotacion = $request->input('idanotacion');
		$ldatFechaEfectiva = $request->input('fechaefectiva');
		$lintIdTipoContrato = $request->input('IdTipoContrato');
        $ldatFechaEfectiva = self::FormatoFecha($ldatFechaEfectiva);

		if ($lintIdAccion==1){ //Asignación a un contrato
			if ($ldatFechaInicioFaena){
				$ldatFechaInicioFaena = \MyFormats::FormatoFecha($ldatFechaInicioFaena);
			}
			$lobjAcreditacion = new Acreditacion($lintIdPersona);
			$larrResultado = $lobjAcreditacion::AssignContract($lintIdContrato, $lintIdPersona, $lintIdRol,0,$ldatFechaInicioFaena);
		}else if ($lintIdAccion==2){ //Cambio de contrato

		}else if ($lintIdAccion==3){ //Desvinculación de un contrato
			$lintResultLeaveAccess = \MyPeoples::LeaveAccess($lintIdPersona);
			$larrResultado = \MyPeoples::LeaveContract($lintIdPersona, $lintIdContrato, $lintIdAnotacion, $ldatFechaEfectiva);
		}else if ($lintIdAccion==4){ //Desvinculación de una persona
			//$lintResultLeaveAccess = \MyPeoples::LeaveAccess($lintIdPersona);
			$larrResultado = \MyPeoples::EliminarTrabajador($lintIdPersona, $lintIdContrato);
		}else if ($lintIdAccion==5){ //Recontratar de una persona
			$larrResultado = \MyPeoples::RecontrataTrabajador($lintIdPersona,$lintIdContrato,$lintIdRol,$lintIdTipoContrato);
		}
		return json_encode($larrResultado);

	}
	public function postGetroles (Request $request) {

		$lintContratoId = $request->input('contrato_id');

		$lobjRoles =  Roles::join("tbl_roles_servicios","tbl_roles_servicios.idrol","=","tbl_roles.idrol")
		              ->join("tbl_contrato","tbl_contrato.idservicio","=","tbl_roles_servicios.idservicio")
		              ->where('tbl_contrato.contrato_id',$lintContratoId)
		              ->select("tbl_roles.IdRol as value",
		              	       "tbl_roles.Descripción as display"
		                       )
		              ->orderBy("display","asc")
		              ->get();

		if (count($lobjRoles)){
			return $lobjRoles;
		}else{
			$lobjRoles = Roles::select("tbl_roles.IdRol as value",
		              	       		   "tbl_roles.Descripción as display")
			             ->orderBy("display","asc")
			             ->get();
			return $lobjRoles;
		}

	}
	public function postData( Request $request)
    {
    	$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');

        $this->data['setting']      = $this->info['setting'];
        $this->data['tableGrid']    = $this->info['config']['grid'];
        $this->data['access']       = $this->access;
        $this->data['anotaciones']  =  \DB::table('tbl_concepto_anotacion')->get();

        return view('personas.table',$this->data);
    }

	public function getDescargarPersonasVigentes(){

		$hoy = date('Y-m-d H:i:s');

		$lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
		$lcontratos = explode(',',$lobjFiltro['contratos']);
		$si =" 'SI' ";
		$no =" 'NO' ";
		$etiqueta = "Fecha";

		// Busco el Id Tipo Documento Valor
		$IdtipoDocumento = \DB::table('tbl_tipo_documento_valor')
		->join('tbl_tipos_documentos','tbl_tipo_documento_valor.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
		->where('tbl_tipo_documento_valor.Etiqueta','like',$etiqueta."%")
		->where('tbl_tipo_documento_valor.TipoValor','like',$etiqueta."%")
		->where('tbl_tipos_documentos.IdProceso',21)
		->select('tbl_tipo_documento_valor.IdTipoDocumentoValor')
		->get();

	    $id = $IdtipoDocumento[0]->IdTipoDocumentoValor; // Obtengo el Id Tipo Documento Valor

	// Consulta con contrato cargado
	$datos = \DB::table('tbl_contratos_personas')
	->join('tbl_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
	->join('tbl_contrato','tbl_contratos_personas.contrato_id','=','tbl_contrato.contrato_id')
	->join('tbl_contratistas','tbl_contratos_personas.IdContratista','=','tbl_contratistas.IdContratista')
	->join('tbl_contratos_servicios','tbl_contrato.idservicio','=','tbl_contratos_servicios.id')
	->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
	->join('tbl_centro','tbl_contratos_centros.IdCentro','=','tbl_centro.IdCentro')
	->join('tbl_movimiento_personal','tbl_contratos_personas.contrato_id','=','tbl_movimiento_personal.contrato_id')
	->join('tbl_documentos','tbl_contratos_personas.IdDocumento','=','tbl_documentos.IdDocumento')
	->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
	->join('tbl_documento_valor','tbl_documentos.IdDocumento','=','tbl_documento_valor.IdDocumento')
	->join('tbl_roles','tbl_contratos_personas.IdRol','=','tbl_roles.IdRol')
	->whereRaw("tbl_personas.IdPersona = tbl_movimiento_personal.IdPersona")
	->where('tbl_contrato.ContratoPrueba',0)
	->where('tbl_tipos_documentos.IdProceso',21)
	->where('tbl_documento_valor.IdTipoDocumentoValor',$id)
	->whereIn('tbl_contrato.contrato_id',$lcontratos)
	->selectRaw('tbl_contratos_personas.IdDocumento as IdDocumento , tbl_contrato.cont_numero AS numeroContrato,tbl_contratistas.RUT AS rutEmpresa,
	tbl_contratistas.RazonSocial AS razonSocial,tbl_contratos_servicios.name AS servicio,
	tbl_centro.Descripcion AS centro,tbl_personas.RUT AS rut,
	tbl_personas.Nombres AS nombre,tbl_personas.Apellidos AS apellidos,
	tbl_roles.Descripción AS rol, tbl_movimiento_personal.FechaEfectiva AS fechaefec,
	tbl_documento_valor.Valor AS fechaContratacion,tbl_personas.FechaNacimiento AS fechaNac,
	case when tbl_personas.discapacidad = 1 then '.$si.' else '.$no.' end AS discapacitado,
	case when tbl_personas.pensionado = 1 then '.$si.' else '.$no.' end AS pensionado')
	->get();
	//$datos = $datos->whereIn()

	// Tomo todos los IdDocumentos y los guardo en un array
	$resulta_id = array();
	$j=1;
	foreach ($datos as $listId)
	{
		array_push($resulta_id,[$j => $listId->IdDocumento]);

		$j++;

	}


	/****Consulta sin contrato cargado */
	$datos_sincargarDoc = \DB::table('tbl_contratos_personas')
	->join('tbl_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
	->join('tbl_contrato','tbl_contratos_personas.contrato_id','=','tbl_contrato.contrato_id')
	->join('tbl_contratistas','tbl_contratos_personas.IdContratista','=','tbl_contratistas.IdContratista')
	->join('tbl_contratos_servicios','tbl_contrato.idservicio','=','tbl_contratos_servicios.id')
	->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
	->join('tbl_centro','tbl_contratos_centros.IdCentro','=','tbl_centro.IdCentro')
	->join('tbl_movimiento_personal','tbl_contratos_personas.contrato_id','=','tbl_movimiento_personal.contrato_id')
	->join('tbl_documentos','tbl_contratos_personas.IdDocumento','=','tbl_documentos.IdDocumento')
	->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
	->join('tbl_roles','tbl_contratos_personas.IdRol','=','tbl_roles.IdRol')
	->whereRaw("tbl_personas.IdPersona = tbl_movimiento_personal.IdPersona")
	->where('tbl_contrato.ContratoPrueba',0)
	->whereNotIn('tbl_documentos.IdDocumento',$resulta_id)
	->whereIn('tbl_contrato.contrato_id',$lcontratos)
	->selectRaw('tbl_contratos_personas.IdDocumento as IdDocumento , tbl_contrato.cont_numero AS numeroContrato,tbl_contratistas.RUT AS rutEmpresa,
	tbl_contratistas.RazonSocial AS razonSocial,tbl_contratos_servicios.name AS servicio,
	tbl_centro.Descripcion AS centro,tbl_personas.RUT AS rut,
	tbl_personas.Nombres AS nombre,tbl_personas.Apellidos AS apellidos,
	tbl_roles.Descripción AS rol, tbl_movimiento_personal.FechaEfectiva AS fechaefec,
	tbl_personas.FechaNacimiento AS fechaNac,
	case when tbl_personas.discapacidad = 1 then '.$si.' else '.$no.' end AS discapacitado,
	case when tbl_personas.pensionado = 1 then '.$si.' else '.$no.' end AS pensionado')
	->get();
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('Acreditados');
		$spreadsheet->setActiveSheetIndex(0);

		 $spreadsheet->getActiveSheet()->getStyle('A1:G3')->getFont()->setBold(true);
		 $sheet->setCellValue('A1', 'Informe Personas Vigentes Asignadas a un Contrato');
		 $sheet->setCellValue('A2', 'Fecha del reporte: '.$hoy);
		 $j=4;
		$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(17);
		$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(17);
		$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(30);
		$spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(30);
		$spreadsheet->getActiveSheet()->setAutoFilter('A4:N4');
		$sheet->setCellValue('A'.$j, 'Numero Contrato');
		$sheet->setCellValue('B'.$j, 'Rut Empresa');
		$sheet->setCellValue('C'.$j, 'Razon Social');
		$sheet->setCellValue('D'.$j, 'Servicio');
		$sheet->setCellValue('E'.$j, 'Centro');
		$sheet->setCellValue('F'.$j, 'Rut');
		$sheet->setCellValue('G'.$j, 'Nombres');
		$sheet->setCellValue('H'.$j, 'Apellidos');
		$sheet->setCellValue('I'.$j, 'Rol');
		$sheet->setCellValue('J'.$j, 'Fecha Contrataciòn');
		$sheet->setCellValue('K'.$j, 'Fecha Faena');
		$sheet->setCellValue('L'.$j, 'Fecha Nacimiento');
		$sheet->setCellValue('M'.$j, 'Discapacitado');
		$sheet->setCellValue('N'.$j, 'Pensionado');
		$j=5;

		foreach ($datos as $dato) {
			$sheet->setCellValue('A'.$j, strtoupper(strtolower($dato->numeroContrato)));
			$sheet->setCellValue('B'.$j, strtoupper(strtolower($dato->rutEmpresa)));
			$sheet->setCellValue('C'.$j, strtoupper(strtolower($dato->razonSocial)));
			$sheet->setCellValue('D'.$j, strtoupper(strtolower($dato->servicio)));
			$sheet->setCellValue('E'.$j, strtoupper(strtolower($dato->centro)));
			$sheet->setCellValue('F'.$j, strtoupper(strtolower($dato->rut)));
			$sheet->setCellValue('G'.$j, strtoupper(strtolower($dato->nombre)));
			$sheet->setCellValue('H'.$j, strtoupper(strtolower($dato->apellidos)));
			$sheet->setCellValue('I'.$j, strtoupper(strtolower($dato->rol)));
			$sheet->setCellValue('J'.$j, strtoupper(strtolower($dato->fechaContratacion)));
			$sheet->setCellValue('K'.$j, strtoupper(strtolower($dato->fechaefec)));
			$sheet->setCellValue('L'.$j, strtoupper(strtolower($dato->fechaNac)));
			$sheet->setCellValue('M'.$j, strtoupper(strtolower($dato->discapacitado)));
			$sheet->setCellValue('N'.$j, strtoupper(strtolower($dato->pensionado)));
			$j++;
		}
		foreach ($datos_sincargarDoc as $data) {
			$sheet->setCellValue('A'.$j, strtoupper(strtolower($data->numeroContrato)));
			$sheet->setCellValue('B'.$j, strtoupper(strtolower($data->rutEmpresa)));
			$sheet->setCellValue('C'.$j, strtoupper(strtolower($data->razonSocial)));
			$sheet->setCellValue('D'.$j, strtoupper(strtolower($data->servicio)));
			$sheet->setCellValue('E'.$j, strtoupper(strtolower($data->centro)));
			$sheet->setCellValue('F'.$j, strtoupper(strtolower($data->rut)));
			$sheet->setCellValue('G'.$j, strtoupper(strtolower($data->nombre)));
			$sheet->setCellValue('H'.$j, strtoupper(strtolower($data->apellidos)));
			$sheet->setCellValue('I'.$j, strtoupper(strtolower($data->rol)));
			$sheet->setCellValue('J'.$j, strtoupper(strtolower('SIN INFORMACIÒN')));
			$sheet->setCellValue('K'.$j, strtoupper(strtolower($data->fechaefec)));
			$sheet->setCellValue('L'.$j, strtoupper(strtolower($data->fechaNac)));
			$sheet->setCellValue('M'.$j, strtoupper(strtolower($data->discapacitado)));
			$sheet->setCellValue('N'.$j, strtoupper(strtolower($data->pensionado)));
			$j++;
		}




		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="InformePersonasVigentes.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;



	}

	function getShowlist(Request $request){

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
    	$lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
        if ($lintLevelUser==1 || $lintLevelUser==7) {
            $filter .= " ";
        }else if($lintLevelUser==20 || $lintLevelUser==21 || $lintLevelUser==22 || $lintLevelUser==23 || $lintLevelUser==24 || $lintLevelUser==25){
					$filter .= " AND ( tbl_personas.IdPersona IN (SELECT cp.IdPersona FROM tbl_contratos_personas as cp WHERE contrato_id in (".$lobjFiltro['contratos'].'))
	OR ( tbl_personas.entry_by = '.$lintIdUser.') ) ';
				}
        else{
            $filter .= " AND ( tbl_personas.IdPersona IN (SELECT cp.IdPersona FROM tbl_contratos_personas as cp WHERE contrato_id in (".$lobjFiltro['contratos'].'))
    OR ( tbl_personas.entry_by_access = '.$lintIdUser.') ) ';
        }

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

			$id = $row->IdPersona;

			$larrResultTemp = array('id'=> ++$i,
								    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$row->IdPersona.'" /> '
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
			if (isset($this->access['is_action_button']) && $this->access['is_action_button']==1) {
				$larrResultTemp['action'] = \AjaxHelpers::buttonAction('personas',$this->access,$id ,$this->info['setting'],3).\AjaxHelpers::buttonActionInline($row->IdPersona,'IdPersona');
			}else{
				$larrResultTemp['action'] = \AjaxHelpers::buttonAction('personas',$this->access,$id ,$this->info['setting']).\AjaxHelpers::buttonActionInline($row->IdPersona,'IdPersona');
			}
			$larrResult[] = $larrResultTemp;
		}

		echo json_encode(array("data"=>$larrResult));

    }

    function getAcciones($id = null){

    	if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['id'] = $id;
		$this->data['access'] = $this->access;
		$this->data['setting'] = $this->info['setting'];
		$this->data['fields'] = \AjaxHelpers::fieldLang($this->info['config']['grid']);
        $this->data['lobjRoles'] = \DB::table('tbl_roles')->get();
        $this->data['lobjRoles'] = array();
        $this->data['lobjOtrosAnexos'] =  DB::table('tbl_otros_anexos')->get();
		$lbolContratado = false;
		$lintIdPersona = $id;

		$lobjPersona = new MyPeoples($lintIdPersona);
		$this->data['lobjPersona'] = $lobjPersona::getDatos();

		$lobjPersonasContratos = $lobjPersona::getDatosContractuales();

		$lobjFiniquito = \DB::table('tbl_tipos_documentos')->where('IdProceso',4)->first();
		if($lobjFiniquito){
			$lobjDocumentoFiniquito = \DB::table('tbl_documentos')
							->where('IdTipoDocumento',$lobjFiniquito->IdTipoDocumento)
							->where('entidad',3)
							->where('IdEntidad',$lintIdPersona)
							->whereNotExists(function($q) use ($lintIdPersona) {
								$q->select(DB::raw(1))->from('tbl_contratos_personas')->where('IdPersona',$lintIdPersona);
							})
							->first();
			if($lobjDocumentoFiniquito){
				$this->data['finiquito'] = $lobjDocumentoFiniquito;
				$this->data['finiquitoContrato'] = \DB::table('tbl_contrato')->where('contrato_id',$lobjDocumentoFiniquito->contrato_id)->first();
				$this->data['lobjRolesFiniquitos'] = \DB::table('tbl_roles')->get();
			}else{
				$this->data['finiquito'] = null;
			}
		}else{
			$this->data['finiquito'] = null;
		}

        if ($lobjPersonasContratos['code']==1) {

        	$this->data['lobjPersonaContrato'] = $lobjPersonasContratos['result'];

	        $this->data['lobjContrato'] = $lobjPersona::getContratosDisponibles();

	        $this->data['lobjAnotaciones'] = \DB::table('tbl_concepto_anotacion')->where('IdEstatus', '=', 1)->get();
        $this->data['lrowTipoContratos'] = \DB::table('tbl_tipos_contratos_personas')
            ->select('tbl_tipos_contratos_personas.id as value', 'tbl_tipos_contratos_personas.nombre as display', 'tbl_tipos_contratos_personas.IdEstatus', 'tbl_tipos_contratos_personas.Vencimiento')
            ->get();

	        return view('personas.acciones',$this->data);

	    }else{
	        return response()->json(array(
				'status'=>'error',
				'message'=> $lobjPersonasContratos['message']
			));
	    }

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
    	                       ->where("tbl_carga_masiva_log.IdProceso","=","1");
    	if ($lintLevelUser!=1){ //Solo el superadmin puede ver lo que ha cargado todos los usuarios
    		$lobjLastUpload->where("tbl_carga_masiva_log.entry_by","=",$lintIdUser);
		}
		$lobjLastUpload = $lobjLastUpload->get();
		echo json_encode(array("data"=>$lobjLastUpload));
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

		$this->data['selectTipoIdentificacion'] = \DB::table('tbl_tipos_identificacion')
		->select(\DB::raw('tbl_tipos_identificacion.IdTipoIdentificacion as value'), \DB::raw('tbl_tipos_identificacion.Descripcion as display'), "tbl_tipos_identificacion.valorxdefecto")
		->orderby("tbl_tipos_identificacion.ValorxDefecto", "desc")
		->orderby("tbl_tipos_identificacion.Descripcion", "asc")
		->get();

		$row = $this->model->find($id);
		if($row)
		{
			$this->data['row'] 		=  $row;
			$this->data['anotacionesP']=  \DB::table('tbl_anotaciones')
			->join('tb_users', 'tbl_anotaciones.entry_by', '=', 'tb_users.id')
			->join('tbl_concepto_anotacion', 'tbl_anotaciones.IdConceptoAnotacion','=','tbl_concepto_anotacion.IdConceptoAnotacion')
			->select('tbl_anotaciones.IdConceptoAnotacion', 'tbl_anotaciones.entry_by','tbl_anotaciones.entry_by_access','tbl_anotaciones.createdOn','tbl_anotaciones.IdPersona', 'tb_users.first_name', 'tb_users.last_name', 'tb_users.id', 'tbl_concepto_anotacion.Descripcion')
			->where('IdPersona',$row['IdPersona'])->get();

		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_personas');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['tableGrid']    = $this->info['config']['grid'];
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
        $this->data['lobjAnotaciones'] = \DB::table('tbl_concepto_anotacion')->where('IdEstatus', '=', 1)
        ->get();
		$this->data['id'] = $id;

		//Recuperamos las etiquetas de los campos
 		$this->data['Campos'] = array();
 		foreach ($this->data['tableGrid'] as $t) {
 			$this->data['Campos'][$t['field']] = \SiteHelpers::activeLang($t['field'],(isset($t['language'])? $t['language']: array()));
		}


		$paises = \DB::table('tbl_paises')->where('prefijo','<>','null')->get();
		$regiones = \DB::table('dim_region')->where('codigoArea','<>','null')->get();

		$this->data['paises'] = $paises;
		$this->data['regiones'] = $regiones;

		return view('personas.form',$this->data);
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
			return view('personas.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_personas ") as $column)
        {
			if( $column->Field != 'IdPersona')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_personas (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_personas WHERE IdPersona IN (".$toCopy.")";
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
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		$pintIdTipoIdentificacion =  $request->IdTipoIdentificacion;

		if (isset($_POST['FechaNacimiento'])) {
            $_POST['FechaNacimiento'] = self::FormatoFecha($_POST['FechaNacimiento']);
        }

		if ($validator->passes()) {
			$data = $this->validatePost('tbl_personas');

			//Validamos en que momento debemos actualizar o no el entry_by_access
			if ($lintLevelUser==6 || $lintLevelUser==4){
				if (empty($data['entry_by_access'])) {
					$data['entry_by_access'] = $lintIdUser;
				}
			}

			if (!isset($data['Sexo'])) {
				$data['Sexo'] = 0;
			}
			if (!isset($data['EstadoCivil'])) {
				$data['EstadoCivil'] = 0;
			}

			if (isset($data['RUT']) && $pintIdTipoIdentificacion == 1) {
				$data['RUT'] = \MySourcing::FormatRut($data['RUT']);
			}

			if(!isset($data['paisTelefono_id'])){
				$data['paisTelefono_id'] = $request->pais;
			}
			if(!isset($data['codigoAreaTelefono_id'])){
				$data['codigoAreaTelefono_id'] = $request->region;
			}
			if(!isset($data['telefono'])){
				$data['telefono'] = $request->telefono;
			}
			if(!isset($data['contactoEmergencia'])){
				$data['contactoEmergencia'] = $request->contactoEmergencia;
			}

            $lobjPersona = \DB::table("tbl_personas")
                ->where("IdPersona","!=",$request->input('IdPersona'))
                ->where("IdTipoIdentificacion","=",$pintIdTipoIdentificacion)
                ->where("Rut","=",$data['RUT'])
                ->get();
            if ($lobjPersona){
                return response()->json(array(
                    'message'	=> "Esta identificación ya la tiene otra persona",
                    'status'	=> 'error'
                ));
            }

			$id = $this->model->insertRow($data , $request->input('IdPersona'));
			if ($lintLevelUser==1){
			  $this->detailviewsave( $this->modelview , $request->all() ,$this->info['config']['subform'] , $id) ;
			}


			/* +++++++++++++++++++  Modulo de Discapacidad +++++++++++++++++++*/
			//se obtiene el id de tipo de documento con id proceso 142
			$idTipoDocumento = \DB::table('tbl_tipos_documentos')->where('IdProceso',142)->first();
			if($idTipoDocumento){
				$idTipoDocumento = $idTipoDocumento->IdTipoDocumento;
				$discapacidad = self::LevantarDocumentosPersonas($data['discapacidad'],$request->input('IdPersona'),$idTipoDocumento, 142);
			}
			/* +++++++++++++++++++ END Modulo de Discapacidad +++++++++++++++++++*/

			/* +++++++++++++++++++  Modulo de Pensionado +++++++++++++++++++*/
			//se obtiene el id de tipo de documento con id proceso 143
			$idTipoDocumento2 = \DB::table('tbl_tipos_documentos')->where('IdProceso',143)->first();
			if($idTipoDocumento2){

				$idTipoDocumento2 = $idTipoDocumento2->IdTipoDocumento;

				$pensionado = self::LevantarDocumentosPersonas($data['pensionado'],$request->input('IdPersona'),$idTipoDocumento2, 143);
			}
			/* +++++++++++++++++++ END Modulo de Pensionado +++++++++++++++++++*/

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
	public function getShowdocuments(Request $request){

		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
		$lobjFiltro = \MySourcing::getFiltroUsuario(2,1);

	  $lintIdUser = \Session::get('uid');
	  $lintIdPersona = $request->input('idpersona');
	  $larrResult = array();

	  //\DB::enableQueryLog();

		if($lintLevelUser==1){
			$lobjDocumentos = \DB::table('tbl_documentos')
					->select("tbl_documentos.IdDocumento","tbl_documentos.IdTipoDocumento", \DB::raw("tbl_tipos_documentos.Descripcion as TipoDocumento"), "tbl_documentos.FechaEmision", "tbl_documentos.FechaVencimiento", "tbl_documentos.IdEstatus", \DB::raw("tbl_documentos_estatus.Descripcion as Estatus"), \DB::raw("if(`tbl_documentos`.`IdEstatusDocumento` IS NULL, 2,`tbl_documentos`.`IdEstatusDocumento`) AS IdEstatusDocumento"), "tbl_documentos.Vencimiento")
					->join("tbl_tipos_documentos","tbl_tipos_documentos.IdTipoDocumento","=","tbl_documentos.IdTipoDocumento")
					->join("tbl_documentos_estatus","tbl_documentos_estatus.IdEstatus","=","tbl_documentos.IdEstatus")
					->where("tbl_documentos.IdEntidad","=",$lintIdPersona)
					->where("tbl_documentos.Entidad","=",3)
					->where(function ($query) {
							$query->where("tbl_documentos.IdEstatus", "!=", 5)
									->orwhere(function ($query) {
											$query->where("tbl_documentos.IdEstatus", "=", 5)
											->where("tbl_documentos.IdEstatusDocumento", "!=", "1");
									})->orwhere(function ($query) {
											$query->whereRaw(\DB::raw("tbl_documentos.IdEstatus = 5 AND DATEDIFF(tbl_documentos.FechaVencimiento,CURDATE())<tbl_tipos_documentos.DiasVencimiento AND tbl_tipos_documentos.Vigencia=1 AND tbl_documentos.Vencimiento=1"));
									});
					})
					->whereExists(function ($query) {
						$lintGroupUser = \MySourcing::GroupUser(\Session::get('uid'));
							$query->select(\DB::raw(1))
										->from('tbl_tipo_documento_perfil')
										->whereRaw('tbl_tipo_documento_perfil.IdPerfil = '.$lintGroupUser)
										->whereRaw('tbl_tipo_documento_perfil.IdTipoDocumento = tbl_documentos.IdTipoDocumento');
				})
			->whereIn("tbl_documentos.contrato_id",$lobjFiltro['contratos'])
			->get();

		}else{
			$lobjDocumentos = \DB::table('tbl_documentos')
					->select("tbl_documentos.IdDocumento","tbl_documentos.IdTipoDocumento", \DB::raw("tbl_tipos_documentos.Descripcion as TipoDocumento"), "tbl_documentos.FechaEmision", "tbl_documentos.FechaVencimiento", "tbl_documentos.IdEstatus", \DB::raw("tbl_documentos_estatus.Descripcion as Estatus"), \DB::raw("if(`tbl_documentos`.`IdEstatusDocumento` IS NULL, 2,`tbl_documentos`.`IdEstatusDocumento`) AS IdEstatusDocumento"),"tbl_documentos.Vencimiento")
					->join("tbl_tipos_documentos","tbl_tipos_documentos.IdTipoDocumento","=","tbl_documentos.IdTipoDocumento")
					->join("tbl_documentos_estatus","tbl_documentos_estatus.IdEstatus","=","tbl_documentos.IdEstatus")
					->where("tbl_documentos.IdEntidad","=",$lintIdPersona)
					->where("tbl_documentos.Entidad","=",3)
					->where(function ($query) {
						 $query->where("tbl_documentos.IdEstatus", "!=", 5)->where("tbl_documentos.IdEstatus", "!=", 7)
								 ->orwhere(function ($query) {
										 $query->where("tbl_documentos.IdEstatus", "=", 5)
										 ->where("tbl_documentos.IdEstatusDocumento", "!=", "1");
								 })->orwhere(function ($query) {
										 $query->whereRaw("tbl_documentos.IdEstatus = 5 AND DATEDIFF(tbl_documentos.FechaVencimiento,CURDATE())<tbl_tipos_documentos.DiasVencimiento AND tbl_tipos_documentos.Vigencia=1 AND tbl_documentos.Vencimiento=1");
								 });
					})
					->whereExists(function ($query) {
					 $lintGroupUser = \MySourcing::GroupUser(\Session::get('uid'));
							$query->select(\DB::raw(1))
										->from('tbl_tipo_documento_perfil')
										->whereRaw('tbl_tipo_documento_perfil.IdPerfil = '.$lintGroupUser)
										->whereRaw('tbl_tipo_documento_perfil.IdTipoDocumento = tbl_documentos.IdTipoDocumento');
			 })
		 ->whereIn("tbl_documentos.contrato_id",$lobjFiltro['contratos'])
		 ->get();
		}

      foreach ($lobjDocumentos as $row) {

			$id = $row->IdDocumento;
			$larrResultTemp = $row;
			$larrResultTemp->FechaVencimiento = \MyFormats::FormatDate($larrResultTemp->FechaVencimiento);
			$larrResultTemp->FechaEmision = \MyFormats::FormatDate($larrResultTemp->FechaEmision);
			if ($row->IdEstatus==5 and $row->IdEstatusDocumento != 2 and $row->Vencimiento==0){
				$larrResultTemp->{'action'} = "-";
			}else{
				if ($row->IdEstatus==5) {
				if (!(intval($row->IdTipoDocumento)==3 || intval($row->IdTipoDocumento)==6 || intval($row->IdTipoDocumento)==21 || intval($row->IdTipoDocumento)==26 || intval($row->IdTipoDocumento)==31)){
					$larrResultTemp->{'action'} = '<div class=" action dropup"><a href="'.\URL::to('documentos/update/-'.$id).'" onclick="SximoModalDocuments(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="Renovar"><i class="fa fa-calendar-times-o"></i></a></div>';
				}else{
					$larrResultTemp->{'action'} = "-";
				}
				}else{
				$larrResultTemp->{'action'} = '<div class=" action dropup"><a href="'.\URL::to('documentos/update/'.$id).'" onclick="SximoModalDocuments(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="Cargar"><i class="fa  fa-upload"></i></a></div>';
				}
			}
			$larrResult[] = $larrResultTemp;
		}

		echo json_encode(array("data"=>$larrResult));

        //var_dump(\DB::getQueryLog());

		//echo json_encode(array("data"=>$lobjDocumentos));
	}

	function postMasivo(Request $request, $id =0){

		//Proceso de carga masiva de personas
		$larrResult = \MyLoadbatch::LoadBach(1, Input::file("FileDataEmployee"));
		return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success'),
				'result'=>$larrResult
				));

	}

	public function getViewResult(Request $request){

		require_once '../app/Library/PHPExcel.php';

		$lstrNombreArchivo = $request->input('lstrCode');
		$destinationPath = "uploads/documents/";
		$objPHPExcel2 = new \PHPExcel();
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel2, "Excel2007");

		// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="$lstrNombreArchivo"');
		header('Cache-Control: max-age=0');
		//$objWriter->save($destinationPath.'Resultado.xlsx');
		$objWriter->save('php://output');
		exit;
	}

	public function postBorrar( Request $request)
	{

		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');

		if($this->access['is_edit'] ==0) {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_restric')
			));
			die;

		}
		if(count($request->input('IdPersona')) >=1)
		{

            $Id = $request->input('IdPersona');
            $anot = $request->input('result');
            $fecha = date('Y/m/d');
            $IdUser = $request->input('usuario');

            $contratos = \DB::table('tbl_contratos_personas')
                  ->select('IdPersona','IdContratista','contrato_id')
                  ->where('IdPersona', '=', $Id)
                  ->get();
            if ($contratos){
                   $IdCont = $contratos[0]->contrato_id;
               }else{
               	$IdCont = 0;
               }
     if ($IdCont){

     	//Eliminamos los documentos que estén por cargar de las personas
        \DB::table('tbl_documentos')->where('Entidad', '=', 3)
                                    ->where('IdEntidad', '=', $Id)
                                    ->where('IdEstatus', '!=', 5)
                                    ->delete();

        //Le quitamos visibilidad de los documentos que tiene la persona
        \DB::table('tbl_documentos')->where('Entidad', '=', 3)
                                    ->where('IdEntidad', '=', $Id)
                                    ->update(["entry_by_access"=>0]);

	 	$IdDoc = \DB::table('tbl_documentos')->insertGetId(
            ['IdTipoDocumento' => 4, 'Entidad' => 3, 'IdEntidad'=> $Id, 'Documento' => NULL, 'DocumentoURL' => NULL, 'FechaVencimiento' => NULL, 'IdEstatus' => 1, 'createdOn' => $fecha, 'entry_by'=> $IdUser, 'entry_by_access' => 0, 'updatedOn'=> NULL, 'FechaEmision'=> NULL, 'contrato_id' => $IdCont, 'Resultado'=> NULL ]);

	      $IdAnotac = \DB::table('tbl_anotaciones')->insertGetId(
		['IdConceptoAnotacion' => $anot, 'IdPersona' => $Id, 'createdOn' => $fecha, 'entry_by'=> $IdUser, 'entry_by_access' => 0, 'updatedOn'=> null ]);

	  	$lintIdMovimientoPersona = \DB::table('tbl_movimiento_personal')
                                ->insertGetId([
                                  "IdAccion" => 2,
                                  "contrato_id" => $IdCont,
                                  "IdPersona" => $Id,
                                  "entry_by" => $lintIdUser]
                                );

	    \DB::table('tbl_contratos_personas')->where('IdPersona', '=', $Id)->where('contrato_id', '=', $IdCont)->delete();


	}
	\DB::table('tbl_personas')->where('IdPersona', $Id)->update(['entry_by_access' => 0]);

	$acce = \DB::table('tbl_accesos')
                  ->select('IdAcceso')
                  ->where('IdPersona', '=', $Id)
                  ->where('contrato_id', '=', $IdCont)
                  ->get();

              if (sizeof($acce)>0) {
                   $valor = $acce[0]->IdAcceso;
                             \DB::table('tbl_acceso_areas')->where('IdAcceso', '=', $valor)->delete();
                              \DB::table('tbl_accesos')->where('IdAcceso', '=', $valor)->delete();
              }

			return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success')
			));

		} else {
			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));

		}

	}

	public function postValidapersona(Request $request) {

		$lbolContratado = false;
		$lintIdPersona = $request->input('IdPersona');
        $lobjPersona = \DB::table('tbl_personas')
           		  ->select('tbl_personas.IdPersona','tbl_personas.Rut','tbl_personas.Nombres','tbl_personas.Apellidos','tbl_contratos_personas.IdRol', \DB::raw('tbl_roles.Descripción as Rol'), 'tbl_contratos_personas.IdContratista','tbl_contratos_personas.contrato_id')
                  ->leftJoin("tbl_contratos_personas","tbl_personas.IdPersona", "=", "tbl_contratos_personas.IdPersona")
                  ->leftJoin("tbl_roles","tbl_roles.IdRol", "=", "tbl_contratos_personas.IdRol")
                  ->where('tbl_personas.IdPersona', '=', $lintIdPersona)
                  ->first();

        if ($lobjPersona) {

	        if ($lobjPersona->contrato_id) {
	        	$lintIdContrato = $lobjPersona->contrato_id;

	        	//Buscamos el contrato de trabajo que debe tener vigente
	        	$lbojContratoTrabajo = \DB::table('tbl_documentos')
	        	->where("tbl_documentos.IdEntidad", "=", $lintIdPersona)
	        	->where("tbl_documentos.entidad", "=", 3)
	        	->where("tbl_documentos.IdTipoDocumento", "=", 21)
	        	->first();

	        	if ($lbojContratoTrabajo){
	        		$ldatFechaContrato = $lbojContratoTrabajo->FechaVencimiento;
	        		$lintIdDocumento = $lbojContratoTrabajo->IdDocumento;
	        		$lobjPersona->{'FechaContrato'} = $ldatFechaContrato;
	        		$lobjPersona->{'IdDocumento'} = $lintIdDocumento;
	        	}
	        	$lbolContratado = true;
	        }
	        return response()->json(array(
					'status'=>'success',
					'message'=> \Lang::get('core.note_success'),
					'lobjPersona'=>$lobjPersona,
					"result"=>$lbolContratado
				));
	    }else{
	        return response()->json(array(
					'status'=>'error',
					'message'=> 'No existe la persona',
					'lobjPersona'=>array(),
					"result"=>''
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
			\DB::table('tbl_anotaciones')->whereIn('IdPersona',$request->input('ids'))->delete();
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
		$model  = new Personas();
		$info = $model::makeInfo('personas');

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
				return view('personas.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdPersona' ,
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
			return view('personas.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_personas');
			 $this->model->insertRow($data , $request->input('IdPersona'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}

	public  function postCompruebarut(Request $request)
	{
		$rut = $request->rut;
        $tipoidentificacion = $request->tipoidentificacion;
		$larrResultado = array();
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	    $lintIdUser = \Session::get('uid');

	    if ($lintLevelUser==6){

	    }

	    if ($tipoidentificacion == 1){
	        $rut = \MySourcing::FormatRut($rut);
        }

	    //limpiamos la base de datos
		$personas = \DB::table('tbl_personas')
							->select(\DB::raw("tbl_personas.*, tbl_anotaciones.IdAnotacion, tbl_anotaciones.IdConceptoAnotacion, tbl_anotaciones.IdPersona as IdpersonaA, tbl_anotaciones.Observacion,tbl_contrato.admin_id"))
							->leftJoin('tbl_anotaciones', 'tbl_personas.IdPersona', '=', 'tbl_anotaciones.IdPersona')
							->leftJoin("tbl_contratos_personas","tbl_personas.IdPersona","=","tbl_contratos_personas.IdPersona")
							->leftJoin("tbl_contrato","tbl_contrato.contrato_id","=","tbl_contratos_personas.contrato_id")
							->where('tbl_personas.RUT', '=', $rut)
                            ->where('tbl_personas.IdTipoIdentificacion','=',$tipoidentificacion)
							->get();

		$larrResultado = array('status'=>'sucess',
							   'valores'=>'',
							   'message'=>\Lang::get('core.note_sucess'),
							   'code'=> '0'
							  );
        if ($personas ){
            $personas[0]->{'FechaNacimiento'} = \MyFormats::FormatDate($personas[0]->{'FechaNacimiento'});
            if ($lintLevelUser==6){
                if ($personas[0]->{'entry_by_access'} == $lintIdUser || $personas[0]->{'entry_by_access'} == 0 ){
                    $larrResultado['code'] = 1;
                    $larrResultado['valores'] = $personas;
                }else{
                    $larrResultado['code'] = 2;
                }
            }else if ($lintLevelUser==4){
                if ($personas[0]->{'entry_by_access'}==$lintIdUser || $personas[0]->{'admin_id'}==$lintIdUser || $personas[0]->{'entry_by_access'} == 0 ){
                    $larrResultado['code'] = 1;
                    $larrResultado['valores'] = $personas;
                }else{
                    $larrResultado['code'] = 2;
                }
            }else{
                $larrResultado['code'] = 1;
                $larrResultado['valores'] = $personas;
            }
        }

		return response()->json($larrResultado);
	}

	public  function postBuscaanotacion(Request $request)
	{
		$Idperso = $request->persona;

 		$anotacionesP =  \DB::table('tbl_anotaciones')
                    ->join('tb_users', 'tbl_anotaciones.entry_by', '=', 'tb_users.id')
                    ->join('tbl_concepto_anotacion', 'tbl_anotaciones.IdConceptoAnotacion','=','tbl_concepto_anotacion.IdConceptoAnotacion')
                    ->where('IdPersona',$Idperso)->get();
		return response()->json(array(
			'status'=>'sucess',
			'anotaciones'=>$anotacionesP,
			'message'=>\Lang::get('core.note_sucess')
			));
	}

    private function FormatoFecha($pstrFecha){
	    if ($pstrFecha){
            $larrFecha = explode("/", $pstrFecha);
            return $larrFecha[2].'-'.$larrFecha[1].'-'.$larrFecha[0];
        }
	}

	public function LevantarDocumentosPersonas($valor,$IdPersona,$idTipoDocumento, $idProceso ){

		if($valor == 1){

			//vemos si la persona esta asignada a un contrato y en ese caso obtenemos los datos del contrato y del contratista
			$contratoPersonas = \DB::table('tbl_contratos_personas')->where('IdPersona', $IdPersona)->first();
			if($contratoPersonas){
				$idContratista = $contratoPersonas->IdContratista;
				$idContrato = $contratoPersonas->contrato_id;
			}else{
				$idContratista = "";
				$idContrato = "";

			}
			//se valida si el documento existe
			$existeDoc = \DB::table('tbl_documentos')
							->where('IdEntidad', $IdPersona)
							->where('Entidad', 3)
							->where('IdTipoDocumento', $idTipoDocumento)
							->first();
			if(!$existeDoc){
				$existeDocH = \DB::table('tbl_documentos_rep_historico')
							->where('IdEntidad', $IdPersona)
							->where('Entidad', 3)
							->where('IdTipoDocumento', $idTipoDocumento)
							->first();

				if($existeDocH){

					//si el documento es del mismo contrato lo pasamos a documentos sino simplemente lo eliminamos y creamos otra solicitud
					if($idContrato == $existeDocH->contrato_id){
						//Pasar documento del historico a la tabla tbl_documentos
						$lobjDocumentos = new MyDocuments();
						$larrResultado = $lobjDocumentos::FromStore($existeDocH->IdDocumento);
						if($idProceso == 143){
							$existePensionado = \DB::table('tbl_pensionados')->where('IdPersona',$IdPersona)->first();
							if($existePensionado){
								$existePensionado->contrato_id = $idContrato;
								$existePensionado->IdContratista = $idContratista;
								$existePensionado->save();
							}else{
								\DB::table('tbl_pensionados')->insert([
									'IdPersona' => $IdPersona,
									'contrato_id' => $idContrato,
									'IdContratista' => $idContratista,
									'Fecha'	 => date('Y-m-d H:i:s')
								]);
							}
						}
					}else{
						//eliminamos el documento
						\DB::table('tbl_documentos_rep_historico')->where('IdDocumentoH',$existeDocH->IdDocumentoH)->delete();
						//creamos una nueva solicitud
						$lobjDocumentos = new MyDocuments();
						$larrResultado = $lobjDocumentos::Save($idTipoDocumento,3,$IdPersona,"",$idContratista, $idContrato);
					}
				}else{
					//sino esta se crea un nuevo documento
					$lobjDocumentos = new MyDocuments();
					$larrResultado = $lobjDocumentos::Save($idTipoDocumento,3,$IdPersona,"",$idContratista, $idContrato);
				}
			}

		}else{
			//si el documento esta y se le quita el check se envia al historico.
			$existeDoc = \DB::table('tbl_documentos')
							->where('IdEntidad', $IdPersona)
							->where('Entidad', 3)
							->where('IdTipoDocumento', $idTipoDocumento)
							->first();
			if($existeDoc){
				$existeDocA = \DB::table('tbl_documentos')
							->where('IdEntidad', $IdPersona)
							->where('Entidad', 3)
							->where('IdTipoDocumento', $idTipoDocumento)
							->where('IdEstatus', 5)
							->first();
				if($existeDocA){
					$lobjDocumentos = new MyDocuments($existeDoc->IdDocumento);
					$larrResultado = $lobjDocumentos::Store();
				}else{
					\DB::table('tbl_documentos')->where('IdDocumento',$existeDoc->IdDocumento)->delete();
				}
			}
			if($idProceso == 143){
				$existePensionado = \DB::table('tbl_pensionados')->where('IdPersona',$IdPersona)->delete();
			}
		}
	}

	public function ReportesLogo($drawing){
		$cliente = \DB::table('tbl_configuraciones')->where('nombre','CNF_APPNAME')->first();
		  	switch ($cliente->Valor) {
				case 'CCU':
					$drawing->setPath(base_path('public/images/ccu.png'));
					break;
				case 'Ohl Industrial':
					$drawing->setPath(base_path('public/images/logoohl.png'));
					break;
				default:
					$drawing->setPath(base_path('public/images/check.png'));
					break;
			}
		 $drawing->setHeight(70);
		 //$drawing->setOffsetX(30);
		 return($drawing);
	}
/// Reporte de Persona Enviar por CSV 14-05-2021
	public function getGenerarPersonaDiarioCSV(){
		$hoy = date('d-m-Y');
		$personasNoAcreditado = \DB::table('tbl_personas')
					->selectRaw("tbl_personas.IdPersona,tbl_contrato_centrotrabajo.contrato_id, SUBSTRING(tbl_personas.RUT, 1, 8) as RUT_SIN_DIGITO, tbl_personas.RUT , tbl_personas.Nombres , tbl_personas.Apellidos , tbl_personas.Telefono , tbl_centro_trabajo.Descripcion as CTP , tbl_contratistas.RazonSocial , tbl_centro.Descripcion as UEN, tbl_documento_valor.Valor as Cargo_Contrato,tbl_documentos_estatus.Descripcion as Documento_Acreditado,tbl_centro_trabajo.descripcion as CTA")
					->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
					->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contratos_personas.IdContratista')
					->join('tbl_contratos_centros','tbl_contratos_centros.IdContratista','=','tbl_contratistas.IdContratista')
					->join('tbl_centro','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
					->join('tbl_documentos','tbl_documentos.IdEntidad','=','tbl_personas.IdPersona')
					->join('tbl_documentos_estatus','tbl_documentos_estatus.IdEstatus','=','tbl_documentos.IdEstatus')
					->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
					->join('tbl_contrato_uenct','tbl_contrato_uenct.contrato_id','=','tbl_contratos_centros.contrato_id')
					->join('tbl_contrato_centrotrabajo','tbl_contrato_centrotrabajo.contrato_id','=','tbl_contrato.contrato_id')
					->join('tbl_uen_ct','tbl_uen_ct.uenct_id','=','tbl_contrato_centrotrabajo.uen_ct_id')
					->join('tbl_centro_trabajo','tbl_centro_trabajo.ct_id','=','tbl_uen_ct.ct_id')
					->join('tbl_documento_valor','tbl_documento_valor.IdDocumento','=','tbl_documentos.IdDocumento')
					->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
					->join('tbl_tipo_documento_valor','tbl_tipo_documento_valor.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
					->whereRaw('tbl_contratos_centros.contrato_id = tbl_contratos_personas.contrato_id')
					->whereRaw('tbl_tipo_documento_valor.IdTipoDocumentoValor = tbl_documento_valor.IdTipoDocumentoValor')
					->whereRaw('tbl_documentos.contrato_id = tbl_contratos_personas.contrato_id')
					->where('tbl_documentos.Entidad','=',3)
					->where('tbl_documentos.IdTipoDocumento','=',55)
					->where('tbl_tipo_documento_valor.IdTipoDocumentoValor','=',128)
					->where('tbl_contrato.ContratoPrueba','=', 0)
					->orderBy('tbl_personas.IdPersona','asc')
             		->get();

	$personaAcreditado = \DB::select(\DB::raw("SELECT p.IdPersona , e.Descripcion as Razon FROM tbl_personas AS  p
							INNER JOIN tbl_contratos_personas AS cp ON
							p.IdPersona = cp.IdPersona
							INNER JOIN tbl_contratistas AS co ON
							co.IdContratista = cp.IdContratista
							INNER JOIN tbl_contratos_centros AS cc ON
							cc.IdContratista = co.IdContratista
							INNER JOIN tbl_centro AS  ce ON
							ce.IdCentro = cc.IdCentro
							INNER JOIN tbl_documentos AS d ON
							d.IdEntidad = p.IdPersona
							INNER JOIN tbl_tipos_documentos td ON
							td.IdTipoDocumento = d.IdTipoDocumento
							INNER JOIN 	tbl_documentos_estatus AS e
							ON e.IdEstatus = d.IdEstatus
							INNER JOIN tbl_contrato AS ct ON
							ct.IdContratista = co.IdContratista
							INNER JOIN tbl_personas_acreditacion AS h1 ON
							h1.IdPersona = p.IdPersona
							INNER JOIN tbl_tipo_documento_valor AS dv on
							dv.IdTipoDocumento = d.IdTipoDocumento
							INNER JOIN tbl_documento_valor AS v on
							v.IdDocumento = d.IdDocumento
							INNER JOIN (select IdPersona,
							max(acreditacion) AS max_fecha
							FROM tbl_personas_acreditacion
							GROUP BY IdPersona
									) h2
							ON  h1.IdPersona = h2.IdPersona
							AND  h1.acreditacion = h2.max_fecha
							AND  h1.IdEstatus = 1
							AND  ct.contrato_id = cc.contrato_id
							AND  d.Entidad = 3
							AND  ct.cont_estado = 1
							AND  d.IdTipoDocumento = 55
							AND ct.ContratoPrueba =0
							AND dv.IdTipoDocumentoValor=128
							AND dv.IdTipoDocumentoValor = v.IdTipoDocumentoValor
							AND  cc.IdContratista = cp.IdContratista
							AND cc.contrato_id = cp.contrato_id
							ORDER BY p.IdPersona ASC"));
	// Tomo todos los id de las personas acreditados y los guardo en un array
		$doc_id = array();
		foreach ($personaAcreditado as $ope)
		{
			array_push($doc_id,['IdPersona' =>$ope->IdPersona,'Razon' =>$ope->Razon]);

		}

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('CONTROL ACCESSO NPR CCU');
		$spreadsheet->setActiveSheetIndex(0);
		$spreadsheet->getActiveSheet()->getStyle('C1')->getFont()->setSize(13)->setBold(true);
		$sheet->setCellValue('C1', 'CONTROL ACCESSO NPR');
		$spreadsheet->getActiveSheet()->setAutoFilter('A1:I1');
		$spreadsheet->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
		$sheet->setCellValue('A1', 'RUT SIN DIGITO');
		$sheet->setCellValue('B1', 'RUT');
		$sheet->setCellValue('C1', 'NOMBRES');
		$sheet->setCellValue('D1', 'APELLIDOS');
		$sheet->setCellValue('E1', 'TELEFONO');
		$sheet->setCellValue('F1', 'CTP');
		$sheet->setCellValue('G1', 'RAZON SOCIAL CONTRATISTA');
		$sheet->setCellValue('H1', 'UEN');
		$sheet->setCellValue('I1', 'CARGO EN CONTRATO');
		$sheet->setCellValue('J1', 'ESTADO');
		$sheet->setCellValue('K1', 'RAZON');
		$sheet->setCellValue('L1', 'CTA');

		$j=2;
		$n=0;
		$i = 0;
		foreach ($personasNoAcreditado as $personacheck) {

			if(!empty($doc_id[$i]['IdPersona'])){

				if($doc_id[$i]['IdPersona'] == $personacheck->IdPersona) {

					$Estado = 'Acreditado';
					$Razon = $doc_id[$i]["Razon"];
					$i++;
				}else{
					$Estado = 'No Acreditado';
					$Razon = '-';
				}

			}else{
					$Estado = 'No Acreditado';
					$Razon = '-';
				 }

			if(substr($personacheck->RUT_SIN_DIGITO,7) == '-') {
				$rut = $n.substr($personacheck->RUT_SIN_DIGITO,0,7);
			}else{
				$rut = $personacheck->RUT_SIN_DIGITO;
				}

				if(!empty($personacheck->Telefono))
				{
					$tlf = $personacheck->Telefono;
				}else{
					$tlf = '255'.$rut;
				}

			$sheet->setCellValue('A'.$j, $rut);
			$sheet->setCellValue('B'.$j, $personacheck->RUT);
			$sheet->setCellValue('C'.$j, strtoupper($personacheck->Nombres));
			$sheet->setCellValue('D'.$j, strtoupper($personacheck->Apellidos));
			$sheet->setCellValue('E'.$j, strtoupper($tlf));
			$sheet->setCellValue('F'.$j, strtoupper($personacheck->CTP));
			$sheet->setCellValue('G'.$j, strtoupper($personacheck->RazonSocial));
			$sheet->setCellValue('H'.$j, strtoupper($personacheck->UEN));
			$sheet->setCellValue('I'.$j, strtoupper($personacheck->Cargo_Contrato));
			$sheet->setCellValue('J'.$j, strtoupper($Estado));
			$sheet->setCellValue('K'.$j, strtoupper($Razon));
			$sheet->setCellValue('L'.$j, strtoupper($personacheck->CTA));
			$j++;

		} // Fin del Foreach

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet charset=UTF-8');
		header('Content-Disposition: attachment;filename="datos_control_acceso_ccu.csv"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Csv');
		$writer->save(storage_path('app/public/datos_control_acceso_ccu.csv'));
		$writer->save(storage_path('datos_control_acceso_ccu.csv'));
	}

	public function sendPersonaEmail()
	{
		$excel = storage_path("datos_control_acceso_ccu.csv");

		\Mail::send('emails.informeacceso',['excel'=>$excel], function ($m) use ($excel){
			$m->from(CNF_EMAIL);
			$destinatarios = 'jmquiroz@deloitte.com';
			$m->to(CNF_EMAIL);
			$m->subject("Datos Control de Accesos CCU");
			$m->bcc($destinatarios);
			$m->attach($excel);
 		});

	}


}
