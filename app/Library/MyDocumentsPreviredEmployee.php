<?php

namespace App\Library;

use App\Models\Documentos;

class MyDocumentsPreviredEmployee extends MyDocuments {

    static protected $gobjDocumentoPadre;

    public function __construct($pintIdDocumento, $pobjDocumento = null, $pobjDocumentoPadre = null)
  	{
        parent::__construct($pintIdDocumento, $pobjDocumento);
        if (!$pobjDocumentoPadre){
            self::$gobjDocumentoPadre = self::Father();
        }else{
            self::$gobjDocumentoPadre = $pobjDocumentoPadre;
        }

    }

    //La función o relación de principal/detalle se puede refacturar luego
    static public function Father(){

        $ldatFechaPeriodo = self::$gobjDocumento->FechaEmision;
        $lintContratoId = self::$gobjDocumento->contrato_id;
        $lintIdProceso = self::getIdProceso('Previred');
        $lobjDocument = Documentos::whereHas('TipoDocumento',function($q) use ($lintIdProceso) {
                                        $q->where('IdProceso',$lintIdProceso);
                                     })
                        ->where('FechaEmision',$ldatFechaPeriodo)
                        ->where('IdEntidad',$lintContratoId)
                        ->where('Entidad',2)
                        ->first();
        return $lobjDocument;

    }

    static public function Crossing() {

        if (self::$gobjDocumentoPadre){

            if (self::$gobjDocumentoPadre->IdEstatus==5){

                //Recuperamos el rut de la persona relacionado con el documento
                $lstrRut = self::$gobjDocumento->Persona->RUT;

                //Validamos el documento con relación al padre
                $lobjAFP = \DB::table('afp_empresa')
                        ->join('afp_trabajador','afp_trabajador.afp_empresa_id','=','afp_empresa.id')
                                        ->where('afp_empresa.IdDocumento',self::$gobjDocumentoPadre->IdDocumento)
                                        ->where(\DB::raw("replace(afp_trabajador.rut,'.','')"),$lstrRut)
                                        ->get();

                $lobjMutual = \DB::table('mutual_empresa')
                        ->join('mutual_trabajador','mutual_trabajador.mutual_empresa_id','=','mutual_empresa.id')
                                        ->where('mutual_empresa.IdDocumento',self::$gobjDocumentoPadre->IdDocumento)
                                        ->where(\DB::raw("replace(mutual_trabajador.rut,'.','')"),$lstrRut)
                                            ->get();

                $lobjIpsMutual = \DB::table('ips_empresa')
                        ->join('ips_trabajador','ips_trabajador.ips_empresa_id','=','ips_empresa.id')
                                        ->where('ips_empresa.IdDocumento',self::$gobjDocumentoPadre->IdDocumento)
                                        ->where(\DB::raw("replace(ips_trabajador.rut,'.','')"),$lstrRut)
                                            ->get();

                $lobjFonasa = \DB::table('fonasa_empresa')
                        ->join('fonasa_trabajador','fonasa_trabajador.fonasa_empresa_id','=','fonasa_empresa.id')
                                        ->where('fonasa_empresa.IdDocumento',self::$gobjDocumentoPadre->IdDocumento)
                                        ->where(\DB::raw("replace(fonasa_trabajador.rut,'.','')"),$lstrRut)
                                            ->get();

                $lobjIsapre = \DB::table('isapre_empresa')
                        ->join('isapre_trabajador','isapre_trabajador.isapre_empresa_id','=','isapre_empresa.id')
                                        ->where('isapre_empresa.IdDocumento',self::$gobjDocumentoPadre->IdDocumento)
                                        ->where(\DB::raw("replace(isapre_trabajador.rut,'.','')"),$lstrRut)
                                            ->get();

                $lintAFP = 0;
                if ($lobjAFP) {
                $lintAFP = 1;
                }

                $lintSalud = 0;
                if ($lobjFonasa || $lobjIsapre) {
                $lintSalud = 1;
                }

                $lintMutual = 0;
                if ($lobjMutual || $lobjIpsMutual) {
                    $lintMutual = 1;
                }

                if ($lintAFP && $lintSalud && $lintMutual){
                self::Approve(1);
                }else{

                if ($lintMutual){
                self::Reject("El trabajador podría encontrarse pensionado, de ser así, cargue el comprobante.");
                }else{
                self::Reject("El trabajador no fue cargado en el archivo de previred del periodo");
                }

                }

            }else{
                return array("success"=>true, "code"=>3, "message"=>"El documento principal no se encuentra aprobado");
            }
        }else{
            return array("success"=>false, "code"=>2, "message"=>"No se encontró el documento principal para la relación de previred");
        }

    }

}

?>
