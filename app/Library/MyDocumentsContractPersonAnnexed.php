<?php

namespace App\Library;

use MyPeoples;
use \App\Models\Documentovalor;
use \App\Models\tiposcontratospersonas;
use \App\Models\Contratospersonas;
use \App\Models\Tipodocumentos;
use \App\Models\Tbldocumentosanexos;
use \App\Models\Documentoslog;
use DB;

class MyDocumentsContractPersonAnnexed extends MyDocuments {

	public function __construct($pintIdDocumento="") 
  	{
    	parent::__construct($pintIdDocumento);
	}

    static public function getCambios(){
        $larrResult = array();
        if (self::$gobjDocumento->Anexos){
            if (isset(self::$gobjDocumento->Anexos->contrato_id) && self::$gobjDocumento->Anexos->contrato_id) {
                $larrResult['contrato_id'] = self::$gobjDocumento->Anexos->contrato_id;
            }
            if (isset(self::$gobjDocumento->Anexos->IdTipoContrato) && self::$gobjDocumento->Anexos->IdTipoContrato) {
                $larrResult['IdTipoContrato'] = self::$gobjDocumento->Anexos->IdTipoContrato;
            }
            if (isset(self::$gobjDocumento->Anexos->FechaVencimiento) && self::$gobjDocumento->Anexos->FechaVencimiento && self::$gobjDocumento->Anexos->FechaVencimiento != '0000-00-00') {
                $larrResult['FechaVencimiento'] = self::$gobjDocumento->Anexos->FechaVencimiento;
            }
            if (isset(self::$gobjDocumento->Anexos->IdRol) && self::$gobjDocumento->Anexos->IdRol) {
                $larrResult['IdRol'] = self::$gobjDocumento->Anexos->IdRol;
            }

        }
        return $larrResult;
    }
	
	static public function Create($pobjPersona, $parrCambios){
        
        $lobjDocumento = \DB::table('tbl_documentos')
        ->select('tbl_documentos.*')
        ->join('tbl_tipos_documentos', 'tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
        ->where('tbl_documentos.entidad','=',3)
        ->where('tbl_documentos.identidad','=',$pobjPersona->IdPersona)
        ->where('tbl_tipos_documentos.IdProceso','=',21)
        ->first();
        
        if ($lobjDocumento){
            $lobjTipoDocumento = Tipodocumentos::Tipo(3)->first();
            if ($lobjTipoDocumento){
               $anexo = 1;
        	   $larrResult = parent::Save($lobjTipoDocumento->IdTipoDocumento, 3, $pobjPersona->IdPersona, $lobjDocumento->IdDocumento,$lobjDocumento->IdContratista, $lobjDocumento->contrato_id, $pdatFechaEmision = "", $anexo);
               if ($larrResult['status']=='success'){

                     //Guardamos los cambios que se generan en el anexo
                     $lobjDocumentosAnexos = new Tbldocumentosanexos();
                     $lobjDocumentosAnexos->fill($parrCambios);
                     self::$gobjDocumento->Anexos()->save($lobjDocumentosAnexos);
 
                     //Obtenemos los valores de la tabla tbl_tipo_documento_valor para asi poder insertar los datos de los anexos dependiendo de su tipo.
                     $larrIdTipoDocumentoValor = array();
                     $larrValor = array();
                     if (isset($parrCambios['contrato_id'])){
                         $valor = DB::table('tbl_tipo_documento_valor')->select('tbl_tipo_documento_valor.IdTipoDocumentoValor')->where('tbl_tipo_documento_valor.IdTipoDocumento',$lobjTipoDocumento->IdTipoDocumento)->where('tbl_tipo_documento_valor.Etiqueta','Contrato')->first();
                         $larrIdTipoDocumentoValor[] = $valor->IdTipoDocumentoValor;
                         $larrValor[] = 'Se cambia de '.$pobjPersona->Contratospersonas->Contrato->cont_numero.' a '.self::$gobjDocumento->Anexos->Contrato->cont_numero;
                     }
                     if (isset($parrCambios['IdTipoContrato'])){
                         $valor = DB::table('tbl_tipo_documento_valor')->select('tbl_tipo_documento_valor.IdTipoDocumentoValor')->where('tbl_tipo_documento_valor.IdTipoDocumento',$lobjTipoDocumento->IdTipoDocumento)->where('tbl_tipo_documento_valor.Etiqueta','Tipo de contrato')->first();
                         $larrIdTipoDocumentoValor[] = $valor->IdTipoDocumentoValor;
                         $larrValor[] = 'Se cambia de "'.$pobjPersona->Contratospersonas->TipoContrato->Nombre.'"" a "'.self::$gobjDocumento->Anexos->TipoContrato->Nombre.'"';
                     }
                     if (isset($parrCambios['FechaVencimiento']) && $parrCambios['FechaVencimiento']){
                         $valor = DB::table('tbl_tipo_documento_valor')->select('tbl_tipo_documento_valor.IdTipoDocumentoValor')->where('tbl_tipo_documento_valor.IdTipoDocumento',$lobjTipoDocumento->IdTipoDocumento)->where('tbl_tipo_documento_valor.Etiqueta','Fecha de Vencimiento')->first();
                         $larrIdTipoDocumentoValor[] = $valor->IdTipoDocumentoValor;
                         $larrValor[] = "Se extiende de ".\MyFormats::FormatDate($lobjDocumento->FechaVencimiento)." a ".\MyFormats::FormatDate($parrCambios['FechaVencimiento']);
                     }
                     if (isset($parrCambios['IdRol'])){
                         $valor = DB::table('tbl_tipo_documento_valor')->select('tbl_tipo_documento_valor.IdTipoDocumentoValor')->where('tbl_tipo_documento_valor.IdTipoDocumento',$lobjTipoDocumento->IdTipoDocumento)->where('tbl_tipo_documento_valor.Etiqueta','Rol')->first();
                         $larrIdTipoDocumentoValor[] = $valor->IdTipoDocumentoValor;
                         $larrValor[] = 'Se cambia de "'.$pobjPersona->Contratospersonas->Rol->Descripción.'" a "'.self::$gobjDocumento->Anexos->Rol->Descripción.'"';
                     }
                     if (isset($parrCambios['OtrosAnexos'])){
                        $valor = DB::table('tbl_tipo_documento_valor')->select('tbl_tipo_documento_valor.IdTipoDocumentoValor')->where('tbl_tipo_documento_valor.IdTipoDocumento',$lobjTipoDocumento->IdTipoDocumento)->where('tbl_tipo_documento_valor.Etiqueta','Otros Anexos')->first();
                        $larrIdTipoDocumentoValor[] = $valor->IdTipoDocumentoValor;
                        $larrValor[] = $parrCambios['OtrosAnexos'];
                     }
                     if (isset($parrCambios['Otros'])){
                         $valor = DB::table('tbl_tipo_documento_valor')->select('tbl_tipo_documento_valor.IdTipoDocumentoValor')->where('tbl_tipo_documento_valor.IdTipoDocumento',$lobjTipoDocumento->IdTipoDocumento)->where('tbl_tipo_documento_valor.Etiqueta','Otros')->first();
                         $larrIdTipoDocumentoValor[] = $valor->IdTipoDocumentoValor;
                         $larrValor[] = $parrCambios['Otros'];
                     }
                        //Guardamos una descripción de los cambios
                        parent::loadvalues($larrIdTipoDocumentoValor, $larrValor);
                        $log =  DB::table('tbl_acciones')->where('tbl_acciones.Nombre','anexo')->first();
                        if(isset($log->IdAccion)){
                            parent::Log($log->IdAccion);
                        }
                                   
               }
               return $larrResult;
            }else{
                return array("code"=>3,"message"=>"No existe documento para proceso de anexo","resutl"=>"");                
            }
        }else{
        	return array("code"=>2,"message"=>"No existe el contrato de trabajo","resutl"=>"");
        }
	}
 
}