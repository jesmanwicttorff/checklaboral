<?php

use App\Models\Indicadorescolor;

class MyFormats {

	static public function CssIndicadores(){
		$lstrResult = "";
		$lobjIndicadores = Indicadorescolor::all();
		foreach ($lobjIndicadores as $lrowData) {
			$lstrResult .= " .ind-".$lrowData->indicador_id.'-ruler-'.$lrowData->id."-color { \n";
			$lstrResult .= "  color:".$lrowData->color."!important\n";
			$lstrResult .= " } \n";
			$lstrResult .= " .ind-".$lrowData->indicador_id.'-ruler-'.$lrowData->id."-bgcolor { \n";
			$lstrResult .= "  background-color:".$lrowData->color."!important\n";
			$lstrResult .= " } \n";
		}
		return $lstrResult;
	}

	static public function ClaseIndicador($pintIdIndicador,$pintValor,$pstrPropiedad = ""){

		$lstrResult = "";

		$lobjIndicadores = Indicadorescolor::where('indicador_id',$pintIdIndicador)
											->where('desde','<=',$pintValor)
											->where('hasta','>=',$pintValor)
											->first();
		if (count($lobjIndicadores)) {
			if ($pstrPropiedad == "color" || $pstrPropiedad == "" ){
				$lstrResult .= " ind-".$lobjIndicadores->indicador_id.'-ruler-'.$lobjIndicadores->id."-color";
			}
			if ($pstrPropiedad == "bgcolor" || $pstrPropiedad == "" ){
				$lstrResult .= " ind-".$lobjIndicadores->indicador_id.'-ruler-'.$lobjIndicadores->id."-bgcolor";
			}
		}else{
			if ($pstrPropiedad == "color" || $pstrPropiedad == "" ){
				$lstrResult .= " ind-sn-ruler-sn-color ";
			}
			if ($pstrPropiedad == "bgcolor" || $pstrPropiedad == "" ){
				$lstrResult .= " ind-sn-ruler-sn-bgcolor ";
			}
		}

		return $lstrResult;

	}

	static public function FormatCurrency($num,$decimal=0,$expression=""){
	  if ($num) {
			if (strpos($num, ',') !== false) {
				return $num;
	    }else{
				if ($expression=="M"){
					$escala = 1000000;
					$simbolo = "<small>M$</small> ";
				}else{
					$escala = 1;
					$simbolo = "";
				}
				return $simbolo."".number_format(($num/$escala), $decimal, ',', '.');
			}
	  }else{
			if ($expression=="M"){
				return "<small>M$</small> 0";
			}else{
				return "0";
			}
	  }
	}
	static public function FormatNumber($num,$decimal=2){
	  if ($num) {
          if (strpos($num, ',') !== false) {
          	return $num;
		  }else{
              return number_format($num, $decimal, ',', '.');
		  }
	  }else{
	    return 0.00;
	  }
	}
	static public function FormatNumberP($num,$decimal=2){
	  if ($num) {
	    return number_format($num*100, $decimal, ',', '.');
	  }else{
	    return 0.00;
	  }
	}
	static public function FormatDateTime($lstrDate){
		if ($lstrDate=='0000-00-00 00:00:00' || $lstrDate == '' || $lstrDate == 'null') {
			return "";
		}else{
			$date = new DateTime($lstrDate);
			return $date->format('d/m/Y h:i a');
		}
	}
	static public function FormatDate($lstrDate, $pstrFormat = 'd/m/Y'){
		if ($lstrDate=='0000-00-00' || $lstrDate == '0000-00-00 00:00:00' || $lstrDate == '' || $lstrDate == 'null') {
			return "";
		}else{
			$date = new DateTime($lstrDate);
			return $date->format($pstrFormat);
		}
	}
	static public function FormatoFecha($pstrFecha){
        if ($pstrFecha=='0000-00-00' || $pstrFecha=='0000-00-00 00:00:00' || $pstrFecha == '' || $pstrFecha == 'null') {
            return "";
        }else{
            if ((strpos($pstrFecha, '/') !== false) and (strlen(trim($pstrFecha))==10)){
                $larrFecha = explode("/", $pstrFecha);
                return $larrFecha[2].'-'.$larrFecha[1].'-'.$larrFecha[0];
			}

        }
    }
	static public function FormatDateMonth($lstrDate){
		if ($lstrDate=='0000-00-00' || $lstrDate == '' || $lstrDate == 'null') {
			return "";
		}else{
			return date('m/Y', strtotime($lstrDate));
		}
	}
	static public function FormatDateYear($lstrDate){
		if ($lstrDate=='0000-00-00' || $lstrDate == '' || $lstrDate == 'null') {
			return "";
		}else{
			return date('Y', strtotime($lstrDate));
		}
	}
	static public function FormatSelect($iddocumentovalor){

		$docvalor = \DB::table('tbl_documento_valor')->where('IdDocumentoValor',$iddocumentovalor)->first();
		if($docvalor)
		{
				$value = \DB::table('tbl_tipo_documento_data')->where('IdTipoDocumentoValor',$docvalor->IdTipoDocumentoValor)->where('Valor',$docvalor->Valor)->first();
		}
		if($value){
				return $value->Display;
		}else{
			return "";
		}


	}

 }

 ?>
