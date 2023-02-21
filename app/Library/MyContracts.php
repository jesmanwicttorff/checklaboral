<?php namespace App\Library;

use App\Library\MyRequirements;
use App\Models\Contratos;
use App\Models\Contratosacciones;
use App\Library\MyDocuments;

class MyContracts
{

	static protected $gintIdUser;
  static private $gintIdLevelUser;
  static private $gintIdContract;
	static protected $gobjContract;

	public function __construct($pintIdContract)
  	{

      self::$gintIdContract = $pintIdContract;
      self::$gobjContract = Contratos::find($pintIdContract);
      self::$gintIdUser = \Session::get('uid');
      self::$gintIdLevelUser = \MySourcing::LevelUser(\Session::get('uid'));

  	}

  static public function getDatos(){
        return self::$gobjContract;
  }

  static public function Extender($pdatFechaVencimiento){

    //actualizamos la fecha del contrato
    \DB::beginTransaction();

    $lstrObservacion = "Extension desde ".\MyFormats::FormatDate(self::$gobjContract->cont_fechaFin). " hasta ". \MyFormats::FormatDate($pdatFechaVencimiento);

    self::$gobjContract->cont_fechaFin = $pdatFechaVencimiento;
    if ($pdatFechaVencimiento>=date('Y-m-d')){
      self::$gobjContract->cont_estado = 1;  
    }
    self::$gobjContract->save();

    //actualizamos los accesos
    self::$gobjContract->Accesos()->where('tbl_accesos.IdTipoAcceso',1)->update(["FechaFinal"=>$pdatFechaVencimiento]);

    //Completamos los documentos
    $lobjMyRequirements = new MyRequirements(self::$gobjContract->contrato_id, 1);
    $lobjRequirements = $lobjMyRequirements::getRequirements(1);

    $lintAcreditacion = self::$gobjContract->acreditacion;
    $lintControlChecklLaboral = self::$gobjContract->controllaboral;

    foreach ($lobjRequirements as $larrRequirements) {

      if (($larrRequirements->TipoDocumento->Acreditacion&&$lintAcreditacion)||($larrRequirements->TipoDocumento->ControlCheckLaboral&&$lintControlChecklLaboral)||(!$larrRequirements->Acreditacion&&!$larrRequirements->ControlCheckLaboral)){

        $lobjRequirements = $lobjMyRequirements::Load($larrRequirements->IdRequisito, 
                                                      self::$gobjContract->contrato_id);

      }

    }

    //registramos el cambio
    self::Log(9, $lstrObservacion);

    \DB::commit();

    return array(
            'code'=>1,
            'status'=>'success',
            'result'=>'',
            'message'=> \Lang::get('core.note_success')
        );

  }

	static public function Settlement(){

		$lintIdContrato = self::$gintIdContract;
		self::$gobjContract->cont_estado = 5;
		self::$gobjContract->save();

		$lobjRequerimientos = new MyRequirements($lintIdContrato);
		$larrRequisitos = $lobjRequerimientos::getRequirements(2); //buscamos los requisitos para el evento de finiquito
		
		if (count($larrRequisitos)>0){
			foreach ($larrRequisitos as $Requisitos) {
				$lobjDocumentos = new MyDocuments();
				$lobjDocumentos::Save($Requisitos->IdTipoDocumento, 2, self::$gobjContract->contrato_id, "", self::$gobjContract->IdContratista, self::$gobjContract->contrato_id);
			}
			return array(
            'code'=>1,
            'status'=>'success',
            'result'=>'',
            'message'=> "Proceso de finiquito comenzado satisfactoriamente"
            );
		}else{
			$larrResult = self::SettlementExecute();
			return $larrResult;
		}

		return array(
            'code'=>1,
            'status'=>'success',
            'result'=>'',
            'message'=> \Lang::get('core.note_success')
        );

	} 


    static public function getPosiblesCambios(){

        $larrContratos = self::$gobjContract
                         ->where("IdContratista",self::$gobjContract->IdContratista)
                         ->where("contrato_id","!=",self::$gobjContract->contrato_id)
                         ->get();
        return $larrContratos;

    }


	static public function SettlementExecute() {

		if (self::$gobjContract->cont_fechaFin < date('Y-m-d')){
			self::$gobjContract->cont_estado = 7; //Settlement
		}else{
			self::$gobjContract->cont_estado = 6;
		}
		self::$gobjContract->save();

		self::log(22); //Se finiquitó el contrato

		return array(
        	'code'=>1,
        	'status'=>'success',
        	'result'=>'',
        	'message'=> "Contrato finiquitado correctamente"
        );

	}

	static private function Log($pintIdAccion, $pstrObservacion = ""){
        
        $lobjDocumentoLog = new Contratosacciones;
        $lobjDocumentoLog->contrato_id = self::$gobjContract->contrato_id;
        $lobjDocumentoLog->accion_id = $pintIdAccion;
        $lobjDocumentoLog->observaciones = $pstrObservacion;
        $lobjDocumentoLog->entry_by = self::$gintIdUser;
        $lobjDocumentoLog->save();

        return array(
            'code'=>1,
            'status'=>'success',
            'result'=>$lobjDocumentoLog,
            'message'=> \Lang::get('core.note_success')
            );
    }

}