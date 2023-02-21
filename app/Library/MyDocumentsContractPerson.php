<?php

namespace App\Library;

use MyPeoples;
use \App\Models\Documentovalor;
use \App\Models\tiposcontratospersonas;
use \App\Models\Contratospersonas;
use \App\Models\Documentos;

class MyDocumentsContractPerson extends MyDocuments {

	public function __construct($pintIdDocumento)
  	{
    	parent::__construct($pintIdDocumento);
	}
	static public function Load($pobjArchivoDocumento, $pdatFechaVencimiento, $pintIdTipoContrato = null) {

		$lobjTipoContrato = tiposcontratospersonas::find($pintIdTipoContrato);
		if ($lobjTipoContrato) {
			parent::$gobjDocumento->Vencimiento = $lobjTipoContrato->Vencimiento;
			if (!parent::$gobjDocumento->Vencimiento){
				$pdatFechaVencimiento = "";
			}
			$larrResult = parent::Load($pobjArchivoDocumento, $pdatFechaVencimiento);
			if ($larrResult["code"]==1){

				$lobjMyPeople = new MyPeoples(parent::$gobjDocumento->IdEntidad);
				$lobjPersona = $lobjMyPeople->getDatos();

				$lobjValores = parent::Loadvalues(['127'],[$lobjTipoContrato->Nombre]);
				if ($lobjPersona->Contratospersonas){
					$lobjValores = parent::Loadvalues(['167'],[$lobjPersona->Contratospersonas->Rol->{'Descripción'}]);

					if ($lobjPersona->Contratospersonas->contrato_id == parent::$gobjDocumento->contrato_id) {
						$lobjContratoPersona = Contratospersonas::find($lobjPersona->Contratospersonas->IdContratosPersonas);
						$id = substr($pintIdTipoContrato,0,1);
						$TipoContrato = \DB::table('tbl_tipos_contratos_personas')->where('id',$id)->first();
						if($TipoContrato->Vencimiento==0){
							$lobjContratoPersona->FechaVencimiento = null;
							$lobjDocumento = Documentos::find(parent::$gobjDocumento->IdDocumento);
							if($lobjDocumento){
								$lobjDocumento->Vencimiento=0;
								$lobjDocumento->IdEstatusDocumento=null;
								$lobjDocumento->FechaVencimiento = null;
								$lobjDocumento->save();
							}
						}else {
							$lobjContratoPersona->FechaVencimiento = $pdatFechaVencimiento;
						}
						$lobjContratoPersona->IdTipoContrato = $pintIdTipoContrato;
						$lobjContratoPersona->IdDocumento = parent::$gobjDocumento->IdDocumento;
						$lobjContratoPersona->IdEstatus = parent::$gobjDocumento->IdEstatus;
						$lobjContratoPersona->save();
					}
				}

			}
		}else{
			$larrResult = array("code"=>2, "message"=>"No se encontró el tipo de contrato persona", "result"=>"");
		}
		return $larrResult;

	}

}
