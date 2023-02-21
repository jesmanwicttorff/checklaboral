<?php

class MyDashboard {

  static public function Ambitos(){

  	//Negocio
  	$lobjKPI = \DB::tabla('tbl_kpigral')
  	               ->join('tbl_kpimensual','tbl_kpimensual.id_kpi','=','tbl_kpigral.id_kpi');
  	$lobjEvaluacion = \DB::tabla('tbl_evalcontratistas')
  	                  ->whereNotNull('eval_puntaje');
  	$lobjCalidad = \DB::tabla('tbl_calidad_adm_cont')
  	               ->whereNotNull('resultado');

  	//Financiero
  	$lobjEvaluacionFinanciera = \DB::tabla('tbl_eval_financiera');
  	$lobjMorocidad = \DB::tabla('tbl_morosidad');
  	$lobjTributaria = \DB::tabla('tbl_contratista_tributario');
  	$lobjGarantia = \DB::tabla('tbl_garantias');

  	//Laboral

  	$lobjObligacionLaboral = \DB::tabla('tbl_obliglab_repo');
  	$lobjCondicionFinanciera = \DB::tabla('tbl_contratistacondicionfin');
  	$lobjFiscalizacion = \DB::tabla('tbl_fiscalización');

  	//Seguridad
  	$lobjSeguridad = \DB::tabla('tbl_accidentes');


  }

}

?>
