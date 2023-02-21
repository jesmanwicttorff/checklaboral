<?php namespace App\Library;



use Carbon\Carbon;
use App\Library\MyLoadbatch;
use App\Models\checklaboral\Personasmaestro;
use App\Models\checklaboral\Contratomaestro;
use App\Models\checklaboral\Diferenciacalculo;
use App\Models\checklaboral\Diferenciancpersonas;
use App\Models\checklaboral\Diferenciancempresas;
use App\Models\checklaboral\Riesgo;
use App\Models\Contratos;
use App\Models\Personas;
use App\Models\TipoDocumentos;
use App\Models\Contratistas;
use App\Models\Reportegeneralcheck;
use App\Models\Reportegeneraldetallecheck;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use ZipArchive;
use DateTime;

class MyCheckLaboral
{
	/**
	* @date 2019-01-24
	* @author Diego Díaz - SCP
	* @package MyCheckLaboral
	*/

  static protected $gintIdUser;
  static private $gintIdLevelUser;
  static protected $gdatPeriodo;
  static protected $garrPeriodos;
  static private $garrFormat;
  static private $gstrRuta = "reportes/checklaboral/";

  public function __construct($pdatPeriodo=null)
  {
    self::$gintIdUser = \Session::get('uid');
    self::$gintIdLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lobjMaestro = Contratomaestro::select('periodo')->groupBy('periodo')->orderBy('periodo','desc')->get();
    if ($pdatPeriodo){
      self::$gdatPeriodo = $pdatPeriodo;
    }else{
      self::$gdatPeriodo = Contratomaestro::max('periodo');
    }
    self::$garrPeriodos = $lobjMaestro;
  }

  static private function getFormato($pintIdProceso=0) {
    if ($pintIdProceso==0){
      $larrFormato = array(
        "cont_numero" => 'A',
        "rut" => 'B',
        "nombre" => 'C',
        "sueldo_base" => 'D',
        "gratificacion_legal" => 'E',
        "horas_extras" => 'F',
        "otros_imponibles" => 'G',
        "no_imponible" => 'H',
        "impuesto" => 'I',
        "otros_descuentos" => 'J',
        "ol_diferencia_calculo" => 'K',
        "ol_diferencia_pago" => 'L',
        "afp" => 'M',
        "ahorro_voluntario" => 'N',
        "salud" => 'O',
        "salud_voluntario" => 'P',
        "ccaf" => 'Q',
        "afc" => 'R',
        "trabajo_pesado" => 'S',
        "subtotal_previsiones" => 'T',
        "sis" => 'U',
        "afc_empleador" => 'V',
        "trabajo_pesado_empleador" => 'W',
        "mutualidad" => 'X',
        "subtotal_previsiones_empleador" => 'Y',
        "op_diferencia_calculo" => 'Z',
        "op_diferencia_pago" => 'AA',
        "sl_diferencia_calculo" => 'AB',
        "sl_diferencia_pago" => 'AC',
        "ias" => 'AD',
        "vacaciones" => 'AE',
        "otros" => 'AF',
        "fl_diferencia_calculo" => 'AG',
        "fl_diferencia_pago" => 'AH',
        "calculado" => 'AI',
        "pagado" => 'AJ',
        "cl_diferencia_calculo" => 'AK',
        "cl_diferencia_pago" => 'AL',
        "diferencia_favor_trabajador" => 'AN',
        "diferencia_favor_empleador" => 'AO',
        "limite" => 'AP'
      );
    }elseif ($pintIdProceso==1){
      $larrFormato = array(
        "correlativo" => 'A',
        "año" => 'B',
        "mes" => 'C',
        "id_nc" => 'D',
        "rut" => 'E',
        "nombre" => 'F',
        "cont_numero" => 'G',
        "empresa" => 'H',
        "rut_empresa" => 'I',
        "planta" => 'J',
        "documento" => 'K',
        "idestado" => 'L',
        "estado" => 'M',
        "comentario" => 'N',
        "no_conformidad" => 'O',
        "filtro_comentario" => 'P'
      );
    }elseif ($pintIdProceso==2){
      $larrFormato = array(
        "mes" => 'A',
        "id_documento" => 'B',
        "cont_numero" => 'C',
        "rut_empresa" => 'D',
        "nombre" => 'E',
        "planta" => 'F',
        "documento" => 'G',
        "idestado" => 'H',
        "estado" => 'I',
        "comentario" => 'J',
        "no_conformidad" => 'K',
        "columna_1" => 'L',
        "columna_2" => 'M',
        "columna_2" => 'N',
        "comentario_sone" => 'O'
      );
    }elseif ($pintIdProceso==3){
      $larrFormato = array(
        "cont_numero" => 'A',
        "empresa" => 'B',
        "dotacion" => 'C',
        "ingreso" => 'D',
        "egreso" => 'E',
        "porcentaje_rotacion" => 'F',
        "variable_1" => 'G',
        "riesgo_rotacion" => 'H',
        "horas_diarias" => 'I',
        "horas_totales" => 'J',
        "horas_vacaciones" => 'K',
        "horas_licencias" => 'L',
        "horas_otros_ausentismo" => 'M',
        "porcentaje_ausentimo" => 'N',
        "variable_2" => 'O',
        "riesgo_ausentismo" => 'P',
        "rc_monto_impago" => 'Q',
        "rc_numero_impago" => 'R',
        "indicador_comercial" => 'S',
        "mayor_400" => 'T',
        "riesgo_comercial" => 'U',
        "patrimonio" => 'V',
        "ri_numero_impago" => 'W',
        "ri_monto_impago" => 'X',
        "indicador_impuesto" => 'Y',
        "mayor_2" => 'Z',
        "riesgo_impuesto" => 'AA',
        "riesgo" => 'AB'
      );
    }
    self::$garrFormat = $larrFormato;
    return $larrFormato;

  }

  static public function getPeriodo() {
    return self::$gdatPeriodo;
  }

  static public function getPeriodos() {
    return self::$garrPeriodos;
  }


  static public function getObservation($pstrTipo){
    $lstrObservacion = "";
    if ($pstrTipo=="diferencias"){
      $lobjResult = \DB::table('tbl_diferencias_calculo')
                    ->where('tbl_diferencias_calculo.periodo',self::getPeriodo())
                    ->where('tbl_diferencias_calculo.IdEstatus','!=',1)
                    ->select(\DB::raw("CASE WHEN tbl_diferencias_calculo.IdEstatus = 1 then '' WHEN tbl_diferencias_calculo.IdEstatus = 2 then 'El rut de la persona no se encuentra' WHEN tbl_diferencias_calculo.IdEstatus = 3 then 'El numero de contrato no se encuentra' else 'No especificado' end as IdEstatus"),
                             \DB::raw('count(*) as cantidad'))
                    ->groupBy('tbl_diferencias_calculo.IdEstatus')
                    ->get();
    }else if ($pstrTipo=="noconformidades"){
      $lobjResult = \DB::table('tbl_diferencias_nc_personas')
                    ->where('tbl_diferencias_nc_personas.periodo',self::getPeriodo())
                    ->where('tbl_diferencias_nc_personas.IdEstatus','!=',1)
                    ->select(\DB::raw("CASE WHEN IdEstatus = 1 then '' WHEN IdEstatus = 2 then 'El rut de la persona no se encuentra' WHEN IdEstatus = 3 then 'El numero de contrato no se encuentra' WHEN IdEstatus = 4 then 'El tipo de documento no se encuentra' else 'No especificado' end as IdEstatus"),
                             \DB::raw('count(*) as cantidad'))
                    ->groupBy('tbl_diferencias_nc_personas.IdEstatus')
                    ->get();
    }else if ($pstrTipo=="noconformidadesempresa"){
      $lobjResult = \DB::table('tbl_diferencias_nc_empresas')
                    ->where('tbl_diferencias_nc_empresas.periodo',self::getPeriodo())
                    ->where('tbl_diferencias_nc_empresas.IdEstatus','!=',1)
                    ->select(\DB::raw("CASE WHEN tbl_diferencias_nc_empresas.IdEstatus = 1 then '' WHEN tbl_diferencias_nc_empresas.IdEstatus = 2 then 'El rut del contratista no se encuentra' WHEN tbl_diferencias_nc_empresas.IdEstatus = 3 then 'El numero de contrato no se encuentra' WHEN tbl_diferencias_nc_empresas.IdEstatus = 4 then 'El tipo de documento no se encuentra' else 'No especificado' end as IdEstatus"),
                             \DB::raw('count(*) as cantidad'))
                    ->groupBy('tbl_diferencias_nc_empresas.IdEstatus')
                    ->get();
    }else if ($pstrTipo=="riesgo"){
      $lobjResult = \DB::table('tbl_riesgo')
                    ->where('tbl_riesgo.periodo',self::getPeriodo())
                    ->where('tbl_riesgo.IdEstatus','!=',1)
                    ->select(\DB::raw("CASE WHEN tbl_riesgo.IdEstatus = 1 then '' WHEN tbl_riesgo.IdEstatus = 2 then 'El numero de contrato no se encuentra' else 'No especificado' end as IdEstatus"),
                             \DB::raw('count(*) as cantidad'))
                    ->groupBy('tbl_riesgo.IdEstatus')
                    ->get();
    }
    foreach ($lobjResult as $larrResultado) {
      $lstrObservacion .= " ".$larrResultado->IdEstatus.':  '.$larrResultado->IdEstatus.'</br>';
    }
    if ($lstrObservacion){
      $lstrObservacion .= "Se encontraron las siguientes observaciones: </br>";
    }
    return $lstrObservacion;
  }

static public function GenerarReporte($pstrPeriodo, $pintContrato, $pintIdReporte = null){

    $larrResult = array();
    $larrParametros = array();
    $lstrRuta = self::$gstrRuta.str_replace('-','',self::$gdatPeriodo).'/';
    if(!\File::exists($lstrRuta)) {
      \File::makeDirectory($lstrRuta, 0777, true, true);
    }

    $larrResult = self::LoadData($larrParametros,null,$pintContrato);
    $numeroContrato = Contratos::where('contrato_id',$pintContrato)->value('cont_numero');
    $lstrRutaNombre = $larrResult['Faena'].' - '.$larrResult['RazonSocial'].' - '.$larrResult['Servicio'].' - '.$numeroContrato.'.pdf';
		$larrResult['DataDesempenio'] = self::LoadDataDesempenio($larrParametros,null,$pintContrato);
		$larrResult['DataNoConformidades'] = self::LoadDataNoConformidades($larrParametros,null,$pintContrato);
    $larrResult['DataRiesgo'] = self::LoadDataRiesgo($larrParametros,null,$pintContrato);
    $larrResult['ldatPeriodo'] = self::getPeriodo();
	  $larrResult['id'] = $pintContrato;

		$pdf = \PDF::loadView('checklaboral.reporte.print', $larrResult);
		$pdf->setOption('enable-javascript', true);
		$pdf->setOption('javascript-delay', 5000);
		$pdf->setOption('enable-smart-shrinking', true);
		$pdf->setOption('no-stop-slow-scripts', true);
    $lstrResult = $pdf->output();

    $disk = \Storage::disk('local');

    // Save the file with the PDF output.
    if ($disk->put($lstrRuta.$lstrRutaNombre, $lstrResult)) {
        // Successfully stored. Return the full path.
        if ($pintIdReporte) {
          $lobjReporteDetalle = new Reportegeneraldetallecheck();
          $lobjReporteDetalle->periodo = self::getPeriodo();
          $lobjReporteDetalle->reporte_id = $pintIdReporte;
          $lobjReporteDetalle->contrato_id = $pintContrato;
          $lobjReporteDetalle->url = $lstrRuta.$lstrRutaNombre;
          $lobjReporteDetalle->save();
        }
        return $lstrRuta.$lstrRutaNombre;
    }

    return $lstrRutaNombre;

}

static public function GenerarReporteTransversal($pstrPeriodo, $pintIdContratista, $pintIdReporte = null){

  $larrResult = array();
  $larrParametros = array();
  $lstrRuta = self::$gstrRuta.str_replace('-','',self::$gdatPeriodo).'/';
  if(!\File::exists($lstrRuta)) {
    \File::makeDirectory($lstrRuta, 0777, true, true);
  }

  $larrResult = self::LoadData($larrParametros,null,null,$pintIdContratista);
  $lstrRutaNombre = $larrResult['RazonSocial'].' - GLOBAL.pdf';
  $larrResult['Contratista'] = $larrResult['RazonSocial'];
  $larrResult['DataDesempenio'] = self::LoadDataDesempenio($larrParametros,null,null,$pintIdContratista);
  $larrResult['DataNoConformidades'] = self::LoadDataNoConformidades($larrParametros,null,null,$pintIdContratista);
  $larrResult['DataRiesgo'] = self::LoadDataRiesgo($larrParametros,null,null,$pintIdContratista);
  $larrResult['ldatPeriodo'] = self::getPeriodo();
  $larrResult['Faena'] = ""; // como recuperamos la faena?
  $larrResult['id'] = $pintIdContratista;

  $pdf = \PDF::loadView('checklaboral.reporte.printtransversal', $larrResult);
  $pdf->setOption('enable-javascript', true);
  $pdf->setOption('javascript-delay', 5000);
  $pdf->setOption('enable-smart-shrinking', true);
  $pdf->setOption('no-stop-slow-scripts', true);
  $lstrResult = $pdf->output();

  $disk = \Storage::disk('local');

  // Save the file with the PDF output.
  if ($disk->put($lstrRuta.$lstrRutaNombre, $lstrResult)) {
      // Successfully stored. Return the full path.
      if ($pintIdReporte) {
        $lobjReporteDetalle = new Reportegeneraldetallecheck();
        $lobjReporteDetalle->periodo = self::getPeriodo();
        $lobjReporteDetalle->reporte_id = $pintIdReporte;
        $lobjReporteDetalle->idcontratista = $pintIdContratista;
        $lobjReporteDetalle->url = $lstrRuta.$lstrRutaNombre;
        $lobjReporteDetalle->save();
      }
      return $lstrRuta.$lstrRutaNombre;
  }

  return $lstrRutaNombre;

}

static public function GenerarReporteGeneral($pbolForzar = false){

  $larrResult = array();
  ini_set('max_execution_time', 2500);
  ini_set('request_terminate_timeout',2500);
  set_time_limit(2500);

  $lobjReporte = Reportegeneralcheck::where('periodo',self::getPeriodo())
                                      ->whereNotNull('url')
                                      ->first();

  if (!$lobjReporte || $pbolForzar){

    //Generamos un reporte
    $lobjReporteGeneral = new Reportegeneralcheck();
    $lobjReporteGeneral->periodo = self::getPeriodo();
    $lobjReporteGeneral->user_id = \Session::get('uid');

    if ($lobjReporteGeneral->save()){

      $lintReporteId = $lobjReporteGeneral->id;

      //Buscamos todos los contratos del maestro
      $lobjContratos = Contratomaestro::where('periodo',self::getPeriodo())
                                    //->limit(1)
                                    ->get();
      if ($lobjContratos){
        //Contratos
        $ldatFecha = date('Y-m-d H:m:i');
        //echo "Iniciamos: ".$ldatFecha."<br/>";
        $lintCantidad = 0;
        foreach ($lobjContratos as $lrowData) {
          $lintCantidad += 1;
          self::GenerarReporte(self::getPeriodo(), $lrowData->contrato_id, $lintReporteId);
        }
        $ldatFechaFin = date('Y-m-d H:m:i');
        //echo "Terminamos: ".$ldatFechaFin." (duración: ".($ldatFechaFin-$ldatFecha).") ";
      }

      //Transversales
      $lobjContratosTransversales = Contratomaestro::where('periodo',self::getPeriodo())
                                    ->select('tbl_contrato.IdContratista')
                                    ->distinct()
                                    //->limit(1)
                                    ->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contrato_maestro.contrato_id')
                                    ->where('tbl_contrato.transversal','=',1)
                                    ->get();

      if ($lobjContratosTransversales) {
        $ldatFecha = date('Y-m-d H:m:i');
        //echo "Iniciamos: ".$ldatFecha."<br/>";
        $lintCantidad = 0;
        foreach ($lobjContratosTransversales as $lrowData) {
          $lintCantidad += 1;
          self::GenerarReporteTransversal(self::getPeriodo(), $lrowData->IdContratista, $lintReporteId);
        }
        $ldatFechaFin = date('Y-m-d H:m:i');
        //echo "Terminamos: ".$ldatFechaFin." (duración: ".($ldatFechaFin-$ldatFecha).") ";
      }

        // comprimimos el archivo

        $lstrRuta = storage_path(self::$gstrRuta);
        $zip_file = 'reporte_checklaboral_'.self::getPeriodo().'.zip';

        $zip = new \ZipArchive();
        $zip->open($lstrRuta.$zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($lstrRuta.str_replace('-','',self::$gdatPeriodo).'/'));
        foreach ($files as $name => $file)
        {
          // We're skipping all subfolders
          if (!$file->isDir()) {
            $filePath     = $file->getRealPath();

            // extracting filename with substr/strlen
            $relativePath = '/' . substr($filePath, strlen($lstrRuta));

            $zip->addFile($filePath, $relativePath);
          }
        }
        $zip->close();

        $lobjReporteGeneral->url = $lstrRuta.$zip_file;
        $lobjReporteGeneral->save();

        $larrResult = array("status"=>"success","message"=>"Reporte generado satisfactoriamente","result"=>array("Ruta"=>$lstrRuta, "Nombre"=>$zip_file));

      }else{
        $larrResult = array("status"=>"error","code"=>"02","message"=>"Error guardando el reporte");
      }

    }else{
      $larrResult = array("status"=>"error","code"=>"01","message"=>"Existe un reporte generado para este periodo");
    }

  return $larrResult;

}

static public function LoadDataNoConformidades($parrParametros, $pintIdCentro = null, $pintContratoId = null, $pintIdContratista = null){

  $larrResult = array();
    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');
    $lobjFiltro = \MySourcing::getFiltroUsuario(2,1);

  $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjPorcentajeDocumento = $lobjConsulta->select('tbl_diferencias_nc_personas.id',
                                                     \DB::raw('IFNULL(case when tbl_documentos.FechaEmision > tbl_diferencias_nc_personas.periodo then tbl_diferencias_nc_personas.periodo else tbl_documentos.FechaEmision end, tbl_diferencias_nc_personas.periodo) as periodo'),
                                                     'tbl_personas.RUT',
                                                     'tbl_personas.Nombres',
                                                     'tbl_personas.Apellidos',
                                                     'tbl_tipos_documentos.descripcion as TipoDocumento',
                                                     'tbl_contrato.cont_numero',
                                                     'tbl_diferencias_nc_personas.Resultado',
                                                     'tbl_documentos_estatus.Descripcion as Estatus'
                                                     )
            ->join('tbl_diferencias_nc_personas','tbl_diferencias_nc_personas.contrato_id','=','tbl_contrato.contrato_id')
            ->join('tbl_documentos','tbl_documentos.IdDocumento','=','tbl_diferencias_nc_personas.IdDocumento')
            ->join('tbl_personas','tbl_personas.IdPersona','=','tbl_diferencias_nc_personas.IdPersona')
            ->join('tbl_documentos_estatus','tbl_documentos_estatus.IdEstatus','=','tbl_diferencias_nc_personas.IdEstatusDocumento')
            ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_diferencias_nc_personas.IdTipoDocumento')
            ->where('tbl_diferencias_nc_personas.periodo',self::getPeriodo())
            ->where('tbl_diferencias_nc_personas.IdEstatusDocumento','!=',0)
            ->where('tbl_diferencias_nc_personas.IdEstatusDocumento','!=',5)
            ->where('tbl_tipos_documentos.ControlCheckLaboral',1)
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
            });
    if ($pintIdCentro){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->whereExists(function ($query) use ($pintIdCentro) {
        $query->select(\DB::raw(1))
            ->from('tbl_contratos_centros')
            ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
            ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
      });
    }
    if ($pintContratoId){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->where('tbl_contrato.contrato_id','=',$pintContratoId);
    }
    if ($pintIdContratista){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->where('tbl_contrato.IdContratista','=',$pintIdContratista)
                                                         ->where('tbl_contrato.Transversal','=',1);
    }
    $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->get();

    $larrResult['NoConformidades'] = $lobjPorcentajeDocumento;

     $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjPorcentajeDocumento = $lobjConsulta->select('tbl_diferencias_nc_empresas.id',
                                                     'tbl_documentos.FechaEmision as periodo',
                                                     'tbl_tipos_documentos.descripcion as TipoDocumento',
                                                     'tbl_centro.Descripcion as Faena',
                                                     'tbl_contrato.cont_numero',
                                                     'tbl_diferencias_nc_empresas.Resultado',
                                                     'tbl_documentos_estatus.Descripcion as Estatus'
                                                     )
            ->join('tbl_diferencias_nc_empresas','tbl_diferencias_nc_empresas.contrato_id','=','tbl_contrato.contrato_id')
            ->join('tbl_documentos','tbl_documentos.IdDocumento','=','tbl_diferencias_nc_empresas.IdDocumento')
            ->join('tbl_documentos_estatus','tbl_documentos_estatus.IdEstatus','=','tbl_diferencias_nc_empresas.IdEstatusDocumento')
            ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_diferencias_nc_empresas.IdTipoDocumento')
            ->join('tbl_contratos_centros',function($table) {
                $table->on('tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
                      ->on('tbl_contratos_centros.IdTipoCentro','=',\DB::raw('1'));
            })
            ->join('tbl_centro','tbl_contratos_centros.IdCentro','=','tbl_centro.IdCentro')
            ->where('tbl_diferencias_nc_empresas.periodo',self::getPeriodo())
            ->where('tbl_diferencias_nc_empresas.IdEstatusDocumento','!=',0)
            ->where('tbl_diferencias_nc_empresas.IdEstatusDocumento','!=',5)
            ->where('tbl_tipos_documentos.ControlCheckLaboral',1)
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
            });
    if ($pintIdCentro){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->whereExists(function ($query) use ($pintIdCentro) {
        $query->select(\DB::raw(1))
            ->from('tbl_contratos_centros')
            ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
            ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
      });
    }
    if ($pintContratoId){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->where('tbl_contrato.contrato_id','=',$pintContratoId);
    }
    if ($pintIdContratista){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->where('tbl_contrato.IdContratista','=',$pintIdContratista)
                                                         ->where('tbl_contrato.Transversal','=',1);
    }
    $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->get();

    $larrResult['NoConformidadesContratos'] = $lobjPorcentajeDocumento;

    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjPorcentajeDocumento = $lobjConsulta->select('tbl_diferencias_nc_empresas.id',
                                                     'tbl_documentos.FechaEmision as periodo',
                                                     'tbl_tipos_documentos.descripcion as TipoDocumento',
                                                     'tbl_centro.Descripcion as Faena',
                                                     'tbl_contrato.cont_numero',
                                                     'tbl_diferencias_nc_empresas.Resultado',
                                                     'tbl_documentos_estatus.Descripcion as Estatus'
                                                     )
            ->join('tbl_diferencias_nc_empresas','tbl_diferencias_nc_empresas.idcontratista','=','tbl_contrato.idcontratista')
            ->join('tbl_documentos','tbl_documentos.IdDocumento','=','tbl_diferencias_nc_empresas.IdDocumento')
            ->join('tbl_documentos_estatus','tbl_documentos_estatus.IdEstatus','=','tbl_diferencias_nc_empresas.IdEstatusDocumento')
            ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_diferencias_nc_empresas.IdTipoDocumento')
            ->join('tbl_contratos_centros',function($table) {
                $table->on('tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
                      ->on('tbl_contratos_centros.IdTipoCentro','=',\DB::raw('1'));
            })
            ->join('tbl_centro','tbl_contratos_centros.IdCentro','=','tbl_centro.IdCentro')
            ->where('tbl_diferencias_nc_empresas.periodo',self::getPeriodo())
            ->where('tbl_diferencias_nc_empresas.IdEstatusDocumento','!=',0)
            ->where('tbl_diferencias_nc_empresas.IdEstatusDocumento','!=',5)
            ->where('tbl_diferencias_nc_empresas.contrato_id','=',0)
            ->where('tbl_tipos_documentos.ControlCheckLaboral',1)
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
            });
    if ($pintIdCentro){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->whereExists(function ($query) use ($pintIdCentro) {
        $query->select(\DB::raw(1))
            ->from('tbl_contratos_centros')
            ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
            ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
      });
    }
    if ($pintContratoId){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->where('tbl_contrato.contrato_id','=',$pintContratoId);
    }
    if ($pintIdContratista){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->where('tbl_contrato.IdContratista','=',$pintIdContratista)
                                                         ->where('tbl_contrato.Transversal','=',1);
    }
    $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->get();

    $larrResult['NoConformidadesEmpresa'] = $lobjPorcentajeDocumento;

    return $larrResult;

}

  static public function loadDiscrepancias($parrParametros, $pintIdCentro = null, $pintContratoId = null, $pintIdContratista = null){

    $periodoActual = self::getPeriodo();
    $porcentaje=0;

    $discrepancia = \DB::table('tbl_f301_discrepancias')->where('contrato_id',$pintContratoId)->where('periodo',$periodoActual)->first();
    if($discrepancia){
      $porcentaje = $discrepancia->valor;
    }

    return $porcentaje;
  }

  static public function loadDiscrepanciasAcumuladas($parrParametros, $pintIdCentro = null, $pintContratoId = null, $pintIdContratista = null){

    $periodoActual = self::getPeriodo();
    $porcentaje=0;

    $discrepancia = \DB::table('tbl_f301_discrepancias')->where('contrato_id',$pintContratoId)->where('periodo','<=',$periodoActual)->avg('valor');
    if($discrepancia){
      $porcentaje = $discrepancia;
    }

    return $porcentaje;
  }

  static public function LoadDataDesempenio($parrParametros, $pintIdCentro = null, $pintContratoId = null, $pintIdContratista = null){

    $larrResult = array();
    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');
    $lobjFiltro = \MySourcing::getFiltroUsuario(2,1);

    //
    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjPorcentajeDocumento = $lobjConsulta->select(\DB::raw('tbl_tipos_documentos.descripcion as tipodocumento'),
                                                      \DB::raw('1-sum(case when tbl_diferencias_nc_empresas.idestatusdocumento != 5 then 1 else 0 end)/count(*) as porcentaje'))
            ->join('tbl_diferencias_nc_empresas','tbl_diferencias_nc_empresas.IdContratista','=','tbl_contrato.IdContratista')
            ->rightjoin('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_diferencias_nc_empresas.IdTipoDocumento')
            ->where('tbl_diferencias_nc_empresas.periodo',self::getPeriodo())
            ->where('tbl_diferencias_nc_empresas.contrato_id',0)
            ->where('tbl_tipos_documentos.ControlCheckLaboral',1)
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
            });
    if ($pintIdCentro){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->whereExists(function ($query) use ($pintIdCentro) {
        $query->select(\DB::raw(1))
            ->from('tbl_contratos_centros')
            ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
            ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
      });
    }
    if ($pintContratoId){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->where('tbl_contrato.contrato_id','=',$pintContratoId);
    }
    if ($pintIdContratista){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->where('tbl_contrato.IdContratista','=',$pintIdContratista)
      ->where('tbl_contrato.transversal','=',1);
    }
    $lobjPorcentajeDocumentoEmpresa = $lobjPorcentajeDocumento->groupBy('tbl_tipos_documentos.descripcion')->get();
    $larrResult['DocumentosPorcentajeEmpresas'] = $lobjPorcentajeDocumentoEmpresa;

    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjPorcentajeDocumento = $lobjConsulta->select(\DB::raw('tbl_tipos_documentos.descripcion as tipodocumento'),
                                                      \DB::raw('1-sum(case when tbl_diferencias_nc_empresas.idestatusdocumento != 5 then 1 else 0 end)/count(*) as porcentaje'))
            ->join('tbl_diferencias_nc_empresas','tbl_diferencias_nc_empresas.contrato_id','=','tbl_contrato.contrato_id')
            ->rightjoin('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_diferencias_nc_empresas.IdTipoDocumento')
            ->where('tbl_diferencias_nc_empresas.periodo',self::getPeriodo())
            ->where('tbl_tipos_documentos.ControlCheckLaboral',1)
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
            });
    if ($pintIdCentro){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->whereExists(function ($query) use ($pintIdCentro) {
        $query->select(\DB::raw(1))
            ->from('tbl_contratos_centros')
            ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
            ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
      });
    }
    if ($pintContratoId){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->where('tbl_contrato.contrato_id','=',$pintContratoId);
    }
    if ($pintIdContratista){
      $lobjPorcentajeDocumento = $lobjPorcentajeDocumento->where('tbl_contrato.IdContratista','=',$pintIdContratista)
      ->where('tbl_contrato.transversal','=',1);
    }
    $lobjPorcentajecontrato = $lobjPorcentajeDocumento->groupBy('tbl_tipos_documentos.descripcion')->get();
    $larrResult['DocumentosPorcentajeContratos'] = $lobjPorcentajecontrato;

    $lobjDocumetosEmpContra = array($lobjPorcentajeDocumentoEmpresa,$lobjPorcentajecontrato);

    $larrResult['lobjDocumetosEmpContra'] = $lobjDocumetosEmpContra[0];
    $doc_personaEmp = array();

    //***********Añadimos los elementos en el array */
    foreach ($lobjPorcentajeDocumentoEmpresa as $ope)
     {

       array_push($doc_personaEmp,['tipodocumento' =>$ope->tipodocumento , 'porcentaje' => $ope->porcentaje ]);

     }

     foreach ($lobjPorcentajecontrato as $ope)
     {

       array_push($doc_personaEmp,['tipodocumento' =>$ope->tipodocumento , 'porcentaje' => $ope->porcentaje ]);

     }

     /// Ordenamos el Array
     usort($doc_personaEmp, function($a, $b)
     {
      return strcmp($a["tipodocumento"], $b["tipodocumento"]);
    });
     $larrResult['DocumentosEMPContrato'] = $doc_personaEmp;


    //
    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjPorcentajeDocumentoP = $lobjConsulta->select(\DB::raw('tbl_tipos_documentos.descripcion as tipodocumento'),
                                                      \DB::raw('1-sum(case when tbl_diferencias_nc_personas.idestatusdocumento != 5 then 1 else 0 end)/count(tbl_diferencias_nc_personas.id) as porcentaje'))
            ->join('tbl_diferencias_nc_personas','tbl_diferencias_nc_personas.contrato_id','=','tbl_contrato.contrato_id')
            ->rightjoin('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_diferencias_nc_personas.IdTipoDocumento')
            ->where('tbl_diferencias_nc_personas.periodo',self::getPeriodo())
            ->where('tbl_tipos_documentos.ControlCheckLaboral',1)
            ->where('tbl_diferencias_nc_personas.IdEstatusDocumento','!=',0)
            ->where('tbl_tipos_documentos.IdProceso','!=',4)
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
            });
    if ($pintIdCentro){
      $lobjPorcentajeDocumentoP = $lobjPorcentajeDocumentoP->whereExists(function ($query) use ($pintIdCentro) {
        $query->select(\DB::raw(1))
            ->from('tbl_contratos_centros')
            ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
            ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
      });
    }
    if ($pintContratoId){
      $lobjPorcentajeDocumentoP = $lobjPorcentajeDocumentoP->where('tbl_contrato.contrato_id','=',$pintContratoId);
    }
    if ($pintIdContratista){
      $lobjPorcentajeDocumentoP = $lobjPorcentajeDocumentoP->where('tbl_contrato.IdContratista','=',$pintIdContratista)
      ->where('tbl_contrato.transversal','=',1);
    }
    $lobjPorcentajeDocumentoP = $lobjPorcentajeDocumentoP->groupBy('tbl_tipos_documentos.descripcion')->get();
    $larrResult['DocumentosPorcentajePersonas'] = $lobjPorcentajeDocumentoP;


    /*******Finaliza */
   $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjPorcentajeDocumentoPF = $lobjConsulta->select(\DB::raw('tbl_tipos_documentos.IdTipoDocumento'),
                                                       \DB::raw('tbl_tipos_documentos.descripcion as tipodocumento'),
                                                       \DB::raw('1-sum(case when tbl_diferencias_nc_personas.idestatusdocumento != 5 then 1 else 0 end)/count(tbl_diferencias_nc_personas.id) as porcentaje'))
            ->join('tbl_diferencias_nc_personas','tbl_diferencias_nc_personas.contrato_id','=','tbl_contrato.contrato_id')
            ->rightjoin('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_diferencias_nc_personas.IdTipoDocumento')
            ->where('tbl_diferencias_nc_personas.periodo',self::getPeriodo())
            ->where('tbl_diferencias_nc_personas.IdEstatusDocumento','!=',0)
            ->where('tbl_tipos_documentos.ControlCheckLaboral',1)
            ->where('tbl_tipos_documentos.IdProceso','=',4)
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
            });
    if ($pintIdCentro){
      $lobjPorcentajeDocumentoPF = $lobjPorcentajeDocumentoPF->whereExists(function ($query) use ($pintIdCentro) {
        $query->select(\DB::raw(1))
            ->from('tbl_contratos_centros')
            ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
            ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
      });
    }
    if ($pintContratoId){
      $lobjPorcentajeDocumentoPF = $lobjPorcentajeDocumentoPF->where('tbl_contrato.contrato_id','=',$pintContratoId);
    }
    if ($pintIdContratista){
      $lobjPorcentajeDocumentoPF = $lobjPorcentajeDocumentoPF->where('tbl_contrato.IdContratista','=',$pintIdContratista)
      ->where('tbl_contrato.transversal','=',1);
    }
    $lobjPorcentajeDocumentoPF = $lobjPorcentajeDocumentoPF->groupBy('tbl_tipos_documentos.IdTipoDocumento')->groupBy('tbl_tipos_documentos.descripcion')->get();
    if (count($lobjPorcentajeDocumentoPF)<=0){
      $lobjPorcentajeDocumentoPF = \DB::Table('tbl_tipos_documentos')
      ->where('tbl_tipos_documentos.IdProceso','=',4)
      ->select(\DB::raw('tbl_tipos_documentos.IdTipoDocumento'),
               \DB::raw('tbl_tipos_documentos.descripcion as tipodocumento'),
               \DB::raw('1 as porcentaje'))
      ->get();
    }
    $larrResult['DocumentosPorcentajePersonasF'] = $lobjPorcentajeDocumentoPF;

    $lobjDocumetos = array($lobjPorcentajeDocumentoP,$lobjPorcentajeDocumentoPF);

   $larrResult['DocPorcentajePER'] = $lobjDocumetos[0];
   $doc_persona = array();

   //***********Añadimos los elementos en el array */
   foreach ($lobjPorcentajeDocumentoP as $op)
    {

      array_push($doc_persona,['tipodocumento' =>$op->tipodocumento , 'porcentaje' => $op->porcentaje ]);

    }

    foreach ($lobjPorcentajeDocumentoPF as $op)
    {

      array_push($doc_persona,['tipodocumento' =>$op->tipodocumento , 'porcentaje' => $op->porcentaje ]);

    }

    /// Ordenamos el Array
    usort($doc_persona, function($a, $b)
    {
     return strcmp($a["tipodocumento"], $b["tipodocumento"]);
   });
    $larrResult['DocumentosPER'] = $doc_persona;

    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjDatosGrafico = $lobjConsulta->select('dim_tiempo.NMes3L',
                                     'tbl_contrato_maestro.periodo',
                                     \DB::raw('avg(tbl_contrato_maestro.porcentaje_ol) as PorcentajeOl'),
                                     \DB::raw('avg(tbl_contrato_maestro.porcentaje_op) as PorcentajeOp'),
                                     \DB::raw('avg(tbl_contrato_maestro.porcentaje_f) as PorcentajeF')
                                     )
            ->join('tbl_contrato_maestro','tbl_contrato.contrato_id','=','tbl_contrato_maestro.contrato_id')
            ->join('dim_tiempo','dim_tiempo.fecha','=','tbl_contrato_maestro.periodo')
            ->whereraw('dim_tiempo.fecha <= \''.self::getPeriodo().'\'')
            ->whereraw('dim_tiempo.fecha >= DATE_SUB(\''.self::getPeriodo().'\', INTERVAL 11 MONTH)')
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
                })
            ->groupBy('dim_tiempo.anio')
            ->groupBy('dim_tiempo.mes')
            ->groupBy('dim_tiempo.NMes3L')
            ->groupBy('tbl_contrato_maestro.periodo');
            if ($pintIdCentro){
              $lobjDatosGrafico = $lobjDatosGrafico->whereExists(function ($query) use ($pintIdCentro) {
                $query->select(\DB::raw(1))
                    ->from('tbl_contratos_centros')
                    ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
                    ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
              });
            }
            if ($pintContratoId){
              $lobjDatosGrafico = $lobjDatosGrafico->where('tbl_contrato.contrato_id','=',$pintContratoId);
            }
            if ($pintIdContratista){
              $lobjDatosGrafico = $lobjDatosGrafico->where('tbl_contrato.IdContratista','=',$pintIdContratista)
              ->where('tbl_contrato.transversal','=',1);
            }
    $lobjDatosGrafico = $lobjDatosGrafico->orderBy('dim_tiempo.anio','asc')
                      ->orderBy('dim_tiempo.mes','asc')
                      ->get();
    $lobjDatosGraficoT = collect($lobjDatosGrafico)->transform(function($larrValue, $lintKey){
      return [$larrValue->NMes3L, round(floatval($larrValue->PorcentajeOl),2)*100 ];
                             })->toArray();
    $larrResult['ObligacionesLaborales'] = $lobjDatosGraficoT;

    //rotacion
    $lobjDatosGraficoT = collect($lobjDatosGrafico)->transform(function($larrValue, $lintKey){
        return [$larrValue->NMes3L, round(floatval($larrValue->PorcentajeOp),2)*100 ];
                               })->toArray();
    $larrResult['ObligacionesPrevisionales'] = $lobjDatosGraficoT;

    $lobjDatosGraficoT = collect($lobjDatosGrafico)->transform(function($larrValue, $lintKey){
      return [$larrValue->NMes3L, round(floatval($larrValue->PorcentajeF),2)*100 ];
                             })->toArray();
    $larrResult['Finiquitos'] = $lobjDatosGraficoT;

    //Pie
    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjDatosGrafico = $lobjConsulta->select('tbl_contrato_maestro.periodo',
                                     'tbl_diferencias_calculo_pie.nombre',
                                     'tbl_diferencias_calculo_pie.grupo',
                                     \DB::raw('avg(tbl_diferencias_calculo_pie.indicador) as Porcentaje')
                                     )
            ->join('tbl_contrato_maestro','tbl_contrato.contrato_id','=','tbl_contrato_maestro.contrato_id')
            ->join('tbl_diferencias_calculo_pie',function($table){
              $table->on('tbl_diferencias_calculo_pie.contrato_id','=','tbl_contrato_maestro.contrato_id')
                    ->on('tbl_diferencias_calculo_pie.periodo','=','tbl_contrato_maestro.periodo');
                  })
            ->where('tbl_contrato_maestro.periodo','=',self::getPeriodo())
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
                })
            ->groupBy('tbl_contrato_maestro.periodo')
            ->groupBy('tbl_diferencias_calculo_pie.nombre')
            ->groupBy('tbl_diferencias_calculo_pie.grupo');
            if ($pintIdCentro){
              $lobjDatosGrafico = $lobjDatosGrafico->whereExists(function ($query) use ($pintIdCentro) {
                $query->select(\DB::raw(1))
                    ->from('tbl_contratos_centros')
                    ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
                    ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
              });
            }
            if ($pintContratoId){
              $lobjDatosGrafico = $lobjDatosGrafico->where('tbl_contrato.contrato_id','=',$pintContratoId);
            }
            if ($pintIdContratista){
              $lobjDatosGrafico = $lobjDatosGrafico->where('tbl_contrato.IdContratista','=',$pintIdContratista)
              ->where('tbl_contrato.transversal','=',1);
            }
    $lobjDatosGrafico = $lobjDatosGrafico->get();

    $larrDatosGraficosPie = array('obligaciones_laborales'=>array('x'=>array(),'y'=>array()),
                                  'obligaciones_previsionales'=>array('x'=>array(),'y'=>array()),
                                  'finiquito'=>array('x'=>array(),'y'=>array()));
    foreach ($lobjDatosGrafico as $DatosGraficosPie) {
        $larrDatosGraficosPie[$DatosGraficosPie->grupo]['x'][]=$DatosGraficosPie->nombre;
        $larrDatosGraficosPie[$DatosGraficosPie->grupo]['y'][]=[$DatosGraficosPie->nombre, round(floatval($DatosGraficosPie->Porcentaje),2)*100];
    }

    $larrResult['ObligacionesLaboralesPie'] = $larrDatosGraficosPie['obligaciones_laborales'];
    $larrResult['ObligacionesPrevisionalesPie'] = $larrDatosGraficosPie['obligaciones_previsionales'];
    $larrResult['FiniquitosPie'] = $larrDatosGraficosPie['finiquito'];

    return $larrResult;

  }

  static public function downloadreport($pstrTipo,$pintContratoId = ""){

    if ($pstrTipo=="reportegeneral"){
      $lobjReporte = Reportegeneralcheck::where('periodo',self::getPeriodo())
                      ->whereNotNull('url')
                      ->where('url','!=','')
                      ->first();
      if ($lobjReporte){
        return array("status"=>"success","message"=>"Reporte recuperado satisfactoriamente","result"=>$lobjReporte->url);
      }else{
        return array("status"=>"error","message"=>"Reporte no existe.");
      }
    }else if ($pstrTipo=="reportecontrato"){
      $lobjReporte = Reportegeneraldetallecheck::
                      join('tbl_reportes_generados', 'tbl_reportes_generados_detalle.reporte_id','=','tbl_reportes_generados.id')
                      ->select('tbl_reportes_generados_detalle.url')
                      ->where('tbl_reportes_generados_detalle.periodo',self::getPeriodo())
                      ->whereNotNull('tbl_reportes_generados_detalle.url')
                      ->where('tbl_reportes_generados_detalle.contrato_id','=',$pintContratoId)
                      ->where('tbl_reportes_generados_detalle.url','!=','')
                      ->orderBy('tbl_reportes_generados_detalle.id','desc')
                      ->first();
      if ($lobjReporte){
        $lstrUrl = storage_path($lobjReporte->url);
        return array("status"=>"success","message"=>"Reporte recuperado satisfactoriamente","result"=>$lstrUrl);
      }else{
        return array("status"=>"error","message"=>"Reporte no existe.");
      }
    }

  }

  static public function LoadDataRiesgo($parrParametros, $pintIdCentro = null, $pintContratoId = null, $pintIdContratista = null){

    $larrResult = array();
    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');
    $lobjFiltro = \MySourcing::getFiltroUsuario(2,1);

    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjDatosGrafico = $lobjConsulta->select('dim_tiempo.NMes3L',
                                     'tbl_riesgo.periodo',
                                     \DB::raw('sum(case when tbl_riesgo.riesgo_impuesto = 5 then 1 else 0 end)/count(tbl_riesgo.contrato_id) as RiesgoImpuesto'),
                                     \DB::raw('sum(tbl_riesgo.ri_numero_impago) as ImpuestoNumeroImpago'),
                                     \DB::raw('sum(tbl_riesgo.ri_monto_impago) as ImpuestoMontoImpago'),
                                     \DB::raw('sum(case when tbl_riesgo.riesgo_comercial = 5 then 1 else 0 end)/count(tbl_riesgo.contrato_id) as RiesgoComercial'),
                                     \DB::raw('sum(tbl_riesgo.rc_numero_impago) as ComercialNumeroImpago'),
                                     \DB::raw('sum(tbl_riesgo.rc_monto_impago) as ComercialMontoImpago'),
                                     \DB::raw('avg(tbl_riesgo.porcentaje_rotacion)/100 as RiesgoRotacion'),
                                     \DB::raw('sum(tbl_riesgo.Ingreso) as RotacionIngreso'),
                                     \DB::raw('sum(tbl_riesgo.Egreso) as RotacionEgreso'),
                                     \DB::raw('sum(tbl_riesgo.Dotacion) as RotacionDotacion'),
                                     \DB::raw('avg(tbl_riesgo.porcentaje_ausentimo)/100 as RiesgoAusentismo'),
                                     \DB::raw('sum(tbl_riesgo.horas_totales) as HorasTotales'),
                                     \DB::raw('sum(tbl_riesgo.horas_vacaciones) as HorasVacaciones'),
                                     \DB::raw('sum(tbl_riesgo.horas_licencias) as HorasLicencias'),
                                     \DB::raw('sum(tbl_riesgo.horas_otros_ausentismo) as HorasOtrosAusentismo'),
                                     \DB::raw('sum(tbl_riesgo.indicador_comercial) as indicador_comercial')
                                     )
            ->join('tbl_contrato_maestro','tbl_contrato.contrato_id','=','tbl_contrato_maestro.contrato_id')
            ->join('tbl_riesgo', function($table){
                    $table->on('tbl_riesgo.contrato_id','=','tbl_contrato_maestro.contrato_id')
                          ->on('tbl_riesgo.periodo','=','tbl_contrato_maestro.periodo');
            })
            ->join('dim_tiempo','dim_tiempo.fecha','=','tbl_contrato_maestro.periodo')
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
                })
            ->whereraw('dim_tiempo.fecha <= \''.self::getPeriodo().'\'')
            ->whereraw('dim_tiempo.fecha >= DATE_SUB(\''.self::getPeriodo().'\', INTERVAL 11 MONTH)')
            ->groupBy('dim_tiempo.anio')
            ->groupBy('dim_tiempo.mes')
            ->groupBy('dim_tiempo.NMes3L')
            ->groupBy('tbl_riesgo.periodo');
            if ($pintIdCentro){
              $lobjDatosGrafico = $lobjDatosGrafico->whereExists(function ($query) use ($pintIdCentro) {
                $query->select(\DB::raw(1))
                    ->from('tbl_contratos_centros')
                    ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
                    ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
              });
            }
            if ($pintContratoId){
              $lobjDatosGrafico = $lobjDatosGrafico->where('tbl_contrato.contrato_id','=',$pintContratoId);
            }
            if ($pintIdContratista){
              $lobjDatosGrafico = $lobjDatosGrafico->where('tbl_contrato.IdContratista','=',$pintIdContratista)
              ->where('tbl_contrato.transversal','=',1);
            }
    $lobjDatosGrafico = $lobjDatosGrafico->orderBy('dim_tiempo.anio','asc')
                      ->orderBy('dim_tiempo.mes','asc')
                      ->get();
    $lobjDatosGraficoT = collect($lobjDatosGrafico)->transform(function($larrValue, $lintKey){
      return [$larrValue->NMes3L, round(floatval($larrValue->RiesgoImpuesto),2)*100 ];
                             })->toArray();
    $larrResult['Impuesto'] = $lobjDatosGraficoT;

    //comercial
    if ($pintContratoId){
      $lobjDatosGraficoT = collect($lobjDatosGrafico)->transform(function($larrValue, $lintKey){
        return [$larrValue->NMes3L, round(floatval($larrValue->indicador_comercial),2) ];
                               })->toArray();
    }else{
      $lobjDatosGraficoT = collect($lobjDatosGrafico)->transform(function($larrValue, $lintKey){
      return [$larrValue->NMes3L, round(floatval($larrValue->RiesgoComercial),2)*100 ];
                             })->toArray();
    }
    $larrResult['Comercial'] = $lobjDatosGraficoT;

    //rotacion
    $lobjDatosGraficoT = collect($lobjDatosGrafico)->transform(function($larrValue, $lintKey){
        return [$larrValue->NMes3L, round(floatval($larrValue->RiesgoRotacion),2)*100 ];
                               })->toArray();
    $larrResult['Rotacion'] = $lobjDatosGraficoT;
    $lobjDatosGraficoT = collect($lobjDatosGrafico)->transform(function($larrValue, $lintKey){
      return [$larrValue->NMes3L, round(floatval($larrValue->RiesgoAusentismo),2)*100 ];
                             })->toArray();
    $larrResult['Ausentismo'] = $lobjDatosGraficoT;

    $lobjDatosGraficoT = collect($lobjDatosGrafico)->transform(function($larrValue, $lintKey){
      if ($larrValue->periodo == self::getPeriodo())
        { return  $larrValue; } } )->toArray();
    //echo count($lobjDatosGraficoT);
    if (count($lobjDatosGraficoT)){
      $larrResult['Informacion'] = array($lobjDatosGraficoT[count($lobjDatosGraficoT)-1]);
    }else{
      $larrResult['Informacion'] = array();
    }

    return $larrResult;
  }

  static public function LoadData($parrParametros, $pintIdCentro = null, $pintContratoId = null, $pintIdContratista = null){

    $larrResult = array();
    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');
    $lobjFiltro = \MySourcing::getFiltroUsuario(2,1);

    $lstrFechaFinal = \DB::table('tbl_contrato_maestro')->select('periodo')->max('periodo');

    //dotacion
    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjDotacion = $lobjConsulta->join('tbl_contrato_maestro','tbl_contrato_maestro.contrato_id','=','tbl_contrato.contrato_id')
                  ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
                  ->where('tbl_contrato_maestro.periodo','=',self::getPeriodo())
                  ->where(function ($query) use ($lobjFiltro) {
                      $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
                      });
                  if ($pintIdCentro){
                    $lobjDotacion = $lobjDotacion->whereExists(function ($query) use ($pintIdCentro) {
                      $query->select(\DB::raw(1))
                          ->from('tbl_contratos_centros')
                          ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
                          ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
                    });
                  }
                  if ($pintContratoId){
                    $lobjDotacion = $lobjDotacion->where('tbl_contrato.contrato_id','=',$pintContratoId);
                  }
                  if ($pintIdContratista){
                    $lobjDotacion = $lobjDotacion->where('tbl_contrato.IdContratista','=',$pintIdContratista)
                    ->where('tbl_contrato.transversal','=',1);
                  }
                  $lobjDotacion->select(\DB::raw('sum(tbl_contrato_maestro.dotacion) as dotacion'));
    $lobjDotacion = $lobjDotacion->first();
    if ($lobjDotacion->dotacion) {
        $lintDotacion = $lobjDotacion->dotacion;
    }else{
        $lintDotacion = 0;
    }

    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjDatos = $lobjConsulta->select(\DB::raw('sum(tbl_contrato_maestro.costo_laboral) as CostoLaboral'),
                                        \DB::raw('tbl_contrato_maestro.contrato_id as id_contrato'),
                                       \DB::raw('sum(tbl_contrato_maestro.pasivo_laboral) as PasivoLaboral'),
                                       \DB::raw('sum(tbl_contrato_maestro.obligaciones_laborales) as ObligacionesLaborales'),
                                       \DB::raw('sum(tbl_contrato_maestro.obligaciones_previsionales) as ObligacionesPrevisionales'),
                                       \DB::raw('avg(tbl_contrato_maestro.porcentaje_f) as PorcentajeFiniquitos'),
                                       \DB::raw('avg(tbl_contrato_maestro.porcentaje_ol) as PorcentajeOl'),
                                       \DB::raw('avg(tbl_contrato_maestro.porcentaje_op) as PorcentajeOp'),
                                       \DB::raw('sum(tbl_contrato_maestro.trabajadores_con_o) as TrabajadoresO'),
                                       \DB::raw('sum(tbl_contrato_maestro.trabajadores_con_ol) as TrabajadoresOl'),
                                       \DB::raw('sum(tbl_contrato_maestro.trabajadores_con_op) as TrabajadoresOp'),
                                       \DB::raw('sum(tbl_contrato_maestro.trabajadores_con_f) as TrabajadoresF'),
                                       \DB::raw('sum(tbl_contrato_maestro.trabajadores_con_of) as TrabajadoresOf'),
                                       \DB::raw("sum((tbl_contrato_maestro.documentacion)*(dotacion/".$lintDotacion."))  as Documentacion"),
                                       \DB::raw("sum((tbl_contrato_maestro.documentacion_mes_actual) * (dotacion/".$lintDotacion."))  as documentacion_mes_actual"),
                                       \DB::raw('sum(tbl_contrato_maestro.nc_generadas) as Nc_generadas'),
                                       \DB::raw('sum(tbl_contrato_maestro.nc_generadas_anterior) as Nc_generadas_anterior'))
                  ->join('tbl_contrato_maestro','tbl_contrato_maestro.contrato_id','=','tbl_contrato.contrato_id')
                  ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
                  ->where('tbl_contrato_maestro.periodo','=',self::getPeriodo())
                  ->where(function ($query) use ($lobjFiltro) {
                      $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
                      });
                  if ($pintIdCentro){
                    $lobjDatos = $lobjDatos->whereExists(function ($query) use ($pintIdCentro) {
                      $query->select(\DB::raw(1))
                          ->from('tbl_contratos_centros')
                          ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
                          ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
                    });
                  }
                  if ($pintContratoId){
                    $lobjDatos = $lobjDatos->where('tbl_contrato.contrato_id','=',$pintContratoId);
                  }
                  if ($pintIdContratista){
                    $lobjDatos = $lobjDatos->where('tbl_contrato.IdContratista','=',$pintIdContratista)
                    ->where('tbl_contrato.transversal','=',1);
                  }
    $lobjDatos = $lobjDatos->first();
    $larrResult = $lobjDatos;

    if ($pintContratoId){
      $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
      $lobjDatoContratoNombre = $lobjConsulta->select('tbl_contratistas.RUT',
                                                      'tbl_contratistas.RazonSocial',
                                                      'tbl_contrato.cont_proveedor',
                                                      'tbl_contrato.cont_numero as numero_contrato',
                                                      'tbl_centro.Descripcion as Faena',
                                                      'tbl_contrato_maestro.Comentarios',
                                                      \DB::raw("concat(tbl_contrato.cont_proveedor, ' - ', tbl_contratistas.RazonSocial) as cont_numero"))
                                ->join('tbl_contrato_maestro','tbl_contrato_maestro.contrato_id','=','tbl_contrato.contrato_id')
                                ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
                                 ->join('tbl_contratos_centros',function($table) {
                                    $table->on('tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
                                          ->on('tbl_contratos_centros.IdTipoCentro','=',\DB::raw('1'));
                                })
                                ->join('tbl_centro','tbl_contratos_centros.IdCentro','=','tbl_centro.IdCentro');
      $lobjDatoContratoNombre = $lobjDatoContratoNombre->where('tbl_contrato.contrato_id','=',$pintContratoId)
                                                       ->where('tbl_contrato_maestro.periodo','=',self::getPeriodo())
                                                       ->first();
      $larrResult['RUT'] = $lobjDatoContratoNombre->RUT;
      $larrResult['RazonSocial'] = $lobjDatoContratoNombre->RazonSocial;
      $larrResult['Servicio'] = $lobjDatoContratoNombre->cont_proveedor;
      $larrResult['cont_numero'] = $lobjDatoContratoNombre->cont_numero;
      $larrResult['numero_contrato'] = $lobjDatoContratoNombre->numero_contrato;
      $larrResult['Faena'] = $lobjDatoContratoNombre->Faena;
      $larrResult['Comentarios'] = $lobjDatoContratoNombre->Comentarios;

    }else{

      $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
      $lobjDatoContratoNombre = $lobjConsulta->select('tbl_contratistas.RUT',
                                                      'tbl_contratistas.RazonSocial',
                                                      'tbl_contrato.cont_proveedor as Servicio',
                                                      'tbl_centro.Descripcion as Faena',
                                                      'tbl_contrato_maestro.Dotacion',
                                                      'tbl_contrato_maestro.Documentacion',
                                                      'tbl_contrato_maestro.Comentarios',
                                                      \DB::raw("concat(tbl_contrato.cont_proveedor, ' - ', tbl_contratistas.RazonSocial) as cont_numero"))
                                ->join('tbl_contrato_maestro','tbl_contrato_maestro.contrato_id','=','tbl_contrato.contrato_id')
                                ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
                                 ->join('tbl_contratos_centros',function($table) {
                                    $table->on('tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
                                          ->on('tbl_contratos_centros.IdTipoCentro','=',\DB::raw('1'));
                                })
                                ->join('tbl_centro','tbl_contratos_centros.IdCentro','=','tbl_centro.IdCentro')
                                ->where('tbl_contrato.IdContratista','=',$pintIdContratista)
                                ->where('tbl_contrato_maestro.periodo','=',self::getPeriodo())
                                ->get();
      $larrResult['ListaContratos'] = $lobjDatoContratoNombre;
      if ($lobjDatoContratoNombre){
        $larrResult['Comentarios'] = "";
        foreach ($lobjDatoContratoNombre as $lrowData) {
          $larrResult['RUT'] = $lrowData->RUT;
          $larrResult['RazonSocial'] = $lrowData->RazonSocial;
          if (!$lrowData->Comentarios){
            $larrResult['Comentarios'] = $lrowData->Comentarios;
          }
        }
      }

    }

    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjDatosContrato = $lobjConsulta->select(\DB::raw('count(distinct(tbl_contrato_maestro.contrato_id)) as Contratos'),
                                    \DB::raw('count(distinct(tbl_contrato_maestro.idcontratista)) as Contratistas'),
                                    \DB::raw('count(distinct(tbl_personas.IdPersona)) as Dotacion'))
            ->join('tbl_contrato_maestro','tbl_contrato_maestro.contrato_id','=','tbl_contrato.contrato_id')
            ->leftjoin('tbl_personas_maestro',function($table) {
                    $table->on('tbl_personas_maestro.contrato_id','=','tbl_contrato_maestro.contrato_id')
                          ->on('tbl_personas_maestro.periodo','=','tbl_contrato_maestro.periodo');
            })
            ->leftjoin('tbl_personas','tbl_personas.idpersona','=','tbl_personas_maestro.idpersona')
            ->where('tbl_contrato_maestro.periodo','=',self::getPeriodo())
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
                });
            if ($pintIdCentro){
              $lobjDatosContrato = $lobjDatosContrato->whereExists(function ($query) use ($pintIdCentro) {
                $query->select(\DB::raw(1))
                    ->from('tbl_contratos_centros')
                    ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
                    ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
              });
            }
            if ($pintContratoId){
              $lobjDatosContrato = $lobjDatosContrato->where('tbl_contrato.contrato_id','=',$pintContratoId);
            }
            if ($pintIdContratista){
              $lobjDatosContrato = $lobjDatosContrato->where('tbl_contrato.IdContratista','=',$pintIdContratista)
              ->where('tbl_contrato.transversal','=',1);
            }
    $lobjDatosContrato = $lobjDatosContrato->first();
    $larrResult['Contratos'] = $lobjDatosContrato->Contratos;
    $larrResult['Contratistas'] = $lobjDatosContrato->Contratistas;
    $larrResult['Dotacion'] = $lobjDatosContrato->Dotacion;

    //Dotación
    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
		$lobjDatosGenero = $lobjConsulta->select('tbl_personas.rut',
                                     \DB::raw('case when tbl_personas.sexo = 1 then 1 else 0 end as Masculino'),
			                               \DB::raw('case when tbl_personas.sexo = 2 then 1 else 0 end as Femenino'),
			                          	   \DB::raw('case when tbl_personas.id_nac = 22 then 1 else 0 end as Chilena'),
										                 \DB::raw('case when tbl_personas.id_nac != 22 then 1 else 0 end as Extranjero'))
                                     ->distinct()
						->join('tbl_personas_maestro','tbl_personas_maestro.contrato_id','=','tbl_contrato.contrato_id')
						->leftjoin('tbl_personas','tbl_personas.idpersona','=','tbl_personas_maestro.idpersona')
            ->where('tbl_personas_maestro.periodo','=',self::getPeriodo())
            ->where(function ($query) use ($lobjFiltro) {
            		$query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
            		});
            if ($pintIdCentro){
              $lobjDatosGenero = $lobjDatosGenero->whereExists(function ($query) use ($pintIdCentro) {
                $query->select(\DB::raw(1))
                    ->from('tbl_contratos_centros')
                    ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
                    ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
              });
            }
            if ($pintContratoId){
              $lobjDatosGenero = $lobjDatosGenero->where('tbl_contrato.contrato_id','=',$pintContratoId);
            }
            if ($pintIdContratista){
              $lobjDatosGenero = $lobjDatosGenero->where('tbl_contrato.IdContratista','=',$pintIdContratista)
              ->where('tbl_contrato.transversal','=',1);
            }

    $lobjDatosGenero = \DB::table(\DB::raw("(".$lobjDatosGenero->toSql().") as t1"))
                       ->mergeBindings($lobjDatosGenero->getQuery())
                       ->select(\DB::raw('sum(t1.Masculino) as Masculino'),
                                \DB::raw('sum(t1.Femenino) as Femenino'),
                                \DB::raw('sum(t1.Chilena) as Chilena'),
                                \DB::raw('sum(t1.Extranjero) as Extranjero') )
                       ->first();
		$larrResult['Genero'] = array( 'Masculino'=>$lobjDatosGenero->Masculino, 'Femenino' => $lobjDatosGenero->Femenino );
		$larrResult['Nacionalidad'] = array( 'Chilena'=>$lobjDatosGenero->Chilena, 'Extranjero' => $lobjDatosGenero->Extranjero );

    //Riesgo
    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjDatosRiesgo = $lobjConsulta->select(\DB::raw('avg(riesgo_impuesto) as riesgo_impuesto'),
                                             \DB::raw('avg(riesgo_rotacion) as riesgo_rotacion'),
                                             \DB::raw('avg(riesgo_ausentismo) as riesgo_ausentismo'),
                                             \DB::raw('avg(riesgo_comercial) as riesgo_comercial'),
                                             \DB::raw('avg(riesgo) as riesgo'))
            ->join('tbl_riesgo','tbl_riesgo.contrato_id','=','tbl_contrato.contrato_id')
            ->where('tbl_riesgo.periodo','=',self::getPeriodo())
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
                });
            if ($pintIdCentro){
              $lobjDatosRiesgo = $lobjDatosRiesgo->whereExists(function ($query) use ($pintIdCentro) {
                $query->select(\DB::raw(1))
                    ->from('tbl_contratos_centros')
                    ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
                    ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
              });
            }
            if ($pintContratoId){
              $lobjDatosRiesgo = $lobjDatosRiesgo->where('tbl_contrato.contrato_id','=',$pintContratoId);
            }
            if ($pintIdContratista){
              $lobjDatosRiesgo = $lobjDatosRiesgo->where('tbl_contrato.IdContratista','=',$pintIdContratista)
              ->where('tbl_contrato.transversal','=',1);
            }
    $lobjDatosRiesgo = $lobjDatosRiesgo->first();
    $larrResult['Riesgo'] = array( 'Riesgo'=>$lobjDatosRiesgo->riesgo,
                                   'Impuesto'=>$lobjDatosRiesgo->riesgo_impuesto,
                                   'Comercial' => $lobjDatosRiesgo->riesgo_comercial,
                                   'Rotacion'=>$lobjDatosRiesgo->riesgo_rotacion,
                                   'Ausentismo' => $lobjDatosRiesgo->riesgo_ausentismo  );
    $lobjFiltroDos = \MySourcing::getFiltroUsuario(1,1);

    $lstrQueryDotacion = "(select tbl_contrato_maestro.periodo,
                             sum(dotacion) as dotacion
                             from tbl_contrato_maestro
                             inner join tbl_contrato on tbl_contrato.contrato_id = tbl_contrato_maestro.contrato_id
                             where tbl_contrato_maestro.contrato_id in (".$lobjFiltroDos['contratos'].")";
   if ($pintIdCentro){
     $lstrQueryDotacion = $lstrQueryDotacion." and exists (select 1
           from tbl_contratos_centros
           where tbl_contrato_maestro.contrato_id = tbl_contratos_centros.contrato_id
           and tbl_contratos_centros.IdCentro = ".$pintIdCentro." ) ";
   }
   if ($pintContratoId){
     $lstrQueryDotacion = $lstrQueryDotacion." and tbl_contrato_maestro.contrato_id = ".$pintContratoId;
   }
   if ($pintIdContratista){
      $lstrQueryDotacion = $lstrQueryDotacion." and tbl_contrato_maestro.IdContratista = ".$pintIdContratista." and tbl_contrato.transversal = 1";
    }
    $lstrQueryDotacion = $lstrQueryDotacion." group by tbl_contrato_maestro.periodo
                                             ) as tbl_contrato_maestro_total";

    $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
    $lobjDatosGrafico = $lobjConsulta->select('dim_tiempo.NMes3L',
                                     \DB::raw('sum(tbl_contrato_maestro.documentacion*(tbl_contrato_maestro.dotacion/tbl_contrato_maestro_total.dotacion)) as documentacion'))
            ->join('tbl_contrato_maestro','tbl_contrato.contrato_id','=','tbl_contrato_maestro.contrato_id')
            ->join(\DB::raw($lstrQueryDotacion),'tbl_contrato_maestro_total.periodo','=','tbl_contrato_maestro.periodo')
            ->join('dim_tiempo','dim_tiempo.fecha','=','tbl_contrato_maestro.periodo')
            ->whereraw('dim_tiempo.fecha <= \''.self::getPeriodo().'\'')
            ->whereraw('dim_tiempo.fecha >= DATE_SUB(\''.self::getPeriodo().'\', INTERVAL 11 MONTH)')
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
                })
            ->groupBy('dim_tiempo.anio')
            ->groupBy('dim_tiempo.mes')
            ->groupBy('dim_tiempo.NMes3L')
            ->groupBy('tbl_contrato_maestro_total.dotacion');
            if ($pintIdCentro){
              $lobjDatosGrafico = $lobjDatosGrafico->whereExists(function ($query) use ($pintIdCentro) {
                $query->select(\DB::raw(1))
                    ->from('tbl_contratos_centros')
                    ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
                    ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
              });
            }
            if ($pintContratoId){
              $lobjDatosGrafico = $lobjDatosGrafico->where('tbl_contrato.contrato_id','=',$pintContratoId);
            }
             if ($pintIdContratista){
              $lobjDatosGrafico = $lobjDatosGrafico->where('tbl_contrato.IdContratista','=',$pintIdContratista)
              ->where('tbl_contrato.transversal','=',1);
            }
    $lobjDatosGrafico = $lobjDatosGrafico->orderBy('dim_tiempo.anio','asc')
                      ->orderBy('dim_tiempo.mes','asc')
                      ->get();
    $lobjDatosGrafico = collect($lobjDatosGrafico)->transform(function($larrValue, $lintKey){
      return [$larrValue->NMes3L, round(floatval($larrValue->documentacion),2)*100 ];
                             })->toArray();
    $larrResult['LineaTiempo'] = $lobjDatosGrafico;

    $lobjDatosGrafico2 = $lobjConsulta->select('dim_tiempo.NMes3L',
                                     \DB::raw('sum(tbl_contrato_maestro.documentacion_mes_actual*(tbl_contrato_maestro.dotacion/tbl_contrato_maestro_total.dotacion)) as documentacion'))
            ->join('tbl_contrato_maestro','tbl_contrato.contrato_id','=','tbl_contrato_maestro.contrato_id')
            ->join(\DB::raw($lstrQueryDotacion),'tbl_contrato_maestro_total.periodo','=','tbl_contrato_maestro.periodo')
            ->join('dim_tiempo','dim_tiempo.fecha','=','tbl_contrato_maestro.periodo')
            ->whereraw('dim_tiempo.fecha <= \''.self::getPeriodo().'\'')
            ->whereraw('dim_tiempo.fecha >= DATE_SUB(\''.self::getPeriodo().'\', INTERVAL 11 MONTH)')
            ->where(function ($query) use ($lobjFiltro) {
                $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
                })
            ->groupBy('dim_tiempo.anio')
            ->groupBy('dim_tiempo.mes')
            ->groupBy('dim_tiempo.NMes3L')
            ->groupBy('tbl_contrato_maestro_total.dotacion');
            if ($pintIdCentro){
              $lobjDatosGrafico2 = $lobjDatosGrafico2->whereExists(function ($query) use ($pintIdCentro) {
                $query->select(\DB::raw(1))
                    ->from('tbl_contratos_centros')
                    ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
                    ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
              });
            }
            if ($pintContratoId){
              $lobjDatosGrafico2 = $lobjDatosGrafico2->where('tbl_contrato.contrato_id','=',$pintContratoId);
            }
             if ($pintIdContratista){
              $lobjDatosGrafico2 = $lobjDatosGrafico2->where('tbl_contrato.IdContratista','=',$pintIdContratista)
              ->where('tbl_contrato.transversal','=',1);
            }
    $lobjDatosGrafico2 = $lobjDatosGrafico2->orderBy('dim_tiempo.anio','asc')
                      ->orderBy('dim_tiempo.mes','asc')
                      ->get();
    $lobjDatosGrafico2 = collect($lobjDatosGrafico2)->transform(function($larrValue, $lintKey){
      return [$larrValue->NMes3L, round(floatval($larrValue->documentacion),2)*100 ];
                             })->toArray();
    $larrResult['LineaTiempoMesActual'] = $lobjDatosGrafico2;

    //Listado de contratoscentros
    if (!$pintContratoId || !$pintIdContratista){
      $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
      $lobjListadoContrato = $lobjConsulta->join('tbl_contrato_maestro','tbl_contrato_maestro.contrato_id','=','tbl_contrato.contrato_id')
              ->join('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_contrato_maestro.contrato_id')
              ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
              ->join('tbl_centro',function($tabla){
                  $tabla->on('tbl_contratos_centros.IdCentro', '=', 'tbl_centro.IdCentro');
              })
              ->where('tbl_contrato_maestro.periodo','=',self::getPeriodo())
              ->where(function ($query) use ($lobjFiltro) {
                  $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
                  })
              ->orderBy('tbl_centro.Descripcion','asc');
              if ($pintIdCentro){
                $lobjListadoContrato = $lobjListadoContrato->select('tbl_centro.IdCentro as IdFaena', 'tbl_centro.Descripcion as Faena', 'tbl_contrato.cont_numero', 'tbl_contrato.cont_nombre', 'tbl_contrato.contrato_id', "tbl_contrato.cont_proveedor", \DB::raw("concat(tbl_contrato.cont_proveedor, ' - ', tbl_contratistas.RazonSocial )  as Contratista"))->orderBy('Contratista','asc')
                ->where('tbl_centro.IdCentro','=',$pintIdCentro);
              }else if ($pintIdContratista){
                $lobjListadoContrato = $lobjListadoContrato->select('tbl_centro.IdCentro as IdFaena', 'tbl_centro.Descripcion as Faena', 'tbl_contrato.cont_numero', 'tbl_contrato.cont_nombre', 'tbl_contrato.contrato_id', "tbl_contrato.cont_proveedor", \DB::raw("concat(tbl_contrato.cont_proveedor, ' - ', tbl_contratistas.RazonSocial )  as Contratista"))->orderBy('Contratista','asc')
                ->where('tbl_contrato.IdContratista','=',$pintIdContratista)
                ->where('tbl_contrato.transversal','=',1);

              }else{
                $lobjListadoContrato = $lobjListadoContrato->select('tbl_centro.IdCentro as IdFaena', 'tbl_centro.Descripcion as Faena')->distinct()->orderBy('Faena','asc');
              }
      $lobjListadoContrato = $lobjListadoContrato->get();
      $larrResult['ListadoContratos'] = $lobjListadoContrato;

      $lobjContratoTransversal = Contratos::where('tbl_contrato.transversal',1)
      ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
      ->select('tbl_contratistas.RazonSocial','tbl_contratistas.IdContratista')->distinct()->get();

      $larrResult['ListadoTransversales'] = $lobjContratoTransversal;

      // listados de contratos con Detalle
      if ($pintIdCentro){
        $lobjConsulta = \MySourcing::ConvierteConsultaFiltro($parrParametros);
        $lobjListadoContratoDetalle = $lobjConsulta->join('tbl_contrato_maestro','tbl_contrato_maestro.contrato_id','=','tbl_contrato.contrato_id')
                ->join('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_contrato_maestro.contrato_id')
                ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
                ->join('tbl_centro',function($tabla){
                    $tabla->on('tbl_contratos_centros.IdCentro', '=', 'tbl_centro.IdCentro');
                })
                ->where('tbl_contrato_maestro.periodo','=',self::getPeriodo())
                ->where(function ($query) use ($lobjFiltro) {
                    $query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
                    })
                ->whereExists(function ($query) use ($pintIdCentro) {
                  $query->select(\DB::raw(1))
                      ->from('tbl_contratos_centros')
                      ->whereRaw('tbl_contrato.contrato_id = tbl_contratos_centros.contrato_id')
                      ->whereRaw('tbl_contratos_centros.IdCentro = '.$pintIdCentro);
                })
                ->select(\DB::raw("concat(tbl_contrato.cont_proveedor, ' - ', tbl_contratistas.RazonSocial )  as Contratista"),
                         \DB::raw('tbl_contrato_maestro.Documentacion*100 as Global'),
                         \DB::raw('tbl_contrato_maestro.porcentaje_ol*100 as PorcentajeOl'),
                         \DB::raw('tbl_contrato_maestro.porcentaje_op*100 as PorcentajeOp'),
                         \DB::raw('tbl_contrato_maestro.porcentaje_f*100 as Finiquitos'),
                         \DB::raw('tbl_contrato_maestro.Documentacion*100 as Documentacion')
                         )
                ->distinct()
                ->orderBy('Contratista','asc')
                ->get();
        $larrResult['ListadoContratosDetalle'] = $lobjListadoContratoDetalle;
      }else{
        $larrResult['ListadoContratosDetalle'] = array();
      }

    }

    if (self::getPeriodo()){
        $lobjLinea = \DB::table('dim_tiempo')
                      ->select('dim_tiempo.NMes3L')
                      ->whereraw('fecha <= \''.self::getPeriodo().'\'')
                      ->whereraw('fecha >= DATE_SUB(\''.self::getPeriodo().'\', INTERVAL 6 MONTH)')
                      ->where('dia',1)
                      ->groupBy('dim_tiempo.anio', 'dim_tiempo.mes', 'dim_tiempo.NMes3L')
                      ->orderBy('dim_tiempo.anio','asc')
                      ->orderBy('dim_tiempo.mes','asc')
                      ->pluck('dim_tiempo.NMes3L');
      $larrResult['LineaTiempoEtiquetas'] = $lobjLinea;
    }else{
      $larrResult['LineaTiempoEtiquetas'] = array();
    }

    return $larrResult;

  }

  static private function Load($pobjFileLoad, $pstrDirectory=null){
    $lobjFileLoad = $pobjFileLoad;
    $filename = $lobjFileLoad->getClientOriginalName();
    $extension = $lobjFileLoad->getClientOriginalExtension();
    $rand = rand(1000,100000000);
    $lstrFileName = strtotime(date('Y-m-d H:i:s')).'-'.$rand.'.'.$extension;
    $lstrDirectory = storage_path($pstrDirectory);
    $uploadSuccess = $lobjFileLoad->move($lstrDirectory, $lstrFileName);
    return array("code"=>1, "directory"=>$lstrDirectory, "filename" => $lstrFileName);
  }
  static public function LoadDiferencias($pobjFileLoad){

    $larrResult = self::Load($pobjFileLoad, 'uploads/checklaboral/diferencias/');
    $lintFila = 1;
    $lintFilaInicio = 10;

    if ($larrResult && $larrResult['code']==1){

      // Se eliminan los datos de la tabla
      Diferenciacalculo::where("periodo",self::getPeriodo())->delete();
      $lstrFileName = $larrResult['directory'].$larrResult['filename'];

      $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
      $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($lstrFileName);
      $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

      $spreadsheet = $reader->load($lstrFileName);

      $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
      $larrFormato = self::getFormato();

      //Validar si la fecha que trae el archivo en la celda B6 es igual a self::getPeriodo() devolver con mensaje de que el archivo no corresponde al periodo

      foreach ($sheetData as $row) {

          if ($lintFila > $lintFilaInicio) {
            $lstrContNumero = isset($larrFormato['cont_numero']) ? strtoupper(trim(isset($row[$larrFormato['cont_numero']])?$row[$larrFormato['cont_numero']]:'')) : '';
            $lstrRut = isset($larrFormato['rut']) ? strtoupper(trim(isset($row[$larrFormato['rut']])?$row[$larrFormato['rut']]:'')) : '';

            if ($lstrContNumero) {

              $lobjContrato = Contratos::where('cont_numero',$lstrContNumero)->select('contrato_id')->first();
              if ($lobjContrato) {
                $lintContratoId = $lobjContrato->contrato_id;
              }else{
                $lintContratoId = 0;
              }

              $lobjPersona = Personas::where('rut',$lstrRut)->select('IdPersona')->first();
              if ($lobjPersona) {
                $lintIdPersona = $lobjPersona->IdPersona;
              }else{
                $lintIdPersona = 0;
              }

              if ($lintIdPersona && $lintContratoId){
                $lintIdEstatus = 1;
              }else{
                if ($lintIdPersona){
                  $lintIdEstatus = 2;
                }
                if ($lintContratoId){
                  $lintIdEstatus = 3;
                }
              }

              $lobjDiferenciacalculo = new Diferenciacalculo();
              $lobjDiferenciacalculo->periodo = self::getPeriodo();
              $lobjDiferenciacalculo->contrato_id = $lintContratoId;
              $lobjDiferenciacalculo->cont_numero = isset($larrFormato['cont_numero']) ? strtoupper(trim(isset($row[$larrFormato['cont_numero']])?$row[$larrFormato['cont_numero']]:'')) : '';
              $lobjDiferenciacalculo->IdPersona = $lintIdPersona;
              $lobjDiferenciacalculo->rut = $lstrRut;
              $lobjDiferenciacalculo->nombre = isset($larrFormato['nombre']) ? strtoupper(trim(isset($row[$larrFormato['nombre']])?$row[$larrFormato['nombre']]:'')) : '';
              $lobjDiferenciacalculo->sueldo_base = self::FormatNumber(isset($larrFormato['sueldo_base']) ? strtoupper(trim(isset($row[$larrFormato['sueldo_base']])?$row[$larrFormato['sueldo_base']]:'')) : '');
              $lobjDiferenciacalculo->gratificacion_legal = self::FormatNumber(isset($larrFormato['gratificacion_legal']) ? strtoupper(trim(isset($row[$larrFormato['gratificacion_legal']])?$row[$larrFormato['gratificacion_legal']]:'')) : '');
              $lobjDiferenciacalculo->horas_extras = self::FormatNumber(isset($larrFormato['horas_extras']) ? strtoupper(trim(isset($row[$larrFormato['horas_extras']])?$row[$larrFormato['horas_extras']]:'')) : '');
              $lobjDiferenciacalculo->otros_imponibles = self::FormatNumber(isset($larrFormato['otros_imponibles']) ? strtoupper(trim(isset($row[$larrFormato['otros_imponibles']])?$row[$larrFormato['otros_imponibles']]:'')) : '');
              $lobjDiferenciacalculo->no_imponible = self::FormatNumber(isset($larrFormato['no_imponible']) ? strtoupper(trim(isset($row[$larrFormato['no_imponible']])?$row[$larrFormato['no_imponible']]:'')) : '');
              $lobjDiferenciacalculo->impuesto = self::FormatNumber(isset($larrFormato['impuesto']) ? strtoupper(trim(isset($row[$larrFormato['impuesto']])?$row[$larrFormato['impuesto']]:'')) : '');
              $lobjDiferenciacalculo->otros_descuentos = self::FormatNumber(isset($larrFormato['otros_descuentos']) ? strtoupper(trim(isset($row[$larrFormato['otros_descuentos']])?$row[$larrFormato['otros_descuentos']]:'')) : '');
              $lobjDiferenciacalculo->ol_diferencia_calculo = self::FormatNumber(isset($larrFormato['ol_diferencia_calculo']) ? strtoupper(trim(isset($row[$larrFormato['ol_diferencia_calculo']])?$row[$larrFormato['ol_diferencia_calculo']]:'')) : '');
              $lobjDiferenciacalculo->ol_diferencia_pago = self::FormatNumber(isset($larrFormato['ol_diferencia_pago']) ? strtoupper(trim(isset($row[$larrFormato['ol_diferencia_pago']])?$row[$larrFormato['ol_diferencia_pago']]:'')) : '');
              $lobjDiferenciacalculo->afp = self::FormatNumber(isset($larrFormato['afp']) ? strtoupper(trim(isset($row[$larrFormato['afp']])?$row[$larrFormato['afp']]:'')) : '');
              $lobjDiferenciacalculo->ahorro_voluntario = self::FormatNumber(isset($larrFormato['ahorro_voluntario']) ? strtoupper(trim(isset($row[$larrFormato['ahorro_voluntario']])?$row[$larrFormato['ahorro_voluntario']]:'')) : '');
              $lobjDiferenciacalculo->salud = self::FormatNumber(isset($larrFormato['salud']) ? strtoupper(trim(isset($row[$larrFormato['salud']])?$row[$larrFormato['salud']]:'')) : '');
              $lobjDiferenciacalculo->salud_voluntario = self::FormatNumber(isset($larrFormato['salud_voluntario']) ? strtoupper(trim(isset($row[$larrFormato['salud_voluntario']])?$row[$larrFormato['salud_voluntario']]:'')) : '');
              $lobjDiferenciacalculo->ccaf = self::FormatNumber(isset($larrFormato['ccaf']) ? strtoupper(trim(isset($row[$larrFormato['ccaf']])?$row[$larrFormato['ccaf']]:'')) : '');
              $lobjDiferenciacalculo->afc = self::FormatNumber(isset($larrFormato['afc']) ? strtoupper(trim(isset($row[$larrFormato['afc']])?$row[$larrFormato['afc']]:'')) : '');
              $lobjDiferenciacalculo->trabajo_pesado = self::FormatNumber(isset($larrFormato['trabajo_pesado']) ? strtoupper(trim(isset($row[$larrFormato['trabajo_pesado']])?$row[$larrFormato['trabajo_pesado']]:'')) : '');
              $lobjDiferenciacalculo->subtotal_previsiones = self::FormatNumber(isset($larrFormato['subtotal_previsiones']) ? strtoupper(trim(isset($row[$larrFormato['subtotal_previsiones']])?$row[$larrFormato['subtotal_previsiones']]:'')) : '');
              $lobjDiferenciacalculo->sis = self::FormatNumber(isset($larrFormato['sis']) ? strtoupper(trim(isset($row[$larrFormato['sis']])?$row[$larrFormato['sis']]:'')) : '');
              $lobjDiferenciacalculo->afc_empleador = self::FormatNumber(isset($larrFormato['afc_empleador']) ? strtoupper(trim(isset($row[$larrFormato['afc_empleador']])?$row[$larrFormato['afc_empleador']]:'')) : '');
              $lobjDiferenciacalculo->trabajo_pesado_empleador = self::FormatNumber(isset($larrFormato['trabajo_pesado_empleador']) ? strtoupper(trim(isset($row[$larrFormato['trabajo_pesado_empleador']])?$row[$larrFormato['trabajo_pesado_empleador']]:'')) : '');
              $lobjDiferenciacalculo->mutualidad = self::FormatNumber(isset($larrFormato['mutualidad']) ? strtoupper(trim(isset($row[$larrFormato['mutualidad']])?$row[$larrFormato['mutualidad']]:'')) : '');
              $lobjDiferenciacalculo->subtotal_previsiones_empleador = self::FormatNumber(isset($larrFormato['subtotal_previsiones_empleador']) ? strtoupper(trim(isset($row[$larrFormato['subtotal_previsiones_empleador']])?$row[$larrFormato['subtotal_previsiones_empleador']]:'')) : '');
              $lobjDiferenciacalculo->op_diferencia_calculo = self::FormatNumber(isset($larrFormato['op_diferencia_calculo']) ? strtoupper(trim(isset($row[$larrFormato['op_diferencia_calculo']])?$row[$larrFormato['op_diferencia_calculo']]:'')) : '');
              $lobjDiferenciacalculo->op_diferencia_pago = self::FormatNumber(isset($larrFormato['op_diferencia_pago']) ? strtoupper(trim(isset($row[$larrFormato['op_diferencia_pago']])?$row[$larrFormato['op_diferencia_pago']]:'')) : '');
              $lobjDiferenciacalculo->sl_diferencia_calculo = self::FormatNumber(isset($larrFormato['sl_diferencia_calculo']) ? strtoupper(trim(isset($row[$larrFormato['sl_diferencia_calculo']])?$row[$larrFormato['sl_diferencia_calculo']]:'')) : '');
              $lobjDiferenciacalculo->sl_diferencia_pago = self::FormatNumber(isset($larrFormato['sl_diferencia_pago']) ? strtoupper(trim(isset($row[$larrFormato['sl_diferencia_pago']])?$row[$larrFormato['sl_diferencia_pago']]:'')) : '');
              $lobjDiferenciacalculo->ias = self::FormatNumber(isset($larrFormato['ias']) ? strtoupper(trim(isset($row[$larrFormato['ias']])?$row[$larrFormato['ias']]:'')) : '');
              $lobjDiferenciacalculo->vacaciones = self::FormatNumber(isset($larrFormato['vacaciones']) ? strtoupper(trim(isset($row[$larrFormato['vacaciones']])?$row[$larrFormato['vacaciones']]:'')) : '');
              $lobjDiferenciacalculo->otros = self::FormatNumber(isset($larrFormato['otros']) ? strtoupper(trim(isset($row[$larrFormato['otros']])?$row[$larrFormato['otros']]:'')) : '');
              $lobjDiferenciacalculo->fl_diferencia_calculo = self::FormatNumber(isset($larrFormato['fl_diferencia_calculo']) ? strtoupper(trim(isset($row[$larrFormato['fl_diferencia_calculo']])?$row[$larrFormato['fl_diferencia_calculo']]:'')) : '');
              $lobjDiferenciacalculo->fl_diferencia_pago = self::FormatNumber(isset($larrFormato['fl_diferencia_pago']) ? strtoupper(trim(isset($row[$larrFormato['fl_diferencia_pago']])?$row[$larrFormato['fl_diferencia_pago']]:'')) : '');
              $lobjDiferenciacalculo->calculado = self::FormatNumber(isset($larrFormato['calculado']) ? strtoupper(trim(isset($row[$larrFormato['calculado']])?$row[$larrFormato['calculado']]:'')) : '');
              $lobjDiferenciacalculo->pagado = self::FormatNumber(isset($larrFormato['pagado']) ? strtoupper(trim(isset($row[$larrFormato['pagado']])?$row[$larrFormato['pagado']]:'')) : '');
              $lobjDiferenciacalculo->cl_diferencia_calculo = self::FormatNumber(isset($larrFormato['cl_diferencia_calculo']) ? strtoupper(trim(isset($row[$larrFormato['cl_diferencia_calculo']])?$row[$larrFormato['cl_diferencia_calculo']]:'')) : '');
              $lobjDiferenciacalculo->cl_diferencia_pago = self::FormatNumber(isset($larrFormato['cl_diferencia_pago']) ? strtoupper(trim(isset($row[$larrFormato['cl_diferencia_pago']])?$row[$larrFormato['cl_diferencia_pago']]:'')) : '');
              $lobjDiferenciacalculo->diferencia_favor_trabajador = self::FormatNumber(isset($larrFormato['diferencia_favor_trabajador']) ? strtoupper(trim(isset($row[$larrFormato['diferencia_favor_trabajador']])?$row[$larrFormato['diferencia_favor_trabajador']]:'')) : '');
              $lobjDiferenciacalculo->diferencia_favor_empleador = self::FormatNumber(isset($larrFormato['diferencia_favor_empleador']) ? strtoupper(trim(isset($row[$larrFormato['diferencia_favor_empleador']])?$row[$larrFormato['diferencia_favor_empleador']]:'')) : '');
              $lobjDiferenciacalculo->limite = self::FormatNumber(isset($larrFormato['limite']) ? strtoupper(trim(isset($row[$larrFormato['limite']])?$row[$larrFormato['limite']]:'')) : '');
              $lobjDiferenciacalculo->IdEstatus = $lintIdEstatus; // por validar
              $lobjDiferenciacalculo->entry_by = self::$gintIdUser;
              $lobjDiferenciacalculo->updated_by = self::$gintIdUser;
              $lobjDiferenciacalculo->save();

            }
          }
          $lintFila += 1;
      }

      //Procesamos los totalizados
      self::UpdateCalculo();

      $lstrObservacion = self::getObservation('diferencias');

      return json_encode(array("code"=>1, "observations"=>$lstrObservacion));

    }else{
      return $larrResult;
    }

  }

  static public function LoadNoConformidades($pobjFileLoad){

    $larrResult = self::Load($pobjFileLoad, 'uploads/checklaboral/diferencias/');
    $lintFila = 1;
    $lintFilaInicio = 2;

    if ($larrResult && $larrResult['code']==1){

      // Se eliminan los datos de la tabla
      Diferenciancpersonas::where("periodo",self::getPeriodo())->delete();
      $lstrFileName = $larrResult['directory'].$larrResult['filename'];

      $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
      $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($lstrFileName);
      $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

      $spreadsheet = $reader->load($lstrFileName);

      $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
      $larrFormato = self::getFormato(1);

      //Validar si la fecha que trae el archivo en la celda B6 es igual a self::getPeriodo() devolver con mensaje de que el archivo no corresponde al periodo

      foreach ($sheetData as $row) {

        if ($lintFila > $lintFilaInicio) {

        $lstrContNumero = isset($larrFormato['cont_numero']) ? strtoupper(trim(isset($row[$larrFormato['cont_numero']])?$row[$larrFormato['cont_numero']]:'')) : '';
        $lstrRut = isset($larrFormato['rut']) ? strtoupper(trim(isset($row[$larrFormato['rut']])?$row[$larrFormato['rut']]:'')) : '';
        $lstrTipoDocumento = isset($larrFormato['documento']) ? strtoupper(trim(isset($row[$larrFormato['documento']])?$row[$larrFormato['documento']]:'')) : '';

        if ($lstrContNumero) {

          $lobjContrato = Contratos::where('cont_numero',$lstrContNumero)->select('contrato_id')->first();
          if ($lobjContrato) {
            $lintContratoId = $lobjContrato->contrato_id;
          }else{
            $lintContratoId = 0;
          }

          $lobjPersona = Personas::where('rut',$lstrRut)->select('IdPersona')->first();
          if ($lobjPersona) {
            $lintIdPersona = $lobjPersona->IdPersona;
          }else{
            $lintIdPersona = 0;
          }

          $lobjTipoDocumento = TipoDocumentos::where('Descripcion',$lstrTipoDocumento)->select('IdTipoDocumento')->first();
          if ($lobjTipoDocumento) {
            $lintIdTipoDocumento = $lobjTipoDocumento->IdTipoDocumento;
          }else{
            $lintIdTipoDocumento = 0;
          }

          if ($lintIdPersona && $lintContratoId && $lintIdTipoDocumento){
            $lintIdEstatus = 1;
          }else{
            if (!$lintIdPersona){
              $lintIdEstatus = 2;
            }
            if (!$lintContratoId){
              $lintIdEstatus = 3;
            }
            if (!$lintIdTipoDocumento){
              $lintIdEstatus = 4;
            }
          }

          $lobjDiferenciapersonas = new Diferenciancpersonas();
          $lobjDiferenciapersonas->periodo = self::getPeriodo();
          $lobjDiferenciapersonas->contrato_id = $lintContratoId;
          $lobjDiferenciapersonas->cont_numero = isset($larrFormato['cont_numero']) ? strtoupper(trim(isset($row[$larrFormato['cont_numero']])?$row[$larrFormato['cont_numero']]:'')) : '';
          $lobjDiferenciapersonas->IdPersona = $lintIdPersona;
          $lobjDiferenciapersonas->rut = $lstrRut;
          $lobjDiferenciapersonas->nombre = isset($larrFormato['nombre']) ? strtoupper(trim(isset($row[$larrFormato['nombre']])?$row[$larrFormato['nombre']]:'')) : '';
          $lobjDiferenciapersonas->IdTipoDocumento = $lintIdTipoDocumento;
          $lobjDiferenciapersonas->TipoDocumento = $lstrTipoDocumento;
          $lobjDiferenciapersonas->IdEstatus = $lintIdEstatus;
          $lobjDiferenciapersonas->IdEstatusDocumento = isset($larrFormato['idestado']) ? strtoupper(trim(isset($row[$larrFormato['idestado']])?$row[$larrFormato['idestado']]:'')) : '';
          $lobjDiferenciapersonas->EstatusDocumento =isset($larrFormato['estado']) ? strtoupper(trim(isset($row[$larrFormato['estado']])?$row[$larrFormato['estado']]:'')) : '';
          $lobjDiferenciapersonas->Resultado = isset($larrFormato['comentario']) ? strtoupper(trim(isset($row[$larrFormato['comentario']])?$row[$larrFormato['comentario']]:'')) : '';
          $lobjDiferenciapersonas->entry_by = self::$gintIdUser;
          $lobjDiferenciapersonas->updated_by = self::$gintIdUser;
          $lobjDiferenciapersonas->save();

        }
      }
      $lintFila += 1;
    }

    //Procesamos totalizados
    self::UpdateDocument();

    $lstrObservacion = self::getObservation('noconformidades');

    return json_encode(array("code"=>1, "observations"=>$lstrObservacion));

    }else{
      return $larrResult;
    }

  }

  static public function LoadNoConformidadesEmpresa($pobjFileLoad){

    $larrResult = self::Load($pobjFileLoad, 'uploads/checklaboral/diferencias/');
    $lintFila = 1;
    $lintFilaInicio = 2;

    if ($larrResult && $larrResult['code']==1){

      // Se eliminan los datos de la tabla
      Diferenciancempresas::where("periodo",self::getPeriodo())->delete();
      $lstrFileName = $larrResult['directory'].$larrResult['filename'];

      $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
      $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($lstrFileName);
      $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

      $spreadsheet = $reader->load($lstrFileName);

      $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
      $larrFormato = self::getFormato(2);

      //Validar si la fecha que trae el archivo en la celda B6 es igual a self::getPeriodo() devolver con mensaje de que el archivo no corresponde al periodo

      foreach ($sheetData as $row) {

        if ($lintFila > $lintFilaInicio) {

          $lstrContNumero = str_ireplace("NULL","",isset($larrFormato['cont_numero']) ? strtoupper(trim(isset($row[$larrFormato['cont_numero']])?$row[$larrFormato['cont_numero']]:'')) : '');
          $lstrRutEmpresa = isset($larrFormato['rut_empresa']) ? strtoupper(trim(isset($row[$larrFormato['rut_empresa']])?$row[$larrFormato['rut_empresa']]:'')) : '';
          $lstrTipoDocumento = isset($larrFormato['documento']) ? strtoupper(trim(isset($row[$larrFormato['documento']])?$row[$larrFormato['documento']]:'')) : '';

          $lobjContrato = Contratos::where('cont_numero',$lstrContNumero)->select('contrato_id')->first();
          if ($lobjContrato) {
            $lintContratoId = $lobjContrato->contrato_id;
          }else{
            $lintContratoId = 0;
          }

          $lobjContratista = Contratistas::where('rut',$lstrRutEmpresa)->select('IdContratista')->first();
          if ($lobjContratista) {
            $lintIdContratista = $lobjContratista->IdContratista;
          }else{
            $lintIdContratista = 0;
          }

          $lobjTipoDocumento = TipoDocumentos::where('Descripcion',$lstrTipoDocumento)->select('IdTipoDocumento')->first();
          if ($lobjTipoDocumento) {
            $lintIdTipoDocumento = $lobjTipoDocumento->IdTipoDocumento;
          }else{
            $lintIdTipoDocumento = 0;
          }

          if ($lintIdContratista && ($lintContratoId || (!$lintContratoId && !$lstrContNumero) ) && $lintIdTipoDocumento){
            $lintIdEstatus = 1;
          }else{
            if (!$lintIdContratista){
              $lintIdEstatus = 2;
            }
            if (!$lintContratoId && $lstrContNumero){
              $lintIdEstatus = 3;
            }
            if (!$lintIdTipoDocumento){
              $lintIdEstatus = 4;
            }
          }

          $lobjDiferenciaempresas = new Diferenciancempresas();
          $lobjDiferenciaempresas->periodo = self::getPeriodo();
          $lobjDiferenciaempresas->mes = isset($larrFormato['mes']) ? strtoupper(trim(isset($row[$larrFormato['mes']])?$row[$larrFormato['mes']]:'')) : '';
          $lobjDiferenciaempresas->contrato_id = $lintContratoId;
          $lobjDiferenciaempresas->cont_numero = str_ireplace("NULL","",isset($larrFormato['cont_numero']) ? strtoupper(trim(isset($row[$larrFormato['cont_numero']])?$row[$larrFormato['cont_numero']]:'')) : '');
          $lobjDiferenciaempresas->IdContratista = $lintIdContratista;
          $lobjDiferenciaempresas->rut_empresa = $lstrRutEmpresa;
          $lobjDiferenciaempresas->IdTipoDocumento = $lintIdTipoDocumento;
          $lobjDiferenciaempresas->TipoDocumento = $lstrTipoDocumento;
          $lobjDiferenciaempresas->IdEstatus = $lintIdEstatus;
          $lobjDiferenciaempresas->IdEstatusDocumento = isset($larrFormato['idestado']) ? strtoupper(trim(isset($row[$larrFormato['idestado']])?$row[$larrFormato['idestado']]:'')) : '';
          $lobjDiferenciaempresas->EstatusDocumento =isset($larrFormato['estado']) ? strtoupper(trim(isset($row[$larrFormato['estado']])?$row[$larrFormato['estado']]:'')) : '';
          $lobjDiferenciaempresas->Resultado = isset($larrFormato['comentario']) ? strtoupper(trim(isset($row[$larrFormato['comentario']])?$row[$larrFormato['comentario']]:'')) : '';
          $lobjDiferenciaempresas->entry_by = self::$gintIdUser;
          $lobjDiferenciaempresas->updated_by = self::$gintIdUser;
          $lobjDiferenciaempresas->save();

        }
        $lintFila += 1;
      }

      //Procesamos totalizados
      self::UpdateDocument();

      $lstrObservacion = self::getObservation('noconformidadesempresa');

      return json_encode(array("code"=>1, "observations"=>$lstrObservacion));

    }else{
      return $larrResult;
    }

  }

  static public function LoadRiesgo($pobjFileLoad){

    $larrResult = self::Load($pobjFileLoad, 'uploads/checklaboral/diferencias/');
    $lintFila = 1;
    $lintFilaInicio = 6;
    $lintIdEstatus = 1;

    if ($larrResult && $larrResult['code']==1){

      // Se eliminan los datos de la tabla
      Riesgo::where("periodo",self::getPeriodo())->delete();
      $lstrFileName = $larrResult['directory'].$larrResult['filename'];

      $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
      $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($lstrFileName);
      $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

      $spreadsheet = $reader->load($lstrFileName);

      $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
      $larrFormato = self::getFormato(3);

      //Validar si la fecha que trae el archivo en la celda B6 es igual a self::getPeriodo() devolver con mensaje de que el archivo no corresponde al periodo
      foreach ($sheetData as $row) {

        if ($lintFila > $lintFilaInicio) {
          $lstrContNumero = isset($larrFormato['cont_numero']) ? strtoupper(trim(isset($row[$larrFormato['cont_numero']])?$row[$larrFormato['cont_numero']]:'')) : '';

          $lobjContrato = Contratos::where('cont_numero',$lstrContNumero)->select('contrato_id')->first();
          if ($lobjContrato) {
            $lintContratoId = $lobjContrato->contrato_id;
            $lintIdEstatus = 1;
          }else{
            $lintContratoId = 0;
            $lintIdEstatus = 2;
          }

          $lobjRiesgo = new Riesgo();
          $lobjRiesgo->periodo = self::getPeriodo();
          $lobjRiesgo->contrato_id = $lintContratoId;
          $lobjRiesgo->cont_numero = isset($larrFormato['cont_numero']) ? strtoupper(trim(isset($row[$larrFormato['cont_numero']])?$row[$larrFormato['cont_numero']]:'')) : '';
          $lobjRiesgo->dotacion = self::FormatNumber(isset($larrFormato['dotacion']) ? strtoupper(trim(isset($row[$larrFormato['dotacion']])?$row[$larrFormato['dotacion']]:'')) : '');
          $lobjRiesgo->ingreso = self::FormatNumber(isset($larrFormato['ingreso']) ? strtoupper(trim(isset($row[$larrFormato['ingreso']])?$row[$larrFormato['ingreso']]:'')) : '');
          $lobjRiesgo->egreso = self::FormatNumber(isset($larrFormato['egreso']) ? strtoupper(trim(isset($row[$larrFormato['egreso']])?$row[$larrFormato['egreso']]:'')) : '');
          $lobjRiesgo->porcentaje_rotacion = self::FormatNumber(isset($larrFormato['porcentaje_rotacion']) ? strtoupper(trim(isset($row[$larrFormato['porcentaje_rotacion']])?$row[$larrFormato['porcentaje_rotacion']]:'')) : '');
          $lobjRiesgo->variable_1 = self::FormatNumber(isset($larrFormato['variable_1']) ? strtoupper(trim(isset($row[$larrFormato['variable_1']])?$row[$larrFormato['variable_1']]:'')) : '');
          $lobjRiesgo->riesgo_rotacion = self::FormatNumber(isset($larrFormato['riesgo_rotacion']) ? strtoupper(trim(isset($row[$larrFormato['riesgo_rotacion']])?$row[$larrFormato['riesgo_rotacion']]:'')) : '');
          $lobjRiesgo->horas_diarias = self::FormatNumber(isset($larrFormato['horas_diarias']) ? strtoupper(trim(isset($row[$larrFormato['horas_diarias']])?$row[$larrFormato['horas_diarias']]:'')) : '');
          $lobjRiesgo->horas_totales = self::FormatNumber(isset($larrFormato['horas_totales']) ? strtoupper(trim(isset($row[$larrFormato['horas_totales']])?$row[$larrFormato['horas_totales']]:'')) : '');
          $lobjRiesgo->horas_vacaciones = self::FormatNumber(isset($larrFormato['horas_vacaciones']) ? strtoupper(trim(isset($row[$larrFormato['horas_vacaciones']])?$row[$larrFormato['horas_vacaciones']]:'')) : '');
          $lobjRiesgo->horas_licencias = self::FormatNumber(isset($larrFormato['horas_licencias']) ? strtoupper(trim(isset($row[$larrFormato['horas_licencias']])?$row[$larrFormato['horas_licencias']]:'')) : '');
          $lobjRiesgo->horas_otros_ausentismo = self::FormatNumber(isset($larrFormato['horas_otros_ausentismo']) ? strtoupper(trim(isset($row[$larrFormato['horas_otros_ausentismo']])?$row[$larrFormato['horas_otros_ausentismo']]:'')) : '');
          $lobjRiesgo->porcentaje_ausentimo = self::FormatNumber(isset($larrFormato['porcentaje_ausentimo']) ? strtoupper(trim(isset($row[$larrFormato['porcentaje_ausentimo']])?$row[$larrFormato['porcentaje_ausentimo']]:'')) : '');
          $lobjRiesgo->variable_2 = self::FormatNumber(isset($larrFormato['variable_2']) ? strtoupper(trim(isset($row[$larrFormato['variable_2']])?$row[$larrFormato['variable_2']]:'')) : '');
          $lobjRiesgo->riesgo_ausentismo = self::FormatNumber(isset($larrFormato['riesgo_ausentismo']) ? strtoupper(trim(isset($row[$larrFormato['riesgo_ausentismo']])?$row[$larrFormato['riesgo_ausentismo']]:'')) : '');
          $lobjRiesgo->rc_monto_impago = self::FormatNumber(isset($larrFormato['rc_monto_impago']) ? strtoupper(trim(isset($row[$larrFormato['rc_monto_impago']])?$row[$larrFormato['rc_monto_impago']]:'')) : '');
          $lobjRiesgo->rc_numero_impago = self::FormatNumber(isset($larrFormato['rc_numero_impago']) ? strtoupper(trim(isset($row[$larrFormato['rc_numero_impago']])?$row[$larrFormato['rc_numero_impago']]:'')) : '');
          $lobjRiesgo->indicador_comercial = self::FormatNumber(isset($larrFormato['indicador_comercial']) ? strtoupper(trim(isset($row[$larrFormato['indicador_comercial']])?$row[$larrFormato['indicador_comercial']]:'')) : '');
          $lobjRiesgo->mayor_400 = self::FormatNumber(isset($larrFormato['mayor_400']) ? strtoupper(trim(isset($row[$larrFormato['mayor_400']])?$row[$larrFormato['mayor_400']]:'')) : '');
          $lobjRiesgo->riesgo_comercial = self::FormatNumber(isset($larrFormato['riesgo_comercial']) ? strtoupper(trim(isset($row[$larrFormato['riesgo_comercial']])?$row[$larrFormato['riesgo_comercial']]:'')) : '');
          $lobjRiesgo->patrimonio = self::FormatNumber(isset($larrFormato['patrimonio']) ? strtoupper(trim(isset($row[$larrFormato['patrimonio']])?$row[$larrFormato['patrimonio']]:'')) : '');
          $lobjRiesgo->ri_numero_impago = self::FormatNumber(isset($larrFormato['ri_numero_impago']) ? strtoupper(trim(isset($row[$larrFormato['ri_numero_impago']])?$row[$larrFormato['ri_numero_impago']]:'')) : '');
          $lobjRiesgo->ri_monto_impago = self::FormatNumber(isset($larrFormato['ri_monto_impago']) ? strtoupper(trim(isset($row[$larrFormato['ri_monto_impago']])?$row[$larrFormato['ri_monto_impago']]:'')) : '');
          $lobjRiesgo->indicador_impuesto = self::FormatNumber(isset($larrFormato['indicador_impuesto']) ? strtoupper(trim(isset($row[$larrFormato['indicador_impuesto']])?$row[$larrFormato['indicador_impuesto']]:'')) : '');
          $lobjRiesgo->mayor_2 = self::FormatNumber(isset($larrFormato['mayor_2']) ? strtoupper(trim(isset($row[$larrFormato['mayor_2']])?$row[$larrFormato['mayor_2']]:'')) : '');
          $lobjRiesgo->riesgo_impuesto = self::FormatNumber(isset($larrFormato['riesgo_impuesto']) ? strtoupper(trim(isset($row[$larrFormato['riesgo_impuesto']])?$row[$larrFormato['riesgo_impuesto']]:'')) : '');
          $lobjRiesgo->riesgo = self::FormatNumber(isset($larrFormato['riesgo']) ? strtoupper(trim(isset($row[$larrFormato['riesgo']])?$row[$larrFormato['riesgo']]:'')) : '');
          $lobjRiesgo->IdEstatus = $lintIdEstatus;
          $lobjRiesgo->entry_by = self::$gintIdUser;
          $lobjRiesgo->updated_by = self::$gintIdUser;
          $lobjRiesgo->save();

        }
        $lintFila += 1;
      }

      $lstrObservacion = self::getObservation('riesgo');

      return json_encode(array("code"=>1, "observations"=>$lstrObservacion));

    }else{
      return $larrResult;
    }

  }
  static public function UpdateNoConformidad($pobjDocumento){

    if ($pobjDocumento->Entidad == 3){

        $lobjDiferenciasPersonas = Diferenciancpersonas::where('IdDocumento', $pobjDocumento->IdDocumento)
                                   ->where("periodo",self::getPeriodo())
                                   ->first();

        if ($lobjDiferenciasPersonas){
          $lobjDiferenciasPersonas = Diferenciancpersonas::find($lobjDiferenciasPersonas->id);
          $lobjDiferenciasPersonas->IdEstatusDocumento = $pobjDocumento->IdEstatus;
          $lobjDiferenciasPersonas->IdEstatus = $pobjDocumento->IdEstatus;
          $lobjDiferenciasPersonas->Resultado = $pobjDocumento->Resultado;
          $lobjDiferenciasPersonas->updated_by = self::$gintIdUser;
          $lobjDiferenciasPersonas->save();
        }else{
          $lobjDiferenciasPersonas = new Diferenciancpersonas();
          $lobjDiferenciasPersonas->periodo = self::getPeriodo();
          $lobjDiferenciasPersonas->IdDocumento = $pobjDocumento->IdDocumento;
          $lobjDiferenciasPersonas->IdPersona = $pobjDocumento->IdEntidad;
          $lobjDiferenciasPersonas->contrato_id = $pobjDocumento->contrato_id;
          $lobjDiferenciasPersonas->IdTipoDocumento = $pobjDocumento->IdTipoDocumento;
          $lobjDiferenciasPersonas->IdEstatusDocumento = $pobjDocumento->IdEstatus;
          $lobjDiferenciasPersonas->IdEstatus = $pobjDocumento->IdEstatus;
          $lobjDiferenciasPersonas->Resultado = $pobjDocumento->Resultado?$pobjDocumento->Resultado:'';
          $lobjDiferenciasPersonas->entry_by = self::$gintIdUser;
          $lobjDiferenciasPersonas->updated_by = self::$gintIdUser;
          $lobjDiferenciasPersonas->save();
        }

    }else{

        $lobjDiferenciasEmpresas = Diferenciancempresas::where('IdDocumento', $pobjDocumento->IdDocumento)
                                   ->where("periodo",self::getPeriodo())
                                   ->first();

        if ($lobjDiferenciasEmpresas){
          $lobjDiferenciasEmpresas = Diferenciancempresas::find($lobjDiferenciasEmpresas->id);
          $lobjDiferenciasEmpresas->IdEstatusDocumento = $pobjDocumento->IdEstatus;
          $lobjDiferenciasEmpresas->IdEstatus = $pobjDocumento->IdEstatus;
          $lobjDiferenciasEmpresas->Resultado = $pobjDocumento->Resultado;
          $lobjDiferenciasEmpresas->updated_by = self::$gintIdUser;
          $lobjDiferenciasEmpresas->save();
        }else{
          $lobjDiferenciasEmpresas = new Diferenciancempresas();
          $lobjDiferenciasEmpresas->periodo = self::getPeriodo();
          $lobjDiferenciasEmpresas->IdDocumento = $pobjDocumento->IdDocumento;
          $lobjDiferenciasEmpresas->Mes = $pobjDocumento->IdEntidad;
          $lobjDiferenciasEmpresas->IdContratista = $pobjDocumento->IdContratista;
          $lobjDiferenciasEmpresas->contrato_id = $pobjDocumento->contrato_id?$pobjDocumento->contrato_id:0;
          $lobjDiferenciasEmpresas->IdTipoDocumento = $pobjDocumento->IdTipoDocumento;
          $lobjDiferenciasEmpresas->IdEstatusDocumento = $pobjDocumento->IdEstatus;
          $lobjDiferenciasEmpresas->IdEstatus = $pobjDocumento->IdEstatus;
          $lobjDiferenciasEmpresas->Resultado = $pobjDocumento->Resultado?$pobjDocumento->Resultado:'';
          $lobjDiferenciasEmpresas->entry_by = self::$gintIdUser;
          $lobjDiferenciasEmpresas->updated_by = self::$gintIdUser;
          $lobjDiferenciasEmpresas->save();
        }

    }

    return array("code"=>1, "message"=> "",  "result"=>"");

  }
  static public function UpdateCalculo(){
    $larrResult = array();
    $lobjDiferencias = Diferenciacalculo::where("tbl_diferencias_calculo.periodo",self::getPeriodo())
                       ->join('tbl_contrato_maestro',function($table)
                       {
                         $table->on('tbl_contrato_maestro.periodo', '=', 'tbl_diferencias_calculo.periodo')
                               ->on('tbl_contrato_maestro.contrato_id', '=', 'tbl_diferencias_calculo.contrato_id');
                       })
                       ->select('tbl_contrato_maestro.id as contrato_maestro_id',
                                \DB::raw('sum(tbl_diferencias_calculo.calculado) as costo_laboral'),
                                \DB::raw('sum(tbl_diferencias_calculo.diferencia_favor_trabajador) as pasivo_laboral'),
                                \DB::raw('sum(case when tbl_diferencias_calculo.ol_diferencia_pago < 0 then tbl_diferencias_calculo.ol_diferencia_pago else 0 end) as obligaciones_laborales'),
                                \DB::raw('sum(case when tbl_diferencias_calculo.op_diferencia_pago < 0 then tbl_diferencias_calculo.op_diferencia_pago else 0 end) as obligaciones_previsionales'),
                                \DB::raw('sum(tbl_diferencias_calculo.fl_diferencia_pago) as finiquito'),
                                \DB::raw('1-((-1*sum(case when tbl_diferencias_calculo.ol_diferencia_pago < 0 then tbl_diferencias_calculo.ol_diferencia_pago else 0 end))/sum(tbl_diferencias_calculo.calculado)) as porcentaje_ol'),
                                \DB::raw('1-((-1*sum(case when tbl_diferencias_calculo.op_diferencia_pago < 0 then  tbl_diferencias_calculo.op_diferencia_pago else 0 end))/sum(tbl_diferencias_calculo.calculado)) as porcentaje_op'),
                                \DB::raw('sum(case when tbl_diferencias_calculo.cl_diferencia_pago < 0 then 1 else 0 end) as trabajadores_con_o'),
                                \DB::raw('sum(case when ifnull(tbl_diferencias_calculo.ol_diferencia_pago,0) < 0 then 1 else 0 end) as trabajadores_con_ol'),
                                \DB::raw('sum(case when ifnull(tbl_diferencias_calculo.op_diferencia_pago,0) < 0 then 1 else 0 end) as trabajadores_con_op'),
                                \DB::raw('sum(tbl_diferencias_calculo.sueldo_base) as sueldo_base'),
                                \DB::raw('sum(tbl_diferencias_calculo.gratificacion_legal) as gratificacion_legal'),
                                \DB::raw('sum(tbl_diferencias_calculo.horas_extras) as horas_extras'),
                                \DB::raw('sum(tbl_diferencias_calculo.otros_imponibles) as otros_imponibles'),
                                \DB::raw('sum(tbl_diferencias_calculo.no_imponible) as no_imponible'),
                                \DB::raw('sum(tbl_diferencias_calculo.impuesto) as impuesto'),
                                \DB::raw('sum(tbl_diferencias_calculo.otros_descuentos) as otros_descuentos'),
                                \DB::raw('sum(tbl_diferencias_calculo.afp) as afp'),
                                \DB::raw('sum(tbl_diferencias_calculo.salud) as salud'),
                                \DB::raw('sum(tbl_diferencias_calculo.afc) as afc'),
                                \DB::raw('sum(tbl_diferencias_calculo.trabajo_pesado) as trabajo_pesado'),
                                \DB::raw('sum(tbl_diferencias_calculo.sis) as sis'),
                                \DB::raw('sum(tbl_diferencias_calculo.afc_empleador) as afc_empleador'),
                                \DB::raw('sum(tbl_diferencias_calculo.mutualidad) as mutualidad'),
                                \DB::raw('sum(tbl_diferencias_calculo.ias) as ias'),
                                \DB::raw('sum(tbl_diferencias_calculo.vacaciones) as vacaciones'),
                                \DB::raw('sum(tbl_diferencias_calculo.otros) as otros')
                              )
                       ->groupBy('tbl_contrato_maestro.id')
                       ->get();

    $lobjDiferenciasMaestro = \DB::table('tbl_diferencias_calculo_pie_maestro')->where('id_estatus',1)->get();
    \DB::table('tbl_diferencias_calculo_pie')->where('periodo',self::getPeriodo())->delete();
    $larrDataInsert = array();

    foreach ($lobjDiferencias as $larrDiferencias) {
      $lobjContratoMaestro = Contratomaestro::find($larrDiferencias->contrato_maestro_id);
      $lobjContratoMaestro->costo_laboral = $larrDiferencias->costo_laboral;
      $lobjContratoMaestro->pasivo_laboral = $larrDiferencias->pasivo_laboral;
      $lobjContratoMaestro->obligaciones_laborales = $larrDiferencias->obligaciones_laborales;
      $lobjContratoMaestro->obligaciones_previsionales = $larrDiferencias->obligaciones_previsionales;
      $lobjContratoMaestro->porcentaje_ol = $larrDiferencias->porcentaje_ol;
      $lobjContratoMaestro->porcentaje_op = $larrDiferencias->porcentaje_op;
      $lobjContratoMaestro->trabajadores_con_o = $larrDiferencias->trabajadores_con_o;
      $lobjContratoMaestro->trabajadores_con_ol = $larrDiferencias->trabajadores_con_ol;
      $lobjContratoMaestro->trabajadores_con_op = $larrDiferencias->trabajadores_con_op;
      $lobjContratoMaestro->save();

      foreach ($lobjDiferenciasMaestro as $larrDiferenciasMaestro) {
          $lintIndicador = -1*$larrDiferencias->{$larrDiferenciasMaestro->nombre_campo};
          $lintIndicadorDivisor = -1*$larrDiferencias->{$larrDiferenciasMaestro->grupo};
          if ($lintIndicadorDivisor>0) {
            if ($lintIndicador>0){
              $lintIndicador = $lintIndicador/$lintIndicadorDivisor;
            }
          }else{
            $lintIndicador = 0;
          }
          $larrDataInsert = array ('contrato_id'=>$lobjContratoMaestro->contrato_id,
                                   'periodo'=>self::getPeriodo(),
                                   'grupo'=>$larrDiferenciasMaestro->grupo,
                                   'nombre'=>$larrDiferenciasMaestro->nombre,
                                   'nombre_campo'=>$larrDiferenciasMaestro->nombre_campo,
                                   'indicador' => $lintIndicador
                                  );
          \DB::table('tbl_diferencias_calculo_pie')->insert($larrDataInsert);
      }

    }
    return $larrResult;
  }

  /*********Funcion donde se saca el total de los ponderado para calcular el porcentaje Mensual de lo acumulado** */

  static public function UpdateDocument($pintContratoId = ""){

    $larrResult = array();
    $lstrQuery = "select (select count(distinct(tbl_personas_maestro.idpersona)) from tbl_personas_maestro where tbl_personas_maestro.periodo = '".self::getPeriodo()."' and tbl_personas_maestro.contrato_id = tbl_diferencias_nc.contrato_id and exists (select 1 from tbl_diferencias_nc_personas where tbl_diferencias_nc_personas.idpersona = tbl_personas_maestro.idpersona and tbl_diferencias_nc_personas.periodo = tbl_personas_maestro.periodo and tbl_diferencias_nc_personas.contrato_id = tbl_personas_maestro.contrato_id and tbl_diferencias_nc_personas.IdEstatusDocumento != 5 )) as trabajadores_con_o, tbl_diferencias_nc.*, tbl_diferencias_finiquitos.cantidad_observacion as trabajadores_con_of, tbl_diferencias_finiquitos.cantidad as trabajadores_con_f, tbl_diferencias_finiquitos.porcentaje as porcentaje_f
                  from (select Total.id, Total.contrato_id, sum(Total.cantidad_no_conformidades) as nc_generadas, sum(Total.porcentaje*(Total.ponderado/100)) as documentacion
                  from (
                  Select tbl_contrato_maestro.id,
                         tbl_contrato.contrato_id,
                         tbl_tipos_documentos.Descripcion as TipoDocumento,
                  			 tbl_tipos_documentos.Ponderado,
                  			 sum(case when tbl_diferencias_nc_personas.IdEstatusDocumento != 5 then 1 else 0 end) as cantidad_no_conformidades,
                  			 count(tbl_diferencias_nc_personas.id) as cantidad_total,
                  			 1-sum(case when tbl_diferencias_nc_personas.IdEstatusDocumento != 5 then 1 else 0 end) / count(tbl_diferencias_nc_personas.id) as porcentaje
                  From tbl_diferencias_nc_personas
                  Inner join tbl_contrato on tbl_contrato.contrato_id = tbl_diferencias_nc_personas.contrato_id
                  inner join tbl_contrato_maestro on tbl_contrato_maestro.contrato_id = tbl_contrato.contrato_id and tbl_diferencias_nc_personas.periodo = tbl_contrato_maestro.periodo
                  Inner join tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_diferencias_nc_personas.IdTipoDocumento
                  where tbl_diferencias_nc_personas.IdEstatusDocumento != 0
                  and tbl_tipos_documentos.ControlCheckLaboral = 1
                  and tbl_tipos_documentos.IdProceso != 4
                  and tbl_diferencias_nc_personas.periodo = '".self::getPeriodo()."'
                  Group by tbl_contrato_maestro.id,
                  tbl_contrato.contrato_id,
                  tbl_tipos_documentos.Descripcion,
                  tbl_tipos_documentos.Ponderado
                  union all
                  select tbl_contrato_maestro.id,
                         tbl_contrato_maestro.contrato_id,
                         ifnull(tbl_diferencias_nc_personas.descripcion,tbl_contrato_maestro.tipodocumento) as TipoDocumento,
                         ifnull(tbl_diferencias_nc_personas.ponderado,tbl_contrato_maestro.ponderado) as ponderado,
                         sum(case when tbl_diferencias_nc_personas.IdEstatusDocumento != 5 then 1 else 0 end) as cantidad_no_conformidades,
                         count(tbl_diferencias_nc_personas.id) as cantidad_total,
                         1-ifnull((sum(case when tbl_diferencias_nc_personas.IdEstatusDocumento != 5 then 1 else 0 end) / count(tbl_diferencias_nc_personas.id)),0) as porcentaje
                  from (select tbl_contrato_maestro.*, tbl_tipos_documentos.descripcion as tipodocumento, tbl_tipos_documentos.ponderado from tbl_contrato_maestro, tbl_tipos_documentos where tbl_tipos_documentos.idproceso = 4) as tbl_contrato_maestro
                  Inner join tbl_contrato on tbl_contrato.contrato_id = tbl_contrato_maestro.contrato_id
                  left join (select tbl_tipos_documentos.idtipodocumento,
                                    tbl_tipos_documentos.descripcion,
                                    tbl_tipos_documentos.ponderado,
                                    tbl_diferencias_nc_personas.id,
                                    tbl_diferencias_nc_personas.periodo,
                                    tbl_diferencias_nc_personas.contrato_id,
                                    tbl_diferencias_nc_personas.IdEstatusDocumento
                             from tbl_tipos_documentos
                             left join tbl_diferencias_nc_personas on tbl_tipos_documentos.idTipoDocumento = tbl_diferencias_nc_personas.IdTipoDocumento
                             where tbl_tipos_documentos.ControlCheckLaboral = 1
                             and tbl_tipos_documentos.IdProceso = 4
                             and tbl_diferencias_nc_personas.IdEstatusDocumento != 0) as tbl_diferencias_nc_personas
                       on tbl_contrato_maestro.contrato_id = tbl_diferencias_nc_personas.contrato_id
                       and tbl_contrato_maestro.periodo = tbl_diferencias_nc_personas.periodo
                  where tbl_contrato_maestro.periodo = '".self::getPeriodo()."'
                  group by tbl_contrato_maestro.id,
                         tbl_contrato_maestro.contrato_id,
                         tbl_diferencias_nc_personas.IdTipoDocumento,
                         tbl_diferencias_nc_personas.descripcion,
                         tbl_diferencias_nc_personas.ponderado
                  union all
                  select tbl_contrato_maestro.id,
                         tbl_contrato.contrato_id,
                         tbl_tipos_documentos.Descripcion as TipoDocumento,
                  			 tbl_tipos_documentos.Ponderado,
                  			 sum(case when tbl_diferencias_nc_empresas.IdEstatusDocumento != 5 then 1 else 0 end) as cantidad_no_conformidades,
                  			 count(tbl_diferencias_nc_empresas.id) as cantidad_total,
                  			 1-sum(case when tbl_diferencias_nc_empresas.IdEstatusDocumento != 5 then 1 else 0 end) / count(tbl_diferencias_nc_empresas.id) as porcentaje
                  from tbl_diferencias_nc_empresas
                  Inner join tbl_contrato on tbl_contrato.contrato_id = tbl_diferencias_nc_empresas.contrato_id
                  inner join tbl_contrato_maestro on tbl_contrato_maestro.contrato_id = tbl_contrato.contrato_id and tbl_contrato_maestro.periodo = tbl_diferencias_nc_empresas.periodo
                  Inner join tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_diferencias_nc_empresas.IdTipoDocumento
                  where tbl_diferencias_nc_empresas.IdEstatusDocumento != 0
                  and tbl_tipos_documentos.ControlCheckLaboral = 1
                  and tbl_diferencias_nc_empresas.periodo = '".self::getPeriodo()."'
                  Group by tbl_contrato_maestro.id,
                  tbl_contrato.contrato_id,
                  tbl_tipos_documentos.Descripcion,
                  tbl_tipos_documentos.Ponderado
                  union all
                  select tbl_contrato_maestro.id,
                         tbl_contrato.contrato_id,
                         tbl_tipos_documentos.Descripcion as TipoDocumento,
                  			 tbl_tipos_documentos.Ponderado,
                  			 sum(case when tbl_diferencias_nc_empresas.IdEstatusDocumento != 5 then 1 else 0 end) as cantidad_no_conformidades,
                  			 count(tbl_diferencias_nc_empresas.id) as cantidad_total,
                  			 1-sum(case when tbl_diferencias_nc_empresas.IdEstatusDocumento != 5 then 1 else 0 end) / count(tbl_diferencias_nc_empresas.id) as porcentaje
                  from tbl_diferencias_nc_empresas
                  Inner join tbl_contrato on tbl_diferencias_nc_empresas.IdContratista = tbl_contrato.IdContratista
                  inner join tbl_contrato_maestro on tbl_contrato_maestro.contrato_id = tbl_contrato.contrato_id and tbl_contrato_maestro.periodo = tbl_diferencias_nc_empresas.periodo
                  Inner join tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_diferencias_nc_empresas.IdTipoDocumento
                  where tbl_diferencias_nc_empresas.IdEstatusDocumento != 0
                  and tbl_diferencias_nc_empresas.contrato_id = 0
                  and tbl_tipos_documentos.ControlCheckLaboral = 1
                  and tbl_diferencias_nc_empresas.periodo = '".self::getPeriodo()."'
                  Group by tbl_contrato_maestro.id,
                  tbl_contrato.contrato_id,
                  tbl_tipos_documentos.Descripcion,
                  tbl_tipos_documentos.Ponderado) as Total
                  group by Total.id, Total.contrato_id) as tbl_diferencias_nc
                  left join (select tbl_contrato_maestro.id,
                                    count(tbl_diferencias_nc_personas.id) as cantidad,
                                    sum(case when tbl_diferencias_nc_personas.idestatusdocumento != 5 then 1 else 0 end) as cantidad_observacion,
                                    1-ifnull(sum(case when tbl_diferencias_nc_personas.idestatusdocumento != 5 then 1 else 0 end)/count(tbl_diferencias_nc_personas.id),0) as porcentaje
                            from tbl_contrato
                            inner join tbl_contrato_maestro on tbl_contrato_maestro.contrato_id = tbl_contrato.contrato_id
                            left join (select tbl_diferencias_nc_personas.*
                                      from tbl_diferencias_nc_personas
                                      where exists (select 1
                                            from tbl_tipos_documentos
                                            where tbl_tipos_documentos.ControlCheckLaboral = 1
                                            and tbl_tipos_documentos.IdTipoDocumento = tbl_diferencias_nc_personas.IdTipoDocumento
                                            and tbl_tipos_documentos.IdProceso = 4)
                                      and tbl_diferencias_nc_personas.IdEstatusDocumento != 0) as tbl_diferencias_nc_personas on tbl_diferencias_nc_personas.periodo = tbl_contrato_maestro.periodo
                                                                                                                                and tbl_diferencias_nc_personas.contrato_id = tbl_contrato_maestro.contrato_id
                              where tbl_contrato_maestro.periodo = '".self::getPeriodo()."'
                              group by tbl_contrato_maestro.id) as tbl_diferencias_finiquitos on tbl_diferencias_finiquitos.id = tbl_diferencias_nc.id";
      if ($pintContratoId){
        $lstrQuery = $lstrQuery." WHERE tbl_diferencias_nc.contrato_id = ".$pintContratoId;
      }
      $lobjDiferencias = \DB::select($lstrQuery);

      foreach ($lobjDiferencias as $larrDiferencias) {
        $lobjContratoMaestro = Contratomaestro::find($larrDiferencias->id);
        $lobjContratoMaestro->porcentaje_f = $larrDiferencias->porcentaje_f;
        $lobjContratoMaestro->trabajadores_con_f = $larrDiferencias->trabajadores_con_f;
        $lobjContratoMaestro->trabajadores_con_of = $larrDiferencias->trabajadores_con_of;
        $lobjContratoMaestro->documentacion = $larrDiferencias->documentacion;
        $lobjContratoMaestro->nc_generadas = $larrDiferencias->nc_generadas;
        $lobjContratoMaestro->trabajadores_con_o = $larrDiferencias->trabajadores_con_o;

        $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();

        $QueryDOC_NOMENSUAL_Ponderado = TipoDocumentos::join('tbl_requisitos',function($table)
        {
          $table->on('tbl_tipos_documentos.IdTipoDocumento', '=', 'tbl_requisitos.IdTipoDocumento');
        });

        if($sitio->Valor=='Fepasa'){
          $QueryDOC_NOMENSUAL_Ponderado =
          $QueryDOC_NOMENSUAL_Ponderado->where('tbl_tipos_documentos.ControlCheckLaboral','=',1)
          ->where('tbl_tipos_documentos.Ponderado','>',0)
          ->get();
        }else{
          $QueryDOC_NOMENSUAL_Ponderado =
          $QueryDOC_NOMENSUAL_Ponderado->where('tbl_tipos_documentos.ControlCheckLaboral','=',1)
          ->where('tbl_tipos_documentos.Periodicidad','>',1)
          ->where('tbl_tipos_documentos.Ponderado','>',0)
          ->get();
        }

        $mes = substr(self::getPeriodo(),-5,2);

		    $total_ponderado=0;
		    $total_ponderadoSem = 0;
		    $total_ponderadoAnual = 0;
		    $cantidad_faltante = 0;
		    $tipoDoc=array();

		    foreach ($QueryDOC_NOMENSUAL_Ponderado as $calcula) {
          array_push($tipoDoc,$calcula->IdTipoDocumento);
          // Trimestral
          $periocidad = $calcula->Periodicidad;

          if($sitio->Valor=='Fepasa'){
            if($periocidad == 1){
              switch ($mes) {
                case 01:
                case 02:
                case 03:
                case 04:
                case 05:
                case 06:
                case 07:
                case 08:
                case 09:
                case 10:
                case 11:
                case 12:
                  $total_ponderado = $total_ponderado + $calcula->Ponderado;
                  $cantidad_faltante = $total_ponderado/100;
                  break;

              }
            }
          }

          if($periocidad == 2) {
            switch($mes) {
              case 01:
                $total_ponderado = $total_ponderado + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderado/100;
                break;
              case 02:
                $total_ponderado = 0;
                break;
              case 03:
                $total_ponderado = $total_ponderado + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderado/100;
                break;
              case 04:
                $total_ponderado = $total_ponderado + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderado/100;
                break;
              case 05:
                $total_ponderado = 0;
                break;
              case 06:
                $total_ponderado = $total_ponderado + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderado/100;
                break;
              case 07:
                $total_ponderado = $total_ponderado + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderado/100;
                break;
              case 08:
                $total_ponderado = 0;
                break;
              case 09:
                $total_ponderado = $total_ponderado + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderado/100;
                break;
              case 10:
                $total_ponderado = $total_ponderado + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderado/100;
                break;
              case 11:
                $total_ponderado = 0;
                break;
              case 12:
                $total_ponderado = $total_ponderado + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderado/100;
                break;
            }
          }

          // Semestral

          if($periocidad == 3) {

            switch($mes) {
              case 01:
                $total_ponderadoSem = 0;
                break;
              case 02:
                $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderadoSem/100;
                break;
              case 03:
                $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderadoSem/100;
                break;
              case 04:
                $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderadoSem/100;
                break;
              case 05:
                $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderadoSem/100;
                break;
              case 06:
                $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderadoSem/100;
                break;
              case 07:
                $total_ponderadoSem = 0;
                break;
              case 08:
                $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderadoSem/100;
                break;
              case 09:
                $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderadoSem/100;
                  break;
              case 10:
                $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderadoSem/100;
                break;
              case 11:
                $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderadoSem/100;
                break;
              case 12:
                $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
                $cantidad_faltante = $total_ponderadoSem/100;
                break;
            }
          }

          // Anual

          if($periocidad == 4) {

            switch($mes) {

            case 01:
              $total_ponderadoAnual = 0;
              break;
            case 02:
              $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderadoAnual/100;
                break;
            case 03:
              $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderadoAnual/100;
            break;
            case 04:
              $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderadoAnual/100;
            break;
            case 05:
              $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderadoAnual/100;
            break;
            case 06:
              $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderadoAnual/100;
            break;
            case 07:
              $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderadoAnual/100;
            break;
            case 08:
              $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderadoAnual/100;
            break;
            case 09:
              $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderadoAnual/100;
            break;
            case 10:
              $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderadoAnual/100;
            break;
            case 11:
              $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderadoAnual/100;
            break;

            case 12:
              $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderadoAnual/100;
            break;

          }
          }

        } // cierra for

        if($sitio->Valor=='Fepasa'){
          $cantidad_faltante=1;
          $docs = \DB::table('tbl_documentos')
            ->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
            ->where('tbl_documentos.contrato_id',$larrDiferencias->contrato_id)
            //->where('tbl_documentos.FechaEmision',self::getPeriodo())
            ->whereIn('tbl_documentos.IdTipoDocumento',$tipoDoc)
            ->where('tbl_tipos_documentos.Entidad','<>',1)
            ->select('tbl_tipos_documentos.Ponderado')
            ->groupBy('tbl_documentos.IdTipoDocumento')
            ->get();
          $lintIdContratista = \DB::table('tbl_contrato')->where('contrato_id',$larrDiferencias->contrato_id)->value('IdContratista');
          $docsContratista = \DB::table('tbl_documentos')
            ->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
            ->where('tbl_documentos.IdContratista',$lintIdContratista)
            //->where('tbl_documentos.FechaEmision',self::getPeriodo())
            ->whereIn('tbl_documentos.IdTipoDocumento',$tipoDoc)
            ->where('tbl_tipos_documentos.Entidad',1)
            ->select('tbl_tipos_documentos.Ponderado')
            ->groupBy('tbl_documentos.IdTipoDocumento')
            ->get();
          $resta=0;
          foreach ($docs as $doc) {
            $resta = $resta + $doc->Ponderado;
          }
          foreach ($docsContratista as $doc) {
            $resta = $resta + $doc->Ponderado;
          }
          $resta= $resta/100;

          $cantidad_faltante = $cantidad_faltante-$resta;

        }

        if($sitio->Valor!='Fepasa'){
          $cantidad_faltante = 0;
        }

        $lobjContratoMaestro->documentacion = $larrDiferencias->documentacion+$cantidad_faltante;
        $lobjContratoMaestro->save();
      }

      $larrResult["code"] = "01";
      $larrResult["status"] = "success";
      $larrResult["message"] = "Porcentajes actualizados satisfactoriamente.";
      return $larrResult;

  }
/*********Funcion donde se saca el total de los ponderado para calcular el porcentaje Mensual de Desempeño** */
  static public function UpdateDocumentMesActual($pintContratoId = ""){

    $QueryDOC_NOMENSUAL_Ponderado = TipoDocumentos::join('tbl_requisitos',function($table)
    {
      $table->on('tbl_tipos_documentos.IdTipoDocumento', '=', 'tbl_requisitos.IdTipoDocumento');
    });

    $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
    if($sitio->Valor=='Fepasa'){
      $QueryDOC_NOMENSUAL_Ponderado =
      $QueryDOC_NOMENSUAL_Ponderado->where('tbl_tipos_documentos.ControlCheckLaboral','=',1)
      ->where('tbl_tipos_documentos.Periodicidad','>=',1)
      ->where('tbl_tipos_documentos.Ponderado','>',0)
      ->get();
    }else{
      $QueryDOC_NOMENSUAL_Ponderado =
      $QueryDOC_NOMENSUAL_Ponderado->where('tbl_tipos_documentos.ControlCheckLaboral','=',1)
      ->where('tbl_tipos_documentos.Periodicidad','>',1)
      ->get();
    }

    $mes = substr(self::getPeriodo(),-5,2);

   $total_ponderado=0;
   $total_ponderadoSem = 0;
   $total_ponderadoAnual = 0;
   $cantidad_faltante = 0;
   $tipoDoc=array();

   foreach ($QueryDOC_NOMENSUAL_Ponderado as $calcula)
   {
     array_push($tipoDoc,$calcula->IdTipoDocumento);
      // Trimestral
      $periocidad = $calcula->Periodicidad;

      if($sitio->Valor=='Fepasa'){
        if($periocidad == 1){
          switch ($mes) {
            case 01:
            case 02:
            case 03:
            case 04:
            case 05:
            case 06:
            case 07:
            case 08:
            case 09:
            case 10:
            case 11:
            case 12:
              $total_ponderado = $total_ponderado + $calcula->Ponderado;
              $cantidad_faltante = $total_ponderado/100;
              break;

          }
        }
      }

      if($periocidad == 2) {

      switch($mes) {

        case 01:
          $total_ponderado = $total_ponderado + $calcula->Ponderado;
          $cantidad_faltante = $total_ponderado/100;
          break;
        case 02:
          $total_ponderado = 0;
            break;
        case 03:
          $total_ponderado = $total_ponderado + $calcula->Ponderado;
          $cantidad_faltante = $total_ponderado/100;
        break;
        case 04:
          $total_ponderado = $total_ponderado + $calcula->Ponderado;
          $cantidad_faltante = $total_ponderado/100;
        break;
        case 05:
          $total_ponderado = 0;
        break;
        case 06:
          $total_ponderado = $total_ponderado + $calcula->Ponderado;
          $cantidad_faltante = $total_ponderado/100;
        break;
        case 07:
          $total_ponderado = $total_ponderado + $calcula->Ponderado;
          $cantidad_faltante = $total_ponderado/100;
        break;
        case 08:
          $total_ponderado = 0;
        break;
        case 09:
          $total_ponderado = $total_ponderado + $calcula->Ponderado;
          $cantidad_faltante = $total_ponderado/100;
        break;
        case 10:
          $total_ponderado = $total_ponderado + $calcula->Ponderado;
          $cantidad_faltante = $total_ponderado/100;
        break;
        case 11:
          $total_ponderado = 0;
        break;

        case 12:
          $total_ponderado = $total_ponderado + $calcula->Ponderado;
          $cantidad_faltante = $total_ponderado/100;
        break;

      }
    }

    // Semestral

    if($periocidad == 3) {

      switch($mes) {

      case 01:
        $total_ponderadoSem = 0;
        break;
      case 02:
        $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoSem/100;
          break;
      case 03:
        $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoSem/100;
      break;
      case 04:
        $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoSem/100;
      break;
      case 05:
        $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoSem/100;
      break;
      case 06:
        $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoSem/100;
      break;
      case 07:
        $total_ponderadoSem = 0;
      break;
      case 08:
        $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoSem/100;
      break;
      case 09:
        $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoSem/100;
      break;
      case 10:
        $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoSem/100;
      break;
      case 11:
        $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoSem/100;
      break;

      case 12:
        $total_ponderadoSem = $total_ponderadoSem + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoSem/100;
      break;

      }
    }

    // Anual

    if($periocidad == 4) {

      switch($mes) {

      case 01:
        $total_ponderadoAnual = 0;
        break;
      case 02:
        $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoAnual/100;
          break;
      case 03:
        $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoAnual/100;
      break;
      case 04:
        $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoAnual/100;
      break;
      case 05:
        $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoAnual/100;
      break;
      case 06:
        $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoAnual/100;
      break;
      case 07:
        $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoAnual/100;
      break;
      case 08:
        $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoAnual/100;
      break;
      case 09:
        $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoAnual/100;
      break;
      case 10:
        $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoAnual/100;
      break;
      case 11:
        $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoAnual/100;
      break;

      case 12:
        $total_ponderadoAnual = $total_ponderadoAnual + $calcula->Ponderado;
        $cantidad_faltante = $total_ponderadoAnual/100;
      break;

    }
    }

    } // cierra for


    $larrResult = array();
    $lstrQuery = "select X.* from (select Total.id, Total.contrato_id, sum(Total.cantidad_no_conformidades) as nc_generadas, sum(Total.porcentaje*((Total.ponderado)/100)) + $cantidad_faltante as documentacion
                 from (
                      SELECT
                      cm.id,
                      d.contrato_id,
                      td.Descripcion AS TipoDocumento,
                      td.Ponderado,
                      sum(case when d.IdEstatus != 5 then 1 else 0 end) as cantidad_no_conformidades,
                      count(d.contrato_id) as cantidad_total,
                      1-sum(case when d.IdEstatus != 5 then 1 else 0 end) / count(d.IdDocumento) as porcentaje
                      FROM tbl_documentos d
                      JOIN tbl_tipos_documentos td ON d.IdTipoDocumento=td.IdTipoDocumento
                      JOIN tbl_contrato_maestro cm ON cm.contrato_id = d.contrato_id
                      WHERE
                      td.ControlCheckLaboral = 1 AND
                      td.IdProceso != 4 and d.entidad=3 and
                      td.Vigencia!=2 and d.fechaemision <= '".self::getPeriodo()."'
                      AND EXISTS (SELECT 1 FROM tbl_personas_maestro pm WHERE d.identidad=pm.idpersona)
                      GROUP BY cm.id,
                       d.contrato_id,
                       td.Descripcion,
                       td.Ponderado

                       UNION all
                         SELECT
                        cm.id,
                        d.contrato_id,
                        td.Descripcion AS TipoDocumento,
                        td.Ponderado,
                        sum(case when d.IdEstatus != 5 then 1 else 0 end) as cantidad_no_conformidades,
                        count(d.contrato_id) as cantidad_total,
                        1-sum(case when d.IdEstatus != 5 then 1 else 0 end) / count(d.IdDocumento) as porcentaje
                        FROM tbl_documentos d
                        JOIN tbl_tipos_documentos td ON d.IdTipoDocumento=td.IdTipoDocumento
                        JOIN tbl_contrato_maestro cm ON cm.contrato_id = d.contrato_id
                        WHERE
                        td.ControlCheckLaboral = 1 AND
                        td.IdProceso != 4 AND
                        td.Vigencia!=2
                        AND d.entidad=2
                        AND d.FechaEmision <= '".self::getPeriodo()."'
                        GROUP BY cm.id,
                         d.contrato_id,
                         td.Descripcion,
                         td.Ponderado

                      UNION ALL
                      select tbl_contrato_maestro.id,
                         tbl_contrato_maestro.contrato_id,
                         ifnull(tbl_diferencias_nc_personas.descripcion,tbl_contrato_maestro.tipodocumento) as TipoDocumento,
                         ifnull(tbl_diferencias_nc_personas.ponderado,tbl_contrato_maestro.ponderado) as ponderado,
                         sum(case when tbl_diferencias_nc_personas.IdEstatusDocumento != 5 then 1 else 0 end) as cantidad_no_conformidades,
                         count(tbl_diferencias_nc_personas.id) as cantidad_total,
                         1-ifnull((sum(case when tbl_diferencias_nc_personas.IdEstatusDocumento != 5 then 1 else 0 end) / count(tbl_diferencias_nc_personas.id)),0) as porcentaje
                  from (select tbl_contrato_maestro.*, tbl_tipos_documentos.descripcion as tipodocumento, tbl_tipos_documentos.ponderado from tbl_contrato_maestro, tbl_tipos_documentos where tbl_tipos_documentos.idproceso = 4) as tbl_contrato_maestro
                  Inner join tbl_contrato on tbl_contrato.contrato_id = tbl_contrato_maestro.contrato_id
                  left join (select tbl_tipos_documentos.idtipodocumento,
                                    tbl_tipos_documentos.descripcion,
                                    tbl_tipos_documentos.ponderado,
                                    tbl_diferencias_nc_personas.id,
                                    tbl_diferencias_nc_personas.periodo,
                                    tbl_diferencias_nc_personas.contrato_id,
                                    tbl_diferencias_nc_personas.IdEstatusDocumento
                             from tbl_tipos_documentos
                             left join tbl_diferencias_nc_personas on tbl_tipos_documentos.idTipoDocumento = tbl_diferencias_nc_personas.IdTipoDocumento
                             where tbl_tipos_documentos.ControlCheckLaboral = 1
                             and tbl_tipos_documentos.IdProceso = 4
                             and tbl_diferencias_nc_personas.IdEstatusDocumento != 0) as tbl_diferencias_nc_personas
                       on tbl_contrato_maestro.contrato_id = tbl_diferencias_nc_personas.contrato_id
                       and tbl_contrato_maestro.periodo = tbl_diferencias_nc_personas.periodo
                  where tbl_contrato_maestro.periodo <= '".self::getPeriodo()."'
                  group by tbl_contrato_maestro.id,
                         tbl_contrato_maestro.contrato_id,
                         tbl_diferencias_nc_personas.IdTipoDocumento,
                         tbl_diferencias_nc_personas.descripcion,
                         tbl_diferencias_nc_personas.ponderado

                  union all

                  SELECT
                  cm.id,
                  d.contrato_id,
                  td.Descripcion AS TipoDocumento,
                  td.Ponderado,
                  sum(case when d.IdEstatus != 5 then 1 else 0 end) as cantidad_no_conformidades,
                  count(d.contrato_id) as cantidad_total,
                  1-sum(case when d.IdEstatus != 5 then 1 else 0 end) / count(d.IdDocumento) as porcentaje
                  FROM tbl_documentos d
                  JOIN tbl_tipos_documentos td ON d.IdTipoDocumento=td.IdTipoDocumento
                  JOIN tbl_contrato_maestro cm ON cm.contrato_id = d.contrato_id
                  WHERE
                  td.ControlCheckLaboral = 1 AND
                  td.IdProceso != 4 AND
                  td.Vigencia=2
                  AND d.FechaEmision = '".self::getPeriodo()."'
                  GROUP BY cm.id,
                   d.contrato_id,
                   td.Descripcion,
                   td.Ponderado

                   union all

                  SELECT
                  cm.id,
                  cm.contrato_id,
                  td.Descripcion AS TipoDocumento,
                  td.Ponderado,
                  sum(case when d.IdEstatus != 5 then 1 else 0 end) as cantidad_no_conformidades,
                  count(d.contrato_id) as cantidad_total,
                  1-sum(case when d.IdEstatus != 5 then 1 else 0 end) / count(d.IdDocumento) as porcentaje
                  FROM tbl_documentos d
                  JOIN tbl_tipos_documentos td ON d.IdTipoDocumento=td.IdTipoDocumento
                  JOIN tbl_contrato_maestro cm ON cm.idcontratista = d.IdContratista
                  WHERE
                  td.ControlCheckLaboral = 1 AND
                  td.IdProceso != 4 AND
                  td.Vigencia=2 AND
                  td.Entidad= 1
                  AND d.FechaEmision = '".self::getPeriodo()."'
                  GROUP BY cm.id,
                  cm.contrato_id,
                  td.Descripcion,
                  td.Ponderado

                  union all

                 SELECT
                 cm.id,
                 cm.contrato_id,
                 td.Descripcion AS TipoDocumento,
                 td.Ponderado,
                 sum(case when d.IdEstatus != 5 then 1 else 0 end) as cantidad_no_conformidades,
                 count(d.contrato_id) as cantidad_total,
                 1-sum(case when d.IdEstatus != 5 then 1 else 0 end) / count(d.IdDocumento) as porcentaje
                 FROM tbl_documentos d
                 JOIN tbl_tipos_documentos td ON d.IdTipoDocumento=td.IdTipoDocumento
                 JOIN tbl_contrato_maestro cm ON cm.idcontratista = d.IdContratista
                 WHERE
                 td.ControlCheckLaboral = 1 AND
                 td.IdProceso != 4 AND
                 td.Vigencia != 2 AND
                 td.Entidad= 1 and d.fechaemision <= '".self::getPeriodo()."'
                 GROUP BY cm.id,
                 cm.contrato_id,
                 td.Descripcion,
                 td.Ponderado
                 ) as Total
                 group by Total.id, Total.contrato_id) as X";
      if ($pintContratoId){
        $lstrQuery = $lstrQuery." WHERE contrato_id = ".$pintContratoId;
      }
      $lobjDiferencias = \DB::select($lstrQuery);



      foreach ($lobjDiferencias as $larrDiferencias) {
        $lobjContratoMaestro = Contratomaestro::find($larrDiferencias->id);
        $lobjContratoMaestro->documentacion_mes_actual = $larrDiferencias->documentacion;

        if($sitio->Valor=='Fepasa'){
          $docs = \DB::table('tbl_documentos')
            ->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
            ->where('tbl_documentos.contrato_id',$larrDiferencias->contrato_id)
            ->where('tbl_documentos.FechaEmision',self::getPeriodo())
            ->whereIn('tbl_documentos.IdTipoDocumento',$tipoDoc)
            ->where('tbl_tipos_documentos.Entidad','<>',1)
            ->select('tbl_tipos_documentos.Ponderado')
            ->groupBy('tbl_documentos.IdTipoDocumento')
            ->get();
          $lintIdContratista = \DB::table('tbl_contrato')->where('contrato_id',$larrDiferencias->contrato_id)->value('IdContratista');
          $docsContratista = \DB::table('tbl_documentos')
            ->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
            ->where('tbl_documentos.IdContratista',$lintIdContratista)
            ->where('tbl_documentos.FechaEmision',self::getPeriodo())
            ->whereIn('tbl_documentos.IdTipoDocumento',$tipoDoc)
            ->where('tbl_tipos_documentos.Entidad',1)
            ->select('tbl_tipos_documentos.Ponderado')
            ->groupBy('tbl_documentos.IdTipoDocumento')
            ->get();
          $resta=0;
          foreach ($docs as $doc) {
            $resta = $resta + $doc->Ponderado;
          }
          foreach ($docsContratista as $doc) {
            $resta = $resta + $doc->Ponderado;
          }
          $resta= $resta/100;

          $lobjContratoMaestro->documentacion_mes_actual = $larrDiferencias->documentacion-$resta;

        }
        if($lobjContratoMaestro->periodo==self::getPeriodo()){
            $lobjContratoMaestro->save();
        }
      }

      $larrResult["code"] = "01";
      $larrResult["status"] = "success";
      $larrResult["message"] = "Porcentajes actualizados satisfactoriamente.";
      return $larrResult;

  }

  static public function UpdateDiscrepancias(){
    $periodoActual = self::getPeriodo();
    $periodoSiguiente = new \DateTime($periodoActual);
    $periodoSiguiente = $periodoSiguiente->modify('+1 month');
    $periodoSiguiente = $periodoSiguiente->format('Y-m-d');



    \DB::table('tbl_f301_discrepancias')->where('periodo',$periodoActual)->delete();

    $C = \DB::table('tbl_f30_1')
      ->join('tbl_contratistas','tbl_f30_1.IdContratista','=','tbl_contratistas.IdContratista')
      ->join('tbl_contrato','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
      ->select('tbl_contrato.contrato_id')
      ->groupBy('tbl_contrato.contrato_id')
      ->get();

      foreach ($C as $contrato) {

        $A = \DB::select(\DB::raw(
          "SELECT pm.idpersona FROM tbl_personas_maestro pm WHERE pm.periodo='$periodoActual' AND pm.Estatus='Vigente' AND pm.FechaEfectiva<'$periodoSiguiente' and pm.contrato_id=$contrato->contrato_id UNION
          SELECT pm.idpersona FROM tbl_personas_maestro pm WHERE pm.periodo='$periodoActual' AND pm.Estatus='Finiquitado' AND pm.FechaEfectiva>='$periodoActual' and pm.FechaEfectiva<'$periodoSiguiente' and pm.contrato_id=$contrato->contrato_id"));

        $f301 = \DB::table('tbl_f30_1')->where('contrato_id',$contrato->contrato_id)->where('Periodo',$periodoActual)->orderBy('IdF301','desc')->first();

        if($f301 and $A){
          $B = \DB::select(\DB::raw(
            "SELECT * FROM tbl_f30_1_empleados e WHERE e.IdF301=$f301->IdF301 AND NOT EXISTS(
              SELECT 1 FROM tbl_personas_maestro pm WHERE pm.periodo='$periodoActual' AND pm.Estatus='Vigente' AND pm.FechaEfectiva<'$periodoSiguiente' AND pm.contrato_id=$contrato->contrato_id AND e.IdPersona=pm.idpersona
              UNION
              SELECT 1 FROM tbl_personas_maestro pm WHERE pm.periodo='$periodoActual' AND pm.Estatus='Finiquitado' AND pm.FechaEfectiva>='$periodoActual' AND pm.FechaEfectiva<'$periodoSiguiente' AND pm.contrato_id=$contrato->contrato_id AND e.IdPersona=pm.idpersona
              )"
          ));
          $f301Empleados = \DB::table('tbl_f30_1_empleados')->where('IdF301',$f301->IdF301)->get();
          $cuenta=0;
          foreach ($f301Empleados as $empleado) {
            foreach ($A as $a) {
              if($empleado->IdPersona == $a->idpersona){
                $cuenta++;
              }
            }
          }

          $porcentaje = 1-($cuenta/(count($A)+count($B)));

          $documento = \DB::table('tbl_documentos')->where('IdDocumento',$f301->IdDocumento)->first();

          if($documento->IdEstatus!=5){
            $porcentaje=1;
          }
          \DB::table('tbl_f301_discrepancias')->insert(['periodo'=>$periodoActual,'valor'=>$porcentaje,'contrato_id'=>$contrato->contrato_id]);
          /*mensual*/
          $lobjContratoMaestro = Contratomaestro::where('periodo',$periodoActual)->where('contrato_id',$contrato->contrato_id)->first();
          $documentacionActual = $lobjContratoMaestro->documentacion_mes_actual;
          $ponderado = (1-$porcentaje)*0.1;
          $nuevaDocumentacion = $documentacionActual+$ponderado;
          $lobjContratoMaestro->documentacion_mes_actual = $nuevaDocumentacion;

          /*acumulada*/
          $periodoActual2 = new \DateTime($periodoActual);
          $promedio = \DB::table('tbl_f301_discrepancias')->where('contrato_id',$contrato->contrato_id)->where('periodo','<=',$periodoActual)->avg('valor');
          $documentacionAcumulada = $lobjContratoMaestro->documentacion;
          $ponderado = (1-$promedio)*0.1;
          $nuevaDocumentacion = $documentacionAcumulada+$ponderado;
          $lobjContratoMaestro->documentacion = $nuevaDocumentacion;

          if($lobjContratoMaestro->periodo==self::getPeriodo()){
            $lobjContratoMaestro->save();
          }

        }
      }
  }


  /// Discrepancia

  static public function agregarDiscrepanciasDocAprobado($contratoId){

    $periodoActual = self::getPeriodo();
    $periodoSiguiente = new \DateTime($periodoActual);
    $periodoSiguiente = $periodoSiguiente->modify('+1 month');
    $periodoSiguiente = $periodoSiguiente->format('Y-m-d');

    \DB::table('tbl_f301_discrepancias')->where('periodo',$periodoActual)->delete();


        $A = \DB::select(\DB::raw(
          "SELECT pm.idpersona FROM tbl_personas_maestro pm WHERE pm.periodo='$periodoActual' AND pm.Estatus='Vigente' AND pm.FechaEfectiva<'$periodoSiguiente' and pm.contrato_id=$contratoId UNION
          SELECT pm.idpersona FROM tbl_personas_maestro pm WHERE pm.periodo='$periodoActual' AND pm.Estatus='Finiquitado' AND pm.FechaEfectiva>='$periodoActual' and pm.FechaEfectiva<'$periodoSiguiente' and pm.contrato_id=$contratoId"));


        $f301 = \DB::table('tbl_f30_1')->where('contrato_id',$contratoId)->where('Periodo',$periodoActual)->orderBy('IdF301','desc')->first();

        if($f301 and $A){
          $B = \DB::select(\DB::raw(
            "SELECT * FROM tbl_f30_1_empleados e WHERE e.IdF301=$f301->IdF301 AND NOT EXISTS(
              SELECT 1 FROM tbl_personas_maestro pm WHERE pm.periodo='$periodoActual' AND pm.Estatus='Vigente' AND pm.FechaEfectiva<'$periodoSiguiente' AND pm.contrato_id=$contratoId AND e.IdPersona=pm.idpersona
              UNION
              SELECT 1 FROM tbl_personas_maestro pm WHERE pm.periodo='$periodoActual' AND pm.Estatus='Finiquitado' AND pm.FechaEfectiva>='$periodoActual' AND pm.FechaEfectiva<'$periodoSiguiente' AND pm.contrato_id=$contratoId AND e.IdPersona=pm.idpersona
              )"
          ));



          $f301Empleados = \DB::table('tbl_f30_1_empleados')->where('IdF301',$f301->IdF301)->get();
          $cuenta=0;
          foreach ($f301Empleados as $empleado) {
            foreach ($A as $a) {
              if($empleado->IdPersona == $a->idpersona){
                $cuenta++;
              }
            }
          }

          \Log::info( $cuenta);

          $porcentaje = 1-($cuenta/(count($A)+count($B)));


          $documento = \DB::table('tbl_documentos')->where('IdDocumento',$f301->IdDocumento)->first();

          if($documento->IdEstatus!=5){
            $porcentaje=1;
          }

          \Log::info( $porcentaje);

          \DB::table('tbl_f301_discrepancias')->insert(['periodo'=>$periodoActual,'valor'=>$porcentaje,'contrato_id'=>$contratoId]);
          /*mensual*/
          $lobjContratoMaestro = Contratomaestro::where('periodo',$periodoActual)->where('contrato_id',$contratoId)->first();
          $documentacionActual = $lobjContratoMaestro->documentacion_mes_actual;
          $ponderado = (1-$porcentaje)*0.1;
          $nuevaDocumentacion = $documentacionActual+$ponderado;
          $lobjContratoMaestro->documentacion_mes_actual = $nuevaDocumentacion;

          /*acumulada*/
          $periodoActual2 = new \DateTime($periodoActual);
          $promedio = \DB::table('tbl_f301_discrepancias')->where('contrato_id',$contratoId)->where('periodo','<=',$periodoActual)->avg('valor');
          $documentacionAcumulada = $lobjContratoMaestro->documentacion;
          $ponderado = (1-$promedio)*0.1;
          $nuevaDocumentacion = $documentacionAcumulada+$ponderado;
          $lobjContratoMaestro->documentacion = $nuevaDocumentacion;

          if($lobjContratoMaestro->periodo==self::getPeriodo()){
            $lobjContratoMaestro->save();
          }

        }

  }



  static private function FormatNumber($pstrNumero){
    return str_ireplace(",",".",str_ireplace(".","",$pstrNumero));
  }
  static public function getDiferencias(){
    $lobjDiferencias = Diferenciacalculo::where("periodo",self::getPeriodo());
    return $lobjDiferencias;

  }

  static public function Open($ldatPeriodo){

    $lobjResultado = array();
    $ldatPeriodo = $ldatPeriodo;

    if ($ldatPeriodo < self::getPeriodo()) {

      $lobjResultado = array("code"=>"0","status"=>"error","message"=>"Error periodo a abrir no puede ser menor al actual");

    }else{

      $lstrQuery = "SELECT fnCierreLaboral('".$ldatPeriodo."',0) as Resultado from dual;";
      $lobjResultado = \DB::select($lstrQuery);

      if ($lobjResultado){
        $lstrResultado = $lobjResultado[0]->Resultado;
        $larrResultado = explode("|",$lstrResultado);
        $lintCodigo = $larrResultado[0];
        $lstrResultado = $larrResultado[1];
        $lobjResultado = array("code"=>$lintCodigo,"status"=>"success","message"=>$lstrResultado);

        self::UpdateDocument();
        self::UpdateDocumentMesActual();
        $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();

        if($sitio->Valor=='Transbank'){
          self::UpdateDiscrepancias(); //tbl_f301_discrepancias
        }

      }else{
        $lobjResultado = array("code"=>"0","status"=>"error","message"=>"Error iniciando un nuevo cierre");
      }

    }

    return $lobjResultado;
  }

  static public function ReOpen(){

    $lobjResultado = array();
    $ldatPeriodo = self::getPeriodo();

    if ($ldatPeriodo != self::getPeriodo()) {

      $lobjResultado = array("code"=>"0","status"=>"error","message"=>"Error periodo a reprocesar no puede ser distinto al actual");

    }else{

      $lstrQuery = "SELECT fnCierreLaboral('".$ldatPeriodo."',1) as Resultado from dual;";
      $lobjResultado = \DB::select($lstrQuery);

      if ($lobjResultado){
        $lstrResultado = $lobjResultado[0]->Resultado;
        $larrResultado = explode("|",$lstrResultado);
        $lintCodigo = $larrResultado[0];
        $lstrResultado = $larrResultado[1];
        $lobjResultado = array("code"=>$lintCodigo,"status"=>"success","message"=>$lstrResultado);

        self::UpdateDocument();
        self::UpdateDocumentMesActual();
        $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();

        if($sitio->Valor=='Transbank'){
          self::UpdateDiscrepancias(); //tbl_f301_discrepancias
        }

      }else{
        $lobjResultado = array("code"=>"0","status"=>"error","message"=>"Error reprocesando el cierre");
      }

    }

    return $lobjResultado;
  }

  static public function Delete(){

    $lobjResultado = array();
    $ldatPeriodo = self::getPeriodo();

    //metodo que elimina el periodo actual
    $lstrQuery = "SELECT fnCierreLaboral('".$ldatPeriodo."',2) as Resultado from dual;";
    $lobjResultado = \DB::select($lstrQuery);

    if ($lobjResultado){
      $lstrResultado = $lobjResultado[0]->Resultado;
      $larrResultado = explode("|",$lstrResultado);
      $lintCodigo = $larrResultado[0];
      $lstrResultado = $larrResultado[1];
      $lobjResultado = array("code"=>$lintCodigo,"status"=>"success","message"=>$lstrResultado);
    }else{
      $lobjResultado = array("code"=>"0","status"=>"error","message"=>"Error eliminado cierre");
    }

    return $lobjResultado;
  }

  static public function AddPerson($pintIdPersona, $pintIdContratista, $pintContratoId, $pdatFechaEfectiva = ""){

    $lobjPersonas = Personasmaestro::where('periodo',self::getPeriodo())
                    ->where('contrato_id',$pintContratoId)
                    ->where('IdPersona',$pintIdPersona)
                    ->first();

    if (!$lobjPersonas){
      $lobjPersonas = new Personasmaestro();
      $lobjPersonas->periodo = self::getPeriodo();
      $lobjPersonas->IdPersona = $pintIdPersona;
      $lobjPersonas->IdContratista = $pintIdContratista;
      $lobjPersonas->contrato_id = $pintContratoId;
      $lobjPersonas->Estatus = 'Vigente';
      $lobjPersonas->FechaEfectiva = $pdatFechaEfectiva;
      $lobjPersonas->save();

      //Actualizamos la dotación
      $lobjContrato = Contratomaestro::where('periodo',self::getPeriodo())
                      ->where('contrato_id',$pintContratoId)
                      ->first();

      if ($lobjContrato){
        $lobjContrato = Contratomaestro::find($lobjContrato->id);
        $lobjContrato->dotacion = $lobjContrato->dotacion+1;
        $lobjContrato->save();
      }

    }

    return array("code"=>1,"status"=>"success","message"=>"Persona agregada satisfactoriamente");

  }

  static public function LeavePeople($pintIdPersona, $pintIdContratista, $pintContratoId, $pdatFechaEfectiva) {

    $lobjPersonas = Personasmaestro::where('periodo',self::getPeriodo())
                    ->where('contrato_id',$pintContratoId)
                    ->where('IdPersona',$pintIdPersona)
                    ->first();

    if ($lobjPersonas){

      $ldatFecha = new DateTime(self::getPeriodo());
      $ldatFecha->modify('last day of this month');
      $ldatFecha = $ldatFecha->format('Y-m-d');
      if ($pdatFechaEfectiva <= $ldatFecha){

        $lobjPersonas->Estatus = 'Finiquitado';
        $lobjPersonas->FechaAnterior = $lobjPersonas->FechaEfectiva;
        $lobjPersonas->FechaEfectiva = $pdatFechaEfectiva;
        $lobjPersonas->save();


      }
    }else{
      $lobjPersonas = new Personasmaestro();
      $lobjPersonas->periodo = self::getPeriodo();
      $lobjPersonas->IdPersona = $pintIdPersona;
      $lobjPersonas->IdContratista = $pintIdContratista;
      $lobjPersonas->contrato_id = $pintContratoId;
      $lobjPersonas->FechaEfectiva = $pdatFechaEfectiva;
      $lobjPersonas->Estatus = 'Finiquitado';
      $lobjPersonas->save();

      //Actualizamos la dotación
      $lobjContrato = Contratomaestro::where('periodo',self::getPeriodo())
                      ->where('contrato_id',$pintContratoId)
                      ->first();

      if ($lobjContrato){
        $lobjContrato = Contratomaestro::find($lobjContrato->id);
        $lobjContrato->dotacion = $lobjContrato->dotacion+1;
        $lobjContrato->save();
      }

    }

    return array("code"=>1,"status"=>"success","message"=>"Persona actualizada satisfactoriamente");

  }

}
