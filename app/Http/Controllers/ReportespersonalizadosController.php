<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Reportespersonalizados as Reportes;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PDF;


class ReportespersonalizadosController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'reportespersonalizados';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Reportes();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportespersonalizados',
			'pageUrl'=>  url('reportespersonalizados'),
			'return'	=> self::returnUrl()

		);
	

	}

	public function getIndex( Request $request )
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
				
		$this->data['access'] = $this->access;
		return view('reportespersonalizados.index',$this->data);
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

		$this->data['access']		= $this->access;
		return view('reportespersonalizados.form',$this->data);
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$this->data['access']		= $this->access;
		return view('reportespersonalizados.view',$this->data);
	}

	function postSave( Request $request)
	{


	}

	public function postDelete( Request $request)
	{

		if($this->access['is_remove'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

	}

	public function reporteEmpresas ( Request $request)
	{
		$lintIdUser = \Session::get('uid');
		$donwloadBy = \DB::table('tb_users')->where('id', $lintIdUser)->first();
		$hoy=date('Y-m-d H:i');
		$data = $this->model->reporteEmpresas()->where('cont_estado',1)->get();
	
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('REPORTE EMPRESAS');
		$spreadsheet->setActiveSheetIndex(0);
	
		$spreadsheet->getActiveSheet()->mergeCells('B3:C3');
		$spreadsheet->getActiveSheet()->mergeCells('B4:C4');
		$spreadsheet->getActiveSheet()->mergeCells('B5:C5');
		$spreadsheet->getActiveSheet()->getStyle('B3')->getFont()->setSize(14)->setBold(true);
		$sheet->getStyle('B3:C3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('B4:C4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('B5:C5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		
		$sheet->setCellValue('B3', 'REPORTE EMPRESAS');
		$sheet->setCellValue('B4', 'FECHA: '.$hoy);
		$sheet->setCellValue('B5', 'DESCARGADO POR: '.$donwloadBy->first_name.' '.$donwloadBy->last_name);
	
		$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		$drawing = self::ReportesLogo($drawing); //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('A2');
		$drawing->setHeight(70);
		$drawing->setResizeProportional(true);
		$drawing->setOffsetX(70);
		$drawing->setWorksheet($spreadsheet->getActiveSheet());
		
		$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		//$drawing->setPath(base_path('public/images/logo-check.png'));; //funcion que pone el logo segun el cliente
		$drawing->setPath(base_path('public/images/Logoscheck-05.png'));; //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('D2');
		$drawing->setHeight(70);
		$drawing->setOffsetX(70);
		$drawing->setResizeProportional(true);
		
		$drawing->setWorksheet($spreadsheet->getActiveSheet());
		$sheet->getStyle('A1:D6')->applyFromArray(self::Styles('header'));
	
		
		$spreadsheet->getActiveSheet()->getStyle('B10')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->getStyle('B10:F7')->getFont()->setBold(true);
		 
		$sheet->getColumnDimension('A')->setWidth(45);
		$sheet->getColumnDimension('B')->setWidth(30);
		$sheet->getColumnDimension('C')->setWidth(30);
		$sheet->getColumnDimension('D')->setWidth(45);
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('F')->setAutoSize(true);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$sheet->getColumnDimension('H')->setAutoSize(true);
		$sheet->getColumnDimension('I')->setAutoSize(true);
		$sheet->getColumnDimension('J')->setAutoSize(true);
		$sheet->getColumnDimension('K')->setAutoSize(true);
		$sheet->getColumnDimension('L')->setAutoSize(true);
		$sheet->getColumnDimension('M')->setAutoSize(true);
		$sheet->getColumnDimension('N')->setAutoSize(true);
		$sheet->getColumnDimension('O')->setAutoSize(true);

		$sheet->setCellValue('A10', 'UEN CCU');
		$sheet->setCellValue('B10', 'Zona');
		$sheet->setCellValue('C10', 'Ubicación');
		$sheet->setCellValue('D10', 'RUT Empresa');
		$sheet->setCellValue('E10', 'Razón Social');
		$sheet->setCellValue('F10', 'N° Contrato');
		$sheet->setCellValue('G10', 'Prioridad');
		$sheet->setCellValue('H10', 'Servicio');
		$sheet->setCellValue('I10', 'Clasificación');
		$sheet->setCellValue('J10', 'Responsable');
		$sheet->setCellValue('K10', 'Subgerencia RRLL');
		$sheet->setCellValue('L10', 'ADC CCU');
		$sheet->setCellValue('M10', 'ADC Contratista');
		$sheet->setCellValue('N10', 'Contacto ADC Contratista');
		$sheet->setCellValue('O10', 'Cant. Trabajadores');

		$spreadsheet->getActiveSheet()->getStyle('A10:O10')->applyFromArray(self::Styles('tablaHeader'));
		$j=11;
		foreach ($data as $key => $tabla) {
			$sheet->setCellValue('A'.$j, $tabla->UEN_CCU);
			$sheet->setCellValue('B'.$j, $tabla->Zona);
			$sheet->setCellValue('C'.$j, $tabla->Ubicación);
			$sheet->setCellValue('D'.$j, $tabla->RUT_Empresa);
			$sheet->setCellValue('E'.$j, $tabla->RazónSocial);
			$sheet->setCellValue('F'.$j, $tabla->N°Contrato);
			$sheet->setCellValue('G'.$j, $tabla->Prioridad);
			$sheet->setCellValue('H'.$j, $tabla->Servicio);
			$sheet->setCellValue('I'.$j, $tabla->Clasificación);
			$sheet->setCellValue('J'.$j, $tabla->Responsable);
			$sheet->setCellValue('K'.$j, $tabla->SubgerenciaRRLL);
			$sheet->setCellValue('L'.$j, $tabla->ADC_CCU);
			$sheet->setCellValue('M'.$j, $tabla->ADC_Contratista);
			$sheet->setCellValue('N'.$j, $tabla->Contacto_ACD_Contratista);
			$sheet->setCellValue('O'.$j, $tabla->Cant_Trabajadores);
			$j++;
		}
		$sheet->getStyle('A11:O'.($j-1))->applyFromArray(self::Styles('allBordes'));
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Reporte_Empresas.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;



	}
	public function reportePersonas ( Request $request)
	{
		$hoy=date('Y-m-d H:i');
		$lintIdUser = \Session::get('uid');
		$donwloadBy = \DB::table('tb_users')->where('id', $lintIdUser)->first();
		/*********** Data del reporte de personas *********/
		$data = $this->model->reportePersonas()->get();
		/***************  Tablas dinamicas ***************/
		$tabla1 = $this->model->reporteEmpresas()
						->where('cont_estado',1)
						->selectRaw('UEN_CCU, count(RUT_Empresa) as empresas, sum(Cant_Trabajadores) as trabajadores')
						->groupBy('UEN_CCU')
						->get();
		$tabla2 = $this->model->reporteEmpresas()
		->where('cont_estado',1)
		->selectRaw('UEN_CCU, count(RUT_Empresa) as empresas, sum(Cant_Trabajadores) as trabajadores, Servicio')
		->groupBy('UEN_CCU','Servicio')
		->orderBy('UEN_CCU')
		->get();

		$tabla3 = $this->model->reporteEmpresas()
		->where('cont_estado',1)
		->selectRaw('Prioridad, count(RUT_Empresa) as empresas, sum(Cant_Trabajadores) as trabajadores, Servicio')
		->groupBy('Prioridad')
		->get();

		$tabla4 = $this->model->reportePersonas()
		->selectRaw('Ubicación, Rol, count(RUT) as trabajadores, null as empresas')
		->groupBy('Rol')
		->orderBy('Ubicación')
		->get();
	
		$tabla5 = $this->model->reporteEmpresas()
						->where('cont_estado',1)
						->selectRaw('Ubicación, count(RUT_Empresa) as empresas, sum(Cant_Trabajadores) as trabajadores')
						->groupBy('Ubicación')
						->get();

		$tabla6 = $this->model->reporteEmpresas()
		->where('cont_estado',1)
		->selectRaw('UEN_CCU, RazónSocial, count(N°Contrato) as empresas, sum(Cant_Trabajadores) as trabajadores')
		->groupBy('N°Contrato')
		->orderBy('UEN_CCU')
		->get();
	


		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('REPORTE PERSONAS');
		$spreadsheet->setActiveSheetIndex(0);
	
		$spreadsheet->getActiveSheet()->mergeCells('B3:C3');
		$spreadsheet->getActiveSheet()->mergeCells('B4:C4');
		$spreadsheet->getActiveSheet()->mergeCells('B5:C5');
		$spreadsheet->getActiveSheet()->getStyle('B3')->getFont()->setSize(14)->setBold(true);
		$sheet->getStyle('B3:C3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('B4:C4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('B5:C5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		
		$sheet->setCellValue('B3', 'REPORTE PERSONAS');
		$sheet->setCellValue('B4', 'FECHA: '.$hoy);
		$sheet->setCellValue('B5', 'DESCARGADO POR: '.$donwloadBy->first_name.' '.$donwloadBy->last_name);
	
		$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		$drawing = self::ReportesLogo($drawing); //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('A2');
		$drawing->setHeight(70);
		$drawing->setResizeProportional(true);
		$drawing->setOffsetX(70);
		$drawing->setWorksheet($spreadsheet->getActiveSheet());
		
		$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		$drawing->setPath(base_path('public/images/Logoscheck-05.png'));; //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('D2');
		$drawing->setHeight(70);
		$drawing->setOffsetX(70);
		$drawing->setResizeProportional(true);
		
		$drawing->setWorksheet($spreadsheet->getActiveSheet());
		$sheet->getStyle('A1:D6')->applyFromArray(self::Styles('header'));
	
		
		$spreadsheet->getActiveSheet()->getStyle('B10')->getFont()->setBold(true);
		$spreadsheet->getActiveSheet()->getStyle('B10:F7')->getFont()->setBold(true);
		 
		$sheet->getColumnDimension('A')->setWidth(45);
		$sheet->getColumnDimension('B')->setWidth(30);
		$sheet->getColumnDimension('C')->setWidth(30);
		$sheet->getColumnDimension('D')->setWidth(45);
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('F')->setAutoSize(true);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$sheet->getColumnDimension('H')->setAutoSize(true);
		$sheet->getColumnDimension('I')->setAutoSize(true);
		$sheet->getColumnDimension('J')->setAutoSize(true);
		$sheet->getColumnDimension('K')->setAutoSize(true);
		$sheet->getColumnDimension('L')->setAutoSize(true);
		$sheet->getColumnDimension('M')->setAutoSize(true);
		$sheet->getColumnDimension('N')->setAutoSize(true);
		$sheet->getColumnDimension('O')->setAutoSize(true);
		$sheet->getColumnDimension('P')->setAutoSize(true);
		$sheet->getColumnDimension('Q')->setAutoSize(true);

		$sheet->setCellValue('A10', 'UEN');
		$sheet->setCellValue('B10', 'Ubicación');
		$sheet->setCellValue('C10', 'Servicio');
		$sheet->setCellValue('D10', 'Prioridad');
		$sheet->setCellValue('E10', 'RUT Empresa');
		$sheet->setCellValue('F10', 'Razón Social');
		$sheet->setCellValue('G10', 'Núm. Contrato');
		$sheet->setCellValue('H10', 'RUT');
		$sheet->setCellValue('I10', 'Nombres');
		$sheet->setCellValue('J10', 'Apellidos');
		$sheet->setCellValue('K10', 'Rol');
		$sheet->setCellValue('L10', 'Fecha Nacimiento');
		$sheet->setCellValue('M10', 'Discapacitado');
		$sheet->setCellValue('N10', 'Pensionado');
		$sheet->setCellValue('O10', 'Monitoreo Mensual Subcontratación');
		$sheet->setCellValue('P10', 'Cargo en contrato');
		$sheet->setCellValue('Q10', 'Contrato Comercial Contratista');

		$spreadsheet->getActiveSheet()->getStyle('A10:Q10')->applyFromArray(self::Styles('tablaHeader'));
		$j=11;
	
		foreach ($data as $key => $tabla) {
			$sheet->setCellValue('A'.$j, $tabla->UEN);
			$sheet->setCellValue('B'.$j, $tabla->Ubicación);
			$sheet->setCellValue('C'.$j, $tabla->Servicio);
			$sheet->setCellValue('D'.$j, $tabla->Prioridad);
			$sheet->setCellValue('E'.$j, $tabla->RUT_Empresa);
			$sheet->setCellValue('F'.$j, $tabla->Razón_Social);
			$sheet->setCellValue('G'.$j, $tabla->Núm_Contrato);
			$sheet->setCellValue('H'.$j, $tabla->RUT);
			$sheet->setCellValue('I'.$j, $tabla->Nombres);
			$sheet->setCellValue('J'.$j, $tabla->Apellidos);
			$sheet->setCellValue('K'.$j, $tabla->Rol);
			$sheet->setCellValue('L'.$j, $tabla->Fecha_Nacimiento);
			$sheet->setCellValue('M'.$j, $tabla->Discapacitado);
			$sheet->setCellValue('N'.$j, $tabla->Pensionado);
			$sheet->setCellValue('O'.$j, $tabla->Monitoreo_Mensual_Subcontratación);
			$sheet->setCellValue('P'.$j, $tabla->Cargo_en_contrato);
			$sheet->setCellValue('Q'.$j, $tabla->Contrato_Comercial_Contratista);
			$j++;
		}
		$sheet->getStyle('A11:Q'.($j-1))->applyFromArray(self::Styles('allBordes'));

		/*****************  Tablas ******************/ 
		// Create a new worksheet, after the default sheet
		$spreadsheet->createSheet();

		// Add some data to the second sheet, resembling some different data types
		$spreadsheet->setActiveSheetIndex(1);
		// Rename 2nd sheet
		$spreadsheet->getActiveSheet()->setTitle('TABLAS');
		$sheet = $spreadsheet->getActiveSheet();

		$spreadsheet->getActiveSheet()->mergeCells('B3:D3');
		$spreadsheet->getActiveSheet()->mergeCells('B4:D4');
		$spreadsheet->getActiveSheet()->mergeCells('B5:D5');
		$spreadsheet->getActiveSheet()->getStyle('B3')->getFont()->setSize(14)->setBold(true);
		$sheet->getStyle('B3:D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('B4:D4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('B5:D5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		
		$sheet->setCellValue('B3', 'TABLAS');
		$sheet->setCellValue('B4', 'FECHA: '.$hoy);
		$sheet->setCellValue('B5', 'DESCARGADO POR: '.$donwloadBy->first_name.' '.$donwloadBy->last_name);
	
		$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		$drawing = self::ReportesLogo($drawing); //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('A2');
		$drawing->setHeight(70);
		$drawing->setResizeProportional(true);
		$drawing->setOffsetX(70);
		$drawing->setWorksheet($spreadsheet->getActiveSheet());
		
		$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		$drawing->setPath(base_path('public/images/Logoscheck-05.png'));; //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('E2');
		$drawing->setHeight(70);
		$drawing->setOffsetX(70);
		$drawing->setResizeProportional(true);
		
		$drawing->setWorksheet($spreadsheet->getActiveSheet());
		$sheet->getStyle('A1:E6')->applyFromArray(self::Styles('header'));

		$j = 10;
		$jInicial = $j;

		//TABLA 1 
		$sheet->getColumnDimension('A')->setAutoSize(true);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->setCellValue('A'.$j, 'UEN');
		$sheet->setCellValue('B'.$j, '# EMPRESAS');
		$sheet->setCellValue('C'.$j, '# TRABAJADORES');
		$spreadsheet->getActiveSheet()->getStyle('A'.$j.':C'.$j)->applyFromArray(self::Styles('tablaHeader'));
		$j++;
		$totalTrabajadores = 0;
		$totalEmpresas = 0;
		foreach ($tabla1 as $key => $tabla) {
			$sheet->setCellValue('A'.$j, strtoupper($tabla->UEN_CCU));
			$sheet->setCellValue('B'.$j, $tabla->empresas);
			$sheet->setCellValue('C'.$j, $tabla->trabajadores);
			$totalTrabajadores = $totalTrabajadores + $tabla->trabajadores;
			$totalEmpresas = $totalEmpresas + $tabla->empresas;
			$j++;
		}
		$sheet->setCellValue('A'.$j, 'TOTAL GENERAL');
		$sheet->setCellValue('B'.$j, $totalEmpresas);
		$sheet->setCellValue('C'.$j, $totalTrabajadores);
		$sheet->getStyle('A'.$jInicial.':C'.$j)->applyFromArray(self::Styles('allBordes'));

		//Tabla 2
		$sheet->getColumnDimension('A')->setAutoSize(true);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$j= $j+2;
		$jInicial = $j;
		$sheet->setCellValue('A'.$j, 'SERVICIOS');
		$sheet->setCellValue('B'.$j, '# EMPRESAS');
		$sheet->setCellValue('C'.$j, '# TRABAJADORES');
		$spreadsheet->getActiveSheet()->getStyle('A'.$j.':C'.$j)->applyFromArray(self::Styles('tablaHeader'));
		
		$totalTrabajadores = 0;
		$totalEmpresas = 0;
		$uenAnterior = null;
		$j++;
		foreach ($tabla2 as $key => $tabla) {
			if($uenAnterior != $tabla->UEN_CCU){
				$sheet->setCellValue('A'.$j, strtoupper($tabla->UEN_CCU));
				$uenAnterior = $tabla->UEN_CCU;
				$sheet->getStyle('A'.$j.':C'.$j)->applyFromArray(self::Styles('boldSize'));
				$j++;
			}
			$sheet->setCellValue('A'.$j, $tabla->Servicio);
			$sheet->setCellValue('B'.$j, $tabla->empresas);
			$sheet->setCellValue('C'.$j, $tabla->trabajadores);
			$totalTrabajadores = $totalTrabajadores + $tabla->trabajadores;
			$totalEmpresas = $totalEmpresas + $tabla->empresas;
			$j++;
		}
		$sheet->setCellValue('A'.$j, 'TOTAL GENERAL');
		$sheet->setCellValue('B'.$j, $totalEmpresas);
		$sheet->setCellValue('C'.$j, $totalTrabajadores);
		$sheet->getStyle('A'.$jInicial.':C'.$j)->applyFromArray(self::Styles('allBordes'));

		//TABLA 3
		$j=$j+2;
		$jInicial = $j;
		$sheet->getColumnDimension('A')->setAutoSize(true);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->setCellValue('A'.$j, 'PRIORIDAD');
		$sheet->setCellValue('B'.$j, '# EMPRESAS');
		$sheet->setCellValue('C'.$j, '# TRABAJADORES');
		$spreadsheet->getActiveSheet()->getStyle('A'.$j.':C'.$j)->applyFromArray(self::Styles('tablaHeader'));
		$j++;
		$totalTrabajadores = 0;
		$totalEmpresas = 0;
		foreach ($tabla3 as $key => $tabla) {
			$sheet->setCellValue('A'.$j, strtoupper($tabla->Prioridad));
			$sheet->setCellValue('B'.$j, $tabla->empresas);
			$sheet->setCellValue('C'.$j, $tabla->trabajadores);
			$totalTrabajadores = $totalTrabajadores + $tabla->trabajadores;
			$totalEmpresas = $totalEmpresas + $tabla->empresas;
			$j++;
		}
		$sheet->setCellValue('A'.$j, 'TOTAL GENERAL');
		$sheet->setCellValue('B'.$j, $totalEmpresas);
		$sheet->setCellValue('C'.$j, $totalTrabajadores);
		$sheet->getStyle('A'.$jInicial.':C'.$j)->applyFromArray(self::Styles('allBordes'));

		//TABLA 4
		
		$sheet->getColumnDimension('A')->setAutoSize(true);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$j= $j+2;
		$jInicial = $j;
		$sheet->setCellValue('A'.$j, 'ROLES');
		$sheet->setCellValue('B'.$j, '# EMPRESAS');
		$sheet->setCellValue('C'.$j, '# TRABAJADORES');
		$spreadsheet->getActiveSheet()->getStyle('A'.$j.':C'.$j)->applyFromArray(self::Styles('tablaHeader'));
		
		$totalTrabajadores = 0;
		$totalEmpresas = 0;
		$uenAnterior = null;
		$j++;
		foreach ($tabla4 as $key => $tabla) {
			if($uenAnterior != $tabla->Ubicación){
				$sheet->setCellValue('A'.$j, strtoupper($tabla->Ubicación));
				$uenAnterior = $tabla->Ubicación;
				$sheet->getStyle('A'.$j.':C'.$j)->applyFromArray(self::Styles('boldSize'));
				$j++;
			}
			
			$sheet->setCellValue('A'.$j, strtoupper($tabla->Rol));
			$numTrab = $this->model->reportePersonas()
			->where('Rol', $tabla->Rol)
			->selectRaw('COUNT(RUT_Empresa)')
			->groupBy('RUT_Empresa')
			->get();
			$empresas = 0;
			foreach ($numTrab as $key => $num) {
				$empresas++;
			}

			$sheet->setCellValue('B'.$j, $empresas);
			$sheet->setCellValue('C'.$j, $tabla->trabajadores);
			$totalTrabajadores = $totalTrabajadores + $tabla->trabajadores;
			$totalEmpresas = $totalEmpresas + $empresas;
			$j++;
		}
		$sheet->setCellValue('A'.$j, 'TOTAL GENERAL');
		$sheet->setCellValue('B'.$j, $totalEmpresas);
		$sheet->setCellValue('C'.$j, $totalTrabajadores);
		$sheet->getStyle('A'.$jInicial.':C'.$j)->applyFromArray(self::Styles('allBordes'));
		
		//TABLA 5
		$j=10;
		$jInicial = $j;
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('F')->setAutoSize(true);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$sheet->setCellValue('E'.$j, 'FAENAS');
		$sheet->setCellValue('F'.$j, '# EMPRESAS');
		$sheet->setCellValue('G'.$j, '# TRABAJADORES');
		$spreadsheet->getActiveSheet()->getStyle('E'.$j.':G'.$j)->applyFromArray(self::Styles('tablaHeader'));
		$j++;
		$totalTrabajadores = 0;
		$totalEmpresas = 0;
		foreach ($tabla5 as $key => $tabla) {
			$sheet->setCellValue('E'.$j, strtoupper($tabla->Ubicación));
			$sheet->setCellValue('F'.$j, $tabla->empresas);
			$sheet->setCellValue('G'.$j, $tabla->trabajadores);
			$totalTrabajadores = $totalTrabajadores + $tabla->trabajadores;
			$totalEmpresas = $totalEmpresas + $tabla->empresas;
			$j++;
		}
		$sheet->setCellValue('E'.$j, 'TOTAL GENERAL');
		$sheet->setCellValue('F'.$j, $totalEmpresas);
		$sheet->setCellValue('G'.$j, $totalTrabajadores);
		$sheet->getStyle('E'.$jInicial.':G'.$j)->applyFromArray(self::Styles('allBordes'));
		
		//Tabla 6
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('F')->setAutoSize(true);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$j= $j+2;
		$jInicial = $j;
		$sheet->setCellValue('E'.$j, 'CONTRATISTAS');
		$sheet->setCellValue('F'.$j, '# CONTRATO');
		$sheet->setCellValue('G'.$j, '# TRABAJADORES');
		$spreadsheet->getActiveSheet()->getStyle('E'.$j.':G'.$j)->applyFromArray(self::Styles('tablaHeader'));
		
		$totalTrabajadores = 0;
		$totalEmpresas = 0;
		$uenAnterior = null;
		$j++;
		foreach ($tabla6 as $key => $tabla) {
			if($uenAnterior != $tabla->UEN_CCU){
				$sheet->setCellValue('E'.$j, strtoupper($tabla->UEN_CCU));
				$uenAnterior = $tabla->UEN_CCU;
				$sheet->getStyle('E'.$j.':G'.$j)->applyFromArray(self::Styles('boldSize'));
				$j++;
			}
			$sheet->setCellValue('E'.$j, $tabla->RazónSocial);
			$sheet->setCellValue('F'.$j, $tabla->empresas);
			$sheet->setCellValue('G'.$j, $tabla->trabajadores);
			$totalTrabajadores = $totalTrabajadores + $tabla->trabajadores;
			$totalEmpresas = $totalEmpresas + $tabla->empresas;
			$j++;
		}
		$sheet->setCellValue('E'.$j, 'TOTAL GENERAL');
		$sheet->setCellValue('F'.$j, $totalEmpresas);
		$sheet->setCellValue('G'.$j, $totalTrabajadores);
		$sheet->getStyle('E'.$jInicial.':G'.$j)->applyFromArray(self::Styles('allBordes'));

		// cambio de hoja
		$spreadsheet->setActiveSheetIndex(0);

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Reporte_Personas.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;


		



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

	public function Styles($style){

		switch ($style) {
			case 'allBordes':
				$allBordes = [
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
							'color' => ['rgb' => '1E3246'],
						],
					],
				];
				return $allBordes;
				break;
					
			case 'header':
				$header = [
					'borders' => [
						'top' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
							'color' => ['rgb' => '1E3246'],
						],
						'bottom' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
							'color' => ['rgb' => '1E3246'],
						],
						'right' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
							'color' => ['rgb' => '1E3246'],
						],
						'left' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
							'color' => ['rgb' => '1E3246'],
						],
					],
				];
				return $header;
				break;
			case 'tablaHeader':
				$tablaHeader = [
					'font' => [
						'bold' => true,
						'size' => 14,
						'color' => array('rgb' => 'FFFFFF'),
					],
					'fill' => [
						'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
						'color' => array('rgb' => '1E3246')
					],
				];
				return $tablaHeader;
				break;

			case 'boldSize':
				$boldSize = [
					'font' => [
						'bold' => true,
						'size' => 14,
						'color' => array('rgb' => '000000'),
					],
				];
				return $boldSize;
				break;
		}
		
		

		
		
	}

	


}
