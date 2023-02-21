<?php


namespace App\Library;

use App\Library\MyDocuments;
use App\Library\MyDocumentsF301;
use App\Library\MyDocumentsContractPerson;
use App\Library\MyDocumentsContractPersonAnnexed;
use App\Library\MyDocumentsSettlementPerson;
use App\Library\MyDocumentsPrevired;
use App\Library\MyDocumentsPreviredEmployee;
use App\Models\Documentos;

class MyRequest {

	static protected $gobjDocumento;
	static private $gintIdDocumento;
    static private $garrIdProcesos = array("F301"=>1, 
                                           "ANEXO_CONTRATO_TRABAJO"=>3,
                                           "FINIQUITO_CONTRATO_TRABAJO"=>4,
                                           "CONTRATO_TRABAJO"=>21,
                                           "PREVIRED_TRABAJADOR"=>120,
                                           "PREVIRED"=>212);

    public function getIdProceso($pstrNombreProceso) {

        if (isset(self::$garrIdProcesos[$pstrNombreProceso])){
            return self::$garrIdProcesos[$pstrNombreProceso];
        }else{
            return false;
        }

    }

	public function __construct($pintIdDocumento="")
    {

    	self::$gintIdDocumento = $pintIdDocumento;
    	self::$gobjDocumento = Documentos::find($pintIdDocumento);
    	
    }
    public function getClass(){
    	if (self::$gobjDocumento){
            switch (self::$gobjDocumento->TipoDocumento->IdProceso) {
                case self::$garrIdProcesos['F301']:
                    //F30-1
                    return new MyDocumentsF301(self::$gintIdDocumento, self::$gobjDocumento);
                    break;
                case self::$garrIdProcesos['ANEXO_CONTRATO_TRABAJO']:
                    //Anexo Contrato
                    return new MyDocumentsContractPersonAnnexed(self::$gintIdDocumento, self::$gobjDocumento);
                    break;
                case self::$garrIdProcesos['FINIQUITO_CONTRATO_TRABAJO']:
                    //Finiquito
                    return new MyDocumentsSettlementPerson(self::$gintIdDocumento, self::$gobjDocumento);
                    break;
                case self::$garrIdProcesos['CONTRATO_TRABAJO']:
                    //Contrato Trabajador
                    return new MyDocumentsContractPerson(self::$gintIdDocumento, self::$gobjDocumento);
                    break;
                case self::$garrIdProcesos['PREVIRED_TRABAJADOR']:
                    //Previred trabajador
                    return new MyDocumentsPreviredEmployee(self::$gintIdDocumento, self::$gobjDocumento);
                    break;
                case self::$garrIdProcesos['PREVIRED']:
                    //Previred
                    return new MyDocumentsPrevired(self::$gintIdDocumento, self::$gobjDocumento);
                    break;
                default:
                    return new MyDocuments(self::$gintIdDocumento, self::$gobjDocumento);
            }
        }
    }

}