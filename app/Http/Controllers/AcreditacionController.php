<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Acreditacion;
use App\Models\Contratos;
use App\Models\Documentos;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Database\Eloquent\Builder;
use Yajra\Datatables\Facades\Datatables;
use Validator, Input, Redirect ; 
use App\Library\MyDocuments;
use App\Library\MyContracts;
use App\Models\Complejidad;
use App\Models\Extensioncontrato;
use App\Models\Tiposdegarantias;
use App\Models\Tipogasto;
use App\Models\Tblcontareafuncional;
use App\Models\Segmentos;
use App\Models\Georeporte;
use App\Models\Contratistas;
use App\Models\Core\Users;
use App\Models\Contratospersonas;
use App\Models\Personas;
use App\Library\Acreditacion as AcreditacionPersona;
use App\Library\AcreditacionContrato;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PDF;
use DB;


class AcreditacionController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'acreditacion';
	static $per_page	= '10';
	
	public function __construct() 
	{
		parent::__construct();
		$this->model = new Acreditacion();
		
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'acreditacion',
			'pageUrl'			=>  url('acreditacion'),
			'return' 			=> 	self::returnUrl()	
		);		
				
	} 
	
	public function getIndex()
	{
		if($this->access['is_view'] ==0) 
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
				
		$this->data['access']		= $this->access;	
		return view('acreditacion.index',$this->data);
	}

	public function postShowlist( Request $request ) {

		$module = 'acreditacion';
		$access = $this->access;
		$setting = $this->info['setting'];

		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');
        $lobjFiltro = \MySourcing::getFiltroUsuario(1, 1);

		$lobjContratos = \DB::table('tbl_contrato')
						 ->select(\DB::raw("'' as Accion"),
								   "tbl_contrato.contrato_id",
								   "tbl_contratistas.RUT",
								   "tbl_contrato.cont_nombre as NombreContrato",
						 	      "tbl_contrato.cont_numero as NumeroContrato",
								  \DB::raw("case when ifnull(tbl_contratistas.NombreFantasia,'')='' then tbl_contratistas.RazonSocial else tbl_contratistas.NombreFantasia end as Contratista"),
								  "tbl_contrato.cont_proveedor as Servicio",
								  "tbl_centro.Descripcion as Faena",
								  \DB::raw("tbl_contrato.acreditacion as Acreditacion"),
								  \DB::raw("tbl_contrato.controllaboral as ControlLaboral"),
								  \DB::raw("case when tbl_contratos_acreditacion.acreditacion is null then '<div class=\"acreditacion noacreditado alert-danger\">Sin Acreditación</div>' else '<div class=\"acreditacion acreditado alert-success\">Con Acreditación</div>' end as Estatus"),
								   "tbl_contratos_acreditacion.acreditacion as FechaAcreditacion"

						 	  )
						 ->leftjoin('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
						 ->leftjoin('tbl_contareafuncional','tbl_contareafuncional.afuncional_id','=','tbl_contrato.afuncional_id')
						 ->leftjoin('tbl_contrato_estatus','tbl_contrato_estatus.id','=','tbl_contrato.cont_estado')
						 ->leftjoin('tbl_contsegmento','tbl_contsegmento.segmento_id','=','tbl_contrato.segmento_id')
						 ->leftJoin('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_contrato.contrato_id')
						 ->leftJoin('tbl_centro','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
						 ->leftjoin('tbl_contratos_acreditacion',function ($table) {
						 		$table->on('tbl_contratos_acreditacion.contrato_id','=','tbl_contrato.contrato_id')
						 		      ->on('tbl_contratos_acreditacion.IdEstatus','=',\DB::raw('1'));
						 })
						 ->whereraw('tbl_contrato.contrato_id IN ('.$lobjFiltro['contratos'].') ');

		$lobjQuery = \DB::table(\DB::raw("({$lobjContratos->toSql()}) as acce"))->select(\DB::raw("*"));
		$lobjDataTable = Datatables::queryBuilder($lobjQuery)
                ->editColumn('Accion', function ($lobjContrato) {
					$onclick = "";
                	$html = '<div class="btnAcciones">';
					$html .= '<a data-id="'.$lobjContrato->contrato_id.'" class="btn btn-xs btn-white tips details-control rotaterow" title="Ver personas"><i class="fa fa-arrow-right"></i></a>';
					$html .= ' </div>';
                    return $html;
                })
                ->editColumn('Acreditacion', function ($lobjContrato) {
					$html = '<center><input disabled type="checkbox" data-id-contrato="'.$lobjContrato->contrato_id.'" class="checkacreditacioncontrato checkbox" '.($lobjContrato->Acreditacion?'checked="checked"':'').' /></center>';
                    return $html;
                })
                ->editColumn('ControlLaboral', function ($lobjContrato) {
					$html = '<center><input disabled type="checkbox" data-id-contrato="'.$lobjContrato->contrato_id.'" class="checkcontrollaboralcontrato checkbox" '.($lobjContrato->ControlLaboral?'checked="checked"':'').' /></center>';
                    return $html;
                })
                ->editColumn('Estatus', function ($lobjContrato) {
                	if ($lobjContrato->Acreditacion){
						$html = $lobjContrato->Estatus;
					}else{
						$html = "";
					}
                    return $html;
                })
                ->editColumn('FechaAcreditacion', function ($lobjContrato) {
                	if ($lobjContrato->Acreditacion){
						$html = \MyFormats::FormatDate($lobjContrato->FechaAcreditacion);
					}else{
						$html = "";
					}
                    return $html;
                })
                ->make(true);
		return $lobjDataTable;

	}

	public function postShowinformation(Request $request, $id = null){
		$module = 'acreditacion';
		$access = $this->access;
		$lobjDocumento = Contratospersonas::join("tbl_personas","tbl_personas.IdPersona","=","tbl_contratos_personas.IdPersona")
						 ->join("tbl_roles","tbl_roles.IdRol","=","tbl_contratos_personas.IdRol")
						 ->select('tbl_personas.RUT', 
								   'tbl_personas.Nombres', 
								   'tbl_personas.Apellidos', 
								   'tbl_roles.Descripción as Rol',
								   "tbl_contratos_personas.IdPersona",
								   "tbl_contratos_personas.contrato_id",
								   "tbl_contratos_personas.acreditacion",
								   "tbl_contratos_personas.controllaboral",
								   \DB::raw("case when tbl_personas_acreditacion.acreditacion is null then '<div class=\"acreditacion noacreditado alert-danger\">Sin Acreditación</div>' else '<div class=\"acreditacion acreditado alert-success\">Con Acreditación</div>' end as Estatus"),
								   "tbl_personas_acreditacion.acreditacion as FechaAcreditacion"
						          )
						 ->leftJoin('tbl_personas_acreditacion',function($table){
						 	$table->on('tbl_personas_acreditacion.IdPersona','=','tbl_contratos_personas.IdPersona')
						 	      ->on('tbl_personas_acreditacion.IdEstatus','=',\DB::raw('1'));
						 })
                  		 ->where("tbl_contratos_personas.contrato_id","=",$id)
				  		 ->get();
		$this->data['access'] = $access;
		$this->data['lobjDocumento'] = $lobjDocumento;

		return view('acreditacion.personas',$this->data);
	}			

	public function postCheckacreditacion( Request $request) {

		$lintIdPersona = $request->input('idpersona');
		$lintContratoId = $request->input('contrato_id');
		$lbolAcreditacion = $request->input('acreditacion');

		$lobjAcreditacion = new AcreditacionPersona($lintIdPersona);
		$larrResult = $lobjAcreditacion::Accreditation($lbolAcreditacion);

		return $larrResult;

	}

	public function postCheckcontrollaboral( Request $request) {

		$lintIdPersona = $request->input('idpersona');
		$lintContratoId = $request->input('contrato_id');
		$lbolControlLaboral = $request->input('controllaboral');
		if ($lbolControlLaboral=="true"){
			$lbolControlLaboral = 1;
		}else{
			$lbolControlLaboral = 0;
		}

		\DB::table('tbl_contratos_personas')->where('contrato_id',$lintContratoId)
											->where('idpersona',$lintIdPersona)
											->update(["controllaboral"=>$lbolControlLaboral]);

		return response()->json(array(
				'status'=>'success',
				'message'=> "Actualizado"
			));	 

	}

	public function postCheckacreditacioncontrato( Request $request) {

		$lintContratoId = $request->input('contrato_id');
		$lbolAcreditacion = $request->input('acreditacion');

		$lobjAcreditacion = new AcreditacionContrato($lintContratoId);
		$larrResult = $lobjAcreditacion::Accreditation($lbolAcreditacion);

		return $larrResult;

	}

	public function postCheckcontrollaboralcontrato( Request $request) {

		$lintContratoId = $request->input('contrato_id');
		$lbolControlLaboral = $request->input('controllaboral');
		if ($lbolControlLaboral=="true"){
			$lbolControlLaboral = 1;
		}else{
			$lbolControlLaboral = 0;
		}

		\DB::table('tbl_contrato')->where('contrato_id',$lintContratoId)
											->update(["controllaboral"=>$lbolControlLaboral]);

		return response()->json(array(
				'status'=>'success',
				'message'=> "Actualizado"
			));	 

	}
	public function getDescargaInforme(){
	
		$hoy = date('Y-m-d H:i:s');
		//dd('Content-Disposition: attachment;filename="Reporte_Acreditados'.$hoy.'.xlsx"');
		$datos = \DB::select('select a.Empresa as empresa, a.`Núm. Contrato` AS numContra, a.`Nombre Contrato` AS nombreContra, a.Acreditado AS acre, a.`Fecha Acreditación Persona` as fechaper, a.`Fecha Acreditación Acceso` AS fechaAc, a.RUT AS rut, a.Nombres AS nombre, a.Apellidos AS apellido  from vw_acreditados_para_cliente a');
			
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('Acreditados');
		$spreadsheet->setActiveSheetIndex(0);

		$spreadsheet->getActiveSheet()->getStyle('A1:G3')->getFont()->setBold(true);
		 $sheet->setCellValue('A1', 'Reporte de Acreditados');
		 $sheet->setCellValue('A2', 'Fecha del reporte: '.$hoy);
		// $sheet->setCellValue('C2', '['.date("Y-m-d", strtotime("$hoy -3 day")).']');
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
		$spreadsheet->getActiveSheet()->setAutoFilter('A4:I4');
		$sheet->setCellValue('A'.$j, 'Empresa');
		$sheet->setCellValue('B'.$j, 'Num contrato');
		$sheet->setCellValue('C'.$j, 'Nombre contrato');
		$sheet->setCellValue('D'.$j, 'Acreditado');
		$sheet->setCellValue('E'.$j, 'Fecha Acreditacion Persona');
		$sheet->setCellValue('F'.$j, 'Fecha Acreditacion Acceso');
		$sheet->setCellValue('G'.$j, 'RUT');
		$sheet->setCellValue('H'.$j, 'Nombres');
		$sheet->setCellValue('I'.$j, 'Apellidos');
		$j=5;
		foreach ($datos as $dato) {
			$sheet->setCellValue('A'.$j, strtoupper(strtolower($dato->empresa)));
			$sheet->setCellValue('B'.$j, strtoupper(strtolower($dato->numContra)));
			$sheet->setCellValue('C'.$j, strtoupper(strtolower($dato->nombreContra)));
			$sheet->setCellValue('D'.$j, strtoupper(strtolower($dato->acre)));
			$sheet->setCellValue('E'.$j, strtoupper(strtolower($dato->fechaper)));
			$sheet->setCellValue('F'.$j, strtoupper(strtolower($dato->fechaAc)));
			$sheet->setCellValue('G'.$j, strtoupper(strtolower($dato->rut)));
			$sheet->setCellValue('H'.$j, strtoupper(strtolower($dato->nombre)));
			$sheet->setCellValue('I'.$j, strtoupper(strtolower($dato->apellido)));
			$j++;
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Reporte_Acreditados_'.$hoy.'.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;

	}
	public function getCredencialTrabajador($id){

		$personas = new Personas();
		//obtengo los datos de la persona
		$persona = $personas->find($id);
  
		  if($persona->ArchivoFoto){ //si tiene archivo de foto habilito su foto de perfil
			  $profilePhoto = 'uploads/employee/'.$persona->ArchivoFoto;
		  }else{
			  if($persona->Sexo==1){ //pregunto para saber que sexo es y asi poder configurar la foto de perfil adecuada
				  $profilePhoto = 'images/userm.png';
			  }else{
				  $profilePhoto = 'images/userf.png';
			  }
		  }
		  $cliente = \DB::table('tbl_configuraciones')->where('nombre','CNF_APPNAME')->first();
		  switch ($cliente->Valor) {
			case 'Ohl Industrial':
				$logo = 'empresa-logo.jpg';
				break;
			default:
				$logo = 'logo-check.png';
			    break;
		}

		  //obtengo los datos necesarios para el credencial
		  $datosCredencial = DB::table('tbl_contratos_personas')
								->select('tbl_contrato.cont_nombre as contrato', 'tbl_contratistas.RazonSocial as contratista', 'tbl_roles.Descripción as cargo', 'tbl_centro.Descripcion as descripcion')
							  ->join('tbl_contrato','tbl_contratos_personas.contrato_id','=','tbl_contrato.contrato_id')
							  ->join('tbl_contratistas','tbl_contratos_personas.IdContratista','=','tbl_contratistas.IdContratista')
							  ->join('tbl_roles','tbl_contratos_personas.IdRol','=','tbl_roles.IdRol')
							  ->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
							  ->join('tbl_centro','tbl_contratos_centros.IdCentro','=','tbl_centro.IdCentro')
							  ->where('tbl_contratos_personas.IdPersona', $persona->IdPersona)
							  ->first();
				
       						
		  //genero el codigo qr del reut de la persona.
		  $qr= QrCode::size(165)->generate($persona->RUT);	
		  $size = 65;
				  $pdf = PDF::loadView('acreditacion.credencialTrabajador',array('logo' => $logo,'persona' => str_limit($persona->Nombres.' '.$persona->Apellidos,$size), 'contratista' => str_limit($datosCredencial->contratista, $size), 'contrato' => str_limit($datosCredencial->contrato,$size), 'qr' => $qr, 'cargo' => str_limit($datosCredencial->cargo,$size), 'profilePhoto' => $profilePhoto,'rut' => $persona->RUT, 'descripcion' => strtoupper($datosCredencial->descripcion)))
					  ->setOption('page-width', '200')
					   ->setOption('page-height', '400')
					  ->setOption('margin-top',0)
					  ->setOption('margin-bottom',0)
					  ->setOption('margin-left',0)
					  ->setOption('margin-right',0);
					  //muestro el pdf por pantalla
				  return $pdf->download('Credencial_'.$persona->RUT.'_'.$persona->Nombres.'_'.$persona->Apellidos.'.pdf');
				   
  
  
				  //return view('acreditacion.credencialTrabajador',array('persona' => str_limit($persona->Nombres.' '.$persona->Apellidos, $size), 'contratista' => str_limit($datosCredencial->contratista, $size), 'contrato' => strtoupper(str_limit($datosCredencial->contrato,$size)), 'qr' => $qr, 'cargo' => str_limit($datosCredencial->cargo,$size),'profilePhoto' => $profilePhoto, 'rut' => $persona->RUT));
  
	  }
	

}
