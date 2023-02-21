<?php

namespace App\Library;

use DB;
use App\Models\Documentos;

class MyDocumentsPrevired extends MyDocuments {
	static public $filename="";
	static public $afpID=0;
	static public $isapreID=0;
	static public $rut = "";
	static public $emp = "";
	static public $isapre = "";
	static public $folio = "";
	static public $mutualID = "";
	static public $caja="";
	static public $periodo="";
	static public $tipo="";
	static public $fechaPago="";
	static public $cajaID="";
	static public $fonasaID="";
	static public $ipsID="";
	static public $gintdebug;

	public function __construct($pintIdDocumento)
	{
			parent::__construct($pintIdDocumento);
	}

	static private function insertaSQL($sql,$valores){
		if(strlen(trim($sql))>0){
		    if(DB::insert($sql,$valores)){
		    	return "OK";
		    }
		}
	}

	static private function fechaDePago($tmp){
		self::$tipo="";
		self::$fechaPago="";
		if(self::$gintdebug){
			print_r($tmp);
		}
		$x=0;
		foreach ($tmp as $valor) {
			if($valor=="PreviRed.com"){
				break;
			}
			if($x<count($tmp)-1){
				$x++;
			}
		}
		if(self::$gintdebug){
			print_r($tmp);
			echo "<br>x=".$x;
		}
		if($tmp[$x]=="PreviRed.com"){
			for ($z = 1; $z <= 10; $z++){
				if(substr_count($tmp[$x-$z],"/")==2){
					//es una fecha
					self::$fechaPago = $tmp[$x-$z]." ".self::$fechaPago;
				}
				if(substr($tmp[$x-$z], 0,5)=="Decla"){
					self::$tipo=$tmp[$x-$z];
					break;
				}
				if(substr($tmp[$x-$z], 0,5)=="Pago "){
					self::$tipo=$tmp[$x-$z];
					break;
				}
			}
			self::$fechaPago=trim(self::$fechaPago);
		}
	}

	static private function putAFPempresa($datos){
		$obj = $datos;
		if(self::$gintdebug){
			print_r($obj);
		}
		$id = 0;
		if(count($obj)==8){
			$sql = "INSERT INTO afp_empresa (id, IdDocumento, archivo, nombre, folio, rut, empresa, periodo, tipo,fechaPago,login_user ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$valores=array(0,self::$gobjDocumento->IdDocumento, $obj["archivo"], $obj["nombre"], $obj["folio"], $obj["rut"], $obj["empresa"], $obj["periodo"], $obj["tipo"], $obj["fechaPago"], 'Alfonso');
			if(self::$gintdebug){
				echo "<br>".$sql;
				print_r($valores);
			}
		    $result = DB::insert($sql, $valores);
		    if($result==1){
				$sql = "SELECT max(id) AS id FROM afp_empresa WHERE IdDocumento=:doc AND rut=:rut AND periodo=:periodo limit 1";
			    $result = db::select($sql, ['doc'=>self::$gobjDocumento->IdDocumento, 'rut'=>$obj["rut"], 'periodo'=>$obj["periodo"] ]);
			    if(count($result)>0){
			    	$maxRecords = count($result);
			    	foreach ($result as $row) {
					    $id = $row->id;
					}
			    }
			}
	    }
	    if(self::$gintdebug){
	    	echo "AFP_ID: ".$id;
	    }
	    return $id;
	}

	static private function putAFPtrabajadores($afpID,$tra){
	    if(self::$gintdebug){
	    	echo "<br>AFP Trabajadores: ";
	    	print_r($tra);
	    }
		if($afpID>0){
			//Barre todos los trabajadores
			$i = 0;
			$token=0;
			$pendiente=0;
			$campos=1;
			foreach ($tra as $valor) {
				$i++;
				if(substr(trim($valor),0,7)=="TOTALES" || substr($valor,0,6)=="ISAPRE"){
					break;
				}
				if(strpos($valor,"-")>0 && strlen(trim($valor))<14){
					//echo "<br>Encontre RUT AFP";
					if($token>0 && is_numeric(substr($ri,0,1)) && is_numeric(substr($co,0,1)) && is_numeric(substr($cv,0,1))){
						if($ri=="0" || $ri=="1"){
							//Tipo Independiente
							//ajustar campos
							$ri=$co;
							$co=$sis;
							$sis=$cv;
							$cv=$c7;
							if($c8==0){
								$codigo=$c8;
								$c11="";
								$c12="";
								$f1="";
								$f2="";
							}
							if( !is_numeric($c7)){
								$c7=$cv;
								$cv="";
								$c8="";
							}
							if(strpos($c9,"/")>1 && strpos($c10,"/")>1){
								//Con Fechas
								$f1=$c9;
								$f2=$c10;
							}elseif (strpos($c9,"/")>1) {
								switch ($codigo) {
									case '2':
										# Asignando la fecha a Término
										$f1="";
										$f2=$c9;
										break;

									case '6':
										# Asignando la fecha a Inicio
										$f1=$c9;
										$f2="";
										break;
								}
							}else{
								$c9="";
								$c10="";
								$c11="";
								$c12="";
							}

						}

						//inserta valores
						if(strlen(trim($c13))>0 && strlen(trim($f2))>0){
							//Todos los campos completos
							if(self::$gintdebug){
								echo "Todos los campos<br>";
							}
							$sql = "INSERT INTO afp_trabajador (id_afp_trabajador, afp_empresa_id, rut, nombre, remuneracion_imponible, cotizacion_obligatoria, sis, apv, contrato, deposito_convenido, deposito_cta_ahorro, remuneracion_imponible_cesantia, cotizacion_afiliado, cotizacion_empleador, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores = array(0, $afpID, $rut, $nom, $ri, $co, $sis, $cv, $c7, $c8, $c9, $c10, $c11, $c12, $codigo, $f1, $f2);
						}
						if(strlen(trim($c13))>0 && strlen(trim($f2))==0){
							//con Contrato pero Sin Fecha de Inicio
							if(self::$gintdebug){
								echo "con Contrato pero Sin Fecha de Inicio<br>";
							}
							$sql = "INSERT INTO afp_trabajador (id_afp_trabajador, afp_empresa_id, rut, nombre, remuneracion_imponible, cotizacion_obligatoria, sis, apv, contrato, deposito_convenido, deposito_cta_ahorro, remuneracion_imponible_cesantia, cotizacion_afiliado, cotizacion_empleador, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores = array(0, $afpID, $rut, $nom, $ri, $co, $sis, $cv, $c7, $c8, $c9, $c10, $c11, $c12, $codigo, '', $f1);
						}
						if(strlen(trim($c13))==0 && strlen(trim($f2))>0){
							//Sin Contrato
							if(self::$gintdebug){
								echo "Sin Contrato<br>";
							}
							$sql = "INSERT INTO afp_trabajador (id_afp_trabajador, afp_empresa_id, rut, nombre, remuneracion_imponible, cotizacion_obligatoria, sis, apv, contrato, deposito_convenido, deposito_cta_ahorro, remuneracion_imponible_cesantia, cotizacion_afiliado, cotizacion_empleador, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores= array(0, $afpID, $rut, $nom, $ri, $co, $sis, $cv, '', $c7, $c8, $c9, $c10, $c11, $c12, $f1, $f2);
						}
						if(strlen(trim($c13))==0 && strlen(trim($f2))==0){
							//Sin Contrato y Sin Fecha de Inicio
							if(self::$gintdebug){
								echo "Sin Contrato y Sin Fecha de Inicio<br>";
							}
							$sql = "INSERT INTO afp_trabajador (id_afp_trabajador, afp_empresa_id, rut, nombre, remuneracion_imponible, cotizacion_obligatoria, sis, apv, contrato, deposito_convenido, deposito_cta_ahorro, remuneracion_imponible_cesantia, cotizacion_afiliado, cotizacion_empleador, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores= array(0, $afpID, $rut, $nom, $ri, $co, $sis, $cv, '', $c7, $c8, $c9, $c10, $c11, $c12, '', $f1);
						}
						if(strlen(trim($nom))>0){
							if(self::$gintdebug){
								echo $sql."<br>";
								print_r($valores);
							}
							$basura = self::insertaSQL($sql,$valores);
						}
						$pendiente=0;
					}
					$token=1;
					$campos=1;
					$c11 = $c12 = $c13 = $f1 = $f2 = null;
					$rut = $valor;
					$pendiente=1;
				}else{
					$campos++;
					switch ($campos) {
						case '2':
							$nom = $valor;
							break;
						case '3':
							$ri = $valor;
							break;
						case '4':
							$co = $valor;
							break;
						case '5':
							$sis = $valor;
							break;
						case '6':
							$cv = $valor;
							break;

						default:
							if(strpos($valor,"/")>0){
								$token++;
								if($token==2){
									$f1=$valor;
								}else{
									$f2=$valor;
								}
							}else{
								$codigo=$valor;
							}
							switch ($campos) {
								case '7':
									$c7 = $valor;
									break;
								case '8':
									$c8 = $valor;
									break;
								case '9':
									$c9 = $valor;
									break;
								case '10':
									$c10 = $valor;
									break;
								case '11':
									$c11 = $valor;
									break;
								case '12':
									$c12 = $valor;
									break;
								case '13':
									if(strpos($valor,"/")>1){
										$c13 = null;
									}else{
										$c13 = $valor;
									}
									break;
							}
							break;
					}

				}
			}
			if($pendiente==1 && is_numeric(substr($ri,0,1)) ){
				if(self::$gintdebug){
					echo "<br>Insertando Trabajador AFP";
				}
						if($ri=="0" || $ri=="1"){
							//Tipo Independiente
							//ajustar campos
							$ri=$co;
							$co=$sis;
							$sis=$cv;
							$cv=$c7;
							if($c8==0){
								$codigo=$c8;
								$c11="";
								$c12=$c8;
								$f1="";
								$f2="";
							}
							if( !is_numeric($c7)){
								$c7=$cv;
								$cv="";
								$c8="";
							}
							if(strpos($c9,"/")>1 && strpos($c10,"/")>1){
								//Con Fechas
								$f1=$c9;
								$f2=$c10;
							}elseif (strpos($c9,"/")>1) {
								switch ($codigo) {
									case '2':
										# Asignando la fecha a Término
										$f1="";
										$f2=$c9;
										break;

									case '6':
										# Asignando la fecha a Inicio
										$f1=$c9;
										$f2="";
										break;
								}
							}else{
								$c9="";
								$c10="";
								$c11="";
							}

						}
						//inserta valores
						if(strlen(trim($c13))>0 && strlen(trim($f2))>0){
							//Todos los campos completos
							if(self::$gintdebug){
								echo "FIN Todos los campos<br>";
							}
							$sql = "INSERT INTO afp_trabajador (id_afp_trabajador, afp_empresa_id, rut, nombre, remuneracion_imponible, cotizacion_obligatoria, sis, apv, contrato, deposito_convenido, deposito_cta_ahorro, remuneracion_imponible_cesantia, cotizacion_afiliado, cotizacion_empleador, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							if($ri=="0" || $ri=="1"){
								$valores=array(0, $afpID, $rut, $nom, $ri, $co, $sis, $cv, $c7, $c8, $c9, $c10, $c11, $c12, $codigo, $f1, $f2);
							}else{
								$valores=array(0, $afpID, $rut, $nom, $ri, $co, $sis, $cv, $c7, $c8, $c9, $c10, $c11, $c12, $codigo, $f1, $f2);
							}
						}
						if(strlen(trim($c13))>0 && strlen(trim($f2))==0){
							//con Contrato pero Sin Fecha de Inicio
							if(self::$gintdebug){
								echo "FIN con Contrato pero Sin Fecha de Inicio<br>";
							}
							$sql = "INSERT INTO afp_trabajador (id_afp_trabajador, afp_empresa_id, rut, nombre, remuneracion_imponible, cotizacion_obligatoria, sis, apv, contrato, deposito_convenido, deposito_cta_ahorro, remuneracion_imponible_cesantia, cotizacion_afiliado, cotizacion_empleador, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores = array(0, $afpID, $rut, $nom, $ri, $co, $sis, $cv, $c7, $c8, $c9, $c10, $c11, $c12, $codigo, '', $f1);
						}
						if(strlen(trim($c13))==0 && strlen(trim($f2))>0){
							//Sin Contrato
							if(self::$gintdebug){
								echo "FIN Sin Contrato<br>";
							}
							$sql = "INSERT INTO afp_trabajador (id_afp_trabajador, afp_empresa_id, rut, nombre, remuneracion_imponible, cotizacion_obligatoria, sis, apv, contrato, deposito_convenido, deposito_cta_ahorro, remuneracion_imponible_cesantia, cotizacion_afiliado, cotizacion_empleador, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores=array(0, $afpID, $rut, $nom, $ri, $co, $sis, $cv, '', $c7, $c8, $c9, $c10, $c11, $c12, $f1, $f2);
						}
						if(strlen(trim($c13))==0 && strlen(trim($f2))==0){
							//Sin Contrato y Sin Fecha de Inicio
							if(self::$gintdebug){
								echo "FIN Sin Contrato y Sin Fecha de Inicio<br>";
							}
							$sql = "INSERT INTO afp_trabajador (id_afp_trabajador, afp_empresa_id, rut, nombre, remuneracion_imponible, cotizacion_obligatoria, sis, apv, contrato, deposito_convenido, deposito_cta_ahorro, remuneracion_imponible_cesantia, cotizacion_afiliado, cotizacion_empleador, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores=array(0, $afpID, $rut, $nom, $ri, $co, $sis, $cv, '', $c7, $c8, $c9, $c10, $c11, $c12, '', $f1);
						}
						if(strlen(trim($nom))>0){
							if(self::$gintdebug){
								echo $sql."<br>";
								print_r($valores);
							}
							$basura = self::insertaSQL($sql,$valores);
						}
			}
	    }
	}

	static private function putISAPREempresa($datos){
		$obj = $datos;
		$id = 0;
		if(count($obj)==8 && $obj["rut"]!="" && $obj["periodo"]!=""){
			$sql = "INSERT INTO isapre_empresa (id, IdDocumento, archivo, nombre, folio, rut, empresa, periodo, tipo, fechaPago, login_user) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
			$valores = array(0, self::$gobjDocumento->IdDocumento, $obj["archivo"], $obj["nombre"], "'".$obj["folio"]."'", $obj["rut"], $obj["empresa"], $obj["periodo"], $obj["tipo"], $obj["fechaPago"], 'Alfonso');
		    if(self::$gintdebug){
		    	echo "<br>".$sql;
		    	print_r($valores);
		    }
			$result = DB::insert($sql,$valores);
		    if($result==1){
				$sql = "SELECT max(id) AS id FROM isapre_empresa WHERE IdDocumento=:doc AND rut=:rut AND periodo=:periodo limit 1";
			    $result = DB::select($sql, ['doc'=>self::$gobjDocumento->IdDocumento, 'rut'=>$obj["rut"], 'periodo'=>$obj["periodo"] ]);
			    if(count($result)>0){
			    	$maxRecords = count($result);
			    	foreach ($result as $row) {
					    $id = $row->id;
					}
			    }
		    }
	    }
	    if(self::$gintdebug){
	    	echo "<br>ID: ".$id;
	    }
	    return $id;
	}

	static private function putISAPREtrabajadores($isapreID,$tra){
	    if(self::$gintdebug){
			echo "<pre>";
			print_r($tra);
			echo "</pre>";
		}
		if($isapreID>0){
			//Barre todos los trabajadores
			$i = 0;
			$token=0;
			$pendiente=0;
			$codigo="";
			$fun="";
			$campos=0;
			$cpac="";
			foreach ($tra as $valor) {
				$i++;
				if(strpos($valor,"-")>0){
					if($token>0){
						//inserta valores
						if(strlen(trim($f2))>0){
							//Todos los campos completos
							if(self::$gintdebug){
								echo "Todos los campos<br>";
							}
							$sql = "INSERT INTO isapre_trabajador (id_isapre_trabajador, isapre_empresa_id, rut, fun, nombre, renta_imponible, cotizacion, ley, cotizacion_adicional, otros, cotiz_a_pagar, cotiz_pactada, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores=array(0, $isapreID, $rut, $fun, $nom, $ri, $co, $ley, $ca, $ot, $cpag, $cpac, $codigo, $f1, $f2);
						}
						if(strlen(trim($f2))==0){
							//con Contrato pero Sin Fecha de Inicio
							if(self::$gintdebug){
								echo "Sin Fecha de Inicio<br>";
							}
							$sql = "INSERT INTO isapre_trabajador (id_isapre_trabajador, isapre_empresa_id, rut, fun, nombre, renta_imponible, cotizacion, ley, cotizacion_adicional, otros, cotiz_a_pagar, cotiz_pactada, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores=array(0, $isapreID, $rut, $fun, $nom, $ri, $co, $ley, $ca, $ot, $cpag, $cpac, $codigo, '', $f1);
						}
						if(strlen(trim($nom))>0){
							if(self::$gintdebug){
								echo $sql."<br>";
								print_r($valores);
							}
							$basura = self::insertaSQL($sql,$valores);
						}
						$pendiente=0;
					}
					$token=1;
					$campos=1;
					$f1 = $f2 = null;
					$rut = $valor;
					$pendiente=1;
				}else{
					$campos++;
					switch ($campos) {
						case '2':
							if(is_numeric($valor)){
								$fun = $valor;
							}else{
								$nom = $valor;
								$campos++;
							}
							break;
						case '3':
							$nom = $valor;
							break;
						case '4':
							$ri = $valor;
							break;
						case '5':
							$co = $valor;
							break;
						case '6':
							$ley = $valor;
							break;
						case '7':
							$ca = $valor;
							break;
						case '8':
							$ot = $valor;
							break;
						case '9':
							$cpag = $valor;
							break;
						case '10':
							$cpac = $valor;
							break;
						default:
							if(strpos($valor,"/")>0){
								$token++;
								if($token==2){
									$f1=$valor;
								}else{
									$f2=$valor;
								}
							}else{
								$codigo=$valor;
							}
							break;
					}

				}
			}
			if($pendiente==1){
						if(strlen(trim($f2))>0){
							//Todos los campos completos
							if(self::$gintdebug){
								echo "Todos los campos<br>";
							}
							$sql = "INSERT INTO isapre_trabajador (id_isapre_trabajador, isapre_empresa_id, rut, fun, nombre, renta_imponible, cotizacion, ley, cotizacion_adicional, otros, cotiz_a_pagar, cotiz_pactada, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores=array(0, $isapreID, $rut, $fun, $nom, $ri, $co, $ley, $ca, $ot, $cpag, $cpac, $codigo, $f1, $f2);
						}
						if(strlen(trim($f2))==0){
							//con Contrato pero Sin Fecha de Inicio
							if(self::$gintdebug){
								echo "Sin Fecha de Inicio<br>";
							}
							$sql = "INSERT INTO isapre_trabajador (id_isapre_trabajador, isapre_empresa_id, rut, fun, nombre, renta_imponible, cotizacion, ley, cotizacion_adicional, otros, cotiz_a_pagar, cotiz_pactada, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores=array(0, $isapreID, $rut, $fun, $nom, $ri, $co, $ley, $ca, $ot, $cpag, $cpac, $codigo, '', $f1);
						}
						if(strlen(trim($nom))>0){
							if(self::$gintdebug){
								echo $sql."<br>";
								print_r($valores);
							}
							$basura = self::insertaSQL($sql,$valores);
						}
			}
	    }else{
	    	echo "isapreID en CEROS!<br>";
	    }
	}

	static private function putMUTUALtrabajadores($mutualID,$tra){
		if($mutualID>0){
			//Barre todos los trabajadores
			$i = 0;
			$token=0;
			$pendiente=0;
			$campos=0;
			$codigo ="";
			$rem=0;
			$f2="";
			foreach ($tra as $valor) {
				$i++;
				if(substr($valor,0,7)=="TOTALES"){
					break;
				}
				if(strpos($valor,"-")>0){
					if($token>0 && is_numeric(substr($rem,0,1))){
						//inserta valores
						if(strlen(trim($f2))>0){
							//Todos los campos completos
							if(self::$gintdebug){
								echo "Todos los campos<br>";
							}
							$sql = "INSERT INTO mutual_trabajador (id_mutual_trabajador, mutual_empresa_id, rut, nombre, remuneracion, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?)";
							$valores=array(0, $mutualID, $rut, $nom, $rem, $codigo, $f1, $f2);
						}
						if(strlen(trim($f2))==0){
							//con Contrato pero Sin Fecha de Inicio
							if(self::$gintdebug){
								echo "Sin Fecha de Inicio<br>";
							}
							$sql = "INSERT INTO mutual_trabajador (id_mutual_trabajador, mutual_empresa_id, rut, nombre, remuneracion, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?)";
							$valores=array(0, $mutualID, $rut, $nom, $rem, $codigo, '', $f1);
						}
						if(self::$gintdebug){
							echo $sql;
							print_r($valores);
						}
						$basura = self::insertaSQL($sql,$valores);
						$pendiente=0;
					}
					$token=1;
					$campos=1;
					$f1 = $f2 = null;
					$rut = $valor;
					$pendiente=1;
					$codigo ="";
					$rem=0;
				}else{
					$campos++;
					switch ($campos) {
						case '2':
							$nom = $valor;
							break;
						case '3':
							$nom .= " ".$valor;
							break;
						case '4':
							if(is_numeric(substr($valor,0,1))){
								$rem = $valor;
								$campos=5;
							}else{
								$nom .= " ".$valor;
							}
							break;
						case '5':
							$rem = $valor;
							break;
						default:
							if(strpos($valor,"/")>0){
								$token++;
								if($token==2){
									$f1=$valor;
								}else{
									$f2=$valor;
								}
							}else{
								if(strlen($codigo)==0){
									$codigo=$valor;
								}
							}
							break;
					}

				}
			}
			if($pendiente==1 && is_numeric(substr($rem,0,1))){
						if(strlen(trim($f2))>0){
							//Todos los campos completos
							if(self::$gintdebug){
								echo "Todos los campos<br>";
							}
							$sql = "INSERT INTO mutual_trabajador (id_mutual_trabajador, mutual_empresa_id, rut, nombre, remuneracion, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?)";
							$valores=array(0, $mutualID, $rut, $nom, $rem, $codigo, $f1, $f2);
						}
						if(strlen(trim($f2))==0){
							//con Contrato pero Sin Fecha de Inicio
							if(self::$gintdebug){
								echo "Sin Fecha de Inicio<br>";
							}
							$sql = "INSERT INTO mutual_trabajador (id_mutual_trabajador, mutual_empresa_id, rut, nombre, remuneracion, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?)";
							$valores=array(0,$mutualID,$rut,$nom,$rem,$codigo,'',$f1);
						}
						if(self::$gintdebug){
							echo $sql;
							print_r($valores);
						}
						$basura = self::insertaSQL($sql,$valores);
			}
	    }
	}

	static private function putCAJAtrabajadores($cajaID,$tra){
		if($cajaID>0){
			//Barre todos los trabajadores
			$i = 0;
			$token=0;
			$pendiente=0;
			foreach ($tra as $valor) {
				$i++;
				if(strpos($valor,"-")>0){
					if($token>0){
						//inserta valores
						$sql = "";
						if(strlen(trim($f2))>0){
							//Todos los campos completos
							if(self::$gintdebug){
								echo "Todos los campos<br>";
							}
							$sql = "INSERT INTO caja_trabajador (id_caja_trabajador, caja_empresa_id, rut, nombre, afiliado_isapre, remuneracion, cotizacion, dias_trabajados, sim, invl, mat, monto, codigo_tramo, pago_af_retoractiva, reintegro, codigo, fecha_inicio, fecha_termino,rut_ent_pag_subs) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores=array(0, self::$cajaID, self::$rut, $nom, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $f1, $f2, $c17);
						}
						if(strlen(trim($f2))==0){
							//con Contrato pero Sin Fecha de Inicio
							if(self::$gintdebug){
								echo "Sin Fecha de Inicio<br>";
							}
							$sql = "INSERT INTO caja_trabajador (id_caja_trabajador, caja_empresa_id, rut, nombre, afiliado_isapre, remuneracion, cotizacion, dias_trabajados, sim, invl, mat, monto, codigo_tramo, pago_af_retoractiva, reintegro, codigo, fecha_inicio, fecha_termino,rut_ent_pag_subs) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores=array(0, self::$cajaID, self::$rut, $nom, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, '', $f1, $c17);
						}
						if(self::$gintdebug){
							echo "<br>".$sql;
							print_r($valores);
						}
						$basura = self::insertaSQL($sql,$valores);
						$pendiente=0;
					}
					$token=1;
					$campos=1;
					$f1 = $f2 = $c17 = null;
					self::$rut = $valor;
					$pendiente=1;
				}else{
					$campos++;
					switch ($campos) {
						case '2':
							$nom = $valor;
							break;
						case '3':
							$c3 = " ".$valor;
							break;
						case '4':
							$c4 = " ".$valor;
							break;
						case '5':
							$c5 = $valor;
							break;
						case '6':
							$c6 = $valor;
							break;
						case '7':
							$c7 = $valor;
							break;
						case '8':
							$c8 = $valor;
							break;
						case '9':
							$c9 = $valor;
							break;
						case '10':
							$c10 = $valor;
							break;
						case '11':
							$c11 = $valor;
							break;
						case '12':
							$c12 = $valor;
							break;
						case '13':
							$c13 = $valor;
							break;
						case '14':
							$c14 = $valor;
							break;
						default:
							if(strpos($valor,"/")>0){
								$token++;
								if($token==2){
									$f1=$valor;
								}else{
									$f2=$valor;
								}
							}else{
								$c17=$valor;
							}

							break;
					}

				}
			}
			if($pendiente==1){
						//inserta valores
						$sql = "";
						if(strlen(trim($f2))>0){
							//Todos los campos completos
							if(self::$gintdebug){
								echo "Todos los campos<br>";
							}
							$sql = "INSERT INTO caja_trabajador (id_caja_trabajador, caja_empresa_id, rut, nombre, afiliado_isapre, remuneracion, cotizacion, dias_trabajados, sim, invl, mat, monto, codigo_tramo, pago_af_retoractiva, reintegro, codigo, fecha_inicio, fecha_termino,rut_ent_pag_subs) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores=array(0, self::$cajaID, self::$rut, $nom, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $f1, $f2, $c17);
						}
						if(strlen(trim($f2))==0){
							//con Contrato pero Sin Fecha de Inicio
							if(self::$gintdebug){
								echo "Sin Fecha de Inicio<br>";
							}
							$sql = "INSERT INTO caja_trabajador (id_caja_trabajador, caja_empresa_id, rut, nombre, afiliado_isapre, remuneracion, cotizacion, dias_trabajados, sim, invl, mat, monto, codigo_tramo, pago_af_retoractiva, reintegro, codigo, fecha_inicio, fecha_termino,rut_ent_pag_subs) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
							$valores=array(0, self::$cajaID, self::$rut, $nom, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, '', $f1, $c17);
						}
						if(self::$gintdebug){
							echo "<br>".$sql;
							print_r($valores);
						}
						$basura = self::insertaSQL($sql,$valores);
			}
	    }
	}

	static private function putFONASAtrabajadores($fonasaID,$tra){
		//echo "FonasaID ".$fonasaID;
		if($fonasaID>0){
			//Barre todos los trabajadores
			$i = 0;
			$token=0;
			$pendiente=0;
			$campos=1;
			foreach ($tra as $valor) {
				$i++;
				//echo "<hr>".$i."<br>".$valor."<br>";
				if(strpos($valor,"-")>0){
					if($token>0){
						//inserta valores
						switch ($cod) {
							case '0':
								$f1 = $f2 = "";
								break;
							case '2':
								$f2 = $f1;
								$f1 = "";
								break;
						}
						$sql = "INSERT INTO fonasa_trabajador (id_fonasa_trabajador, fonasa_empresa_id, rut, nombre, dias, entidad, remuneracion, cotizacion, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
						$valores=array(0, trim($fonasaID), trim($rut), trim($nom), $dias, $ent, $rem, $cot, $cod, $f1, $f2);
						if(self::$gintdebug){
							echo "<br>".$sql;
							print_r($valores);
						}
						$basura = self::insertaSQL($sql,$valores);
						$pendiente=0;
					}
					$token=1;
					$campos=1;
					$f1 = $f2 = null;
					$rut = $valor;
					$pendiente=1;
				}else{
					$campos++;
					switch ($campos) {
						case '2':
							$nom = $valor;
							break;
						case '3':
							$nom .= " ".$valor;
							break;
						case '4':
							$basura = substr(trim($valor), 0, 1);
							if(is_numeric($basura)){
								$token=1;
								$dias = $valor;
								$campos=5;
							}else{
								$nom .= " ".$valor;
							}
							break;
						case '5':
							$dias = $valor;
							break;
						case '6':
							$ent = $valor;
							break;
						case '7':
							$rem = $valor;
							break;
						case '8':
							$cot = $valor;
							break;
						case '9':
							$cod = $valor;
							break;
						case '10':
							$f1 = $valor;
							break;
						case '11':
							$f1 .= "/".$valor;
							break;
						case '12':
							$f1 .= "/".$valor;
							break;
						case '13':
							$f2 = $valor;
							break;
						case '14':
							$f2 .= "/".$valor;
							break;
						case '15':
							$f2 .= "/".$valor;
							break;
						case '16':
							break;
					}
				}
			}
			if($pendiente==1){
						//inserta valores
						switch ($cod) {
							case '0':
								$f1 = $f2 = "";
								break;
							case '2':
								$f2 = $f1;
								$f1 = "";
								break;
						}
						$sql = "INSERT INTO fonasa_trabajador (id_fonasa_trabajador, fonasa_empresa_id, rut, nombre, dias, entidad, remuneracion, cotizacion, codigo, fecha_inicio, fecha_termino) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
						$valores=array(0, $fonasaID, trim($rut), trim($nom), $dias, $ent, $rem, $cot, $cod, $f1, "'".$f2."'");
						if(self::$gintdebug){
							echo "<br>".$sql;
							print_r($valores);
						}
						$basura = self::insertaSQL($sql,$valores);
			}
	    }
	}

	static private function extrae($texto, $ini, $fin, $excluye){
		// $excluye = 1, elimina el string $ini del resultado
		// 			= 2, elimina letras del resultado
		//			= 3, elimina números del resultado
		//			= 5, Busca con UPPERCASE
		if($excluye==5){
			$texto_orig = $texto;
			$texto = strtoupper($texto);
			$ini = strtoupper($ini);
			$fin = strtoupper($fin);
		}
		$posI = strpos($texto, $ini);
		if ($posI>0){
			if($excluye>0){
				$posI = $posI + strlen($ini);
			}
			$newT=substr($texto, $posI);
			//echo "<br>NewT= ".$newT;
			$posF = strpos($newT, $fin);
			if ($posF>0){
				if($excluye == 2){
					return preg_replace("/[^0-9,.]/", "", substr($newT, 0, $posF));
				}
				if($excluye == 3){
					return preg_replace("/[^a-zA-Z ]/", "", substr($newT, 0, $posF));
				}
				if($excluye == 4){
					$posF++;
					$posF++;
				}
				if($excluye == 5){
					$newT = substr($texto_orig, $posI);
				}
				return substr($newT, 0, $posF);
			}else{
				return "Fin No encontrado";
			}
		}else{
			return "Inicio No encontrado";
		}
	}

	static private function extraeBloque($texto,$ini,$veces){
		// devuelve bloques donde contiene el string de inicio
		$posI = strpos($texto, $ini);
		if ($posI>0){
			$newT=substr($texto, $posI);
			$bloques = array();
			$v = 0;
			while (strpos($newT, $ini,1) > 0){
				$v++;
				$posF = strpos($newT, $ini,1);
				if($veces>0){
					$posFn = strpos($newT, $ini, ($posF+1));
					if($posFn>0){
						$posF = $posFn;
					}
				}
				$bloque = substr($newT, 0, $posF);
				$bloques[] = array('bloque' => $bloque);
				$newT=substr($newT, $posF);
			}
			$bloques[] = array('bloque' => $newT);
			return $bloques;
		}else{
			return array();
		}
	}

	static private function parseaBloque($bloques, $indice){
		// Extrae los datos relevantes del bloque
		global $text, $sFin;
		$errorMsg = "";
		//$str = $bloques["bloque"];
		$str = $bloques;
		$buscar="\|\|AFP";
		$ini = preg_match("/".$buscar."/",$str);
		if( preg_match("/".$buscar."/",$str)==1 && preg_match("/Identificación del Empleador/", $str)==1 ){
			//Procesa AFP
			$nom = self::extrae($str,"||","||Número",1);
			//echo "<hr>Nombre: ".$nom."<br>";
			$folio = self::extrae($str,"Folio:","|",1);
			//echo "Folio: ".$folio."<br>";
			$emp = self::extrae($str,"Social|RUT|","|Dirección",1);
			$emp = explode("|", $emp);
			switch ( count($emp) ) {
				case 2:
					$rut = $emp[1];
					$emp = $emp[0];
					break;
				case 4:
					$rut = $emp[2];
					$emp = $emp[1];
					break;
				default:
					$rut="";
			}
			if(strlen(trim($rut))>0){
				//echo "RUT: ".$rut."<br>";
				//echo "Empresa: ".$emp."<br>";
				$periodo = self::extrae($str,"Periodo|","|Retroactivo",1);
				//echo "Periodo: ".$periodo."<br>";
				$tmp = explode("|", $str);
				$nada = self::fechaDePago($tmp);
				//$fpago = self::extrae($str,"TOTAL A PAGAR A","PreviRed.com",5);
				//echo "Tipo: ".$fpago[6]."<br>";
				//echo "Fecha de Pago: ".$fpago[8]."<br>";
				$datos = array('archivo' => self::$filename, 'nombre' => $nom, 'folio' => $folio, 'rut' => $rut, 'empresa' => $emp, 'periodo' => $periodo, 'tipo' => self::$tipo, 'fechaPago' => self::$fechaPago);
				self::$afpID = self::putAFPempresa($datos);
				//echo "<br>afpID=".self::$afpID;
			}
		}

		$buscar="Cod.\|Fecha Inicio";
		$ini = preg_match("/".$buscar."/",$str);
		if( preg_match("/".$buscar."/",$str)==1 ){
			//			$primerRUT = self::extrae($str,"Término","TOTALES",1);
			//			echo "<br>Primer: ".$primerRUT;

			//			$tra = self::extrae($str,"Cod.|Fecha Inicio|Fecha","TOTALES",1);
			//			$tra = explode("|", $tra);
			$tra=explode("|", $str);

			if(self::$gintdebug){
				echo "<br>AFP Trabajadores";
			}
			//print_r($tra);
			$afpT  = 0;
			if(self::$afpID>0){
				$afpT  = self::putAFPtrabajadores(self::$afpID,$tra);
			}
			//$tra = extraeTrabajadoresAFP($string);
		}else{
			//echo "<br>Cod. no encontrado...";
		}
	}

	static private function extraeTrabajadoresAFP($textoT){
		//global $text;
		$match = "FechaTérmino";
		$posI = strpos($textoT, $match);
		if ($posI>0){
			$posI = $posI + strlen("FechaTérmino");
			$newT=substr($textoT, $posI);
			$posF = stripos($newT, "TOTAL");
			if ($posF>0){
				$tmp = substr($newT, 0, $posF);
			$claves = preg_split("/[\s,]+/", trim($tmp));
			$cont="";
			$fecI="";
			$fecT="";

			$rem="";
			$cotO="";
			$sis="";
			$cotV="";
			$depC="";
			$depA="";
			$remI="";
			$cotA ="";
			$cotE="";
			$cod="";
			if(isset($_GET['debug'])){
				echo "<pre>";
				print_r($claves);
				echo "</pre>";
			}
			return;
				for ($i= 0; $i <count($claves); $i++){
					if (strpos($claves[$i], "-")>0){
						//Checa si tiene un guión; solo el RUT tiene guión
						if($i>1){
							//Almacena los resultados
							if(empty($cam2) and empty($cam3)){
								//solo Fecha de Término
								$fecT = $cam1;
							}else{
								if(!empty($cam3)){
									//Todos llenos
									$fecT = $cam3;
									$fecI = $cam2;
									$cont = $cam1;
								}else{
									$fecT = $cam2;
									$fecI = $cam1;
								}
							}
							$tra[] = array('rut' => $rut, 'nombre' => $nom, 'Remuneracion' => $rem, 'CotObligatoria' => $cotO, 'sis' => $sis, 'CotVoluntaria' => $cotV, 'Contrato' => $cont, 'DepositoConvenido' => $depC, 'DepositoAhorro' => $depA, 'RemuneracionImponible' => $remI, 'CotizacionAfiliado' => $cotA, 'CotizacionEmpleador' => $cotE, 'Cod' => $cod, 'FechaInicio' => $fecI, 'FechaTermino' => $fecT);
						}
						$rut = trim($claves[$i]);
						$cont="";
						$fecI="";
						$fecT="";
						$nom = "";
						$cam1 = "";
						$cam2 = "";
						$cam3 = "";
						$esperaNumero = 0;
						$x = 0;
					}else{
						if(is_numeric($claves[$i])){
							$esperaNumero = 1;
							$x++;
							switch ($x) {
								case 1:
									$rem = trim($claves[$i]);
									break;
								case 2:
									$cotO = trim($claves[$i]);
									break;
								case 3:
									$sis = trim($claves[$i]);
									break;
								case 4:
									$cotV = trim($claves[$i]);
									break;
								case 5:
									$depC = trim($claves[$i]);
									break;
								case 6:
									$depA = trim($claves[$i]);
									break;
								case 7:
									$remI = trim($claves[$i]);
									break;
								case 8:
									$cotA = trim($claves[$i]);
									break;
								case 9:
									$cotE = trim($claves[$i]);
									break;
								case 10:
									$cod = trim($claves[$i]);
									break;
								case 11:
									$otr1 = trim($claves[$i]);
									break;
								case 12:
									$otr2 = trim($claves[$i]);
									break;
								case 13:
									$otr3 = trim($claves[$i]);
									break;
							}
						}else{
							if($esperaNumero == 0){
								$nom = $nom." ".trim($claves[$i]);
								$y=0;
							}else{
								$y++;
								switch ($y) {
									case 1:
										$cam1 = trim($claves[$i]);
										break;
									case 2:
										$cam2 = trim($claves[$i]);
										break;
									case 3:
										$cam3 = trim($claves[$i]);
										break;
								}
							}
						}
					}
				}
				if(empty($cam2) and empty($cam3)){
					//solo Fecha de Término
					$fecT = $cam1;
				}else{
					if(!empty($cam3)){
						//Todos llenos
						$fecT = $cam3;
						$fecI = $cam2;
						$cont = $cam1;
					}else{
						$fecT = $cam2;
						$fecI = $cam1;
					}
				}
				$tra[] = array('rut' => $rut, 'nombre' => $nom, 'Remuneracion' => $rem, 'CotObligatoria' => $cotO, 'sis' => $sis, 'CotVoluntaria' => $cotV, 'Contrato' => $cont, 'DepositoConvenido' => $depC, 'DepositoAhorro' => $depA, 'RemuneracionImponible' => $remI, 'CotizacionAfiliado' => $cotA, 'CotizacionEmpleador' => $cotE, 'Cod' => $cod, 'FechaInicio' => $fecI, 'FechaTermino' => $fecT);
				return $tra;
			}else{
				return "";
			}
		}else{
			return "";
		}
	}

	static private function parseaISAPRE($bloques, $indice, $param){
		$tmp = $bloques;
		if(strpos($tmp, "Mutual")>0){
			$tmp=substr($tmp, 0,strpos($tmp, "Mutual"));
		}
		//echo "<hr>".$indice." - ";
		//print_r($tmp);
		//echo $GLOBALS['max']."<br>";
		if(strpos($tmp, "Participante")>0 && $indice<>$param['max']-1){
			$periodo = self::extrae($tmp,"Participante|","|N°",1);
			$tmp2 = explode("|", $tmp);
			$nada = self::fechaDePago($tmp2);
			///echo "<br>Periodo: ".$periodo."<br>";
			$datos = array('archivo' => self::$filename, 'nombre' => 'ISAPRE '.self::$isapre, 'folio' => self::$folio, 'rut' => self::$rut, 'empresa' => self::$emp, 'periodo' => $periodo, 'tipo' => self::$tipo, 'fechaPago' => self::$fechaPago);
			self::$isapreID = self::putISAPREempresa($datos);
		}
		if(strpos($tmp, "jurídicamente")<30 && strpos($tmp, "jurídicamente")>0){
			//echo "salto jurídicamente<br>";
			return;
		}

		if(strpos($tmp, "ASIGNACIÓN")>0){
			//echo "salto jurídicamente<br>";
			return;
		}

		if(strpos($tmp, "Fecha Término")>0){
			//echo "salto Término<br>";
			//Procesa Trabajadores ISAPRE
			$trab = substr($tmp,strpos($tmp, "Fecha Término||")+strlen("Fecha Término||"),strpos($tmp, "|TOTALES ")-(strpos($tmp, "Fecha Término||")+strlen("Fecha Término||")));
			$tra = explode("|", $trab);
			if(isset($_GET['debug'])){
				echo "<hr>Trabajadores<hr>";
				echo "<pre>";
				print_r($tra);
				echo "</pre><hr>";
			}
			$basura = self::putISAPREtrabajadores(self::$isapreID,$tra);
			return;
		}
		if(strpos($tmp, "Número")>0){
			//echo "salto Número<br>";
			//Procesa Encabezados ISAPRE
			self::$isapre = substr($tmp,0, strpos($tmp, "||Número"));
			self::$folio = self::extrae($tmp,"Folio:","||",1);
			///echo "<hr>Nombre: ISAPRE ".self::$isapre."<br>Número: ".self::$folio."<br>";
			self::$emp = self::extrae($tmp,"Econom.|","|",1);
			self::$rut = self::extrae($tmp,self::$emp."|","|",1);
			///echo "RUT: ".self::$rut."<br>";
			///echo "Empresa: ".self::$emp."<br>";
			return;
		}
		if(strpos($tmp, "Mutual")>0){
			return;
		}
		return;
	}

	static private function parseaMutual($bloques, $indice){
		$tmp = $bloques;
		if(isset($_GET['debug'])){
				echo "<hr>Datos Mutual $indice<hr>";
				echo $tmp;
				echo "<hr>";
			}
		if(strpos($tmp, "Econom")>0 ){
			//Encabezado
			$mutual = substr($tmp,0, strpos($tmp, "||Número"));
			///echo "**".$mutual."**";
			$num = self::extrae($tmp,"Folio:","|",1);
			if($mutual==" del Trabajo IST"){
				///echo "Cambiando...<br>";
				$mutual = "Instituto".$mutual;
			}else{
				$mutual = "Mutual".$mutual;
			}
			///echo "<hr>Nombre: Mutual $mutual<br>Número: ".$num."<br>";
			$emp = self::extrae($tmp,"Econom.|","|",1);
			$correo = self::extrae($tmp,$emp."|","|",1);
			$rut = self::extrae($tmp,$correo."|","|",1);
			///echo "RUT: ".$rut;
			///echo "<br>Empresa: ".$emp."<br>";
			$periodo = self::extrae($tmp,"Contrato|","|",1);
			//$fechaPago = self::extrae($tmp,"Pago Electrónico||","|",1);
			$fpago="";
			$porcentaje = self::extrae($tmp,"TASA COTIZACIÓN|","|Detalle",1);
			$tmp2=explode("|", $tmp);
			$nada = self::fechaDePago($tmp2);

			///echo "Periodo: ".$periodo;
			///echo "<br>Porcentaje: ".$porcentaje;
			///echo "<br>Fecha de Pago: ".$fechaPago;
			if(substr($periodo, 0,8)!="Contrato" && substr($porcentaje, 0,4)!="TASA" && is_numeric(substr(trim($porcentaje), 0,1))){
				$sql = "INSERT INTO mutual_empresa (id, IdDocumento, archivo, nombre, folio, rut, empresa, porcentaje, periodo, tipo, fechaPago) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
				$valores=array(0, self::$gobjDocumento->IdDocumento, self::$filename, $mutual, $num, $rut, $emp, $porcentaje, $periodo, self::$tipo, self::$fechaPago);
				if(self::$gintdebug){
					echo "<br>".$sql;
					print_r($valores);
				}
				$result=DB::insert($sql,$valores);
				self::$mutualID = 0;
			    if($result==1){
					$sql = "SELECT max(id) AS id FROM mutual_empresa WHERE IdDocumento=:doc AND rut=:rut AND periodo=:periodo limit 1";
				    $result = DB::select($sql, ['doc'=>self::$gobjDocumento->IdDocumento, 'rut'=>$rut, 'periodo'=>$periodo ]);
				    ///print_r($result);
				    if(count($result)>0){
				    	$maxRecords = count($result);
				    	foreach ($result as $row) {
						    self::$mutualID = $row->id;
						}
				    }
			    }
			}
			//return;
		}
		if(strpos($tmp, "Fecha Termino")>0 && self::$mutualID != 0){
			//echo "salto Término<br>";
			//Procesa Trabajadores Mutual
			$trab = substr($tmp,strpos($tmp, "Fecha Termino||")+strlen("Fecha Termino||"),(strpos($tmp, "TOTALES ")-(strpos($tmp, "Fecha Termino||")-3+strlen("TOTALES "))));
			$tra = explode("|", $trab);
			if(isset($_GET['debug'])){
				echo "<hr>Trabajadores Mutual<hr>";
				echo "<pre>";
				print_r($tra);
				echo "</pre><hr>";
			}
			$basura = self::putMUTUALtrabajadores(self::$mutualID,$tra);
			return;
		}
	}

	static private function parseaMutualIPS($bloques, $indice){
		$tmp = $bloques;
		//if($indice==0){
			//Nos saltamos el primer bloque
		//	return;
		//}

		if(strpos($tmp, " serie resumen")>0){
			$temp = explode("|", $tmp);
			$i = 0;
			$token=0;
			$start=-1;
			$rutE=0;
			foreach ($temp as $llave => $valor) {
				$i++;
				switch ($valor) {
					case 'N° serie resumen':
						$start=$i+1;
						$token="folio";
						break;
					case 'REMUNERACION':
						$start=$i+1;
						$token="periodoMes";
						break;
					case 'PreviRed.com':
						$start=$i;
						$token="tipo";
						break;

					default:
						if(strpos($valor, "-")>0 && strpos($valor, ".")>0){
							$start=$i;
							$token="rut";
						}
						break;
				}
				if($start == $i){
					switch ($token) {
						case 'folio':
							self::$folio=$valor;
							$start=-1;
							break;
						case 'rut':
							if($rutE==0){
								self::$rut=$valor;
								$start=$i+1;
								$token="nomE";
							}else{
								self::$rut=$valor;
								$start=$i+1;
								$token="dv";
							}
							break;
						case 'nomE':
							$emp=$valor;
							$start=-1;
							$token="";
							break;
						case 'periodoMes':
							self::$periodo=$valor;
							$start=$i+1;
							$token="periodoAnio";
							break;
						case 'periodoAnio':
							self::$periodo=self::$periodo." ".$valor;
							$start=-1;
							///echo "<hr>Mutual IPS";
							///echo "<br>Periodo: ".self::$periodo;
							break;
						case 'tipo':
							/*
							if(substr($temp[$x-5], 0,5)=="Decla"){
								$tipo=$temp[$x-5];
								self::$fechaPago=$temp[$x-4].", ".$tmp[$x-3];
							}else{
								$tipo=$temp[$x-4];
								self::$fechaPago=$temp[$x-2];
							} */
							for ($z = 1; $z <= 10; $z++){
								if(substr_count($temp[$x-$z],"/")==2){
									//es una fecha
									self::$fechaPago = self::$fechaPago." ".$temp[$x-$z];
								}
								if(substr($temp[$x-$z], 0,5)=="Decla"){
									self::$tipo=$temp[$x-$z];
									break;
								}
								if(substr($temp[$x-$z], 0,5)=="Pago "){
									self::$tipo=$temp[$x-$z];
									break;
								}
							}
							break;
					}
				}
			}
		}elseif( (strpos($tmp, "PreviRed.com")>0) || (strpos($tmp, "PREVIRED")>0) ){
			$temp = explode("|", $tmp);
			$i = 0;
			$token=0;
			$start=-1;
			$rutE=0;
			$tipo="";
			$done=0;
			$fechaPago="";
			foreach ($temp as $llave => $valor) {
				$i++;
				switch ($valor) {
					case 'PreviRed.com':
						$start=$i;
						$token="tipo";
						break;
					case 'PREVIRED':
						$start=$i;
						$token="tipo";
						break;
				}
				if($start == $i){
					switch ($token) {
						case 'tipo':
							for ($z = 1; $z <= 10; $z++){
								if(substr_count($temp[$i-$z],"/")==2){
									//es una fecha
									self::$fechaPago = self::$fechaPago." ".$temp[$i-$z];
								}
								if(substr($temp[$i-$z], 0,5)=="Decla"){
									self::$tipo=$temp[$i-$z];
									break;
								}
								if(substr($temp[$i-$z], 0,5)=="Pago "){
									self::$tipo=$temp[$i-$z];
									break;
								}
							}
							$start=-1;
							if($done==0){
								$done=1;
								$sql = "INSERT INTO ips_empresa (id, IdDocumento, archivo, nombre, folio, rut, empresa, periodo, tipo, fechaPago, login_user) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
								$valores=array(0, self::$gobjDocumento->IdDocumento, self::$filename, 'IPS', self::$folio, self::$rut, self::$emp, self::$periodo, $tipo, self::$fechaPago, 'Alfonso');
								if(self::$gintdebug){
									echo $sql."<br>";
									print_r($valores);
								}
								$basura = self::insertaSQL($sql,$valores);
									$sql = "SELECT max(id) AS id FROM ips_empresa WHERE IdDocumento=:doc AND rut=:rut AND periodo=:periodo limit 1";
								    $result = DB::select($sql, ['doc'=>self::$gobjDocumento->IdDocumento, 'rut'=>self::$rut, 'periodo'=>self::$periodo ]);
								    if(count($result)>0){
								    	$maxRecords = count($result);
								    	foreach ($result as $row) {
										    self::$ipsID = $row->id;
										}
								    }
						    }
							break;
					}
				}
			}
		}
		if(strpos($tmp, "BONIF. ART19 LEY")>0){
			//Procesa Trabajadores IPS
			$ini=strpos($tmp, "BONIF. ART19 LEY");
			$trab = substr($tmp,$ini,(strpos($tmp, "TOTALES")-$ini));
			$tra = explode("|", $trab);
			if(isset($_GET['debug'])){
				echo "<hr>Trabajadores IPS ".self::$ipsID."<hr>";
				echo "<pre>";
				print_r($tra);
				echo "</pre><hr>";
			}
			$basura = self::putIPStrabajadores(self::$ipsID,$tra);
			return;
		}
	}

	static private function putIPStrabajadores($ipsID,$tra){
		if($ipsID>0){
			//Barre todos los trabajadores
			$i = 0;
			$token=0;
			$pendiente=0;
			$dias=0;
			$campos=0;
			$campo="tramo";
			$rut="";
			$sim=0;
			$pen=0;
			foreach ($tra as $valor) {
				$i++;
				if(substr_count($valor,".")>1 && is_numeric(substr($valor,0,1))==true ){
					//Encontre un RUT
					if($token>0 && isset($rem)){
						//inserta valores
						$sql = "INSERT INTO ips_trabajador (id_ips_trabajador, ips_empresa_id, rut, nombre, dias, remuneracion, pension, fonasa, accidentes, desahucio_rem, desahucio_cot, mov_cod, mov_fecha_inicio, mov_fecha_termino, tramo, simple, invalida, maternal, monto, bonificacion) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
						$valores=array(0, $ipsID, $rut, $nom, $dias, $rem, $pen, $fon, $acc, $desR, $desC, $cod, $f1, $f2, $tra, $sim, $inv, $mat, $mon, $bon);
						if(self::$gintdebug){
							echo $sql."<br>";
							print_r($valores);
						}
						$basura = self::insertaSQL($sql,$valores);
						$f1 = $f2 = "";
						$pendiente=0;
					}
					$token=1;
					$campos=1;
					$f1 = $f2 = null;
					$rut = $valor;
					$pendiente=1;
					$campo ="";
					$pen=0;
					$fon=0;
					$acc=0;
					$desR=0;
					$desC=0;
					$cod=0;
					$inv=0;
					$mat=0;
					$mon=0;
					$bon=0;
				}else{
					$campos++;
					switch ($campos) {
						case '2':
							$rut = $rut."-".$valor;
							break;
						case '3':
							$nom = $valor;
							break;
						case '4':
							$dias = $valor;
							break;
						case '5':
							$rem = $valor;
							break;
						case '6':
							$pen = $valor;
							break;
						case '7':
							$fon = $valor;
							break;
						case '8':
							$acc = $valor;
							break;
						case '9':
							$desR = $valor;
							break;
						case '10':
							$desC = $valor;
							break;
						case '11':
							$cod = $valor;
							$campo="tramo";
							break;
						default:
							if(strpos($valor,"/")>0){
								$token++;
								if($token==2){
									$f1=$valor;
								}else{
									$f2=$valor;
								}
								$campo="tramo";
							}else{
								switch ($campo) {
									case 'tramo':
										$tra=$valor;
										$campo="simple";
										break;
									case 'simple':
										$sim=$valor;
										$campo="invalida";
										break;
									case 'invalida':
										$inv=$valor;
										$campo="maternal";
										break;
									case 'maternal':
										$mat=$valor;
										$campo="monto";
										break;
									case 'monto':
										$mon=$valor;
										$campo="bonif";
										break;
									case 'bonif':
										$bon=$valor;
										$campo="";
										break;
								}
							}
							break;
					}

				}
			}
			if($pendiente==1){
						$sql = "INSERT INTO ips_trabajador (id_ips_trabajador, ips_empresa_id, rut, nombre, dias, remuneracion, pension, fonasa, accidentes, desahucio_rem, desahucio_cot, mov_cod, mov_fecha_inicio, mov_fecha_termino, tramo, simple, invalida, maternal, monto, bonificacion) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
						$valores=array(0, $ipsID, $rut, $nom, $dias, $rem, $pen, $fon, $acc, $desR, $desC, $cod, $f1, $f2, $tra, $sim, $inv, $mat, $mon, $bon);
						if(self::$gintdebug){
							echo $sql."<br>";
							print_r($valores);
						}
						$basura = self::insertaSQL($sql,$valores);
			}
	    }
	}

	static private function parseaCaja($bloques, $indice){
		$tmp = $bloques;
		//echo "<hr>$indice<br>$tmp<hr>";
		if(strpos($tmp, "Número")>0 && $indice==1){
			self::$caja = "Caja de Compensación ".substr($tmp,0, strpos($tmp, "||Número"));
			self::$folio = self::extrae($tmp,"Folio:","|",1);
			///echo "<hr>Nombre: ".self::$caja."<br>Número: ".self::$folio."<br>";
			$tit = self::extrae($tmp,"Econom.|","|Dirección",1);
			$tit = explode("|", $tit);
			if(count($tit)>2){
				self::$emp = $tit[0];
				self::$rut = $tit[2];
			}
			///echo "RUT: ".self::$rut;
			///echo "<br>Empresa: ".self::$emp."<br>";
			return;
		}
		if(strpos($tmp, "Código Participante")>0 && $indice>0){
			self::$periodo = self::extrae($tmp,"Código Participante|","|N°",1);
			self::$fechaPago = self::extrae($tmp,"Pago Electrónico||","|",1);
			///echo "<br>Periodo: ".self::$periodo."<br>";
			///echo "Fecha de Pago: ".self::$fechaPago;
			$sql = "INSERT INTO caja_empresa (id, IdDocumento, archivo, nombre, folio, rut, empresa, periodo, fechaPago,login_user) VALUES (?,?,?,?,?,?,?,?,?,?)";
			$valores=array(0, self::$gobjDocumento->IdDocumento, self::$filename, self::$caja, self::$folio, self::$rut, self::$emp, self::$periodo, self::$fechaPago, 'Alfonso');
			if(self::$gintdebug){
				echo "<br>".$sql;
				print_r($valores);
			}
			$result=DB::insert($sql,$valores);
		    if($result==1){
				$sql = "SELECT max(id) AS id FROM caja_empresa WHERE IdDocumento=:doc AND rut=:rut AND periodo=:periodo limit 1";
			    $result = DB::select($sql, ['doc'=>self::$gobjDocumento->IdDocumento, 'rut'=>self::$rut, 'periodo'=>self::$periodo ]);
			    ///print_r($result);
			    if(count($result)>0){
			    	$maxRecords = count($result);
			    	foreach ($result as $row) {
					    self::$cajaID = $row->id;
					}
			    }
			    ///echo "CajaID: ".self::$cajaID;
		    }

		}
		if(strpos($tmp, "Pag.|Subs.|")>0){
			//echo "salto Término<br>";
			//Procesa Trabajadores CAJA
			$trab = substr($tmp,strpos($tmp, "Pag.|Subs.|")+strlen("Pag.|Subs.|"),(strpos($tmp, "|TOTALES ")-(strpos($tmp, "Pag.|Subs.|")+strlen("TOTALES "))));
			$tra = explode("|", $trab);
			if(isset($_GET['debug'])){
				echo "<hr>Trabajadores CAJA<hr>";
				echo "<pre>";
				print_r($tra);
				echo "</pre><hr>";
			}
			$basura = self::putCAJAtrabajadores(self::$cajaID,$tra);
		}
		if(strpos($tmp, "DE OBLIGACIONES PREVISIONALES")>0){
			//FONASA
			$cordenadas = self::extrae($tmp,"DE OBLIGACIONES PREVISIONALES","|NÚMERO DE SERIE",1);
			self::$folio = self::extrae($tmp,"NÚMERO DE SERIE:","TIPO DE PAGO",1);
			$fol = explode("|", self::$folio);
			self::$emp = self::extrae($tmp,"Razón Social o Nombre:","R.U.T:",1);
			self::$rut = self::extrae($tmp,"R.U.T:","Dirección:",1);
			$arut = explode("|", self::$rut);
			$per = self::extrae($tmp,"REMUNERACIÓN|","|Mes|Año|",1);
			$per = explode("|", $per);
			$periodo = "";
			foreach ($per as $value){
   				$periodo .= $value." ";
   			}
   			self::$periodo = trim($periodo);
				$tmp2 = explode("|", $tmp);
			$nada = self::fechaDePago($tmp2);
			//$fechaPago = self::extrae($tmp,"Pago Electrónico||","|PreviRed.com",1);
				///echo "<hr>Fonasa";
				//echo "Coordenadas ".$cordenadas;
				///echo "<br>Número: ".self::$folio[0];
				///echo "<br>Empresa: ".self::$emp;
				///echo "<br>RUT: ".self::$rut[0];
				///echo "<br>Periodo: ".self::$periodo;
				///echo "<br>Pago:".$fechaPago;
				//echo "<hr>Trabajadores<hr>";

			$sql = "INSERT INTO fonasa_empresa (id, IdDocumento, archivo, nombre, folio, rut, empresa, periodo, tipo, fechaPago,login_user) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
			$valores=array(0, self::$gobjDocumento->IdDocumento, self::$filename, 'FONASA', $fol[0], trim($arut[0]), trim(self::$emp), self::$periodo, self::$tipo, self::$fechaPago, 'Alfonso');
			if(self::$gintdebug){
				echo "<br>".$sql;
				print_r($valores);
			}
			$result = DB::insert($sql,$valores);
		    if($result==1){
				$sql = "SELECT max(id) AS id FROM fonasa_empresa WHERE IdDocumento=:doc AND rut=:rut AND periodo=:periodo ";
			    $result = DB::select($sql, ['doc'=>self::$gobjDocumento->IdDocumento, 'rut'=>trim(self::$rut[0]), 'periodo'=>trim(self::$periodo) ]);
			    ///print_r($result);
			    if(count($result)>0){
			    	$maxRecords = count($result);
			    	foreach ($result as $row) {
					    self::$fonasaID = $row->id;
					}
			    }
			    if(self::$gintdebug){
			    	echo "FonasaID: ".self::$fonasaID;
			    }
		    }
			$a=0;
			if(isset($_GET['debug'])){
				print_r($tmp);
			}
			$trab="";
			while (strpos($tmp, "TÉRMINO|DÍA|MES|AÑO||")>0) {
				$a++;
				$trab .= self::extrae($tmp,"TÉRMINO|DÍA|MES|AÑO||","|TOTAL PÁGINA",1);
				$tmp=substr($tmp, strpos($tmp, "|TOTAL PÁGINA")+1);
			}
			$tra = explode("|", $trab);
			if(isset($_GET['debug'])){
				echo "<pre>";
				print_r($tra);
				echo "</pre><hr>";
			}
			$basura = self::putFONASAtrabajadores(self::$fonasaID,$tra);
			return;
		}
	}

	static private function parseaFonasa($bloques, $indice){
		//echo "<br>Fonasa: ".$indice;
		$tmp=$bloques;
		if(strpos($tmp, "OBLIGACIONES PREVISIONALES")>0){
			//FONASA
			$cordenadas = self::extrae($tmp,"DE OBLIGACIONES PREVISIONALES","|NÚMERO DE SERIE",1);
			self::$folio = self::extrae($tmp,"NÚMERO DE SERIE:","TIPO DE PAGO",1);
			$fol = explode("|", self::$folio);
			self::$emp = self::extrae($tmp,"Razón Social o Nombre:","R.U.T:",1);
			self::$rut = self::extrae($tmp,"R.U.T:","Dirección:",1);
			$arut = explode("|", self::$rut);
			$per = self::extrae($tmp,"REMUNERACIÓN|","|Mes|Año|",1);
			$per = explode("|", $per);
			$periodo = "";
			foreach ($per as $value){
   				$periodo .= $value." ";
   			}
   			self::$periodo = trim($periodo);
				$tmp2 = explode("|", $tmp);
			$nada = self::fechaDePago($tmp2);
			//$fechaPago = self::extrae($tmp,"Pago Electrónico||","|PreviRed.com",1);
				///echo "<hr>Fonasa";
				//echo "Coordenadas ".$cordenadas;
				///echo "<br>Número: ".self::$folio[0];
				///echo "<br>Empresa: ".self::$emp;
				///echo "<br>RUT: ".self::$rut[0];
				///echo "<br>Periodo: ".self::$periodo;
				///echo "<br>Pago:".$fechaPago;
				//echo "<hr>Trabajadores<hr>";

			$sql = "INSERT INTO fonasa_empresa (id, IdDocumento, archivo, nombre, folio, rut, empresa, periodo, tipo, fechaPago,login_user) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
			$valores=array(0, self::$gobjDocumento->IdDocumento, self::$filename, 'FONASA', $fol[0], trim($arut[0]), trim(self::$emp), trim(self::$periodo), self::$tipo, self::$fechaPago, 'Alfonso');
			if(self::$gintdebug){
				echo "<br>".$sql;
				print_r($valores);
			}
			$result = DB::insert($sql,$valores);
		    if($result==1){
				$sql = "SELECT max(id) AS id FROM fonasa_empresa WHERE IdDocumento=:doc AND rut=:rut AND periodo=:periodo ";
			    $result = DB::select($sql, ['doc'=>self::$gobjDocumento->IdDocumento, 'rut'=>trim($arut[0]), 'periodo'=>trim(self::$periodo) ]);
			    //echo "<br>".$sql." ".trim($arut[0])." ".self::$periodo." ";
			    ///print_r($result);
			    if(count($result)>0){
			    	$maxRecords = count($result);
			    	foreach ($result as $row) {
					    self::$fonasaID = $row->id;
					}
			    }
			    if(self::$gintdebug){
			    	echo "FonasaID: ".self::$fonasaID;
			    }
		    }
			$a=0;
			if(isset($_GET['debug'])){
				print_r($tmp);
			}
			$trab="";
			while (strpos($tmp, "TÉRMINO|DÍA|MES|AÑO||")>0) {
				$a++;
				$trab .= self::extrae($tmp,"TÉRMINO|DÍA|MES|AÑO||","|TOTAL PÁGINA",1);
				$tmp=substr($tmp, strpos($tmp, "|TOTAL PÁGINA")+1);
			}
			$tra = explode("|", $trab);
			if(isset($_GET['debug'])){
				echo "<pre>";
				print_r($tra);
				echo "</pre><hr>";
			}
			$basura = self::putFONASAtrabajadores(self::$fonasaID,$tra);
			return;
		}
	}
	static private function parseaCajaOtrasPrestaciones($bloques, $indice){
		$tmp = $bloques;
		if(self::$gintdebug){
			echo "<hr>$indice<br>parseaCajaOtrasPrestaciones<br>$tmp<hr>";
		}
		if(strpos($tmp, "Número de Folio:")>0 && $indice==1){
			self::$caja = "Caja de Compensación ".substr($tmp,0, strpos($tmp, "||Número"));
			self::$folio = self::extrae($tmp,"Folio:","|",1);
			///echo "<hr>Nombre: ".self::$caja."<br>Número: ".self::$folio."<br>";
			$sql = "SELECT max(id) AS id FROM caja_empresa WHERE IdDocumento=:doc AND archivo=:archivo AND folio=:folio";
		    $result = DB::select($sql, ['doc'=>self::$gobjDocumento->IdDocumento, 'archivo'=>self::$filename, 'folio'=>self::$folio ]);
		    ///print_r($result);
		    if(count($result)>0){
		    	$maxRecords = count($result);
		    	foreach ($result as $row) {
				    self::$cajaID = $row->id;
				}
		    }
		    ///echo "CajaID: ".self::$cajaID;
		}
		if(strpos($tmp, "|Otros")>0 && self::$cajaID>0){
			//echo "salto Término<br>";
			//Procesa Trabajadores CAJA
			$trab = substr($tmp,strpos($tmp, "|Otros"),strpos($tmp, "|TOTALES ")-strpos($tmp, "|Otros"));
			$tra = explode("|", $trab);
			if(isset($_GET['debug'])){
				echo "<hr>Trabajadores CAJA OTRAS PRESTACIONES<hr>";
				echo "<pre>";
				print_r($tra);
				echo "</pre><hr>";
			}
			$basura = self::putCAJAOtrasPrestacionesTrabajadores(self::$cajaID,$tra);
		}
	}

	static private function putCAJAOtrasPrestacionesTrabajadores($cajaID,$tra){
		///echo "CajaID ".$cajaID;
		if($cajaID>0){
			//Barre todos los trabajadores
			$i = 0;
			$token=0;
			$pendiente=0;
			$campos=9;
			$c3="";
			$c4="";
			$c5="";
			$c6="";
			$c7="";
			foreach ($tra as $valor) {
				$i++;
				if(substr(trim($valor), 0,7)=="TOTALES"){
					return;
				}
				if(strpos($valor,"-")>0){
					if($token>0 && is_numeric(substr($c4,0,1)) && is_numeric(substr($c5,0,1)) && is_numeric(substr($c6,0,1)) && is_numeric(substr($c7,0,1))){
						//inserta valores
							$sql = "INSERT INTO caja_pago_otras_prestaciones (`id_caja_trabajador`, `caja_empresa_id`, `rut`, `nombre`, `credito_personal`, `convenio_dental`, `leasing`, `seguro_de_vida`, `otros`) VALUES (?,?,?,?,?,?,?,?,?)";
							$valores=array(0, $cajaID, $rut, $nom, $c3, $c4, $c5, $c6, $c7);
						if(self::$gintdebug){
							echo $sql."<br>";
							print_r($valores);
						}
						$basura = self::insertaSQL($sql,$valores);
						$pendiente=0;
					}
					$token=1;
					$campos=1;
					$rut = $valor;
					$pendiente=1;
				}else{
					$campos++;
					switch ($campos) {
						case '2':
							$nom = $valor;
							break;
						case '3':
							$c3 = " ".$valor;
							break;
						case '4':
							$c4 = " ".$valor;
							break;
						case '5':
							$c5 = $valor;
							break;
						case '6':
							$c6 = $valor;
							break;
						case '7':
							$c7 = $valor;
							break;
					}

				}
			}
			if($pendiente==1){
						//inserta valores
						$sql = "INSERT INTO caja_pago_otras_prestaciones (`id_caja_trabajador`, `caja_empresa_id`, `rut`, `nombre`, `credito_personal`, `convenio_dental`, `leasing`, `seguro_de_vida`, `otros`) VALUES (?,?,?,?,?,?,?,?,?)";
						$valores=array(0, $cajaID, $rut, $nom, $c3, $c4, $c5, $c6, $c7);
						if(self::$gintdebug){
							echo "<br>".$sql;
							print_r($valores);
						}
						$basura = self::insertaSQL($sql,$valores);
			}
	    }
	}

	static private function extraeTrabajadoresISAPRE($textoT){
		//global $text;
		$match = "Término";
		$string = $textoT['bloque'];
		$posI = strpos($string, $match);
		if ($posI>0){
			$posI = $posI + strlen("Término")-1;
			$newT=substr($string, $posI);
			$posF = stripos($newT, "TOTAL");
			if ($posF>0){
				$tmp = substr($newT, 1, $posF-1);
				if(isset($_GET['debug'])){
					echo "<pre>";
					print_r($tmp);
					echo "</pre><br>";
				}

		//	for ($a=0; $a < strlen($tmp); $a++){
		//		echo substr($tmp, $a,1)." | ".ord(substr($tmp, $a,1))."<br>";
		//	}

			$claves = preg_split("/[\s,]+/", trim($tmp));
			$cont="";
			$fecI="";
			$fecT="";

			$rem="";
			$cotO="";
			$sis="";
			$cotV="";
			$depC="";
			$depA="";
			$remI="";
			$cotA ="";
			$cotE="";
			$cod="";
			if(isset($_GET['debug'])){
				echo "<br>claves<pre>";
				print_r($claves);
				echo "</pre>";
			}
			return;
				for ($i= 0; $i <count($claves); $i++){
					if (strpos($claves[$i], "-")>0){
						//Checa si tiene un guión; solo el RUT tiene guión
						if($i>1){
							//Almacena los resultados
							if(empty($cam2) and empty($cam3)){
								//solo Fecha de Término
								$fecT = $cam1;
							}else{
								if(!empty($cam3)){
									//Todos llenos
									$fecT = $cam3;
									$fecI = $cam2;
									$cont = $cam1;
								}else{
									$fecT = $cam2;
									$fecI = $cam1;
								}
							}
							$tra[] = array('rut' => $rut, 'nombre' => $nom, 'Remuneracion' => $rem, 'CotObligatoria' => $cotO, 'sis' => $sis, 'CotVoluntaria' => $cotV, 'Contrato' => $cont, 'DepositoConvenido' => $depC, 'DepositoAhorro' => $depA, 'RemuneracionImponible' => $remI, 'CotizacionAfiliado' => $cotA, 'CotizacionEmpleador' => $cotE, 'Cod' => $cod, 'FechaInicio' => $fecI, 'FechaTermino' => $fecT);
						}
						$rut = trim($claves[$i]);
						$cont="";
						$fecI="";
						$fecT="";
						$nom = "";
						$cam1 = "";
						$cam2 = "";
						$cam3 = "";
						$esperaNumero = 0;
						$x = 0;
					}else{
						if(is_numeric($claves[$i])){
							$esperaNumero = 1;
							$x++;
							switch ($x) {
								case 1:
									$rem = trim($claves[$i]);
									break;
								case 2:
									$cotO = trim($claves[$i]);
									break;
								case 3:
									$sis = trim($claves[$i]);
									break;
								case 4:
									$cotV = trim($claves[$i]);
									break;
								case 5:
									$depC = trim($claves[$i]);
									break;
								case 6:
									$depA = trim($claves[$i]);
									break;
								case 7:
									$remI = trim($claves[$i]);
									break;
								case 8:
									$cotA = trim($claves[$i]);
									break;
								case 9:
									$cotE = trim($claves[$i]);
									break;
								case 10:
									$cod = trim($claves[$i]);
									break;
								case 11:
									$otr1 = trim($claves[$i]);
									break;
								case 12:
									$otr2 = trim($claves[$i]);
									break;
								case 13:
									$otr3 = trim($claves[$i]);
									break;
							}
						}else{
							if($esperaNumero == 0){
								$nom = $nom." ".trim($claves[$i]);
								$y=0;
							}else{
								$y++;
								switch ($y) {
									case 1:
										$cam1 = trim($claves[$i]);
										break;
									case 2:
										$cam2 = trim($claves[$i]);
										break;
									case 3:
										$cam3 = trim($claves[$i]);
										break;
								}
							}
						}
					}
				}
				if(empty($cam2) and empty($cam3)){
					//solo Fecha de Término
					$fecT = $cam1;
				}else{
					if(!empty($cam3)){
						//Todos llenos
						$fecT = $cam3;
						$fecI = $cam2;
						$cont = $cam1;
					}else{
						$fecT = $cam2;
						$fecI = $cam1;
					}
				}
				$tra[] = array('rut' => $rut, 'nombre' => $nom, 'Remuneracion' => $rem, 'CotObligatoria' => $cotO, 'sis' => $sis, 'CotVoluntaria' => $cotV, 'Contrato' => $cont, 'DepositoConvenido' => $depC, 'DepositoAhorro' => $depA, 'RemuneracionImponible' => $remI, 'CotizacionAfiliado' => $cotA, 'CotizacionEmpleador' => $cotE, 'Cod' => $cod, 'FechaInicio' => $fecI, 'FechaTermino' => $fecT);
				return $tra;
			}else{
				return "";
			}
		}else{
			return "";
		}
	}

	static private function extraeTrabajadoresF30($texto){
		$match = "TRABAJADOR";
		$regex = '/\b'.$match.'\b/';
		preg_match($regex, $texto, $match, PREG_OFFSET_CAPTURE);
		$posI = $match[0][1];
		//$posI = stripos($texto, "TRABAJADOR");
		if ($posI>0){
			$posI = $posI + strlen("TRABAJADOR");
			$newT=substr($texto, $posI);
			$posF = stripos($newT, "TOTAL");
			if ($posF>0){
				$tmp = explode(" ",substr($newT, 0, $posF));
				$j=0;
				$mes = "";
				$nom = "";
				$tra = array();
				for ($i= 0; $i <count($tmp); $i++){
					if(empty($mes) and !empty($tmp[$i])){
						$mes = trim($tmp[$i]);
					}
					if ($mes == $tmp[$i]){
						if ($j <> 0){
							$tra[] = array('mes' => $mes, 'anio' => $anio, 'rut' => $rut, 'nombre' => $nom );
							$nom = "";
						}
						$j = $i;
					}
					if($j + 1 == $i){
						$anio = trim($tmp[$i]);
					}
					if($j + 2 == $i){
						$rut = trim($tmp[$i]);
					}
					if($j + 2 < $i){
						$nom = $nom ." ". trim($tmp[$i]);
					}
				}
				$tra[] = array('mes' => $mes, 'anio' => $anio, 'rut' => $rut, 'nombre' => $nom );
				return $tra;
			}else{
				return "TOTAL no encontrado";
			}
		}else{
			return "TRABAJADOR no encontrado";
		}
	}

	static public function Approve($IdAprobador=null) {
		$result = parent::Approve();
		self::Parsea();
		return $result;
	}

	static public function Parsea($debug=0) {
		self::Parse();
		self::Crossing();
	}

	static public function Crossing($debug=0) {

		$lintIdDocumento = self::$gobjDocumento->IdDocumento;
		$lintIdContrato = self::$gobjDocumento->IdEntidad;
		$ldatFechaPeriodo = self::$gobjDocumento->FechaEmision;
		$lintIdProceso = self::getIdProceso('PreviredTrabajador');
		$lobjDocumentoPadre = self::$gobjDocumento;

		$lobjDocumento = Documentos::whereHas('TipoDocumento',function($q) use ($lintIdProceso) {
			                          	$q->where('IdProceso',$lintIdProceso);
																})
		                             ->where('FechaEmision',$ldatFechaPeriodo)
									 ->where('contrato_id',$lintIdContrato)
									 ->where('Entidad',3)
									 ->where('IdEstatus','=',7)
									 ->get();

		foreach($lobjDocumento as $larrDocumentos){

			$lstrRut = $larrDocumentos->Persona->RUT;
			$lobjMyDocumentsPreviredEmployee = new MyDocumentsPreviredEmployee($larrDocumentos->IdDocumento,null,$lobjDocumentoPadre);
			$larrResult = $lobjMyDocumentsPreviredEmployee->Crossing();

		}

	}

	static private function EmptyParseo(){

		$lintIdDocumento = self::$gobjDocumento->IdDocumento;

		//Limpiamos el documento de la afp
		$lobjAFPTrabajador = \DB::table('afp_trabajador')
								->whereExists( function($query) use ($lintIdDocumento){
									$query->select(\DB::raw(1))
										->from('afp_empresa')
										->whereRaw('afp_empresa.IdDocumento = '.$lintIdDocumento)
										->whereRaw('afp_trabajador.afp_empresa_id = afp_empresa.id');
								})
								->delete();
		$lobjAFP = \DB::table('afp_empresa')
					 ->where('afp_empresa.IdDocumento','=',$lintIdDocumento)
					 ->delete();

		//Limpiamos los registro de la caja
		$lobjCajaTrabajador = \DB::table('caja_trabajador')
				->whereExists( function($query) use ($lintIdDocumento){
					$query->select(\DB::raw(1))
						->from('caja_empresa')
						->whereRaw('caja_empresa.IdDocumento = '.$lintIdDocumento)
						->whereRaw('caja_trabajador.caja_empresa_id = caja_empresa.id');
				})
				->delete();

		$lobjCajaOtros = \DB::table('caja_pago_otras_prestaciones')
				->whereExists( function($query) use ($lintIdDocumento){
					$query->select(\DB::raw(1))
						->from('caja_empresa')
						->whereRaw('caja_empresa.IdDocumento = '.$lintIdDocumento)
						->whereRaw('caja_pago_otras_prestaciones.caja_empresa_id = caja_empresa.id');
				})
				->delete();

		$lobjAFP = \DB::table('caja_empresa')
				->where('caja_empresa.IdDocumento','=',$lintIdDocumento)
				->delete();

		//Limpiamos el documento de Mutual
		$lobjMutualTrabajador = \DB::table('mutual_trabajador')
					 ->whereExists( function($query) use ($lintIdDocumento){
						 $query->select(\DB::raw(1))
							 ->from('mutual_empresa')
							 ->whereRaw('mutual_empresa.IdDocumento = '.$lintIdDocumento)
							 ->whereRaw('mutual_trabajador.mutual_empresa_id = mutual_empresa.id');
					 })
					 ->delete();
		$lobjMutual = \DB::table('mutual_empresa')
					->where('mutual_empresa.IdDocumento','=',$lintIdDocumento)
					->delete();

		//Limpiamos el documento de la IPS
		$lobjIpsMutualTrabajador = \DB::table('ips_trabajador')
					 ->whereExists( function($query) use ($lintIdDocumento){
						 $query->select(\DB::raw(1))
							 ->from('ips_empresa')
							 ->whereRaw('ips_empresa.IdDocumento = '.$lintIdDocumento)
							 ->whereRaw('ips_trabajador.ips_empresa_id = ips_empresa.id');
					 })
					 ->delete();
		$lobjIpsMutual = \DB::table('ips_empresa')
					->where('ips_empresa.IdDocumento','=',$lintIdDocumento)
					->delete();

		//Limpiamos el documento de Fonasa
		$lobjFonasaTrabajador = \DB::table('fonasa_trabajador')
								->whereExists( function($query) use ($lintIdDocumento){
									$query->select(\DB::raw(1))
										->from('fonasa_empresa')
										->whereRaw('fonasa_empresa.IdDocumento = '.$lintIdDocumento)
										->whereRaw('fonasa_trabajador.fonasa_empresa_id = fonasa_empresa.id');
								})
								->delete();
		$lobjFonasa = \DB::table('fonasa_empresa')
						->where('fonasa_empresa.IdDocumento','=',$lintIdDocumento)
						->delete();

		//Limpiamos el documento de Isapre
		//Limpiamos el documento de Fonasa
		$lobjIsapreTrabajador = \DB::table('isapre_trabajador')
								->whereExists( function($query) use ($lintIdDocumento){
									$query->select(\DB::raw(1))
										->from('isapre_empresa')
										->whereRaw('isapre_empresa.IdDocumento = '.$lintIdDocumento)
										->whereRaw('isapre_trabajador.isapre_empresa_id = isapre_empresa.id');
								})
								->delete();
		$lobjIsapre = \DB::table('isapre_empresa')
						->where('isapre_empresa.IdDocumento','=',$lintIdDocumento)
						->delete();

		return array("success"=>true, "code"=>1, "message"=>"Se limpiaron las tablas satifactoriamente.");

	}

	static public function Parse($debug=0) {

		self::$gintdebug = $debug;
		// Parseo de archivos PDF
		// acorona@sourcing.cl
		// Abril-2018

		//Eliminamos los datos anteriormente cargados para este documento.
		self::EmptyParseo();

		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 3000);

        $datosRuta = self::getDirectorio();

        //if(gethostname()=="transbank"){
        	$datosRuta=public_path($datosRuta);
        //}

	self::$filename = $datosRuta.self::$gobjDocumento->DocumentoURL;
	self::$filename = trim(str_replace("'", "", self::$filename));
	//self::$filename = trim(str_replace("/", "\\", self::$filename));
	//$filename = realpath(__DIR__).'/'.$filename;
	//echo "Archivo: ".self::$filename."<hr>";
	if (file_exists(self::$filename)) {
 		// Include Composer
 		$parser = new \Smalot\PdfParser\Parser();
	    $pdf = $parser->parseFile(self::$filename);
	    try {
	    	$text = $pdf->getText();
	    } catch (Exception $e) {
	    	$text = "";
	    }

		//	echo $text;
			$qq = explode(" ", $text);
			//echo "<pre>";
			//print_r($qq);
			//echo "</pre>";
			$string = preg_replace('/[\x00-\x1F\x7F]/u', '|', $text);
			$string = preg_replace('/\s+/', ' ',$string);
			//$full_array = preg_split("/[\s,]+/", $string);
			//echo "<hr>".$string;
			//echo "<hr><pre>";
			//print_r($full_array);
			//echo "</pre><hr>";
	    $ok=0;

	    $sText = $text;
	    $sFin = 1;

		$pos = strpos($string, "ADO DE ANTECEDENTES LABORALES");
		if ($pos !== false) {
			echo "Archivo F30...<br>";
			$num = self::extrae($string,"presente:","1.-",1);
			echo "Numero: ".$num."<br>";
			$rut = self::extrae($string,"SOLICITANTE:"," ",2);
			echo "RUT: ".$rut."<br>";
			$razonsocial = self::extrae($string,"RUT","RAZÓN",3);
			echo "razon social: ".$razonsocial."<br>";
			$multasE = self::extrae($string,"MULTAS EJECUTORIADAS - NO INCLUIDAS EN BOLETÍN DE INFRACTORES","DEUDA PREVISIONAL",1);
			echo "multas ejecutoriadas: ".$multasE."<br>";
			$deuda = self::extrae($string,"DEUDA PREVISIONAL (BOLETIN DE INFRACTORES)","RESOLUCIONES DE MULTA",1);
			echo "multas ejecutoriadas: ".$deuda."<br>";
			$resoluciones = self::extrae($string,"RESOLUCIONES DE MULTA (BOLETIN DE INFRACTORES)","PLAZO Y ÁMBITO",1);
			echo "Resoluciones de Multa: ".$resoluciones."<br>";
			$vigencia = self::extrae($string,"vigencia hasta el "," ",1);
			echo "Vigencia: ".$vigencia."<br>";
			$folio = self::extrae($string,"FiscalizaciónOf. de Partes","EL SIGUIENTE ES",1);
			echo "Folio: ".$folio."<br>";
			$ok=1;
		}
		$pos = strpos($string, "CERTIFICADO DE CUMPLIMIENTO DE OBLIGACIONES");
		if ($pos !== false) {
			echo "Archivo F30-1...<br><pre>";
			$num = self::extrae($string,"AÑO CERTIFICADO","CERTIFICADO DE CUMPLIMIENTO",1);
			echo "Número: ".$num."<br>";
			$rut = self::extrae($string,"SOCIAL / NOMBRE","RUT REP",1);
			echo "RUT: ".$rut."<br>";
			$trabDeclarados = self::extrae($string,"PERÍODO TOTAL TRABAJADORES VIGENTES","2.2.-",1);
			echo "Situación Trabajadores: ".$trabDeclarados."<br>";
			$edo = self::extrae($string,"ADJUNTA NÓMINA","2.3.-",1);
			echo "Estado de las Cotizaciones: ".$edo."<br>";
			$det = self::extrae($string,"2.3.- DETALLE DE REMUNERACIONES MES AÑO N° TRABAJADORES CON PAGO MONTO PAGADO ($) N° TRABAJADORES SIN PAGO","2.4.-",1);
			echo "Detalle de Remuneraciones: ".$det."<br>";
			$idemnizacion = self::extrae($string,"2.4.1.- INDEMNIZACIÓN SUSTITUTIVA DEL AVISO PREVIO N° TRABAJADORES CON PAGO MONTO PAGADO ($) N° TRABAJADORES SIN PAGO","2.4.2.-",1);
			echo "Indemnización sustitutiva: ".$idemnizacion."<br>";
			$idemnizacionAniosSer = self::extrae($string,"2.4.2.- INDEMNIZACION POR AÑO(S) DE SERVICIO N° TRABAJADORES CON PAGO MONTO PAGADO ($) N° TRABAJADORES SIN PAGO","3.-",1);
			echo "Indemnización por años de servicio: ".$idemnizacionAniosSer."<br>";
			$validez = self::extrae($string,"período comprendido entre",", siendo válido en todo el territorio nacional. 6.-",1);
			echo "Validez: ".$validez."<br>";
			$codigoV = self::extrae($string,"Firma electrónica Avanzada.","Código de Verificación",1);
			echo "Código de Verificación: ".$codigoV."<br>";
			if(isset($_GET['debug'])){
				print_r(extraeTrabajadoresF30($text));
			}
			$ok=1;
		}
		$pos = strpos($string, "DE COTIZACIONES PREVISIONALES");

		if ($pos !== false) {
			\Log::info("Previred: ".date("Y-m-d H:i:s")."  ".self::$gobjDocumento->IdDocumento." ".self::$filename);
			//echo "<hr>Archivo de Obligaciones Previsionales.pdf...<br>";
			//Procesa AFP
			if(self::$gintdebug){
				echo self::$filename."<br>";
				if(self::$gintdebug=='9')
					die();
			}
			//$blo = self::extraeBloque($string,"PAGO DE COTIZACIONES PREVISIONALES Y DEPOSITO",0);
			$blo = explode("DE COTIZACIONES PREVISIONALES",$string);
			if(count($blo)>0){
				$tra = array_walk($blo, 'self::parseaBloque');
			}

			unset($blo);
			unset($tmp);
			unset($tra);
			//Procesa ISAPRE
			$fonasaID = $cajaID = $isapre = $folio = $emp = $rut = $periodo = $fpago = $isapreID = $isapreID = null;
			$blo = explode("ISAPRE",$string);
			$max = count($blo);
			if(count($blo)>0){
				$param=array('max'=>$max, 'isapre'=>$isapre, 'folio'=>$folio, 'rut'=>$rut, 'emp'=>$emp, 'isapreID'=>$isapreID);
				$isa = array_walk($blo, 'self::parseaISAPRE', $param);
			}

			//Procesa Instituto de Seguridad
			$blo = explode("Instituto de Seguridad",$string);
			self::$mutualID = null;
			if(count($blo)>0){
				$mut = array_walk($blo, 'self::parseaMutual');
			}

			//Procesa Asociación Chilena de Seguridad (ACHS)
			$blo = explode("Asociación Chilena de Seguridad",$string);
			self::$mutualID = null;
			if(count($blo)>0){
				$mut = array_walk($blo, 'self::parseaMutual');
			}

			//Procesa Mutual
			$blo = explode("Mutual",$string);
			self::$mutualID = null;
			if(count($blo)>0){
				$mut = array_walk($blo, 'self::parseaMutual');
			}

			//Procesa Caja de Compensación
			$blo = explode("Caja de Compensación",$string);
			if(count($blo)>0){
				$mut = array_walk($blo, 'self::parseaCaja');
			}

			//Procesa Caja de Compensación PAGO DE OTRAS PRESTACIONES
			$cajaID = 0;
			$blo = explode("DE OTRAS PRESTACIONES",$string);
			if(count($blo)>0){
				$mut = array_walk($blo, 'self::parseaCajaOtrasPrestaciones');
			}

			//Procesa IPS (ex INP)  Instituto de Previsión Social
			$ipsID = 0;
			$done = 0;
			$blo = explode("IPS (ex INP)",$string);
			if(count($blo)>0){
				$mut = array_walk($blo, 'self::parseaMutualIPS');
			}

			//Fonasa
			$ipsID = 0;
			$done = 0;
			$blo = explode("OBLIGACIONES PREVISIONALES)",$string);
			if(count($blo)>0){
				$mut = array_walk($blo, 'self::parseaFonasa');
			}

			$ok=1;
		}
		if ($ok==0){
			\Log::info("Archivo ".self::$filename." NO Reconocido :-(");
		}else{
			\Log::info("Archivo ".self::$filename." procesado :-)");
		}
	}else{
		\Log::info("Archivo no existe: ".base_path()." - ".self::$filename);
	}




	}

}
