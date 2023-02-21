<?php

use App\Models\TblPlanesYProgramasAmbito;
use App\Models\TblPlanesYProgramasAmbitosub;

use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\FontMetrics;

class MyReports {

	static $pintDayChange;
	static $pintCurrentDay;
	static $pintCurrentMonth;
	static $pintCurrentYear;
	static $pintCurrentDate;
	static $parrFilter = array("year"=>"", "month"=>"","ind"=>"", "reg" => "", "area" => "", "seg" => "");
	static $pintMonth;
	static $garrParameters = array("unid"=>"%","title"=>"","subtitle"=>"","ejex"=>"anual","format"=>"2");
	static $garrMetas = array();
	static $gintLevelUser;
	static $gintIdUser;
	static $garrColorsRange;
	static $garrColorsRangeDefault;

	static function setColorGeneral(){
		self::$garrColorsRange = array( array("Start"=>0,"End"=>75, "Color"=>"#e64427"),
    		                            array("Start"=>75,"End"=>100, "Color"=>"#3d9b35") );
	}

	static function getColorGeneral(){
	    return array( array("Start"=>0,"End"=>75, "Color"=>"#e64427"),
    		                            array("Start"=>75,"End"=>100, "Color"=>"#3d9b35") );
	}

	static function setInformacion($pstrIdTipo) {

		//self::$garrMetas = self::getMetas($pstrIdTipo);

        self::$parrFilter['ind'] = $pstrIdTipo;
        self::$garrParameters['ejex'] = "anual";
        self::$garrParameters['format'] = "2";

        self::setColorGeneral();

        if ($pstrIdTipo == "fin") {
        	self::$garrColorsRange = array( array("Start"=>-100,"End"=>-10, "Color"=>"#e64427"),
        					array("Start"=>-9.99,"End"=>-5, "Color"=>"#FFC000"),
                            array("Start"=>-4.99,"End"=>0, "Color"=>"#3d9b35"),
                            array("Start"=>0,"End"=>4.99, "Color"=>"#3d9b35"),
                            array("Start"=>5,"End"=>9.99, "Color"=>"#FFC000"),
                            array("Start"=>10,"End"=>100, "Color"=>"#e64427")
                             );
        }elseif ($pstrIdTipo == "kpi") {
        	self::$garrParameters['title'] = "KPIs";
        	self::$garrParameters['unid'] = "%";
        }elseif ($pstrIdTipo == "evp") {
        	//self::$garrParameters['title'] = array("text"=>"");
        	self::$garrParameters['title'] = "Evaluación de proveedor";
        	self::$garrParameters['unid'] = "%";
        	self::$garrColorsRange = array( array("Start"=>0,"End"=>69.99, "Color"=>"#e64427"),
    		                            array("Start"=>70,"End"=>100, "Color"=>"#3d9b35") );
        }else if ($pstrIdTipo == "adm") {
        	self::$garrParameters['title'] = "Calidad administrador de contrato";
        	self::$garrParameters['unid'] = "%";
        }else if ($pstrIdTipo == "esf") {
        	self::$garrParameters['title'] = "Evaluación Estado Financiero";
        	self::$garrParameters['unid'] = "%";
        	self::$garrColorsRange = array( array("Start"=>0,"End"=>49.99, "Color"=>"#3d9b35"),
    		                            array("Start"=>50,"End"=>100, "Color"=>"#e64427") );
        }else if ($pstrIdTipo == "dym") {
        	self::$garrParameters['title'] = "Deuda y morosidad";
        	self::$garrParameters['unid'] = "%";
        	self::$garrColorsRange = array( array("Start"=>0,"End"=>29.99, "Color"=>"#3d9b35"),
    		                            array("Start"=>30,"End"=>100, "Color"=>"#e64427") );
        }else if ($pstrIdTipo == "tri") {
        	self::$garrParameters['title'] = "% Contratos de conflictos tributarios";
        	self::$garrParameters['unid'] = "%";
        	self::$garrColorsRange = array( array("Start"=>0,"End"=>4.99, "Color"=>"#3d9b35"),
    		                            array("Start"=>5,"End"=>100, "Color"=>"#e64427") );
        }else if ($pstrIdTipo == "gar") {
        	self::$garrParameters['title'] = "Garantías y respaldos";
        }else if ($pstrIdTipo == "obl") {
        	self::$garrParameters['title'] = "% Empresas con obligaciones laborales no cubiertas";
        	self::$garrParameters['unid'] = "%";
        	self::$garrColorsRange = array( array("Start"=>0,"End"=>15.99, "Color"=>"#3d9b35"),
    		                            array("Start"=>16,"End"=>100, "Color"=>"#e64427") );
        }else if ($pstrIdTipo == "mit") {
        	self::$garrParameters['title'] = "Multas inspección del trabajo";
        	self::$garrParameters['unid'] = "%";
        	self::$garrColorsRange = array( array("Start"=>0,"End"=>0.99, "Color"=>"#3d9b35"),
    		                            array("Start"=>1,"End"=>100, "Color"=>"#e64427") );
        }else if ($pstrIdTipo == "fla") {
        	self::$garrParameters['title'] = "Fiscalización laboral";
        }else if ($pstrIdTipo == "acc") {
        	self::$garrParameters['title'] = "Índice de frecuencia";
        	self::$garrParameters['unid'] = "";
        	self::$garrColorsRange = array( array("Start"=>0,"End"=>2.6, "Color"=>"#3d9b35"),
    		                            array("Start"=>2.6,"End"=>5.25, "Color"=>"#e64427") );
        }else if ($pstrIdTipo == "gra") {
        	self::$garrParameters['title'] = "Índice de gravedad";
        	self::$garrParameters['unid'] = "";
        	self::$garrColorsRange = array( array("Start"=>0,"End"=>30, "Color"=>"#3d9b35"),
    		                            array("Start"=>30,"End"=>60, "Color"=>"#e64427") );
        }else if ($pstrIdTipo == "fct") {
        	self::$garrParameters['title'] = "Fiscalización condiciones de trabajo";
        	self::$garrParameters['unid'] = "%";
        }else{
        	self::$garrColorsRange = array( array("Start"=>0,"End"=>74.99, "Color"=>"#e64427"),
    		                            array("Start"=>75,"End"=>100, "Color"=>"#3d9b35") );
        }

    }

    static function getInformacion(){
    	return self::$garrParameters;
    }

    static function getMetas($parrEjeX){

    	$larrResult = array();
    	$lstrIdTipo = self::$parrFilter['ind'];
    	$larrMetas = array();
    	$lobjIndicador = \DB::table("tbl_indicadores")
		->where("tbl_indicadores.Indicador","=",$lstrIdTipo)
		->first();

        if ($lobjIndicador){
        	foreach ($parrEjeX as $key => $value) {
        		if (is_null($lobjIndicador->Meta)){
        			$larrResult[] = null;
        		}else{
        			$larrResult[] = floatval($lobjIndicador->Meta);
        		}
        	}
        	if ($larrResult){
        		$larrMetas = array("name"=>"Meta", "data"=>$larrResult, "color"=>"#ffdbd1");
        	}
        }

        return $larrMetas;

    }

    static function setOptions($parrParameters){
    	if (isset($parrParameters['title'])) {
    		self::$garrParameters['title'] = $parrParameters['title'];
    	}
    	if (isset($parrParameters['subtitle'])) {
    		self::$garrParameters['subtitle'] = $parrParameters['subtitle'];
    	}
    }

	static function getYears() {
    	$lobjYears = \DB::table('dim_tiempo')
		->distinct()
		->select(\DB::raw("dim_tiempo.Anio as value"), \DB::raw("dim_tiempo.Anio as display"))
		->where("dim_tiempo.Anio",">=",\DB::raw("YEAR(DATE_ADD(CURRENT_DATE, INTERVAL -5 YEAR))"))
		->where("dim_tiempo.Anio","<=",\DB::raw("YEAR(DATE_ADD(CURRENT_DATE, INTERVAL -".self::$pintMonth." MONTH))"))
		->orderBy("dim_tiempo.Anio","DESC")
		->get();

		return $lobjYears;
    }

    static function getMonths($pintYear=0) {

    	if (!$pintYear){
    		$pintYear = self::$pintCurrentYear;
    	}
        $lobjMonths = \DB::table('dim_tiempo')
		->distinct()
		->select(\DB::raw("dim_tiempo.mes as value"), \DB::raw("dim_tiempo.NMes3L as display"))
		->where("dim_tiempo.fecha_mes","<=",\DB::raw("DATE_ADD(CURRENT_DATE, INTERVAL -".self::$pintMonth." MONTH)"))
		->where("dim_tiempo.Anio","=",$pintYear)
		->orderBy("dim_tiempo.Anio","DESC")
		->orderBy("dim_tiempo.Mes","DESC")
		->get();

		return $lobjMonths;
    }

    static function getFaena(){
    	$lobjFaena = \DB::table('tbl_contgeografico')
		->select(\DB::raw("tbl_contgeografico.geo_id as value"), \DB::raw("tbl_contgeografico.geo_nombre as display"))
		->orderBy("tbl_contgeografico.geo_nombre","ASC")
		->get();
		return $lobjFaena;
	}

	static function getArea(){
		$lobjArea = \DB::table('tbl_contareafuncional')
		->select(\DB::raw("tbl_contareafuncional.afuncional_id as value"), \DB::raw("tbl_contareafuncional.afuncional_nombre as display"))
		->orderBy("tbl_contareafuncional.afuncional_nombre","ASC")
		->get();
		return $lobjArea;
	}

	static function getSegmento(){
		$lobjSegmento = \DB::table('tbl_contsegmento')
		->select(\DB::raw("tbl_contsegmento.segmento_id as value"), \DB::raw("tbl_contsegmento.seg_nombre as display"))
		->orderBy("tbl_contsegmento.seg_nombre","ASC")
		->get();
		return $lobjSegmento;
	}
	static function getContratos($pintControlaReporte = 1){
		$lobjContrato = \DB::table('tbl_contrato')
		->select(\DB::raw("tbl_contrato.contrato_id as value"), \DB::raw("concat(tbl_contratistas.RazonSocial, ' ', tbl_contrato.cont_numero) as display"))
		->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");
		if ($pintControlaReporte){
			$lobjContrato = $lobjContrato->whereraw("tbl_contrato.ControlaReporte = 1");
		}
		$lobjContrato = $lobjContrato->orderBy("tbl_contratistas.RazonSocial","ASC");
		if (self::$gintLevelUser==4){
        	$lobjContrato = $lobjContrato->whereraw("tbl_contrato.admin_id = ".self::$gintIdUser);
        }else if (self::$gintLevelUser==6){
        	$lobjContrato = $lobjContrato->whereraw("tbl_contrato.entry_by_access = ".self::$gintIdUser);
        }
		$lobjContrato = $lobjContrato->get();
		return $lobjContrato;
	}

	static function getComentarios($pintIdContrato,$pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0) {

		if (!$pintYear){
			$pintYear = self::$parrFilter['year'];
		}
		if (!$pintMonth){
			$pintMonth = self::$parrFilter['month'];
		}
		$lstrIndicador = self::$parrFilter['ind'];
		$lobjComentarios = \DB::table("tbl_planes_y_programas")
		->select(\DB::raw("tbl_planes_y_programas.programas_idFechaInicio as FechaReal"),
				 \DB::raw("dim_tiempo.mes as Mes"),
				 \DB::raw("dim_tiempo.NMes as Fecha"),
			     \DB::raw("tbl_planes_y_programas_ambito.ambito_nombre as Ambito"),
			     \DB::raw("tbl_planes_y_programas_ambitosub.ambito_nombre as Indicador"),
			     \DB::raw("tbl_planes_y_programas.programas_idDescripcion as Comentario")
			     )
		->join("tbl_planes_y_programas_ambito","tbl_planes_y_programas_ambito.ambito_id","=","tbl_planes_y_programas.ambito_id")
		->join("tbl_planes_y_programas_ambitosub","tbl_planes_y_programas_ambitosub.subambito_id","=","tbl_planes_y_programas.subambito_id")
		->join("dim_tiempo","dim_tiempo.fecha","=","tbl_planes_y_programas.programas_idFechaInicio")
		->where("tbl_planes_y_programas.contrato_id", "=", $pintIdContrato)
		->where("tbl_planes_y_programas_ambitosub.subambito_codigo", "=", $lstrIndicador)
		->where("tbl_planes_y_programas.programas_idTipo", "=", 1);
		if ($pintYear) {
			$lobjComentarios = $lobjComentarios->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjComentarios = $lobjComentarios->whereraw("dim_tiempo.mes = ".$pintMonth);
		}
		$lobjComentarios = $lobjComentarios->orderBy("dim_tiempo.mes","ASC");
		return $lobjComentarios;

	}

	static function getIndicadorGlobal($pintYear=NULL, $pintMonth=NULL, $pintControlaReporte=NULL, $pintIdContrato = null) {
		$larrResult = array();
		$lobjGlobal = self::getModelData("global", $pintYear, $pintMonth, 0, $pintControlaReporte, $pintIdContrato);
		//return $lobjGlobal;
		if (!is_null($lobjGlobal["EjeY"])) {
			$larrResult["valor"] = \MyFormats::FormatNumber($lobjGlobal["EjeY"][0],self::$garrParameters['format']);
			$larrResult["unid"] = self::$garrParameters["unid"];
			$larrResult["color"] = self::CalculaColor($lobjGlobal["EjeY"][0]);
		}else{
			$larrResult["valor"] = 0;
			$larrResult["unid"] = self::$garrParameters["unid"];
			$larrResult["color"] = self::CalculaColor(array(),"global");
		}
		return $larrResult;
	}

	static function ListContratos($pintIdContrato = null, $pintIdContratista = null, $pintControlaReporte = 1){

		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');

	    $lobjContratos = \DB::table('tbl_contrato')
	    ->select("tbl_contrato.contrato_id","tbl_contratistas.Rut", "tbl_contratistas.RazonSocial", "tbl_contrato.cont_numero", "tbl_contrato.cont_nombre")
	    ->join("tbl_contratistas", "tbl_contrato.IdContratista","=","tbl_contratistas.IdContratista")
	    ->join("dim_tiempo","tbl_contrato.cont_FechaFin","=","dim_tiempo.fecha");
	    if ($pintControlaReporte){
	    	$lobjContratos = $lobjContratos->where("controlareporte","=", 1);
	    }

	    if ($pintIdContrato){
			$lobjContratos = $lobjContratos->where('tbl_contrato.contrato_id', '=', $pintIdContrato);
	    }

	    if ($pintIdContratista){
	    	$lobjContratos = $lobjContratos->where('tbl_contrato.IdContratista', '=', $pintIdContratista);
	    }

        if (isset(self::$parrFilter['reg']) && self::$parrFilter['reg']){
			$lobjContratos = $lobjContratos->where('tbl_contrato.geo_id', '=', self::$parrFilter['reg']);
	    }

	    if (isset(self::$parrFilter['area']) && self::$parrFilter['area']){
			$lobjContratos = $lobjContratos->where('tbl_contrato.afuncional_id', '=', self::$parrFilter['area']);
	    }

        if (isset(self::$parrFilter['seg']) && self::$parrFilter['seg']){
        	$lobjContratos = $lobjContratos->where('tbl_contrato.segmento_id', '=', self::$parrFilter['seg']);
	    }

	    if ($lintLevelUser==6) {
      		$lobjContratos = $lobjContratos->where("tbl_contrato.entry_by_access","=",$lintIdUser);
  		}else if ($lintLevelUser==4) {
  			$lobjContratos = $lobjContratos->where("tbl_contrato.admin_id","=",$lintIdUser);
  		}

  		$lobjContratos = $lobjContratos->orderby("tbl_contratistas.RazonSocial","asc")->get();

		$lstrRender = array("lista"=>'<table style="width:100%;">', "detalle" => '<table style="width:100%;">');
		$lstrLink = "";
		$lintContador = "";
		$lintContadorDos = "";
		$lstrActual = 1;

	    foreach ($lobjContratos as $larrContratos) {

	    	//Lista
	    	if ($lintContador>1 || $lintContador===""){
	    		if ($lintContador!==""){
	    			$lstrRender["lista"] .= '</tr>';
	    		}
	    		$lintContador = 0;
	    		$lstrRender["lista"] .= '<tr>';
	    	}

	    	//Detalle
	    	if ($lintContadorDos > 1 || $lintContadorDos ===""){
	    		if ($lstrActual!="" && $lintContadorDos !== ""){
    				$lstrRender["detalle"] .= '</tr>';
    			}
	    		if ($lstrActual!="") {
	    			$lintContadorDos = 0;
	    			$lstrRender["detalle"] .= '<tr>';
	    		}
	    	}

	    	if ($lstrActual!="") {
	    		$lintContadorDos = $lintContadorDos===""?0:$lintContadorDos+1;
	    	}

	    	$lstrActual = "";
	    	$lintContador = $lintContador===""?0:$lintContador+1;

	    	$larrResultado = self::getGlobal("global",null,null,1, $larrContratos->contrato_id);

	    	$lintResultado = $larrResultado["general"];
	    	$lstrResultado['data'][] = array("rut"=>$larrContratos->Rut,
	    		                             "razonsocial"=>$larrContratos->RazonSocial,
	    		                             "cont_numero"=>$larrContratos->cont_numero,
	    		                             "cont_nombre"=>$larrContratos->cont_nombre,
	    		                             "general"=>$lintResultado);

	    	$lstrRender["lista"] .= '<td style="width:50%;">';
	    	$lstrRender["lista"] .= '<table>';
	    	$lstrRender["lista"] .= '<tr>';
	    	$lstrRender["lista"] .= '<td>';
	    	$lstrRender["lista"] .= '<div class="circle" style="background-color:'.$lintResultado['color'].'; width: 26px; height: 19px; padding-top:7px; text-align: center; vertical-align: middle; border-radius: 13px; font-size: 8px; color: white; ">';
	    	$lstrRender["lista"] .= $lintResultado["value"].'%';
	    	$lstrRender["lista"] .= '</div>';
	    	$lstrRender["lista"] .= '</td>';
	    	$lstrRender["lista"] .= '<td style="padding-left:10px;">';
	    	$lstrRender["lista"] .= '<a style="font-size:11px;">';
	    	$lstrRender["lista"] .= $larrContratos->cont_numero.' - '.$larrContratos->RazonSocial;
	    	$lstrRender["lista"] .= '</a> ';
	    	$lstrRender["lista"] .= '</td>';
	    	$lstrRender["lista"] .= '</tr>';
	    	$lstrRender["lista"] .= '</table>';
	    	$lstrRender["lista"] .= '</td>';

	    	$lstrActual = self::DetalleContratos($larrContratos->RazonSocial, $larrResultado, $lintContador, $larrContratos->cont_numero, $pintIdContrato);
	    	$lstrRender["detalle"] .= $lstrActual;

	    }

	    if ($lintContador==1){
	    	$lstrRender["lista"] .= '<td></td>';
	    }
	    if ($lintContadorDos==1){
	    	$lstrRender["detalle"] .= '<td></td>';
	    }
	    $lstrRender["lista"] .= '</tr></table>';
	    $lstrRender["detalle"] = $lstrRender["detalle"].'</tr></table>';

	    return $lstrRender;
	}

	static function DetalleContratos($pstrRazonSocial, $parrValores, $pintLinea, $pstrContrato = "", $pintIdContrato = null) {

		$lstrRender = "";
		$lstrRender .= '<td style="border:1px solid #666666;">
						  <table style="width:100%;">
                          <tr>
                            <td colspan="2" style="width:100%; text-align:center; font-size:20px;">
                             '.$pstrRazonSocial.'<br/><small style="font-size:14px;">'.$pstrContrato.'</small>
                            </td>
                          </tr>
						  <tr>
						    <td width="50%" style="vertical-align: top;">';
        $lstrNegocio = self::DetalleAmbito($parrValores['negocio'], $parrValores['parametros']['negocio']);
        $lstrRender .= $lstrNegocio['resultado'];
        $lstrRender .= '    </td>
        				    <td width="50%" style="vertical-align: top;">';
        $lstrFinanciero = self::DetalleAmbito($parrValores['financiero'], $parrValores['parametros']['financiero']);
        $lstrRender .= $lstrFinanciero['resultado'];
        $lstrRender .= '    </td>
        				  </tr>
        				  <tr>
                		    <td width="50%" style="vertical-align: top;">';
        $lstrLaboral = self::DetalleAmbito($parrValores['laboral'], $parrValores['parametros']['laboral']);
        $lstrRender .= $lstrLaboral['resultado'];
        $lstrRender .= '    </td>
                            <td width="50%" style="vertical-align: top;">';
        $lstrSeguridad = self::DetalleAmbito($parrValores['seguridad'], $parrValores['parametros']['seguridad']);
        $lstrRender .= $lstrSeguridad['resultado'];
        $lstrRender .= '    </td>';
        $lstrRender .= '  </tr>';
        $lstrRender .= '  </table>';
        $lstrRender .= '</td>';

        if ( ($lstrNegocio['code'] || $lstrFinanciero['code'] || $lstrLaboral['code'] || $lstrSeguridad['code']) || $pintIdContrato ) {
        	return $lstrRender;
        }else{
        	return "";
        }


	}

	static function DetalleAmbito($parrGeneralAmbito, $parrDataAmbito){

		$lintBandera = 0;
		$lstrResultado = '<table style="width:100%; font-size:10px; color:#333333;">';
		$lstrResultado .= '  <tr width="100%">';
		$lstrResultado .= '    <td colspan="3">';
		$lstrResultado .= '      <div style="color:#ffffff; text-align:center; background-color:'.$parrGeneralAmbito['color'].'">';
		$lstrResultado .= '        '.$parrGeneralAmbito['text'].'';
		$lstrResultado .= '      </div>';
		$lstrResultado .= '    </td>';
		$lstrResultado .= '  </tr>';

		foreach ($parrDataAmbito as $key => $value) {
			if ( $key!="negocioglobal" && $key != "financieroglobal" && $key != "laboralglobal" && $key != "seguridadglobal" ){
				$larrAmbito = $value[0];
				$lstrResultado .= '<tr width="100%">';
				$lstrResultado .= '    <td cellspacing="0" style="">'.$larrAmbito->Nombre.'</td>';
				$lstrResultado .= '    <td cellspacing="0" width="10%" style="text-align:right;">'.$larrAmbito->PonderadoFormat.'%</td>';
				$lstrResultado .= '    <td cellspacing="0" width="1%" style="background-color:'.$larrAmbito->Color.'"><br/></td>';
				$lstrResultado .= '</tr>';
				if ($larrAmbito->Color != "#3d9b35"){
					$lintBandera = 1;
				}
			}
		}

		$lstrResultado .= '</table>';
		if ($lintBandera){
			return array("code"=>1, "resultado" => $lstrResultado);
		}else{
			return array("code"=>0, "resultado" => $lstrResultado);
		}

	}

	static function GenerateReport($pintYear=NULL, $pintMonth=NULL, $pintReg = NULL, $pintArea = NULL, $pintSeg = NULL, $pintregdesc = NULL, $pintareadesc = NULL, $pintsegdesc = NULL, $pintIdContrato = NULL, $pintIdContratista = NULL, $pincoment = NULL) {

		 //asignamos las viriables para el gráfico
    	$larrData['DataReporte'] = array("reg"=>$pintReg,
					             'seg' => $pintSeg,
					             'area' => $pintArea,
					             'ind' => null,
					             "regdesc"=>$pintregdesc,
					             'segdesc' => $pintsegdesc,
					             'areadesc' => $pintareadesc,
					             'year' => $pintYear,
					             'mes' => $pintMonth,
					             'coment' => $pincoment,
            					 'completo' => 1,
					             'id'=>null
    	);

    	$lintControlaReporte = 1;
		$larrData["lstrContratista"] = "";
		$larrData["lstrContrato"] = "";

    	if ($pintIdContrato || $pintIdContratista){

    		$lintControlaReporte = 0;
    		if ($pintIdContratista){
    			$lobjContratistas = \DB::table('tbl_contratistas')
                      ->select('tbl_contratistas.rut', "tbl_contratistas.RazonSocial")
                      ->where('tbl_contratistas.IdContratista','=',$pintIdContratista)->first();
                if ($lobjContratistas){
    				$larrData["lstrContratista"] = $lobjContratistas->rut." ".$lobjContratistas->RazonSocial;
    			}
    		}
    		if ($pintIdContrato){
    			$lobjContrato = \DB::table('tbl_contrato')
                      ->select('tbl_contratistas.rut', "tbl_contratistas.RazonSocial", "tbl_contrato.cont_numero")
                      ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
                      ->where('tbl_contrato.contrato_id','=',$pintIdContrato)->first();
                if ($lobjContrato){
    				$larrData["lstrContrato"] = $lobjContrato->rut." ".$lobjContrato->RazonSocial." ".$lobjContrato->cont_numero;
    			}
    		}

    	}

    	//$lobjMyReports = new \MyReports($larrData['DataReporte']);
    	$larrDataRender = self::getGlobal("global", null, $pintMonth, $lintControlaReporte, $pintIdContrato, $pintIdContratista);
		$larrData["larrDataRender"] = $larrDataRender;

		$listacontratos = self::ListContratos($pintIdContrato, $pintIdContratista, $lintControlaReporte);
		$larrData["listacontratos"] = $listacontratos['lista'];
		$larrData["detallecontratos"] = $listacontratos['detalle'];

		//Armamos las barras
		$larrData["negocio"] = self::RenderBar($larrDataRender,'negocio');
		$larrData["financiero"] = self::RenderBar($larrDataRender,'financiero');
		$larrData["laboral"] = self::RenderBar($larrDataRender,'laboral');
		$larrData["seguridad"] = self::RenderBar($larrDataRender,'seguridad');

		$lstrHtmlView =  view('reportgral.print',$larrData)->render();

		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isPhpEnabled', true);
		$options->set('isRemoteEnabled', true);
		$options->set('isJavascriptEnabled', true);
		$options->set('defaultFont', 'arial');
		$options->set('defaultPaperSize','letter');
		$options->set('defaultPaperOrientation', 'landscape');

		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($lstrHtmlView);
		$dompdf->render();
        $lstrHtmlView = $dompdf->output();

        return $lstrHtmlView;

	}

    static function GeneratePreview($pintYear=NULL, $pintMonth=NULL, $pintReg = NULL, $pintArea = NULL, $pintSeg = NULL, $pintregdesc = NULL, $pintareadesc = NULL, $pintsegdesc = NULL, $pintIdContrato = NULL, $pintIdContratista = NULL, $pincoment = NULL) {

        //asignamos las viriables para el gráfico
        $larrData['DataReporte'] = array("reg"=>$pintReg,
            'seg' => $pintSeg,
            'area' => $pintArea,
            'ind' => null,
            "regdesc"=>$pintregdesc,
            'segdesc' => $pintsegdesc,
            'areadesc' => $pintareadesc,
            'year' => $pintYear,
            'mes' => $pintMonth,
            'coment' => $pincoment,
            'completo' => 1,
            'id'=>null
        );

        $lintControlaReporte = 1;
        $larrData["lstrContratista"] = "";
        $larrData["lstrContrato"] = "";

        if ($pintIdContrato || $pintIdContratista){

            $lintControlaReporte = 0;
            if ($pintIdContratista){
                $lobjContratistas = \DB::table('tbl_contratistas')
                    ->select('tbl_contratistas.rut', "tbl_contratistas.RazonSocial")
                    ->where('tbl_contratistas.IdContratista','=',$pintIdContratista)->first();
                if ($lobjContratistas){
                    $larrData["lstrContratista"] = $lobjContratistas->rut." ".$lobjContratistas->RazonSocial;
                }
            }
            if ($pintIdContrato){
                $lobjContrato = \DB::table('tbl_contrato')
                    ->select('tbl_contratistas.rut', "tbl_contratistas.RazonSocial", "tbl_contrato.cont_numero")
                    ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
                    ->where('tbl_contrato.contrato_id','=',$pintIdContrato)->first();
                if ($lobjContrato){
                    $larrData["lstrContrato"] = $lobjContrato->rut." ".$lobjContrato->RazonSocial." ".$lobjContrato->cont_numero;
                }
            }

        }

        //$lobjMyReports = new \MyReports($larrData['DataReporte']);
        $larrDataRender = self::getGlobal("global", null, $pintMonth, $lintControlaReporte, $pintIdContrato, $pintIdContratista);
        $larrData["larrDataRender"] = $larrDataRender;
        $listacontratos = self::ListContratos($pintIdContrato, $pintIdContratista, $lintControlaReporte);
        $larrData["listacontratos"] = $listacontratos['lista'];
        $larrData["detallecontratos"] = $listacontratos['detalle'];

        //Armamos las barras
        $larrData["negocio"] = self::RenderBar($larrDataRender,'negocio');
        $larrData["financiero"] = self::RenderBar($larrDataRender,'financiero');
        $larrData["laboral"] = self::RenderBar($larrDataRender,'laboral');
        $larrData["seguridad"] = self::RenderBar($larrDataRender,'seguridad');

        $lstrHtmlView =  view('reportgral.print',$larrData)->render();

        return $lstrHtmlView;

    }

	static function RenderBar($parrDataRender, $pstrAmbito){

		$lstrData = "";

		foreach ( $parrDataRender['parametros'][$pstrAmbito] as $larrAmbito ) {

			if (isset($larrAmbito[0])) {
				$lstrData .= '<table style="margin-top:30px;"> ';
				$lstrData .= '  <tr style="width:100%;">';
				$lstrData .= '    <td style="font-size:16px;">'.$larrAmbito[0]->Nombre.'</td>';
				$lstrData .= '  </tr>';
				$lstrData .= '</table>';
				$lstrData .= '<div style="margin-top:20px;">';
				$lstrData .= '<table style="width:100%;">';
				$lstrData .= '  <tr style="width:100%;">';
				if ($larrAmbito[0]->Ponderado === null) {
					$lstrData .= '    <td style="height:50px; width:100%; background-color:#dbd9d9;" >';
	                $lstrData .= '    </td>';
	                $min = $larrAmbito[0]->Banda[0];
					$max = $larrAmbito[0]->Banda[1];
				}else{
					$min = $larrAmbito[0]->Banda[0];
					$max = $larrAmbito[0]->Banda[1];
					$lstrData .= '      <td class="barra bandaminima" style="height:50px; width:'.$min['End'].'%; background-color: '.$min['Color'].'">';
	                $lstrData .= '      </td>';
	                $lstrData .= '      <td class="barra bandamaxima" style="height:50px; width:'.(100-$min['End']).'%; background-color: '.$max['Color'].'">';
	                $lstrData .= '      </td>';
				}
				$lstrData .= '  </tr>';
				$lstrData .= '</table>';
				if ($larrAmbito[0]->Ponderado === null) {

				}else{
					$lstrData .= '      <div class="marca" style="display:block; position:relative; top:-75px; left:0px; width: 100%; height: 60px;">';
					if ($larrAmbito[0]->Ponderado>50) {
						$lstrData .= '        <div style="border-left:3px solid #000000; height: 73px; margin-left:'.$larrAmbito[0]->Ponderado.'%"><span style="margin-left:-50px; width:50px; text-align:right;">'.\MyFormats::FormatNumber($larrAmbito[0]->Ponderado).$larrAmbito[0]->Unidad.'</span></div>';
					}else{
						$lstrData .= '        <div style="border-left:3px solid #000000; height: 73px; margin-left:'.$larrAmbito[0]->Ponderado.'%">'.\MyFormats::FormatNumber($larrAmbito[0]->Ponderado).$larrAmbito[0]->Unidad.'</div>';
					}
					$lstrData .= '      </div>';
				}
				$lstrData .= '</div>';
				if ($larrAmbito[0]->Ponderado === null) {
					$lstrData .= '<table style="width:100%;">';
				}else{
					$lstrData .= '<table style="width:100%; margin-top:-53px;">';
				}
				$lstrData .= '  <tr style="color:#666666; font-size: 10px;">';
	            $lstrData .= '    <td class="min " width="10%" style="text-align: left;">';
	            $lstrData .= $min['Start'];
	            $lstrData .= '    </td>';
	            $lstrData .= '    <td class="med " width="80%" style="text-align: center;">';
	            $lstrData .= '    </td>';
	            $lstrData .= '    <td class="max " width="10%" style="text-align: right;">';
	            $lstrData .= $max['End'];
	            $lstrData .= '    </td>';
	            $lstrData .= '  </tr>';
				$lstrData .= '</table>';
        	}
		}

		return $lstrData;

	}

	static function getFilters($pintControlaReporte = 1){

		$larrResult = array();
		$larrResult["selectYear"] = self::getYears();
		$larrResult["selectMonth"] = self::getMonths(self::$parrFilter['year']);
		$larrResult["selectTipoFaena"] = self::getFaena();
		$larrResult["selectArea"] = self::getArea();
		$larrResult["selectSegmento"] = self::getSegmento();
		$larrResult["selectContratos"] = self::getContratos($pintControlaReporte);

		return $larrResult;

	}

	static function LineCharts($pstrType=null, $pintYear=null, $pintMonth=null, $pintConEscala=null, $pintControlaReporte=null, $pintIdContrato = null){

		//Declaramos la variable
		$larrCharts = array();
		//Datos generales
		$larrCharts ["chart"] = array ("type" => "line");
		if (self::$garrParameters['title']){
			$larrCharts ["title"] = array ("text" => self::$garrParameters['title']);
		}else{
			$larrCharts ["title"] = array ("style" => array("display"=>"none"));
		}
		if (self::$garrParameters['subtitle']){
			$larrCharts ["subtitle"] = array ("text" => self::$garrParameters['subtitle']);
		}else{
			$larrCharts ["subtitle"] = array ("enabled" => false);
		}

		$larrCharts ["credits"] = array ("enabled" => false);
		//Preparamos el set de datos

		$larrResultData = self::getModelData($pstrType, $pintYear, $pintMonth, $pintConEscala, $pintControlaReporte, $pintIdContrato);

		//Los graficos de lineas los definimos
		$larrCharts ["series"] [] = array (
		            "name" => "Real",
		            "data" => $larrResultData["EjeY"]
		);
		$larrCategoria = $larrResultData["EjeX"];
		$larrCharts ["xAxis"] = array ("categories" => $larrCategoria );

		//Establecemos las metas para el indicador
		$larrMetas = self::getMetas($larrCharts["xAxis"]["categories"]);
		if ($larrMetas){
			$larrCharts ["series"] [] = $larrMetas;
		}

		$larrCharts ["yAxis"] = array ("title" => array ("text" => self::$garrParameters['unid'] ), "plotBands" => self::$garrMetas);
		if (self::$garrParameters['unid'] == "%") {
			$larrCharts ["yAxis"]["min"] = 0;
			$larrCharts ["yAxis"]["max"] = 100;
		}
		$larrCharts ["tooltip"] = array ("valueSuffix" => self::$garrParameters['unid'], "valueDecimals" => 2, "format" =>'{point.x:,.2f}', );
		$larrCharts ["legend"] = array ("layout" => "horizontal", "align" => "center", "verticalAlign"=>"bottom", "borderWidth"=>0 );

		return $larrCharts;
	}

	static function BubbleCharts($pstrType="detalle", $pintYear=null, $pintMonth=null, $pintConEscala=null, $pintControlaReporte=null, $pintIdContrato = null){

		//Declaramos la variable
		$larrCharts = array();
		//Datos generales
		$larrCharts ["chart"] = array ("type" => "bubble",
		                               "plotBorderWidth" => 1,
            						   "zoomType" => 'xy');
		$larrCharts ["legend"] = array ("enabled" => false);
		$larrCharts ["exporting"] = array ("enabled" => false);
		$larrCharts ["credits"] = array ("enabled" => false);
		$larrCharts ["xAxis"] = array ("gridLineWidth" => 1, "title" => array("text"=>""), "labels" => array("enabled" => false) );


		$larrCharts ["yAxis"] = array ("startOnTick" => true,
			                           "endOnTick" => true,
			                           "title" => array ("text" => self::$garrParameters['unid'] ),
			                           "gridLineWidth" => 1,
			                           "labels" => array ("text" => self::$garrParameters['unid']),
            							"showLastLabel" => false, "showFirstLabel" => false
			                           );
		if (self::$garrParameters['unid'] == "%") {
			$larrCharts ["yAxis"]["min"] = self::$garrColorsRange[0]["Start"]-20;
	        $larrCharts ["yAxis"]["max"] = self::$garrColorsRange[count(self::$garrColorsRange)-1]["End"]+20;
		}
		$larrCharts ["plotOptions"] = array ("series" => array( "dataLabels" => array ("enabled" => true, "format" => '{point.name}') ) );


		if (self::$garrParameters['title']){
			$larrCharts ["title"] = array ("text" => self::$garrParameters['title']);
		}else{
			$larrCharts ["title"] = array ("style" => array("display"=>"none"));
		}
		if (self::$garrParameters['subtitle']){
			$larrCharts ["subtitle"] = array ("text" => self::$garrParameters['subtitle']);
		}else{
			$larrCharts ["subtitle"] = array ("enabled" => false);
		}

		$larrResultData = self::getModelData($pstrType, $pintYear, $pintMonth, $pintConEscala, $pintControlaReporte, $pintIdContrato); //Los graficos de lineas los definimos
		$larrChartsTemp = array();
		foreach ($larrResultData["ResultData"] as $lintKey => $larrResult) {
			if (!is_null($larrResult->Valor)){
			$larrChartsTemp[] = array (
			           "name" => $larrResult->Codigo,
			            "x" => $lintKey,
	                    "y" => floatval($larrResult->Valor),
	                    "z" => floatval($larrResult->Dinero),
	                    "code" => $larrResult->Nombre,
	                    "idcontrato" => $larrResult->contrato_id,
	                    "ind"=>self::$parrFilter['ind'],
	                    "cont_numero" => $larrResult->Numero,
						);
			}
		}
		$larrCharts["series"][] = array("data"=>$larrChartsTemp);
		$larrCharts ["tooltip"] = array ( "useHTML" => true,
										  "headerFormat" => '<table>',
										  "pointFormat" => '<table><tr><td colspan="2"><h3>{point.code}</h3></td></tr>
										                            <tr><td colspan="2"><h5>{point.cont_numero}</h5></td></tr>
										                            <tr><td>Valor: </td><td>{point.y:,.2f}'.self::$garrParameters['unid'].'</td></tr>
										                            <tr><td>Dinero: </td><td>{point.z:,.0f}</td></tr></table>',
										  "footerFormat" => '</table>',
										  "followPointer" => false,
								          "crop" => false,
								          "overflow" => 'none',
								          "hideDelay" => 0,
								          "valueDecimals" => 2,
			                            );

            // formatter: function(){return
		    //    '<table>
		    //       <tr>
		    //         <th colspan="2"><h3>'+this.point.name+/*' '+this.point.numCont+*/'</h3></th>
		    //       </tr>'  +
            //    '<tr><th>'+medu + medp + unid+':</th><td>'+this.point.y+med+'</td></tr>' +
			//    '<tr><th>Costo:</th><td>$ '+ Highcharts.numberFormat(this.point.z,0,',','.')+'</td></tr></table>';},


		return $larrCharts;
	}

	static function ColumnCharts($pstrType=null, $pintYear=null, $pintMonth=null, $pintConEscala=null, $pintControlaReporte=null, $pintIdContrato = null){

		//Declaramos la variable
		$larrCharts = array();
		$larrCategoria = array();
		//$pstrType = "global"; //Configurado para mostrar solamente los globales

		self::setColorGeneral();

		//Datos generales
		$larrCharts ["chart"] = array ("type" => "column",
		                               "marginBottom" => 10,
            						   "marginTop" => 50,
									   "marginLeft" => 80,
									   "marginRight" => 80
            						  );
		$larrCharts ["plotOptions"] = array("bar"=>array("color"=>"#000000",
			                                        "shadow"=>false,
			                                        "borderWidth"=>0),
											"scatter" => array("marker"=>array("symbol"=>"line",
												                               "lineWidth"=>6,
												                               //"radius"=>15,
												                               "lineColor"=>"#000000",
												                               "fillColor"=>"none"),
														        "dataLabels" => array("x" =>-50,
																		              "enabled" =>true,
																		              "align" => 'rigth',
																		              "color" => 'black',
																		              "verticalAlign" => 'middle',
																		              "format" =>'{point.y:,.2f}%',
																		              "borderColor" => 'black',
																		              "shadow" => false,
																		              "style" => array("font-size" =>"20px"))));
		$larrCharts ["legend"] = array ("enabled" => false);
		$larrCharts ["exporting"] = array ("enabled" => false);
		$larrCharts ["credits"] = array ("enabled" => false);



		if (self::$garrParameters['title']){
			$larrCharts ["title"] = array ("text" => self::$garrParameters['title']);
		}else{
			$larrCharts ["title"] = array ("style" => array("display"=>"none"));
		}
		if (self::$garrParameters['subtitle']){
			$larrCharts ["subtitle"] = array ("text" => self::$garrParameters['subtitle']);
		}else{
			$larrCharts ["subtitle"] = array ("enabled" => false);
		}

		$larrResultData = self::getModelData($pstrType, $pintYear, $pintMonth, $pintConEscala, $pintControlaReporte, $pintIdContrato); //Los graficos de lineas los definimos

	    $larrChartsTemp = array();
		foreach ($larrResultData["EjeYPonderado"] as $lintKey => $lfltValue) {
			$larrChartsTemp[] = array (
			            "y" => $lfltValue,
	                    "value" => $larrResultData["EjeY"][$lintKey],
						);
		}
		$larrCharts ["series"] [] = array ("name" => "Cumplimiento General",
							               "type" => "scatter",
							               "data" => $larrChartsTemp
										  );

		$larrCharts ["yAxis"] = array("minPadding" => 0,
						        	  "maxPadding" => 0,
						        	  "tickColor" => '#000',
						        	  "tickWidth" => 1,
						        	  "tickLength" => 3,
						        	  "title" => array("text"=>''),
						        	  "gridLineWidth" => 0,
						        	  "endOnTick" => true,
							  	      "min"=>self::$garrColorsRange[0]["Start"],
	                                  "max"=>self::$garrColorsRange[count(self::$garrColorsRange)-1]["End"],
	                          		  "plotBands"=>array());

		if (!isset($larrResultData["EjeY"][0]) || (isset($larrResultData["EjeY"][0]) && $larrResultData["EjeY"][0]===null) ){
			$larrCharts ["yAxis"]["plotBands"][] = array("from"=>self::$garrColorsRangeDefault[0]["Start"],"to"=>self::$garrColorsRangeDefault[count(self::$garrColorsRangeDefault)-1]["End"],"color"=>"#dbd9d9");
		}else{
			foreach (self::$garrColorsRangeDefault as $lintRange => $larrRange) {
				$larrCharts ["yAxis"]["plotBands"][] = array("from"=>$larrRange["Start"],"to"=>$larrRange["End"],"color"=>$larrRange["Color"]);
			}
		}

		$larrCharts ["tooltip"] = array ("useHTML" => true,
										 "headerFormat" => '<table>',
										 "pointFormat" => '<table><tr><td colspan="2"><h3>Cumplimiento</h3></td></tr>
										                            <tr><td>Ponderado: </td><td>{point.y:,.2f}'.self::$garrParameters['unid'].'</td></tr>
										                            <tr><td>Valor Real: </td><td>{point.value:,.2f}'.self::$garrParameters['unid'].'</td></tr></table>',
										 "footerFormat" => '</table>',
										 "followPointer" => false,
								         "crop" => false,
								         "hideDelay" => 0,
								         "valueDecimals" => 2
		                                );

		$larrCharts ["xAxis"] = array ("categories" => $larrCategoria );
		//$larrCharts ["yAxis"] = array ("title" => array ("text" => self::$garrParameters['unid'] ) );



		return $larrCharts;
	}

	static function BarCharts($pstrType=null, $pintYear=null, $pintMonth=null, $pintConEscala=null, $pintControlaReporte=null, $pintIdContrato = null){

		//Declaramos la variable
		$larrCharts = array();
		$larrCategoria = array();

		//Datos generales
		$larrCharts ["chart"] = array ("type" => "bar");
		$larrCharts ["plotOptions"] = array("bar"=>array("color"=>"#000000",
			                                             "shadow"=>false,
			                                             "borderWidth"=>0,
			                                             "dataLabels" => array("enabled" => false) ),
											"scatter" => array("marker"=>array("symbol"=>"diamond",
												                               "lineWidth"=>3,
												                               "radius"=>20,
												                               "lineColor"=>"gray",
												                               "fillColor"=>"white")));
		if (self::$garrParameters['title']){
			$larrCharts ["title"] = array ("text" => self::$garrParameters['title']);
		}else{
			$larrCharts ["title"] = array ("text" => "", "style" => array("display"=>"none"));
		}
		if (self::$garrParameters['subtitle']){
			$larrCharts ["subtitle"] = array ("text" => self::$garrParameters['subtitle']);
		}else{
			$larrCharts ["subtitle"] = array ("enabled" => false);
		}

		$larrCharts ["credits"] = array ("enabled" => false);
		//Preparamos el set de datos
		$larrResultData = self::getModelData($pstrType, $pintYear, $pintMonth, $pintConEscala, $pintControlaReporte, $pintIdContrato); //Los graficos de lineas los definimos

		if ($pstrType=="global"){
			$larrCharts ["series"] [] = array (
            	"name" => "Reals",
            	"type" => "scatter",
            	"data" => array(array("y" => $larrResultData["EjeY"][0],
                				"name" => $larrCharts ["title"]['text'],
                				"cantidad" => $larrResultData["ResultData"][0]->Cantidad,
                				"ind"=>self::$parrFilter['ind'],
                				"color" => ''))
			);

			$lintMaxValue = $larrResultData["EjeY"][0];

			//Determinamos cuanto es el max
			//Recuperamos el ultimo valor de los rangos
			$lintMax = self::$garrColorsRange[count(self::$garrColorsRange)-1]["End"];

			if ($lintMax < $lintMaxValue){ //si el valor es mayor al valor maximo
				$lintMax = $lintMaxValue+($lintMaxValue*0.05);
			}

			//echo " valor: ".$lintMax;

			$larrCharts ["yAxis"] = array("min"=>self::$garrColorsRange[0]["Start"],
				                          "max"=>$lintMax,
				                          //"label"=>array("y"=>16, "style"=>array("fontSize"=>"20px")),
				                          "plotBands"=>array());

			if ($larrResultData["EjeY"][0]===null){
				$larrCharts ["yAxis"]["plotBands"][] = array("from"=>self::$garrColorsRange[0]["Start"],"to"=>self::$garrColorsRange[count(self::$garrColorsRange)-1]["End"],"color"=>"#dbd9d9");
			}else{
				foreach (self::$garrColorsRange as $lintRange => $larrRange) {
					$larrCharts ["yAxis"]["plotBands"][] = array("from"=>$larrRange["Start"],"to"=>$larrRange["End"],"color"=>$larrRange["Color"]);
				}
			}
			$larrCharts["yAxis"]["plotBands"][count($larrCharts ["yAxis"]["plotBands"])-1]['to'] = $lintMax;

		}else{
			$larrCharts ["series"] [] = array (
            	"name" => "Real",
            	"data" => $larrResultData["EjeY"]
			);
			$larrCategoria = $larrResultData["EjeX"];
			$larrCharts ["yAxis"] = array ("title" => array ("text" => self::$garrParameters['unid'] ) );
		}
		//$larrCharts ["xAxis"] = array ("label"=>array("enabled"=>false),"showFirstLabel" => false, "showLastLabel" => false );
		$larrCharts ["tooltip"] = array ( "useHTML" => true,
										  "headerFormat" => '<table>',
										  "pointFormat" => '<table><tr><td colspan="2"><h3>{point.name}</h3></td></tr>
										                            <tr><td>Valor: </td><td>{point.y:,.2f} '.self::$garrParameters['unid'].'</td></tr>
										                            <tr><td>Universo: </td><td>{point.cantidad:,.0f}</td></tr>
										                    </table>',
										  "footerFormat" => '</table>',
										  "followPointer" => false,
								          "crop" => false,
								          "overflow" => 'none',
								          "hideDelay" => 0,
								          "valueDecimals" => 2,
			                            );
		//$larrCharts ["legend"] = array ("layout" => "horizontal", "align" => "center", "verticalAlign"=>"bottom", "borderWidth"=>0 );

		return $larrCharts;
	}

	static function CustomCharts(){

		//Declaramos la variable
		$larrCharts = array();
		//Datos generales
		$larrCharts ["chart"] = array ("backgroundColor" => 'white',
			                            "credits" => array ("enabled" => false),
			                            "legend" => array ("enabled" => false));


		return $larrCharts;
	}

	static function getCharts($pstrChartsType, $pstrType=null, $pintYear=null, $pintMonth=null, $pintConEscala=null, $pintControlaReporte=null, $pintIdContrato = null){

		if (is_null($pstrType)){ $pstrType = "detalle"; }
		$larrResult = "";
		if ($pstrChartsType=="line"){
			$larrResult = self::LineCharts($pstrType, $pintYear, $pintMonth,$pintConEscala, $pintControlaReporte,$pintIdContrato);
		}else if ($pstrChartsType=="bubble"){
			$larrResult = self::BubbleCharts($pstrType, $pintYear, $pintMonth,$pintConEscala, $pintControlaReporte,$pintIdContrato);
		}else if ($pstrChartsType=="column"){
			$larrResult = self::ColumnCharts($pstrType, $pintYear, $pintMonth,$pintConEscala, $pintControlaReporte,$pintIdContrato);
		}else if ($pstrChartsType=="bar"){
			$larrResult = self::BarCharts($pstrType, $pintYear, $pintMonth,$pintConEscala, $pintControlaReporte,$pintIdContrato);
		}else if ($pstrChartsType=="custom"){
			$larrResult = self::CustomCharts();
		}
		return $larrResult;
	}

    public function __construct($parrFilters){

    	self::$pintDayChange = 20; // días a partir del que se habilita el mes anterior
    	self::$pintCurrentDate = date('Y-m-d');
    	self::$pintCurrentDay = date('d');
    	if (self::$pintCurrentDay <= self::$pintDayChange){
	    	self::$pintMonth = 2;
	    }else{
	    	self::$pintMonth = 1;
	    }
	    self::$pintCurrentDate = strtotime ( '-'.self::$pintMonth.' month' , strtotime ( self::$pintCurrentDate ) ) ;
		self::$pintCurrentDate = date ( 'Y-m-d' , self::$pintCurrentDate );
		self::$pintCurrentDay = date('d',strtotime(self::$pintCurrentDate));
    	self::$pintCurrentMonth = date('m',strtotime(self::$pintCurrentDate));
    	self::$pintCurrentYear = date('Y',strtotime(self::$pintCurrentDate));

    	self::$garrColorsRange = array( array("Start"=>0,"End"=>74.99, "Color"=>"#e64427"),
    		                            array("Start"=>75,"End"=>100, "Color"=>"#3d9b35") );
    	self::$garrColorsRangeDefault = self::$garrColorsRange;

    	self::$gintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        self::$gintIdUser = \Session::get('uid');

         //Logica del filtro, si es despues de un día en particular resta menos meses
    	if (isset($parrFilters['year'])){
    		if ($parrFilters['year']>0) {
				self::$parrFilter['year'] = $parrFilters['year'];
			}else{
    			self::$parrFilter['year'] = self::$pintCurrentYear;
    		}
    	}else{
			self::$parrFilter['year'] = self::$pintCurrentYear;
		}

    	if (isset($parrFilters['mes'])){
			if ($parrFilters['mes']>0) {
				self::$parrFilter['month'] = $parrFilters['mes'];
			}else{
				self::$parrFilter['month'] = 0;
			}
		}else{
			self::$parrFilter['month'] = 0;
		}

    	/*if (isset($parrFilters['mes'])){
    		if ($parrFilters['mes']>0) {
				self::$parrFilter['month'] = $parrFilters['mes'];
			}else{
    			self::$parrFilter['month'] = self::$pintCurrentMonth;
    		}
    	}
    	*/

    	if (isset($parrFilters['ind'])){
    		self::$parrFilter['ind'] = $parrFilters['ind'];
    	}else{
    		self::$parrFilter['ind'] = "";
    	}

    	if (isset($parrFilters['reg'])){
    		self::$parrFilter['reg'] = $parrFilters['reg'];
    	}else{
    		self::$parrFilter['reg'] = "";
    	}

    	if (isset($parrFilters['area'])){
    		self::$parrFilter['area'] = $parrFilters['area'];
    	}else{
    		self::$parrFilter['area'] = "";
    	}

		if (isset($parrFilters['seg'])){
    		self::$parrFilter['seg'] = $parrFilters['seg'];
    	}else{
    		self::$parrFilter['seg'] = "";
    	}

    	self::setInformacion(self::$parrFilter['ind']);

	    //Si el año es el actual y el mes de consulta es mayor colocamos todo como valor por defecto
    	//if (self::$parrFilter['year']==self::$pintCurrentYear && self::$parrFilter['month'] > self::$pintCurrentMonth){
    		//self::$parrFilter['month'] = 0;
    	//}

    }

    /***************************************************
	// Implementamos las consultas para cada uno de los
	// gráficos
    ****************************************************/
    /*POR MODIFICAR agregar variable que controle la clase de la base de datos calcPonderador */
    static function getModelData($pstrIdTipo, $pintYear=NULL, $pintMonth=NULL, $pintConEscala=NULL, $pintControlaReporte=NULL, $pintIdContrato = NULL, $pintIdContratista = NULL){

    	//echo "tipo: ".$pstrIdTipo;

    	if ($pstrIdTipo===null){
    		$pstrIdTipo = "resumen";
    	}
    	if ($pintYear===null){
    		$pintYear = self::$parrFilter['year'];
    	}
    	if ($pintMonth===null){
    		$pintMonth = self::$parrFilter['month'];
    	}
    	$larrResult = null;
    	$larrResultData = array();
    	switch (self::$parrFilter['ind']){
    		//AMBITO DEL NEGOCIO
    		case 'fin': // AVANCE FINANCIERO
    			$lstrFunctionPonderado = "SUM(Valor)";
    			$lstrFunctionValor = "CASE WHEN ((SUM(Valor)/SUM(ValorPlan))*100) <= 100
    									   THEN -((SUM(Valor)/SUM(ValorPlan))*100)
    									   ELSE ((SUM(Valor)/SUM(ValorPlan))*100)-100 END";
    			$larrResult = self::getModelFinanciero($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato);
    			break;
    		case 'kpi': // KPI
    			$lstrFunctionPonderado = "calcKPI(AVG(Valor))";
    			$lstrFunctionValor = "AVG(Valor)";
    			$larrResult = self::getModelKPI($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		case 'evp': // EVALUACION DEL PROVEEDOR
    		    $lstrFunctionPonderado = "calcEvaluacionProveedor(AVG(Valor))";
    			$lstrFunctionValor = "AVG(Valor)";
    			$larrResult = self::getModelEvaluacionProveedor($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		case 'adm': // EVALUACION ADMINISTRADOR DE CONTRATO
    			$lstrFunctionPonderado = "calcEvaluacionAdministrador(AVG(Valor))";
    			$lstrFunctionValor = "AVG(Valor)";
    			$larrResult = self::getModelEvaluacionAdministracion($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		//FIN AMBITO DEL NEGOCIO
    		//INICIO AMBITO FINANCIERO
    		case 'esf': // EVAL ESTADO FINANCIERO
    			$lstrFunctionPonderado = "fnCalculoPonderado('esf', avg(valor))";
    			$lstrFunctionValor = "avg(valor)";
    			$larrResult = self::getModelEvalEstadoFinanciero($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		case 'dym': // DEUDA Y MOROSIDAD
    			$lstrFunctionPonderado = "fnCalculoPonderado('dym', avg(valor))";
    			$lstrFunctionValor = "AVG(Valor)";
    			$larrResult = self::getModelDeudaYMorosidad($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		case 'tri': // SITUACION TRIBUTARIA
    		    $lstrFunctionPonderado = "(1-SUM(DISTINCT(Valor))/COUNT(DISTINCT(contrato_id)))*100";
    			$lstrFunctionValor = "AVG(Valor)";
    			$larrResult = self::getModelSituacionTributaria($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		case 'gar': // GARANTIAS Y RESPALDOS
    			$lstrFunctionPonderado = "fnCalculoPonderado('gar', avg(valor))";
    			$lstrFunctionValor = "AVG(Valor)";
    			$larrResult = self::getModelGarantiasYRespaldos($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		//FIN AMBITO FINANCIERO
    		//INICIO AMBITO DEL ENTORNO LABORAL
    		case 'obl': // OBLIGACIONES LABORALES
    			$lstrFunctionPonderado = "fnCalculoPonderado('obl',avg(Valor))";
    			$lstrFunctionValor = "avg(Valor)";
    			$larrResult = self::getModelObligacionesLaborales($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		case 'mit': // MULTAS INSPECCION DEL TRABAJO
    			$lstrFunctionPonderado = "fnCalculoPonderado('mit',avg(Valor))";
    			$lstrFunctionValor = "AVG(Valor)";
    			$larrResult = self::getModelMultasInspeccionTrabajo($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		case 'fla': // FISCALIZACION LABORAL
    		    $lstrFunctionPonderado = "fnCalculoPonderado('fla',avg(Valor))";
    			$lstrFunctionValor = "AVG(Valor)";
    			$larrResult = self::getModelFiscalizacionLaboral($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		//FIN AMBITO DEL ENTORNO LABORAL
    		//INICIO AMBITO DE SEGURIDAD Y CONDICIONES LABORALES
    		case 'acc': // INDICE DE FRECUENCIA
    			$lstrFunctionPonderado = "fnCalculoPonderado('acc',(sum(n_accid)/sum(hh))*200000)";
    			$lstrFunctionValor = "(sum(n_accid)/sum(hh))*200000";
    			$larrResult = self::getModelIndiceFrecuencia($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		case 'gra': // INDICE DE GRAVEDAD
    			$lstrFunctionPonderado = "fnCalculoPonderado('gra',(sum(dias_perd)/sum(hh))*200000)";
    			$lstrFunctionValor = "(sum(dias_perd)/sum(hh))*200000";
    			$larrResult = self::getModelIndiceGravedad($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		case 'fct': // FISCALIZACION CONDICIONES DE TRABAJO
    		    $lstrFunctionPonderado = "fnCalculoPonderado('fct', AVG(Valor))";
    			$lstrFunctionValor = "AVG(Valor)";
    			$larrResult = self::getModelCondicionesLaborales($pintControlaReporte, $pintYear, $pintMonth,$pintConEscala, $pintIdContrato, $pintIdContratista);
    			break;
    		//FIN AMBITO DE SEGURIDAD Y CONDICIONES LABORALES

    	}
    	//Completamos la información
    	//echo $pstrIdTipo;
    	if (self::$parrFilter['ind']=="acc" && $pstrIdTipo == 'resumen'){
			//echo "SELECT ".$lstrFunctionPonderado." as Ponderado, ".$lstrFunctionValor." as Valor, COUNT(DISTINCT tabla.contrato_id) as Cantidad FROM (".$larrResult->toSql().") as Tabla";
		}
    	if (!is_null($larrResult)){
    		if ($pstrIdTipo=="resumen"){
	    		//echo "paso 1";
	    	}
	    	if ($pstrIdTipo == "global"){
	    		if (self::$parrFilter['ind']=="acc"){
	    			//echo "SELECT ".$lstrFunctionPonderado." as Ponderado, ".$lstrFunctionValor." as Valor, COUNT(DISTINCT tabla.contrato_id) as Cantidad FROM (".$larrResult->toSql().") as Tabla";
	    		}
	    		$larrResult = \DB::select("SELECT ".$lstrFunctionPonderado." as Ponderado, ".$lstrFunctionValor." as Valor, COUNT(DISTINCT tabla.contrato_id) as Cantidad FROM (".$larrResult->toSql().") as tabla");
	    	}else if ($pstrIdTipo == "resumen"){
	    		if (self::$parrFilter['ind']=="acc"){
	    		 //echo "SELECT Tabla.Titulo, avg(Tabla.Valor) as Valor FROM (".$larrResult->toSql().") as Tabla GROUP BY Tabla.Titulo Order By Tabla.Orden";
	    		}
	    		$larrResult = \DB::select("SELECT Tabla.Titulo, ".$lstrFunctionValor." as Valor FROM (".$larrResult->toSql().") as Tabla GROUP BY Tabla.Titulo Order By Tabla.Orden");
	    	}else if ($pstrIdTipo == "detalle"){
	    		if (self::$parrFilter['ind']=="acc"){
	    			//echo "SELECT Tabla.Nombre, Tabla.contrato_id, Tabla.Codigo, Tabla.Numero, ".$lstrFunctionValor." as Valor, sum(Tabla.Dinero) as Dinero FROM (".$larrResult->toSql().") as Tabla GROUP BY Tabla.Nombre, Tabla.contrato_id, Tabla.Codigo, Tabla.Numero Order By Tabla.Nombre";
	    		}
	    		$larrResult = \DB::select("SELECT Tabla.Nombre, Tabla.contrato_id, Tabla.Codigo, Tabla.Numero, ".$lstrFunctionValor." as Valor, sum(Tabla.Dinero) as Dinero FROM (".$larrResult->toSql().") as Tabla GROUP BY Tabla.Nombre, Tabla.contrato_id, Tabla.Codigo, Tabla.Numero Order By Tabla.Nombre");
	    	}else if ($pstrIdTipo == "directa"){
	    		$larrResult = $larrResult->get();
	    	}
	    	$larrResultData["ResultData"] = $larrResult;
	    	if ($pstrIdTipo != "global"){
		    	$larrResultData["EjeY"] = collect($larrResult)->transform(function($larrValue, $lintKey){ if ($larrValue->Valor=="") { return null; } else { return floatval($larrValue->Valor); } })->toArray();
		    	if ($pstrIdTipo == "resumen"){
		    		$larrResultData["EjeX"] = collect($larrResult)->transform(function($larrValue, $lintKey){ return (string) $larrValue->Titulo; })->toArray();
		    	}
	    	}else{
	    		$larrResultData["EjeYPonderado"] = collect($larrResult)->transform(function($larrValue, $lintKey){ if ($larrValue->Ponderado=="") { return null; } else { return floatval($larrValue->Ponderado); } })->toArray();
	    		$larrResultData["EjeY"] = collect($larrResult)->transform(function($larrValue, $lintKey){ if ($larrValue->Valor=="") { return null; } else { return floatval($larrValue->Valor); } })->toArray();
	    	}
    	}else {
            $larrResultData["ResultData"] = null;
        }
        if (self::$parrFilter['ind']== "acc" && $pstrIdTipo == 'resumen'){

    		//var_dump($larrResultData);
        }
    	return $larrResultData;
    }

	static function getGlobalNegocio($pstrIdTipo, $pintYear=0, $pintMonth=0, $pintControlaReporte=0, $pintIdContrato = null, $pintIdContratista = null){

	    $larrResultGlobal = array();
	    $lstrFilterInd = self::$parrFilter['ind'];
	    $lintTotal = null;
	    $lintCantidad = null;

	    //Recuperamos los indicadores configurados para el Ambito de negocio
	    $lobjAmbitoNegocio = TblPlanesYProgramasAmbitosub::Ambito(1)->get();

	    foreach ($lobjAmbitoNegocio as $larrAmbitoNegocio) {
	    	self::setInformacion($larrAmbitoNegocio->subambito_codigo);
 			$larrResult = self::getModelData("global",$pintYear, $pintMonth, 0, $pintControlaReporte, $pintIdContrato,$pintIdContratista);
 			$larrResult["ResultData"][0]->{"Unidad"} = self::$garrParameters['unid'];
 			$larrResult["ResultData"][0]->{"Nombre"} = $larrAmbitoNegocio->ambito_nombre;
 			$larrResult["ResultData"][0]->{"Banda"} = self::$garrColorsRange;
 			$larrResult["ResultData"][0]->{"Color"} = self::CalculaColorGeneral($larrResult["ResultData"][0]->Ponderado);
 			$larrResult["ResultData"][0]->{"PonderadoFormat"} = \MyFormats::FormatNumber($larrResult["ResultData"][0]->Ponderado);
 			$larrResult["ResultData"][0]->{"ValorFormat"} = \MyFormats::FormatNumber($larrResult["ResultData"][0]->Valor);
        	$larrResultGlobal[self::$parrFilter['ind']] = $larrResult["ResultData"];
        	$lintCantidad = $lintCantidad + 1;
        	if ($larrResult["ResultData"]){
        		if (!is_null($larrResult["ResultData"][0]->Ponderado) ) {
        			$lintTotal = $lintTotal + $larrResult["ResultData"][0]->Ponderado;
        		}
        	}
	    }

        self::$parrFilter['ind'] = $lstrFilterInd;
        if (!is_null($lintCantidad) && $lintCantidad > 0 && !is_null($lintTotal) ){
        	$larrResultGlobal['negocioglobal'] = ($lintTotal / $lintCantidad);
        }else{
        	$larrResultGlobal['negocioglobal'] = null;
        }

        return $larrResultGlobal;
	}

	static function getGlobalFinanciero($pstrIdTipo, $pintYear=0, $pintMonth=0, $pintControlaReporte=0, $pintIdContrato = null, $pintIdContratista = null){

		$larrResultGlobal = array();
	    $lstrFilterInd = self::$parrFilter['ind'];
	    $lintTotal = null;
	    $lintCantidad = null;

	    //Recuperamos los indicadores configurados para el Ambito de negocio
	    $lobjAmbitoNegocio = TblPlanesYProgramasAmbitosub::Ambito(2)->get();

	    foreach ($lobjAmbitoNegocio as $larrAmbitoNegocio) {
	    	self::setInformacion($larrAmbitoNegocio->subambito_codigo);
 			$larrResult = self::getModelData("global",$pintYear, $pintMonth, 0, $pintControlaReporte, $pintIdContrato, $pintIdContratista);
 			$larrResult["ResultData"][0]->{"Unidad"} = self::$garrParameters['unid'];
 			$larrResult["ResultData"][0]->{"Nombre"} = $larrAmbitoNegocio->ambito_nombre;
 			$larrResult["ResultData"][0]->{"Banda"} = self::$garrColorsRange;
 			$larrResult["ResultData"][0]->{"Color"} = self::CalculaColorGeneral($larrResult["ResultData"][0]->Ponderado);
 			$larrResult["ResultData"][0]->{"PonderadoFormat"} = \MyFormats::FormatNumber($larrResult["ResultData"][0]->Ponderado);
 			$larrResult["ResultData"][0]->{"ValorFormat"} = \MyFormats::FormatNumber($larrResult["ResultData"][0]->Valor);
        	$larrResultGlobal[self::$parrFilter['ind']] = $larrResult["ResultData"];
        	$lintCantidad = $lintCantidad + 1;
        	if ($larrResult["ResultData"]){
        		if (!is_null($larrResult["ResultData"][0]->Ponderado) ) {
        			$lintTotal = $lintTotal + $larrResult["ResultData"][0]->Ponderado;
        		}
        	}
	    }

        self::$parrFilter['ind'] = $lstrFilterInd;
        if (!is_null($lintCantidad) && $lintCantidad > 0 && !is_null($lintTotal) ){
        	$larrResultGlobal['financieroglobal'] = ($lintTotal / $lintCantidad);
        }else{
        	$larrResultGlobal['financieroglobal'] = null;
        }

        return $larrResultGlobal;

	}

	static function getGlobalEntornoLaboral($pstrIdTipo, $pintYear=0, $pintMonth=0, $pintControlaReporte=0, $pintIdContrato = null, $pintIdContratista = null){

  	    $larrResultGlobal = array();
	    $lstrFilterInd = self::$parrFilter['ind'];
	    $lintTotal = null;
	    $lintCantidad = null;

	    //Recuperamos los indicadores configurados para el Ambito de negocio
	    $lobjAmbitoNegocio = TblPlanesYProgramasAmbitosub::Ambito(3)->get();

	    foreach ($lobjAmbitoNegocio as $larrAmbitoNegocio) {
	    	self::setInformacion($larrAmbitoNegocio->subambito_codigo);
 			$larrResult = self::getModelData("global",$pintYear, $pintMonth, 0, $pintControlaReporte, $pintIdContrato, $pintIdContratista);
 			$larrResult["ResultData"][0]->{"Unidad"} = self::$garrParameters['unid'];
 			$larrResult["ResultData"][0]->{"Nombre"} = $larrAmbitoNegocio->ambito_nombre;
 			$larrResult["ResultData"][0]->{"Banda"} = self::$garrColorsRange;
 			$larrResult["ResultData"][0]->{"Color"} = self::CalculaColorGeneral($larrResult["ResultData"][0]->Ponderado);
 			$larrResult["ResultData"][0]->{"PonderadoFormat"} = \MyFormats::FormatNumber($larrResult["ResultData"][0]->Ponderado);
 			$larrResult["ResultData"][0]->{"ValorFormat"} = \MyFormats::FormatNumber($larrResult["ResultData"][0]->Valor);
        	$larrResultGlobal[self::$parrFilter['ind']] = $larrResult["ResultData"];
        	$lintCantidad = $lintCantidad + 1;
        	if ($larrResult["ResultData"]){
        		if (!is_null($larrResult["ResultData"][0]->Ponderado) ) {
        			$lintTotal = $lintTotal + $larrResult["ResultData"][0]->Ponderado;
        		}
        	}
	    }

        self::$parrFilter['ind'] = $lstrFilterInd;
        if (!is_null($lintCantidad) && $lintCantidad > 0 && !is_null($lintTotal) ){
        	$larrResultGlobal['laboralglobal'] = ($lintTotal / $lintCantidad);
        }else{
        	$larrResultGlobal['laboralglobal'] = null;
        }

        return $larrResultGlobal;

	}

	static function getGlobalSegYCondLaboral($pstrIdTipo, $pintYear=0, $pintMonth=0, $pintControlaReporte=0, $pintIdContrato = null, $pintIdContratista = null){

		$larrResultGlobal = array();
	    $lstrFilterInd = self::$parrFilter['ind'];
	    $lintTotal = null;
	    $lintCantidad = null;

	    //Recuperamos los indicadores configurados para el Ambito de negocio
	    $lobjAmbitoNegocio = TblPlanesYProgramasAmbitosub::Ambito(4)->get();

	    foreach ($lobjAmbitoNegocio as $larrAmbitoNegocio) {
	    	self::setInformacion($larrAmbitoNegocio->subambito_codigo);
 			$larrResult = self::getModelData("global",$pintYear, $pintMonth, 0, $pintControlaReporte, $pintIdContrato, $pintIdContratista);
 			$larrResult["ResultData"][0]->{"Unidad"} = self::$garrParameters['unid'];
 			$larrResult["ResultData"][0]->{"Nombre"} = $larrAmbitoNegocio->ambito_nombre;
 			$larrResult["ResultData"][0]->{"Banda"} = self::$garrColorsRange;
 			$larrResult["ResultData"][0]->{"Color"} = self::CalculaColorGeneral($larrResult["ResultData"][0]->Ponderado);
 			$larrResult["ResultData"][0]->{"PonderadoFormat"} = \MyFormats::FormatNumber($larrResult["ResultData"][0]->Ponderado);
 			$larrResult["ResultData"][0]->{"ValorFormat"} = \MyFormats::FormatNumber($larrResult["ResultData"][0]->Valor);
        	$larrResultGlobal[self::$parrFilter['ind']] = $larrResult["ResultData"];
        	$lintCantidad = $lintCantidad + 1;
        	if ($larrResult["ResultData"]){
        		if (!is_null($larrResult["ResultData"][0]->Ponderado) ) {
        			$lintTotal = $lintTotal + $larrResult["ResultData"][0]->Ponderado;
        		}
        	}
	    }

        self::$parrFilter['ind'] = $lstrFilterInd;
        if (!is_null($lintCantidad) && $lintCantidad > 0 && !is_null($lintTotal) ){
        	$larrResultGlobal['seguridadglobal'] = ($lintTotal / $lintCantidad);
        }else{
        	$larrResultGlobal['seguridadglobal'] = null;
        }

        return $larrResultGlobal;

	}

    static function getGlobal($pstrIdTipo, $pintYear=null, $pintMonth=null,$pintControlaReporte=0, $pintIdContrato = null, $pintIdContratista = NULL){

		$larrResultGlobal = array();
		$lintTotal = null;
		//resultado para ambito global

		if (is_null($pintYear)) { $pintYear = self::$parrFilter['year']; }
		if (is_null($pintMonth)) { $pintMonth = self::$parrFilter['month']; }

		$larrResultGlobal['parametros']['negocio'] = self::getGlobalNegocio($pstrIdTipo, $pintYear, $pintMonth,$pintControlaReporte, $pintIdContrato, $pintIdContratista);
		$lintValueNegocio = $larrResultGlobal['parametros']['negocio']['negocioglobal'];
		$larrResultGlobal['negocio'] = array();
		$larrResultGlobal['negocio']['text'] = "Ambito del negocio";
		$larrResultGlobal['negocio']['value'] = \MyFormats::FormatNumber($lintValueNegocio);
		$larrResultGlobal['negocio']['unid'] = "%";
		$larrResultGlobal['negocio']['color'] = self::CalculaColor($lintValueNegocio,"global");
		if (!is_null($lintValueNegocio)){
			$lintTotal = $lintTotal + $lintValueNegocio;
		}

		$larrResultGlobal['parametros']['financiero'] = self::getGlobalFinanciero($pstrIdTipo, $pintYear, $pintMonth,$pintControlaReporte, $pintIdContrato, $pintIdContratista);
		$lintValueFinanciero = $larrResultGlobal['parametros']['financiero']['financieroglobal'];
		$larrResultGlobal['financiero'] = array();
		$larrResultGlobal['financiero']['text'] = "Ambito financiero";
		$larrResultGlobal['financiero']['value'] = \MyFormats::FormatNumber($lintValueFinanciero);
		$larrResultGlobal['financiero']['unid'] = "%";
		$larrResultGlobal['financiero']['color'] = self::CalculaColor($lintValueFinanciero,"global");
		if (!is_null($lintValueFinanciero)){
			$lintTotal = $lintTotal + $lintValueFinanciero;
		}

		$larrResultGlobal['parametros']['laboral'] = self::getGlobalEntornoLaboral($pstrIdTipo, $pintYear, $pintMonth,$pintControlaReporte, $pintIdContrato, $pintIdContratista);
		$lintValueLaboral = $larrResultGlobal['parametros']['laboral']['laboralglobal'];
		$larrResultGlobal['laboral'] = array();
		$larrResultGlobal['laboral']['text'] = "Ambito del entorno laboral";
		$larrResultGlobal['laboral']['value'] = \MyFormats::FormatNumber($lintValueLaboral);
		$larrResultGlobal['laboral']['unid'] = "%";
		$larrResultGlobal['laboral']['color'] = self::CalculaColor($lintValueLaboral,"global");
		if (!is_null($lintValueLaboral)){
			$lintTotal = $lintTotal + $lintValueLaboral;
		}

		$larrResultGlobal['parametros']['seguridad'] = self::getGlobalSegYCondLaboral($pstrIdTipo, $pintYear, $pintMonth,$pintControlaReporte, $pintIdContrato, $pintIdContratista);
		$lintValueSeguridad = $larrResultGlobal['parametros']['seguridad']['seguridadglobal'];
		$larrResultGlobal['seguridad'] = array();
		$larrResultGlobal['seguridad']['text'] = "Seguridad y condiciones laborales";
		$larrResultGlobal['seguridad']['value'] = \MyFormats::FormatNumber($lintValueSeguridad);
		$larrResultGlobal['seguridad']['unid'] = "%";
		$larrResultGlobal['seguridad']['color'] = self::CalculaColor($lintValueSeguridad,"global");
		if (!is_null($lintValueSeguridad)){
			$lintTotal = $lintTotal + $lintValueSeguridad;
		}

		if (!is_null($lintTotal)){
			$lintValue = $lintTotal/4;
		}else{
			$lintValue = null;
		}
		$larrResultGlobal['general'] = array();
		$larrResultGlobal['general']['text'] = "";
		$larrResultGlobal['general']['value'] = \MyFormats::FormatNumber($lintValue,0);
		$larrResultGlobal['general']['unid'] = "%";
		$larrResultGlobal['general']['color'] = self::CalculaColor($lintValue);

		$larrResultGlobal['escala'] = self::$garrColorsRange;


		return $larrResultGlobal;
    }

    /*Modelo de funciones estructurales para ambito del negocio*/
    static function getModelFinanciero($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato = 0){

    	$lobjSetData = \DB::table("tbl_financiero_and")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    		     "tbl_contrato.contrato_id",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoplan as ValorPlan"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"));

		$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_financiero_and.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_financiero_and.financieroand_fecha");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_financiero_and.financieroand_fecha");
		}
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes= ".$pintMonth);
		}
        return $lobjSetData;
    }
    
    static function getModelKPI($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato = 0, $pintIdContratista = 0){
    	$lobjSetData = \DB::table("vw_kpi")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    			 "dim_tiempo.fecha",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     "vw_kpi.Valor",
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"),
    		     \DB::raw("'>=' as MetaIndicador"),
    		     \DB::raw("tbl_indicadores.Meta as Meta")
    		     )
    	->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

    	$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("vw_kpi.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala==1) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","vw_kpi.kpiDet_fecha");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","vw_kpi.kpiDet_fecha");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'vw_kpi.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'vw_kpi.kpiDet_fecha');
        });

		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}
        return $lobjSetData;
    }

    static function getModelKPIDetalle($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0){
    	$lobjSetData = \DB::table("tbl_kpis")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    		     "tbl_contrato.contrato_id",
    			 "dim_tiempo.fecha",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("tbl_kpis_detalles.Puntaje as Puntaje"),
    		     \DB::raw("tbl_kpis.Nombre as Nombre"),
    			 \DB::raw("'>=' as MetaIndicador"),
    		     \DB::raw("CASE WHEN tbl_kpis.IdTipo = 1 THEN
					                  concat(' >= ', REPLACE(CAST(tbl_kpis_detalles.RangoSuperior AS DECIMAL(11,2)),'.',','), ' ', tbl_kpis.IdUnidad)
					                WHEN tbl_kpis.IdTipo = 2 THEN
					                  concat(' <= ', REPLACE(CAST(tbl_kpis_detalles.RangoSuperior AS DECIMAL(11,2)),'.',','), ' ', tbl_kpis.IdUnidad)
					                WHEN tbl_kpis.IdTipo = 3 THEN
					                  CASE WHEN tbl_kpis_detalles.MetaInferior = tbl_kpis_detalles.MetaSuperior THEN
					                    concat(' [ ', REPLACE(CAST(tbl_kpis_detalles.MetaInferior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' ] ' )
					                  ELSE
					                  	concat(' [ ', REPLACE(CAST(tbl_kpis_detalles.MetaInferior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' - ', REPLACE(CAST(tbl_kpis_detalles.MetaSuperior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' ] ' )
					                  END
					                WHEN tbl_kpis.IdTipo = 4 THEN
					                  CASE WHEN tbl_kpis_detalles.MetaInferior = tbl_kpis_detalles.MetaSuperior THEN
					                    concat(' [ ', REPLACE(CAST(tbl_kpis_detalles.MetaInferior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' ] ' )
					                  ELSE
					                    concat(' [ ', REPLACE(CAST(tbl_kpis_detalles.MetaSuperior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' - ', REPLACE(CAST(tbl_kpis_detalles.MetaInferior AS DECIMAL(11,2)),'.',','),' ', tbl_kpis.IdUnidad, ' ] ' )
					                  END
					            END as Meta"),
    		     \DB::raw("tbl_kpis_detalles.Resultado as Resultado"),
    		     \DB::raw("CASE WHEN tbl_kpis_detalles.Resultado < 0 THEN 0   WHEN tbl_kpis_detalles.Resultado > 100 THEN 100 ELSE tbl_kpis_detalles.Resultado END as ResultadoAjustado"))
        ->whereraw("tbl_kpis_detalles.Puntaje is not null")
    	->join("tbl_kpis_detalles","tbl_kpis.IdKpi","=","tbl_kpis_detalles.IdKpi")
    	->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));
    	if ($pintControlaReporte){
    		$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_kpis.contrato_id AND tbl_contrato.ControlaReporte = 1"));
    	}else{
    		$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=","tbl_kpis.contrato_id");
    	}
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");
		if ($pintConEscala==1) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_kpis_detalles.Fecha");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_kpis_detalles.Fecha");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_kpis.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_kpis_detalles.Fecha');
        });

		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}
		if (self::$gintLevelUser==4){
        	$lobjSetData = $lobjSetData->whereraw("tbl_contrato.entry_by_access = ".self::$gintIdUser);
        }else if (self::$gintLevelUser==6){
        	$lobjSetData = $lobjSetData->whereraw("tbl_contrato.admin_id = ".self::$gintIdUser);
        }
        //Filtramos por el Id del contrato
        if ($pintIdContrato){
        	$lobjSetData = $lobjSetData->whereraw("tbl_kpis.contrato_id = ".$pintIdContrato);
        }
        //$lobjSetData = $lobjSetData->groupBy("tbl_contratistas.RazonSocial", "tbl_contrato.contrato_id", "dim_tiempo.Mes", "dim_tiempo.NMes3L", "dim_tiempo.NMes", "dim_tiempo.fecha", "tbl_contrato.cont_nombre","tbl_contrato.cont_numero","tbl_kpimensual.kpiDet_puntaje");
        $lobjSetData = $lobjSetData->orderBy("dim_tiempo.Mes","ASC")->orderBy("tbl_kpis.Nombre","ASC");
        return $lobjSetData;
    }

    static function getModelEvaluacionProveedor($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_evalcontratistas")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    			 "dim_tiempo.fecha",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("tbl_evalcontratistas.eval_puntaje as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"),
    		     \DB::raw("'>=' as MetaIndicador"),
    		     \DB::raw("tbl_indicadores.Meta as Meta"))
    	->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

    	$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_evalcontratistas.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_evalcontratistas.eval_fecha");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_evalcontratistas.eval_fecha");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_evalcontratistas.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_evalcontratistas.eval_fecha');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Mes = ".$pintMonth);
		}
        $lobjSetData = $lobjSetData->orderBy("dim_tiempo.Mes","ASC");
        return $lobjSetData;
    }

    static function getModelEvaluacionAdministracion($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_calidad_adm_cont")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    			 "dim_tiempo.fecha",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("tbl_calidad_adm_cont.resultado as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"),
    		     \DB::raw("'>=' as MetaIndicador"),
    		     \DB::raw("tbl_indicadores.Meta as Meta"))
    	->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

		$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_calidad_adm_cont.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_calidad_adm_cont.fec_rev");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_calidad_adm_cont.fec_rev");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_calidad_adm_cont.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_calidad_adm_cont.fec_rev');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}


        return $lobjSetData;
    }

    static function getModelEvalEstadoFinanciero($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_eval_financiera")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 \DB::raw("tbl_contrato.contrato_id"),
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("tbl_eval_financiera.valor as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"))
    	->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

		$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_eval_financiera.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_eval_financiera.fecha_validez");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_eval_financiera.fecha_validez");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_eval_financiera.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_eval_financiera.fecha_validez');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}

        return $lobjSetData;
    }

    static function getModelDeudaYMorosidad($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_morosidad")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("tbl_morosidad.per_moros*100 as Valor"), //se multiplica por 100 ya que cada valor esta representado en fraccion del entero 0.xx
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"))
    	->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

    	$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_morosidad.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_morosidad.fecha_Eval");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_morosidad.fecha_Eval");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_morosidad.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_morosidad.fecha_Eval');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}

        return $lobjSetData;
    }

    static function getModelSituacionTributaria($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_contratista_tributario")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("CASE WHEN tbl_contratista_tributario.tCont_eval = 'BUENO' THEN 0 WHEN tbl_contratista_tributario.tCont_eval = 'MALO' THEN 1 ELSE tbl_contratista_tributario.tCont_eval END as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"))
    	->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

    	$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_contratista_tributario.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

    	if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_contratista_tributario.tCont_fecha");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_contratista_tributario.tCont_fecha");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_contratista_tributario.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_contratista_tributario.tCont_fecha');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}

        return $lobjSetData;
    }

    static function getModelGarantiasYRespaldos($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_garantias")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("((tbl_garantias.total_fondos+tbl_garantias.prendas)/(tbl_garantias.mutuos+tbl_garantias.total_impacto))*100 as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"))
		->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

		$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_garantias.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");


		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_garantias.fecha");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_garantias.fecha");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_garantias.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_garantias.fecha');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}

        return $lobjSetData;
    }

    static function getModelObligacionesLaborales($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_obliglab_repo")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("(tbl_obliglab_repo.valor)*100 as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"))
		->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

		$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_obliglab_repo.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_obliglab_repo.fecha");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_obliglab_repo.fecha");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_obliglab_repo.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_obliglab_repo.fecha');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}

        return $lobjSetData;
    }

    static function getModelMultasInspeccionTrabajo($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_contratistacondicionfin")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("tbl_contratistacondicionfin.finCont_cantidad as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"))
    	->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

    	$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_contratistacondicionfin.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_contratistacondicionfin.finCont_fecha");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_contratistacondicionfin.finCont_fecha");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_contratistacondicionfin.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_contratistacondicionfin.finCont_fecha');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}

        return $lobjSetData;
    }

    static function getModelFiscalizacionLaboral($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_fiscalización")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("tbl_fiscalización.perc_lab as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"))
    	->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

    	$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_fiscalización.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_fiscalización.fecha");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_fiscalización.fecha");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_fiscalización.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_fiscalización.fecha');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}

        return $lobjSetData;
    }

    static function getModelIndiceFrecuencia($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_accidentes")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("sum(tbl_accidentes.n_accid) as n_accid"),
    		     \DB::raw("sum(tbl_accidentes.hh) as hh"),
    		     \DB::raw("(sum(tbl_accidentes.n_accid) / sum(tbl_accidentes.hh))*200000 as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"))
    	->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

    	$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_accidentes.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_accidentes.fecha_informe");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_accidentes.fecha_informe");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_accidentes.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_accidentes.fecha_informe');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}

		$lobjSetData = $lobjSetData->groupBy("tbl_contratistas.RazonSocial",
	                                 "tbl_contrato.contrato_id",
	                                 "tbl_contrato.cont_nombre",
	                                 "tbl_contrato.cont_numero",
	                                 "dim_tiempo.Mes",
	                                 "dim_tiempo.NMes3L",
	                                 "dim_tiempo.NMes",
	                                 "tbl_financiero_and.financieroand_mtoreal");

        return $lobjSetData;
    }

    static function getModelIndiceGravedad($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_accidentes")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("sum(tbl_accidentes.dias_perd) as dias_perd"),
    		     \DB::raw("sum(tbl_accidentes.hh) as hh"),
    		     \DB::raw("(sum(tbl_accidentes.dias_perd)/sum(tbl_accidentes.hh))*200000 as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"))
    	->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

    	$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_accidentes.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_accidentes.fecha_informe");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_accidentes.fecha_informe");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_accidentes.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_accidentes.fecha_informe');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}

		$lobjSetData = $lobjSetData->groupBy("tbl_contratistas.RazonSocial",
			                                 "tbl_contrato.contrato_id",
			                                 "tbl_contrato.cont_nombre",
			                                 "tbl_contrato.cont_numero",
			                                 "dim_tiempo.Mes",
			                                 "dim_tiempo.NMes3L",
			                                 "dim_tiempo.NMes",
			                                 "tbl_financiero_and.financieroand_mtoreal");

        return $lobjSetData;
    }

    static function getModelCondicionesLaborales($pintControlaReporte=0, $pintYear=0, $pintMonth=0,$pintConEscala=0, $pintIdContrato=0, $pintIdContratista = 0){

    	$lobjSetData = \DB::table("tbl_fiscalización")
    	->select(\DB::raw("tbl_contratistas.RazonSocial as Nombre"),
    			 "tbl_contrato.contrato_id",
    		     \DB::raw("tbl_contrato.cont_nombre as Codigo"),
    		     \DB::raw("tbl_contrato.cont_numero as Numero"),
    		     \DB::raw("dim_tiempo.Mes as Orden"), //establecemos el valor por el que se van a ordenar los datos
    		     \DB::raw("dim_tiempo.NMes3L as Titulo"), //establecemos que sea mensual
    		     \DB::raw("dim_tiempo.NMes as TituloLargo"), //establecemos que sea mensual
    		     \DB::raw("(tbl_fiscalización.perc_cond+tbl_fiscalización.perc_equip)/2 as Valor"),
    		     \DB::raw("tbl_financiero_and.financieroand_mtoreal as Dinero"))
		->Leftjoin("tbl_indicadores","tbl_indicadores.indicador","=",\DB::raw(" '".self::$parrFilter['ind']."' "));

		$lstrCondicionesContrato = self::ConstructorFiltroContrato($pintIdContrato, $pintControlaReporte, $pintIdContratista);

    	$lobjSetData = $lobjSetData->join("tbl_contrato","tbl_contrato.contrato_id","=",\DB::raw("tbl_fiscalización.contrato_id ".$lstrCondicionesContrato));
		$lobjSetData = $lobjSetData->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista");

		if ($pintConEscala) {
			$lobjSetData = $lobjSetData->Rightjoin("dim_tiempo","dim_tiempo.fecha","=","tbl_fiscalización.fecha");
		}else{
			$lobjSetData = $lobjSetData->join("dim_tiempo","dim_tiempo.fecha","=","tbl_fiscalización.fecha");
		}
		$lobjSetData = $lobjSetData->Leftjoin('tbl_financiero_and', function ($join) {
            $join->on('tbl_financiero_and.contrato_id', '=', 'tbl_fiscalización.contrato_id')
            ->on('tbl_financiero_and.financieroand_fecha', '=', 'tbl_fiscalización.fecha');
        });
		if ($pintYear) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.Anio = ".$pintYear);
		}
		if ($pintMonth) {
			$lobjSetData = $lobjSetData->whereraw("dim_tiempo.mes = ".$pintMonth." ");
		}

        return $lobjSetData;
    }

    static function CalculaColor($pintValor, $pstrIndicador = ""){
		$lstrColor = "#dbd9d9";
		if ($pintValor!==null){
			if ($pstrIndicador){
				self::setInformacion($pstrIndicador);
			}
			foreach (self::$garrColorsRange as $larrColor) {
				if ($pintValor >= $larrColor["Start"] && $pintValor <= $larrColor["End"] ) {
					$lstrColor = $larrColor["Color"];
					return $lstrColor;
				}
			}
			if ($pintValor<self::$garrColorsRange[0]['Start']){
				return self::$garrColorsRange[0]['Color'];
			}
			if ($pintValor>self::$garrColorsRange[count(self::$garrColorsRange)-1]['End']){
				return self::$garrColorsRange[count(self::$garrColorsRange)-1]['Color'];
			}
		}
    	return $lstrColor;
    }

    static function CalculaColorGeneral($pintValor){
    	$lstrColor = "#dbd9d9";
		if ($pintValor!==null){
			foreach (self::getColorGeneral() as $larrColor) {
				if ($pintValor >= $larrColor["Start"] && $pintValor <= $larrColor["End"] ) {
					$lstrColor = $larrColor["Color"];
					break;
				}
			}
		}
    	return $lstrColor;
    }

    static function ConstructorFiltroContrato($pintIdContrato = null, $pintControlaReporte = null, $pintIdContratista = null){

    	$lstrResultado = "";

        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
        $lstrResultado .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';

        if ($pintIdContrato){
        	$lstrResultado .= " AND tbl_contrato.contrato_id = ".$pintIdContrato;
        }

        if ($pintIdContratista){
        	$lstrResultado .= " AND tbl_contrato.IdContratista = ".$pintIdContratista;
        }

        if (isset(self::$parrFilter['reg']) && self::$parrFilter['reg']){
			$lstrResultado .= " AND tbl_contrato.geo_id = ".self::$parrFilter['reg'];
	    }

	    if (isset(self::$parrFilter['area']) && self::$parrFilter['area']){
			$lstrResultado .= " AND tbl_contrato.afuncional_id = ".self::$parrFilter['area'];
	    }

        if (isset(self::$parrFilter['seg']) && self::$parrFilter['seg']){
        	$lstrResultado .= " AND tbl_contrato.segmento_id = ".self::$parrFilter['seg'];
	    }

    	if ($pintControlaReporte && ( self::$gintLevelUser == 1 || self::$gintLevelUser == 4 || self::$gintLevelUser == 7  ) ) {
	    	$lstrResultado .= " AND tbl_contrato.ControlaReporte = 1 ";
	    }

    	return $lstrResultado;
    }

}

?>
