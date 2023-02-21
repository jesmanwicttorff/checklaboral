<?php

use App\Models\Documentos;
use App\Library\MyDocuments;

class MyLoadbatch {

    static private $parrFormat;
    static private $pstrDirectory = "uploads/documents/";
    static private $pstrDirectoryResult = "uploads/documents/";

    static public function getDirectory() {
        return self::$pstrDirectory;
    }

    static public function getDirectoryResult() {
        return self::$pstrDirectoryResult;
    }

    static private function ExcelFormatToPHP($pstrFecha) {
        $lstrResultado = "";
        switch ($pstrFecha) {
            case "mm-dd-yy":
                $lstrResultado = "m-d-y";
                break;
        }
        return $lstrResultado;
    }

    static private function setFormat($pintIdProceso) {
        $larrFormato = array();
        if ($pintIdProceso == 1) {
            //Formato de personas de personas
            $larrFormato = array(
                "TIPOIDENTIFICACION" => "A",
                "IDENTIFICACION" => "B",
                "NOMBRES" => "C",
                "APELLIDOS" => "D",
                "DIRECCION" => "E",
                "FECHANACIMIENTO" => "F",
                "SEXO" => "G",
                "NACIONALIDAD" => "H",
                "ESTADOCIVIL" => "I",
                "CONTRATO" => "J",
                "FECHAINICIO" => "K",
                "ROL" => "L",
                "RESULTADO" => "M"
            );
        } elseif ($pintIdProceso == 2) {
            //Formato de personas de contratos
            $larrFormato = array("RUT" => "A",
                "NUMERO" => "B",
                "FECHAINICIO" => "C",
                "FECHAFIN" => "D",
                "MONTO" => "E",
                "TIPOGASTO" => "F",
                "EXTENSION" => "G",
                "REPORTE" => "H",
                "RESULTADO" => "I",
            );
        } elseif ($pintIdProceso == 3) {
            //Formato de personas de personas
            $larrFormato = array("RUT" => "A",
                "RAZONSOCIAL" => "B",
                "NOMBREFANTASIA" => "C",
                "REPRESENTANTE" => "D",
                "REPRESENTANTEFONO" => "E",
                "REPRESENTANTEEMAIL" => "F",
                "DIRECCION" => "G",
                "FONOEMPRESA" => "H",
                "EMAILEMPRESA" => "I",
                "PAGINAWEB" => "J",
                "RESULTADO" => "K"
            );
        } elseif ($pintIdProceso == 4) { //Charla de inducción
            $larrFormato = array("TIPOIDENTIFICACION" => "A",
                "IDENTIFICACION" => "B",
                "FECHAASISTENCIA" => "C",
                "RESULTADO" => "D"
            );
        } elseif ($pintIdProceso == 5) { //Multas chile
            $larrFormato = array("RUT" => "A",
                "RAZONSOCIAL" => "B",
                "FECHA" => "C",
                "UTM" => "D",
                "SITUACION" => "E",
                "CANTIDAD" => "F",
                "COMENTARIOS" => "G",
                "MONTOUTM" => "H",
                "MONTO" => "I",
                "RESULTADO" => "J"
            );
        } elseif ($pintIdProceso == 6) { //Situación Tributaria
            $larrFormato = array("RUT" => "A",
                "NOMBRE_CONTRATISTA" => "B",
                "PERIODO" => "C",
                "OBSERVACIONES_SII" => "D",
                "EVALUACION" => "E",
                "RESULTADO" => "F"
            );
        } elseif ($pintIdProceso == 17) { //Multas general
            $larrFormato = array("IDENTIFICACION" => "A",
                "RAZONSOCIAL" => "B",
                "FECHA" => "C",
                "SITUACION" => "D",
                "CANTIDAD" => "E",
                "COMENTARIOS" => "F",
                "MONTO" => "G",
                "RESULTADO" => "H"
            );
        } elseif ($pintIdProceso == 18) { //Carga masiva de diferencias
            $larrFormato = array("IDENTIFICACION" => "A",
                "RAZONSOCIAL" => "B",
                "FECHA" => "C",
                "SITUACION" => "D",
                "CANTIDAD" => "E",
                "COMENTARIOS" => "F",
                "MONTO" => "G",
                "RESULTADO" => "H"
            );
        }
        self::$parrFormat = $larrFormato;
        return $larrFormato;
    }

    static public function LoadBach($pintIdProceso, $pobjFileLoad, $pintIdTipoDocumento = '') {
        $lstrDirectory = self::$pstrDirectory;
        $lstrDirectoryResult = self::$pstrDirectoryResult;
        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUserLogin = \Session::get('uid');

        $lstrFileName = $pobjFileLoad->getClientOriginalName();
        $lstrFileExtension = $pobjFileLoad->getClientOriginalExtension();
        $lintIdRand = rand(1000, 100000000);
        $lstrFullFileName = strtotime(date('Y-m-d H:i:s')) . '-' . $lintIdRand . '.';
        $lstrFullFileNameResult = "result-" . $lstrFullFileName . "xlsx";
        $lstrFullFileName = $lstrFullFileName . $lstrFileExtension;
        try {
            $lstrResultMove = $pobjFileLoad->move($lstrDirectory, $lstrFullFileName);
        } catch (Exception $e) {
            $lstrResultMove = "";
        }

        $larrResultRejected = array();
        $larrResult = array("Cargados" => 0,
            "Modificados" => 0,
            "Rechazados" => 0,
            "IdTipoDOcumento" => $pintIdTipoDocumento
        );
        if ($lstrResultMove) {

            //Abrimos el archivo
            include '../app/Library/PHPExcel/IOFactory.php';
            $lstrDirectoryFileName = $lstrDirectory . $lstrFullFileName;
            try {
                $inputFileType = PHPExcel_IOFactory::identify($lstrDirectoryFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $lobjPHPExcel = \PHPExcel_IOFactory::load($lstrDirectoryFileName);
            } catch (Exception $e) {
                return array('status' => 'success',
                    'message' => "Error abriendo excel " . $e->getMessage(),
                    'code' => '1'
                );
            }
            //Convertimos objeto en arreglo
            $larrPHPExcel = $lobjPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            $lintCount = count($larrPHPExcel);
            $larrFormato = self::setFormat($pintIdProceso);

//            if ($inputFileType != 'CSV') {
                if (!self::ValidateColumns($larrPHPExcel[1], $larrFormato)) {
                    return array('status' => 'error', 'message' => "Formato de archivo", 'code' => '-1');
                }
//            } else {
//                if (!self::ValidateColumnsCSV($larrPHPExcel[1], $larrFormato)) {
//                    return array('status' => 'error', 'message' => "Error en el número de columnas", 'code' => '-2');
//                }
//            }

            if ($pintIdProceso == 1) { //Proceso de carga de personas
                $i = 2;
                for ($i; $i <= $lintCount; $i++) {
                    $larrResultado = array();
                    $larrResultado = self::LoadBatchPeople($larrPHPExcel[$i], $lintLevelUser, $lintIdUserLogin);
                    if ($larrResultado["code"] == "1") {
                        $larrResult["Cargados"] += $larrResultado["result"]["nuevos"];
                        $larrResult["Modificados"] += $larrResultado["result"]["modificados"];
                    } elseif ($larrResultado["code"] == "-1") {
                        return array('status' => 'success',
                            'message' => "Error en el formato de archivo ",
                            'code' => '3'
                        );
                    } else {
                        $larrResult["Rechazados"] += 1;
                        $larrResultado["result"] = $larrPHPExcel[$i];
                        $larrResultRejected[] = $larrResultado;
                    }
                }
            } else if ($pintIdProceso == 2) { //Proceso de carga de contratos
                $i = 2;
                for ($i; $i <= $lintCount; $i++) {
                    $larrResultado = array();
                    $larrResultado = self::LoadBatchContracts($larrPHPExcel[$i], $lintLevelUser, $lintIdUserLogin);
                    if ($larrResultado["code"] == "1") {
                        $larrResult["Cargados"] += $larrResultado["result"]["nuevos"];
                        $larrResult["Modificados"] += $larrResultado["result"]["modificados"];
                    } elseif ($larrResultado["code"] == "-1") {
                        return array('status' => 'success',
                            'message' => "Error en el formato de archivo ",
                            'code' => '3'
                        );
                    } else {
                        $larrResult["Rechazados"] += 1;
                        $larrResultado["result"] = $larrPHPExcel[$i];
                        $larrResultRejected[] = $larrResultado;
                    }
                }
            } else if ($pintIdProceso == 3) { //Proceso de carga de contratistas
                $i = 2;
                for ($i; $i <= $lintCount; $i++) {
                    $larrResultado = array();
                    $larrResultado = self::LoadBatchContractors($larrPHPExcel[$i], $lintLevelUser, $lintIdUserLogin);
                    if ($larrResultado["code"] == "1") {
                        $larrResult["Cargados"] += $larrResultado["result"]["nuevos"];
                        $larrResult["Modificados"] += $larrResultado["result"]["modificados"];
                    } elseif ($larrResultado["code"] == "-1") {
                        return array('status' => 'success',
                            'message' => "Error en el formato de archivo ",
                            'code' => '3'
                        );
                    } else {
                        $larrResult["Rechazados"] += 1;
                        $larrResultado["result"] = $larrPHPExcel[$i];
                        $larrResultRejected[] = $larrResultado;
                    }
                }
            } else if ($pintIdProceso == 4) { //Proceso de carga de charla de inducción
                $i = 2;
                for ($i; $i <= $lintCount; $i++) {
                    $larrResultado = array();
                    $larrResultado = self::LoadBatchCharla($larrPHPExcel[$i], $lintLevelUser, $lintIdUserLogin);
                    if ($larrResultado["code"] == "1") {
                        $larrResult["Cargados"] += $larrResultado["result"]["nuevos"];
                        $larrResult["Modificados"] += $larrResultado["result"]["modificados"];
                    } elseif ($larrResultado["code"] == "-1") {
                        return array('status' => 'success',
                            'message' => "Error en el formato de archivo ",
                            'code' => '3'
                        );
                    } else {
                        $larrResult["Rechazados"] += 1;
                        $larrResultado["result"] = $larrPHPExcel[$i];
                        $larrResultRejected[] = $larrResultado;
                    }
                }
            } else if ($pintIdProceso == 5) { //Proceso de carga de charla de inducción
                $i = 2;
                for ($i; $i <= $lintCount; $i++) {
                    $larrResultado = array();
                    $larrResultado = self::LoadBatchMultas($larrPHPExcel[$i], $lintLevelUser, $lintIdUserLogin);
                    if ($larrResultado["code"] == "1") {
                        $larrResult["Cargados"] += $larrResultado["result"]["nuevos"];
                        $larrResult["Modificados"] += $larrResultado["result"]["modificados"];
                    } elseif ($larrResultado["code"] == "-1") {
                        return array('status' => 'success',
                            'message' => "Error en el formato de archivo ",
                            'code' => '3'
                        );
                    } else {
                        $larrResult["Rechazados"] += 1;
                        $larrResultado["result"] = $larrPHPExcel[$i];
                        $larrResultRejected[] = $larrResultado;
                    }
                }
            } else if ($pintIdProceso == 6) { //Proceso de carga de Situación Tributaria
                $i = 2;

                for ($i; $i <= $lintCount; $i++) {
                    $larrResultado = array();
                    $larrResultado = self::LoadBatchSituacionTrib($larrPHPExcel[$i], $lintLevelUser, $lintIdUserLogin);
                    if ($larrResultado["code"] == "1") {
                        $larrResult["Cargados"] += $larrResultado["result"]["nuevos"];
                        $larrResult["Modificados"] += $larrResultado["result"]["modificados"];
                    } elseif ($larrResultado["code"] == "-1") {
                        return array('status' => 'success',
                            'message' => "Error en el formato de archivo ",
                            'code' => '3'
                        );
                    } else {
                        $larrResult["Rechazados"] += 1;
                        $larrResultado["result"] = $larrPHPExcel[$i];
                        $larrResultRejected[] = $larrResultado;
                    }
                }
            } else if ($pintIdProceso == 17) { //Proceso de carga de Situación Tributaria
                $i = 2;

                for ($i; $i <= $lintCount; $i++) {
                    $larrResultado = array();
                    $larrResultado = self::LoadBatchMultasGeneral($larrPHPExcel[$i], $lintLevelUser, $lintIdUserLogin);
                    if ($larrResultado["code"] == "1") {
                        $larrResult["Cargados"] += $larrResultado["result"]["nuevos"];
                        $larrResult["Modificados"] += $larrResultado["result"]["modificados"];
                    } elseif ($larrResultado["code"] == "-1") {
                        return array('status' => 'success',
                            'message' => "Error en el formato de archivo ",
                            'code' => '3'
                        );
                    } else {
                        $larrResult["Rechazados"] += 1;
                        $larrResultado["result"] = $larrPHPExcel[$i];
                        $larrResultRejected[] = $larrResultado;
                    }
                }
            } else {
                return array('status' => 'success',
                    'message' => "Proceso no definido",
                    'code' => '2'
                );
            }
        } else {
            $larrResult = array('status' => 'success',
                'message' => "Error guardando archivo",
                'code' => '0'
            );
        }

        //Generamos el archivo de resultado
        if ($larrResultRejected) {
            $larrFormat = self::$parrFormat;
            //require_once '../app/Library/PHPExcel.php';
            $lobjPHPExcelResult = new \PHPExcel();
            $lobjExcelResult = \PHPExcel_IOFactory::createWriter($lobjPHPExcelResult, "Excel2007");
            $lobjSheet = $lobjPHPExcelResult->getActiveSheet();
            $lobjSheet->setTitle('Resultado');
            foreach ($larrFormat as $key => $value) {
                $lobjSheet->getCell($value . '1')->setValue($key);
                $lobjSheet->getColumnDimension($value)->setAutoSize(true);
                $lobjSheet->getStyle($value . '1')->applyFromArray(array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFC000')
                    )
                        )
                );
            }
            $lintResultado = 1;
            foreach ($larrResultRejected as $larrRejected) {
                $lintResultado += 1;
                foreach ($larrFormat as $key => $value) {
                    if ($key == "RESULTADO") {
                        $lstrValue = $larrRejected["message"];
                    } else {
                        if ($key == "FECHANACIMIENTO"){
                            if ($larrRejected["result"][$value]) {
                                $ldatFechaNacimiento = new DateTime();
                                $ldatFechaNacimiento = DateTime::createFromFormat('d-m-Y',$larrRejected["result"][$value]);
                                $lstrValue = $ldatFechaNacimiento->format('d-m-Y');
                            }else{
                                $lstrValue = "";
                            }
                        }else{
                            $lstrValue = $larrRejected["result"][$value];
                        }
                    }
                    $lobjSheet->getCell($value . $lintResultado)->setValue($lstrValue);
                }
            }
            $lobjExcelResult->save($lstrDirectoryResult . $lstrFullFileNameResult);
        } else {
            $lstrFullFileNameResult = "";
        }

        //Insertamos el registro de log con el resultado del proceso
        $larrResult["IdProceso"] = $pintIdProceso;
        $larrResult["ArchivoURL"] = $lstrFullFileName;
        $larrResult["ArchivoResultadoURL"] = $lstrFullFileNameResult;
        $larrResult["entry_by"] = $lintIdUserLogin;
        \DB::table("tbl_carga_masiva_log")->insertGetId($larrResult);


        return array('status' => 'success',
            'message' => "Se ejecuto correctamente el proceso de carga",
            'code' => '1',
            'result' => $larrResult
        );
    }

    static private function LoadBatchPeople($pobjPHPExcel, $pintLevelUser, $pintIdUserLogin) {

        $larrResultado = array("nuevos" => 0,
            "modificados" => 0);
        $lobjContratosPersonas = array();
        $lintIdUserAccess = $pintIdUserLogin;

        $larrFormato = self::$parrFormat;

        $lintTipoIdentificacion = 0;
        $lstrTipoIdentificacion = isset($larrFormato['TIPOIDENTIFICACION']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['TIPOIDENTIFICACION']])) : '';
        if ($lstrTipoIdentificacion) {
            $lobjTipoIdentificacion = \DB::table('tbl_tipos_identificacion')->select('IdTipoIdentificacion','Descripcion')->where('Descripcion', $lstrTipoIdentificacion)->get();
            if (!$lobjTipoIdentificacion) {
                return array('status' => 'error', 'message' => "El tipo de identificacion no existe", 'code' => '7');
            } else {
                $lintTipoIdentificacion = $lobjTipoIdentificacion[0]->IdTipoIdentificacion;
            }
        } else {
            return array('status' => 'error', 'message' => "El tipo de identificacion no puede ser vacio", 'code' => '10');
        }

        //validamos rut
        $lstrRut = isset($larrFormato['IDENTIFICACION']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['IDENTIFICACION']])) : '';
        if ($lintTipoIdentificacion == 1){
            $lstrRut = \MySourcing::FormatRut($lstrRut);
            if (!\MySourcing::ValidateRut($lstrRut)) {
                return array('status' => 'error', 'message' => "Rut no valido", 'code' => '2');
            } elseif ($lstrRut == "") {
                return array('status' => 'error', 'message' => "El campo Identificacion es requerido", 'code' => '3');
            }
        }else{
            if ($lstrRut == "") {
                return array('status' => 'error', 'message' => "El campo Identificacion es requerido", 'code' => '3');
            }
        }

        //buscamos el rut en la base de datos de personas
        $lobjPersonas = \DB::table('tbl_personas')
        ->where('RUT', $lstrRut)
        ->get();

        //asignamos datos de la persona
        $lstrNombres = isset($larrFormato['NOMBRES']) ? trim($pobjPHPExcel[$larrFormato['NOMBRES']]) : '';
        if ($lstrNombres == "") {
            return array('status' => 'error', 'message' => "El nombre de la personas no puede ser vacio", 'code' => '12');
        }
        $lstrApellidos = isset($larrFormato['APELLIDOS']) ? trim($pobjPHPExcel[$larrFormato['APELLIDOS']]) : '';
        //Valida que los Apellidos sean obligatorios
        if ($lstrApellidos == "") {
            return array('status' => 'error', 'message' => "Los Apellidos de las personas no pueden ser vacios", 'code' => '3');
        }
        $lstrDireccion = isset($larrFormato['DIRECCION']) ? trim($pobjPHPExcel[$larrFormato['DIRECCION']]) : '';

        //asignamos fecha de nacimiento
        $ldatFechaNacimiento = isset($larrFormato['FECHANACIMIENTO']) ? trim($pobjPHPExcel[$larrFormato['FECHANACIMIENTO']]) : '';
        if ($ldatFechaNacimiento) {
    	    $edadCumplida = 0;
	        $now = new DateTime();
	        $nac = new DateTime();
	        $nac = DateTime::createFromFormat('d-m-Y',$ldatFechaNacimiento);
	        $edadCumplida = $now->diff($nac)->y;
	        if ( $edadCumplida < 18) {
	            return array('status' => 'error', 'message' => "La persona es menor de edad", 'code' => '3');
	        }
            try {
                $ldatFechaNacimiento = $nac->format('Y-m-d');
            } catch (Exception $e) {
                return array('status' => 'error', 'message' => "La fecha de nacimiento no es correcta (dd-mm-yyyy)", 'code' => '4');
            }
        }else{
	        return array('status' => 'error', 'message' => "La fecha de nacimiento es requerida", 'code' => '3');
        }

        //asignamos el sexo
        $lstrSexo = isset($larrFormato['SEXO']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['SEXO']])) : '';
        if ($lstrSexo) {
            if ($lstrSexo == "HOMBRE") {
                $lintSexo = 1;
            } elseif ($lstrSexo == "MUJER") {
                $lintSexo = 2;
            } else {
                return array('status' => 'error', 'message' => "Error en formato de sexo", 'code' => '5');
            }
        } else {
            return array('status' => 'error', 'message' => "El campo sexo es requerido", 'code' => '9');
        }

        //asignamos la nacionalidad
        $lstrNacionalidad = isset($larrFormato['NACIONALIDAD']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['NACIONALIDAD']])) : '';
        if ($lstrNacionalidad) {
            $lobjNacionalidad = \DB::table('tbl_nacionalidad')->select('id_Nac')->where('nacionalidad', 'like', $lstrNacionalidad)->get();
            if ($lobjNacionalidad) {
                $lintIdNacionalidad = $lobjNacionalidad[0]->id_Nac;
            } else {
                return array('status' => 'error', 'message' => "Error en formato de nacionalidad, no existe", 'code' => '10');
            }
        } else {
            $lintIdNacionalidad = "";
        }

        //asignamos el estado civil
        $lstrEstadoCivil = isset($larrFormato['ESTADOCIVIL']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['ESTADOCIVIL']])) : '';
        if ($lstrEstadoCivil) {
            if ($lstrEstadoCivil == "SOLTERO") {
                $lintEstadoCivil = 1;
            } elseif ($lstrEstadoCivil == "CASADO") {
                $lintEstadoCivil = 2;
            } elseif ($lstrEstadoCivil == "DIVORCIADO") {
                $lintEstadoCivil = 3;
            } elseif ($lstrEstadoCivil == "VIUDO") {
                $lintEstadoCivil = 4;
            } else {
                return array('status' => 'error', 'message' => "Error en formato de estado civil", 'code' => '6');
            }
        } else {
            $lintEstadoCivil = "";
        }

        //Asignamos el contrato
        if (array_key_exists($larrFormato['CONTRATO'], $pobjPHPExcel)) {
            $lstrContrato = trim($pobjPHPExcel[$larrFormato['CONTRATO']]);
            if ($lstrContrato) {
                $lobjContrato = \DB::table('tbl_contrato')->select('contrato_id', 'IdContratista', 'entry_by_access')->where('cont_numero', $lstrContrato)->get();
                if (!$lobjContrato) {
                    return array('status' => 'error', 'message' => "El contrato no existe", 'code' => '7');
                } else {
                    if ($pintLevelUser != 6) {
                        $lintIdUserAccess = $lobjContrato[0]->entry_by_access;
                    }
                }
            } else {
                $lobjContrato = "";
            }
        }

        if ($lobjPersonas) {
            if (!empty($lobjPersonas[0]->entry_by_access)) {
                if ($lobjPersonas[0]->entry_by_access != $lintIdUserAccess) {
                    return array('status' => 'error', 'message' => "Esta persona ya se encuentra asociada a otro usuario", 'code' => '11');
                }
            }
        }

        $ldatFechaInicio = isset($larrFormato['FECHAINICIO']) ? trim($pobjPHPExcel[$larrFormato['FECHAINICIO']]) : '';
        if ($ldatFechaInicio) {
            $edadCumplida = 0;
            $now = new DateTime();
            $nac = new DateTime();
            $ldatFechaIniciodat = DateTime::createFromFormat('d-m-Y',$ldatFechaInicio);
            try {
                $ldatFechaInicio = $ldatFechaIniciodat->format('Y-m-d');
            } catch (Exception $e) {
                if ($lobjContrato) {
                    return array('status' => 'error', 'message' => "La fecha de inicio no es correcta (dd-mm-yyyy)", 'code' => '4');
                }
            }
        }else{
            if ($lobjContrato) {
                return array('status' => 'error', 'message' => "La fecha de inicio es requerida", 'code' => '3');
            }
        }

        //Asignamos el rol
        if (array_key_exists($larrFormato['ROL'], $pobjPHPExcel)) {
            $lstrRol = strtoupper(trim($pobjPHPExcel[$larrFormato['ROL']]));
            if ($lstrRol) {
                $lobjRol = \DB::table('tbl_roles')->select('IdRol')->where('Descripción', 'like', $lstrRol)->get();
                if ($lobjRol) {
                    $lintIdRol = $lobjRol[0]->IdRol;
                } else {
                    return array('status' => 'error', 'message' => "El rol no existe", 'code' => '8');
                }
            }
        }

        $larrDataPersona["IdTipoIdentificacion"] = $lintTipoIdentificacion;
        $larrDataPersona["RUT"] = $lstrRut;
        $larrDataPersona["Nombres"] = $lstrNombres;
        if ($lstrApellidos != "") {
            $larrDataPersona["Apellidos"] = $lstrApellidos;
        }
        if ($lstrDireccion != "") {
            $larrDataPersona["Direccion"] = $lstrDireccion;
        }
        if ($ldatFechaNacimiento != "") {
            $larrDataPersona["FechaNacimiento"] = $ldatFechaNacimiento;
        }
        $larrDataPersona["Sexo"] = $lintSexo;
        if ($lintIdNacionalidad != "") {
            $larrDataPersona["id_Nac"] = $lintIdNacionalidad;
        }
        if ($lintEstadoCivil != "") {
            $larrDataPersona["EstadoCivil"] = $lintEstadoCivil;
        }
        $larrDataPersona["IdEstatus"] = 1;
        $larrDataPersona["entry_by"] = $pintIdUserLogin;
        $larrDataPersona["entry_by_access"] = $lintIdUserAccess;
        $larrDataPersona["updatedOn"] = date('Y-m-d');

        if ($lobjPersonas) {
            $lintIdPersona = $lobjPersonas[0]->IdPersona;
            //Consultamos si esta persona ya se encuentra vinculada al contrato para no duplicar los datos
            if ($lobjContrato) {
                $lintIdContratista = $lobjContrato[0]->IdContratista;
                $lintIdContrato = $lobjContrato[0]->contrato_id;
                $lobjContratosPersonas = \DB::table('tbl_contratos_personas')
                        ->where('IdPersona', $lintIdPersona)
                        ->get();
                if ($lobjContratosPersonas) {
                    if ($lobjContratosPersonas[0]->contrato_id != $lintIdContrato) {
                        return array('status' => 'error', 'message' => "La persona ya se encuentra asignada a otro contrato", 'code' => '9');
                    }
                }
            }
            \DB::table('tbl_personas')->where("IdPersona", "=", $lintIdPersona)->update($larrDataPersona);
            $larrResultado["modificados"] = 1;
        } else {
            if ($lintIdNacionalidad == "") {
                $larrDataPersona["id_Nac"] = 22;
            }
            $larrDataPersona["createdOn"] = date('Y-m-d');
            $lintIdPersona = \DB::table('tbl_personas')->insertGetId($larrDataPersona);
            $larrResultado["nuevos"] = 1;
        }


        if ($lobjContrato) {
            if (!$lobjContratosPersonas) {
                $lintIdContrato = $lobjContrato[0]->contrato_id;

                $SubCont = \MyPeoples::EsSubcontratista($lintIdContrato);
                if ( $SubCont>0){
                    //Verificamos que si es un subcontratista la carta de aprobacion este aprobada
                    $lobjCartaAprobacion = \DB::table("tbl_documentos")
                        ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento", "=", "tbl_tipos_documentos.IdTipoDocumento")
                        ->where("tbl_documentos.Entidad","=","9")
                        ->where("tbl_documentos.IdEntidad","=",$SubCont)
                        ->where("tbl_documentos.contrato_id","=",$lintIdContrato)
                        ->where("tbl_tipos_documentos.IdProceso","=",89)
                        ->where("tbl_documentos.IdEstatus","!=",5)
                        ->get();

                    if ($lobjCartaAprobacion){
                        return array("status" => "error", "message"=>"El subcontatista no tiene la carta de aceptación aprobada", "result"=>$lintIdContrato);
                    }
                }

                $lintIdcontratis = \MyPeoples::BuscaContratista($lintIdContrato);
                if ($lintIdcontratis){
                    $lintIdcontratis = $lintIdcontratis[0];
                }

                if ($lintIdRol){
                    \MyPeoples::RestoreHistorico($lintIdContrato,$lintIdPersona,$lintIdRol);
                }

                $larrResultadoPersonas = \MyPeoples::AssignContract($lintIdContrato, $lintIdPersona, $lintIdRol,0,$ldatFechaInicio);

            }
        }

        return array('status' => 'success', 'message' => "", 'code' => '1', 'result' => $larrResultado);
    }

    static private function LoadBatchContracts($pobjPHPExcel, $pintLevelUser, $pintIdUserLogin) {

        $larrResultado = array("nuevos" => 0,
            "modificados" => 0);

        $lintIdUserAccess = $pintIdUserLogin;

        $larrFormato = self::$parrFormat;

        //validamos rut
        $lstrRut = \MySourcing::FormatRut($pobjPHPExcel[$larrFormato['RUT']]);
        if (!\MySourcing::ValidateRut($lstrRut)) {
            return array('status' => 'error', 'message' => "Rut no valido", 'code' => '2');
        } elseif ($lstrRut == "") {
            return array('status' => 'error', 'message' => "El campo RUT es requerido", 'code' => '3');
        }

        //buscamos el rut en la base de datos de contratistas
        $lobjContratista = \DB::table('tbl_contratistas')->where('RUT', $lstrRut)->get();
        if ($lobjContratista) {
            $lintIdContratista = $lobjContratista[0]->IdContratista;
            $lintIdUserAccess = $lobjContratista[0]->entry_by_access;
        } else {
            return array('status' => 'error', 'message' => "El contratista no existe en el sistema", 'code' => '4');
        }

        //asignamos datos de la contratos
        $lstrNumero = isset($larrFormato['NUMERO']) ? trim($pobjPHPExcel[$larrFormato['NUMERO']]) : '';
        if ($lstrNumero != "") {
            $lobjContrato = \DB::table('tbl_contrato')->where('cont_numero', $lstrNumero)->get();
            if ($lobjContrato) {
                return array('status' => 'error', 'message' => "El número de contrato ya existe en el sistema", 'code' => '13');
            }
        } else {
            return array('status' => 'error', 'message' => "El número de contrato es un campo requerido", 'code' => '12');
        }

        //asignamos fecha de inicio
        $ldatFechaInicio = isset($larrFormato['FECHAINICIO']) ? trim($pobjPHPExcel[$larrFormato['FECHAINICIO']]) : '';
        if ($ldatFechaInicio) {
            try {
                $ldatFechaInicio = \DateTime::createFromFormat("m-d-y", $ldatFechaInicio);
                if ($ldatFechaInicio) {
                    $ldatFechaInicio = $ldatFechaInicio->format('Y-m-d');
                }
            } catch (Exception $e) {
                return array('status' => 'error', 'message' => "La fecha inicio no es correcta (dd-mm-yyyy)", 'code' => '5');
            }
        } else {
            return array('status' => 'error', 'message' => "La fecha inicio es requerida", 'code' => '6');
        }

        //asignamos fecha de fin
        $ldatFechaFin = isset($larrFormato['FECHAFIN']) ? trim($pobjPHPExcel[$larrFormato['FECHAFIN']]) : '';
        if ($ldatFechaFin) {
            try {
                $ldatFechaFin = \DateTime::createFromFormat("m-d-Y", $ldatFechaFin);
                if ($ldatFechaFin) {
                    $ldatFechaFin = $ldatFechaFin->format('Y-m-d');
                }
            } catch (Exception $e) {
                return array('status' => 'error', 'message' => "La fecha fin no es correcta (dd-mm-yyyy)", 'code' => '7');
            }
        } else {
            return array('status' => 'error', 'message' => "La fecha fin es requerida", 'code' => '8');
        }

        //asignamos el monto
        $lintMonto = isset($larrFormato['MONTO']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['MONTO']])) : '';
        if ($lintMonto != "") {
            if (!is_numeric($lintMonto)) {
                return array('status' => 'error', 'message' => "El formato del campo monto debe ser numérico", 'code' => '10');
            }
        }

        //asignamos el control de reporte
        $lstrControlaReporte = isset($larrFormato['REPORTE']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['REPORTE']])) : '';
        if ($lstrControlaReporte) {
            if (strtoupper($lstrControlaReporte) == "SI") {
                $lintIdControlaReporte = 1;
            } elseif (strtoupper($lstrControlaReporte) == "NO") {
                $lintIdControlaReporte = 0;
            } else {
                return array('status' => 'error', 'message' => "Error en el campo Reporte, no existe en el sistema", 'code' => '13');
            }
        } else {
            return array('status' => 'error', 'message' => "El campo Reporte es un campo requerido", 'code' => '11');
        }

        //asignamos la extension
        $lstrExtension = isset($larrFormato['EXTENSION']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['EXTENSION']])) : '';
        $lstrExtension = trim($lstrExtension);
        if ($lstrExtension) {
            $lstrExtension = strtoupper($lstrExtension);
            if ($lstrExtension == "PERMANENTE") {
                $lintIdExtension = 1;
            } elseif ($lstrExtension == "NO PERMANENTE") {
                $lintIdExtension = 2;
            } elseif ($lstrExtension == "OTM") {
                $lintIdExtension = 3;
            } else {
                return array('status' => 'error', 'message' => "Error en formato de extension, no existe en el sistema", 'code' => '6');
            }
        } else {
            return array('status' => 'error', 'message' => "La extensión es un campo requerido", 'code' => '11');
        }

        $larrDataContrato = array("IdContratista" => $lintIdContratista,
            "cont_numero" => $lstrNumero,
            "cont_fechaInicio" => $ldatFechaInicio,
            "cont_fechaFin" => $ldatFechaFin,
            "cont_montoTotal" => $lintMonto,
            "controlareporte" => $lintIdControlaReporte,
            "entry_by" => $lintIdControlaReporte,
            "entry_by_access" => $pintIdUserLogin,
            "cont_estado" => 1,
            "id_extension" => $lintIdExtension);

        $larrDataContrato["createdOn"] = date('Y-m-d');
        $lintIdContrato = \DB::table('tbl_contrato')->insertGetId($larrDataContrato);
        $larrResultado["nuevos"] = 1;

        return array('status' => 'success', 'message' => "", 'code' => '1', 'result' => $larrResultado);
    }

    static private function LoadBatchContractors($pobjPHPExcel, $pintLevelUser, $pintIdUserLogin) {

        //Datos iniciales
        $larrResultado = array("nuevos" => 0,
            "modificados" => 0);
        $larrDatosContratistas = array();

        $larrFormato = self::$parrFormat;

        //validamos rut
        $lstrRut = \MySourcing::FormatRut($pobjPHPExcel[$larrFormato['RUT']]);
        if (!\MySourcing::ValidateRut($lstrRut)) {
            return array('status' => 'error', 'message' => "Rut no valido", 'code' => '2');
        } elseif ($lstrRut == "") {
            return array('status' => 'error', 'message' => "El campo RUT es requerido", 'code' => '3');
        }

        //Pasamos los valores
        $lobjContratistas = \DB::table('tbl_contratistas')->where('RUT', $lstrRut)->get();

        if ($lobjContratistas) {
            return array('status' => 'error', 'message' => "El contratista ya existe", 'code' => '4');
        }

        $larrDatosContratistas["RUT"] = $lstrRut;
        $larrDatosContratistas["RazonSocial"] = isset($larrFormato['RAZONSOCIAL']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['RAZONSOCIAL']])) : '';
        $larrDatosContratistas["NombreFantasia"] = isset($larrFormato['NOMBREFANTASIA']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['NOMBREFANTASIA']])) : '';
        $larrDatosContratistas["Representante"] = isset($larrFormato['REPRESENTANTE']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['REPRESENTANTE']])) : '';
        $larrDatosContratistas["RepresentanteFono"] = isset($larrFormato['REPRESENTANTEFONO']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['REPRESENTANTEFONO']])) : '';
        $larrDatosContratistas["RepresentanteEmail"] = isset($larrFormato['REPRESENTANTEEMAIL']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['REPRESENTANTEEMAIL']])) : '';
        $larrDatosContratistas["Direccion"] = isset($larrFormato['DIRECCION']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['DIRECCION']])) : '';
        $larrDatosContratistas["Fono"] = isset($larrFormato['FONOEMPRESA']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['FONOEMPRESA']])) : '';
        $larrDatosContratistas["Email"] = isset($larrFormato['EMAILEMPRESA']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['EMAILEMPRESA']])) : '';
        $larrDatosContratistas["PaginaWeb"] = isset($larrFormato['PAGINAWEB']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['PAGINAWEB']])) : '';
        $larrDatosContratistas["entry_by"] = $pintIdUserLogin;
        $larrDatosContratistas["entry_by_access"] = $pintIdUserLogin;
        $larrDatosContratistas["IdEstatus"] = 1;
        $larrDatosContratistas["createdOn"] = date('Y-m-d');

        $lintIdContrato = \DB::table('tbl_contratistas')->insertGetId($larrDatosContratistas);

        $larrResultado["nuevos"] = 1;

        return array('status' => 'success', 'message' => "", 'code' => '1', 'result' => $larrResultado);
    }

    static private function LoadBatchCharla($pobjPHPExcel, $pintLevelUser, $pintIdUserLogin) {

        //Datos iniciales
        $ldatFechaActual = date('Y-m-d h:i:s');
        $larrResultado = array("nuevos" => 0, "modificados" => 0);
        $larrDatosContratistas = array();
        $lintIdUser = \Session::get('uid');

        $larrFormato = self::$parrFormat;

        $lintTipoIdentificacion = 0;
        $lstrTipoIdentificacion = isset($larrFormato['TIPOIDENTIFICACION']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['TIPOIDENTIFICACION']])) : '';
        if ($lstrTipoIdentificacion) {
            $lobjTipoIdentificacion = \DB::table('tbl_tipos_identificacion')->select('IdTipoIdentificacion','Descripcion')->where('Descripcion', $lstrTipoIdentificacion)->get();
            if (!$lobjTipoIdentificacion) {
                return array('status' => 'error', 'message' => "El tipo de identificacion no existe", 'code' => '7');
            } else {
                $lintTipoIdentificacion = $lobjTipoIdentificacion[0]->IdTipoIdentificacion;
            }
        } else {
            return array('status' => 'error', 'message' => "El tipo de identificacion no puede ser vacio", 'code' => '10');
        }

        //validamos rut
        $lstrRut = isset($larrFormato['IDENTIFICACION']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['IDENTIFICACION']])) : '';
        if ($lintTipoIdentificacion == 1){
        $lstrRut = \MySourcing::FormatRut($lstrRut);
        if (!\MySourcing::ValidateRut($lstrRut)) {
            return array('status' => 'error', 'message' => "Rut no valido", 'code' => '2');
        } elseif ($lstrRut == "") {
            return array('status' => 'error', 'message' => "El campo de identificacion es requerido", 'code' => '3');
        }
        }else{
            if ($lstrRut == "") {
                return array('status' => 'error', 'message' => "El campo de identificacion es requerido", 'code' => '3');
            }
        }

        $ldatFechaAsistencia = isset($larrFormato['FECHAASISTENCIA']) ? trim($pobjPHPExcel[$larrFormato['FECHAASISTENCIA']]) : '';
        if ($ldatFechaAsistencia!="") {
            try {
                $ldatFechaAsistencia = \DateTime::createFromFormat("d-m-Y", $ldatFechaAsistencia);
                if ($ldatFechaAsistencia){
                    $ldatFechaAsistencia = $ldatFechaAsistencia->format('Y-m-d');
                }else{
                    return array('status' => 'error', 'message' => "La fecha asistencia no es correcta (dd-mm-yyyy) " . $e->getMessage(), 'code' => '4');
                }
            } catch (Exception $e) {
                return array('status' => 'error', 'message' => "La fecha asistencia no es correcta (dd-mm-yyyy) ", 'code' => '4');
            }
        } else {
            return array('status' => 'error', 'message' => "La fecha asistencia es requerida", 'code' => '5');
        }

        $lintIdAccion = 1; //partimos de que crearemos un nuevo registro

        //Le sumamos dos años a la fecha de emision de la charla
        $ldatFechaVencimiento = date('Y-m-d', strtotime('+2 year', strtotime($ldatFechaAsistencia)));

        //Hacemos un backup
        $lobjExist = \DB::table('tbl_charla_seguridad')
        ->where('tbl_charla_seguridad.IdTipoIdentificacion','=',$lintTipoIdentificacion)
        ->where('tbl_charla_seguridad.RUT','=',$lstrRut)
        ->first();

        if ($lobjExist){

            if ($ldatFechaVencimiento>$lobjExist->FechaVencimiento){
                \DB::table('tbl_charla_seguridad')
                ->where('tbl_charla_seguridad.id','=',$lobjExist->id)
                ->update(['FechaAsistencia'=>$ldatFechaAsistencia, "FechaVencimiento"=> $ldatFechaVencimiento, "updated_at"=>date('Y-m-d H:i:s')]);
                $lintIdAccion = 2;
            }

        }else{

            $larrDataCharla = array("IdTipoIdentificacion"=>$lintTipoIdentificacion,
                                    "RUT"=>$lstrRut,
                                    "FechaAsistencia"=>$ldatFechaAsistencia,
                                    "FechaVencimiento"=>$ldatFechaVencimiento,
                                    "entry_by"=>$lintIdUser,
                                    "created_at" => date('Y-m-d H:i:s'),
                                    "updated_at" => date('Y-m-d H:i:s')
                                    );
            \DB::table('tbl_charla_seguridad')
            ->insert($larrDataCharla);

        }

        $lobjPersonas = \DB::table('tbl_personas')
                ->leftJoin("tbl_documentos", "tbl_documentos.IdEntidad", "=", \DB::raw(" tbl_personas.IdPersona AND tbl_documentos.entidad = 3 AND tbl_documentos.IdTipoDocumento = 6"))
                ->select("tbl_personas.IdPersona", "tbl_documentos.IdEstatus")
                ->where('RUT', $lstrRut)
                ->where('IdTipoIdentificacion',$lintTipoIdentificacion)
                ->get();

        if ($lobjPersonas) {
            $dataempleado['IdPersona'] = $lobjPersonas[0]->IdPersona;
            $lintIdCount = \DB::table("tbl_documentos")
                    ->where("IdTipoDocumento", 6)
                    ->where("IdEntidad", $lobjPersonas[0]->IdPersona)
                    ->update(array("IdEstatus" => "5",
                        "updatedOn" => $ldatFechaActual,
                        "FechaEmision" => $ldatFechaAsistencia,
                        "FechaVencimiento" => $ldatFechaVencimiento));
            if ($lintIdCount){
                $lintIdCount = 2;
            }
        }

        if ($lintIdAccion == 1) {
            $larrResultado["nuevos"] = 1;
        } else {
            $larrResultado["modificados"] = 1;
        }

        return array('status' => 'success', 'message' => "", 'code' => '1', 'result' => $larrResultado);
    }

    static private function LoadBatchSituacionTrib($pobjPHPExcel, $pintLevelUser, $pintIdUserLogin) {
        //Datos iniciales
        $larrResultado = array("nuevos" => 0, "modificados" => 0);
        $larrFormato = self::$parrFormat;
        //informacion en formato Excel
        $st_RUT = \MySourcing::FormatRut($pobjPHPExcel[$larrFormato['RUT']]);
        $st_obs_sii = isset($larrFormato['OBSERVACIONES_SII']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['OBSERVACIONES_SII']])) : '';
        $st_evalu = isset($larrFormato['EVALUACION']) ? strtoupper(trim($pobjPHPExcel[$larrFormato['EVALUACION']])) : '';
        if (strlen(trim($st_evalu))<1){
            return array('status' => 'error', 'message' => "El campo EVALUACION es requerido", 'code' => '6');
        }

        //validamos si el registro corresponde al periodo
        $st_periodo = $pobjPHPExcel[$larrFormato['PERIODO']];
        if ($st_periodo == '') {
            return array('status' => 'error', 'message' => "El campo PERIODO es requerido", 'code' => '2');
        }

        //validamos rut
        $lstrRut = $st_RUT;
        if (!\MySourcing::ValidateRut($lstrRut)) {
            return array('status' => 'error', 'message' => "RUT no valido", 'code' => '5');
        } elseif ($lstrRut == "") {
            return array('status' => 'error', 'message' => "El campo RUT es requerido", 'code' => '4');
        }
        //Obtiene los contratos vigentes para el Contratistas
        $contratistasConContrato = \DB::getFetchMode();
        \DB::setFetchMode(\PDO::FETCH_ASSOC);
        $lobjQuery = \DB::table("tbl_contrato")
                ->select(\DB::raw("RUT as RUT"), \DB::raw("contrato_id as CONTRATO")
                )
                ->leftJoin('tbl_contratistas', 'tbl_contratistas.IdContratista', '=', 'tbl_contrato.IdContratista')
                ->where('RUT', '=', $lstrRut)
                ->whereRaw('tbl_contrato.id_extension = 1')
                ->whereRaw("tbl_contrato.cont_fechaFin > CONCAT(DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -1 MONTH),'%Y-%m'),'-01')")
                ->whereRaw("tbl_contrato.cont_fechaInicio <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL -1 MONTH))")
                ->get();
        \DB::setFetchMode($contratistasConContrato);
        foreach ($lobjQuery as $st_contratos) {
            //Contratista (RUT) valido, buscando registro en tbl_contratista_tributario
            $fetchMode = \DB::getFetchMode();
            \DB::setFetchMode(\PDO::FETCH_ASSOC);
            $lobjQuery2 = \DB::table("tbl_contratista_tributario")
                    ->select(\DB::raw("tributarioContratista"), \DB::raw("CONCAT(DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -1 MONTH),'%Y-%m'),'-01') as tFecha")
                    )
                    ->where('contrato_id', $st_contratos['CONTRATO'])
                    ->where('tCont_fecha', date('Y-m-01', mktime(0, 0, 0, date('m')-1, 1, date('Y') )))
                    ->get();
            if (count($lobjQuery2) > 0) {
                //Actualiza registro
                $larrDatosContratistaTributario['contrato_id'] = $lobjQuery[0]['CONTRATO'];
                $larrDatosContratistaTributario['tCont_fecha'] = $st_periodo;
                $larrDatosContratistaTributario['tCont_Obs_SII'] = $st_obs_sii;
                $larrDatosContratistaTributario['tCont_eval'] = $st_evalu;
                $lintIdContrato = \DB::table('tbl_contratista_tributario')
                        ->where('tributarioContratista', $lobjQuery2[0]['tributarioContratista'])
                        ->update([
                    'tCont_Obs_SII' => $st_obs_sii,
                    'tCont_eval' => $st_evalu
                ]);
                $larrResultado["modificados"] = 1;
            } else {
                //Inserta registro
                $larrDatosContratistaTributario['contrato_id'] = $st_contratos['CONTRATO'];
                $larrDatosContratistaTributario['tCont_fecha'] =  $st_periodo;
                $larrDatosContratistaTributario['tCont_Obs_SII'] = $st_obs_sii;
                $larrDatosContratistaTributario['tCont_eval'] = $st_evalu;
                $lintIdContrato = \DB::table('tbl_contratista_tributario')->insertGetId($larrDatosContratistaTributario);
                $larrResultado["nuevos"] = 1;
            }
        }
        return array('status' => 'success', 'message' => "", 'code' => '1', 'result' => $larrResultado);
    }

   static  private function LoadBatchMultas($pobjPHPExcel, $pintLevelUser, $pintIdUserLogin){

    	//Datos iniciales
    	$larrResultado = array("nuevos"=>0,
    		                   "modificados"=>0);
    	$larrDatosContratistas = array();

    	$larrFormato = self::$parrFormat;

  	//validamos rut
  	$lstrRut = \MySourcing::FormatRut($pobjPHPExcel[$larrFormato['RUT']]);
  	if (!\MySourcing::ValidateRut($lstrRut)){
  		return array('status'=>'error', 'message'=>"Rut no valido", 'code'=> '2');
  	}elseif($lstrRut==""){
  		return array('status'=>'error', 'message'=>"El campo RUT es requerido", 'code'=> '3');
  	}

  	//Pasamos los valores
  	$lobjContratistas = \DB::table('tbl_contratistas')->where('RUT',$lstrRut)->first();

  	if (!$lobjContratistas){
  		return array('status'=>'error', 'message'=>"El contratista no existe", 'code'=> '4');
  	}

  	$lintIdContratista = $lobjContratistas->IdContratista;

  	$ldatFecha = isset($larrFormato['FECHA'])?trim($pobjPHPExcel[$larrFormato['FECHA']]):'';
      if ($ldatFecha){
        try {
          $ldatFecha = \DateTime::createFromFormat("d-m-Y", $ldatFecha);
          if ($ldatFecha){
            $ldatFecha =  $ldatFecha->format('Y-m-d');
          }
        } catch (Exception $e) {
          return array('status'=>'error', 'message'=>"La fecha no es correcta (dd-mm-yyyy) ".$e->getMessage(), 'code'=> '4');
        }
      }else{
        return array('status'=>'error', 'message'=>"La fecha es requerida", 'code'=> '5');
      }

  	$lstrTipo = isset($larrFormato['SITUACION'])?strtoupper(trim($pobjPHPExcel[$larrFormato['SITUACION']])):'';
  	if ($lstrTipo){
  		if ($lstrTipo=="SIN MULTAS"){
  			$lintIdTipo = 0;
  		}elseif ($lstrTipo=="PROTESTOS"){
  			$lintIdTipo = 1;
  		}elseif ($lstrTipo=="INCUMPLIMIENTOS"){
  			$lintIdTipo = 2;
  		}elseif ($lstrTipo=="MULTAS"){
  			$lintIdTipo = 3;
  		}else{
  			return array('status'=>'error', 'message'=>"Error en formato de tipo", 'code'=> '5');
  		}
  	}else{
  		return array('status'=>'error', 'message'=>"El tipo es requerido", 'code'=> '9');
  	}

  	if ($lintIdTipo){
  		$lintCantidad = isset($larrFormato['CANTIDAD'])?strtoupper(trim($pobjPHPExcel[$larrFormato['CANTIDAD']])):'';
  	    if ($lintCantidad!=""){
  		    if (!is_numeric($lintCantidad)){
  		        return array('status'=>'error', 'message'=>"El formato del campo cantidad debe ser numérico", 'code'=> '10');
  		    }
  	    }else{
  	    	return array('status'=>'error', 'message'=>"La cantidad es requerida", 'code'=> '10');
  	    }

  	    $lintMonto = isset($larrFormato['MONTO'])?strtoupper(trim($pobjPHPExcel[$larrFormato['MONTO']])):'';
  	    if ($lintMonto!=""){
  		    if (!is_numeric($lintMonto)){
  		        return array('status'=>'error', 'message'=>"El formato del campo monto debe ser numérico", 'code'=> '10');
  		    }
  	    }else{
  	    	return array('status'=>'error', 'message'=>"El monto es requerida", 'code'=> '10');
  	    }
      }else{
      	$lintCantidad = 0;
      	$lintMonto = 0;
      }

  	$larrDatosMultas["contrato_id"] = 0;
  	$larrDatosMultas["finCont_fecha"] = $ldatFecha;
  	$larrDatosMultas["finCont_tipoProblema"] = $lintIdTipo;
  	$larrDatosMultas["finCont_cantidad"] = $lintCantidad;
  	$larrDatosMultas["finCont_comentario"] = isset($larrFormato['COMENTARIOS'])?strtoupper(trim($pobjPHPExcel[$larrFormato['COMENTARIOS']])):'';
  	$larrDatosMultas["finCont_monto"] = $lintMonto;

  	$lobjContratos = \DB::table('tbl_contrato')
  	->select("tbl_contrato.contrato_id")
  	->where('tbl_contrato.IdContratista',"=",$lintIdContratista)
  	->whereRaw("tbl_contrato.cont_fechaFin >= '".$ldatFecha."'")
  	->whereRaw("tbl_contrato.cont_fechaInicio <= '".$ldatFecha."'")
  	->whereNotExists(function($query) use ($ldatFecha){
  		$query->select(\DB::raw(1))
  		->from('tbl_contratistacondicionfin')
  		->whereraw('tbl_contratistacondicionfin.contrato_id = tbl_contrato.contrato_id')
  		->whereraw("tbl_contratistacondicionfin.finCont_fecha = '".$ldatFecha."'");
  	})
  	->get();

  	foreach ($lobjContratos as $larrContratos) {
  		$larrDatosMultas["contrato_id"] = $larrContratos->contrato_id;
  		$lintIdContrato = \DB::table('tbl_contratistacondicionfin')->insertGetId($larrDatosMultas);
  	}

  	$larrResultado["nuevos"] = 1;

  	return array('status'=>'success', 'message'=>"", 'code'=> '1', 'result'=>$larrResultado);

  }

  static  private function LoadBatchMultasGeneral($pobjPHPExcel, $pintLevelUser, $pintIdUserLogin){

    //Datos iniciales
    $larrResultado = array("nuevos"=>0,
                           "modificados"=>0);
    $larrDatosContratistas = array();

    $larrFormato = self::$parrFormat;

    //validamos rut
    $lstrRut = \MySourcing::FormatRut($pobjPHPExcel[$larrFormato['IDENTIFICACION']]);
    if (!\MySourcing::ValidateRut($lstrRut)){
        return array('status'=>'error', 'message'=>"Identificación de empresa no válida", 'code'=> '2');
    }elseif($lstrRut==""){
        return array('status'=>'error', 'message'=>"El campo de Identificación es requerido", 'code'=> '3');
    }

    //Pasamos los valores
    $lobjContratistas = \DB::table('tbl_contratistas')->where('RUT',$lstrRut)->first();

    if (!$lobjContratistas){
        return array('status'=>'error', 'message'=>"El contratista no existe", 'code'=> '4');
    }

    $lintIdContratista = $lobjContratistas->IdContratista;

    $ldatFecha = isset($larrFormato['FECHA'])?trim($pobjPHPExcel[$larrFormato['FECHA']]):'';
    if ($ldatFecha){
      try {
        $ldatFecha = \DateTime::createFromFormat("d-m-Y", $ldatFecha);
        if ($ldatFecha){
          $ldatFecha =  $ldatFecha->format('Y-m-d');
        }
      } catch (Exception $e) {
        return array('status'=>'error', 'message'=>"La fecha no es correcta (dd-mm-yyyy) ".$e->getMessage(), 'code'=> '4');
      }
    }else{
      return array('status'=>'error', 'message'=>"La fecha es requerida", 'code'=> '5');
    }

    $lstrTipo = isset($larrFormato['SITUACION'])?strtoupper(trim($pobjPHPExcel[$larrFormato['SITUACION']])):'';
    if ($lstrTipo){
        if ($lstrTipo=="SIN MULTAS"){
            $lintIdTipo = 0;
        }elseif ($lstrTipo=="PROTESTOS"){
            $lintIdTipo = 1;
        }elseif ($lstrTipo=="INCUMPLIMIENTOS"){
            $lintIdTipo = 2;
        }elseif ($lstrTipo=="MULTAS"){
            $lintIdTipo = 3;
        }else{
            return array('status'=>'error', 'message'=>"Error en formato de tipo", 'code'=> '5');
        }
    }else{
        return array('status'=>'error', 'message'=>"El tipo es requerido", 'code'=> '9');
    }

    if ($lintIdTipo){
        $lintCantidad = isset($larrFormato['CANTIDAD'])?strtoupper(trim($pobjPHPExcel[$larrFormato['CANTIDAD']])):'';
        if ($lintCantidad!=""){
            if (!is_numeric($lintCantidad)){
                return array('status'=>'error', 'message'=>"El formato del campo cantidad debe ser numérico", 'code'=> '10');
            }
        }else{
            return array('status'=>'error', 'message'=>"La cantidad es requerida", 'code'=> '10');
        }

        $lintMonto = isset($larrFormato['MONTO'])?strtoupper(trim($pobjPHPExcel[$larrFormato['MONTO']])):'';
        if ($lintMonto!=""){
            if (!is_numeric($lintMonto)){
                return array('status'=>'error', 'message'=>"El formato del campo monto debe ser numérico", 'code'=> '10');
            }
        }else{
            return array('status'=>'error', 'message'=>"El monto es requerida", 'code'=> '10');
        }
    }else{
        $lintCantidad = 0;
        $lintMonto = 0;
    }

    $larrDatosMultas["contrato_id"] = 0;
    $larrDatosMultas["finCont_fecha"] = $ldatFecha;
    $larrDatosMultas["finCont_tipoProblema"] = $lintIdTipo;
    $larrDatosMultas["finCont_cantidad"] = $lintCantidad;
    $larrDatosMultas["finCont_comentario"] = isset($larrFormato['COMENTARIOS'])?strtoupper(trim($pobjPHPExcel[$larrFormato['COMENTARIOS']])):'';
    $larrDatosMultas["finCont_monto"] = $lintMonto;

    $lobjContratos = \DB::table('tbl_contrato')
    ->select("tbl_contrato.contrato_id")
    ->where('tbl_contrato.IdContratista',"=",$lintIdContratista)
    ->whereRaw("tbl_contrato.cont_fechaFin >= '".$ldatFecha."'")
    ->whereRaw("tbl_contrato.cont_fechaInicio <= '".$ldatFecha."'")
    ->whereNotExists(function($query) use ($ldatFecha){
        $query->select(\DB::raw(1))
        ->from('tbl_contratistacondicionfin')
        ->whereraw('tbl_contratistacondicionfin.contrato_id = tbl_contrato.contrato_id')
        ->whereraw("tbl_contratistacondicionfin.finCont_fecha = '".$ldatFecha."'");
    })
    ->get();

    foreach ($lobjContratos as $larrContratos) {
        $larrDatosMultas["contrato_id"] = $larrContratos->contrato_id;
        $lintIdContrato = \DB::table('tbl_contratistacondicionfin')->insertGetId($larrDatosMultas);
    }

    $larrResultado["nuevos"] = 1;

    return array('status'=>'success', 'message'=>"", 'code'=> '1', 'result'=>$larrResultado);

  }

    static private function ValidateColumns($pobjPHPExcel, $parrFormato) {
        foreach ($parrFormato as $key => $value) {
            if (!isset($pobjPHPExcel[$value])) {
                if ($key != 'RESULTADO') {
                    return false;
                }
            }
        }
        return true;
    }

    static private function ValidateColumnsCSV($pobjPHPExcel, $parrFormato) {
        $CSVcols = explode(';', $pobjPHPExcel['A']);
        if (count($CSVcols) != (count($parrFormato) - 1)) {
            return false;
        }
        return true;
    }

    static private function DocumentsDownload($lobjQuery, $larrFormat, $headers = true, $pintIdProceso = 0) {

        require_once '../app/Library/PHPExcel.php';
        $lobjPHPExcelResult = new \PHPExcel();
        $lobjExcelResult = \PHPExcel_IOFactory::createWriter($lobjPHPExcelResult, "Excel2007");
        $lobjSheet = $lobjPHPExcelResult->getActiveSheet();
        $lobjSheet->setTitle('Resultado');
        if ($headers) {
            foreach ($larrFormat as $key => $value) {
                if ($key != "RESULTADO") {
                    $lobjSheet->getCell($value . '1')->setValue($key);
                    $lobjSheet->getColumnDimension($value)->setAutoSize(true);
                    $lobjSheet->getStyle($value . '1')->applyFromArray(array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FFC000')
                        )
                            )
                    );
                }
            }
            $lintResultado = 1;
        } else {
            $lintResultado = 0;
        }
        foreach ($lobjQuery as $larrRejected) {
            $lintResultado += 1;
            foreach ($larrFormat as $key => $value) {
                if ($key != "RESULTADO") {
                    $lstrValue = $larrRejected[$key];
                    if ($key == "MONTO" && $pintIdProceso == 5) {
                        $lobjSheet->getCell($value . $lintResultado)->setValue("=D" . $lintResultado . "*H" . $lintResultado);
                    } else {
                        $lobjSheet->getCell($value . $lintResultado)->setValue($lstrValue);
                    }
                }
            }
        }
        if ($pintIdProceso == 6) {
            $lobjPHPExcelResult->createSheet();

        // Add some data to the second sheet, resembling some different data types
        $lobjPHPExcelResult->setActiveSheetIndex(1);
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('A1', 'CAMPO');
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('B1', 'TIPO');
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('C1', 'FORMATO');
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('D1', 'TAMAÑO (MAX)');
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('E1', 'DESCRIPCION');
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('F1', 'OBLIGATORIO');
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('G1', 'VALORES');

        //set up the style in an array
        $style = array('font' => array('size' => 10,'bold' => FALSE,'color' => array('rgb' => 'FFFFFF')));
        $lobjPHPExcelResult->getActiveSheet()->getStyle('A1:G1')->applyFromArray($style);

        for ($i = 'A'; $i != $lobjPHPExcelResult->getActiveSheet()->getHighestColumn(); $i++) {
            $lobjPHPExcelResult->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }
        $lobjPHPExcelResult->getActiveSheet()
         ->getStyle('A1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('000000');
        $lobjPHPExcelResult->getActiveSheet()
            ->getStyle('B1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('000000');
        $lobjPHPExcelResult->getActiveSheet()
            ->getStyle('C1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('000000');
        $lobjPHPExcelResult->getActiveSheet()
            ->getStyle('D1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('000000');
        $lobjPHPExcelResult->getActiveSheet()
            ->getStyle('E1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('000000');
        $lobjPHPExcelResult->getActiveSheet()
            ->getStyle('F1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('000000');
        $lobjPHPExcelResult->getActiveSheet()
            ->getStyle('G1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('000000');

        $lobjPHPExcelResult->getActiveSheet()->setCellValue('A2', 'EVALUACION *');
        $lobjPHPExcelResult->getActiveSheet()->getStyle('A2')->applyFromArray(
            array(
                'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'FFC000')
                )
            )
            );
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('B2', 'TEXTO');
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('D2', '5');
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('F2', 'SI');
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('G2', "BUENO\nMALO");
        $lobjPHPExcelResult->getActiveSheet()->getStyle('G2')->getAlignment()->setWrapText(true);
        $lobjPHPExcelResult->getActiveSheet()->getColumnDimension('G')->setWidth("50");
        $lobjPHPExcelResult->setActiveSheetIndex(1)
            ->mergeCells('A5:G5');
        $lobjPHPExcelResult->getActiveSheet()
            ->getCell('A5')
            ->setValue('This is the text that I want to see in the merged cells');
        $lobjPHPExcelResult->getActiveSheet()->setCellValue('A5', 'NOTA: Elimina todas las hojas excepto RESULTADO al momento de subir el archivo.');

        // Rename 2nd sheet
        $lobjPHPExcelResult->getActiveSheet()->setTitle('(FORMATO)');

        }
        $lstrDirectoryResult = self::$pstrDirectoryResult;
        $lintIdRand = rand(1000, 100000000);
        $lstrFullFileName = strtotime(date('Y-m-d H:i:s')) . '-' . $lintIdRand . '.';
        $lstrFullFileNameResult = "result-" . $lstrFullFileName . "xlsx";
        $lobjExcelResult->save($lstrDirectoryResult . $lstrFullFileNameResult);

        return $lstrFullFileNameResult;
    }

    static public function DocumentsMultas() {
        $pintIdProceso = 5;
        $fetchMode = \DB::getFetchMode();
        \DB::setFetchMode(\PDO::FETCH_ASSOC);
        $lobjQuery = \DB::table("tbl_contratistas")
                        ->select("tbl_contratistas.RUT",
				                 "tbl_contratistas.RAZONSOCIAL",
				                 \DB::raw("CONCAT('01-',DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -1 MONTH),'%m-%Y')) as FECHA"),
				                 \DB::raw("'' as UTM"),
				                 \DB::raw("'' as SITUACION"),
				                 \DB::raw("'' as CANTIDAD"),
				                 \DB::raw("'' as COMENTARIOS"),
				                 \DB::raw("'' as MONTOUTM"),
				                 \DB::raw("concat('=D','*H') as MONTO"),
				                 \DB::raw("'' as RESULTADO") )
                ->whereExists(function ($query) {
                    $query->select(\DB::raw(1))
                    ->from('tbl_contrato')
                    ->whereRaw('tbl_contrato.IdContratista = tbl_contratistas.IdContratista')
                    ->whereRaw('tbl_contrato.cont_fechaFin > CONCAT(DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -1 MONTH),\'%Y-%m\'),\'-01\')')
                    ->whereRaw('tbl_contrato.cont_fechaInicio <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL -1 MONTH))')
                    ->whereRaw('tbl_contrato.id_extension = 1');
                })
                ->get();
        \DB::setFetchMode($fetchMode);

        self::setFormat($pintIdProceso);
        $lstrDocument = self::DocumentsDownload($lobjQuery, self::$parrFormat);

        $headers = array('Content-Type: application/excell');
        return Response::download(self::$pstrDirectoryResult.$lstrDocument,$lstrDocument, $headers);
    }

    static public function DocumentsMultasGeneral() {
        $pintIdProceso = 17;
        $fetchMode = \DB::getFetchMode();
        \DB::setFetchMode(\PDO::FETCH_ASSOC);
        $lobjQuery = \DB::table("tbl_contratistas")
                        ->select(\DB::raw("tbl_contratistas.RUT as IDENTIFICACION"),
                                 "tbl_contratistas.RAZONSOCIAL",
                                 \DB::raw("CONCAT('01-',DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -1 MONTH),'%m-%Y')) as FECHA"),
                                 \DB::raw("'' as SITUACION"),
                                 \DB::raw("'' as CANTIDAD"),
                                 \DB::raw("'' as COMENTARIOS"),
                                 \DB::raw("'' as MONTO"),
                                 \DB::raw("'' as RESULTADO") )
                ->whereExists(function ($query) {
                    $query->select(\DB::raw(1))
                    ->from('tbl_contrato')
                    ->whereRaw('tbl_contrato.IdContratista = tbl_contratistas.IdContratista')
                    ->whereRaw('tbl_contrato.cont_fechaFin > CONCAT(DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -1 MONTH),\'%Y-%m\'),\'-01\')')
                    ->whereRaw('tbl_contrato.cont_fechaInicio <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL -1 MONTH))')
                    ->whereRaw('tbl_contrato.id_extension = 1');
                })
                ->get();
        \DB::setFetchMode($fetchMode);

        self::setFormat($pintIdProceso);
        $lstrDocument = self::DocumentsDownload($lobjQuery, self::$parrFormat);

        $headers = array('Content-Type: application/excell');
        return Response::download(self::$pstrDirectoryResult.$lstrDocument,$lstrDocument, $headers);
    }

    static public function ContratistasVigentes() {
        $pintIdProceso = 6;
        $fetchMode = \DB::getFetchMode();
        \DB::setFetchMode(\PDO::FETCH_ASSOC);
        $lobjQuery = \DB::table("tbl_contratistas")
                ->select(\DB::raw("RUT"), \DB::raw("razonsocial AS NOMBRE_CONTRATISTA"), \DB::raw("CONCAT(DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -1 MONTH),'%Y-%m'),'-01') as PERIODO"), \DB::raw("'' as OBSERVACIONES_SII"), \DB::raw("'' as EVALUACION"), \DB::raw("'' as RESULTADO")
                )
                ->whereExists(function($query) {
                    $query->select(\DB::raw(1))
                    ->from('tbl_contrato')
                    ->whereRaw('tbl_contratistas.IdContratista = tbl_contrato.IdContratista')
                    ->whereRaw('tbl_contrato.id_extension = 1')
                    ->whereRaw("tbl_contrato.cont_fechaFin > CONCAT(DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -1 MONTH),'%Y-%m'),'-01')")
                    ->whereRaw("tbl_contrato.cont_fechaInicio <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL -1 MONTH))");
                })
                ->get();
        \DB::setFetchMode($fetchMode);
        self::setFormat(6);
        $lstrDocument = self::DocumentsDownload($lobjQuery, self::$parrFormat, true, $pintIdProceso);

        $headers = array('Content-Type: application/excell');
        return Response::download(public_path("uploads\documents\\" . $lstrDocument), $lstrDocument, $headers);
    }

    static public function AccesosDiarios(){

        $lstrDirectoryResult = self::$pstrDirectoryResult;

        //include '../app/Library/PHPExcel/IOFactory.php';

        $larrFormato = array("CENTRO" => "A",
                "RUT" => "B",
                "NOMBRES" => "C",
                "APELLIDOS" => "D",
                "ROL" => "E",
                "NOMBRECONTRATO" => "F"
            );

        $lstrQuery = "select GROUP_CONCAT(distinct(tbl_centro.descripcion)) as CENTRO,
              	 tbl_accesos.data_rut as RUT,
              	 tbl_accesos.data_Nombres as NOMBRES,
              	 tbl_accesos.data_Apellidos as APELLIDOS,
              	 tbl_roles.Descripción as ROL,
              	 tbl_contratistas.RazonSocial as NOMBRECONTRATO
              from tbl_accesos
              inner join tbl_acceso_areas on tbl_accesos.IdAcceso = tbl_acceso_areas.IdAcceso
              left join tbl_personas on tbl_accesos.IdPersona = tbl_personas.IdPersona
              left join tbl_contratos_personas on tbl_contratos_personas.IdPersona = tbl_personas.IdPersona
              left join tbl_contrato on tbl_contrato.contrato_id = tbl_contratos_personas.contrato_id
              left join tbl_contratistas on tbl_contratistas.IdContratista = tbl_contrato.IdContratista
              left join tbl_roles on tbl_roles.IdRol = tbl_contratos_personas.IdRol
              inner join tbl_centro on tbl_acceso_areas.IdCentro = tbl_centro.IdCentro
              where tbl_acceso_areas.IdCentro in ( 4, 2, 5, 19, 8 )
              and tbl_accesos.FechaInicio <= current_date()
              and tbl_accesos.FechaFinal > current_date()
              and tbl_accesos.IdEstatusUsuario = 1
              group by tbl_accesos.data_rut , tbl_accesos.data_nombres, tbl_accesos.data_apellidos, tbl_roles.Descripción, tbl_contratistas.RazonSocial";
        $lobjQuery = \DB::select($lstrQuery);

        if ($lobjQuery) {
            $lobjPHPExcelResult = new \PHPExcel();
            $lobjExcelResult = \PHPExcel_IOFactory::createWriter($lobjPHPExcelResult, "Excel2007");
            $lobjSheet = $lobjPHPExcelResult->getActiveSheet();
            $lobjSheet->setTitle('Backup Accesos');

            //Guardamos el encabezado
            foreach ($larrFormato as $key => $value) {
                $lobjSheet->getCell($value . '1')->setValue($key);
                $lobjSheet->getColumnDimension($value)->setAutoSize(true);
                 $lobjSheet->getStyle($value . '1')->applyFromArray(array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFC000')
                    )
                        )
                );
            }

            $lintResultado = 1;
            foreach ($lobjQuery as $larrQuery) {
                $lintResultado += 1;
                foreach ($larrFormato as $key => $value) {
                    if ($key == 'FECHAHASTA'){
                        $lstrValue = \MyFormats::FormatDate($larrQuery->{$key});
                    }else{
                        $lstrValue = $larrQuery->{$key};
                    }
                    $lobjSheet->getCell($value.$lintResultado)->setValue($lstrValue);

                }
            }
           // base_path('vendor/bin');uploads/documents/
            $rand = rand(1000,100000000);
            $newfilename = strtotime(date('Y-m-d H:i:s')).'-'.$rand.'.xlsx';
            $Directory = public_path('uploads/documents/');
            $lobjExcelResult->save($Directory . $newfilename);

            $lstrArchivoEmail = $Directory . $newfilename;
            //$to = ["ddiaz@scpsoluciones.com","rsion@sourcing.cl"];
			$to = ["smaipu@koandina.com", "srenca@koandina.com", "seguridadcarlosvaldovinos@koandina.com", "scoquimbo@koandina.com", "santofagasta@koandina.com", "spuentealto@koandina.com", "seguridadrancagua@koandina.com"];
            //$cc = "asalazar@scpsoluciones.com";
			$cc = ["CCTV2@koandina.com", "CCTV1@koandina.com", "ivila@koandina.com", "gampuero@koandina.com", "rluna@koandina.com", "fceron@koandina.com", "terceros@koandina.com", "rsion@sourcing.cl", "ddiaz@sourcing.cl", "lulloa@koandina.com"];
            //$to = "fceron@koandina.com; ddiaz@sourcing.cl";
            $subject = "Backup de accesos ";
            $email = "Envío diario de backup de accesos";
            $data = array();
            if(CNF_MAIL =='swift')
            {
                \Mail::send("emails.accesos", $data, function ($message) use ($to, $cc, $subject, $lstrArchivoEmail) {
                    $message->to($to)
                            ->cc($cc)
                            ->subject($subject)
                            ->attach($lstrArchivoEmail);
                });
            }  else {
                //$message = view("user.emails.".$email, $data);
                $message = view("emails.accesos", $data)->render();
                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $headers .= 'From: '.CNF_APPNAME.' <'.CNF_EMAIL.'>' . "\r\n";
                mail($to, $subject, $message, $headers);
            }
        }

    }

    static public function LoadExcelABT($pobjFileLoad, $pintIdTipoDocumento = '', $IdDocumento) {
        $lstrDirectory = self::$pstrDirectory;
        $lstrDirectoryResult = self::$pstrDirectoryResult;
        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUserLogin = \Session::get('uid');

        $lstrFileName = $pobjFileLoad->getClientOriginalName();
        $lstrFileExtension = $pobjFileLoad->getClientOriginalExtension();
        $lintIdRand = rand(1000, 100000000);
        $lstrFullFileName = strtotime(date('Y-m-d H:i:s')) . '-' . $lintIdRand . '.';
        $lstrFullFileNameResult = "result-" . $lstrFullFileName . "xlsx";
        $lstrFullFileName = $lstrFullFileName . $lstrFileExtension;
        try {
            $lstrResultMove = $pobjFileLoad->move($lstrDirectory, $lstrFullFileName);
        } catch (Exception $e) {
            $lstrResultMove = "";
        }

        $larrResultRejected = array();
        $larrResult = array("Cargados" => 0,
            "Modificados" => 0,
            "Rechazados" => 0,
            "IdTipoDOcumento" => $pintIdTipoDocumento
        );
        if ($lstrResultMove) {
          //Abrimos el archivo
          include '../app/Library/PHPExcel/IOFactory.php';
          $lstrDirectoryFileName = $lstrDirectory . $lstrFullFileName;
          try {
              $inputFileType = PHPExcel_IOFactory::identify($lstrDirectoryFileName);
              $objReader = PHPExcel_IOFactory::createReader($inputFileType);
              $lobjPHPExcel = \PHPExcel_IOFactory::load($lstrDirectoryFileName);
          } catch (Exception $e) {
              return array('status' => 'success',
                  'message' => "Error abriendo excel " . $e->getMessage(),
                  'code' => '1'
              );
          }

          $larrPHPExcel = $lobjPHPExcel->getActiveSheet()->toArray(null, true, true, true);
          $lintCount = count($larrPHPExcel);
          $count=0;

          for($i=8;$i<=$lintCount;$i++){ \Log::info($larrPHPExcel[$i]);

            $run = trim($larrPHPExcel[$i]['C']);
            $Persona = \DB::table('tbl_personas')->where('RUT',$run)->first();
            if($Persona){
              \Log::info("persona encontrada");

              //\DB::table('tbl_personas_info_adicional')->insert()

              $count++;
            }

          }

          $lobjMyDocumentos = new MyDocuments($IdDocumento);
      		$lobjDocumento = $lobjMyDocumentos::getDatos();

          if (count($lobjDocumento->TipoDocumento->Aprobadores)){
            $IdEstatus = 2;
          }else{
            $IdEstatus = 5;
          }

          Documentos::where('IdDocumento',$IdDocumento)->update(['IdEstatus'=>$IdEstatus, 'DocumentoURL'=>$lstrFullFileName]);

          return array('status'=>'success', 'message'=>"Personas importadas ($count)", 'code'=> '1');

        }else{
          return array('status'=>'error', 'message'=>"Falla al importar el archivo", 'code'=> '3');
        }

    }

}

?>
