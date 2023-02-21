<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Digitaliquidacion;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DB;


class DigitaliquidacionController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'digitaliquidacion';
	static $per_page	= '10';

	public function __construct()
	{

		parent::__construct();
		$this->model = new Digitaliquidacion();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'digitaliquidacion',
			'return'	=> self::returnUrl()

		);
		\App::setLocale(CNF_LANG);
		if (defined('CNF_MULTILANG') && CNF_MULTILANG == '1') {

		$lang = (\Session::get('lang') != "" ? \Session::get('lang') : CNF_LANG);
		\App::setLocale($lang);
		}


	}

	public function getIndex( Request $request )
	{

		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

		$sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
		$this->data['sitio']=$sitio->Valor;

		return view('digitaliquidacion.index',$this->data);
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
		return view('digitaliquidacion.form',$this->data);
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$this->data['access']		= $this->access;
		return view('digitaliquidacion.view',$this->data);
	}

	function postSave( Request $request)
	{


	}

	public function postUpload(Request $request){
		$this->validate($request, [
        'file_excel'  => 'required|file',
				'ano' => 'required|integer|min:2019',
				'mes' => 'required|integer|min:1|max:12'
      ]);

      $path = $request->file('file_excel')->getRealPath();
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

			$spreadsheet = $reader->load($path);
			$sheetData = $spreadsheet->getActiveSheet()->toArray();

			$mes=$request->mes;
			$ano=$request->ano;

			if (!empty($sheetData)) {
				DB::table('tbl_liquidaciones_digitadas')->truncate();
          for ($i=1; $i<count($sheetData[1])-1; $i++) {
						for($j=1;$j<13;$j++){
							$data[$j] = $sheetData[$j][$i+1];
						}
						if(isset($data[1])){
								DB::table('tbl_liquidaciones_digitadas')->insertGetId(
									[
										'mes'=>$mes,
										'ano'=>$ano,
										'cont_numero'=>$data[1],
										'RUT'=>$data[2],
										'diasTrab'=>$data[3],
										'sueldoBase'=>$data[4],
										'gratif'=>$data[5],
										'nHe50'=>$data[6],
										'pHe50'=>$data[7],
										'nHe100'=>$data[8],
										'pHe100'=>$data[9],
										'totalImponible'=>$data[10],
										'totalHaberes'=>$data[11],
										'totalLiquido'=>$data[12]
									]);
						}
          }
      }

			self::generaReporte($ano,$mes);

		return back();

	}

	public function generaReporte($ano,$mes){
		$spreadsheet = new Spreadsheet();
		$spreadsheet->getActiveSheet()->setTitle('Reporte Liquidaciones');
		$spreadsheet->setActiveSheetIndex(0);
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
		$spreadsheet->getDefaultStyle()->getFont()->setSize(8);

		$spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(20);
		$spreadsheet->getActiveSheet()->getRowDimension('2')->setRowHeight(20);
		$j=1;
		$spreadsheet->getActiveSheet()->getStyle('G1:X1')->getFont()->setSize(8)->setBold(true);
		$sheet->setCellValue('L'.$j, 'Periodo');
		$sheet->setCellValue('M'.$j, $mes."/".$ano);
		$sheet->mergeCells('N1:V1');
		$sheet->getStyle('N1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
		$sheet->getStyle('N1')->getFill()->getStartColor()->setARGB('FFFFC000');
		$sheet->getStyle('N1')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
		$sheet->getStyle('N1')->getAlignment()->setHorizontal('center');
		$sheet->setCellValue('N'.$j, 'IMPONIBLE');
		$sheet->mergeCells('W1:AA1');
		$sheet->getStyle('W1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
		$sheet->getStyle('W1')->getFill()->getStartColor()->setARGB('FF4473C4');
		$sheet->getStyle('W1')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
		$sheet->getStyle('W1')->getAlignment()->setHorizontal('center');
		$sheet->setCellValue('W'.$j, 'DESCUENTOS');
		$sheet->getStyle('AB1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
		$sheet->getStyle('AB1')->getFill()->getStartColor()->setARGB('FF009900');
		$sheet->getStyle('AB1')->getAlignment()->setHorizontal('center');
		$sheet->mergeCells('AB1:AE1');
		$sheet->setCellValue('AB'.$j, 'TOTALES');
		$j=2;
		$spreadsheet->getActiveSheet()->getStyle('A2:AE2')->getFont()->setSize(8)->setBold(true);
		$sheet->setCellValue('A'.$j, 'N° Contrato');
		$sheet->setCellValue('B'.$j, 'Faena');
		$sheet->setCellValue('C'.$j, 'Razón Social');
		$sheet->setCellValue('D'.$j, 'RUT Empresa');
		$sheet->setCellValue('E'.$j, 'RUT Persona');
		$sheet->setCellValue('F'.$j, 'Nombre');
		$sheet->setCellValue('G'.$j, 'Sexo');
		$sheet->setCellValue('H'.$j, 'Nacionalidad');
		$sheet->setCellValue('I'.$j, 'Fecha Nacimiento');
		$sheet->setCellValue('J'.$j, 'Estatus');
		$sheet->setCellValue('K'.$j, 'Fecha Contratación');
		$sheet->setCellValue('L'.$j, 'Fecha Inicio Faena');
		$sheet->setCellValue('M'.$j, 'Fecha Fin Faena');
		$sheet->setCellValue('N'.$j, 'Sueldo base en contrato');
		$sheet->setCellValue('O'.$j, '# Días Trab.');
		$sheet->setCellValue('P'.$j, 'Sueldo base días trabajados');
		$sheet->setCellValue('Q'.$j, 'Gratif.');
		$sheet->setCellValue('R'.$j, '#HE 50%');
		$sheet->setCellValue('S'.$j, '$HE 50%');
		$sheet->setCellValue('T'.$j, '#HE 100%');
		$sheet->setCellValue('U'.$j, '$HE 100%');
		$sheet->setCellValue('V'.$j, 'Otros imponible');
		$sheet->setCellValue('W'.$j, 'AFP');
		$sheet->setCellValue('X'.$j, 'Salud');
		$sheet->setCellValue('Y'.$j, 'AFC');
		$sheet->setCellValue('Z'.$j, 'Otros Descuentos');
		$sheet->setCellValue('AA'.$j, 'Total Descuentos');
		$sheet->setCellValue('AB'.$j, 'Total Imponible');
		$sheet->setCellValue('AC'.$j, 'Total Otros No Imponible');
		$sheet->setCellValue('AD'.$j, 'TOTAL HABERES');
		$sheet->setCellValue('AE'.$j, 'TOTAL LIQUIDO');

		//$datas = \DB::table('tbl_liquidaciones_digitadas')->where('mes',$mes)->where('ano',$ano)->get();

		$infoContratoContratista = \DB::table('tbl_contrato')
									->join('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
									->join('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_contrato.contrato_id')
									->join('tbl_centro','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro');

		$objFaenas = $infoContratoContratista->select('tbl_centro.Descripcion','tbl_contrato.cont_numero','tbl_contratistas.RazonSocial','tbl_contratistas.RUT')->get();

		$j=3;
		if($mes<10){
			$mes="0".$mes;
		}

		$pmm = \DB::table('tbl_personas_maestro_movil')
			->leftJoin('tbl_personas','tbl_personas_maestro_movil.idpersona','=','tbl_personas.IdPersona')
			->leftJoin('tbl_nacionalidad','tbl_nacionalidad.id_Nac','=','tbl_personas.id_Nac')
			->leftJoin('tbl_liquidaciones_digitadas','tbl_liquidaciones_digitadas.RUT','=','tbl_personas.RUT')
			->leftJoin('tbl_contrato','tbl_contrato.contrato_id','=','tbl_personas_maestro_movil.contrato_id')
			->where('tbl_personas_maestro_movil.periodo',"$ano-$mes-01")
			->select('tbl_personas.RUT','tbl_personas.Nombres','tbl_personas.Apellidos','nacionalidad','tbl_personas.Sexo','FechaNacimiento','tbl_contrato.cont_numero','tbl_personas_maestro_movil.Estatus','tbl_personas_maestro_movil.FechaInicioFaena','FechaFinFaena','diasTrab','sueldoBase','gratif','nHe50','pHe50','nHe100','pHe100','totalImponible','totalHaberes','totalLiquido')
			->get();

		foreach ($pmm as $data) {
			/*
			$objPersona = \DB::table('tbl_personas')
			->join('tbl_nacionalidad','tbl_nacionalidad.id_Nac','=','tbl_personas.id_Nac')
			->join('tbl_personas_maestro_movil','tbl_personas_maestro_movil.idpersona','=','tbl_personas.IdPersona')
			->where('RUT',$data->RUT)
			->whereIn('Estatus',['Vigente','Baja Observada'])
			->where('periodo',"$ano-$mes-01")
			->first();
			*/
				$spreadsheet->getActiveSheet()->getStyle("A$j:X$j")->getFont()->setSize(8);
				$sheet->setCellValue('A'.$j, strtoupper(strtolower($data->cont_numero)));
				foreach ($objFaenas as $faena) {
					if($faena->cont_numero==$data->cont_numero) $sheet->setCellValue('B'.$j, strtoupper(strtolower($faena->Descripcion)));
					if($faena->cont_numero==$data->cont_numero) $sheet->setCellValue('C'.$j, strtoupper(strtolower($faena->RazonSocial)));
					if($faena->cont_numero==$data->cont_numero) $sheet->setCellValue('D'.$j, strtoupper(strtolower($faena->RUT)));
				}
				$sheet->setCellValue('E'.$j, strtoupper(strtolower($data->RUT)));
				$sheet->setCellValue('F'.$j, strtoupper(strtolower($data->Nombres." ".$data->Apellidos)));
				if($data->Sexo==1){
					$sexo='Masculino';
				}else{
					$sexo='Femenino';
				}
				$sheet->setCellValue('G'.$j, strtoupper(strtolower($sexo)));
				if(isset($data->nacionalidad))$sheet->setCellValue('H'.$j, strtoupper(strtolower($data->nacionalidad)));
				if(isset($data->FechaNacimiento))$sheet->setCellValue('I'.$j, strtoupper(strtolower($data->FechaNacimiento)));
				if(isset($data->Estatus))$sheet->setCellValue('J'.$j, strtoupper(strtolower($data->Estatus)));

				$tipoDocumentovalor = \DB::table('tbl_tipo_documento_valor')->where('Etiqueta','Like','Fecha Contratación')->first();
				$documento = \DB::table('tbl_documentos')
					->join('tbl_contrato','tbl_documentos.contrato_id','=','tbl_contrato.contrato_id')
					->join('tbl_documento_valor','tbl_documento_valor.IdDocumento','=','tbl_documentos.IdDocumento')
					->where('tbl_contrato.cont_numero',$data->cont_numero)
					->where('tbl_documentos.IdTipoDocumento',$tipoDocumentovalor->IdTipoDocumento)
					->where('tbl_documento_valor.IdTipoDocumentoValor',$tipoDocumentovalor->IdTipoDocumentoValor)
					->first();
				if($documento){
						$sheet->setCellValue('K'.$j, strtoupper(strtolower($documento->Valor)));
				}else{
					$sheet->setCellValue('K'.$j, '-');
				}
				$sheet->setCellValue('L'.$j, strtoupper(strtolower($data->FechaInicioFaena)));
				$sheet->setCellValue('M'.$j, strtoupper(strtolower($data->FechaFinFaena)));

				$sheet->setCellValue('N'.$j, 0);
				$sheet->setCellValue('O'.$j, $data->diasTrab);
				$sheet->setCellValue('P'.$j, $data->sueldoBase);
				$sheet->getStyle('P'.$j)->getNumberFormat()->setFormatCode('$#,##0');
				$sheet->setCellValue('Q'.$j, $data->gratif);
				$sheet->getStyle('Q'.$j)->getNumberFormat()->setFormatCode('$#,##0');
				$sheet->setCellValue('R'.$j, $data->nHe50);
				$sheet->setCellValue('S'.$j, $data->pHe50);
				$sheet->getStyle('S'.$j)->getNumberFormat()->setFormatCode('$#,##0');
				$sheet->setCellValue('T'.$j, $data->nHe100);
				$sheet->setCellValue('U'.$j, $data->pHe100);
				$sheet->setCellValue('V'.$j, $data->totalImponible-($data->sueldoBase+$data->gratif+$data->pHe50+$data->pHe100));

				if(isset($data->RUT)){
					if(strpos($data->RUT,'-')){
						$rutTmp = explode( "-", $data->RUT );
						$rutPuntos=number_format( $rutTmp[0], 0, "", ".") . '-' . $rutTmp[1];
					}else{
						$rutPuntos=$data->RUT;
					}

					$afpEmpresa = \DB::table('afp_trabajador')
						->join('afp_empresa','afp_trabajador.afp_empresa_id','=','afp_empresa.id')
						->where('afp_empresa.periodo',"$mes/$ano")
						->where('afp_trabajador.rut',$rutPuntos)
						->whereNotExists(function($q){
							$q->select(\DB::raw("1"))
								->from('caja_trabajador')
								->where('caja_trabajador.rut','afp_trabajador.rut')
								->where('caja_trabajador.cotizacion','afp_trabajador.cotizacion_obligatoria');
						})->first();

						if($afpEmpresa) {
							$afp = (int)str_replace('.','',$afpEmpresa->cotizacion_obligatoria);
							$afc = (int)(str_replace('.','',$afpEmpresa->cotizacion_afiliado));
						}else{
							$afp=0;
							$afc=0;
						}
						$sheet->setCellValue('W'.$j, $afp);

						$fonasaEmpresa = \DB::table('fonasa_trabajador')
							->join('fonasa_empresa','fonasa_trabajador.fonasa_empresa_id','=','fonasa_empresa.id')
							->where('fonasa_empresa.periodo',"$mes $ano")
							->where('fonasa_trabajador.rut',$rutPuntos)
							->first();

							$isapreEmpresa = \DB::table('isapre_trabajador')
								->join('isapre_empresa','isapre_trabajador.isapre_empresa_id','=','isapre_empresa.id')
								->where('isapre_empresa.periodo',"$mes $ano")
								->where('isapre_trabajador.rut',$rutPuntos)
								->first();

					if($fonasaEmpresa){
						$salud = (int)str_replace('.','',$fonasaEmpresa->cotizacion);
					}elseif($isapreEmpresa) {
						$salud = (int)str_replace('.','',$isapreEmpresa->cotizacion);
					}else{
						$salud = 0;
					}

					$sheet->getStyle("U$j:AE$j")->getNumberFormat()->setFormatCode('$#,##0');
					$sheet->setCellValue('X'.$j, $salud);
					$sheet->setCellValue('Y'.$j, $afc);
					$sheet->setCellValue('Z'.$j, $data->totalHaberes-$data->totalLiquido-($afp+$salud+$afc));
					$sheet->setCellValue('AA'.$j, $data->totalHaberes-$data->totalLiquido);
					$sheet->setCellValue('AB'.$j, $data->totalImponible);
					$sheet->setCellValue('AC'.$j, $data->totalHaberes-$data->totalImponible);
					$sheet->setCellValue('AD'.$j, $data->totalHaberes);
					$sheet->setCellValue('AE'.$j, $data->totalLiquido);
				}
				$j++;

		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Reporte_Liquidaciones.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;
	}

	public function postDelete( Request $request)
	{

		if($this->access['is_remove'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

	}

	public function getDatas(Request $request)
  {
		$sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();


		$data = \DB::table('tbl_personas_maestro_movil')
				->leftJoin('tbl_personas','tbl_personas.idpersona','=','tbl_personas_maestro_movil.idpersona')
				->leftJoin('tbl_contrato','tbl_contrato.contrato_id','=','tbl_personas_maestro_movil.contrato_id')
				->leftJoin('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_personas_maestro_movil.idcontratista')
				->leftJoin('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_personas_maestro_movil.contrato_id')
				->leftJoin('tbl_centro','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
				->leftJoin('tbl_nacionalidad','tbl_nacionalidad.id_Nac','=','tbl_personas.id_Nac')
				->leftJoin('tbl_contrato_estatus','tbl_contrato_estatus.id','=','tbl_contrato.cont_estado')
				->leftJoin('tbl_sexo','tbl_sexo.id_sexo','=','tbl_personas.Sexo');

		if($sitio->Valor=='Abastible'){
			$data = $data	->leftJoin('tbl_trab_dueno','tbl_trab_dueno.IdPersona','=','tbl_personas_maestro_movil.idpersona')
										->leftJoin('tbl_pensionados','tbl_pensionados.IdPersona','=','tbl_personas_maestro_movil.idpersona')
										->leftJoin('tbl_documentos', function ($join){
											$join->on('tbl_documentos.IdEntidad','=','tbl_personas_maestro_movil.idpersona');
											$join->on('tbl_documentos.IdTipoDocumento','=',DB::raw(96));
											$join->on('tbl_documentos.contrato_id','=','tbl_personas_maestro_movil.contrato_id');
										})
										->leftJoin('tbl_documento_valor', function($join){
											$join->on('tbl_documento_valor.IdDocumento', '=','tbl_documentos.IdDocumento');
											$join->on('tbl_documento_valor.IdTipoDocumentoValor','=',DB::raw(177));
										})
										->select(
											[
												'tbl_personas_maestro_movil.id',
												'periodo',
												'tbl_personas_maestro_movil.idpersona',
												'tbl_personas_maestro_movil.contrato_id',
												'estatus',
												'FechaEfectiva',
												DB::raw("tbl_personas.RUT as RUTPersona"),
												'tbl_contrato.cont_numero',
												'tbl_personas_maestro_movil.FechaInicioFaena',
												'tbl_personas_maestro_movil.FechaFinFaena',
												'tbl_personas.FechaNacimiento',
												'tbl_contratistas.IdContratista',
												DB::raw("tbl_contratistas.RUT as RUTContratista"),
												'tbl_contratistas.RazonSocial',
												'tbl_centro.Descripcion',
												'tbl_personas.Nombres',
												'tbl_personas.Apellidos',
												'tbl_sexo.tipo',
												'tbl_nacionalidad.nacionalidad',
												DB::raw("if(tbl_pensionados.id_pens is null,'no','si') as pensionado"),
												DB::raw("if(tbl_trab_dueno.id_dueno is null,'no','si') as dueno"),
												'tbl_documento_valor.Valor',
												'tbl_contrato_estatus.nombre as cont_estado'
											])
										->groupBy('tbl_personas_maestro_movil.id')
										->where('tbl_contrato.ContratoPrueba','<>',1)
										->orderBy('id','desc');
		}else{
			$data = $data->select(
				[
					'tbl_personas_maestro_movil.id',
					'periodo',
					'tbl_personas_maestro_movil.idpersona',
					'tbl_personas_maestro_movil.contrato_id',
					'estatus',
					'FechaEfectiva',
					DB::raw("tbl_personas.RUT as RUTPersona"),
					'tbl_contrato.cont_numero',
					'tbl_personas_maestro_movil.FechaInicioFaena',
					'tbl_personas_maestro_movil.FechaFinFaena',
					'tbl_personas.FechaNacimiento',
					'tbl_contratistas.IdContratista',
					DB::raw("tbl_contratistas.RUT as RUTContratista"),
					'tbl_contratistas.RazonSocial',
					'tbl_centro.Descripcion',
					'tbl_personas.Nombres',
					'tbl_personas.Apellidos',
					'tbl_sexo.tipo',
					'tbl_nacionalidad.nacionalidad',
					'tbl_contrato_estatus.nombre as cont_estado'
				])
			->groupBy('tbl_personas_maestro_movil.id')
			->where('tbl_contrato.ContratoPrueba','<>',1)
			->orderBy('id','desc');
		}

			/*
    $data = \DB::table('tbl_personas_maestro_movil')
				->leftJoin('tbl_personas','tbl_personas.idpersona','=','tbl_personas_maestro_movil.idpersona')
				->leftJoin('tbl_contrato','tbl_contrato.contrato_id','=','tbl_personas_maestro_movil.contrato_id')
				->leftJoin('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_personas_maestro_movil.idcontratista')
				->leftJoin('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_personas_maestro_movil.contrato_id')
				->leftJoin('tbl_centro','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
				->leftJoin('tbl_nacionalidad','tbl_nacionalidad.id_Nac','=','tbl_personas.id_Nac')
				->leftJoin('tbl_trab_dueno','tbl_trab_dueno.IdPersona','=','tbl_personas_maestro_movil.idpersona')
				->leftJoin('tbl_pensionados','tbl_pensionados.IdPersona','=','tbl_personas_maestro_movil.idpersona')
				->select(
					[
						'id',
						'periodo',
						'tbl_personas_maestro_movil.idpersona',
						'tbl_personas_maestro_movil.contrato_id',
						'estatus',
						'FechaEfectiva',
						DB::raw("tbl_personas.RUT as RUTPersona"),
						'tbl_contrato.cont_numero',
						'tbl_personas_maestro_movil.FechaInicioFaena',
						'tbl_personas_maestro_movil.FechaFinFaena',
						'tbl_personas.FechaNacimiento',
						'tbl_contratistas.IdContratista',
						DB::raw("tbl_contratistas.RUT as RUTContratista"),
						'tbl_contratistas.RazonSocial',
						'tbl_centro.Descripcion',
						'tbl_personas.Nombres',
						'tbl_personas.Apellidos',
						'tbl_personas.Sexo',
						'tbl_nacionalidad.nacionalidad',
						DB::raw("if(tbl_pensionados.id_pens is null,'no','si') as pensionado"),
						DB::raw("if(tbl_trab_dueno.id_dueno is null,'no','si') as dueno")
					])
				->orderBy('id','desc');
				*/
    return \Datatables::of($data)->make(true);

  }


}
