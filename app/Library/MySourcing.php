<?php

use App\Models\Contratistas;
use App\Models\TblConfiguracion;
use Freshwork\ChileanBundle\Rut;
use App\Models\Contratos;
use App\Library\MyDocuments;

class MySourcing
{
  static $larrItems;
  static protected $garrProcesos = array("F301"=>1,
                                       "ContratoTrabajo" => 21,
                                       "ContratoTrabajoAnexo"=>3,
                                       "Finiquito"=>4,
                                       "Previred"=>212,
                                       "PreviredTrabajador"=>120);
## Formatos de columnas ##
static public function FormatCurrency($num){
  if ($num) {
    return "$".number_format($num, 0, ',', '.');
  }else{
    return "$0,00";
  }
}

static public function ConvierteConsultaFiltro($parrParametros){
	$larrResult = new Contratos;
	if ($parrParametros){
		foreach ($parrParametros as $larrParametros) {
			if ($larrParametros['type'] == "select"){
				$larrResult = $larrResult->whereIn('tbl_contrato.'.$larrParametros['data']['field'], $larrParametros['data']['values']);
			}else if ($larrParametros['type'] == "selectmultiple"){
				$larrResult = $larrResult->whereIn('tbl_contrato.'.$larrParametros['data']['field'], $larrParametros['data']['values'][0]);
			}else if ($larrParametros['type'] == "text") {
				$larrResult = $larrResult->where('tbl_contrato.'.$larrParametros['data']['field'],'like', $larrParametros['data']['values']);
			}else if ( $larrParametros['type'] == "number" || $larrParametros['type'] == "date" ) {
				$larrResult = $larrResult->where('tbl_contrato.'.$larrParametros['data']['field'],'=', $larrParametros['data']['values']);
			}else if ($larrParametros['type'] == "numberbtw" || $larrParametros['type'] == "datebtw" ){
				if ( (isset($larrParametros['data']['values'][0]) && $larrParametros['data']['values'][0] != '') && (isset($larrParametros['data']['values'][1]) && $larrParametros['data']['values'][1] != '') ) {
					$larrResult = $larrResult->whereBetween('tbl_contrato.'.$larrParametros['data']['field'],'=', $larrParametros['data']['values']);
				}else if ( (isset($larrParametros['data']['values'][0]) )  && ($larrParametros['data']['values'][0] != '') ){
					$larrResult = $larrResult->where('tbl_contrato.'.$larrParametros['data']['field'],'>', $larrParametros['data']['values'][0]);
				}else if ( (isset($larrParametros['data']['values'][1]) ) && ($larrParametros['data']['values'][1] != '') ){
					$larrResult = $larrResult->where('tbl_contrato.'.$larrParametros['data']['field'],'<', $larrParametros['data']['values'][1]);
				}

			}
		}
	}
	return $larrResult;

}

static public function FormatNumber($num){
  if ($num) {
    return "".number_format($num, 2, ',', '.');
  }else{
    return "0,00";
  }
}

static public function FormatPriority($pintIdPrioridad){
	switch ($pintIdPrioridad) {
    case 1:
       return '<span class="label label-danger" > Alta </span>';
        break;
    case 2:
        return '<span class="label label-warning" > Media </span>';
        break;
    case 3:
        return '<span class="label label-success" > Baja </span>';
        break;
    }
}

static public function FormatRut($pstrRut, $pstrPais = NULL){

	if (is_null($pstrPais)){
		if (!is_null(\app('session')->get('CNF_PAIS'))){
			$pstrPais = \app('session')->get('CNF_PAIS');
		}else{
			$pstrPais = 'CL'; //Por defecto cargamos Chile
		}
	}

	//Realizamos una limpieza del rut, quitando cualquier caracter comun en un copiado
	$lstrRut = trim(str_replace(chr(194),"",str_replace(chr(9),"",str_replace(chr(10),"",str_replace(chr(13),"",str_replace(".","",$pstrRut))))));

	if ($pstrPais=='CL') { //Aplicamos formato para Chile
		$larrRut = explode('-',$lstrRut);
		if (count($larrRut)==2){
			$lintRut =  (int) $larrRut[0];
			$lstrResultado = $lintRut.'-'.$larrRut[1];
		}else{
			$lstrResultado = $lstrRut;
		}
	}else{ // Cualquier otro pais
		$lstrResultado = $lstrRut;
	}
	return strtoupper($lstrResultado);
}

/**
 * Descripción: Función que devuelve los contratos disponibles para el usuario
 * Autor: Diego Díaz
 * Fecha: 11/01/2018
 *
 * @return Array
 */
static public function getFiltroUsuario($pintIdTipoRespuesta=0, $pbolReload=false){

	$larrData = array();
	$larrDataContrato = array();
	$larrDataContratista = array();

	$lintGroupUser = self::GroupUser(\Session::get('uid'));
	$lintLevelUser = self::LevelUser(\Session::get('uid'));
  $lintIdUser = \Session::get('uid');

  switch ($lintLevelUser) {
    case '1':
    case '7':
    case '8':{
      $lobjContratos = \DB::table('tbl_contrato')
        ->select('tbl_contrato.contrato_id')
        ->get();
      $lobjContratistas = \DB::table('tbl_contratistas')
        ->select('tbl_contratistas.IdContratista')
        ->get();
      }
      break;
    case '4':{
      $lobjContratos = \DB::table('tbl_contrato')
        ->select('tbl_contrato.contrato_id')
        ->where('tbl_contrato.admin_id','=',$lintIdUser)
        ->get();
      $lobjContratistas = \DB::table('tbl_contratistas')
        ->select('tbl_contratistas.IdContratista')
        ->whereExists(function ($query) use ($lintIdUser) {
          $query->select(\DB::raw(1))
            ->from('tbl_contrato')
            ->whereRaw('tbl_contrato.admin_id = '.$lintIdUser)
            ->whereRaw('tbl_contrato.IdContratista = tbl_contratistas.IdContratista');
        })
        ->get();
      }
      break;
    case '6':
    case '15':{
      $lobjContratos = \DB::table('tbl_contrato')
  		  ->select('tbl_contrato.contrato_id')
  		  ->where('tbl_contrato.entry_by_access','=',$lintIdUser)
        ->get();
      $lobjContratistas = \DB::table('tbl_contratistas')
  		  ->select('tbl_contratistas.IdContratista')
  		  ->where('tbl_contratistas.entry_by_access','=',$lintIdUser)
        ->get();
      }
      break;
    case '20':{
      $lobjContratos = \DB::table('tbl_contrato')
        ->select('tbl_contrato.contrato_id')
        ->where('usuarioContrato',$lintIdUser)
        ->get();
      $lobjContratistas = \DB::table('tbl_contratistas')
        ->join('tbl_contrato','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
        ->where('usuarioContrato',$lintIdUser)
        ->select('tbl_contratistas.IdContratista')
        ->get();
      }
      break;
    case '21':
    case '22':
    case '23':
    case '24':
    case '25':
    {
      $lobjContratos = \DB::table('tbl_contrato')
        ->join('tbl_groups_levels_assoc_contract','tbl_groups_levels_assoc_contract.contrato_id','=','tbl_contrato.contrato_id')
        ->select('tbl_contrato.contrato_id')
        ->where('tbl_groups_levels_assoc_contract.level',$lintLevelUser)
        ->where('tbl_groups_levels_assoc_contract.user_id','=',$lintIdUser)
        ->get();
      $lobjContratistas = \DB::table('tbl_contratistas')
        ->join('tbl_contrato','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
        ->join('tbl_groups_levels_assoc_contract','tbl_groups_levels_assoc_contract.contrato_id','=','tbl_contrato.contrato_id')
        ->where('tbl_groups_levels_assoc_contract.level',$lintLevelUser)
        ->where('tbl_groups_levels_assoc_contract.user_id','=',$lintIdUser)
        ->select('tbl_contratistas.IdContratista')
        ->get();
      }
      break;
    default:
      {
        $lobjContratos = \DB::table('tb_assocc')
          ->select('tb_assocc.contrato_id')
          ->where('tb_assocc.user_id','=',$lintIdUser)
          ->whereNotNull('tb_assocc.contrato_id')
          ->get();
        $lobjContratistas = \DB::table('tb_assocc')
          ->select(\DB::raw('tb_assocc.contratista_id as IdContratista'))
          ->distinct()
          ->where('tb_assocc.user_id','=',$lintIdUser)
          ->get();
      }
      break;
  }

    foreach ($lobjContratos as $larrContrato) {
		$larrDataContrato[] = $larrContrato->contrato_id;
	}
	foreach ($lobjContratistas as $larrContratistas) {
		$larrDataContratista[] = $larrContratistas->IdContratista;
	}

    $larrData['contratos'] = $larrDataContrato;
    $larrData['contratistas'] = $larrDataContratista;

    if ($pintIdTipoRespuesta==0){
    	session(['sesion_contratos' => $larrData['contratos']]);
    	session(['sesion_contratistas' => $larrData['contratistas']]);
    }else if ($pintIdTipoRespuesta==1) {

        if (count($larrDataContrato)==0) {
            $larrData['contratos'] = "''";
        }else{
            $larrData['contratos'] = implode(',', $larrDataContrato);
        }
        if (count($larrDataContratista)==0){
            $larrData['contratistas'] = "''";
        }else{
            $larrData['contratistas'] = implode(',', $larrDataContratista);
        }

    	return $larrData;
    }else{
    	return $larrData;
    }

    session(['sesion_contratos' => $larrData['contratos']]);
    session(['sesion_contratistas' => $larrData['contratistas']]);

}

/**
 * Descripción: Función que controla la lista de procesos que se van implementando en el sistema,
 *              de manera de no utilizar un código que ya esté siendo utilizado en otro proceso.
 * Regresa la lista de procesos declarados en el sistema.
 *
 * @return Array
 */
static public function getProcessNumber() {
	$larrData = array(
		               array("IdProceso" => "1", "Codigo" => "F301", "Descripcion" => "F30-1 Empresas"),
		               array("IdProceso" => "2", "Codigo" => "F301PERSONAS", "Descripcion" => "F30-1 para personas"),
		               array("IdProceso" => "3", "Codigo" => "ANEXOTRABAJADOR", "Descripcion" => "Documento anexo de trabajador"),
		               array("IdProceso" => "4", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "5", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "6", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "7", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "8", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "9", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "10", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "11", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "12", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "13", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "14", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "15", "Codigo" => "", "Descripcion" => ""),
		               array("IdProceso" => "16", "Codigo" => "MULTASCL", "Descripcion" => "Proceso de carga de multas para chile"),
		               array("IdProceso" => "17", "Codigo" => "MULTASAR", "Descripcion" => "Proceso de carga de multas formato general"),
		              );
	return $larrData;
}

static public function ValidateRut($pstrRut, $pstrPais = NULL){
	if (is_null($pstrPais)){
		if (!is_null(\app('session')->get('CNF_PAIS'))){
			$pstrPais = \app('session')->get('CNF_PAIS');
		}else{
			$pstrPais = 'CL'; //Por defecto cargamos Chile
		}
	}
	if ($pstrPais=='CL'){
		$larrRut = explode('-',$pstrRut);
		if (count($larrRut)==2){
			try {
				$lintRut =  (int) $larrRut[0];
				if ($lintRut<=0){
					return false;
				}
			} catch (Exception $e) {
				return false;
			}
			$lintRutValidador = $larrRut[1];
		}else{
			return false;
		}
		if (Rut::parse($lintRut.'-'.$lintRutValidador)->quiet()->validate()){
	    	return Rut::parse($lintRut.'-'.$lintRutValidador)->isValid();
		}else{
			return false;
		}
	}else{
		return true;
	}
}

static public function SetConfiguracion(){
	$larrConfiguracion = array();
	$larrConfiguracion['CNF_APPNAME'] = 'Sourcing One';
	$larrConfiguracion['CNF_APPDESC'] = 'Gestión y control de contratistas';
	$larrConfiguracion['CNF_COMNAME'] = 'Sourcing';
	$larrConfiguracion['CNF_EMAIL'] = 'sistemas@sourcing.cl';
	$larrConfiguracion['CNF_METAKEY'] = 'my site , my company  , Larvel Crud';
	$larrConfiguracion['CNF_METADESC'] = 'Write description for your site';
	$larrConfiguracion['CNF_GROUP'] = '15';
	$larrConfiguracion['CNF_ACTIVATION'] = 'auto';
	$larrConfiguracion['CNF_MULTILANG'] = '1';
	$larrConfiguracion['CNF_LANG'] = 'es';
	$larrConfiguracion['CNF_REGIST'] = 'true';
	$larrConfiguracion['CNF_FRONT'] = 'false';
	$larrConfiguracion['CNF_RECAPTCHA'] = 'false';
	$larrConfiguracion['CNF_THEME'] = 'default';
	$larrConfiguracion['CNF_THEME_SOURCING'] = 'default';
	$larrConfiguracion['CNF_RECAPTCHAPUBLICKEY'] = '';
	$larrConfiguracion['CNF_RECAPTCHAPRIVATEKEY'] = '';
	$larrConfiguracion['CNF_MODE'] = 'production';
	$larrConfiguracion['CNF_LOGO'] = 'logo.png';
	$larrConfiguracion['CNF_LOGO_LIGHT'] = 'logolight.png';
	$larrConfiguracion['CNF_BACKGROUND'] = 'background.png';
	$larrConfiguracion['CNF_FAVICON'] = 'favicon.ico';
	$larrConfiguracion['CNF_ALLOWIP'] = '';
	$larrConfiguracion['CNF_RESTRICIP'] = '192.116.134 , 194.111.606.21 ';
	$larrConfiguracion['CNF_MAIL'] = 'phpmail';
	$larrConfiguracion['CNF_DATE'] = 'm/d/y';
	$larrConfiguracion['CNF_MODULO_PERSONAS'] = '1';
	$larrConfiguracion['CNF_MODULO_ACCESOS_PERSONAS'] = '1';
	$larrConfiguracion['CNF_MODULO_PARTIDAS'] = '1';
	$larrConfiguracion['CNF_MODULO_ACTIVOS'] = '1';
	$larrConfiguracion['CNF_MODULO_ACCESOS_ACTIVOS'] = '1';
	$larrConfiguracion['CNF_MODULO_FISICO'] = '1';
	$larrConfiguracion['CNF_TEMPLATE_CONTRATISTA'] = 'form';
	$larrConfiguracion['CNF_TEMPLATE_CONTRATO'] = 'form';

	$larrConfiguracion['CNF_PAIS'] = 'CL';

	$lobjConfiguracion = TblConfiguracion::all();
	//if (is_null(\app('session')->get('CNF_APPNAME'))){
	    foreach ($lobjConfiguracion as $Configuracion) {
	  	    \app('session')->put($Configuracion->Nombre,$Configuracion->Valor);
		}
	//}
	foreach ($larrConfiguracion as $lstrNombre => $lstrValor) {
		$lstrValor = !is_null(\app('session')->get($lstrNombre))?\app('session')->get($lstrNombre):$lstrValor;
		if (!defined($lstrNombre)) define($lstrNombre,$lstrValor);
	}
	return true;
}

static public function ProccessDocument($pintIdDocumento,$pobjDocumento){
  	$larrResult = array();
  	//Obtenemos el tipo de documento
  	$lintIdTipo = $pobjDocumento['IdTipoDocumento'];
    $tipoDocumento = \DB::table('tbl_tipos_documentos')->where('IdTipoDocumento',$lintIdTipo)->first();
    $idProceso = $tipoDocumento->IdProceso;
    if(self::$garrProcesos['F301']==$idProceso){ //F30-1
  		$lstrNombreFull = "uploads/documents/".$pobjDocumento['DocumentoURL'];
        $parser = new \Smalot\PdfParser\Parser();
	    $pdf = $parser->parseFile($lstrNombreFull);
	    try {
	    	$lbloArchivoTexto = $pdf->getText();
	    } catch (Exception $e) {
	    	$lbloArchivoTexto = "";
	    }
        $lbloArchivoTexto = str_replace("\n"," ",$lbloArchivoTexto); //remplaza los fines de lineas
        $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados
        $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados
        $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados
        $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados
        $pobjDocumento['DocumentoTexto'] = $lbloArchivoTexto;
  	}
  	if (self::$garrProcesos['F301']==$idProceso){ //F30-1
  		$larrResult = self::parserFthirty($pobjDocumento['DocumentoTexto']); //Aplicamos inteligencia al texto del archivo
		$lintCode = isset($larrResult["code"])?$larrResult["code"]:0;
		if ($lintCode==1) {
			//Enviamos a guardar el Archivo
			$lobjFthirty = isset($larrResult["result"])?$larrResult["result"]:'';
			if (date('Y-m',strtotime($pobjDocumento["FechaEmision"])) != date('Y-m',strtotime($lobjFthirty['PERIODO']))) {
				$pobjDocumento["IdEstatus"] = 3;
				$larrResult = "La fecha del documento no coincide con la solicitud";
			}else{
				$larrResult = \MySourcing::parserFthirtySave($larrResult['result'], $pobjDocumento);
				$lintCode = isset($larrResult["code"])?$larrResult["code"]:0;
				if ($lintCode > 1) {
					$pobjDocumento["IdEstatus"] = 3;
				}else{

					//Si tiene aprobadores queda por aprobar
					$lobjMyDocumentos = new MyDocuments($pintIdDocumento);
					$lobjDocumento = $lobjMyDocumentos::getDatos();
					if (count($lobjDocumento->TipoDocumento->Aprobadores)){
			            $pobjDocumento["IdEstatus"] = 2;
			        }else{
			            $pobjDocumento["IdEstatus"] = 5;
			        }

				}
			}
		}else{
			$pobjDocumento["IdEstatus"] = 3;
		}
		//Limpiamos la fecha de emision y fecha de vencimiento ya que ahora no va a ser actualizada por el proceso sino que va a venir
		unset($pobjDocumento["FechaVencimiento"]);
		unset($pobjDocumento["FechaEmision"]);
		//Limpiamos la fecha de emision y fecha de vencimiento
		$lintCode = isset($larrResult["code"])?$larrResult["code"]:0;
		$lstrMessage = isset($larrResult["message"])?$larrResult["message"]:$larrResult;
		$pobjDocumento["Resultado"] = $lstrMessage;
  	}elseif  ($lintIdTipo==5){ //Archivo de inducción
  		$larrResult = \MySourcing::ProccessInduction($pobjDocumento);
  	}elseif  ($lintIdTipo==7){ //Archivo prework
  		$file = Input::file('DocumentoURLFirma');
	 	if(!empty($file)){
			$destinationPath = 'uploads/documents/';
			$filename = $file->getClientOriginalName();
			$extension =$file->getClientOriginalExtension(); //if you need extension of the file
			$rand = rand(1000,100000000);
			$newfilename = strtotime(date('Y-m-d H:i:s')).'-'.$rand.'.'.$extension;
			$uploadSuccess = $file->move($destinationPath, $newfilename);
		}

  		$larrResult = \MySourcing::ProccessPrework($pobjDocumento,$newfilename);
  		if( !empty($uploadSuccess )) {
		   $pobjDocumento['DocumentoURL'] = $newfilename;
		}
  	}elseif  ($lintIdTipo==10){ //Archivo de estado de pago
  		$lstrResultado = \MySourcing::Estadodepago($pobjDocumento);
  		if ($lstrResultado["code"]!=1){
  			$pobjDocumento["IdEstatus"] = 3;
  			$pobjDocumento["Resultado"] = $lstrResultado["message"];
  		}
  	}elseif($lintIdTipo==11){ //Libro de Remuneraciones
      $larrResult = \MySourcing::ProccessLibroRemun($pobjDocumento);
    }elseif($lintIdTipo==112){ //Previred
	  $lstrNombreFull = "uploads/documents/".$pobjDocumento['DocumentoURL'];

      $parser = new \Smalot\PdfParser\Parser();
      $pdf    = $parser->parseFile($lstrNombreFull);
      $lbloArchivoTexto = $pdf->getText();
      $lbloArchivoTexto = str_replace("\n"," ",$lbloArchivoTexto); //remplaza los fines de lineas
      $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados
      $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados
      $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados
      $lbloArchivoTexto = str_replace("  "," ",$lbloArchivoTexto); //elimina los dobles espaciados
      $pobjDocumento['DocumentoTexto'] = $lbloArchivoTexto;
      $larrResult = self::ProccessPrevired($pobjDocumento['DocumentoTexto']); //Aplicamos inteligencia al texto del archivo
	  $lintCode = isset($larrResult["code"])?$larrResult["code"]:0;
	  $pobjDocumento["IdEstatus"] = 5;
    }
    //var_dump($pobjDocumento);
    unset($pobjDocumento['updatedOn']);
  	\DB::table("tbl_documentos")->where("IdDocumento",$pintIdDocumento)->update($pobjDocumento);

  	return $larrResult;
  }

  static public function ProccessPrevired($pstrText){
  		$larrDataResult = array();
  		$larrDataResult['code'] = "1";
		$larrDataResult['status'] = "success";
		$larrDataResult['message'] = "Archivo procesado satisfactoriamente";
		$larrDataResult['result'] = '';
		return $larrDataResult;
  }

  static public function LimpiaGuiones($pstrText){
  	$pstrText = str_replace("A-", "A ", $pstrText);
  	$pstrText = str_replace("B-", "B ", $pstrText);
  	$pstrText = str_replace("C-", "C ", $pstrText);
  	$pstrText = str_replace("D-", "D ", $pstrText);
  	$pstrText = str_replace("E-", "E ", $pstrText);
  	$pstrText = str_replace("F-", "F ", $pstrText);
  	$pstrText = str_replace("G-", "G ", $pstrText);
  	$pstrText = str_replace("H-", "H ", $pstrText);
  	$pstrText = str_replace("I-", "I ", $pstrText);
  	$pstrText = str_replace("J-", "J ", $pstrText);
  	$pstrText = str_replace("K-", "K ", $pstrText);
  	$pstrText = str_replace("L-", "L ", $pstrText);
  	$pstrText = str_replace("M-", "M ", $pstrText);
  	$pstrText = str_replace("N-", "N ", $pstrText);
  	$pstrText = str_replace("O-", "O ", $pstrText);
  	$pstrText = str_replace("P-", "P ", $pstrText);
  	$pstrText = str_replace("Q-", "Q ", $pstrText);
  	$pstrText = str_replace("R-", "R ", $pstrText);
  	$pstrText = str_replace("S-", "S ", $pstrText);
  	$pstrText = str_replace("T-", "T ", $pstrText);
  	$pstrText = str_replace("U-", "U ", $pstrText);
  	$pstrText = str_replace("V-", "V ", $pstrText);
  	$pstrText = str_replace("W-", "W ", $pstrText);
  	$pstrText = str_replace("X-", "X ", $pstrText);
  	$pstrText = str_replace("Y-", "Y ", $pstrText);
  	$pstrText = str_replace("Z-", "Z ", $pstrText);
  	$pstrText = str_replace("A -", "A ", $pstrText);
  	$pstrText = str_replace("B -", "B ", $pstrText);
  	$pstrText = str_replace("C -", "C ", $pstrText);
  	$pstrText = str_replace("D -", "D ", $pstrText);
  	$pstrText = str_replace("E -", "E ", $pstrText);
  	$pstrText = str_replace("F -", "F ", $pstrText);
  	$pstrText = str_replace("G -", "G ", $pstrText);
  	$pstrText = str_replace("H -", "H ", $pstrText);
  	$pstrText = str_replace("I -", "I ", $pstrText);
  	$pstrText = str_replace("J -", "J ", $pstrText);
  	$pstrText = str_replace("K -", "K ", $pstrText);
  	$pstrText = str_replace("L -", "L ", $pstrText);
  	$pstrText = str_replace("M -", "M ", $pstrText);
  	$pstrText = str_replace("N -", "N ", $pstrText);
  	$pstrText = str_replace("O -", "O ", $pstrText);
  	$pstrText = str_replace("P -", "P ", $pstrText);
  	$pstrText = str_replace("Q -", "Q ", $pstrText);
  	$pstrText = str_replace("R -", "R ", $pstrText);
  	$pstrText = str_replace("S -", "S ", $pstrText);
  	$pstrText = str_replace("T -", "T ", $pstrText);
  	$pstrText = str_replace("U -", "U ", $pstrText);
  	$pstrText = str_replace("V -", "V ", $pstrText);
  	$pstrText = str_replace("W -", "W ", $pstrText);
  	$pstrText = str_replace("X -", "X ", $pstrText);
  	$pstrText = str_replace("Y -", "Y ", $pstrText);
  	$pstrText = str_replace("Z -", "Z ", $pstrText);
  	return $pstrText;
  }

  static public function parserFthirty($pstrText){

		$larrData = array();
		$larrDataResult = array();
		$lstrTextProcess = "0";
		$lstrText = $pstrText;
		//Ejemplo: DIRECCIÓN DEL TRABAJO Nº: Codigo Oficina AÑO CERTIFICADO 2000 2015 3219003 CERTIFICADO DE CUMPLIMIENTO DE OBLIGACIONES LABORALES Y PREVISIONALES La Dirección del Trabajo, respecto de la empresa solicitante que se individualiza a continuación, en su calidad de CONTRATISTA y de conformidad con la información entregada en la Solicitud de Certificado, que es de su responsabilidad, certifica lo siguiente: 1.- INDIVIDUALIZACIÓN DEL SOLICITANTE RUT RAZÓN SOCIAL / NOMBRE 76355804-5 SOURCING SPA RUT REP. LEGAL REPRESENTANTE LEGAL 13028567-8 EUGENIO CEPEDA SÁNCHEZ DOMICILIO Av. Nueva Providencia 2155, of. 601, TC REGIÓN COMUNA TELÉFONO 13 PROVIDENCIA 76494634 CÓDIGO DE ACTIVIDAD ECONÓMICA (CAE) SERVICIOS DE INGENIERIA PRESTADOS POR EMPRESAS N.C.P. 2.- ANTECEDENTES DE LA OBRA, EMPRESA O FAENA OBJETO DEL CERTIFICADO NOMBRE DE LA OBRA, FAENA, PUESTO DE TRABAJO O SERVICIO SEGÚN CONTRATO CIVIL División Ministro Hales DOMICILIO DE LA OBRA Calama REGIÓN COMUNA LOCALIDAD (SI CORRESPONDE) 02 CALAMA 2.1.- SITUACIÓN DE LOS TRABAJADORES DECLARADOS A LA FECHA DE LA SOLICITUD DESVINCULADOS EN EL PERÍODO TOTAL TRABAJADORES VIGENTES 0 7 2.2.- ESTADO DE LAS COTIZACIONES PREVISIONALES PAGADAS NO PAGADAS SE ADJUNTA NÓMINA X No 2.3.- DETALLE DE REMUNERACIONES MES AÑO N° TRABAJADORES CON PAGO MONTO PAGADO ($) N° TRABAJADORES SIN PAGO 11 2015 7 11198938 0 2.4.- DETALLE DE INDEMNIZACIONES 2.4.1.- INDEMNIZACIÓN SUSTITUTIVA DEL AVISO PREVIO N° TRABAJADORES CON PAGO MONTO PAGADO ($) N° TRABAJADORES SIN PAGO - - - 2.4.2.- INDEMNIZACION POR AÑO(S) DE SERVICIO N° TRABAJADORES CON PAGO MONTO PAGADO ($) N° TRABAJADORES SIN PAGO - - - 3.- ANTECEDENTES DE LA EMPRESA PRINCIPAL RUT RAZÓN SOCIAL / NOMBRE 61704000-K Corporación Nacional del Cobre de Chile RUT REP. LEGAL REPRESENTANTE LEGAL 11820221-K Ignacio Tejeda Salazar DOMICILIO DE EMPRESA PRINCIPAL Huerfanos 1270 REGIÓN COMUNA TELÉFONO 13 SANTIAGO 66171344 4.- OBJETIVO DEL CERTIFICADO CURSAR ESTADOS DE PAGO DEVOLUCIÓN DE GARANTÍA CUMPLIMIENTO DE OBLIGACIONES X - 5.- PERÍODO CERTIFICADO Y ÁMBITO DE VALIDEZ El presente Certificado cubre exclusivamente la Obra, Empresa o Faena señalada en el punto 2 anterior y por el período comprendido entre 11/2015  y  11/2015 , siendo válido en todo el territorio nacional. 6.- REQUISITOS DE VALIDEZ Este Certificado tiene validez sin enmendaduras y con su respectivo CÓDIGO DE VERIFICACIÓN. 7.- OBSERVACIÓN FINAL La empresa principal deberá verificar que los datos consignados en el presente Certificado, entregados por el propio solicitante correspondan a la realidad de los servicios prestados en su calidad de contratista o subcontratista, según sea el caso, como por ejemplo “TOTAL TRABAJADORES VIGENTES”, del punto 2.1 del presente Certificado. GABRIEL ISMAEL RAMIREZ ZUÑIGA SUBJEFE DEPARTAMENTO DE INSPECCIÓN DIRECCION DEL TRABAJO - Fecha de emisión en linea 16-12-2015 12:59:33 Hrs. - Es de responsabilidad de la empresa principal o contratista, según corresponda, verificar la validez del certificado en el sitio web de la Dirección del Trabajo, <004B005700570053001D001200120057005500440050004C0057004800560011004700570011004A0052004500110046004F00120057005500440050004C00570048005600480051004F004C00510048 00440012003900480055004C0049004C004600440047005200550037005500440050004C0057004800560012003900480055004C0049004C004600440047005200550037005500440050004C00570048 00560011004400560053005B> (Ingresar el folio en el recuadro “Verificación de Trámites”, y seleccionar el trámite “Certificado Cumplimiento de Obligaciones Laborales”). - El certificado se podrá verificar hasta 60 días después de su emisión. - El presente Certificado incorpora Firma electrónica Avanzada. Ht5w8K3N Código de Verificación   CERTIFICADO 2000/2015/3219003 Detalle por mes, de los trabajadores declarados en la certificación Nómina de Trabajadores MES AÑO RUT NOMBRE TRABAJADOR 11 2015 12722349-1 EDUARDO ALEX MATURANA TEUSCHER 11 2015 13028567-8 EUGENIO ERNESTO CEPEDA SANCHEZ 11 2015 13065447-9 ABEL MAURICIO CARREÑO MIRANDA 11 2015 15525396-7 DANNY ALEJANDRO LETELIER CABEZAS 11 2015 8111914-7 JUAN PABLO CANCINO GONZALEZ 11 2015 8890969-0 GABRIEL ARTURO CARRASCO DONOSO 11 2015 9293085-8 JORGE LEANDRO ABARCA CORNEJO TOTAL DE TRABAJADORES: 7

		if (!$pstrText){
			$larrDataResult['code'] = "4";
		    $larrDataResult['message'] = "El formato del documento no corresponde al solicitado.";
		    $larrDataResult['result'] = "";
		    return $larrDataResult;
		}

		//Extraemos la empresa a la que se le está cargando el archivo
		$lstrTextProcess = strrpos($lstrText,"1.- INDIVIDUALIZACIÓN DEL SOLICITANTE RUT RAZÓN SOCIAL / NOMBRE"); //Usamos como etiqueta clave el encabezado de la linea

		if ($lstrTextProcess){ //Si el encabezado de la linea existe, entonces procedemos a revisar
			$lstrTextProcess = trim(substr($lstrText, $lstrTextProcess+65)); //Tomamos la información que se encuentra luego del encabezado.
			$lstrTextProcess = trim(substr($lstrTextProcess, 0, strpos($lstrTextProcess,"RUT REP. LEGAL"))); //Nos traemos hasta el final del nombre de la empresa que sería el comienzo del representante.
			$larrData['RUT'] = trim(substr($lstrTextProcess, 0, strpos($lstrTextProcess," ")));
			$larrData['NOMBRE'] = trim(substr($lstrTextProcess, strpos($lstrTextProcess," ")));
			$larrData['PERIODO'] = "";

			//Extraemos el detalle de los desvinculados y vigentes
			$lstrTextProcess = strrpos($lstrText,"DESVINCULADOS EN EL PERÍODO TOTAL TRABAJADORES VIGENTES"); //Usamos como etiqueta clave el encabezado de la linea
			if ($lstrTextProcess){ //Si el encabezado de la linea existe, entonces procedemos a revisar
				$lstrTextProcess = trim(substr($lstrText, $lstrTextProcess+56));
				$lstrTextProcess = trim(substr($lstrTextProcess, 0, strpos($lstrTextProcess,"2.2")));
				$larrDataDesvinculados = explode(" ",$lstrTextProcess);
				$larrData['DESVINCULADOS'] = $larrDataDesvinculados[0];
				$larrData['VIGENTES'] = $larrDataDesvinculados[1];
			}

			//Extraemos el detalle de si está o no pagada y el total haberes
			$lstrTextProcess = strrpos($lstrText,"DETALLE DE REMUNERACIONES MES AÑO N° TRABAJADORES CON PAGO MONTO PAGADO ($) N° TRABAJADORES SIN PAGO"); //Usamos como etiqueta clave el encabezado de la linea
			if ($lstrTextProcess){ //Si el encabezado de la linea existe, entonces procedemos a revisar
				$lstrTextProcess = trim(substr($lstrText, $lstrTextProcess+103));
				$lstrTextProcess = trim(substr($lstrTextProcess, 0, strpos($lstrTextProcess,"2.4.")));
				$larrDataHaberes = explode(" ",$lstrTextProcess);
				$larrData['MONTH'] = $larrDataHaberes[0];
				$larrData['YEAR'] = $larrDataHaberes[1];
				$larrData['PAGADOS'] = $larrDataHaberes[2];
				$larrData['MONTOPAGADO'] = $larrDataHaberes[3];
				$larrData['SINPAGOS'] = $larrDataHaberes[4];
			}

	        //Extraemos el detalle de los empleados
			$lstrTextProcess = strrpos($lstrText,"MES AÑO RUT NOMBRE TRABAJADOR"); //Usamos como etiqueta clave el encabezado de la linea
			if ($lstrTextProcess){ //Si el encabezado de la linea existe, entonces procedemos a revisar
			  $lstrTextProcess = trim(substr($lstrText, $lstrTextProcess+30)); //Tomamos la información que se encuentra luego del encabezado.
			  $lintCountEmployee = 0; //Inicializamos variables que vamos a utilizar
			  $larrDataTemp = array(); //Inicializamos variables que vamos a utilizar
			  $lstrTextProcess = self::LimpiaGuiones( $lstrTextProcess );
			  $larrRUT = explode('-',$lstrTextProcess); //Tomamos un caracter que sea constante en la estructura, en este caso el guion, de tal manera sabemos que cada elemento del arreglo comienza por el ultimo digito del rut del elmento anterior. (a excepción del primer elemento)
			  $lintPre = 3; //Como estámos picando por el guion, sabemos que los ultimos 3 elementos de cada elemento del arreglo corresponden al mes, año y rut del empleado, sin su verificador (a excepción del ultimo)
			  foreach ($larrRUT as $key => $value) { //Comenzamos a recorrer el arreglo
			  	if (strrpos($value,"TOTAL DE TRABAJADORES:")){ //Usamos como etiqueta clave el total de los trabajadores, ya que marcará el final de la información de los empleados.
			  	  $lintCountEmployee = trim(substr($value,strrpos($value,"TOTAL DE TRABAJADORES:")+22));
			      $value = trim(substr($value,0,strrpos($value,"TOTAL DE TRABAJADORES:")));
			      $lintPre = 0;
			  	}
			  	if ($key==0){
			  	  $larrDataTemp = explode(" ",$value);
			  	  if (count($larrDataTemp) == 3){
			  	  	$larrDataTemp = array("month" =>$larrDataTemp[0],
			  	  					      "year" =>$larrDataTemp[1],
			  	  					      "rut" => $larrDataTemp[2],
			  	  					      "name"=> ""
			  	  		                  );
			  	  }
			  	}else{

			  	  $larrDataTemp = explode(" ",$value);
			  	  $lintCount = count($larrDataTemp);
			  	  if ($lintCount){
			  	  	$larrData['employeedetail'][$key-1]['rut'] .= '-'.$larrDataTemp[0];
			  	    $lstrName = "";
			  	    for ($i=1; $i < $lintCount-$lintPre; $i++) {
			  	    	$lstrName .= $larrDataTemp[$i]." ";
			  	    }
			  	    $larrData['employeedetail'][$key-1]['name'] = trim($lstrName);

			  	  	$larrDataTemp = array("month" =>$larrDataTemp[$lintCount-3],
			  	  					      "year" =>$larrDataTemp[$lintCount-2],
			  	  					      "rut" => $larrDataTemp[$lintCount-1],
			  	  					      "name"=> ""
			  	  		                  );
			  	  }
			  	}
			  	if ($lintPre){
			  	  $larrData['employeedetail'][] = $larrDataTemp;
			  	  $larrData['PERIODO'] = $larrDataTemp['year']."-".$larrDataTemp['month']."-01";
			  	}
			  }

			  if (count($larrData['employeedetail']) and $lintCountEmployee){
				if (count($larrData['employeedetail']) != $lintCountEmployee){
				  $larrDataResult['code'] = "2";
				  $larrDataResult['message'] = "La cantidad de empleados no coincide con el total";
				  $larrDataResult['result'] = $larrData;
				}
			  }
			}else{ //Si el encabezado de la linea NO existe, entonces enviamos un mensaje de no encontrarse detalle de los empleados
				$larrDataResult['code'] = "3";
				$larrDataResult['message'] = "Formato del documento proporcionado no corresponde al solicitado, debe cargar F30-1 extraído directamente desde la página de la Dirección del Trabajo";
				$larrDataResult['result'] = "";
			}

		}else{
			$larrDataResult['code'] = "3";
			$larrDataResult['message'] = "Formato del documento proporcionado no corresponde al solicitado, debe cargar F30-1 extraído directamente desde la página de la Dirección del Trabajo";
			$larrDataResult['result'] = "";
		}

		if (!isset($larrDataResult['message'])){
			$larrDataResult['code'] = "1";
			$larrDataResult['status'] = "success";
			$larrDataResult['message'] = "Archivo procesado satisfactoriamente";
			$larrDataResult['result'] = $larrData;
		}

		return $larrDataResult;
	}

	static public function parserFthirtySave($lobjDocument,$lobjDocumento){

		$lintIdUser = \Session::get('uid');
		$larrResult = array();

		//Verificamos que el documento haya sido procesado anteriormente
		$lobjF301 = DB::table('tbl_f30_1')->where("IdDocumento","=",$lobjDocumento['IdDocumento'])->first();

		if ($lobjF301){ //Ya fue procesado, procedemos a reprorcesar

		}


		$lobjContratistas = \DB::select('select * from tbl_contratistas where UPPER(RUT) = UPPER(\''.$lobjDocument['RUT'].'\') ');

		if ($lobjContratistas) {

			if ($lobjDocumento['Entidad']==6){

					$lobjContratista = \DB::table('tbl_contratistas')
			                        ->join('tbl_contrato','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
			                        ->select('tbl_contratistas.Rut', 'tbl_contratistas.IdContratista')
			                        ->where('tbl_contrato.contrato_id','=',$lobjDocumento['contrato_id'])
			                        ->first();
			}else if ($lobjDocumento['Entidad']==2){
				$lobjContratista = \DB::table('tbl_contratistas')
			                        ->select('tbl_contratistas.Rut', 'tbl_contratistas.IdContratista')
			                        ->join('tbl_contrato','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
			                        ->where('tbl_contrato.contrato_id','=',$lobjDocumento['IdEntidad'])
			                        ->first();
			}else{
					$lobjContratista = \DB::table('tbl_contratistas')
			                        ->select('tbl_contratistas.Rut', 'tbl_contratistas.IdContratista')
			                        ->where('tbl_contratistas.IdContratista','=',$lobjDocumento['IdEntidad'])
			                        ->first();
			}

			if (strtoupper($lobjContratista->Rut) == strtoupper($lobjDocument['RUT'])) {

				$lintIdContratista = $lobjContratistas[0]->IdContratista;
				$lstrRUT = $lobjDocument['RUT'];
				$lstrNombre = $lobjDocument['NOMBRE'];
				$lstrTrabajadoresVigentes = $lobjDocument['VIGENTES'];
				$lstrTrabajadoresDesvinculados = $lobjDocument['DESVINCULADOS'];
				$lstrTrabajadoresPagados = $lobjDocument['PAGADOS'];
				$lstrTrabajadoresNoPagados = $lobjDocument['SINPAGOS'];
				$lstrTotalCotizaciones = $lobjDocument['MONTOPAGADO'];
				$lstrPeriodo = $lobjDocument['YEAR']."-".$lobjDocument['MONTH'].'-1';

				$lintIdF30 = DB::table('tbl_f30_1')-> insertGetId(array(
																'IdDocumento' => $lobjDocumento['IdDocumento'],
																'Periodo' => $lstrPeriodo,
														        'IdContratista' => $lintIdContratista,
														        'RUT' => $lstrRUT,
														        'RazonSocial' => $lstrNombre,
														        'TrabajadoresVigentes' => $lstrTrabajadoresVigentes,
														        'TrabajadoresDesvinculados' => $lstrTrabajadoresDesvinculados,
														        'TrabajadoresPagados' => $lstrTrabajadoresPagados,
														        'TrabajadoresNoPagados' => $lstrTrabajadoresNoPagados,
														        'TotalCotizaciones' => $lstrTotalCotizaciones,
														        'createdOn' => date('Y-m-d H:i:s'),
														        'entry_by' => $lintIdUser,
														        'contrato_id' => $lobjDocumento['contrato_id']
														));

				foreach ($lobjDocument['employeedetail'] as $rows) {

					$dataempleado = array();

					//Buscamos cada uno de los empleados de la contratista
					$lobjPersonas = \DB::table('tbl_personas')
					->select("tbl_personas.IdPersona", "tbl_contratos_personas.contrato_id", "tbl_contratos_personas.IdContratista")
					->leftjoin("tbl_contratos_personas","tbl_contratos_personas.IdPersona","=","tbl_personas.Idpersona")
					->where("tbl_personas.rut","=",$rows['rut'])
					->first();

					$ldatPeriodo = $rows['year']."-".$rows['month'].'-1';
					$lstrRUT = $rows['rut'];
					$lstrNombre = $rows['name'];

					if ($lobjPersonas){
						$dataempleado['IdPersona'] = $lobjPersonas->IdPersona;
						$dataempleado['contrato_id'] = $lobjPersonas->contrato_id;
						$dataempleado['IdContratista'] = $lobjPersonas->IdContratista;
            /*
						\DB::table("tbl_documentos")
						     ->where("IdTipoDocumento",2)
						     ->where("IdEntidad",$dataempleado['IdPersona'])
						     ->update(array("idestatus"=>"5",
						     				"updatedOn" => $lobjDocumento['updatedOn'],
						     				"FechaVencimiento" => $lobjDocumento['FechaVencimiento'],
						     				"Documento" => $lobjDocumento['Documento'],
											"DocumentoURL"=> $lobjDocumento['DocumentoURL'],
											"DocumentoTexto" => $lobjDocumento['DocumentoTexto'],
						     				"FechaEmision" => $ldatPeriodo));
                        */
					}

					$dataempleado['IdF301'] = $lintIdF30;
					$dataempleado['Periodo'] = $ldatPeriodo;
					$dataempleado['RUT'] = $lstrRUT;
					$dataempleado['Nombre'] = $lstrNombre;

					/*if ($dataempleado['RUT']=='17286847-9'){
						var_dump($dataempleado);
						var_dump($lobjDocumento);
						break;
					}
					*/
					//Definimos la regla del negocio para consulta
					$lintIdEstatus = 0;
					if ( isset($dataempleado['contrato_id']) ) {

						if ( $dataempleado['contrato_id'] ){
							if ($dataempleado['contrato_id']==$lobjDocumento['contrato_id']){
								$lintIdEstatus = 1;
							}else{
								if ($dataempleado['IdContratista']==$lobjContratista->IdContratista){
									$lintIdEstatus = 2;
								}else{
									$lintIdEstatus = 3;
								}
							}
						}else{
							$lintIdEstatus = 4;
						}

					}

					$dataempleado['IdEstatus'] = $lintIdEstatus;

					$lintIdF30Detalle = DB::table('tbl_f30_1_empleados')-> insertGetId($dataempleado);

				}

				$larrResult["code"] = "1";
				$larrResult['status'] = "success";
				$larrResult["message"] = "Archivo procesado satisfactoriamente";
				$larrResult["result"] = "";


			}else{
			    $larrResult["code"] = "6";
			    $larrResult["message"] = "El archivo F30-1 que intenta cargar no pertenece al contratista de la solicitud";
			    $larrResult["result"] = "";
		    }
		}else{
			$larrResult["code"] = "4";
			$larrResult["message"] = "El archivo F30-1 que intenta cargar no pertenece a ningún contratista registrado";
			$larrResult["result"] = "";
		}
		return $larrResult;

    }

    static public function ProccessInduction($lobjDocument){

    	$larrResult = array();

    	include '../app/Library/PHPExcel/IOFactory.php';
    	$lstrNombreFull = "uploads/documents/".$lobjDocument['DocumentoURL'];
		try {
		    $objPHPExcel = \PHPExcel_IOFactory::load($lstrNombreFull);
		} catch(Exception $e) {
		    die('Error loading file "'.pathinfo($lstrNombreFull,PATHINFO_BASENAME).'": '.$e->getMessage());
		}

		$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		$arrayCount = count($allDataInSheet);

		for($i=2;$i<=$arrayCount;$i++){
			$rut = trim($allDataInSheet[$i]["A"]);
			$fecha = trim($allDataInSheet[$i]["B"]);
			if ($fecha){
		        try {
		            $fecha = \DateTime::createFromFormat("m-d-y", $fecha);
		            if ($fecha){
		              $fecha =  $fecha->format('Y-m-d');
		            }
				} catch (Exception $e) {
					$fecha = "";
				}
			}

			$lobjPersonas = \DB::table('tbl_personas')->where('RUT',$rut)->get();

			if ($lobjPersonas){
				$dataempleado['IdPersona'] = $lobjPersonas[0]->IdPersona;
				\DB::table("tbl_documentos")
				     ->where("IdTipoDocumento",6)
				     ->where("IdEntidad",$lobjPersonas[0]->IdPersona)
				     ->update(array("idestatus"=>"5",
				     				"FechaEmision" => $fecha ));
			}

		}

		$larrResult["code"] = "1";
		$larrResult["message"] = "Archivo procesado satisfactoriamente";
		$larrResult["result"] = "";

		return $larrResult;

    }

    static public function ProccessPrework($lobjDocument,$pstrNameFile){

    	$larrResult = array();

    	include '../app/Library/PHPExcel/IOFactory.php';
    	$lstrNombreFull = "uploads/documents/".$lobjDocument['DocumentoURL'];
		try {
		    $objPHPExcel = \PHPExcel_IOFactory::load($lstrNombreFull);
		} catch(Exception $e) {
		    die('Error loading file "'.pathinfo($lstrNombreFull,PATHINFO_BASENAME).'": '.$e->getMessage());
		}

		$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		$arrayCount = count($allDataInSheet);

		for($i=2;$i<=$arrayCount;$i++){
			$rut = trim($allDataInSheet[$i]["A"]);

			$lobjPersonas = \DB::table('tbl_personas')->where('RUT',$rut)->get();

			if ($lobjPersonas){
				$dataempleado['IdPersona'] = $lobjPersonas[0]->IdPersona;
				\DB::table("tbl_documentos")
				     ->where("IdTipoDocumento",8)
				     ->where("IdEntidad",$lobjPersonas[0]->IdPersona)
				     ->update(array("idestatus"=>"5",
				     	 			"DocumentoURL" => $pstrNameFile));
			}

		}

		$larrResult["code"] = "1";
		$larrResult["message"] = "Archivo procesado satisfactoriamente";
		$larrResult["result"] = "";

		return $larrResult;

    }

    static public function Estadodepago($lobjDocument){
  		$larrResult = array();
  		$ldatFechaEmision = date('Y-m-d',strtotime($lobjDocument['FechaEmision']));
    	include '../app/Library/PHPExcel/IOFactory.php';
    	$lstrNombreFull = "uploads/documents/".$lobjDocument['DocumentoURL'];
		try {
		    $objPHPExcel = \PHPExcel_IOFactory::load($lstrNombreFull);
		} catch(Exception $e) {
		    die('Error loading file "'.pathinfo($lstrNombreFull,PATHINFO_BASENAME).'": '.$e->getMessage());
		}

		$larrDataFile = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		$lintCountDataFile = count($larrDataFile);

		//aqui voy con el codigo
        $lintRowInit = 4;
        $larrFormato = array("ITEM"=>"A",
                             "DESCRIPCION"=>"B",
                             "UNIDAD"=>"C",
                             "CANTIDAD"=>"D",
                             "MONTO"=>"E"
                             );

        $ldatFechaPlan = isset($larrDataFile[1][$larrFormato['CANTIDAD']])?$larrDataFile[1][$larrFormato['CANTIDAD']]:"";
        if ($ldatFechaPlan){
          //  var_dump($ldatFechaPlan);
            if (\PHPExcel_Shared_Date::isDateTime($objPHPExcel->getActiveSheet()->getCell($larrFormato['CANTIDAD']."1"))) {
                   // echo "Fecha!!!!"."<br><br><br>";

              $lstrFormat = self::ExcelFormatToPHP($objPHPExcel->getActiveSheet()->getStyle($larrFormato['CANTIDAD']."1")->getNumberFormat()->getFormatCode());
              $fecha = \DateTime::createFromFormat($lstrFormat, $ldatFechaPlan);
              $ldatFechaPlan =  $fecha->format('Y-m-d');
            }else{

               /* return response()->json(array(
                  'status'=>'error',
                  'message'=> \Lang::get('core.note_success'),
                  'result'=>$newfilename)
                );*/

                $larrResult["code"] = "2";
                $larrResult["message"] = "La fecha no cumple con el formato";
                $larrResult["result"] = "";
                return $larrResult;
            }
        }

        if ($ldatFechaPlan!=$ldatFechaEmision){
        	$larrResult["code"] = "2";
		    $larrResult["message"] = "La fecha del estado de pago no concuerda con la solicitud";
		    $larrResult["result"] = "";
		    return $larrResult;
        }

        for($i=$lintRowInit;$i<=$lintCountDataFile;$i++){

            $lstrResultado = "";
            $lstrItem = trim($larrDataFile[$i][$larrFormato['ITEM']]);
            $lstrDescripcion = str_replace("⅛","",str_replace("¼","",str_replace("⅜","",str_replace("½","",str_replace("¾","",str_replace("Ø","",str_replace('"','\"',trim($larrDataFile[$i][$larrFormato['DESCRIPCION']]))))))));
            $lintCantidad = isset($larrFormato['CANTIDAD'])?trim($larrDataFile[$i][$larrFormato['CANTIDAD']]):0;
			if ($lstrItem){
				$lstrQuery = "SELECT a.Monto
							  from tbl_contratos_items as a
							  left join (select tbl_contratos_items.*, '.' as separador from tbl_contratos_items) as b on a.Idparent = b.IdContratoItem
							  left join (select tbl_contratos_items.*, '.' as separador from tbl_contratos_items) as c on b.Idparent = c.IdContratoItem
							  where a.contrato_id = ".$lobjDocument['contrato_id']."
							  and concat(ifnull(c.identificacion,''),ifnull(c.separador,''), ifnull(b.identificacion,''), ifnull(b.separador,''), a.Identificacion) = '".$lstrItem."'";
	            $lobjMonto = \DB::select($lstrQuery);

	            if (count($lobjMonto)>0)
	                $lintMonto = $lobjMonto[0]->Monto;

	           // $lintMonto = isset($larrFormato['MONTO'])?trim($larrDataFile[$i][$larrFormato['MONTO']]):0;

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
	            $lintEstadoPago = 1;

	            $larrEstadoPago[$lintEstadoPago] = array("fecha"=>$ldatFechaPlan,"cantidad"=>$larrDataFile[$i][$larrFormato['CANTIDAD']],"monto"=>$lintMonto);
	            $lintEstadoPago += 1;

	            //Definimos los niveles, solo permitimos 5 niveles
	            if ($lintCount==0){
	              self::$larrItems[$larrItem[0]] = array("title"=>$lstrDescripcion,"children"=>array(),"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
	            }elseif ($lintCount==1){
	              self::$larrItems[$larrItem[0]]["children"][$larrItem[1]] = array("title"=>$lstrDescripcion,"children"=>array(),"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
	            }elseif ($lintCount==2){
	              self::$larrItems[$larrItem[0]]["children"][$larrItem[1]]["children"][$larrItem[2]] = array("title"=>$lstrDescripcion,"children"=>array(),"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
	            }elseif ($lintCount==3){
	              self::$larrItems[$larrItem[0]]["children"][$larrItem[1]]["children"][$larrItem[2]]["children"][$larrItem[3]] = array("title"=>$lstrDescripcion,"children"=>array(),"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
	            }elseif ($lintCount==4){
	              self::$larrItems[$larrItem[0]]["children"][$larrItem[1]]["children"][$larrItem[2]]["children"][$larrItem[3]]["children"][$larrItem[4]] = array("title"=>$lstrDescripcion,"children"=>array(),"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
	            }

            }

        }

          self::SaveEstadosdepago($lobjDocument['contrato_id'],self::$larrItems);

            $larrResult["code"] = "1";
            $larrResult["message"] = "Archivo procesado satisfactoriamente";
            $larrResult["result"] = "";

		return $larrResult;
  }

  static public function ExcelFormatToPHP($pstrFecha){
      $lstrResultado = "";
      switch ($pstrFecha) {
        case "mm-dd-yy":
          $lstrResultado = "m-d-y";
          break;
      }
      return $lstrResultado;
  }

  static function SaveEstadosdepago($pintIdContrato, $parrItemizado){

      $lintIdAnterior = "";
      foreach ($parrItemizado as $lstrIdPosiciones => $larrPosiciones) {

        //guardo las posiciones
        $lobjPosiciones = \DB::table('tbl_contratos_items')
                             ->where('Descripcion', '=', $larrPosiciones['title'])
                             ->where('contrato_id','=',$pintIdContrato)
                             ->get();

        if ($lobjPosiciones){
            $lintIdPosicion = $lobjPosiciones[0]->IdContratoItem;
        }


        foreach ($larrPosiciones['children'] as $lintIdItem => $larrItems) {

            //guardo las posiciones
            $lobjItems = \DB::table('tbl_contratos_items')
                                 ->where('Descripcion', '=', $larrItems['title'])
                                 ->where('contrato_id','=',$pintIdContrato)
                                 ->where('IdParent','=',$lintIdPosicion)
                                 ->get();
             if ($lobjItems){
            $lintIdItems = $lobjItems[0]->IdContratoItem;
            }


            //luego de guardar las posiciones vamos a la tabla de plan para verificar si se debe guardar
            if ($lintIdItems){

                foreach ($larrItems['plan'] as $lstrPlan => $larrPlan) {
                  $lobjItemsPlan = \DB::table('tbl_contratos_items_r')
                                     ->where('IdItem', '=', $lintIdItems)
                                     ->where('Mes','=',$larrPlan['fecha'])
                                     ->get();

                  if (!$lobjItemsPlan){
                      $lintIdPlan = \DB::table('tbl_contratos_items_r')->insertGetId(array("IdItem"=> $lintIdItems,
                                                                                      "Mes"=> $larrPlan['fecha'],
                                                                                      "contrato_id"=>$pintIdContrato,
                                                                                      "Cantidad"=> str_replace(",",".",$larrPlan['cantidad']),
                                                                                      "Monto"=> $larrPlan['monto'],
                                                                                      "SubTotal"=> $larrPlan['cantidad']*$larrPlan['monto'] ));
                  }else{
                      $lintIdPlan = \DB::table('tbl_contratos_items_r')->where("IdItemReal","=",$lobjItemsPlan[0]->IdItemReal)->update(array("Cantidad"=> str_replace(",",".",$larrPlan['cantidad']),
                                                                               "Monto"=> $larrPlan['monto'],
                                                                               "SubTotal"=> $larrPlan['cantidad']*$larrPlan['monto'] ));
                      $lintIdPlan = $lobjItemsPlan[0]->IdItemReal;
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

                if ($lobjItems2){
                  $lintIdItems2 = $lobjItems2[0]->IdContratoItem;
                }

                //luego de guardar las posiciones vamos a la tabla de plan para verificar si se debe guardar
                if ($lintIdItems2){

                    foreach ($larrItems2['plan'] as $lstrPlan => $larrPlan) {
                      $lobjItemsPlan = \DB::table('tbl_contratos_items_r')
                                         ->where('IdItem', '=', $lintIdItems2)
                                         ->where('Mes','=',$larrPlan['fecha'])
                                         ->get();
                      if (!$lobjItemsPlan){
                          $lintIdPlan = \DB::table('tbl_contratos_items_r')->insertGetId(array("IdItem"=> $lintIdItems2,
                                                                                          "Mes"=> $larrPlan['fecha'],
                                                                                          "contrato_id"=>$pintIdContrato,
                                                                                          "Cantidad"=> str_replace(",",".",$larrPlan['cantidad']),
                                                                                          "Monto"=> $larrPlan['monto'],
                                                                                          "SubTotal"=> $larrPlan['cantidad']*$larrPlan['monto'] ));
                      }else{
                          $lintIdPlan = \DB::table('tbl_contratos_items_r')->where("IdItemReal","=",$lobjItemsPlan[0]->IdItemReal)->update(array("Cantidad"=> str_replace(",",".",$larrPlan['cantidad']),
                                                                                   "Monto"=> $larrPlan['monto'],
                                                                                   "SubTotal"=> $larrPlan['cantidad']*$larrPlan['monto'] ));
                          $lintIdPlan = $lobjItemsPlan[0]->IdItemReal;
                      }
                    }
                }

            }
        }

      }

    }

    static public function ProccessLibroRemun($lobjDocument){

      $larrResult = array();

      include '../app/Library/PHPExcel/IOFactory.php';
      $lstrNombreFull = "uploads/documents/".$lobjDocument['DocumentoURL'];
    try {
        $objPHPExcel = \PHPExcel_IOFactory::load($lstrNombreFull);
    } catch(Exception $e) {
        die('Error loading file "'.pathinfo($lstrNombreFull,PATHINFO_BASENAME).'": '.$e->getMessage());
    }

    $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
    $arrayCount = count($allDataInSheet);

    $roles = DB::select(DB::raw('SELECT tbl_roles.Descripción FROM tbl_roles;'));
    $listaRoles = array();
    $listaRechazo = array();
    foreach($roles as $rol){
      array_push($listaRoles, $rol->Descripción);
    }

    for($i=2;$i<=$arrayCount;$i++){
      if(!empty($allDataInSheet[$i]['A']) && !empty($allDataInSheet[$i]['B']) && !empty($allDataInSheet[$i]['C']) && !empty($allDataInSheet[$i]['D'])){
        //if(!in_array($allDataInSheet[$i]['E'], $listaRoles)){
          //array_push($listaRechazo, $allDataInSheet[$i]['E']);
        //}
        $query = DB::table('tbl_remuneraciones_mensual')->insertGetId(['contrato_id' => $lobjDocument['contrato_id'], 'periodo' => date("Y-m-d", strtotime($lobjDocument['createdOn'])), 'cargo' => $allDataInSheet[$i]['E'], 'cantidad' => 1, 'prom_remuneracion' => $allDataInSheet[$i]['F'] ]);
      }
    }
    if(empty($listaRechazo)){
      //for($i=2;$i<=$arrayCount;$i++){
        //if(!empty($allDataInSheet[$i]['A']) && !empty($allDataInSheet[$i]['B']) && !empty($allDataInSheet[$i]['C']) && !empty($allDataInSheet[$i]['D'])){
          //$query = DB::table('tbl_remuneraciones_mensual')->insertGetId(['contrato_id' => $lobjDocument['contrato_id'], 'periodo' => date("Y-m-d", strtotime($lobjDocument['createdOn'])), 'cargo' => $allDataInSheet[$i]['E'], 'cantidad' => 1, 'prom_remuneracion' => $allDataInSheet[$i]['F'] ]);
        //}
      //}
      $larrResult["code"] = "1";
      $larrResult["message"] = "Archivo procesado satisfactoriamente";
      $larrResult["result"] = "";
      $larrResult["rechazado"] = "";
      return $larrResult;
    } else{
      $larrResult["code"] = "2";
      $larrResult["message"] = "Error: Cargos no encontrados";
      $larrResult["result"] = "";
      $larrResult["rechazado"] = $listaRechazo;
      return $larrResult;
    }
    }

  	static public function CargaDocumentoEstatus($pintIdEstatus){

  	  if ($pintIdEstatus==1){
		$lstrResult = "Cargado";
  	  }else if ($pintIdEstatus==2){
  	  	$lstrResult = "Procesado";
  	  }else if ($pintIdEstatus==3){
  	  	$lstrResult = "Estructura no correcta";
  	  }else if ($pintIdEstatus==4){
  	  	$lstrResult = "Error en datos";
  	  }else{
  	  	$lstrResult = "No especificado";
  	  }
  	  return $lstrResult;
  	}

    static public function AssocGUser($pintIdUser) {
        $lobjUserGroup = DB::table("tb_users")
                             ->select("tb_assoccgroup.group_id")
                             ->join("tb_assoccgroup","tb_users.group_id", "=", "tb_assoccgroup.group_id")
                             ->where("id","=",$pintIdUser)
                             ->get();
        // Almaceno el resulrado de la consulta en un vector
        $larrUserGroup = array();
        foreach ($lobjUserGroup as $value){
            $larrUserGroup[] = $value->group_id;
        }
        if (count($larrUserGroup)) {
            return $larrUserGroup[0];
        }else{
            return 0;
        }
    }

    static public function LevelUser($pintIdUser) {
        $lobjUserLevel = DB::table("tb_users")
                             ->select("tb_groups.level","tb_groups.group_id")
                             ->join("tb_groups","tb_users.group_id", "=", "tb_groups.group_id")
                             ->where("id","=",$pintIdUser)
                             ->get();
        // Almaceno el resulrado de la consulta en un vector
        $larrUserLevel = array();
        foreach ($lobjUserLevel as $value){
            $larrUserLevel[] = $value->level;
        }
        if (count($larrUserLevel)) {
            return $larrUserLevel[0];
        }else{
            return 0;
        }
    }

    static public function GroupUser($pintIdUser) {
        $lobjUserLevel = DB::table("tb_users")
                             ->select("tb_groups.level","tb_groups.group_id")
                             ->join("tb_groups","tb_users.group_id", "=", "tb_groups.group_id")
                             ->where("id","=",$pintIdUser)
                             ->get();
        // Almaceno el resulrado de la consulta en un vector
        $larrUserLevel = array();
        foreach ($lobjUserLevel as $value){
            $larrUserLevel[] = $value->group_id;
        }
        if (count($larrUserLevel)) {
            return $larrUserLevel[0];
        }else{
            return 0;
        }
    }

    static public function RelationshiplUser($pintIdContrato, $pintIdUser) {

    	$lobjContratos = \DB::table('tbl_contrato')
    	->select('tbl_contrato.entry_by_access', 'tbl_contrato.IdContratista')
    	->where('tbl_contrato.contrato_id','=',$pintIdContrato)
    	->first();

    	if ($lobjContratos->entry_by_access == $pintIdUser){
    		return array("relationship" => "01", 'IdContratista' => $lobjContratos->IdContratista);
    	}else{

    		$lobjSubContratistas = \DB::table('tbl_contratos_subcontratistas')
    		->select('tbl_contratistas.entry_by_access', 'tbl_contratistas.IdContratista')
    		->join('tbl_contratistas','tbl_contratos_subcontratistas.IdSubContratista','=','tbl_contratistas.IdContratista')
    		->where('tbl_contratos_subcontratistas.contrato_id','=',$pintIdContrato)
    		->where('tbl_contratistas.entry_by_access','=',$pintIdUser)
    		->first();

    		if ($lobjSubContratistas) {
    			return array("relationship" => "02", 'IdContratista' => $lobjSubContratistas->IdContratista);
    		}else{
    			return array("relationship" => "03", 'IdContratista' => '');
    		}
    	}

    }

  	static public function Status($pintIdEstatus){

  	  if ($pintIdEstatus==0){
  	  	$lstrResult = "Inactivo";
  	  }elseif ($pintIdEstatus==1){
		$lstrResult = "Activo";
  	  }else if ($pintIdEstatus==2){
  	  	$lstrResult = "Suspendido";
  	  }else if ($pintIdEstatus==3){
  	  	$lstrResult = "Precontratista";
  	  }else{
  	  	$lstrResult = "No especificado";
  	  }
  	  return $lstrResult;
  	}
    static public function StatusAnot($pintIdEstatus){

       if ($pintIdEstatus==1){
        $lstrResult = "Activo";
      }else if ($pintIdEstatus==2){
        $lstrResult = "Inactivo";
      }else{
        $lstrResult = "No especificado";
      }
      return $lstrResult;

    }
  	static public function FormatStatus($pintIdEstatus){
  		switch ($pintIdEstatus) {
	        case 1:
	           return '<span > Activo </span>';
	            break;
	        case 2:
	            return '<span > Cerrado </span>';
	            break;
	    }
  	}
  	static public function ValorUno($pintIdSexo=""){

  	  if ($pintIdSexo==1){
		$lstrResult = "Hombre";
  	  }else if ($pintIdSexo==2){
  	  	$lstrResult = "Mujer";
  	  }else{
  	  	$lstrResult = "No especificado";
  	  }
  	  return $lstrResult;
  	}
  	static public function RecurrenciaDocumentos($pintVigencia=0){
	   if ($pintVigencia==1){
	  	$lstrResult = "Solicitada";
	  }else if ($pintVigencia==2){
	  	$lstrResult = "Programada";
	  }else if ($pintVigencia==3){
	  	$lstrResult = "Una Sola vez";
  	}else{
  	  	$lstrResult = "No especificado";
  	  }
  	  return $lstrResult;
  	}
  	static public function ValorDos($pintIdEstadoCivil=""){

  	  if ($pintIdEstadoCivil==1){
		$lstrResult = "Soltero";
  	  }else if ($pintIdEstadoCivil==2){
  	  	$lstrResult = "Casado";
  	  }else if ($pintIdEstadoCivil==3){
  	  	$lstrResult = "Divorciado";
  	  }else if ($pintIdEstadoCivil==4){
  	  	$lstrResult = "Viudo";
  	  }else{
  	  	$lstrResult = "No especificado";
  	  }
  	  return $lstrResult;
  	}
  	static public function AccessStatus($pintIdEstatus=""){

  	  if ($pintIdEstatus==1){
		$lstrResult = "Activo";
  	  }else if ($pintIdEstatus==2){
  	  	$lstrResult = "Suspendido";
  	  }else if ($pintIdEstatus==3){
  	  	$lstrResult = "No autorizado";
  	  }else if ($pintIdEstatus==4){
  	  	$lstrResult = "Temporal";
  	  }else if ($pintIdEstatus==5){
  	  	$lstrResult = "No permitido";
  	  }else{
  	  	$lstrResult = "No especificado";
  	  }
  	  return $lstrResult;
  	}
  	static public function AccessType($pintIdTipo=""){

  	  if ($pintIdTipo==1){
		$lstrResult = "Trabajador";
  	  }else if ($pintIdTipo==2){
  	  	$lstrResult = "Visitante";
  	  }else if ($pintIdTipo==3){
  	  	$lstrResult = "Provisional";
  	  }else{
  	  	$lstrResult = "No especificado";
  	  }
  	  return $lstrResult;
  	}
	static public function tipoTamano($pintIdTipo=""){

  	  if ($pintIdTipo==0){
		$lstrResult = "PYME";
  	  }else if ($pintIdTipo==1){
  	  	$lstrResult = "Pequeña";
  	  }else if ($pintIdTipo==2){
  	  	$lstrResult = "Mediana";
	  }else if ($pintIdTipo==3){
  	  	$lstrResult = "Grande";
  	  }else{
  	  	$lstrResult = "No especificado";
  	  }
  	  return $lstrResult;
  	}
  	static public function DocumentsStatus($pintIdEstatus=""){

  	  if ($pintIdEstatus==1){
		$lstrResult = "Por cargar";
  	  }else if ($pintIdEstatus==2){
  	  	$lstrResult = "Por aprobar";
  	  }else if ($pintIdEstatus==3){
  	  	$lstrResult = "No aprobado";
  	  }else if ($pintIdEstatus==4){
  	  	$lstrResult = "Temporal";
  	  }else if ($pintIdEstatus==5){
  	  	$lstrResult = "Aprobado";
	  }else if ($pintIdEstatus==6){
  	  	$lstrResult = "Atrasado";
  	  }else if ($pintIdEstatus==7){
  	  	$lstrResult = "Por asociar";
  	  }else if ($pintIdEstatus==8){
  	  	$lstrResult = "Vencido";
  	  }else{
  	  	$lstrResult = "No especificado";
  	  }
  	  return $lstrResult;
  	}
  	static public function DocumentsView($pstrName1) {

	if(is_array($pstrName1)){
			$pstrName = implode(",",$pstrName1);
		}
		else
			$pstrName = $pstrName1;

  		if (strpos($pstrName,',')>=0){

		  $larrArg = explode(",",$pstrName);
		  if ($larrArg[0]!=''){
			$status = DB::table("tbl_documentos")
                             ->select("IdEstatus","estado_carga","tipo")
                             ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento","=","tbl_tipos_documentos.IdTipoDocumento")
                             ->where("IdDocumento","=",$larrArg[1])
                             ->get();

			$st = array();
			$st2 = array();
			foreach ($status as $value){
				$st[] = $value->IdEstatus;
				$st2[] = $value->estado_carga;
				$st3[] = $value->tipo;
        }

      return "<a onClick=\"ViewPDF('".$larrArg[0]."',".$larrArg[1].",".$st[0].",".$st2[0].",".$st3[0].");\" class=\"btn btn-xs btn-white tips\"><i class=\"\" ></i>Ver</a>";
		  }else{
      $tipo =  \DB::table("tbl_documentos")
                              ->where("IdDocumento","=",$larrArg[1])
                              ->get();
        //  if (($tipo[0]->IdTipoDocumento==76) && ($tipo[0]->IdEstatus>1))
          if ($tipo[0]->IdTipoDocumento==78 || $tipo[0]->IdTipoDocumento==84)
          return '<div class=" action dropup"><a href="'.\URL::to('encuestas/update/doc='.$larrArg[1]).'" onclick="ViewEncuesta(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class=""></i>Ver</a></div>';
          else
			return "<a onClick=\"ViewPDF('','" .$larrArg[1]."');\" class=\"btn btn-xs btn-white tips\"><i class=\"\" ></i>Ver</a>";
		  }
		}else{
		  if ($pstrName!=''){
			return "<a onClick=\"ViewPDF('".$pstrName."','');\" class=\"btn btn-xs btn-white tips\"><i class=\"\" ></i>Ver</a>";
		  }else{
			return "-";
		  }
		}


  	}
  	static public function DocumentsDetail($pintEntidad){
  		$larrArg = explode(",",$pintEntidad);
  		if (count($larrArg)>1){
  			if ($larrArg[1]==1){//Se trata de un contratista
  				$module = "contratistas";
  			}else if ($larrArg[1]==2){//Se trata de un contrato
  				$module = "contratos";
  			}else if ($larrArg[1]==3){//Se trata de una persona
				$module = "personas";
  			}else if ($larrArg[1]==6) {//Se trata de un centro
                $module = "centros";
            }else if ($larrArg[1]==9){//Se trata de un subcontratista
                    $module = "contratistas";
  			}else {
  				$module = "";
  			}
  			if ($module){
  			  $onclick = " onclick=\"SximoModal(this.href,'View Detail'); return false; \"" ;
			  $html = '<a href="'.URL::to($module.'/show/'.$larrArg[0]).'" '.$onclick.' class="btn btn-xs btn-white tips" title="'.Lang::get('core.btn_view').'"><i class="fa fa-search"></i></a>';
  			}else{
				$html = "";
			}
  		}
  		return $html;
  	}
  	static public function DocumentsDetailTwo($pintEntidad){
  		$larrArg = explode(",",$pintEntidad);
  		if (count($larrArg)>1){
  			if ($larrArg[1]==1){//Se trata de un contratista
  				$module = "contratistas";
  			}else if ($larrArg[1]==2){//Se trata de un contrato
  				$module = "contratos";
  			}else if ($larrArg[1]==3){//Se trata de una persona
				$module = "personas";
  			}else if ($larrArg[1]==6){//Se trata de un centro
  				$module = "centros";
  			}else if ($larrArg[1]==9){//Se trata de un subcontratista
                $module = "contratistas";
            }else {
  				$module = "";
  			}
  			if ($module){
	  			$onclick = " onclick=\"SximoModal(this.href,'View Detail'); return false; \"" ;
				$html = '<a href="'.URL::to($module.'/show/'.$larrArg[0]).'" '.$onclick.' class="btn btn-xs btn-white tips" title="'.Lang::get('core.btn_view').'">ver más</a>';
			}else{
				$html = "";
			}
  		}
  		return $html;
  	}

         static public function tipoAcceso($IdTipoAcceso)
            {
               switch ($IdTipoAcceso) {
                        case 1:
                           return '<span > Trabajador </span>';
                            break;
                        case 2:
                            return '<span > Visitante </span>';
                            break;
                        case 3:
                            return '<span > Provisional </span>';
                            break;
                    }
            }
        static public function statusAcceso($IdEstatus)
            {
                if ($IdEstatus==1){
                    return '<span class="label label-primary"> Con Acceso </span>';
                }
                else if ($IdEstatus==2){
                    return '<span class="label label-danger"> Sin Acceso </span>';
                }
                else{
                         return '<span class="label label-success"> Acceso Temporal </span>';
                }


            }

    static public function statusUsuario($IdEstatus)
    {
        if ($IdEstatus==1){
            return '<span class="label label-primary"> Activo </span>';
        }
        else if ($IdEstatus==0){
            return '<span class="label label-danger"> Inactivo </span>';
        }


    }

    static public function ChangeContract($pintIdPersona, $pintIdContratoAnterior, $pintIdContratoNuevo){

    	//Buscamos los documentos que se deben revisar.



    }

    static public function UnidadName($Idunidad) {
      if ($Idunidad>0){
        $status = DB::table("tbl_unidades")
                               ->select("Descripcion")
                               ->where("IdUnidad","=",$Idunidad)
                               ->get();
        $valor = $status[0]->Descripcion;
      }
      else
        $valor="";

      return $valor;

    }

    static public function TdocumentoName($Tdoc) {
        if ($Tdoc>0){
            $tipod = DB::table("tbl_tipos_documentos")
                ->select("Descripcion")
                ->where("IdTipoDocumento","=",$Tdoc)
                ->first();
            $valor = $tipod->Descripcion;
        }
        else
            $valor="";

        return $valor;

    }

    static public function MotiveType($pintIdMotiv=""){

        if ($pintIdMotiv==1){
            $lstrResult = "Rechazar";
        }else if ($pintIdMotiv==2){
            $lstrResult = "Anular";
        }else{
            $lstrResult = "No especificado";
        }
        return $lstrResult;
    }

    static public function ShortEstType($pintIdMotiv=""){

        if ($pintIdMotiv==1){
            $lstrResult = "SI";
        }else if ($pintIdMotiv==0){
            $lstrResult = "NO";
        }else{
            $lstrResult = "No especificado";
        }
        return $lstrResult;
    }

    static public function RegistrarlogDocs($IdDocumento,$IdAccion,$Observaciones=null){
        $documentos=explode(",",$IdDocumento);
        $lintIdUser = \Session::get('uid');
        foreach ($documentos as $documento)

        $lDocURL = \DB::table('tbl_documentos')->where('IdDocumento',$documento)->pluck('DocumentoURL');

        $lstrResultado = \DB::table("tbl_documentos_log")
            ->insert(array("IdDocumento"=>$documento,"IdAccion"=>$IdAccion,"DocumentoURL"=>$lDocURL,"observaciones"=>$Observaciones,"entry_by"=>$lintIdUser));
        return response()->json(array(
            'status'=>'success',
            'result'=>$lstrResultado,
            'message'=> \Lang::get('core.note_success')
        ));
	}
	static public function DiscapacidadTable($pintIdDisc){
		if($pintIdDisc == 1){
			return "SI";
		}else{
			return "NO";
		}
	}

}

?>
