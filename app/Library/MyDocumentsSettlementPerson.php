<?php

namespace App\Library;

use MyPeoples;
use \App\Models\Documentovalor;
use \App\Models\tiposcontratospersonas;
use \App\Models\Contratospersonas;
use \App\Models\Conceptoanotacion;

class MyDocumentsSettlementPerson extends MyDocuments {

	public function __construct($pintIdDocumento="", $pobjDocumento = null) 
  	{
    	parent::__construct($pintIdDocumento);
	}
	static public function Save($pintIdTipoDocumento, $pintEntidad, $pintIdEntidad, $pintIdDocumentoRelacion = "", $pintIdContratista= "", $pintContratoId="", $pdatFechaEmision = "", $pintIdAnotacion= "") {
        
        //Recuperamos el tipo de documento para los contratos
        $lobjTipoDocumento = \DB::table('tbl_tipos_documentos')
                             ->where('tbl_tipos_documentos.IdProceso',4)
                             ->first();
        
        //Existe un documento de finiquito configurado
        if ($lobjTipoDocumento){

            $lintIdTipoDocumento = $lobjTipoDocumento->IdTipoDocumento;
            $lintEntidad = 3; //documento a persona
            $lintIdDocumentoRelacion = "";

            //Guardamos el documento 
            $larrResult = parent::Save($lintIdTipoDocumento, $lintEntidad, $pintIdEntidad, $lintIdDocumentoRelacion, $pintIdContratista, $pintContratoId, null);

            if ($larrResult['status']=='success'){
                
                $lobjAnotacion = Conceptoanotacion::find($pintIdAnotacion);

                //Complementamos la información del documento 
                $larrIdTipoDocumentoValor = array();
                $larrValor = array();
                //Fecha Efectiva
                $larrIdTipoDocumentoValor[] = '171';
                $larrValor[] = \MyFormats::FormatDate($pdatFechaEmision);
                //Motivo de desvinculación
                $larrIdTipoDocumentoValor[] = '177';
                $larrValor[] = $lobjAnotacion->Descripcion;
                //Guardamos una descripción de los cambios
                self::loadvalues($larrIdTipoDocumentoValor, $larrValor);

            }

            return $larrResult;
        }
    
        return array("code"=>1,'status'=>'success',"message"=>"Documento guardado satisfactoriamente","result"=>array("IdDocumento"=>null));

	}

}