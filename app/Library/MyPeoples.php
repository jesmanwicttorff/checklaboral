<?php

use App\Models\Personas;
use App\Models\Contratos;
use App\Models\Contratospersonas;
use App\Library\MyCheckLaboral;
use App\Library\MyDocuments;
use App\Library\MyRequirements;
use App\Library\MyDocumentsSettlementPerson;
use App\Library\MyDocumentsContractPersonAnnexed;
use App\Library\Acreditacion;

class MyPeoples {

  static protected $gintIdUser;
  static private $gintIdLevelUser;
  static private $gintIdPersona;
  static protected $gobjPersona;
  static private $gobjDatosContractuales;

  public function __construct($pintIdpersona)
  {
      self::$gintIdPersona = $pintIdpersona;
      self::$gobjPersona = Personas::with('Contratospersonas')->find($pintIdpersona);
      self::$gintIdUser = \Session::get('uid');
      self::$gintIdLevelUser = \MySourcing::LevelUser(\Session::get('uid'));

  }

  static public function getDatos() {
      return self::$gobjPersona;
  }
  static public function getDatosContratos() {
      return self::$gobjPersona->Contratospersonas;
  }

  //*************************************************************
  // @autor: Diego Diaz
  // @date: 22/06/2017
  // @description: función que se encarga de limpiar los documentos
  //               de una persona que no hayan sido aprobados
  // @parameters:  $pintIdPersona: id de la persona a la que se le
  //               quiere limpiar los documentos
  //               $pintConPermanencia: mantiene solo los
  //               los documentos que tienen permanencia
  //*************************************************************
  static public function ResetPeople($pintIdPersona, $pintConPermanencia=1,$pstrContrato=''){

    $lobjPersona = \DB::table("tbl_personas")
    ->where("tbl_personas.IdPersona","=",$pintIdPersona);
    if ($lobjPersona){
      //echo "Encontramos a la persona ".var_dump($lobjPersona)."<br/>";
      $lintIdEdicion = \DB::table("tbl_personas")
      ->where("tbl_personas.IdPersona","=",$pintIdPersona)
      ->update(array("entry_by_access"=>0));
      //echo "Mandamos a reiniciar los documentos ".$pintIdPersona." ".$pintConPermanencia." ".$pstrContrato."<br/>";
      self::ResetDocumento($pintIdPersona, $pintConPermanencia,$pstrContrato);
    }else{
       return array("status" => "success", "code"=>0,"message"=>"No existe la persona", "result"=>false);
    }

  }
  static public function CatchDocument($pintIdPersona,$pintIdTipoDocumento){

    $lobjDocumentos = \DB::table("tbl_documentos")
    ->where("tbl_documentos.IdEntidad","=",$pintIdPersona)
    ->where("tbl_documentos.Entidad","=",3)
    ->where("tbl_documentos.IdTipoDocumento","=",$pintIdTipoDocumento)
    ->orderBy("tbl_documentos.IdDocumento","DESC")
    ->first();

    if ($lobjDocumentos){
      return array("status" => "success", "code"=>0,"message"=>"Captura ejecutada correctamente", "result"=>$lobjDocumentos);
    }else{
      return array("status" => "success", "code"=>0,"message"=>"No existe documento para la persona", "result"=>array());
    }

  }

  static public function DatosHistorico($pintIdEntidad,$pintIdEstatus)
  {

      // se inserta la data en la tabla de tbl_documentos_rep_historico
      $consulta = "INSERT INTO tbl_documentos_rep_historico(`IdDocumentoH`,`IdDocumento`,`IdTipoDocumento`,`Entidad`,`IdEntidad`,`Documento`,`DocumentoURL`,`FechaEmision`,`FechaAprobacion`,`FechaVencimiento`,`IdEstatus`,`IdEstatusDocumento`,`Resultado`,`load_by`,`approv_by`,`contrato_id`,`IdContratista`)
                                SELECT NULL as IdDocumentoH,
                                     IdDocumento,
                                     IdTipoDocumento,
                                     Entidad,
                                     IdEntidad,
                                     Documento,
                                     DocumentoURL,
                                     FechaEmision,
									 now() as FechaAprobacion,
                                     FechaVencimiento,
                                     '$pintIdEstatus' as IdEstatus,
                                     IdEstatusDocumento,
                                     Resultado,
                                     entry_by,
		                             entry_by_access as approv_by,
                                     contrato_id,
                                     IdContratista
                                FROM tbl_documentos
                                WHERE IdDocumento='$pintIdEntidad'";
      \DB::insert($consulta);

      $consultaDV = "INSERT INTO tbl_documento_valor_historico(`id`,`IdDocumento`,`IdTipoDocumentoValor`,`Valor`,`idCargado`,`entry_by`,`entry_by_access`)
                SELECT NULL as id,
                              tbl_documento_valor.IdDocumento,
                              tbl_documento_valor.IdTipoDocumentoValor,
                              tbl_documento_valor.Valor,
                              tbl_documento_valor.idCargado,
                              tbl_documento_valor.entry_by,
                              tbl_documento_valor.entry_by_access
            FROM tbl_documento_valor
            INNER JOIN tbl_documentos on tbl_documento_valor.IdDocumento=tbl_documentos.IdDocumento
            WHERE tbl_documentos.IdDocumento='$pintIdEntidad'";

      \DB::insert($consultaDV);


      $consultaH = "INSERT INTO tbl_documentos_log_historico(`id`,`IdDocumento`,`IdAccion`,`DocumentoURL`,`observaciones`,`entry_by`,`createdOn`)
                SELECT NULL as id,
                 tbl_documentos_log.IdDocumento,
                 tbl_documentos_log.IdAccion,
                 tbl_documentos_log.DocumentoURL,
                 tbl_documentos_log.observaciones,
                 tbl_documentos_log.entry_by,
                 tbl_documentos_log.createdOn
            FROM tbl_documentos_log
            INNER JOIN tbl_documentos on tbl_documentos_log.IdDocumento=tbl_documentos.IdDocumento
            WHERE tbl_documentos.IdDocumento='$pintIdEntidad'";

      \DB::insert($consultaH);

      \DB::table('tbl_documentos_log')->where('IdDocumento', '=', $pintIdEntidad)->delete();
      \DB::table('tbl_documento_valor')->where('IdDocumento', '=', $pintIdEntidad)->delete();
      \DB::table('tbl_documentos')->where('IdDocumento', '=', $pintIdEntidad)->delete();

  }
  static public function RestoreHistorico($pintIdContrato,$pintIdPersona,$pintIdRol,$pdatFechaEfectiva = ""){
    $consulta = "select tbl_documentos_rep_historico.*,tbl_requisitos.IdRequisito
                    FROM tbl_documentos_rep_historico
                                INNER JOIN tbl_tipos_documentos on tbl_documentos_rep_historico.IdTipoDocumento=tbl_tipos_documentos.IdTipoDocumento
                                INNER JOIN tbl_requisitos on tbl_tipos_documentos.IdTipoDocumento=tbl_requisitos.IdTipoDocumento
                                WHERE tbl_documentos_rep_historico.IdEntidad='$pintIdPersona' and tbl_documentos_rep_historico.Entidad='3'
                                and tbl_tipos_documentos.Permanencia=1
                                and tbl_documentos_rep_historico.IdEstatus!=9
                    and exists ( SELECT *
                                      FROM tbl_requisitos
                                      INNER JOIN tbl_requisitos_detalles on tbl_requisitos.IdRequisito=tbl_requisitos_detalles.IdRequisito
                                      WHERE tbl_requisitos_detalles.IdEntidad = '$pintIdRol'
                                      AND tbl_documentos_rep_historico.IdTipoDocumento = tbl_requisitos.IdTipoDocumento)
                     and not EXISTS (SELECT *
                                      FROM tbl_documentos
                                      WHERE tbl_documentos.IdEntidad = '$pintIdPersona' and tbl_documentos_rep_historico.Entidad='3'
                                      AND tbl_documentos_rep_historico.IdTipoDocumento = tbl_documentos.IdTipoDocumento)
                    union
                    select tbl_documentos_rep_historico.*,tbl_requisitos.IdRequisito
                    FROM tbl_documentos_rep_historico
                    INNER JOIN tbl_tipos_documentos on tbl_documentos_rep_historico.IdTipoDocumento=tbl_tipos_documentos.IdTipoDocumento
                    INNER JOIN tbl_requisitos on tbl_tipos_documentos.IdTipoDocumento=tbl_requisitos.IdTipoDocumento
                    where tbl_documentos_rep_historico.Entidad = 3
                    and IdEntidad='$pintIdPersona'
                    and tbl_tipos_documentos.Permanencia=1
                    and tbl_documentos_rep_historico.IdEstatus!=9
                    and  tbl_tipos_documentos.IdTipoDocumento in ( SELECT tbl_requisitos.IdTipoDocumento
                                                          FROM tbl_requisitos
                                                          WHERE tbl_requisitos.Entidad = 3
                                                          AND tbl_requisitos.IdTipoDocumento NOT IN (2,8,64))
                    and not EXISTS (SELECT *
                                      FROM tbl_documentos
                                      WHERE tbl_documentos.IdEntidad = '$pintIdPersona' and tbl_documentos_rep_historico.Entidad='3'
                                      AND tbl_documentos_rep_historico.IdTipoDocumento = tbl_documentos.IdTipoDocumento)
                    union
                    select tbl_documentos_rep_historico.*,tbl_requisitos.IdRequisito
                    FROM tbl_documentos_rep_historico
                                INNER JOIN tbl_tipos_documentos on tbl_documentos_rep_historico.IdTipoDocumento=tbl_tipos_documentos.IdTipoDocumento
                                INNER JOIN tbl_requisitos on tbl_tipos_documentos.IdTipoDocumento=tbl_requisitos.IdTipoDocumento
                    WHERE tbl_tipos_documentos.IdTipoDocumento = 64
                                                AND tbl_documentos_rep_historico.Entidad = 3
                                    AND tbl_documentos_rep_historico.IdEntidad = '$pintIdPersona'
                                    and tbl_documentos_rep_historico.IdEstatus!=9
                      AND EXISTS (SELECT  tbl_personas.*
                         FROM tbl_personas
                         WHERE tbl_personas.IdPersona = '$pintIdPersona'
                                  AND tbl_personas.id_Nac not in (21,22))
                      and not EXISTS (SELECT *
                                      FROM tbl_documentos
                                      WHERE tbl_documentos.IdEntidad = '$pintIdPersona' and tbl_documentos_rep_historico.Entidad='3'
                                      AND tbl_documentos_rep_historico.IdTipoDocumento = tbl_documentos.IdTipoDocumento)";

    $lobjData = \DB::select($consulta);



    if (count($lobjData)>0) {
        foreach ($lobjData as $value) {
            $arrayDocs[] = $value->IdDocumento;
        }

        $lobjDataBitacora = \DB::table('tbl_documentos_log_historico')->whereIn('IdDocumento', $arrayDocs)->get();

        $lobjDataDocumentoV = \DB::table('tbl_documento_valor_historico')->whereIn('IdDocumento', $arrayDocs)->get();

    }


    foreach ($lobjData as $lisData) {

        if ($pdatFechaEfectiva){
          $ldatFechaEfectiva = $pdatFechaEfectiva;
        }else{
          $ldatFechaEfectiva = $lisData->FechaEmision;
        }

        $LintIdDoc = \DB::table('tbl_documentos')->insertGetId(
            ['IdDocumentoRelacion'=> NULL, 'IdRequisito'=> $lisData->IdRequisito,
                'IdTipoDocumento' => $lisData->IdTipoDocumento, 'Entidad' => $lisData->Entidad,
                'IdEntidad' => $lisData->IdEntidad, 'Documento' => $lisData->Documento,
                'DocumentoURL' => $lisData->DocumentoURL, 'DocumentoTexto'=> NULL,
                'FechaVencimiento' => $lisData->FechaVencimiento, 'IdEstatus'=>'5',
                'IdEstatusDocumento' => $lisData->IdEstatusDocumento, 'createdOn'=> new \DateTime(),
                'entry_by' => $lisData->load_by, 'entry_by_access' => $lisData->approv_by,
                'updatedOn'=> NULL, 'FechaEmision'=> $ldatFechaEfectiva, 'Resultado'=> '-',
                'contrato_id' => $pintIdContrato, 'IdContratista' => $lisData->IdContratista,'estado_carga'=>'0']);

        if (count($lobjDataDocumentoV)>0) {
            foreach ($lobjDataDocumentoV as $lisDataV) {
                if ($lisData->IdDocumento==$lisDataV->IdDocumento){
                    \DB::table('tbl_documento_valor')->insert(
                        ['IdDocumento' => $LintIdDoc, 'IdTipoDocumentoValor' => $lisDataV->IdTipoDocumentoValor,
                            'Valor' => $lisDataV->Valor, 'idCargado' => $lisDataV->idCargado,
                            'entry_by' => $lisDataV->entry_by, 'entry_by_access' => $lisDataV->entry_by_access]);

                    \DB::table('tbl_documento_valor_historico')->where('IdDocumento', '=', $lisDataV->IdDocumento)->delete();
                }
            }
        }

        if (count($lobjDataBitacora)>0) {
            foreach ($lobjDataBitacora as $lisDataB) {
                if ($lisData->IdDocumento==$lisDataB->IdDocumento) {
                    \DB::table('tbl_documentos_log')->insert(
                        ['IdDocumento' => $LintIdDoc, 'IdAccion' => $lisDataB->IdAccion,
                            'DocumentoURL' => $lisDataB->DocumentoURL, 'observaciones' => $lisDataB->observaciones,
                            'entry_by' => $lisDataB->entry_by, 'createdOn' => $lisDataB->createdOn]);

                    \DB::table('tbl_documentos_log_historico')->where('IdDocumento', '=', $lisDataB->IdDocumento)->delete();
                }
            }
        }

        \DB::table('tbl_documentos_rep_historico')->where('IdDocumento', '=', $lisData->IdDocumento)->delete();
    }

}
  static public function ResetDocumento($pintIdPersona, $pintConPermanencia=1, $pstrContrato = ''){


    $lobjPersona = \DB::table('tbl_personas')
    ->where("tbl_personas.IdPersona","=",$pintIdPersona)
    ->first();
    if ($lobjPersona){

      //Eliminamos los documentos valores de los documentos que no importan
      $lobjDocumentosValorDistintos = \DB::table('tbl_documento_valor')
      ->whereExists(function($query) use ($pintIdPersona) {
        $query->select(\DB::raw(1))
              ->from('tbl_documentos')
              ->whereraw('tbl_documentos.iddocumento = tbl_documento_valor.iddocumento')
              ->whereraw('tbl_documentos.Entidad = 3')
              ->whereraw('tbl_documentos.IdEntidad = '.$pintIdPersona)
              ->whereraw('tbl_documentos.IdEstatus != 5')
              ->whereraw('exists (select 1 from tbl_tipos_documentos where tbl_documentos.IdTipoDocumento = tbl_tipos_documentos.IdTipoDocumento and ifnull(tbl_tipos_documentos.ControlCheckLaboral,0) != 1)');
      })->delete();

      if($pintConPermanencia!=3){
        $lobjDocumentosDistintos = \DB::table('tbl_documentos')
        ->where("tbl_documentos.IdEntidad","=",$pintIdPersona)
        ->where("tbl_documentos.Entidad","=",3)
        ->where("tbl_documentos.IdEstatus","!=",5)
        ->whereExists(function($query) {
            $query->select(\DB::raw(1))
            ->from('tbl_tipos_documentos')
            ->whereraw('tbl_documentos.IdTipoDocumento = tbl_tipos_documentos.IdTipoDocumento')
            ->whereraw('ifnull(tbl_tipos_documentos.ControlCheckLaboral,0) != 1');
        })
        ->delete();
      }

      $lobjDocumentos = \DB::table('tbl_documentos')
      ->where("tbl_documentos.IdEntidad","=",$pintIdPersona)
      ->where("tbl_documentos.Entidad","=",3);
      if ($pintConPermanencia==1){
        $lobjDocumentos->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('tbl_tipos_documentos')
                      ->whereRaw('tbl_tipos_documentos.idtipodocumento = tbl_documentos.idtipodocumento')
                      ->whereRaw('tbl_tipos_documentos.Permanencia = 0');
        });

      }elseif ($pintConPermanencia==2){
        $lobjDocumentos->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('tbl_tipos_documentos')
                      ->whereRaw('tbl_tipos_documentos.idtipodocumento = tbl_documentos.idtipodocumento')
                      ->whereRaw('tbl_tipos_documentos.ControlCheckLaboral != 1');
        });

      }else if($pintConPermanencia==3){
        $lobjDocumentos->where("tbl_documentos.IdEstatus",5);
        $lobjDocumentos->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('tbl_tipos_documentos')
                      ->whereRaw('tbl_tipos_documentos.idtipodocumento = tbl_documentos.idtipodocumento');
        });
      }
      if ($pstrContrato) {
        $lobjDocumentos->whereRaw("tbl_documentos.IdTipoDocumento NOT IN (SELECT tbl_tipos_documentos.IdTipoDocumento from tbl_tipos_documentos where tbl_tipos_documentos.IdProceso = ".$pstrContrato.")");
      }


        $lobjDataDoc = $lobjDocumentos->get();


        if (count($lobjDataDoc)>0){

            foreach ($lobjDataDoc as $larrDocumentoR) {
                \MyPeoples::DatosHistorico($larrDocumentoR->IdDocumento,5);
            }
        }

    }else{
      return array("status" => "success", "code"=>0,"message"=>"No existe la persona", "result"=>false);
    }

  }

  static public function getDatosContractuales() {

    $larrResult = \DB::table('tbl_contratos_personas')
                  ->select("tbl_contratos_personas.contrato_id",
                           "tbl_contrato.id_extension",
                           "tbl_contrato.cont_numero",
                           "tbl_contratos_personas.IdRol",
                           "tbl_roles.Descripción as Rol",
                           "tbl_contratos_personas.IdContratista",
                           "tbl_contrato.IdContratista as IdContratistaContrato",
                           "tbl_contratos_personas.IdDocumento",
                           "tbl_contratos_personas.IdTipoContrato",
                           "tbl_tipos_contratos_personas.Descripcion as TipoContrato",
                           "tbl_tipos_contratos_personas.Vencimiento as TipoContratoVencimiento",
                           "tbl_contratos_personas.FechaVencimiento",
                           "tbl_contratos_personas.IdEstatus"
                          )
                  ->Join("tbl_contratistas","tbl_contratistas.IdContratista", "=", "tbl_contratos_personas.IdContratista")
                  ->Join("tbl_contrato","tbl_contrato.contrato_id", "=", "tbl_contratos_personas.contrato_id")
                  ->leftJoin("tbl_tipos_contratos_personas","tbl_tipos_contratos_personas.id", "=", "tbl_contratos_personas.IdTipoContrato")
                  ->Join("tbl_roles","tbl_roles.IdRol", "=", "tbl_contratos_personas.IdRol")
                  ->where('tbl_contratos_personas.IdPersona', '=', self::$gintIdPersona)
                  ->first();

    self::$gobjDatosContractuales = $larrResult;

    return array("code"=>1,"message"=>"","result"=>$larrResult);

  }

  static public function getContratosDisponibles(){

    // self::$gintIdUser
    // self::$gintIdLevelUser
    $lintIdUser = \Session::get('uid');
    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lobjFiltro = \MySourcing::getFiltroUsuario(2,0);

    //Cargamos los contratos de un contrastitas
    $lobjContrato = Contratos::select(\DB::raw('tbl_contrato.*'),
                                      'tbl_contratos_servicios.name as servicio',
                                      'tbl_contrato_estatus.BloqueaVinculacion')
                               ->leftjoin('tbl_contratos_servicios', 'tbl_contratos_servicios.id','=','tbl_contrato.idservicio')
                               ->join('tbl_contrato_estatus', 'tbl_contrato_estatus.id','=','tbl_contrato.cont_estado');

    if ($lintLevelUser==6 || $lintLevelUser==15) {
        $lobjContrato = $lobjContrato->where("tbl_contrato.entry_by_access",$lintIdUser);
        $lobjContrato = $lobjContrato->orWhereExists(function($query) use ($lintIdUser){
                          $query->select(DB::raw(1))
                                ->from('tbl_contratos_subcontratistas')
                                ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contratos_subcontratistas.IdSubContratista')
                                ->whereRaw('tbl_contratos_subcontratistas.contrato_id = tbl_contrato.contrato_id')
                                ->whereRaw('tbl_contratistas.entry_by_access = '.$lintIdUser);
                        });
    }else{
        $lobjContrato = $lobjContrato->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos']);
    }

    return $lobjContrato->get();

  }

  static public function LeaveAccess($pintIdPersona){


    \DB::beginTransaction();

      $lobjContrato = \DB::table("tbl_acceso_areas")
      ->whereExists(function ($query) use ($pintIdPersona) {
              $query->select(DB::raw(1))
                    ->from('tbl_accesos')
                    ->whereRaw('tbl_accesos.IdPersona = '.$pintIdPersona)
                    ->whereRaw('tbl_accesos.IdAcceso = tbl_acceso_areas.IdAcceso')
                    ->whereRaw('tbl_accesos.IdTipoAcceso = 1');
      })
      ->delete();

      $lobjContrato = \DB::table("tbl_accesos")
      ->where("tbl_accesos.IdPersona","=",$pintIdPersona)
      ->where('tbl_accesos.IdTipoAcceso', '=', '1')
      ->delete();

    \DB::commit();

  }

  static public function LeaveContract($pintIdPersona, $pintIdContrato = 0, $pintIdAnotacion = 0, $pdatFechaEfectiva){

    $lobjContrato = \DB::table("tbl_contratos_personas")
    ->select("tbl_contrato.IdContratista","tbl_contrato.contrato_id", "tbl_contratos_personas.IdPersona", "tbl_contratos_personas.IdRol", "tbl_contratos_personas.createdOn", "tbl_contrato.entry_by_access")
    ->join("tbl_contrato","tbl_contrato.contrato_id","=","tbl_contratos_personas.contrato_id")
    ->where("tbl_contratos_personas.IdPersona","=",$pintIdPersona);
    if ($pintIdContrato) {
      $lobjContrato->where("tbl_contratos_personas.contrato_id","=",$pintIdContrato);
    }
    $lobjContrato = $lobjContrato->first();
    $ldatFechaActual = date('Y-m-d h:i:s');
    $ldatFechaEmision = date('Y-m')."-01";
    $lintIdUsuario = \Session::get('uid');

    if ($lobjContrato){

      $lintIdContrato = $lobjContrato->contrato_id;
      $lintIdContratista = $lobjContrato->IdContratista;
      $lintEntryBy = $lobjContrato->entry_by_access;

      //Iniciamos la transacción
      \DB::beginTransaction();

      self::UpdateMaestroMovil($pintIdPersona,$lintIdContrato,$pdatFechaEfectiva,'LeaveContract','Baja Observada');

      //Resolvemos que hacer con los documentos
      //se incluye opcion 3 para pasar todos al historico menos el contrato de trabajo
      $larrResultDocument = self::ResetDocumento($pintIdPersona,3,"21");

      //Insertamos el finiquito del trabajador
      $lobjMyDocuments = new MyDocumentsSettlementPerson();
      $lobjMyDocuments::save("","",$pintIdPersona, "", $lintIdContratista, $lintIdContrato, $pdatFechaEfectiva, $pintIdAnotacion);

      //Registramos la salida de la persona del contrato
      $lintResultadoRegister = self::RegisterMovePeople($pintIdPersona, 2, $lintIdContrato, $pdatFechaEfectiva);

      //Eliminamos el registro de la persona
      $lintResultado = \DB::table('tbl_contratos_personas')
      ->where("tbl_contratos_personas.IdPersona","=",$pintIdPersona)
      ->where("tbl_contratos_personas.contrato_id","=",$lintIdContrato)
      ->delete();

      //Quitamos relacion de la persona
      $lintResultadoUpdatePersona = \DB::table("tbl_personas")
      ->where("tbl_personas.IdPersona","=",$pintIdPersona)
      ->update(array("entry_by_access"=>0, "discapacidad"=>0, "pensionado"=>0));

      //Asociamos la anotacion a la persona
      $lintResultadoAnotacion = \DB::table("tbl_anotaciones")
      ->insertGetId(array("IdConceptoAnotacion"=>$pintIdAnotacion, "IdPersona" => $pintIdPersona, "entry_by"=>$lintIdUsuario , "entry_by_access"=>0, "createdOn" => $ldatFechaActual));

      //Verificamos si afecta al maestro
      $lobjMyCheckLaboral = new MyCheckLaboral();
      $lobjMyCheckLaboral::LeavePeople($pintIdPersona,$lintIdContratista,$lintIdContrato, $pdatFechaEfectiva);

       //eliminamos el documento de discapacidad y el de pensionado si no esta en estatus aprobado

       $lobjDocumentosValorDistintos = \DB::table('tbl_documentos')
       ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
       ->where('tbl_documentos.Entidad', 3)
       ->where('tbl_documentos.IdEntidad', $pintIdPersona)
       ->where('tbl_documentos.IdEstatus','!=', 5)
       ->whereIn('tbl_tipos_documentos.IdProceso', [142,143])
       ->delete();

       //si la persona esta en la tabla pensionados la borramos

       $existePensionado = \DB::table('tbl_pensionados')->where('IdPersona',$pintIdPersona)->delete();

      \DB::commit();

      return array("status" => "success", "code"=>1,"message"=>"Persona desvinculada correctamente", "result"=>$lobjContrato);
    }else{
      return array("status" => "success", "code"=>0,"message"=>"La persona no se asignada a ningun contrato", "result"=>"");
    }

  }

  static public function EliminarTrabajador($pintIdPersona, $pintIdContrato){

    $flag=false;
    \DB::beginTransaction();

    $persona = \DB::table('tbl_personas')->where('IdPersona',$pintIdPersona)->first();

    try {
      \DB::table('tbl_contratos_personas')->where('IdPersona',$pintIdPersona)->where('contrato_id',$pintIdContrato)->delete();
      \DB::table('tbl_movimiento_personal')->where('IdPersona',$pintIdPersona)->where('contrato_id',$pintIdContrato)->where('IdAccion',1)->delete();
      \DB::table('tbl_documentos')->where('Entidad',3)->where('IdEntidad',$pintIdPersona)->where('contrato_id',$pintIdContrato)->delete();
      \DB::table('tbl_documentos_rep_historico')->where('Entidad',3)->where('IdEntidad',$pintIdPersona)->where('contrato_id',$pintIdContrato)->delete();
      \DB::table('tbl_personas_acreditacion')->where('IdPersona',$pintIdPersona)->where('contrato_id',$pintIdContrato)->delete();
      \DB::table('tbl_personas_maestro_movil')->where('idpersona',$pintIdPersona)->where('contrato_id',$pintIdContrato)->delete();
      \DB::table('tbl_personas')->where('IdPersona',$pintIdPersona)->update(['entry_by'=>1,'entry_by_access'=>'null']);
      \DB::table('tbl_trabajador_eliminado_registro')->insert(['IdPersona'=>$pintIdPersona,'contrato_id'=>$pintIdContrato,'eliminated_at'=>date('Y-m-d H:i'),'RUT'=>$persona->RUT]);

      \DB::commit();

    } catch (\Exception $e) {
      DB::rollback();
      return array("status" => "fail", "code"=>1,"message"=>"Error al borrar, no se realizo ningún cambio", "result"=>$e);
    }

    return array("status" => "success", "code"=>1,"message"=>"Persona desvinculada correctamente", "result"=>"ok");

  }

  static public function RecontrataTrabajador($pintIdPersona,$lintIdContrato,$pintIdRol,$pintIdTipoContrato){

    $lobjMovPersonal = \DB::table('tbl_movimiento_personal')->where('contrato_id',$lintIdContrato)->where('IdPersona',$pintIdPersona)->where('IdAccion',1)->orderBy('IdMovimientoPersonal','desc')->first();
    $lobjContrato = \DB::table('tbl_contrato')->where('contrato_id',$lintIdContrato)->first();
    $lobjDocumentoHistorico = \DB::table('tbl_documentos_rep_historico')
              ->where('Entidad',3)
              ->where('IdEntidad',$pintIdPersona)
              ->whereExists(function ($q){
                $q->select(\DB::raw(1))
                ->from('tbl_tipos_documentos')
                ->where('IdProceso',21)
                ->whereRaw('tbl_tipos_documentos.IdTipoDocumento = tbl_documentos_rep_historico.IdTipoDocumento');
              })->first();
    if(!$lobjDocumentoHistorico){
      $lobjDocumentoHistorico = \DB::table('tbl_documentos')
                ->where('Entidad',3)
                ->where('IdEntidad',$pintIdPersona)
                ->whereExists(function ($q){
                  $q->select(\DB::raw(1))
                  ->from('tbl_tipos_documentos')
                  ->where('IdProceso',21)
                  ->whereRaw('tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento');
                })->first();
    }

    \DB::beginTransaction();

    try{
      \DB::table('tbl_movimiento_personal')
        ->where('contrato_id',$lintIdContrato)
        ->where('IdPersona',$pintIdPersona)
        ->where('IdAccion',2)
        ->orderBy('IdMovimientoPersonal','desc')
        ->limit(1)
        ->delete();
      \DB::table('tbl_contratos_personas')->insert([
        'IdPersona'=>$pintIdPersona,
        'IdContratista'=>$lobjContrato->IdContratista,
        'contrato_id'=>$lintIdContrato,
        'IdRol'=>$pintIdRol,
        'FechaInicioFaena'=>$lobjMovPersonal->FechaEfectiva,
        'IdDocumento'=>$lobjDocumentoHistorico->IdDocumento,
        'IdEstatus'=>$lobjDocumentoHistorico->IdEstatus,
        'IdTipoContrato'=>$pintIdTipoContrato,
        'createdOn'=>$lobjMovPersonal->createdOn,
        'updatedOn'=>date('Y-m-d H:i'),
        'acreditacion'=>$lobjContrato->acreditacion,
        'controllaboral'=>$lobjContrato->controllaboral,
        'Otros'=>'Restaurado Automatico'
      ]);
      \DB::table('tbl_personas')->where('IdPersona',$pintIdPersona)->update(['entry_by_access'=>$lobjContrato->entry_by_access]);
      \DB::table('tbl_documentos')
                ->where('Entidad',3)
                ->where('IdEntidad',$pintIdPersona)
                ->whereExists(function ($q){
                  $q->select(\DB::raw(1))
                  ->from('tbl_tipos_documentos')
                  ->where('IdProceso',4)
                  ->whereRaw('tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento');
                })->limit(1)->delete();
      //self::RestoreHistorico($pintIdPersona,$lintIdContrato,$pintIdRol);

      self::RestoreHistoricoFull($pintIdPersona,$lintIdContrato);

      \DB::commit();

      return array("status" => "success", "code"=>1,"message"=>"Trabajador Restaurado", "result"=>"ok");
    }catch (\Exception $e) {
      \Log::info($e->getMessage());
      DB::rollback();
      return array("status" => "fail", "code"=>1,"message"=>"Error al recuperar al trabajador, no se realizo ningún cambio");
    }

    return array("status" => "fail", "code"=>1,"message"=>"Funcion todavia no implementada", "result"=>"nok");
  }

  static public function RestoreHistoricoFull($pintIdPersona,$pintIdContrato){
    $lobjDocumentos = \DB::table('tbl_documentos_rep_historico')->where('IdEntidad',$pintIdPersona)->where('entidad',3)->where('contrato_id',$pintIdContrato)->get();

    foreach ($lobjDocumentos as $lisData) {
      $LintIdDoc = \DB::table('tbl_documentos')->insertGetId(
          ['IdDocumentoRelacion'=> NULL,
              'IdTipoDocumento' => $lisData->IdTipoDocumento, 'Entidad' => $lisData->Entidad,
              'IdEntidad' => $lisData->IdEntidad, 'Documento' => $lisData->Documento,
              'DocumentoURL' => $lisData->DocumentoURL, 'DocumentoTexto'=> NULL,
              'FechaVencimiento' => $lisData->FechaVencimiento, 'IdEstatus'=>'5',
              'IdEstatusDocumento' => $lisData->IdEstatusDocumento, 'createdOn'=> new \DateTime(),
              'entry_by' => $lisData->load_by, 'entry_by_access' => $lisData->approv_by,
              'updatedOn'=> NULL, 'FechaEmision'=> $lisData->FechaEmision, 'Resultado'=> '-',
              'contrato_id' => $pintIdContrato, 'IdContratista' => $lisData->IdContratista,'estado_carga'=>'0']);

      \DB::table('tbl_documentos_rep_historico')->where('IdDocumento', '=', $lisData->IdDocumento)->delete();

      if ($lisData->Entidad == 3 ){

          $lobjAcreditacion = new Acreditacion($lisData->IdEntidad);
          $lobjAcreditacion::Accreditation();

      }

    }

    foreach ($lobjDocumentos as $value) {
        $arrayDocs[] = $value->IdDocumento;
    }

    $lobjDataBitacora = \DB::table('tbl_documentos_log_historico')->whereIn('IdDocumento', $arrayDocs)->get();

    if (count($lobjDataBitacora)>0) {
        foreach ($lobjDataBitacora as $lisDataB) {
            \DB::table('tbl_documentos_log')->insert(
                ['IdDocumento' => $lisDataB->IdDocumento, 'IdAccion' => $lisDataB->IdAccion,
                    'DocumentoURL' => $lisDataB->DocumentoURL, 'observaciones' => $lisDataB->observaciones,
                    'entry_by' => $lisDataB->entry_by, 'createdOn' => $lisDataB->createdOn]);

            \DB::table('tbl_documentos_log_historico')->where('IdDocumento', '=', $lisDataB->IdDocumento)->delete();
        }
    }

  }

  //Funcion actual
  static public function CambiosContractual($pintIdContrato, $pintIdTipoContrato, $pdatFechaVencimiento, $pintIdRol, $pstrOtros, $pintIdOtrosAnexos,$ldatFechaInicioFaena=null) {

      //$lobjDatosContractuales = self::getDatosContractuales();
      $lobjDatosContractuales = self::getDatosContratos();
      $larrCambios = array();
      $lbolGeneraAnexo = false;


      //Cargamos la configuración de anexos
      $lobjConfiguracion = \DB::table('tbl_conf_anexos')
      ->select('tbl_conf_anexos.*')
      ->join('tbl_contrato', 'tbl_contrato.id_extension', '=', 'tbl_conf_anexos.id_extension' )
      ->where('tbl_contrato.contrato_id','=',$lobjDatosContractuales->contrato_id)
      ->first();

      //Determinamos cuales son los cambios a realizar
      if ($lobjDatosContractuales->IdTipoContrato != $pintIdTipoContrato && $pintIdTipoContrato){
        $larrCambios['IdTipoContrato'] = $pintIdTipoContrato;
        if ($lobjConfiguracion && $lobjConfiguracion->CambioTipoContrato){
          $lbolGeneraAnexo = true;
        }
      }

      if ($lobjDatosContractuales->contrato_id != $pintIdContrato && $pintIdContrato){
        $larrCambios['contrato_id'] = $pintIdContrato;
        $docsVencidos = \DB::table('tbl_documentos')->where('Vencimiento',1)->where('Entidad',3)->where('IdEntidad',$lobjDatosContractuales->IdPersona)->where('contrato_id',$lobjDatosContractuales->contrato_id)->update(['Vencimiento'=>0]);
        if ($lobjConfiguracion->CambioContrato){
          $lobjTipoContrato = \DB::table('tbl_extension_tipos_contratos')
                              ->where("tbl_extension_tipos_contratos.IdTiposContratoPersona",'=',$lobjDatosContractuales->IdTipoContrato)
                              ->where("tbl_extension_tipos_contratos.IdExtension","=",$lobjConfiguracion->id_extension)
                              ->first();
          if ($lobjTipoContrato){
            $lbolGeneraAnexo = true;
          }
        }
      }
      if ($lobjDatosContractuales->FechaVencimiento != $pdatFechaVencimiento && $pdatFechaVencimiento){
        $larrCambios['FechaVencimiento'] = $pdatFechaVencimiento;
        if ($lobjConfiguracion->CambioFecha){
          $lbolGeneraAnexo = true;
        }
      }
      if ($lobjDatosContractuales->IdRol != $pintIdRol && $pintIdRol){
        $larrCambios['IdRol'] = $pintIdRol;
        if ($lobjConfiguracion->CambioRol){
          $lbolGeneraAnexo = true;
        }
      }
      if ($pintIdOtrosAnexos){
        $larrCambios['OtrosAnexos'] = $pintIdOtrosAnexos;
        $lbolGeneraAnexo = true;
      }
      if ($pstrOtros){
        $larrCambios['Otros'] = $pstrOtros;
        $lbolGeneraAnexo = true;
      }

      if ($lobjDatosContractuales){
          if (!$lbolGeneraAnexo){
              $lstrResult = self::ChangeContractExecute($larrCambios, $lobjDatosContractuales);
          }else{
              //eliminamos cualquier otro anexo que haya estado pendiente

              self::$gobjPersona->DocumentoContractual->DocumentosRelacionados()->where('IdEstatus','!=',5)->delete();

              $lobjMyDocumentAnexo = new MyDocumentsContractPersonAnnexed();
              $lstrResult = $lobjMyDocumentAnexo::Create(self::$gobjPersona, $larrCambios);
          }
      }

      if($ldatFechaInicioFaena){
        if($ldatFechaInicioFaena<$lobjDatosContractuales->FechaInicioFaena){
          try{
            $flag=true;

            \DB::beginTransaction();
            $cliente = \DB::table('tbl_configuraciones')->where('nombre','CNF_APPNAME')->first();
            if($cliente->Valor != 'Transbank'){
              Contratospersonas::where('idPersona',$lobjDatosContractuales->IdPersona)->where('contrato_id',$lobjDatosContractuales->contrato_id)->update(['FechaInicioFaena'=>$ldatFechaInicioFaena]);
              \DB::table('tbl_movimiento_personal')->where('idPersona',$lobjDatosContractuales->IdPersona)->where('contrato_id',$lobjDatosContractuales->contrato_id)->where('IdAccion',1)->update(['FechaEfectiva'=>$ldatFechaInicioFaena]);
              \DB::table('tbl_documentos')
                ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
                ->where('tbl_documentos.contrato_id',$lobjDatosContractuales->contrato_id)
                ->where('tbl_documentos.IdEstatus',5)
                ->where('tbl_tipos_documentos.RelacionPersona',1)
                ->update(['tbl_documentos.IdEstatus'=>3,'Resultado'=>'posterior a la aprobación de este documento se modificó la nómina del personal para este mes, por lo tanto debe volver a proporcionar documento, asegurándose que incluya a toda la nómina para el periodo']);
            }
            if($flag){
              $requisitos = MyRequirements::getRequirements(3,1);
              foreach ($requisitos as $requisito) {
                MyRequirements::Load($requisito->IdRequisito,$lobjDatosContractuales->contrato_id,$lobjDatosContractuales->IdPersona);
              }
              $requisitos = MyRequirements::getRequirements(3,2);
              foreach ($requisitos as $requisito) {
                MyRequirements::Load($requisito->IdRequisito,$lobjDatosContractuales->contrato_id,$lobjDatosContractuales->IdPersona);
              }
              $requisitos = MyRequirements::getRequirements(3,3);
              foreach ($requisitos as $requisito) {
                MyRequirements::Load($requisito->IdRequisito,$lobjDatosContractuales->contrato_id,$lobjDatosContractuales->IdPersona);
              }
              //se cambia la fecha de emision de los documentos de carga unica que se crearon al asignarle contrato por la fecha actual.
              $ldatFechaEmision = new DateTime($ldatFechaInicioFaena);
              $ldatFechaEmision = $ldatFechaEmision->format('Y-m').'-01';
              $consulta = \DB::table('tbl_documentos')
                ->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
                ->join('tbl_requisitos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_requisitos.IdTipoDocumento')
                ->join('tbl_personas','tbl_documentos.IdEntidad','=','tbl_personas.IdPersona')
                ->where('tbl_documentos.Entidad',3)
                ->where('tbl_requisitos.IdEvento',3)
                ->where('tbl_personas.IdPersona', $lobjDatosContractuales->IdPersona)
                ->where('tbl_tipos_documentos.Periodicidad','<>', '1')
                ->update(['tbl_documentos.FechaEmision' => $ldatFechaEmision]);

            }
            \DB::commit();
            $lstrResult= array('status'=>'success','code'=>'1','message'=>'Cambios Aplicados');
          }catch(\Exception $e){
            $flag=false;
            \DB::rollback();
            $lstrResult= array('status'=>'fail','code'=>'2','message'=>'No se pudo realizar el cambio Fecha Inicio Faena');
          }
        }
        if($ldatFechaInicioFaena>$lobjDatosContractuales->FechaInicioFaena){
          $lstrResult= array('status'=>'fail','code'=>'2','message'=>'Solo se pueden fechas anteriores');
        }
      }

      return $lstrResult;

  }

  static public function ChangeContractExecute($parrData){

      if ($parrData){

        $lobjDatosContractuales = self::getDatosContractuales(); //actualizamos los datos contractuales

      //Registramos movimiento de personal
      if (isset($parrData['contrato_id'])){

        $larrDatosContractuales = $lobjDatosContractuales['result'];
        $lstrResult = self::ChangeContract($larrDatosContractuales->contrato_id, $parrData['contrato_id'], self::$gintIdPersona, $larrDatosContractuales->IdRol, $larrDatosContractuales->IdDocumento, $larrDatosContractuales->FechaVencimiento, $larrDatosContractuales->IdContratista);

      }

      \DB::table("tbl_contratos_personas")
        ->where("tbl_contratos_personas.IdPersona","=", self::$gintIdPersona)
        ->update($parrData);

      $lobjDatosContractuales = self::getDatosContractuales(); //actualizamos los datos contractuales
      $larrDatosContractuales = $lobjDatosContractuales['result'];

      if (isset($parrData['FechaVencimiento'])){
          \DB::table('tbl_documentos')
          ->where('tbl_documentos.IdDocumento','=',$larrDatosContractuales->IdDocumento)
          ->update(array("FechaVencimiento"=>$parrData['FechaVencimiento']));
      }

      //Actualizamos los documentos
      if (isset($parrData['IdTipoContrato'])){
          \DB::table('tbl_documento_valor')
          ->where('tbl_documento_valor.IdDocumento','=',$larrDatosContractuales->IdDocumento)
          ->where('tbl_documento_valor.IdTipoDocumentoValor','=', 127)
          ->update(array("Valor"=>$larrDatosContractuales->TipoContrato));

          $lobjTipoContratoPersona = \DB::table('tbl_tipos_contratos_personas')->where('id',$parrData['IdTipoContrato'])->first();
          if ($lobjTipoContratoPersona){
            if ($lobjTipoContratoPersona->Vencimiento=="1"){
              $lobjDocumentoVencimiento = \DB::table('tbl_documentos')->where('IdDocumento',$larrDatosContractuales->IdDocumento)->first();
                $ldatFechaVencimiento = $lobjDocumentoVencimiento->FechaVencimiento;
            }else{
              //var_dump($larrDatosContractuales->IdDocumento);
              \DB::table('tbl_documentos')
                ->where('tbl_documentos.IdDocumento','=',$larrDatosContractuales->IdDocumento)
                ->update(array("Vencimiento"=>0, "IdEstatusDocumento"=>null, "FechaVencimiento"=>null));
                $ldatFechaVencimiento = null;

            }
            \DB::table('tbl_contratos_personas')
            ->where('tbl_contratos_personas.IdDocumento','=',$larrDatosContractuales->IdDocumento)
            ->update(array("FechaVencimiento"=>$ldatFechaVencimiento));
          }
      }

      //var_dump($lobjTipoContratoPersona); die();

      return array("code"=>1, "message"=>"Cambios realizados satisfactoriamente", "result"=> "");
    }else{
      return array("code"=>1, "message"=>"No se realizó ningún cambio", "result"=> "");
    }

  }

  static public function ChangeContract($pintIdContrato, $pintIdContratoNuevo, $pintIdPersona, $pintIdRol, $pintIdDocumento, $pdatFechaContrato,$pintIdContratista = 0) {

    $ldatFechaActual = date('Y-m-d h:i:s');
    $ldatFechaEmision = date('Y-m')."-01";
    $lintIdUsuario = \Session::get('uid');
    $lintIdContrato = $pintIdContrato;
    $lintIdCambioContrato = false;
    $lintContratoComercial = 0;
    $larrCambiosContrato = array("CambioContrato"=>false, "CambioFecha" => false, "CambioRol" => false, "CambioOtros" => false);
    $lbolGeneraAnexo = "false";
    $lobjExtensionAnterior = array();
    $lobjExtensionNuevo = array();

    if ($pintIdContratista){

      //Recuperamos el entry_by_access del IdContraista nuevo
      $lobjContratista = \DB::table('tbl_contratistas')->select('entry_by_access')->where('IdContratista','=',$pintIdContratista)->first();
      $lintEntryByAccess = $lobjContratista->entry_by_access;

      //Cambiamos la relación del IdContratista en la relación con el contrato
      \DB::table("tbl_contratos_personas")
      ->where("tbl_contratos_personas.IdPersona","=",$pintIdPersona)
      ->where("tbl_contratos_personas.contrato_id","=",$pintIdContrato)
      ->update(array("IdContratista"=>$pintIdContratista, 'entry_by_access'=> $lintEntryByAccess));

      //Cambiamos los documentos de la persona
      \DB::table("tbl_documentos")
      ->where("tbl_documentos.IdEntidad","=",$pintIdPersona)
      ->where("tbl_documentos.entidad","=",3)
      ->where("tbl_documentos.contrato_id","=",$pintIdContrato)
      ->update(array("IdContratista"=>$pintIdContratista, 'entry_by_access'=>$lintEntryByAccess));

      //Actualiamos el entry_by_access de la persona y del acceso
      \DB::table("tbl_personas")
      ->where("tbl_personas.IdPersona","=",$pintIdPersona)
      ->update(array('entry_by_access'=>$lintEntryByAccess));

      \DB::table("tbl_accesos")
      ->where("tbl_accesos.IdPersona","=",$pintIdPersona)
      ->where("tbl_accesos.contrato_id","=",$pintIdContrato)
      ->update(array('entry_by_access'=>$lintEntryByAccess));

    }

    $lobjContratoPersonas = \DB::table("tbl_contratos_personas")
    ->select("tbl_contratos_personas.IdContratosPersonas", "tbl_contrato.contrato_id", "tbl_contratos_personas.IdPersona", "tbl_contratos_personas.IdRol", "tbl_contratos_personas.createdOn", "tbl_contrato.entry_by_access","tbl_contrato.id_extension")
    ->leftJoin("tbl_contrato","tbl_contrato.contrato_id","=","tbl_contratos_personas.contrato_id")
    ->where("tbl_contratos_personas.IdPersona","=",$pintIdPersona)
    ->where("tbl_contratos_personas.contrato_id","=",$pintIdContrato)
    ->get();

    //Si
    if ($lobjContratoPersonas){

      //Buscamos la parametrización que existe para un tipo de contrato
      $lobjExtensionAnterior = \DB::table('tbl_conf_anexos')
      ->where("tbl_conf_anexos.id_extension","=",$lobjContratoPersonas[0]->id_extension)
      ->get();

      if ($lobjContratoPersonas[0]->contrato_id != $pintIdContratoNuevo && $pintIdContratoNuevo) {

          //Buscamos la información del contrato
          $lobjContrato = \DB::table("tbl_contrato")
          ->select(\DB::raw("tbl_contrato.*"),"tbl_contrato.id_extension")
          ->where("tbl_contrato.contrato_id","=",$pintIdContratoNuevo)
          ->first();

          //Si existe seguimos
          if ($lobjContrato){

            //Se trata de un cambio de contrato
            $larrCambiosContrato["CambioContrato"] = true;

            //Buscamos la parametrización que existe para un tipo de contrato
            $lobjExtensionNuevo = \DB::table('tbl_conf_anexos')
            ->where("tbl_conf_anexos.id_extension","=",$lobjContrato->id_extension)
            ->get();

            $lintIdContrato = $pintIdContratoNuevo;
            //echo "Estas cambiando de ".$pintIdContrato." a ".$lintIdContrato." <br/>";

            //Validamos que el contrato se encuentre activo
            if ($lobjContrato->cont_estado!=1){
              return array("status" => "success", "code"=>2,"message"=>"El contrato no se encuentra activo", "result"=>$lobjContrato);
            }

            //Eliminamos los documentos por cargar del contrato actual
            $lobjDocumentosDistintos = \DB::table('tbl_documentos')
            ->where("tbl_documentos.contrato_id","=",$lobjContratoPersonas[0]->contrato_id)
            ->where("tbl_documentos.IdEntidad","=",$pintIdPersona)
            ->where("tbl_documentos.Entidad","=",3)
            ->where("tbl_documentos.IdEstatus","!=",5)
            ->delete();

            //Validamos si hay un cambio de rol implicito
            if (($lobjContratoPersonas[0]->IdRol != $pintIdRol && $pintIdRol)){
              $larrCambiosContrato["CambioRol"] = true;
            }

            \DB::beginTransaction();
             //echo "Iniciamos <br/>";

            //echo "Mandamos a reiniciar a la persona <br/>".$pintIdPersona;
            //Limpiamos los documentos de la persona
            $larrResultReset = self::ResetPeople($pintIdPersona,1,"21");

            //echo "Volvi de reiniciar <br/>";
            //Actualizamos el contrato de trabajo
            $lintContratoComercial = \DB::table("tbl_documentos")
            ->where("tbl_documentos.contrato_id","=",$lobjContratoPersonas[0]->contrato_id)
            ->where("tbl_documentos.Entidad","=","3")
            ->where("tbl_documentos.IdEntidad","=",$pintIdPersona)
            ->whereexists(function($query) {
                  $query->select(\DB::raw(1))
                        ->from("tbl_tipos_documentos")
                        ->whereraw("tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento")
                        ->whereraw("tbl_tipos_documentos.IdProceso = 21");
            })
            ->update(array("entry_by_access" => $lobjContrato->entry_by_access,
                           "contrato_id" => $lobjContrato->contrato_id ));

            //echo "Cambiamos al contrato <br/>".$pintIdPersona;
            //Actualizamos los documentos que quedaron
            /*
            $lintResultadoUpdate = \DB::table("tbl_documentos")
            ->where("tbl_documentos.Entidad","=",3)
            ->where("tbl_documentos.IdEntidad","=",$pintIdPersona)
            ->where("tbl_documentos.IdEstatus","=",5)
            ->update(array("entry_by_access"=>$lobjContrato->entry_by_access, "contrato_id"=>$lobjContrato->contrato_id));
            */

            //Retiramos a la persona del contrato
            $lintResultContratoPersonas = \DB::table("tbl_contratos_personas")
            ->where("tbl_contratos_personas.IdContratosPersonas","=", $lobjContratoPersonas[0]->IdContratosPersonas)
            ->delete();

            $lintIdCambioContrato = true;

              if ($pintIdRol!=2){

                  $lintlicenHist = \DB::table('tbl_documentos')
                      ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento", "=", "tbl_tipos_documentos.IdTipoDocumento")
                      ->where("tbl_documentos.Entidad","=",3)
                      ->where("tbl_documentos.IdEntidad","=",$lobjContratoPersonas[0]->IdPersona)
                      ->where("tbl_tipos_documentos.IdProceso","=",49)
                      ->where("tbl_documentos.IdEstatus","=",5)
                      ->get();

                  if (count($lintlicenHist)>0){

                      foreach ($lintlicenHist as $larrlicenHist) {
                          \MyPeoples::DatosHistorico($larrlicenHist->IdDocumento,5);
                      }
                  }

              }


              //Registramos la salida de la persona del contrato
            self::RegisterMovePeople($pintIdPersona, 2, $pintIdContrato,null,1);

            $larrResultAssign = self::AssignContract($pintIdContratoNuevo, $pintIdPersona, $pintIdRol);

            \DB::commit();

          }

        }

        //Hacemos cambio de rol
        if (($lobjContratoPersonas[0]->IdRol != $pintIdRol && $pintIdRol) && ($lintIdCambioContrato==false) ){

          $larrCambiosContrato["CambioRol"] = true;

          //buscamos si el rol anterior posee documentos asociados
          self::$lintRol = $lobjContratoPersonas[0]->IdRol;
          $lintResultadoEliminar = \DB::table('tbl_documentos')
          ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                      ->from('tbl_requisitos')
                      ->join("tbl_requisitos_detalles","tbl_requisitos.IdRequisito","=","tbl_requisitos_detalles.IdRequisito")
                      ->whereRaw("tbl_requisitos_detalles.IdEntidad = ".self::$lintRol)
                      ->whereRaw('tbl_documentos.IdTipoDocumento = tbl_requisitos.IdTipoDocumento');
            })
          ->where("tbl_documentos.Entidad","=",3)
          ->where("tbl_documentos.IdEntidad","=",$lobjContratoPersonas[0]->IdPersona)
          ->where("tbl_documentos.IdEstatus","!=",5)
          ->delete();

            if ($pintIdRol!=2){

                $lintlicenHist = \DB::table('tbl_documentos')
                    ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento", "=", "tbl_tipos_documentos.IdTipoDocumento")
                    ->where("tbl_documentos.Entidad","=",3)
                    ->where("tbl_documentos.IdEntidad","=",$lobjContratoPersonas[0]->IdPersona)
                    ->where("tbl_tipos_documentos.IdProceso","=",49)
                    ->where("tbl_documentos.IdEstatus","=",5)
                    ->get();

                if (count($lintlicenHist)>0){

                    foreach ($lintlicenHist as $larrlicenHist) {
                        \MyPeoples::DatosHistorico($larrlicenHist->IdDocumento,5);
                    }
                }

            }

            self::RestoreHistorico($pintIdContrato,$pintIdPersona,$pintIdRol);

          $lintIdContratosPersonas = $lobjContratoPersonas[0]->IdContratosPersonas;
          $lintIdResultado = \DB::table("tbl_contratos_personas")
          ->where("tbl_contratos_personas.IdContratosPersonas","=",$lintIdContratosPersonas)
          ->update(array("IdRol"=>$pintIdRol));

        }

        $ldatFechaContrato = "";

        //Solo si hay un contrato activo es que vamos a solicitar un anexo
         if ($pintIdDocumento){
          $lobjDocumentoContrato = \DB::table("tbl_documentos")
          ->where("tbl_documentos.IdDocumento","=",$pintIdDocumento)
          ->first();
          if ($lobjDocumentoContrato){
            if ($lobjDocumentoContrato->FechaVencimiento != $pdatFechaContrato) {
              $larrCambiosContrato["CambioFecha"] = true;
              $ldatFechaContrato = $pdatFechaContrato;
            }
          }

          if (isset($larrResultAssig['entry_by_access'])){
            $lintEntryBy = $larrResultAssig['entry_by_access'];
          }elseif (isset($lobjContratoPersonas->entry_by_access)){
            $lintEntryBy = $lobjContratoPersonas->entry_by_access;
          }else{
            $lintEntryBy = null;
          }

          \DB::table('tbl_documentos')
          ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento", "=", "tbl_tipos_documentos.IdTipoDocumento")
          ->where("tbl_documentos.IdEntidad","=", $pintIdPersona)
          ->where("tbl_documentos.Entidad","=", 3)
          ->where("tbl_tipos_documentos.IdProceso","=",3) //proceso 3: anexo de contrato
          ->where("tbl_documentos.IdEstatus","!=", 5)
          ->delete();

          $idTipoDocumentoAnexoContrato = \DB::table('tbl_tipos_documentos')->where('IdProceso',3)->first();

            //Guardamos a la persona en el contrato
            $lobjDocumentoData = array("IdDocumentoRelacion" => $pintIdDocumento,
                                     "contrato_id"=>$lintIdContrato,
                                     "Entidad" => 3,
                                     "IdEntidad"=>$pintIdPersona,
                                     "IdTipoDocumento" => $idTipoDocumentoAnexoContrato->IdTipoDocumento,
                                     "FechaEmision"=> $ldatFechaEmision,
                                     "FechaVencimiento" => NULL,
                                     "IdEstatus" => 1,
                                     "createdOn"=>$ldatFechaActual,
                                     "entry_by"=>$lintIdUsuario,
                                     "entry_by_access"=>$lintEntryBy);
            if ($lobjDocumentoContrato) {

              //Si no se realizó cambio en ningun tipo, quiere decir que se trata de otros
              if ($larrCambiosContrato["CambioContrato"] == false && $larrCambiosContrato["CambioFecha"] == false && $larrCambiosContrato["CambioRol"] == false){
                  $larrCambiosContrato["CambioOtros"] == true;
              }

              //Valisamos si debo o no levantar el anexo
              foreach ($lobjExtensionAnterior as $larrGeneraAnexo) {
                  if ($larrGeneraAnexo->CambioContrato == "1" &&  $larrCambiosContrato["CambioContrato"] == true){
                    $lbolGeneraAnexo = "true";
                  }elseif ($larrGeneraAnexo->CambioFecha == "1" &&  $larrCambiosContrato["CambioFecha"] == true){
                    $lbolGeneraAnexo = "true";
                  }elseif ($larrGeneraAnexo->CambioRol == "1" &&  $larrCambiosContrato["CambioRol"] == true){
                    $lbolGeneraAnexo = "true";
                  }elseif ($larrGeneraAnexo->CambioOtros == "1" &&  $larrCambiosContrato["CambioOtros"] == true){
                    $lbolGeneraAnexo = "true";
                  }
              }

				//var_dump($lobjExtensionAnterior);

              //Valisamos si debo o no levantar el anexo
              foreach ($lobjExtensionNuevo as $larrGeneraAnexo) {
                  if ($larrGeneraAnexo->CambioContrato == "1" &&  $larrCambiosContrato["CambioContrato"] == true){
                    $lbolGeneraAnexo = "true";
                  }elseif ($larrGeneraAnexo->CambioFecha == "1" &&  $larrCambiosContrato["CambioFecha"] == true){
                    $lbolGeneraAnexo = "true";
                  }elseif ($larrGeneraAnexo->CambioRol == "1" &&  $larrCambiosContrato["CambioRol"] == true){
                    $lbolGeneraAnexo = "true";
                  }elseif ($larrGeneraAnexo->CambioOtros == "1" &&  $larrCambiosContrato["CambioOtros"] == true){
                    $lbolGeneraAnexo = "true";
                  }
              }

			  //var_dump($lobjExtensionNuevo);

			  //var_dump($larrCambiosContrato);

              if ($lbolGeneraAnexo == "true"){
                $lintResultAnexo = \DB::table("tbl_documentos")->insertGetId($lobjDocumentoData);
              }

            }
        }

        return array("status" => "success", "code"=>1,"message"=>"Persona cambiada satisfactoriamente", "result"=>$lobjContratoPersonas);

      }else{
        return array("status" => "success", "code"=>1,"message"=>"La persona no se encuentra asignado al contrato indicado", "result"=>$lobjContratoPersonas);
      }

  }

    static public function EsSubcontratista($pintIdContrato)
    {
        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');

        $lobjSubContratista = \DB::table('tbl_contratistas')
            ->join("tbl_contratos_subcontratistas","tbl_contratistas.IdContratista","=","tbl_contratos_subcontratistas.IdSubContratista")
            ->where("tbl_contratistas.entry_by_access","=",$lintIdUser)
            ->where("tbl_contratos_subcontratistas.contrato_id","=",$pintIdContrato)
            ->pluck("tbl_contratistas.IdContratista");

        if ($lobjSubContratista){
            return $lobjSubContratista;
        }else{
            return 0;
        }

    }

        static public function BuscaContratista($pintIdContrato)
    {

        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');
        if ($lintLevelUser==6){
            $empresa = \DB::table('tbl_contrato')->where('contrato_id', '=', $pintIdContrato)->pluck('IdContratista');

            $contratista = \DB::table('tbl_contratistas')
                ->join("tbl_contrato","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista")
                ->where("tbl_contratistas.entry_by_access","=",$lintIdUser)
                ->where("tbl_contrato.contrato_id","=",$pintIdContrato)
                ->pluck("tbl_contratistas.IdContratista");

            if ($empresa==$contratista){
                $resultado = $contratista;
            }else{

                $resultado = self::EsSubcontratista($pintIdContrato);
            }

        }
        else{
            $resultado = \DB::table('tbl_contrato')->where('contrato_id', '=', $pintIdContrato)->pluck('IdContratista');
        }
        return $resultado;

    }
  static public function ChangePeople($pintIdPersona, $pintIdContratista, $pintIdRol) {
      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = \Session::get('uid');
      $ldatFechaActual = date('Y-m-d H:i:s');


  }
  static public function AssignContract($pintIdContrato, $pintIdPersona, $pintIdRol, $pintIdContratista=0, $pdatFechaInicioFaena = '') {

    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');
    $ldatFechaActual = date('Y-m-d H:i:s');
    if ($pdatFechaInicioFaena){
      $ldatFechaEmision = new DateTime($pdatFechaInicioFaena);
      $ldatFechaEmision = $ldatFechaEmision->format('Y-m').'-01';
    }else{
      $ldatFechaActual = date('Y-m').'-01';
    }

    //Buscamos la información del contrato
    $lobjContrato = Contratos::find($pintIdContrato);

    //Si existe seguimos
    if ($lobjContrato){

        //Validamos que el contrato se encuentre activo
        //if ($lobjContrato->Estatus->BloqueaVinculacion){
          //return array("status" => "success", "code"=>2, "message"=>"El estatus del contrato no permite asignación de personas", "result"=>$lobjContrato);
        //}

        //Determinamos el entry_by_access
        if ($lintLevelUser==6 && $lintIdUser!=$lobjContrato->entry_by_access){
          $lintEntryByAccess = $lintIdUser;
        }else{
          $lintEntryByAccess = $lobjContrato->entry_by_access;
        }

        $SubCont = self::EsSubcontratista($pintIdContrato);

        if ( $SubCont>0){
          //Verificamos que si es un subcontratista la carta de aprobacion este aprobada
          $lobjCartaAprobacion = \DB::table("tbl_documentos")
              ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento", "=", "tbl_tipos_documentos.IdTipoDocumento")
              ->where("tbl_documentos.Entidad","=","9")
              ->where("tbl_documentos.IdEntidad","=",$SubCont)
              ->where("tbl_documentos.contrato_id","=",$pintIdContrato)
              ->where("tbl_tipos_documentos.IdProceso","=",89)
              ->where("tbl_documentos.IdEstatus","!=",5)
              ->get();

          if ($lobjCartaAprobacion){
              return array("status" => "success", "code"=>4, "message"=>"El subcontatista no tiene la carta de aceptación aprobada", "result"=>$lobjContrato);
          }
      }

      \DB::beginTransaction();

      //Verificamos que la fecha de inicio no sea anterior a la del contrato
      if ($lobjContrato->cont_fechaInicio > $pdatFechaInicioFaena) {
        return array("status" => "error", "code"=>5, "message"=>"La fecha de inicio en faena es menor a la fecha de inicio del contrato comercial", "result"=>[]);
      }

        $contr = self::BuscaContratista($pintIdContrato);
        if ($contr){
          $contr = $contr[0];
        }

        //Actualiza los documentos que tenía la persona asociada
        /*
        //se comenta mientras se valida como recuperar los documentos que permanencen a las personas.
        \DB::table("tbl_documentos")
        ->where("tbl_documentos.Entidad","=","3")
        ->where("tbl_documentos.IdEntidad","=",$pintIdPersona)
        ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                      ->from('tbl_tipos_documentos')
                      ->whereRaw('tbl_tipos_documentos.idtipodocumento = tbl_documentos.idtipodocumento')
                      ->whereRaw('tbl_tipos_documentos.Permanencia in (1,2)');
        })
        ->update(array("entry_by"=> $lintIdUser,
                       "entry_by_access" => $lintEntryByAccess,
                       "FechaEmision" => $ldatFechaEmision,
                       "contrato_id" =>  $lobjContrato->contrato_id,
                       "idcontratista" =>  $contr,
                       "updatedOn" => new \DateTime()));
        */

      //Validamos que la persona no se encuentre asignada a otro contrato
      if (self::$gobjPersona->Contratospersonas === null){

        self::RestoreHistorico($pintIdContrato, $pintIdPersona, $pintIdRol, $ldatFechaEmision);

        $lobjContratosPersonas = new Contratospersonas();
        $lobjContratosPersonas->contrato_id = $pintIdContrato;
        $lobjContratosPersonas->IdContratista = $contr;
        $lobjContratosPersonas->IdRol = $pintIdRol;
        $lobjContratosPersonas->IdPersona = self::$gobjPersona->IdPersona;
        $lobjContratosPersonas->FechaInicioFaena = $ldatFechaEmision;
        $lobjContratosPersonas->entry_by = $lintIdUser;
        $lobjContratosPersonas->entry_by_access = $lintEntryByAccess;
        $lobjContratosPersonas->acreditacion = $lobjContrato->acreditacion;
        $lobjContratosPersonas->controllaboral = $lobjContrato->controllaboral;

        //Guardamos a la persona en el contrato
        self::$gobjPersona->Contratospersonas()->save($lobjContratosPersonas);
        self::$gobjPersona->load('Contratospersonas');

        //Creamos la relacion de la persona con el contratista
        $lintResultadoUpdatePersona = \DB::table("tbl_personas")
        ->where("tbl_personas.IdPersona","=",$pintIdPersona)
        ->update(array("entry_by_access"=>$lintEntryByAccess));

        //Se levantan los documentos
        $lobjMyRequirements = new MyRequirements($pintIdContrato);
        $lobjRequirements = $lobjMyRequirements::getRequirements(3); // 3 = evento asignación de persona

        $lintAcreditacion = self::$gobjPersona->Contratospersonas->Acreditacion;
        $lintControlChecklLaboral = self::$gobjPersona->Contratospersonas->ControlCheckLaboral;

        foreach ($lobjRequirements as $larrRequirements) {

          if ( ( $larrRequirements->TipoDocumento->Acreditacion && $lintAcreditacion) || ($larrRequirements->TipoDocumento->ControlCheckLaboral && $lintControlChecklLaboral) || ( !$larrRequirements->Acreditacion && !$larrRequirements->ControlCheckLaboral ) ){
            $lobjRequirements = $lobjMyRequirements::Load($larrRequirements->IdRequisito, $pintIdContrato, $pintIdPersona);
          }

        }

        if (self::$gobjPersona->DocumentoContractual) {
          self::$gobjPersona->Contratospersonas->IdDocumento = self::$gobjPersona->DocumentoContractual->IdDocumento;
          self::$gobjPersona->Contratospersonas->IdEstatus = self::$gobjPersona->DocumentoContractual->IdEstatus;
          //Buscamos el IdTipoContrato
          $lobjTipoContrato = self::$gobjPersona->DocumentoContractual->Documentovalor()->where('tbl_documento_valor.IdTipoDocumentoValor',127)->join('tbl_tipos_contratos_personas','tbl_tipos_contratos_personas.Nombre','=','tbl_documento_valor.valor')->select('tbl_tipos_contratos_personas.id')->first();
          if ($lobjTipoContrato){
            self::$gobjPersona->Contratospersonas->IdTipoContrato = $lobjTipoContrato->id;
          }
          self::$gobjPersona->Contratospersonas->FechaVencimiento = self::$gobjPersona->DocumentoContractual->FechaVencimiento;
          self::$gobjPersona->Contratospersonas->save();
        }

        $lobjCheckLaboral = new MyCheckLaboral();
        $lobjCheckLaboral::UpdateDocument($pintIdContrato);

        //Registramos el movimiento
        self::RegisterMovePeople($pintIdPersona, 1, $pintIdContrato, $pdatFechaInicioFaena);

        self::UpdateMaestroMovil($pintIdPersona, $pintIdContrato, $pdatFechaInicioFaena,'AssignContract','Vigente');

        /*  +++++ DISCAPACIDAD ++++++++ */
        $persona = \DB::table("tbl_personas")->where('IdPersona', $pintIdPersona)->first();
        $discapacidad = $persona->discapacidad;
        if($discapacidad){
          $docDisca = \DB::table("tbl_documentos")
            ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
            ->where('tbl_documentos.Entidad', 3)
            ->where('tbl_documentos.IdEntidad', $pintIdPersona)
            ->where('tbl_tipos_documentos.IdProceso', 142)
            ->first();

          if($docDisca){
            //Si la persona tiene creado el documento de discapacidad y ademas discapacidad esta en 1 se actualiza el contratista, el cntrato y el entry_by_access
           $update = \DB::table("tbl_documentos")
             ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
             ->where('tbl_documentos.Entidad', 3)
             ->where('tbl_documentos.IdEntidad', $pintIdPersona)
             ->where('tbl_tipos_documentos.IdProceso', 142)
             ->update(array("tbl_documentos.entry_by"=> $lintIdUser,
                           "tbl_documentos.entry_by_access" => $lintEntryByAccess,
                           "tbl_documentos.FechaEmision" => $ldatFechaEmision,
                           "tbl_documentos.contrato_id" =>  $lobjContrato->contrato_id,
                           "tbl_documentos.idcontratista" =>  $contr,
                           "tbl_documentos.updatedOn" => new \DateTime()));
          }else{
            //se obtiene el id de tipo de documento con id proceso 142
            $idTipoDocumento = \DB::table('tbl_tipos_documentos')->where('IdProceso',142)->first();
            $idTipoDocumento = $idTipoDocumento->IdTipoDocumento;
            //no se tiene el documento asi que se levanta la solicitud.
            $lobjDocumentos = new MyDocuments();
						$larrResultado = $lobjDocumentos::Save($idTipoDocumento,3,$pintIdPersona,"",$contr, $lobjContrato->contrato_id);
          }
        }
         // Pensionado
        $pensionado = $persona->pensionado;
        if($pensionado){
          $docPen = \DB::table("tbl_documentos")
            ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
            ->where('tbl_documentos.Entidad', 3)
            ->where('tbl_documentos.IdEntidad', $pintIdPersona)
            ->where('tbl_tipos_documentos.IdProceso', 143)
            ->first();

          if($docPen){
            //Si la persona tiene creado el documento de pensionado y ademas pensionado esta en 1 se actualiza el contratista, el cntrato y el entry_by_access
          $update = \DB::table("tbl_documentos")
            ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
            ->where('tbl_documentos.Entidad', 3)
            ->where('tbl_documentos.IdEntidad', $pintIdPersona)
            ->where('tbl_tipos_documentos.IdProceso', 143)
            ->update(array("tbl_documentos.entry_by"=> $lintIdUser,
                          "tbl_documentos.entry_by_access" => $lintEntryByAccess,
                          "tbl_documentos.FechaEmision" => $ldatFechaEmision,
                          "tbl_documentos.contrato_id" =>  $lobjContrato->contrato_id,
                          "tbl_documentos.idcontratista" =>  $contr,
                          "tbl_documentos.updatedOn" => new \DateTime()));
          }else{
            //se obtiene el id de tipo de documento con id proceso 143
            $idTipoDocumento = \DB::table('tbl_tipos_documentos')->where('IdProceso',143)->first();
            $idTipoDocumento = $idTipoDocumento->IdTipoDocumento;
            //no se tiene el documento asi que se levanta la solicitud.
            $lobjDocumentos = new MyDocuments();
            $larrResultado = $lobjDocumentos::Save($idTipoDocumento,3,$pintIdPersona,"",$contr, $lobjContrato->contrato_id);
          }
        }


        \DB::commit();

        $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
    		if($sitio->Valor=='CCU'){
          $gobjPersona = self::$gobjPersona;
          $contratista = \DB::table('tbl_contratistas')->where('IdContratista',$lobjContrato->IdContratista)->first();
          $servicio = \DB::table('tbl_contrato')->join('tbl_contratos_servicios','tbl_contrato.idservicio','=','tbl_contratos_servicios.id')->where('tbl_contrato.contrato_id',$lobjContrato->contrato_id)->first();
          $rol = \DB::table('tbl_contratos_personas')->join('tbl_roles','tbl_roles.IdRol','=','tbl_contratos_personas.IdRol')->where('tbl_contratos_personas.contrato_id',$lobjContrato->contrato_id)->where('tbl_contratos_personas.IdPersona',$pintIdPersona)->first();
          $uen = \DB::table('tbl_contratos_centros')->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')->join('tbl_centro','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')->where('tbl_contrato.contrato_id',$lobjContrato->contrato_id)->first();
          $usuarioContrato = \DB::table('tbl_contrato')->join('tb_users','tbl_contrato.usuarioContrato','=','tb_users.id')->where('tbl_contrato.contrato_id',$lobjContrato->contrato_id)->first();
          if(!$servicio){   $servicio='';  }
          if(!$rol){        $rol='';      }
          if($usuarioContrato){
            \Mail::send('emails.assignContract',
              ['lobjPersona'=>$gobjPersona,
               'lobjContrato'=>$lobjContrato,
               'lobjContratista'=>$contratista,
               'lobjServicio'=>$servicio,
               'lobjRol'=>$rol,
               'lobUen'=>$uen,
               'lobjUsuario'=>["usuario"=>$usuarioContrato->first_name." ".$usuarioContrato->last_name, "perfil"=>'Usuario Contrato']
             ], function ($m) use ($usuarioContrato){
              $email = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_EMAIL')->first();
              $m->from($email->Valor);
              $m->to($usuarioContrato->email)->subject("[Asignación Trabajador a Contrato]");
              //$m->to("gneira@sourcing.cl")->subject("[Asignación Contrato]");
            });
          }
          $usuarioADC = \DB::table('tbl_contrato')->join('tb_users','tbl_contrato.admin_id','=','tb_users.id')->where('tbl_contrato.contrato_id',$lobjContrato->contrato_id)->first();
          if($usuarioADC){
            \Mail::send('emails.assignContract',
              ['lobjPersona'=>$gobjPersona,
               'lobjContrato'=>$lobjContrato,
               'lobjContratista'=>$contratista,
               'lobjServicio'=>$servicio,
               'lobjRol'=>$rol,
               'lobUen'=>$uen,
               'lobjUsuario'=>["usuario"=>$usuarioADC->first_name." ".$usuarioADC->last_name, "perfil"=>'Administrador Contrato']
             ], function ($m) use ($usuarioADC){
              $email = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_EMAIL')->first();
              $m->from($email->Valor);
              $m->to($usuarioADC->email)->subject("[Asignación Trabajador a Contrato]");
              //$m->to("gneira@sourcing.cl")->subject("[Asignación Contrato]");
            });
          }

          $usuarioContratista = \DB::table('tbl_contrato')
            ->join('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
            ->join('tb_users','tbl_contratistas.entry_by_access','=','tb_users.id')
            ->where('tbl_contrato.contrato_id',$lobjContrato->contrato_id)->first();
          if($usuarioContratista){
            \Mail::send('emails.assignContract',
              ['lobjPersona'=>$gobjPersona,
               'lobjContrato'=>$lobjContrato,
               'lobjContratista'=>$contratista,
               'lobjServicio'=>$servicio,
               'lobjRol'=>$rol,
               'lobUen'=>$uen,
               'lobjUsuario'=>["usuario"=>$usuarioContratista->first_name." ".$usuarioContratista->last_name, "perfil"=>'Usuario Contratista']
             ], function ($m) use ($usuarioContratista){
              $email = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_EMAIL')->first();
              $m->from($email->Valor);
              $m->to($usuarioContratista->email)->subject("[Asignación Trabajador a Contrato]");
              //$m->to("gneira@sourcing.cl")->subject("[Asignación Contrato]");
            });
          }

          $usuarioPrevencionista = \DB::table('tbl_contrato')
            ->join('tbl_groups_levels_assoc_contract','tbl_contrato.contrato_id','=','tbl_groups_levels_assoc_contract.contrato_id')
            ->join('tb_users','tb_users.id','=','tbl_groups_levels_assoc_contract.user_id')
            ->where('tbl_contrato.contrato_id',$lobjContrato->contrato_id)
            ->where('tbl_groups_levels_assoc_contract.level',21)
            ->first();
          if($usuarioPrevencionista){
            \Mail::send('emails.assignContract',
              ['lobjPersona'=>$gobjPersona,
               'lobjContrato'=>$lobjContrato,
               'lobjContratista'=>$contratista,
               'lobjServicio'=>$servicio,
               'lobjRol'=>$rol,
               'lobUen'=>$uen,
               'lobjUsuario'=>["usuario"=>$usuarioPrevencionista->first_name." ".$usuarioPrevencionista->last_name, "perfil"=>'Prevencionista']
             ], function ($m) use ($usuarioPrevencionista){
              $email = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_EMAIL')->first();
              $m->from($email->Valor);
              $m->to($usuarioPrevencionista->email)->subject("[Asignación Trabajador a Contrato]");
              //$m->to("gneira@sourcing.cl")->subject("[Asignación Contrato]");
            });
          }
        }

        return array("status" => "success", "code"=>1,"message"=>"Persona asignada satisfactoriamente", "result"=>self::$gobjPersona->Contratospersonas);
      }else{
        \DB::rollback();
        return array("status" => "success", "code"=>3,"message"=>"La persona ya se encuentra asignada a un contrato", "result"=>self::$gobjPersona->Contratospersonas);
      }
    }else{
      return array("status" => "success", "code"=>0,"message"=>"El contrato no existe", "result"=>'');
    }

  }

  static public function RegisterMovePeople($pintIdPersona, $pintIdMovimiento, $pintIdContrato, $pdatFechaEfectiva = null, $pintMotivo = 0) {

      if($pdatFechaEfectiva==null){
        $pdatFechaEfectiva=date('Y-m-d');
      }

      $lintIdMovimientoPersona = \DB::table('tbl_movimiento_personal')
                                ->insertGetId([
                                  "IdAccion" => $pintIdMovimiento,
                                  "contrato_id" => $pintIdContrato,
                                  "IdPersona" => $pintIdPersona,
                                  "FechaEfectiva" => $pdatFechaEfectiva,
                                  "Motivo" => $pintMotivo,
                                  "entry_by" => \Session::get('uid')]
                                );

  }

  static public function UpdateMaestroMovil($pintIdPersona, $pintIdContrato, $pdatFechaEfectiva, $strAccion, $estado) {

    $lintIdContratista = \DB::table('tbl_contrato')->where('contrato_id',$pintIdContrato)->value('IdContratista');
    $lintContratoPrueba = \DB::table('tbl_contrato')->where('contrato_id',$pintIdContrato)->value('ContratoPrueba');

    //ignora contratos de prueba
    if($lintContratoPrueba==0){
      $periodo = new \DateTime($pdatFechaEfectiva);
      if($pdatFechaEfectiva>$periodo->format('Y-m-01')){
        $periodo = $periodo->modify('+1 month');
      }
      $periodo = $periodo->format('Y-m-01');
      $periodoOriginal = $periodo;
      $hoy = new \DateTime();
      $hoy = $hoy->format('Y-m-01');

      $pmv =\DB::table('tbl_personas_maestro_movil')
              ->where('idpersona',$pintIdPersona)
              ->where('contrato_id',$pintIdContrato)
              ->where('idcontratista',$lintIdContratista);

      if($strAccion=='LeaveContract'){
        $ldatFechaEfectiva = $pmv->where('periodo',$periodo)->value('FechaEfectiva');
        $leaveUpdate = $pmv->where('periodo',$periodo)->update(['Estatus'=>$estado,'updated_at'=>date('Y-m-d H:i'),'FechaEfectiva'=>$pdatFechaEfectiva,'FechaAnterior'=>$ldatFechaEfectiva,'FechaFinFaena'=>$ldatFechaEfectiva]);

        if($periodo<$hoy){
          do{
            $periodo = new \DateTime($periodo);
            $periodo = $periodo->modify('+1 month');
            $periodo = $periodo->format('Y-m-01');
            \DB::table('tbl_personas_maestro_movil')
                    ->where('idpersona',$pintIdPersona)
                    ->where('contrato_id',$pintIdContrato)
                    ->where('idcontratista',$lintIdContratista)
                    ->where('periodo',$periodo)
                    ->update(['Estatus'=>$estado,'updated_at'=>date('Y-m-d H:i'),'FechaFinFaena'=>$ldatFechaEfectiva]);
            }while($periodo<$hoy);
        }
      }

      if($strAccion=='LeaveContractApproved'){
        $leaveUpdate = $pmv->where('periodo',$periodo)->update(['Estatus'=>$estado,'updated_at'=>date('Y-m-d H:i')]);
        if($periodo<$hoy){
          do{
            $periodo = new \DateTime($periodo);
            $periodo = $periodo->modify('+1 month');
            $periodo = $periodo->format('Y-m-01');
            \DB::table('tbl_personas_maestro_movil')
                    ->where('idpersona',$pintIdPersona)
                    ->where('contrato_id',$pintIdContrato)
                    ->where('idcontratista',$lintIdContratista)
                    ->where('periodo',$periodo)
                    ->update(['Estatus'=>$estado,'updated_at'=>date('Y-m-d H:i')]);
          }while($periodo<$hoy);
        }
      }

      if($strAccion=='LeaveContractApprovedFinish'){
        $leaveUpdate = $pmv->where('periodo',$periodo)->update(['Estatus'=>$estado,'updated_at'=>date('Y-m-d H:i')]);
        $periodo = new \DateTime($periodo);
        $periodo = $periodo->modify('+1 month');
        $periodo = $periodo->format('Y-m-01');
        \DB::table('tbl_personas_maestro_movil')
                ->where('idpersona',$pintIdPersona)
                ->where('contrato_id',$pintIdContrato)
                ->where('idcontratista',$lintIdContratista)
                ->where('periodo',$periodo)
                ->delete();
        if($periodo<$hoy){
          do{
            $periodo = new \DateTime($periodo);
            $periodo = $periodo->modify('+1 month');
            $periodo = $periodo->format('Y-m-01');
            \DB::table('tbl_personas_maestro_movil')
                    ->where('idpersona',$pintIdPersona)
                    ->where('contrato_id',$pintIdContrato)
                    ->where('idcontratista',$lintIdContratista)
                    ->where('periodo',$periodo)
                    ->delete();
          }while($periodo<$hoy);
        }
      }

      if($strAccion=='AssignContract'){
        if($periodo<$hoy){
          do{
            \DB::table('tbl_personas_maestro_movil')->insert([
              'periodo'=>$periodo,
              'idpersona'=>$pintIdPersona,
              'contrato_id'=>$pintIdContrato,
              'idcontratista'=>$lintIdContratista,
              'created_at'=>date('Y-m-d H:i'),
              'Estatus'=>$estado,
              'FechaEfectiva'=>$pdatFechaEfectiva,
              'FechaInicioFaena'=>$pdatFechaEfectiva
            ]);
            $periodo = new \DateTime($periodo);
            $periodo = $periodo->modify('+1 month');
            $periodo = $periodo->format('Y-m-01');
            }while($periodo<$hoy);
        }else{
          \DB::table('tbl_personas_maestro_movil')->insert([
            'periodo'=>$periodo,
            'idpersona'=>$pintIdPersona,
            'contrato_id'=>$pintIdContrato,
            'idcontratista'=>$lintIdContratista,
            'created_at'=>date('Y-m-d H:i'),
            'Estatus'=>$estado,
            'FechaEfectiva'=>$hoy,
            'FechaInicioFaena'=>$pdatFechaEfectiva
          ]);
        }
        $cliente = \DB::table('tbl_configuraciones')->where('nombre','CNF_APPNAME')->first();
        if($cliente->Valor != 'Transbank'){
          \DB::table('tbl_documentos')
            ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
            ->where('tbl_documentos.contrato_id',$pintIdContrato)
            ->where('tbl_documentos.IdEstatus',5)
            ->where('tbl_tipos_documentos.RelacionPersona',1)
            ->where('tbl_documentos.FechaEmision',$periodoOriginal)
            ->update(['tbl_documentos.IdEstatus'=>3,'Resultado'=>'posterior a la aprobación de este documento se modificó la nómina del personal para este mes, por lo tanto debe volver a proporcionar documento, asegurándose que incluya a toda la nómina para el periodo']);
        }
      }
    }

  }

}
