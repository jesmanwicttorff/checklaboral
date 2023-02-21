<?php


namespace App\Library;

use App\Models\Documentos;
use App\Models\Documentovalor;
use App\Models\Documentoslog;
use App\Models\Contratistas;
use App\Models\Contratos;
use App\Models\Personas;
use App\Models\Centros;
use App\Models\Documentoshistorico;
use App\Models\Documentovalorhistorico;
use App\Models\Documentosloghistorico;
use App\Models\TipoDocumentos;
use App\Library\MyCheckLaboral;
use App\Library\Acreditacion;
use App\Library\AcreditacionContrato;
use App\Library\AcreditacionActivos;
use Illuminate\Http\Request;
use Validator, Input;
use DB;


class MyDocuments{

    static protected $gintIdUser;
    static private $gintIdLevelUser;
    static protected $garrProcesos = array("F301"=>1,
                                         "ContratoTrabajo" => 21,
                                         "ContratoTrabajoAnexo"=>3,
                                         "Finiquito"=>4,
                                         "Previred"=>212,
                                         "PreviredTrabajador"=>120,
                                         "LiquidacionTrabajador"=>118);
    static private $gintIdDocumento;
    static protected $gobjDocumento;

    public function __construct($pintIdDocumento="", $pobjDocumento = null)
    {

        self::$gintIdDocumento = $pintIdDocumento;
        if ($pobjDocumento){
            self::$gobjDocumento = $pobjDocumento;
        }else{
            self::$gobjDocumento = Documentos::find($pintIdDocumento);
            if(empty(self::$gobjDocumento)){
                self::$gobjDocumento = Documentoshistorico::where('IdDocumento',$pintIdDocumento)->first();
            }
        }
        self::$gintIdUser = \Session::get('uid');
        self::$gintIdLevelUser = \MySourcing::LevelUser(\Session::get('uid'));

    }
    static public function getIdProceso($pstrTipoDocumento){
        if (isset(self::$garrProcesos[$pstrTipoDocumento])){
            return self::$garrProcesos[$pstrTipoDocumento];
        }else{
            return null;
        }
    }
    static public function ValidaTipoDocumento($pstrTipoDocumento){
        if (isset(self::$garrProcesos[$pstrTipoDocumento])){
            if (self::$gobjDocumento->TipoDocumento->IdProceso == self::$garrProcesos[$pstrTipoDocumento]){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    static public function getDatos() {
      return self::$gobjDocumento;
    }
    static public function getDatosEntidad() {
        $lobjEntidad = "";
        if (self::$gobjDocumento->Entidad == 1) {
            $lobjEntidad = Contratistas::find(self::$gobjDocumento->IdEntidad);
        }elseif (self::$gobjDocumento->Entidad == 2) {
            $lobjEntidad = Contratos::find(self::$gobjDocumento->IdEntidad);
        }elseif (self::$gobjDocumento->Entidad == 3) {
            $lobjEntidad = Personas::find(self::$gobjDocumento->IdEntidad);
        }elseif (self::$gobjDocumento->Entidad == 6) {
            $lobjEntidad = Centros::find(self::$gobjDocumento->IdEntidad);
        }elseif (self::$gobjDocumento->Entidad == 10) {
            $lobjEntidad = \DB::table('tbl_activos_data_detalle')->where('IdActivoData',self::$gobjDocumento->IdEntidad)->orderBy('IdActivoDetalle','asc')->first();

        }
        return array("Entidad"=>self::$gobjDocumento->Entidad, "DatosEntidad"=>$lobjEntidad);
    }
    static public function getFormato(){
        $lstrFormato = "";
        if (self::$gobjDocumento->TipoDocumento->IdFormato == 'application/pdf'){
            $lstrFormato = "mimes:pdf";
        }else if (self::$gobjDocumento->TipoDocumento->IdFormato == 'application/vnd.ms-excel'){
            $lstrFormato = "mimes:xls,xlsx";
        }else if (self::$gobjDocumento->TipoDocumento->IdFormato == 'all/all'){
            $lstrFormato = "all/all";
        }
        return array("DocumentoURL"=>$lstrFormato);
    }
    static public function getDirectorio(){
        if (self::$gobjDocumento->idCarga=="1") {
            return "docs/";
        }else{
            return "uploads/documents/";
        }
    }

    static private function SaveFile($pobjArchivoDocumento){

        $lstrDocumentURL = "";
        if(!empty($pobjArchivoDocumento)){
            $destinationPath = './uploads/documents/';
            $filename = $pobjArchivoDocumento->getClientOriginalName();
            $extension =$pobjArchivoDocumento->getClientOriginalExtension(); //if you need extension of the file
            $rand = rand(1000,100000000);
            $newfilename = strtotime(date('Y-m-d H:i:s')).'-'.$rand.'.'.$extension;
            $uploadSuccess = $pobjArchivoDocumento->move($destinationPath, $newfilename);
            if( $uploadSuccess ) {
               $lstrDocumentURL = $newfilename;
            }
        }
        return $lstrDocumentURL;

    }

    static public function Load($pobjArchivoDocumento, $pdatFechaVencimiento) {

        //Validamos que se esté cargando el formato correcto
        $lstrFormato = self::getFormato();
        $lbolValido = false;
        if ($lstrFormato['DocumentoURL']=="all/all"){
            $lbolValido = true;
        }else{
            $validator = Validator::make(array("DocumentoURL"=>$pobjArchivoDocumento), $lstrFormato);
            $lbolValido = $validator->passes();
        }
        if (!$lbolValido){
            return array( 'code'=> 3,
                          'message' => 'El formato del documento no es correcto.',
                          'result'=>'',
                          'status' => 'error'
                    );
        }
        //Validamos que se envíe la fecha de vencimiento
        if (self::$gobjDocumento->TipoDocumento->Vigencia != 3) {
            if (!$pdatFechaVencimiento){
                if (self::$gobjDocumento->Vencimiento==1){
                    return array( 'code'=> 2,
                                  'message' => 'La fecha de vencimiento es obligatoria',
                                  'result'=>'',
                                  'status' => 'error'
                        );
                }
            }else{
                self::$gobjDocumento->Vencimiento = 1;
                self::$gobjDocumento->FechaVencimiento = $pdatFechaVencimiento;
                self::$gobjDocumento->IdEstatusDocumento = 1;
            }
        }else{
            self::$gobjDocumento->Vencimiento = 0;
            self::$gobjDocumento->FechaVencimiento = "";
        }

        //Guardamos el documento
        self::$gobjDocumento->DocumentoURL = self::SaveFile($pobjArchivoDocumento);
        $lintIdEstatus = self::$gobjDocumento->IdEstatus;
        if (count(self::$gobjDocumento->TipoDocumento->Aprobadores)){
            self::$gobjDocumento->IdEstatus = 2; //A futuro se puede intervenir y controlar el flujo
        }else{
            self::$gobjDocumento->IdEstatus = 5;
        }

        $idPersona = self::$gobjDocumento->IdEntidad;
        if (self::$gobjDocumento->TipoDocumento->IdProceso == 4) { //Finiquito
          $tipoDocContrato = \DB::table('tbl_tipos_documentos')->where('IdProceso',21)->first();
          $contratoexist = \DB::table('tbl_documentos')->where('IdEntidad',$idPersona)->where('IdTipoDocumento',$tipoDocContrato->IdTipoDocumento)->where('DocumentoURL','!=','')->first();
          if(!$contratoexist){
            $contratoexistHistorico = \DB::table('tbl_documentos_rep_historico')->where('IdEntidad',$idPersona)->where('IdTipoDocumento',$tipoDocContrato->IdTipoDocumento)->where('DocumentoURL','!=','')->first();
            if(!$contratoexistHistorico){
              self::$gobjDocumento->IdEstatus = 3;
              self::$gobjDocumento->Resultado = "Debe cargar el contrato de trabajo para este trabajador, para poder validar el finiquito";
            }
          }
        }

        if (self::$gobjDocumento->TipoDocumento->IdProceso == 21) { //contrato
          $tipoDocFiniquito = \DB::table('tbl_tipos_documentos')->where('IdProceso',4)->first();
          $tipoDocCedula = \DB::table('tbl_tipos_documentos')->where('Descripcion','LIKE','Cedula de identidad')->first();
          $finiquitoexist = \DB::table('tbl_documentos')->where('IdEntidad',$idPersona)->where('IdTipoDocumento',$tipoDocFiniquito->IdTipoDocumento)->where('IdEstatus',3)->first();
          if($finiquitoexist){
            \DB::table('tbl_documentos')->where('IdEntidad',$idPersona)->where('IdTipoDocumento',$tipoDocFiniquito->IdTipoDocumento)->where('IdEstatus',3)->update(['IdEstatus'=>2,'Resultado'=>'']);
          }
          if($tipoDocCedula){
              $cedulaExist = \DB::table('tbl_documentos')->where('IdEntidad',$idPersona)->where('IdTipoDocumento',$tipoDocCedula->IdTipoDocumento)->where('contrato_id', self::$gobjDocumento->contrato_id)->whereIn('IdEstatus', [1,3])->first();
              if($cedulaExist){
                self::$gobjDocumento->IdEstatus=3;
                self::$gobjDocumento->Resultado = "Falta cargar cédula de identidad en solicitud correspondiente para poder validar los datos del trabajador, una vez cargada la cédula usted debe volver a cargar contrato de trabajo.";
              }
          }
        }

        self::$gobjDocumento->save();

        if (self::$gobjDocumento->TipoDocumento->ControlCheckLaboral) {

            //Si se trata de un documento de control laboral
            $lobjCheckLaboral = new MyCheckLaboral();
            $lobjCheckLaboral::UpdateNoConformidad(self::$gobjDocumento);
            $lobjCheckLaboral::UpdateDocument(self::$gobjDocumento->contrato_id);
            $lobjCheckLaboral::UpdateDocumentMesActual(self::$gobjDocumento->contrato_id);

        }

        //Registramos en el log que se cargó el documento
        if ($lintIdEstatus==1 || $lintIdEstatus==7){
            self::Log(12); //Se está cargado por primera vez
        }else{
            self::Log(16);
        }

        return array( 'code'=> 1,
                      'message' => 'Cargado satisfactoriamente.',
                      'result' => self::$gobjDocumento,
                      'status' => 'success'
                    );

    }

    static public function Loadvalues($parrIdTipoDocumentoValor, $parrValores){

        foreach (self::$gobjDocumento->TipoDocumento->Valores as $larrTipoDocumentoValor) {
            if ($parrIdTipoDocumentoValor) {
                $lintIdValor = array_search($larrTipoDocumentoValor->IdTipoDocumentoValor, $parrIdTipoDocumentoValor);
            }else{
                $lintIdValor = false;
            }
            if ($lintIdValor!==false) {
                $lintIdDocumento = self::$gobjDocumento->IdDocumento;
                $lintIdTipoDocumentoValor = $larrTipoDocumentoValor->IdTipoDocumentoValor;
                $lobjDocumentoValor = Documentovalor::where('IdDocumento',$lintIdDocumento)
                                                    ->where('IdTipoDocumentoValor', $lintIdTipoDocumentoValor)
                                                    ->first();
                if (!$lobjDocumentoValor){
                    $lobjDocumentoValor = new Documentovalor;
                }
                $lobjDocumentoValor->IdTipoDocumentoValor = $larrTipoDocumentoValor->IdTipoDocumentoValor;
                if ($larrTipoDocumentoValor->TipoValor=="Fecha"){
                    $lobjDocumentoValor->Valor = \MyFormats::FormatoFecha($parrValores[$lintIdValor]);
                }else{
                    $lobjDocumentoValor->Valor = $parrValores[$lintIdValor];
                }
                $lobjDocumentoValor->entry_by = \Session::get('uid');
                self::$gobjDocumento->Documentovalor()->save($lobjDocumentoValor);
            }
        }
        return array('code'=>1,
            'status'=>'success',
            'result'=>'',
            'message'=> ''
            );

    }

    static public function Save($pintIdTipoDocumento, $pintEntidad, $pintIdEntidad, $pintIdDocumentoRelacion = "", $pintIdContratista= "", $pintContratoId="", $pdatFechaEmision = "", $anexo = 0) {

        if ($pdatFechaEmision){
            $ldatFechaEmision = date('Y-m',strtotime($pdatFechaEmision))."-01";
        }else{
            $ldatFechaEmision = date('Y-m')."-01";
        }

        $lobjTipoDocumento = TipoDocumentos::find($pintIdTipoDocumento);
        $idproceso = $lobjTipoDocumento->IdProceso;

        if (!$lobjTipoDocumento){
            array("code"=>2,'status'=>'error',"message"=>"El tipo de documento no existe","resutl"=>"");
        }

        //Verificamos que no exista el documento

        $lobjDocumento = Documentos::where('IdTipoDocumento',$pintIdTipoDocumento)
                        ->where('Entidad',$pintEntidad)
                        ->where('IdEntidad',$pintIdEntidad);

        if ($lobjTipoDocumento->Periodicidad == 1){
            $lobjDocumento = $lobjDocumento->where('FechaEmision',$ldatFechaEmision);
        }

        if ($pintEntidad !=1 && $pintContratoId){
            $lobjDocumento = $lobjDocumento->where('contrato_id',$pintContratoId);
        }
        $lobjDocumento = $lobjDocumento->first();
        $noCrear = 0;
        if($anexo == 1){

           $sinAprobar = Documentos::where('IdTipoDocumento', $pintIdTipoDocumento )->where('Entidad',3)->where('IdEntidad',$pintIdEntidad)->where('IdEstatus','!=',5)->first();
            if(isset($sinAprobar->IdDocumento)){
                $noCrear = 1;
            }else{
                $lobjDocumento = false;
            }

        }

        if (!$lobjDocumento || $idproceso == 21){
            $lobjDocumento = new Documentos();
            $lobjDocumento->IdTipoDocumento = $pintIdTipoDocumento;
            $lobjDocumento->IdDocumentoRelacion = $pintIdDocumentoRelacion;
            $lobjDocumento->Entidad = $pintEntidad;
            $lobjDocumento->IdEntidad = $pintIdEntidad;
            $lobjDocumento->FechaEmision = $ldatFechaEmision;

            if ($pintEntidad!=1){
                $lobjDocumento->contrato_id = $pintContratoId;
            }

            $lobjDocumento->IdContratista =  $pintIdContratista;
            $lobjDocumento->entry_by = self::$gintIdUser;
            $lobjDocumento->IdEstatus = 1; //acá podemos definir reglas de flujos
            if($idproceso==120 or $idproceso==118){
              $lobjDocumento->IdEstatus = 7;
            }
        }
        if($noCrear != 1){

            if ($lobjDocumento->save()){

                self::$gobjDocumento = $lobjDocumento;

                //Si se trata de un documento de control laboral
                if ($lobjTipoDocumento->ControlCheckLaboral){
                    $automatico = \Session::get('automatico');
                    if(isset($automatico) && $automatico==1){
                      //si es el proceso automatico mensual no hacemos la validacion previred
                    }else{
                      //Validamos si es un documento de previred trabajador
                      if (self::ValidaTipoDocumento('PreviredTrabajador')){
                          $lobjMyDocumentsPreviredEmployee = new MyDocumentsPreviredEmployee(self::$gintIdDocumento,
                                                                                             self::$gobjDocumento);
                          $lobjMyDocumentsPreviredEmployee->Crossing();
                      }
                    }

                    $lobjCheckLaboral = new MyCheckLaboral();
                    if ( self::$gobjDocumento->FechaEmision <= $lobjCheckLaboral::getPeriodo() ) {
                        $lobjCheckLaboral::UpdateNoConformidad(self::$gobjDocumento);
                    }

                }

                return array("code"=>1,'status'=>'success',"message"=>"Documento guardado satisfactoriamente","result"=>array("IdDocumento"=>$lobjDocumento->IdDocumento));
            }else{
                return array("code"=>2,'status'=>'error',"message"=>"Error guardando documento","resutl"=>"");
            }

        }else{
            return array("code"=>2,'status'=>'error',"message"=>"Existe Un Anexo cargado sin Aprobar","result"=>"");
        }

    }

    static public function Approve($IdAprobador = null){

        if (self::$gobjDocumento->TipoDocumento->IdProceso == 70){

            $lobjDocComprueba = Documentos::where('Entidad',"=",self::$gobjDocumento->Entidad)
                                          ->where('IdEntidad',"=",self::$gobjDocumento->IdEntidad)
                                          ->where('IdTipoDocumento',"=",self::$gobjDocumento->IdTipoDocumento)
                                          ->where('IdEstatus',"!=",'5')
                                          ->where('FechaEmision',"<",self::$gobjDocumento->FechaEmision)
                                          ->get();

            if (count($lobjDocComprueba)){
                return response()->json(array(
                    "code"=>3,
                    "status"=>'error',
                    "message"=>"Error aprobando documento, existen documentos pendientes de meses anteriores",
                    "result"=>''
                ));
            }

        }

        //Si tiene un documento relacionado
        $lobjDocumentoRelacion = Documentos::find(self::$gobjDocumento->IdDocumentoRelacion);
        if ($lobjDocumentoRelacion){

            if (self::$gobjDocumento->TipoDocumento->IdProceso == 3) { // anexo contrato trabajador

                $lobjMyDocumentosAnexo = new MyDocumentsContractPersonAnnexed(self::$gobjDocumento->IdDocumento);
                $larrDataCambios = $lobjMyDocumentosAnexo->getCambios();

                $lobjPersona = new \MyPeoples($lobjDocumentoRelacion->IdEntidad);
                $lobjPersona::ChangeContractExecute($larrDataCambios);
                //self::$gobjDocumento->Anexos->delete();

            }elseif (self::$gobjDocumento->TipoDocumento->IdProceso == 31) { // anexo contrato comercial

                if (self::$gobjDocumento->FechaVencimiento > $lobjDocumentoRelacion->FechaVencimiento) {
                    $lobjDocumentoRelacion->FechaVencimiento = self::$gobjDocumento->FechaVencimiento;
                    $lobjDocumentoRelacion->save();
                    $lobjContrato = new MyContracts(self::$gobjDocumento->IdEntidad);
                    $lobjContrato::Extender(self::$gobjDocumento->FechaVencimiento);
                }

            }

        }

        self::$gobjDocumento->IdEstatus = 5;
        self::$gobjDocumento->Resultado = "";
        if (self::$gobjDocumento->save()){
          if($IdAprobador!=null){
              //1: root
              self::Log(13,1);
          }else{
              self::Log(13);
          }

          // Discrepancia 
          $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
          $lobjCheckLaboral = new MyCheckLaboral();

        if($sitio->Valor=='Transbank')
        {
          
                if(self::$gobjDocumento->contrato_id != 0 && self::$gobjDocumento->contrato_id != NULL) 
                {
                
                $lobjCheckLaboral::agregarDiscrepanciasDocAprobado(self::$gobjDocumento->contrato_id);
                }

        }
            //Recalculamos los porcentajes de completitud
            if (self::$gobjDocumento->TipoDocumento->ControlCheckLaboral) {

                //Si se trata de un documento de control laboral
                $lobjCheckLaboral::UpdateNoConformidad(self::$gobjDocumento);
                $lobjCheckLaboral::UpdateDocument(self::$gobjDocumento->contrato_id);
                $lobjCheckLaboral::UpdateDocumentMesActual(self::$gobjDocumento->contrato_id);
            }

            //Si el documento es de acreditación
            if (self::$gobjDocumento->TipoDocumento->Acreditacion) {

                //Si el documento es de persona enviamos a evaluarción
                if (self::$gobjDocumento->Entidad == 3 ){

                    $lobjAcreditacion = new Acreditacion(self::$gobjDocumento->IdEntidad);
                    $lobjAcreditacion::Accreditation();

                }
                if (self::$gobjDocumento->Entidad == 2 ){

                    $lobjAcreditacion = new AcreditacionContrato(self::$gobjDocumento->IdEntidad);
                    $lobjAcreditacion::Accreditation();

                }
                if (self::$gobjDocumento->Entidad == 10 ){
                    $lobjAcreditacion = new AcreditacionActivos(self::$gobjDocumento->IdEntidad);
                    $lobjAcreditacion::Accreditation(1);
                }


            }

            if (self::$gobjDocumento->TipoDocumento->IdProceso == 4) { // finiquito

              $lobjMovPersonal = \DB::table('tbl_movimiento_personal')->where('IdPersona',self::$gobjDocumento->IdEntidad)->where('contrato_id',self::$gobjDocumento->contrato_id)->where('IdAccion',2)->first();

              if($lobjMovPersonal){
                $lobjPersonaa = new \MyPeoples(self::$gobjDocumento->IdEntidad);
                $debePapeles = \DB::table('tbl_documentos')->where('IdEntidad',self::$gobjDocumento->IdEntidad)->where('Entidad',3)->where('contrato_id',self::$gobjDocumento->contrato_id)->where('IdEstatus','<>',5)->first();
                if($debePapeles){
                    $lobjPersonaa::UpdateMaestroMovil(self::$gobjDocumento->IdEntidad,self::$gobjDocumento->contrato_id,$lobjMovPersonal->FechaEfectiva,'LeaveContractApproved','Finiquitado');
                }else{
                  $lobjPersonaa::UpdateMaestroMovil(self::$gobjDocumento->IdEntidad,self::$gobjDocumento->contrato_id,$lobjMovPersonal->FechaEfectiva,'LeaveContractApprovedFinish','Finiquitado');
                }
              }

              $docsVencidos = Documentos::where('Vencimiento',1)->where('Entidad',3)->where('IdEntidad',self::$gobjDocumento->IdEntidad)->where('contrato_id',self::$gobjDocumento->contrato_id)->update(['Vencimiento'=>0]);
              return self::Store();
            }

            if (self::$gobjDocumento->Entidad == 3) { // Es de personas
                $lobjPersona = Personas::find(self::$gobjDocumento->IdEntidad);
                if ($lobjPersona->Contratospersonas) {
                    if ($lobjPersona->Contratospersonas->contrato_id != self::$gobjDocumento->contrato_id){
                        self::Store();
                    }
                }else{
                    self::Store();
                }

                $lobjMovPersonal = \DB::table('tbl_movimiento_personal')->where('IdPersona',self::$gobjDocumento->IdEntidad)->where('contrato_id',self::$gobjDocumento->contrato_id)->where('IdAccion',2)->first();
                if($lobjMovPersonal){
                  $lobjPersonaa = new \MyPeoples(self::$gobjDocumento->IdEntidad);
                  $periodo = new \DateTime($lobjMovPersonal->FechaEfectiva);
                  if($lobjMovPersonal->FechaEfectiva > $periodo->format('Y-m-01')){
                    $periodo = $periodo->modify('+1 month');
                  }
                  $periodo = $periodo->format('Y-m-01');
                  $estadoMaestroMovil = \DB::table('tbl_personas_maestro_movil')->where('idpersona',self::$gobjDocumento->IdEntidad)->where('contrato_id',self::$gobjDocumento->contrato_id)->where('periodo',$periodo)->first();
                  if($estadoMaestroMovil){
                      if($estadoMaestroMovil->Estatus=='Finiquitado'){
                          $debePapeles = \DB::table('tbl_documentos')->where('IdEntidad',self::$gobjDocumento->IdEntidad)->where('Entidad',3)->where('contrato_id',self::$gobjDocumento->contrato_id)->where('IdEstatus','<>',5)->count();
                          if($debePapeles==0){
                            $lobjPersonaa::UpdateMaestroMovil(self::$gobjDocumento->IdEntidad,self::$gobjDocumento->contrato_id,$lobjMovPersonal->FechaEfectiva,'LeaveContractApprovedFinish','Finiquitado');
                          }
                      }
                  }
                }

            }

            if (self::$gobjDocumento->TipoDocumento->IdProceso == 21) { // contrato de trabajo
                $lobjPersona = Personas::find(self::$gobjDocumento->IdEntidad);
                if ($lobjPersona->Contratospersonas) {
                    if ($lobjPersona->Contratospersonas->contrato_id == self::$gobjDocumento->contrato_id){
                        $lobjPersona->Contratospersonas->IdEstatus = self::$gobjDocumento->IdEstatus;
                        $lobjPersona->Contratospersonas->save();
                    }
                }
            }elseif (self::$gobjDocumento->TipoDocumento->IdProceso == 9) { // carta de termino de contrato
                $lobjContrato = new MyContracts(self::$gobjDocumento->IdEntidad);
                $lobjContrato::SettlementExecute();
            }

            if ($lobjDocumentoRelacion){

                if (self::$gobjDocumento->TipoDocumento->IdProceso != 3 && self::$gobjDocumento->TipoDocumento->IdProceso != 31) {

                    // averiguar como hacer la nueva asociación
                    \DB::table('tbl_documentos_log')
                                ->where('IdDocumento','=',self::$gobjDocumento->IdDocumentoRelacion)
                                ->update(['IdDocumento' => self::$gobjDocumento->IdDocumento]);

                    \DB::table('tbl_documentos')
                        ->where('IdDocumento','=',self::$gobjDocumento->IdDocumento)
                        ->update(['IdDocumentoRelacion' => null]);

                    \DB::table('tbl_documentos')->where('IdDocumento', '=', self::$gobjDocumento->IdDocumentoRelacion)->delete();

                }
            }
            /* Sobre el F30 y el Certificado Deuda Tesorería:
            Se requiere que cuando se apruebe alguno de esos documentos, en todos los demás que estén en 1 o 3 para ese tipo de documento de meses anteriores
            se copie el documento y se apruebe con un comentario genérico "Reemplazado con documento cargado para el periodo XX-XXXX"*/
            if(self::$gobjDocumento->TipoDocumento->IdProceso == 110 or self::$gobjDocumento->TipoDocumento->IdProceso == 111){

                $cliente = DB::table('tbl_configuraciones')->where('tbl_configuraciones.Nombre', 'CNF_APPNAME')->select('tbl_configuraciones.Valor')-> first();
                if($cliente->Valor == 'Abastible'){
                    $documentoAnteriores = DB::table('tbl_documentos')
                        ->where('tbl_documentos.FechaEmision','<',self::$gobjDocumento->FechaEmision )
                        ->where('tbl_documentos.Entidad', 1 )
                        ->where('tbl_documentos.IdTipoDocumento', self::$gobjDocumento->TipoDocumento->IdTipoDocumento)
                        ->where('tbl_documentos.IdEntidad', self::$gobjDocumento->IdEntidad)
                        ->whereIn('tbl_documentos.IdEstatus', [1,3])
                        ->update([
                            'DocumentoURL' => self::$gobjDocumento->DocumentoURL,
                            'IdEstatus' => 5,
                            'Resultado' => "Reemplazado con documento cargado para el periodo ".self::$gobjDocumento->FechaEmision
                        ]);
                }
            }
            if(self::$gobjDocumento->TipoDocumento->IdProceso == 143){
				      $existePensionado = \DB::table('tbl_pensionados')->where('IdPersona',self::$gobjDocumento->IdEntidad)->first();
              if($existePensionado){
                    //$contrato = \DB::table('tbl_contratos_personas')->where('IdPersona',$self::$gobjDocumento->IdEntidad)->first();
    					$existePensionado->contrato_id = self::$gobjDocumento->contrato_id;
    					$existePensionado->IdContratista = self::$gobjDocumento->IdContratista;
    					$existePensionado->save();
      				}else{
      					\DB::table('tbl_pensionados')->insert([
      						'IdPersona' => self::$gobjDocumento->IdEntidad,
      						'contrato_id' => self::$gobjDocumento->contrato_id,
      						'IdContratista' => self::$gobjDocumento->IdContratista,
      						'Fecha'	 => date('Y-m-d H:i:s')
      					]);
      				}
			      }

            self::EmailEstado(self::$gobjDocumento->IdDocumento,self::$gobjDocumento->IdEstatus);

            return array("code"=>1,
                     'status'=>'success',
                     "message"=>"Documento aprobado satisfactoriamente",
                     "result"=>'');

        }else{
            return array("code"=>2,
                     'status'=>'error',
                     "message"=>"Error aprobando documento",
                     "result"=>'');
        }

    }

    static public function Reject($pstrResultado){

        if (self::$gobjDocumento){
            self::$gobjDocumento->IdEstatus = 3;
            self::$gobjDocumento->Resultado = $pstrResultado;
            if (self::$gobjDocumento->save()){
                self::log(14);

                if (self::$gobjDocumento->TipoDocumento->ControlCheckLaboral) {

                    //Si se trata de un documento de control laboral
                    $lobjCheckLaboral = new MyCheckLaboral();
                    $lobjCheckLaboral::UpdateNoConformidad(self::$gobjDocumento);
                    $lobjCheckLaboral::UpdateDocument(self::$gobjDocumento->contrato_id);
                    $lobjCheckLaboral::UpdateDocumentMesActual(self::$gobjDocumento->contrato_id);

                }

                if (self::$gobjDocumento->TipoDocumento->IdProceso == 4) { // finiquito

                  $lobjMovPersonal = \DB::table('tbl_movimiento_personal')->where('IdPersona',self::$gobjDocumento->IdEntidad)->where('contrato_id',self::$gobjDocumento->contrato_id)->where('IdAccion',2)->first();

                  if($lobjMovPersonal){
                    $lobjPersonaa = new \MyPeoples(self::$gobjDocumento->IdEntidad);
                    $lobjPersonaa::UpdateMaestroMovil(self::$gobjDocumento->IdEntidad,self::$gobjDocumento->contrato_id,$lobjMovPersonal->FechaEfectiva,'LeaveContractApproved','Baja Observada');
                  }

                }

                self::EmailEstado(self::$gobjDocumento->IdDocumento,self::$gobjDocumento->IdEstatus);

                if (self::$gobjDocumento->TipoDocumento->Acreditacion) {

                    //Si el documento es de persona enviamos a evaluarción
                    if (self::$gobjDocumento->Entidad == 3 ){
                        $lobjAcreditacion = new Acreditacion(self::$gobjDocumento->IdEntidad);
                        $lobjAcreditacion::Accreditation();
                    }
                    if (self::$gobjDocumento->Entidad == 2 ){
                        $lobjAcreditacion = new AcreditacionContrato(self::$gobjDocumento->IdEntidad);
                        $lobjAcreditacion::Accreditation();
                    }
                    if (self::$gobjDocumento->Entidad == 10 ){
                        $lobjAcreditacion = new AcreditacionActivos(self::$gobjDocumento->IdEntidad);
                        $lobjAcreditacion::Accreditation(2);
                    }

                }

               // \Log::info(self::$gobjDocumento->contrato_id);

                 // Discrepancia 
          $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
          if($sitio->Valor=='Transbank')
          {
            if(self::$gobjDocumento->contrato_id != 0 && self::$gobjDocumento->contrato_id != NULL ) 
                {
                        $lobjCheckLaboral = new MyCheckLaboral();
                        $lobjCheckLaboral::agregarDiscrepanciasDocAprobado(self::$gobjDocumento->contrato_id);
                }

            }

                return array("code"=>1,
                         'status'=>'success',
                         "message"=>"Documento rechazado satisfactoriamente",
                         "result"=>'');
            }else{
                return array("code"=>2,
                         'status'=>'error',
                         "message"=>"Error rechazando",
                         "result"=>'');
            }
        }else{
                    return array("code"=>3,
                             'status'=>'error',
                             "message"=>"No se encontró el documento",
                             "result"=>'');
        }
    }

    static public function Cancel($IdDocumento){
        if (self::$gobjDocumento){
            self::$gobjDocumento->IdEstatus = 9;
            self::$gobjDocumento->Resultado = $pstrResultado;
            if (self::$gobjDocumento->save()){
                self::log(21);

                if (self::$gobjDocumento->TipoDocumento->ControlCheckLaboral) {

                    //Si se trata de un documento de control laboral
                    $lobjCheckLaboral = new MyCheckLaboral();
                    $lobjCheckLaboral::UpdateNoConformidad(self::$gobjDocumento);
                    $lobjCheckLaboral::UpdateDocument(self::$gobjDocumento->contrato_id);
                    $lobjCheckLaboral::UpdateDocumentMesActual(self::$gobjDocumento->contrato_id);

                }

                if (self::$gobjDocumento->TipoDocumento->Acreditacion) {

                    //Si el documento es de persona enviamos a evaluarción
                    if (self::$gobjDocumento->Entidad == 3 ){
                        $lobjAcreditacion = new Acreditacion(self::$gobjDocumento->IdEntidad);
                        $lobjAcreditacion::Accreditation();
                    }
                    if (self::$gobjDocumento->Entidad == 2 ){
                        $lobjAcreditacion = new AcreditacionContrato(self::$gobjDocumento->IdEntidad);
                        $lobjAcreditacion::Accreditation();
                    }

                }

                return array("code"=>1,
                         'status'=>'success',
                         "message"=>"Documento rechazado satisfactoriamente",
                         "result"=>'');
            }else{
                return array("code"=>2,
                         'status'=>'error',
                         "message"=>"Error rechazando",
                         "result"=>'');
            }
        }else{
            return array("code"=>3,
                     'status'=>'error',
                     "message"=>"No se encontró el documento",
                     "result"=>'');
        }
    }

    static public function Store(){

        $lobjDocumentoHistorico = new Documentoshistorico();
        $lobjDocumentoHistorico->IdDocumento = self::$gobjDocumento->IdDocumento;
        $lobjDocumentoHistorico->IdTipoDocumento = self::$gobjDocumento->IdTipoDocumento;
        $lobjDocumentoHistorico->Entidad = self::$gobjDocumento->Entidad;
        $lobjDocumentoHistorico->IdEntidad = self::$gobjDocumento->IdEntidad;
        $lobjDocumentoHistorico->Documento = self::$gobjDocumento->Documento;
        $lobjDocumentoHistorico->DocumentoURL = self::$gobjDocumento->DocumentoURL;
        $lobjDocumentoHistorico->FechaEmision = self::$gobjDocumento->FechaEmision;
        $lobjDocumentoHistorico->FechaVencimiento = self::$gobjDocumento->FechaVencimiento;
        $lobjDocumentoHistorico->IdEstatus = self::$gobjDocumento->IdEstatus;
        $lobjDocumentoHistorico->IdEstatusDocumento = self::$gobjDocumento->IdEstatusDocumento;
        $lobjDocumentoHistorico->Resultado = self::$gobjDocumento->Resultado;
        $lobjDocumentoHistorico->entry_by = self::$gobjDocumento->entry_by;
        $lobjDocumentoHistorico->contrato_id = self::$gobjDocumento->contrato_id;
        $lobjDocumentoHistorico->IdContratista  = self::$gobjDocumento->IdContratista;

        if ($lobjDocumentoHistorico->save()){
            foreach (self::$gobjDocumento->Documentovalor as $lobjDocumentoValores) {
                $lobjDocumentosValoresHistorico = new Documentovalorhistorico();
                $lobjDocumentosValoresHistorico->IdDocumento = $lobjDocumentoValores->IdDocumento;
                $lobjDocumentosValoresHistorico->IdTipoDocumentoValor = $lobjDocumentoValores->IdTipoDocumentoValor;
                $lobjDocumentosValoresHistorico->Valor = $lobjDocumentoValores->Valor;
                $lobjDocumentosValoresHistorico->entry_by = $lobjDocumentoValores->entry_by;
                $lobjDocumentosValoresHistorico->save();

            }
            foreach (self::$gobjDocumento->Bitacora as $lobjDocumentolog) {
                $lobjDocumentosLogHistorico = new Documentosloghistorico();
                $lobjDocumentosLogHistorico->IdDocumento = $lobjDocumentolog->IdDocumento;
                $lobjDocumentosLogHistorico->IdAccion = $lobjDocumentolog->IdAccion;
                $lobjDocumentosLogHistorico->DocumentoURL = $lobjDocumentolog->DocumentoURL;
                $lobjDocumentosLogHistorico->Observaciones = $lobjDocumentolog->Observaciones;
                $lobjDocumentosLogHistorico->entry_by = $lobjDocumentolog->entry_by;
                $lobjDocumentosLogHistorico->createdOn = $lobjDocumentolog->createdOn;
                $lobjDocumentosLogHistorico->save();
            }
            self::$gobjDocumento->delete();
            return array("code"=>1,
                     'status'=>'success',
                     "message"=>"Documento pasadado al historico satifactoriamente",
                     "result"=>'');
        }else{
            return array("code"=>2,
                     'status'=>'error',
                     "message"=>"Error insertando documento en el historico",
                     "result"=>'');
        }

    }

    static public function Log($pintIdAccion, $IdAprobador=null){

        $lobjDocumentoLog = new Documentoslog;
        $lobjDocumentoLog->IdDocumento = self::$gobjDocumento->IdDocumento;
        $lobjDocumentoLog->IdAccion = $pintIdAccion;
        $lobjDocumentoLog->DocumentoURL = self::$gobjDocumento->DocumentoURL;
        $lobjDocumentoLog->observaciones = self::$gobjDocumento->Observaciones;
        if($IdAprobador!=null){
            $lobjDocumentoLog->entry_by = $IdAprobador;
            $lobjDocumentoLog->observaciones = "Aprobado por sistema, usuario gatillante: ".self::$gintIdUser;
        }else{
            $lobjDocumentoLog->entry_by = self::$gintIdUser;
        }
        $lobjDocumentoLog->save();

        return array(
            'code'=>1,
            'status'=>'success',
            'result'=>$lobjDocumentoLog,
            'message'=> \Lang::get('core.note_success')
            );
    }

    static public function EmailEstado($pintIdDocumento,$pintIdEstatus){

      $config = \DB::table('tbl_configuraciones')->where('Nombre','CNF_EMAILAR')->select('Valor')->first();

      if($config->Valor==1){
        $automatico = \Session::get('automatico');
        $emailar = \Session::get('CNF_EMAILAR');
        $flag=true;
        if(isset($automatico) && $automatico==1){
          if(isset($emailar) && $emailar==0){
            $flag=false;
          }
        }
        $lobjEntidad = \DB::table('tbl_documentos')->select('Entidad','IdEntidad')->where('IdDocumento',$pintIdDocumento)->first();
        if($lobjEntidad && $flag){
          switch ($lobjEntidad->Entidad) {
            case '3':
              $lobjPersona = \DB::table('tbl_documentos')
                ->join('tbl_contratos_personas','tbl_documentos.IdEntidad','=','tbl_contratos_personas.IdPersona')
                ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contratos_personas.IdContratista')
                ->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
                ->join('tb_users','tb_users.id','=','tbl_contratistas.entry_by_access')
                ->join('tbl_personas','tbl_documentos.IdEntidad','=','tbl_personas.IdPersona')
                ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
                ->select('cont_numero','cont_nombre','Descripcion','Nombres','Apellidos' ,'tbl_personas.RUT','RazonSocial','tbl_documentos.Entidad','tb_users.email', 'FechaEmision','Resultado')
                ->where('tbl_documentos.IdDocumento',$pintIdDocumento)->get();

              break;
            case '2':
              $lobjPersona = \DB::table('tbl_documentos')
                ->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_documentos.IdEntidad')
                ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
                ->join('tb_users','tb_users.id','=','tbl_contratistas.entry_by_access')
                ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
                ->select('cont_numero','cont_nombre','Descripcion','RazonSocial','tbl_documentos.Entidad','tb_users.email', 'FechaEmision','Resultado')
                ->where('tbl_documentos.IdDocumento',$pintIdDocumento)->get();
              break;
            case '1':
              $lobjPersona = \DB::table('tbl_documentos')
                ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_documentos.IdEntidad')
                ->join('tb_users','tb_users.id','=','tbl_contratistas.entry_by_access')
                ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
                ->select('Descripcion','RazonSocial','tbl_documentos.Entidad','tb_users.email', 'FechaEmision','Resultado')
                ->where('tbl_documentos.IdDocumento',$pintIdDocumento)->get();
              break;
            case '6':
              $lobjPersona = \DB::table('tbl_documentos')
                ->join('tbl_centro','tbl_centro.IdCentro','=','tbl_documentos.IdEntidad')
                ->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_documentos.contrato_id')
                ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
                ->join('tb_users','tb_users.id','=','tbl_contratistas.entry_by_access')
                ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
                ->select('cont_numero','cont_nombre','tbl_tipos_documentos.Descripcion','RazonSocial','tbl_documentos.Entidad','tb_users.email', 'FechaEmision','Resultado')
                ->where('tbl_documentos.IdDocumento',$pintIdDocumento)->get();
              break;

            default:
                $flag=false;
              break;
          }

          if($flag)
    		  if(count($lobjPersona)>0)
    		    if( $lobjPersona[0]->email!='' ){
    			     \Mail::send('emails.documentos',['lobjPersona'=>$lobjPersona,'estado'=>$pintIdEstatus], function ($m) use ($lobjPersona){
                 $email = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_EMAIL')->first();
    				     $m->from($email->Valor);
    				     $m->to($lobjPersona[0]->email)->subject("[Revisión Documento]");
    				});
    		    }
          }
      }
    }

    static public function FromStore($IdDocumento){

        //se pasa un documento del historico a la tabla tbl_documentos
        //se obtiene el documento del historico
        $docHistorico =   \DB::table('tbl_documentos_rep_historico')->where('IdDocumento', $IdDocumento)->first();

        if($docHistorico){

            $lobjDocumento = new Documentos();

            $lobjDocumento->IdDocumento = $docHistorico->IdDocumento;
            $lobjDocumento->IdTipoDocumento = $docHistorico->IdTipoDocumento;
            $lobjDocumento->Entidad = $docHistorico->Entidad;
            $lobjDocumento->IdEntidad = $docHistorico->IdEntidad;
            $lobjDocumento->Documento = $docHistorico->Documento;
            $lobjDocumento->DocumentoURL = $docHistorico->DocumentoURL;
            $lobjDocumento->FechaEmision = $docHistorico->FechaEmision;
            $lobjDocumento->FechaVencimiento = $docHistorico->FechaVencimiento;
            $lobjDocumento->IdEstatus = $docHistorico->IdEstatus;
            $lobjDocumento->IdEstatusDocumento = $docHistorico->IdEstatusDocumento;
            $lobjDocumento->Resultado = $docHistorico->Resultado;
            $lobjDocumento->entry_by = $docHistorico->entry_by;
            $lobjDocumento->contrato_id = $docHistorico->contrato_id;
            $lobjDocumento->IdContratista  = $docHistorico->IdContratista;


            //se inserta en tbl_documentos
            if($lobjDocumento->save()){
                //se borra el documento del historico
                \DB::table('tbl_documentos_rep_historico')->where('IdDocumento', $IdDocumento)->delete();
                return array("code"=>1,
                     'status'=>'success',
                     "message"=>"Documento recuperado Del historico satifactoriamente",
                     "result"=>'');
            }else{
                return array("code"=>2,
                     'status'=>'error',
                     "message"=>"Error al mover el documento del historico",
                     "result"=>'');
            }
        }else{
            return array("code"=>2,
                     'status'=>'error',
                     "message"=>"Error no existe el documento en el historico",
                     "result"=>'');
        }
    }
}
