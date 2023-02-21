<?php

namespace App\Traits;
use App\Models\Personas;
use App\Models\Contratospersonas;
use App\Models\Contratos;
use App\Models\Extensiontiposcontratos;
use Carbon\Carbon;


trait People {
    
    public function getObjetoPersona($Idpersona) {

        $getObjetoPersona = Personas::with('Contratospersonas')->find($Idpersona);
        if($getObjetoPersona){
            return $getObjetoPersona;
        }else{
            return false;
        }
    }
    public function getObjetoContratoPersona($Idpersona) {

        $getObjetoPersona = Personas::with('Contratospersonas')->find($Idpersona);
        if($getObjetoPersona){
            return $getObjetoPersona->Contratospersonas;
        }else{
            return false;
        }
    }
    //contrato TODO:cambiar
    public function configuracionAnexo($contrato_id){

        $contrato = Contratos::find($contrato_id);
        if($contrato){
            return $configuracion = $contrato->confAnexo;
        }
    }

    //contrato TODO:cambiar
    public function extensionTipoContrato($tipoContratoPersona, $extension){

        $extensionTipoContrato = Extensiontiposcontratos::where($extension, $tipoContratoPersona);
        if($extensionTipoContrato){
            return $extensionTipoContrato;
        }else{
            return false;
        }
    }
}