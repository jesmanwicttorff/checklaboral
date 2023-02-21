<?php

namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Contratos;
use App\Models\Documentos;
use App\Models\Complejidad;
use App\Models\Moneda;
use App\Models\Gruposespecificos;
use App\Models\Contratosservicios;
use App\Models\JustificacionProveedorUnico;
use App\Models\TipoAdjudicacion;
use App\Models\Core\Groups;
use Illuminate\Http\Request;
use App\Models\Roles;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use App\Library\MyContracts;
use App\Library\MyDocuments;
use App\Library\MyRequirements;
use App\Library\Acreditacion;
use App\Library\AcreditacionContrato;

class ContratosController extends Controller {

    protected $layout = "layouts.main";
    protected $data = array();
    public $module = 'contratos';
    static $per_page    = '10';

    public function __construct()
    {

        parent::__construct();
        $this->model = new Contratos();
        $this->modelview = new  \App\Models\Contratospersonas();
        $this->modelviewtwo = new  \App\Models\Contratoscentros();
        $this->modelitemsdetail = new  \App\Models\Itemsdetail();

        $this->info = $this->model->makeInfo( $this->module);
        $this->access = $this->model->validAccess($this->info['id']);

        $this->data = array(
            'pageTitle'         =>  $this->info['title'],
            'pageNote'          =>  $this->info['note'],
            'pageModule'        => 'contratos',
            'pageUrl'           =>  url('contratos'),
            'return'            =>  self::returnUrl()
        );

    }

    public function getIndex()
    {
        if($this->access['is_view'] ==0)
            return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

        $this->data['access']       = $this->access;
        return view('contratos.index',$this->data);
        // return view('layouts.appvue',$this->data);
    }

    private function GetAccess($pintIdUser = "", $pintLevelUser = ""){
      $lintLevelUser = $pintLevelUser?$pintLevelUser:\MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = $pintIdUser?$pintIdUser:\Session::get('uid');
      $larrAccessUser['general'] = array("access"=>1,"view"=>"general");
      if (CNF_TEMPLATE_CONTRATO != 'general'){
        $larrAccessUser['general'] = array("access"=>1,"view"=>CNF_TEMPLATE_CONTRATO);
      }
      $larrAccessUser['centros'] = array("access"=>1,"view"=>"centros");
      $larrAccessUser['requisitos'] = array("access"=>1,"view"=>"requisitos");
      $larrAccessUser['personas'] = array("access"=>1,"view"=>"personas");
      $larrAccessUser['partidas'] = array("access"=>1,"view"=>"partidas");
      $larrAccessUser['personascentros'] = array("access"=>1,"view"=>"personascentros");
      $larrAccessUser['activos'] = array("access"=>1,"view"=>"activos");
      $larrAccessUser['acciones'] = array("access"=>1,"view"=>"acciones");
      if ($lintLevelUser==6 || $lintLevelUser==15 || $lintLevelUser==4){ //contratista y pre-contratista
        $larrAccessUser['general'] = array("access"=>0,"view"=>"consulta");
        if (CNF_TEMPLATE_CONTRATO != 'general'){
          $larrAccessUser['general'] = array("access"=>1,"view"=>CNF_TEMPLATE_CONTRATO."consulta");
        }
        $larrAccessUser['centros'] = array("access"=>0,"view"=>"consulta");
        $larrAccessUser['requisitos'] = array("access"=>0,"view"=>"consulta");
        $larrAccessUser['partidas'] = array("access"=>0,"view"=>"consulta");
        if ($lintLevelUser==4){
          $larrAccessUser['personas'] = array("access"=>0,"view"=>"consulta");
          $larrAccessUser['activos'] = array("access"=>0,"view"=>"consulta");
          $larrAccessUser['partidas'] = array("access"=>1,"view"=>"consulta");
        }
      }
      return $larrAccessUser;
    }

    public function getReportes ($id) {

      $lobjContrato = \DB::table('tbl_contrato')
                      ->select('tbl_contratistas.rut', "tbl_contratistas.RazonSocial", "tbl_contrato.cont_numero","tbl_contsegmento.seg_nombre",
                          "tbl_contgeografico.geo_nombre","tbl_contareafuncional.afuncional_nombre")
                      ->join('tbl_contratistas','tbl_contratistas.IdContratista','=','tbl_contrato.IdContratista')
                      ->join('tbl_contsegmento','tbl_contsegmento.segmento_id','=','tbl_contrato.segmento_id')
                      ->join('tbl_contgeografico','tbl_contgeografico.geo_id','=','tbl_contrato.geo_id')
                      ->join('tbl_contareafuncional','tbl_contareafuncional.afuncional_id','=','tbl_contrato.afuncional_id')
                      ->where('tbl_contrato.contrato_id','=',$id)->first();


      $this->data['rut'] = "";
      $this->data['RazonSocial'] = "";
      $this->data['cont_numero'] = "";
      $this->data['segmento'] = "";
      $this->data['faena'] = "";
      $this->data['areaf'] = "";

      if ($lobjContrato){
        $this->data['rut'] = $lobjContrato->rut;
        $this->data['RazonSocial'] = $lobjContrato->RazonSocial;
        $this->data['cont_numero'] = $lobjContrato->cont_numero;
        $this->data['segmento'] = $lobjContrato->seg_nombre;
        $this->data['faena'] = $lobjContrato->geo_nombre;
        $this->data['areaf'] = $lobjContrato->afuncional_nombre;
      }

      $this->data['id'] = $id;
      $this->data['access']   = $this->access;
      $this->data['setting']    = $this->info['setting'];
      $this->data['fields']     = \AjaxHelpers::fieldLang($this->info['config']['grid']);
      $this->data['subgrid']    = (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());

      //asignamos las viriables
      $this->data['reg'] = "";
      $this->data['seg'] = "";
      $this->data['area'] = "";
      $this->data['ind'] = "";
      $this->data['rep'] = "";
      $this->data['year'] = "";
      $this->data['mes'] = "";

      $lobjMyReports = new \MyReports($this->data);
      $larrFilters = $lobjMyReports::getFilters();
      $this->data = array_merge($this->data,$larrFilters);

      return view('contratos.reportes',$this->data);

    }

    public function postDesvincularmasivo(Request $request){

      $lstrResultado = "";
      $ljsoIdPersonas = $request->input('IdPersona');
      $lintAnotaciones = $request->input('anotacion');
      $ldatFechaEfectiva = $request->input('fechaefectiva');
      $ldatFechaEfectiva = self::FormatoFecha($ldatFechaEfectiva);
      $larrIdPersonas = json_decode($ljsoIdPersonas);

      foreach ($larrIdPersonas as $larrPersona) {

        $lobjPersona = \MyPeoples::LeaveAccess($larrPersona->value);
        $lobjPersona = \MyPeoples::LeaveContract($larrPersona->value,0,$lintAnotaciones, $ldatFechaEfectiva);

      }

      return response()->json(array(
            'status'=>'success',
            'result'=>$lstrResultado,
            'message'=> \Lang::get('core.note_success')
        ));

    }

    public function postCambiarpersonasmasivo(Request $request){

      $lstrResultado = "";
      $ljsoIdPersonas = $request->input('IdPersona');
      $lintContratoId = $request->input('contrato_id_change');
      $larrIdPersonas = json_decode($ljsoIdPersonas);

      foreach ($larrIdPersonas as $larrPersona) {

        $lobjPersona = new \MyPeoples($larrPersona->value);
        $larrResult = $lobjPersona::CambiosContractual($lintContratoId, "", "", "", "");

      }

      return response()->json(array(
            'status'=>'success',
            'result'=>$lstrResultado,
            'message'=> \Lang::get('core.note_success')
        ));

    }

    public function postData( Request $request)
    {

        $this->data['setting']      = $this->info['setting'];
        $this->data['tableGrid']    = $this->info['config']['grid'];
        $this->data['access']       = $this->access;
        return view('contratos.table',$this->data);

    }

    public function Registraractividad($contrato_id,$IdAccion,$Observaciones=null){
        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');
        $lstrResultado = \DB::table("tbl_contratos_acciones")
            ->insert(array("contrato_id"=>$contrato_id,"accion_id"=>$IdAccion,"observaciones"=>$Observaciones,"entry_by"=>$lintIdUser));
        return response()->json(array(
            'status'=>'success',
            'result'=>$lstrResultado,
            'message'=> \Lang::get('core.note_success')
        ));
    }

  public  function postCompruebanumerocontrato(Request $request)
  {
      $lintIdContrato = $request->input('idcontrato');
      $lstrContNumero = $request->input('cont_numero');
      $larrResultado = array();
      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = \Session::get('uid');

       //limpiamos la base de datos
      $lobjContrato = \DB::table('tbl_contrato')
                ->where('tbl_contrato.cont_numero', '=', $lstrContNumero)
                ->where('tbl_contrato.contrato_id', '!=', $lintIdContrato)
                ->get();

      if ($lobjContrato){
         $larrResultado = array('status'=>'sucess',
                   'valores'=>'',
                   'message'=>\Lang::get('core.note_sucess'),
                   'code'=> '1'
                  );
       }else{
         $larrResultado = array('status'=>'sucess',
           'valores'=>'',
           'message'=>\Lang::get('core.note_sucess'),
           'code'=> '0'
          );
       }

       return response()->json($larrResultado);

  }
  public function postAccioncontratos(Request $request){

    $lintIdAccion = $request->input('accion');
    $lintIdContrato = $request->input('contrato');
    $ldatFechaContrato = $request->input('fecha');
    if ($ldatFechaContrato){
        $ldatFechaContrato = self::FormatoFecha($ldatFechaContrato);
    }
    $lintIdDocumento = $request->input('iddocumento');

    $lobjContrato = new MyContracts($lintIdContrato);

    if ($lintIdAccion==1){
      $larrResultado = self::ChangeContract($lintIdContrato, $lintIdDocumento, $ldatFechaContrato);
    }else if ($lintIdAccion==2){
      $larrResultado = $lobjContrato::Settlement();
    }else{
      $larrResultado = array("status" => "success", "code"=>4,"message"=>"Debe seleccionar una opcion", "result"=>$lobjContrato);
    }

    return json_encode($larrResultado);
  }

  public function StopContract($pintIdContrato){
    $ldatFechaActual = date('Y-m-d h:i:s');
    $ldatFechaEmision = date('Y-m')."-01";
    $lintIdUsuario = \Session::get('uid');
    $lintIdContrato = $pintIdContrato;

    //Buscamos la información del contrato
    $lobjContrato = \DB::table("tbl_contrato")
    ->where("tbl_contrato.contrato_id","=",$lintIdContrato)
    ->first();

    if ($lobjContrato){

      $lintEntryBy = $lobjContrato->entry_by_access;

      //Buscamos si es que no hay un documento carta de termino creado
      //-------------------------------------------------------------------
      $lobjDocumentoCarta = \DB::table("tbl_documentos")
      ->where("tbl_documentos.IdTipoDocumento","=",9)
      ->where("tbl_documentos.IdEntidad","=",$lintIdContrato)
      ->where("tbl_documentos.Entidad","=",2)
      ->first();

      if (!$lobjDocumentoCarta) {
        //Guardamos el anexo anexo de
        $lobjDocumentoData = array("contrato_id"=>$lintIdContrato,
                                 "Entidad" => 2,
                                 "IdEntidad"=>$lintIdContrato,
                                 "IdTipoDocumento" => 9,
                                 "FechaEmision"=> $ldatFechaEmision,
                                 "FechaVencimiento" => NULL,
                                 "IdEstatus" => 1,
                                 "createdOn"=>$ldatFechaActual,
                                 "entry_by"=>$lintIdUsuario,
                                 "entry_by_access"=>$lintEntryBy);
        $lintResultAnexo = \DB::table("tbl_documentos")->insertGetId($lobjDocumentoData);
        return array("status" => "success", "code"=>1,"message"=>"Carta de termino generada satisfatoriamente", "result"=>$lobjContrato);
      }else{
        return array("status" => "success", "code"=>3,"message"=>"Ya posee una carta de termino", "result"=>$lobjContrato);
      }
    }

  }
  public function ChangeContract($pintIdContrato, $pintIdDocumento, $pdatFechaContrato){
    $ldatFechaActual = date('Y-m-d h:i:s');
    $ldatFechaEmision = date('Y-m')."-01";
    $lintIdUsuario = \Session::get('uid');
    $lintIdContrato = $pintIdContrato;
    $lintIdDocumento = $pintIdDocumento;

    //Buscamos la información del contrato
    $lobjContrato = \DB::table("tbl_contrato")
    ->where("tbl_contrato.contrato_id","=",$lintIdContrato)
    ->first();

    if ($lobjContrato){

      $lintEntryBy = $lobjContrato->entry_by_access;

      $lobjDocumentoContrato = \DB::table("tbl_documentos")
      ->where("tbl_documentos.IdDocumento","=",$lintIdDocumento)
      ->first();

      if ($lobjDocumentoContrato){

        if ($lobjDocumentoContrato->IdEstatus != 5 ){
          return array("status" => "success", "code"=>2,"message"=>"El documento contractual no está aprobado", "result"=>$lobjContrato);
        }else{

          //Buscamos si es que no hay un documento anexo de contrato ya creado
          //-------------------------------------------------------------------
          $lobjDocumentoContratoAnexo = \DB::table("tbl_documentos")
          ->where("tbl_documentos.IdTipoDocumento","=",31)
          ->where("tbl_documentos.IdEntidad","=",$lintIdContrato)
          ->where("tbl_documentos.IdEstatus","=",1)
          ->where("tbl_documentos.Entidad","=",2)
          ->first();

          if (!$lobjDocumentoContratoAnexo) {
            //Guardamos el anexo anexo de
            $lobjDocumentoData = array("IdDocumentoRelacion" => $lintIdDocumento,
                                     "contrato_id"=>$lintIdContrato,
                                     "Entidad" => 2,
                                     "IdEntidad"=>$lintIdContrato,
                                     "IdTipoDocumento" => 31,
                                     "FechaEmision"=> $ldatFechaEmision,
                                     "FechaVencimiento" => $pdatFechaContrato,
                                     "IdEstatus" => 1,
                                     "createdOn"=>$ldatFechaActual,
                                     "entry_by"=>$lintIdUsuario,
                                     "entry_by_access"=>$lintEntryBy);
            $lintResultAnexo = \DB::table("tbl_documentos")->insertGetId($lobjDocumentoData);
            return array("status" => "success", "code"=>1,"message"=>"Anexo generado satisfatoriamente", "result"=>$lobjContrato);
          }else{
            return array("status" => "success", "code"=>3,"message"=>"Ya posee un anexo de contrato generado", "result"=>$lobjContrato);
          }

        }

      }else{
        return array("status" => "success", "code"=>2,"message"=>"El documento contractual no existe o no se encuentra aprobado", "result"=>$lobjContrato);
      }
    }else{
      return array("status" => "success", "code"=>0,"message"=>"El contrato no se encuentra asignado", "result"=>$lobjContrato);
    }

  }

  function getUpdate(Request $request, $id = null) {

    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');

    if($id =='')
    {
     if($this->access['is_add'] ==0 )
     return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
    }

    if($id !='')
    {
     if($this->access['is_edit'] ==0 )
     return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
    }

    $lobjContrato = new MyContracts($id);
    $lobjDataContrato = $lobjContrato->getDatos();

    //Recuperamos las etiquetas de los campos
    $lobjInfoGeoReporte = $this->info;
    $this->data['Campos'] = array();
    foreach ($lobjInfoGeoReporte['config']['forms'] as $t) {
      $this->data['Campos'][$t['field']] = \SiteHelpers::activeLang($t['field'],(isset($t['language'])? $t['language']: array()));
    }

    $this->data['setting']   = $this->info['setting'];
    $this->data['fields']   =  \AjaxHelpers::fieldLang($this->info['config']['forms']);
    $datageneral['fields']   =  $this->data['fields'];

    $row = $this->model->find($id);

    $this->data['selectIdSubContratista'] = array();

    if($row)
    {
     $this->data['row']   =  $row;

     //Buscamos si el contratista al que pertenece e contrato tiene subcontratistas
     //------------------------------------------------------------------------------
     $this->data['selectIdSubContratista'] = \DB::table('tbl_subcontratistas')
     ->join("tbl_contratistas","tbl_subcontratistas.SubContratista","=","tbl_contratistas.IdContratista")
     ->leftjoin("tbl_contratos_subcontratistas","tbl_contratos_subcontratistas.IdSubContratista", "=", \DB::raw("tbl_subcontratistas.SubContratista AND tbl_contratos_subcontratistas.contrato_id = ".$id))
     ->select(\DB::raw('tbl_subcontratistas.SubContratista as value'), \DB::raw('concat(tbl_contratistas.RUT,\' \',tbl_contratistas.RazonSocial) as display'), \DB::raw(" case when tbl_contratos_subcontratistas.contrato_id is not null then 'selected' else '' end as isselect"))
     ->where('tbl_subcontratistas.IdContratista', '=', $row['IdContratista'])->get();

     $this->data['selectCostoId'] = \DB::table("tbl_contclasecosto")
        ->leftjoin("tbl_contratos_centrocosto","tbl_contratos_centrocosto.centrocosto_id", "=", \DB::raw("tbl_contclasecosto.claseCosto_id AND tbl_contratos_centrocosto.contrato_id = ".$id))
        ->select(\DB::raw('tbl_contclasecosto.claseCosto_id as value'), \DB::raw("CONCAT(tbl_contclasecosto.ccost_numero, ' ', tbl_contclasecosto.ccost_nombre) as display"),\DB::raw(" case when tbl_contratos_centrocosto.contrato_id is not null then 'selected' else '' end as isselect"))
        ->orderBy("tbl_contclasecosto.ccost_nombre","ASC")
        ->get();


     $this->data['subContratista'] = \DB::table('tbl_contratos_subcontratistas')->Select('IdSubContratista')->where('contrato_id',$row['contrato_id'])->get();

     $this->data['rowContratoComercial'] = \DB::table('tbl_documentos')
          ->select(\DB::raw("tbl_documentos.*"))
          ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento", "=", "tbl_tipos_documentos.IdTipoDocumento")
          ->where("tbl_documentos.IdEntidad","=",$id)
          ->where("tbl_documentos.Entidad","=",2)
          ->where("tbl_documentos.IdEstatus","!=",1)
          ->where("tbl_tipos_documentos.IdProceso","=",26)
          ->get();

     $this->data['rowRContratistas'] = \DB::table('tbl_documentos')
      ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
      ->join('tbl_contratistas', 'tbl_contratistas.IdContratista', '=', 'tbl_documentos.IdEntidad')
      ->select(\DB::raw('0 as IdDocumento'),
               'tbl_documentos.Entidad',
               'tbl_documentos.IdTipoDocumento',
               'tbl_tipos_documentos.Periodicidad',
               "tbl_tipos_documentos.Descripcion",
               \DB::raw('count(tbl_documentos.IdTipoDocumento) as Cantidad'))
      ->where('tbl_documentos.Entidad',1)
      ->where('tbl_documentos.IdEstatus', '!=', '99')
      ->where('tbl_contratistas.IdContratista', '=', $row['IdContratista'])
      ->groupBy('tbl_documentos.Entidad',
                'tbl_documentos.IdTipoDocumento',
                'tbl_tipos_documentos.Periodicidad',
                'tbl_tipos_documentos.Descripcion')
      ->orderBy('tbl_tipos_documentos.Periodicidad','asc')
      ->get();

     $this->data['rowRContratos'] = \DB::table('tbl_documentos')
      ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
      ->select(\DB::raw('0 as IdDocumento'),
               'tbl_documentos.Entidad',
              'tbl_documentos.IdTipoDocumento',
              'tbl_tipos_documentos.Periodicidad',
              'tbl_tipos_documentos.Descripcion',
              \DB::raw('count(tbl_documentos.IdTipoDocumento) as Cantidad'))
      ->distinct()
      ->where('tbl_documentos.Entidad',2)
      ->where('tbl_documentos.IdEstatus', '!=', '99')
      ->where('tbl_documentos.IdEntidad', '=', $row['contrato_id'])
      ->groupBy('tbl_documentos.Entidad',
                'tbl_documentos.IdTipoDocumento',
                'tbl_tipos_documentos.Periodicidad',
                'tbl_tipos_documentos.Descripcion')
      ->orderBy('tbl_tipos_documentos.Periodicidad','asc')
      ->get();

     $this->data['rowContrCentros'] =  \DB::table('tbl_contratos_centros')
      ->join('tbl_centro', 'tbl_contratos_centros.IdCentro', '=', 'tbl_centro.IdCentro')
      ->select('tbl_contratos_centros.contrato_id','tbl_contratos_centros.IdCentro','tbl_centro.Descripcion')
      ->where('contrato_id', '=', $row['contrato_id'])
      ->get();

  	   $this->data['rowPersonasAreas'] = \DB::table('tbl_contratos_personas')
        ->select('tbl_contratos_personas.IdPersona','tbl_acceso_areas.IdAreaTrabajo')
        ->leftjoin("tbl_accesos","tbl_accesos.IdPersona","=", \DB::raw("tbl_contratos_personas.IdPersona AND tbl_accesos.IdTipoAcceso = 1"))
        ->leftjoin('tbl_acceso_areas', 'tbl_accesos.IdAcceso', '=', 'tbl_acceso_areas.IdAcceso')
        ->where('tbl_contratos_personas.contrato_id',$row['contrato_id'])
        ->get();

      $this->data['rowActivosAreas'] = \DB::table('tbl_accesos_activos')
       ->join('tbl_acceso_activos_areas', 'tbl_accesos_activos.IdAccesoActivo', '=', 'tbl_acceso_activos_areas.IdAccesoActivo')
       ->select('tbl_accesos_activos.IdActivoData','tbl_acceso_activos_areas.IdAreaTrabajo')
       ->where('tbl_accesos_activos.contrato_id',$row['contrato_id'])->get();

      $datageneral['selectServicios'] = Contratosservicios::select(\DB::raw('id as value'),
                                                                 \DB::raw('name as display'),
                                                                 "IdEstatus")
                                      ->where('id',$row['idservicio'])
                                      ->orderBy('display','asc')
                                      ->get();
      $lintIdUserContract = $row->{'entry_by_access'};



    } else {

     $this->data['row']   = $this->model->getColumnTable('tbl_contrato');
     $this->data['rowContratoComercial'] = NULL;

     $this->data['rowRContratistas'] = \DB::table('tbl_requisitos')
      ->join('tbl_tipos_documentos', 'tbl_requisitos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
      ->join('tbl_entidades', 'tbl_requisitos.Entidad', '=', 'tbl_entidades.IdEntidad')
      ->select('tbl_requisitos.IdRequisito',
               'tbl_requisitos.Entidad',
               'tbl_requisitos.IdTipoDocumento',
               'tbl_entidades.Entidad AS EntidadV',
               'tbl_tipos_documentos.Descripcion',
               \DB::raw("'' as Cantidad"),
               'tbl_tipos_documentos.Periodicidad')
      ->distinct()
      ->where('tbl_requisitos.Entidad',1)
      ->where('tbl_requisitos.IdEvento',1)
      ->where(\DB::raw("ifnull(tbl_tipos_documentos.Periodicidad,0)"),"!=",\DB::raw(1))
      ->get();

     $this->data['rowRContratos'] = \DB::table('tbl_requisitos')
       ->join('tbl_tipos_documentos', 'tbl_requisitos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
      ->join('tbl_entidades', 'tbl_requisitos.Entidad', '=', 'tbl_entidades.IdEntidad')
      ->select('tbl_requisitos.IdRequisito',
               'tbl_requisitos.Entidad',
               'tbl_requisitos.IdTipoDocumento',
               'tbl_entidades.Entidad AS EntidadV',
               'tbl_tipos_documentos.Descripcion',
               \DB::raw("'' as Cantidad"),
               'tbl_tipos_documentos.Periodicidad')
      ->distinct()
      ->where('tbl_requisitos.Entidad',2)
      ->where('tbl_requisitos.IdEvento',1)
      ->where(\DB::raw("ifnull(tbl_tipos_documentos.Periodicidad,0)"),"!=",\DB::raw(1))
      ->get();

      $datageneral['selectServicios'] = Contratosservicios::select(\DB::raw('id as value'),
                                                                 \DB::raw('name as display'),
                                                                 "IdEstatus")
                                      ->orderBy('display','asc')
                                      ->get();

  	  $this->data['rowPersonasAreas'] = array();
      $this->data['rowActivosAreas'] = array();

    }

    $this->data['selectIdContratista'] = \DB::table('tbl_contratistas')
    ->select(\DB::raw('tbl_contratistas.IdContratista as value'), \DB::raw('concat(tbl_contratistas.RUT,\' \',tbl_contratistas.RazonSocial) as display'))
    ->where('IdEstatus','=',1)
    ->get();

     $this->data['anotaciones'] =  \DB::table('tbl_concepto_anotacion')->get();

    //controlamos las vistas según el perfil
    $larrAccessUser = self::GetAccess();

    //Construimos la información general del contrato
    $datageneral['lintLevelUser'] = $lintLevelUser;
    $datageneral['rowContratoComercial'] = $this->data['rowContratoComercial'];
    $datageneral['row'] = $this->data['row'];
    $datageneral['selectGrupoEspecifico'] = Gruposespecificos::select(\DB::raw('id as value'),
                                                                   \DB::raw('name as display'))
                                      ->orderBy('display','asc')
                                      ->get();

    $datageneral['selectIdContratista'] = $this->data['selectIdContratista'];
    $datageneral['selectIdSubContratista'] = $this->data['selectIdSubContratista'];
    $datageneral['selectGarantias'] = \DB::table('tbl_tipos_de_garantias')
    ->select(\DB::raw('tbl_tipos_de_garantias.IdTipoGarantia as value'), \DB::raw('tbl_tipos_de_garantias.Descripcion as display'), "tbl_tipos_de_garantias.SinMonto", "tbl_tipos_de_garantias.IdEstatus")
    ->get();
    $datageneral['selectAdminId'] = \DB::table("tb_users")
    ->select(\DB::raw('tb_users.id as value'), \DB::raw('concat(tb_users.first_name,\' \',tb_users.last_name) as display'))
    ->join("tb_groups","tb_groups.group_id","=","tb_users.group_id")
    ->where("tb_groups.level","=",4)
    ->get();
    $datageneral['selectCategoriaId'] = \DB::table("tbl_contcategorias")
    ->select(\DB::raw('tbl_contcategorias.categorias_id as value'), \DB::raw('tbl_contcategorias.cat_nombre as display'))
    ->orderBy("tbl_contcategorias.cat_nombre","ASC")
    ->get();
    $datageneral['selectSegmentoId'] = \DB::table("tbl_contsegmento")
    ->select(\DB::raw('tbl_contsegmento.segmento_id as value'), \DB::raw('tbl_contsegmento.seg_nombre as display'))
    ->orderBy("tbl_contsegmento.seg_nombre","ASC")
    ->get();
    $datageneral['selectGeoId'] = \DB::table("tbl_contgeografico")
    ->select(\DB::raw('tbl_contgeografico.geo_id as value'), \DB::raw('tbl_contgeografico.geo_nombre as display'))
    ->orderBy("tbl_contgeografico.geo_nombre","ASC")
    ->get();
    $datageneral['selectFuncionalId'] = \DB::table("tbl_contareafuncional")
    ->select(\DB::raw('tbl_contareafuncional.afuncional_id as value'), \DB::raw('tbl_contareafuncional.afuncional_nombre as display'))
    ->orderBy("tbl_contareafuncional.afuncional_nombre","ASC")
    ->get();

    $datageneral['selectIdComplejidad'] = Complejidad::all();
    $datageneral['selectIdMoneda'] = Moneda::all();
    $datageneral['selectIdTipoAdjudicacion'] = TipoAdjudicacion::all();
    $datageneral['selectJustificacionProveedorUnico'] = JustificacionProveedorUnico::all();
    $datageneral['selectAdminIdContratista'] = \DB::table("tb_users")
    ->select(\DB::raw('tb_users.id as value'), \DB::raw('concat(tb_users.first_name,\' \',tb_users.last_name) as display'))
    ->join("tb_groups","tb_groups.group_id","=","tb_users.group_id")
    ->whereIn("tb_groups.level",[6,15])
    ->get();


        if($row){
            $datageneral['selectCostoId'] = $this->data['selectCostoId'];
        }
      else{
          $datageneral['selectCostoId'] = \DB::table("tbl_contclasecosto")
              ->select(\DB::raw('tbl_contclasecosto.claseCosto_id as value'), \DB::raw("CONCAT(tbl_contclasecosto.ccost_numero, ' ', tbl_contclasecosto.ccost_nombre) as display"),\DB::raw(" ''  as isselect"))
              ->orderBy("tbl_contclasecosto.ccost_nombre","ASC")
              ->get();
      }


    $datageneral['selectIdTipoGasto'] = \DB::table("tbl_contrato_tipogasto")
    ->select(\DB::raw('tbl_contrato_tipogasto.id_tipogasto as value'), \DB::raw('tbl_contrato_tipogasto.nombre_gasto as display'))
    ->orderBy("tbl_contrato_tipogasto.nombre_gasto","ASC")
    ->get();
    $datageneral['selectIdExtension'] = \DB::table("tbl_contrato_extension")
    ->select(\DB::raw('tbl_contrato_extension.id_extension as value'), \DB::raw('tbl_contrato_extension.nombre as display'))
    ->orderBy("tbl_contrato_extension.nombre","ASC")
    ->get();
    $datageneral['Campos'] = $this->data['Campos'];
    $datageneral['lintIdUser'] = $lintIdUser;
    $datageneral['lobjDataContrato'] = $lobjDataContrato;
    $this->data['viewGeneral'] = view('contratos.form.general.'.$larrAccessUser['general']['view'],$datageneral);
    $datageneral = "";

    $this->data['areasT'] = \DB::table('tbl_area_de_trabajo')->get();

    //Construimos los centros según el modelo de sximo
    //Filtramos los centros que ya no están seleccionados
    $lobjCentrosOperacionales = \DB::table('tbl_centro')
    ->select(\DB::raw('tbl_centro.IdCentro as value'), \DB::raw('tbl_centro.Descripcion as display'))
    ->orderBy("tbl_centro.Descripcion","ASC");
    if ($id){
      $lobjCentrosOperacionales = $lobjCentrosOperacionales->whereNotExists(function ($query) use ($id){
          $query->select(\DB::raw(1))
                ->from('tbl_contratos_centros')
                ->whereRaw('tbl_contratos_centros.IdCentro = tbl_centro.IdCentro')
                ->whereRaw('tbl_contratos_centros.contrato_id = '.$id);
      });
    }
    $datacentros['selectCentrosOperacionales']  = $lobjCentrosOperacionales->get();
    $datacentros['selectIdTipoCentro'] = \DB::table('tbl_centros_tipos')
        ->select(\DB::raw('tbl_centros_tipos.id as value'),
            \DB::raw('tbl_centros_tipos.nombre as display'))->get();
    $larrContratosCentros = array( "title" => "Centros", "master" => "contratos", "master_key" => "contrato_id", "module" => "contratoscentros", "table" => "tbl_contratos_centros", "key" => "contrato_id" );
    $larrCentrosContrato = $this->detailview($this->modelviewtwo ,  $larrContratosCentros ,$id );
    $datacentros['subformcentros'] = $larrCentrosContrato;
    $this->data['viewCentros'] = view('contratos.form.centros.'.$larrAccessUser['centros']['view'],$datacentros);
    $datacentros="";

      //Construimos los requisitos
    $this->data['selectIdTipoDocumento'] =  \DB::table("tbl_tipos_documentos")
    ->select(\DB::raw('tbl_tipos_documentos.IdTipoDocumento as value'),
             \DB::raw('tbl_tipos_documentos.Descripcion as display'),
             "tbl_tipos_documentos.Entidad")
    ->Where(\DB::raw("ifnull(tbl_tipos_documentos.Periodicidad,0)"),"!=",1)
    ->orderBy("tbl_tipos_documentos.Entidad","ASC")
    ->orderBy("tbl_tipos_documentos.Descripcion","ASC")
    ->get();
    $this->data['viewRequisitos'] = view('contratos.form.requisitos.'.$larrAccessUser['requisitos']['view'],$this->data);
    $datarequisitos = "";

    //Construimos las personas
    $lobjPersonas = \DB::table('tbl_contratos_subcontratistas')
    ->select(\DB::raw('count(*) as IdContratista'))
    ->where('tbl_contratos_subcontratistas.contrato_id','=',$id)
    ->first();
    $lobjPersonasSubcontrato = array();
    $larrRelationshipUser = array("relationship"=>"00","IdContratista"=>"");
    if ($lobjPersonas->IdContratista > 0) { //El contrato es compartido
        if ($lintLevelUser == 6){
          //Verificamos si el usuario es el dueño o uno de sus subcontratistas
          $larrRelationshipUser = \MySourcing::RelationshiplUser($id, $lintIdUser);
          //if ($larrRelationshipUser['relationship']=="01"){
          $lobjPersonasSubcontrato =  \DB::table("tbl_contratos_personas")
            ->select('tbl_contratos_personas.IdPersona', 'tbl_contratistas.RazonSocial', \DB::raw("concat(tbl_personas.RUT, ' ', tbl_personas.Nombres, ' ', tbl_personas.Apellidos) as Persona"), \DB::raw('tbl_roles.Descripción as Rol'), 'tbl_contratos_personas.FechaInicioFaena')
            ->join("tbl_personas","tbl_personas.IdPersona","=","tbl_contratos_personas.IdPersona")
            ->join("tbl_roles","tbl_contratos_personas.IdRol","=","tbl_roles.IdRol")
            ->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contratos_personas.IdContratista")
            ->where("tbl_contratos_personas.contrato_id","=",$id)
            ->where("tbl_contratos_personas.IdContratista","!=",$larrRelationshipUser['IdContratista'])
            ->get();

          //}
        }else{
          $lobjPersonasSubcontrato =  \DB::table("tbl_contratos_personas")
            ->select('tbl_contratos_personas.IdPersona', 'tbl_contratistas.RazonSocial', \DB::raw("concat(tbl_personas.RUT, ' ', tbl_personas.Nombres, ' ', tbl_personas.Apellidos) as Persona"), \DB::raw('tbl_roles.Descripción as Rol'), 'tbl_contratos_personas.FechaInicioFaena')
            ->join("tbl_personas","tbl_personas.IdPersona","=","tbl_contratos_personas.IdPersona")
            ->join("tbl_roles","tbl_contratos_personas.IdRol","=","tbl_roles.IdRol")
            ->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contratos_personas.IdContratista")
            ->where("tbl_contratos_personas.contrato_id","=",$id)
            ->where("tbl_contratos_personas.IdContratista","!=",$larrRelationshipUser['IdContratista'])
            ->get();
        }
    }
    $datapersonas['selectIdPersona'] = \DB::table("tbl_personas")
    ->select(\DB::raw('tbl_personas.IdPersona as value'), \DB::raw('concat(tbl_personas.RUT,\' \',tbl_personas.Nombres,\' \', tbl_personas.Apellidos) as display'))
    ->whereNotExists(function ($query) {
        $query->select(\DB::raw(1))
              ->from('tbl_contratos_personas')
              ->whereRaw('tbl_contratos_personas.IdPersona = tbl_personas.IdPersona');
    })
    ->orderBy("tbl_personas.Rut","ASC");
    if ($lintLevelUser==6) {
        $datapersonas['selectIdPersona']->where("tbl_personas.entry_by_access","=",$lintIdUser);
    }

        $datapersonas['cartaA'] = 0;
        $SubCont = \MyPeoples::EsSubcontratista($id);
        if ( $SubCont>0){

            //Verificamos que si es un subcontratista la carta de aprobacion este aprobada
            $lobjCartaAprobacion = \DB::table("tbl_documentos")
                ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento", "=", "tbl_tipos_documentos.IdTipoDocumento")
                ->where("tbl_documentos.Entidad","=","9")
                ->where("tbl_documentos.IdEntidad","=",$SubCont)
                ->where("tbl_documentos.contrato_id","=",$id)
                ->where("tbl_tipos_documentos.IdProceso","=",89)
                ->where("tbl_documentos.IdEstatus","!=",5)
                ->pluck("tbl_documentos.IdDocumento");

            if ($lobjCartaAprobacion){
                $datapersonas['cartaA'] = $lobjCartaAprobacion;
            }
        }


    $datapersonas['personassubcontrato'] = $lobjPersonasSubcontrato;
    $datapersonas['anotaciones']=  \DB::table('tbl_concepto_anotacion')->where('IdEstatus', '=', 1)->get();
    $datapersonas['selectIdPersona'] = $datapersonas['selectIdPersona']->get();
    $datapersonas['selectIdRol'] = Roles::select("tbl_roles.IdRol as value",
                                                 "tbl_roles.Descripción as display"
                                                )
                                          ->orderBy("display","asc");

    if ($lobjDataContrato){
      $datapersonas['selectIdRol'] = $datapersonas['selectIdRol']->join("tbl_roles_servicios","tbl_roles_servicios.idrol","=","tbl_roles.idrol")
        ->where('tbl_roles_servicios.idservicio', $lobjDataContrato->idservicio);
    }
    $datapersonas['selectIdRol'] = $datapersonas['selectIdRol']->get();


    $datapersonas['lintIdUser'] = $lintIdUser;
    $datapersonas['relationshipuser'] = $larrRelationshipUser;
    $datapersonas['subcontratos'] = $lobjPersonas->IdContratista;
    $datapersonas['lintLevelUser'] = $lintLevelUser;
    $datapersonas['IdContratista'] = $this->data['row']['IdContratista'];
    $datapersonas['selectIdContratista'] = $this->data['selectIdContratista'];
    $datapersonas['selectIdSubContratista'] = $this->data['selectIdSubContratista'];
    if ($id){
        $datapersonas['selectIdContrato'] = $lobjContrato::getPosiblesCambios();
    }else{
        $datapersonas['selectIdContrato'] = array();
    }
    $larrPersonasContrato = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
    $datapersonas['subformpersonas'] = $larrPersonasContrato;
    $this->data['viewPersonas'] = view('contratos.form.personas.'.$larrAccessUser['personas']['view'],$datapersonas);
    $datapersonas="";


    //Construimos las personas centro
    $datapersonascentro["subformpersonascentro"] = $this->data['rowPersonasAreas'];
    $lintIdPersona = 0;
    $lstrPerconasCentro = "";
    foreach ($datapersonascentro["subformpersonascentro"] as $value) {
      if ($lintIdPersona!=$value->IdPersona){
        if ($lintIdPersona!=0){
          $lstrPerconasCentro .= ' ], ';
        }
        $lintIdPersona = $value->IdPersona;
        $lstrPerconasCentro .= $value->IdPersona.':';
        $lstrPerconasCentro .= ' [ "'.$value->IdAreaTrabajo.'", ';
      }else{
          $lstrPerconasCentro .= ' "'.$value->IdAreaTrabajo.'", ';
      }
    }
    if ($lintIdPersona!=0){
      $lstrPerconasCentro .= ' ] ';
    }
    $datapersonascentro["subformpersonascentroarray"] = $lstrPerconasCentro;
    $datapersonascentro["contrato_id"] = $id;
    $datapersonascentro["subformpersonascontrato"] = $larrPersonasContrato;
    $datapersonascentro['subformcentroscontrato'] = $larrCentrosContrato;
    $this->data['viewPersonasCentros'] = view('contratos.form.personascentros.'.$larrAccessUser['personascentros']['view'],$datapersonascentro);
    $datapersonascentro= "";

    //Construimos los activos centros
    $dataactivoscentro["subformactivoscentro"] = $this->data['rowActivosAreas'];
    $lintIdActivo = 0;
    $lstrActivoCentro = "";
    foreach ($dataactivoscentro["subformactivoscentro"] as $value) {
      if ($lintIdActivo!=$value->IdActivoData){
        if ($lintIdActivo!=0){
          $lstrActivoCentro .= ' ], ';
        }
        $lintIdActivo = $value->IdActivoData;
        $lstrActivoCentro .= '"'.$value->IdActivoData.'":';
        $lstrActivoCentro .= ' [ 0, '.$value->IdAreaTrabajo.', ';
      }else{
        $lstrActivoCentro .= ' '.$value->IdAreaTrabajo.', ';
      }
    }
    if ($lintIdActivo!=0){
      $lstrActivoCentro .= ' ] ';
    }
    $dataactivoscentro["subformactivoscentroarray"] = $lstrActivoCentro;
    $dataactivoscentro["subformactivoscontrato"] = \DB::table('tbl_activos_data')
    ->join('tbl_activos', 'tbl_activos_data.IdActivo', '=', 'tbl_activos.IdActivo')
    ->join('tbl_activos_detalle', 'tbl_activos.IdActivo', '=', 'tbl_activos_detalle.IdActivo')
    ->join('tbl_activos_data_detalle', function ($join) {
        $join->on('tbl_activos_detalle.IdActivoDetalle', '=', 'tbl_activos_data_detalle.IdActivoDetalle')->on('tbl_activos_data.IdActivoData', '=', 'tbl_activos_data_detalle.IdActivoData');
    })
    ->select('tbl_activos_data.IdActivoData','tbl_activos_data.IdActivo','tbl_activos_data.contrato_id','tbl_activos.Descripcion','tbl_activos.IdTipoAcceso','tbl_activos_detalle.IdActivoDetalle','tbl_activos_detalle.Etiqueta','tbl_activos_data_detalle.Valor')
    ->where("tbl_activos_data.contrato_id",$id)
    ->where('tbl_activos.ControlaAcceso', '=', 1)
    ->where('tbl_activos_detalle.Unico', '=', "SI")
    ->get();
    $dataactivoscentro['subformcentroscontrato'] = $larrCentrosContrato;
    $this->data['viewActivos'] = view('contratos.form.activos.'.$larrAccessUser['activos']['view'],$dataactivoscentro);
    $dataactivoscentro = "";

        if ($id){
            $dataacciones['lobjAcciones'] = \DB::table("tbl_contratos_acciones")
                ->select("tbl_contratos_acciones.createdOn","tbl_contratos_acciones.observaciones", "tb_users.first_name", "tb_users.last_name", "tb_users.avatar", "tbl_acciones.nombre", "tbl_acciones.descripcion")
                ->join("tbl_acciones","tbl_contratos_acciones.accion_id", "=", "tbl_acciones.IdAccion")
                ->join("tb_users","tb_users.id", "=", "tbl_contratos_acciones.entry_by")
                ->where("tbl_contratos_acciones.contrato_id","=",$id)
                ->orderBy("tbl_contratos_acciones.id",'desc')
                ->get();
            //Acciones
            $this->data['viewAcciones'] = view('contratos.form.acciones.'.$larrAccessUser['acciones']['view'],$dataacciones);
        }

      //garantias
      $this->data['selectGarantias'] = \DB::table('tbl_tipos_de_garantias')
      ->select(\DB::raw('tbl_tipos_de_garantias.IdTipoGarantia as value'), \DB::raw('tbl_tipos_de_garantias.Descripcion as display'), "tbl_tipos_de_garantias.SinMonto", "tbl_tipos_de_garantias.IdEstatus")
      ->get();

      //garantias
      $this->data['selectCentrosTipos'] = \DB::table('tbl_centros_tipos')
      ->select(\DB::raw('tbl_centros_tipos.id as value'), \DB::raw('tbl_centros_tipos.Nombre as display'))
      ->get();

    $this->data['setting']   = $this->info['setting'];
    $this->data['fields']   =  \AjaxHelpers::fieldLang($this->info['config']['forms']);

    $this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );


    //Construimos basados en sximo un nuevo subformulario
    $larrContratosCentros = array( "title" => "Centros", "master" => "contratos", "master_key" => "contrato_id", "module" => "contratoscentros", "table" => "tbl_contratos_centros", "key" => "contrato_id" );
    $this->data['subformtwo'] = $this->detailview($this->modelviewtwo ,  $larrContratosCentros ,$id );

    //Construimos basados en sximo un nuevo subformulario de items
    if (CNF_MODULO_PARTIDAS) {
      $larrContratosPartidas = array( "title" => "Partidas", "master" => "contratos", "master_key" => "contrato_id", "module" => "itemsdetail", "table" => "tbl_contratos_items", "key" => "contrato_id" );
      $datapartidas['subformpartidas'] = $this->detailview($this->modelitemsdetail ,  $larrContratosPartidas ,$id );
      $datapartidas['contrato_id'] = $id;
      $this->data['viewPartidas'] = view('contratos.form.partidas.'.$larrAccessUser['partidas']['view'],$datapartidas);
      $datapartidas = "";
    }

    $this->data['id'] = $id;

    return view('contratos.form.form',$this->data);
  }

  public function getShow( $id = null)
  {

      if($this->access['is_detail'] ==0)
          return Redirect::to('dashboard')
              ->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

      $row = $this->model->getRow($id);
      if($row)
      {
          $this->data['row'] =  $row;

          $this->data['id'] = $id;
          $this->data['access']       = $this->access;
          $this->data['setting']      = $this->info['setting'];
          $this->data['fields']       = \AjaxHelpers::fieldLang($this->info['config']['grid']);
          $this->data['subgrid']      = (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
          return view('contratos.view',$this->data);

      } else {

          return response()->json(array(
              'status'=>'error',
              'message'=> \Lang::get('core.note_error')
          ));
      }
  }

  public function getShowlist( Request $request ) {
    // Get Query
    $sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
    $order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
    // End Filter sort and order for query
    // Filter Search for query
    $filter = '';
    if(!is_null($request->input('search')))
    {
      $search =   $this->buildSearch('maps');
      $filter = $search['param'];
      $this->data['search_map'] = $search['maps'];
    }
    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');

    $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
    if ($lintLevelUser==6 || $lintLevelUser==15) {
       $filter .= " AND (tbl_contrato.entry_by_access = ".$lintIdUser." OR tbl_contrato.contrato_id IN (select tbl_contratos_subcontratistas.contrato_id from tbl_contratistas inner join tbl_contratos_subcontratistas on tbl_contratos_subcontratistas.IdSubContratista = tbl_contratistas.IdContratista where entry_by_access = ".$lintIdUser.") )  ";
    }else{
       $filter .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';
    }

    $params = array(
      'page'    => '',
      'limit'   => '',
      'sort'    => $sort ,
      'order'   => $order,
      'params'  => $filter,
      'global'  => (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
    );
    // Get Query
    $results = $this->model->getRows( $params );

    $larrResult = array();
    $larrResultTemp = array();
    $i = 0;

    foreach ($results['rows'] as $row) {

      $id = $row->contrato_id;

      $larrResultTemp = array('id'=> ++$i,
                    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$id.'" /> '
                    );
      foreach ($this->info['config']['grid'] as $field) {
        if($field['view'] =='1') {
          $limited = isset($field['limited']) ? $field['limited'] :'';
          if (\SiteHelpers::filterColumn($limited )){
            $value = \SiteHelpers::formatRows($row->{$field['field']}, $field , $row);
            $larrResultTemp[$field['field']] = $value;
          }
        }
      }
      $onclick = "";
      $lstrModule = "contratos";
      $lstrHtml = "";
      if($this->access['is_detail'] ==1) {
        if($this->info['setting']['view-method'] != 'expand'){
          $onclick = " onclick=\"ajaxViewDetail('#".$lstrModule."',this.href); return false; \"" ;
          if($this->info['setting']['view-method'] =='modal') {
            $onclick = " onclick=\"SximoModal(this.href,'View Detail'); return false; \"" ;
          }
          $lstrHtml .= '<a href="'.\URL::to($lstrModule.'/show/'.$id).'" '.$onclick.' class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_view').'"><i class="fa fa-search"></i></a>';
        }
      }
      if($this->access['is_edit'] ==1) {
        $onclick = " onclick=\"ajaxViewDetail('#".$lstrModule."',this.href); return false; \"" ;
        if($this->info['setting']['form-method'] =='modal'){
          $onclick = " onclick=\"SximoModal(this.href,'Edit Form'); return false; \"" ;
        }
        $lstrHtml .= ' <a href="'.\URL::to($lstrModule.'/update/'.$id).'" '.$onclick.'  class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class="fa  fa-edit"></i></a>';
      }
      if (isset($this->access['is_action_button']) && $this->access['is_action_button']==1) {
        $onclick = " onclick=\"SximoModalContract(this.href,'Edit Form'); return false; \"" ;
        $lstrHtml .= ' <a href="'.\URL::to($lstrModule.'/acciones/'.$id).'" '.$onclick.'  class="btn btn-xs btn-white tips" title="" data-original-title="Acciones"><i class="fa fa-cog"></i></a>';
      }
      if(isset($this->access['is_report_view']) && $this->access['is_report_view']==1){
        $lstrHtml .= '<a href="'.\URL::to('contratos/reportes/'.$id).'" onclick="SximoModal(this.href,\'View Detail\'); return false;" class="btn btn-xs btn-white tips" title="Reporte"><i class="fa fa-bar-chart"></i></a>';
      }
      $larrResultTemp['action'] = $lstrHtml;
      $larrResult[] = $larrResultTemp;
    }

    echo json_encode(array("data"=>$larrResult));
  }

public function postServicios (Request $request) {
      $lintIdGrupoEspecifico = $request->input('grupoespecifico');

      $lobjContratosServicios = Contratosservicios::select(\DB::raw('id as value'),
                                                               \DB::raw('name as display'),
                                                               "IdEstatus")
                                    ->orderBy('display','asc')
                                    ->where('idgrupoespecifico',$lintIdGrupoEspecifico)
                                    ->get();
      return $lobjContratosServicios;

}

public function getShowdocuments(Request $request){
    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));

      $lintIdUser = \Session::get('uid');
      $lintIdContrato = $request->input('idcontrato');
      $larrContratista = \DB::table('tbl_contratistas')
                           ->select('tbl_contratistas.IdContratista')
                           ->join('tbl_contrato','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
                           ->where('tbl_contrato.contrato_id','=',$request->input('idcontrato'))
                           ->first();
      $lintIdContratista = $larrContratista->IdContratista;
      $larrResult = array();

      //\DB::enableQueryLog();

      $lobjDocumentos = \DB::table('tbl_documentos')
        ->select("tbl_documentos.IdDocumento","tbl_documentos.IdTipoDocumento", \DB::raw("tbl_tipos_documentos.Descripcion as TipoDocumento"), "tbl_documentos.FechaEmision", "tbl_documentos.FechaVencimiento", "tbl_documentos.IdEstatus", \DB::raw("tbl_documentos_estatus.Descripcion as Estatus"))
        ->join("tbl_tipos_documentos","tbl_tipos_documentos.IdTipoDocumento","=","tbl_documentos.IdTipoDocumento")
        ->join("tbl_documentos_estatus","tbl_documentos_estatus.IdEstatus","=","tbl_documentos.IdEstatus")
        ->where(function ($query) use ($lintIdContratista, $lintIdContrato) {
            $query->where("tbl_documentos.IdEntidad","=",$lintIdContrato)
                  ->where("tbl_documentos.Entidad","=",2)
                  ->orwhere(function($query) use ($lintIdContratista) {
                      $query->where("tbl_documentos.IdEntidad","=",$lintIdContratista)
                            ->where("tbl_documentos.Entidad","=", 1);
                  });
        })
        ->where(function ($query) {
            $query->where("tbl_documentos.IdEstatus", "!=", 5)
                ->orwhere(function ($query) {
                    $query->where("tbl_documentos.IdEstatus", "=", 5)
                    ->where("tbl_documentos.IdEstatusDocumento", "!=", "1");
                });
        })
        ->whereExists(function ($query) {
          $lintGroupUser = \MySourcing::GroupUser(\Session::get('uid'));
            $query->select(\DB::raw(1))
                  ->from('tbl_tipo_documento_perfil')
                  ->whereRaw('tbl_tipo_documento_perfil.IdPerfil = '.$lintGroupUser)
                  ->whereRaw('tbl_tipo_documento_perfil.IdTipoDocumento = tbl_documentos.IdTipoDocumento');
        })
        ->get();

        $lobjEncuestasDos =\DB::table('tbm_encuestas')
    		->select('tbm_encuestas.IdTipoDocumento')
    		->distinct()
    		->get();
    		$larrEncuentasDos = array();
    		foreach ($lobjEncuestasDos as $arrEncuetasDos) {
    			$larrEncuentasDos[$arrEncuetasDos->IdTipoDocumento] = 1;
    		}

        foreach ($lobjDocumentos as $row) {

      $id = $row->IdDocumento;
      $larrResultTemp = $row;
      $larrResultTemp->FechaVencimiento = \MyFormats::FormatDate($larrResultTemp->FechaVencimiento);
      $larrResultTemp->FechaEmision = \MyFormats::FormatDate($larrResultTemp->FechaEmision);
      if ($row->IdEstatus==5) {
        if (!(intval($row->IdTipoDocumento)==3 || intval($row->IdTipoDocumento)==6 || intval($row->IdTipoDocumento)==21 || intval($row->IdTipoDocumento)==26 || intval($row->IdTipoDocumento)==31)){
          $larrResultTemp->{'action'} = '<div class="bbb action dropup"><a href="'.\URL::to('documentos/update/-'.$id).'" onclick="SximoModalDocuments(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="Renovar"><i class="fa fa-calendar-times-o"></i></a></div>';
        }else{
          $larrResultTemp->{'action'} = "-";
        }
        }else{
        if (isset($larrEncuentasDos[$row->IdTipoDocumento])){
          $larrResultTemp->{'action'} = '<div class=" action dropup"><a href="' . \URL::to('encuestados/respuestas/doc=' . $id) . '" onclick="SximoModalDocuments(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="' . \Lang::get('core.btn_edit') . '"><i class="fa  fa-upload"></i></a></div>';
        }else{
          $larrResultTemp->{'action'} = '<div class="action dropup"><a href="'.\URL::to('documentos/update/'.$id).'" onclick="SximoModalDocuments(this.href,\'Edit Form\'); return false; "  class="btn btn-xs btn-white tips" title="Cargar"><i class="fa  fa-upload"></i></a></div>';
        }

        }
      $larrResult[] = $larrResultTemp;
    }

    echo json_encode(array("data"=>$larrResult));

        //var_dump(\DB::getQueryLog());

    //echo json_encode(array("data"=>$lobjDocumentos));
  }

 function getAcciones($id = null){

      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = \Session::get('uid');

      if($this->access['is_detail'] ==0)
      return Redirect::to('dashboard')
        ->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

      $lobjContrato = new MyContracts($id);
      $lobjDatosContrato =  $lobjContrato::getDatos();

      $lobjContrato = \DB::table("tbl_contrato")
      ->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista")
      ->select(\DB::raw("tbl_contrato.*"),"tbl_contratistas.Rut", "tbl_contratistas.RazonSocial")
      ->where("tbl_contrato.contrato_id","=",$id)
      ->first();

      if ($lobjDatosContrato) {

        $this->data['selectGarantias'] = \DB::table('tbl_tipos_de_garantias')
  ->select(\DB::raw('tbl_tipos_de_garantias.IdTipoGarantia as value'), \DB::raw('tbl_tipos_de_garantias.Descripcion as display'), "tbl_tipos_de_garantias.SinMonto", "tbl_tipos_de_garantias.IdEstatus")
  ->get();

        $this->data['id'] = $id;
        $this->data['access']   = $this->access;
        $this->data['setting']    = $this->info['setting'];
        $this->data['fields']     = \AjaxHelpers::fieldLang($this->info['config']['grid']);
        $this->data['LevelUser'] = $lintLevelUser;

        //Buscamos el contrato de trabajo que debe tener vigente
        $lobjDocumentoContractual = \DB::table('tbl_documentos')
        ->where("tbl_documentos.IdEntidad", "=", $id)
        ->where("tbl_documentos.entidad", "=", 2)
        ->where("tbl_documentos.IdTipoDocumento", "=", 26)
        ->first();

        if ($lobjDocumentoContractual){
          $ldatFechaContrato = $lobjDocumentoContractual->FechaVencimiento;
          $lintIdDocumento = $lobjDocumentoContractual->IdDocumento;
          $lobjContrato->{'FechaContrato'} = $ldatFechaContrato;
          $lobjContrato->{'IdDocumento'} = $lintIdDocumento;
          $lbolContratado = true;
        }else{
          $lobjContrato->{'FechaContrato'} = "";
          $lobjContrato->{'IdDocumento'} = "";
        }

        $this->data['lobjContrato'] =  $lobjDatosContrato;

        return view('contratos.acciones',$this->data);

      }else{
          return response()->json(array(
        'status'=>'error',
        'message'=> \Lang::get('core.note_error')
      ));
      }

    }

  function getShowlistupload(){
      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = \Session::get('uid');

      $lstrDirectory = \MyLoadbatch::getDirectory();
      $lstrDirectoryResult = \MyLoadbatch::getDirectoryResult();

      $lobjLastUpload = \DB::table('tbl_carga_masiva_log')
                             ->join('tb_users','tb_users.id', '=', 'tbl_carga_masiva_log.entry_by')
                             ->select(\DB::raw("concat(tb_users.first_name , ' ', tb_users.last_name) as entry_by_name"),
                                      "tbl_carga_masiva_log.createdOn",
                                      "tbl_carga_masiva_log.Cargados",
                                      "tbl_carga_masiva_log.Modificados",
                                      "tbl_carga_masiva_log.Rechazados",
                                       \DB::raw("case when tbl_carga_masiva_log.ArchivoURL != '' then concat('<a href=\"".$lstrDirectory."',tbl_carga_masiva_log.ArchivoURL, '\"><i class=\"fa fa-download\"></i> descargar</a>') else ' ' end as ArchivoURL"),
                                      \DB::raw("case when tbl_carga_masiva_log.ArchivoResultadoURL != '' then concat('<a href=\"".$lstrDirectoryResult."',tbl_carga_masiva_log.ArchivoResultadoURL, '\"><i class=\"fa fa-download\"></i> descargar</a>') else ' ' end  as ArchivoResultadoURL"))
                             ->orderBy("tbl_carga_masiva_log.IdCargaMasiva","DESC")
                             ->where("tbl_carga_masiva_log.IdProceso","=","2");
      if ($lintLevelUser!=1){ //Solo el superadmin puede ver lo que ha cargado todos los usuarios
        $lobjLastUpload->where("tbl_carga_masiva_log.entry_by","=",$lintIdUser);
    }
    $lobjLastUpload = $lobjLastUpload->get();
    echo json_encode(array("data"=>$lobjLastUpload));
  }

    function postCopy( Request $request)
    {

        foreach(\DB::select("SHOW COLUMNS FROM tbl_contrato ") as $column)
        {
            if( $column->Field != 'contrato_id')
                $columns[] = $column->Field;
        }
        if(count($request->input('ids')) >=1)
        {

            $toCopy = implode(",",$request->input('ids'));


            $sql = "INSERT INTO tbl_contrato (".implode(",", $columns).") ";
            $sql .= " SELECT ".implode(",", $columns)." FROM tbl_contrato WHERE contrato_id IN (".$toCopy.")";
            \DB::select($sql);
            return response()->json(array(
                'status'=>'success',
                'message'=> \Lang::get('core.note_success')
            ));

        } else {
            return response()->json(array(
                'status'=>'success',
                'message'=> 'Please select row to copy'
            ));
        }


    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    function postSave(Request $request, $id =0)
    {

      $lintIdUser = \Session::get('uid');
      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));

      $larrAccessUser = self::GetAccess($lintIdUser,$lintLevelUser);

      $larrSmartSave = $request->smartsave;
      $ldatFechaActual = date('Y-m-d H:i:s');
      $lintIdContrato = $request->input('contrato_id');

        //remplazamos las fechas por el formato correcta
        if (isset($_POST['cont_fechaInicio'])){
        $_POST['cont_fechaInicio'] = self::FormatoFecha($_POST['cont_fechaInicio']);
        }
        if (isset($_POST['cont_fechaInicioContrato'])){
        $_POST['cont_fechaInicioContrato'] = self::FormatoFecha($_POST['cont_fechaInicioContrato']);
        }
        if (isset($_POST['idservicio'])){
          $lobjContratosservicios = Contratosservicios::find($_POST['idservicio']);
          if ($lobjContratosservicios){
            $_POST['cont_proveedor'] = $lobjContratosservicios->name;
          }else{
            $_POST['cont_proveedor'] = "";
          }
        }
        if (isset($_POST['cont_fechaFin'])) {
            $_POST['cont_fechaFin'] = self::FormatoFecha($_POST['cont_fechaFin']);
        }
        if (isset($_POST['FechaAdjudicacion'])) {
            $_POST['FechaAdjudicacion'] = self::FormatoFecha($_POST['FechaAdjudicacion']);
        }
        if (isset($_POST['impacto'])) {
            $_POST['impacto'] = str_replace(",", ".", str_replace(".", "", $_POST['impacto']));
        }
        if (isset($_POST['complejidad'])) {
            $_POST['complejidad'] = str_replace(",", ".", str_replace(".", "", $_POST['complejidad']));
        }

        if ($larrAccessUser["general"]['access']==1 && $larrSmartSave["tabcontratos"]["change"]==1){
        $rules = $this->validateForm();
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $data = $this->validatePost('tbl_contrato');
            //validamos que el numero de contrato ya no exista
            if (isset($data['cont_numero'])){
              $lobjContrato = \DB::table('tbl_contrato')
                  ->where('tbl_contrato.cont_numero', '=', $data['cont_numero'])
                  ->where('tbl_contrato.contrato_id', '!=', $lintIdContrato)
                  ->get();
              if ($lobjContrato){
                  return response()->json(array(
                      'message'   => "El número de contrato ya se encuentra asignado",
                      'status'    => 'error'
                   ));
              }
            }


            //Remplazamos los valores de formatos:
            if (isset($data['cont_montoTotal']) && $data['cont_montoTotal']) {
              $data['cont_montoTotal'] = str_replace(",",".",str_replace(".","",$data['cont_montoTotal']));
            }
            if (isset($data['cont_garantia_sugerida']) && $data['cont_garantia_sugerida']) {
              $data['cont_garantia_sugerida'] = str_replace(",",".",str_replace(".","",$data['cont_garantia_sugerida']));
            }
            if (isset($data['cont_garantia']) && $data['cont_garantia']) {
              $data['cont_garantia'] = str_replace(",",".",str_replace(".","",$data['cont_garantia']));
            }


            if (!$request->input('contrato_id')){
                $contNuevo = 1;

                //validaciones de fecha
                if ($data['cont_fechaInicio'] > $data['cont_fechaFin']){
                    return response()->json(array(
                        'message'	=> 'No se puede crear registro con fecha de inicio mayor a la fecha fin',
                        'status'	=> 'error'
                    ));
                }
                //fin validaciones de fecha

            }else{
                $contNuevo = 0;

                //Verificamos si se cambia el estatus para almacenar la accion
                $informacion =   \DB::table('tbl_contrato')->select('cont_estado','id_extension')->where('contrato_id', '=', $lintIdContrato)->first();
                if ($data['cont_estado']!=$informacion->cont_estado){
                    if ($data['cont_estado']==1)
                        $Observ ="El Estatus del contrato cambio de Inactivo a Activo";
                    else
                        $Observ ="El Estatus del contrato cambio de Activo a Inactivo";
                    self::Registraractividad($lintIdContrato,10,$Observ);
                }

                if ($data['id_extension']!=$informacion->id_extension){

                    $tipoN =   \DB::table('tbl_contrato_extension')->select('nombre')->where('id_extension', '=', $data['id_extension'])->first();
                    $tipoO =   \DB::table('tbl_contrato_extension')->select('nombre')->where('id_extension', '=', $informacion->id_extension)->first();

                    $Observ ="El tipo de contrato cambio de ".$tipoO->nombre." a ". $tipoN->nombre;

                    self::Registraractividad($lintIdContrato,11,$Observ);
                }

            }

			$data['cont_fechaInicioContrato'] = $_POST['cont_fechaInicioContrato'];
      if (isset($_POST['zona'])) {  $data['zona']= $_POST['zona']; }
      if (isset($_POST['prioridad'])) {  $data['prioridad']= $_POST['prioridad']; }

            //Guardamos los datos del contrato
            $lintIdContrato = $this->model->insertRow($data , $lintIdContrato);
            $lobjMyContrats = New AcreditacionContrato($lintIdContrato);

            if ($contNuevo) {
                self::Registraractividad($lintIdContrato,8);
            }


            //Guardamos los subcontratistas
            $larrSubContratistas = $request->IdSubContratista;
            if (!(empty($larrSubContratistas))) {
                $subC =  \DB::table('tbl_contratos_subcontratistas')->where('contrato_id', '=', $lintIdContrato)->lists('IdSubContratista');
                if (count($subC)>0 ){
                    foreach ($subC as $valor) {
                        if (!(in_array($valor, $larrSubContratistas))){

                            $personasSub =  \DB::table('tbl_contratos_subcontratistas')
                                ->join('tbl_contratos_personas', function ($join) {
                                    $join->on('tbl_contratos_personas.contrato_id', '=', 'tbl_contratos_subcontratistas.contrato_id')
                                        ->on("tbl_contratos_personas.IdContratista", "=", "tbl_contratos_subcontratistas.IdSubContratista");
                                })
                                ->where('tbl_contratos_personas.IdContratista', '=', $valor)
                                ->where('tbl_contratos_personas.contrato_id', '=', $lintIdContrato)
                                ->count();


                            if ($personasSub==0){
                                \DB::table('tbl_contratos_subcontratistas')->where('IdSubContratista', '=', $valor)->where('contrato_id', '=', $lintIdContrato)->delete();

                                //Se elimina el documento "carga de aprobacion de contratista"
                                \DB::table('tbl_documentos')
                                    ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento","=", "tbl_tipos_documentos.IdTipoDocumento")
                                    ->where('tbl_documentos.Entidad','=',9)
                                    ->where('tbl_documentos.IdEntidad','=',$valor)
                                    ->where("tbl_documentos.contrato_id","=",$lintIdContrato)
                                    ->where('tbl_tipos_documentos.IdProceso','=',89)
                                    ->delete();

                                $empresa =   \DB::table('tbl_contratistas')->where('IdContratista', '=', $valor)->pluck('RazonSocial');

                                $Observ ="La empresa ".$empresa." ha sido Retirada ";
                                self::Registraractividad($lintIdContrato,19,$Observ);
                            }
                            else{
                                return response()->json(array(
                                    'message'	=> 'No se puede desvincular a una empresa con personas asociadas a este contrato',
                                    'status'	=> 'error'
                                ));
                            }

                        }
                    }
                }

                foreach ($larrSubContratistas as $larrSubContratista) {
                    if (!(in_array($larrSubContratista, $subC))) {
                        $lintResultSubContratista = \DB::table('tbl_contratos_subcontratistas')->insertGetId(
                            ['contrato_id' => $lintIdContrato, 'IdSubContratista' => $larrSubContratista]);

                        $empresa = \DB::table('tbl_contratistas')->where('IdContratista', '=', $larrSubContratista)->pluck('RazonSocial');

                        $Observ = "La empresa " . $empresa[0]. " ha sido Registrada ";
                        self::Registraractividad($lintIdContrato, 18, $Observ);
                    }
                }
            }else{
                $subC =  \DB::table('tbl_contratos_subcontratistas')->where('contrato_id', '=', $lintIdContrato)->lists('IdSubContratista');
                if (count($subC)>0 ){
                    foreach ($subC as $valor) {

                            $personasSub =  \DB::table('tbl_contratos_subcontratistas')
                                ->join('tbl_contratos_personas', function ($join) {
                                    $join->on('tbl_contratos_personas.contrato_id', '=', 'tbl_contratos_subcontratistas.contrato_id')
                                        ->on("tbl_contratos_personas.IdContratista", "=", "tbl_contratos_subcontratistas.IdSubContratista");
                                })
                                ->where('tbl_contratos_personas.IdContratista', '=', $valor)
                                ->where('tbl_contratos_personas.contrato_id', '=', $lintIdContrato)
                                ->count();

                            if ($personasSub==0){
                                \DB::table('tbl_contratos_subcontratistas')->where('IdSubContratista', '=', $valor)->where('contrato_id', '=', $lintIdContrato)->delete();
                                //Se elimina el documento "carga de aprobacion de contratista"
                                \DB::table('tbl_documentos')
                                    ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento","=", "tbl_tipos_documentos.IdTipoDocumento")
                                    ->where('tbl_documentos.Entidad','=',9)
                                    ->where('tbl_documentos.IdEntidad','=',$valor)
                                    ->where("tbl_documentos.contrato_id","=",$lintIdContrato)
                                    ->where('tbl_tipos_documentos.IdProceso','=',89)
                                    ->delete();

                                $empresa =   \DB::table('tbl_contratistas')->where('IdContratista', '=', $valor)->pluck('RazonSocial');

                                $Observ ="La empresa ".$empresa[0]." ha sido Retirada ";
                                self::Registraractividad($lintIdContrato,19,$Observ);
                            }
                            else{
                                return response()->json(array(
                                    'message'	=> 'No se puede desvincular a una empresa con personas asociadas a este contrato',
                                    'status'	=> 'error'
                                ));
                            }

                    }
                }

            }

            //Guardamos los Centros de costo
            $larrCentroCostos = $request->claseCosto;
            if (!(empty($larrCentroCostos))) {
                \DB::table('tbl_contratos_centrocosto')->where('contrato_id', '=', $lintIdContrato)->delete();
                foreach ($larrCentroCostos as $larrCentroCosto) {
                    $lintResultCentrocosto = \DB::table('tbl_contratos_centrocosto')->insertGetId(
                        ['contrato_id' => $lintIdContrato, 'centrocosto_id' => $larrCentroCosto]);
                }
            }else{
                \DB::table('tbl_contratos_centrocosto')->where('contrato_id', '=', $lintIdContrato)->delete();
            }

        } else {
            $message = $this->validateListError(  $validator->getMessageBag()->toArray() );
            return response()->json(array(
                'message'   => $message,
                'status'    => 'error'
            ));
        }
      }else{
        //No vendrán los datos  por lo que tendremos que complementarlos
        $fetchMode = \DB::getFetchMode();
        \DB::setFetchMode(\PDO::FETCH_ASSOC);
        $data = \DB::table("tbl_contrato")
        ->where("tbl_contrato.contrato_id","=",$lintIdContrato)
        ->first();
        \DB::setFetchMode($fetchMode);
      }

      if ($lintLevelUser==6 && $lintIdUser!=$data['entry_by_access']){
        $lintEntryByAccess = $lintIdUser;
      }else{
        $lintEntryByAccess = $data['entry_by_access'];
      }

      if ($larrAccessUser["centros"]['access']==1 && $larrSmartSave["tabcentros"]["change"]==1){

          $larrCentrosDelete = json_decode($request->centrodelete);

          if ($larrCentrosDelete) {
              foreach ($larrCentrosDelete as $lintIdCentro) {
                  $centrosP = \DB::table('tbl_acceso_areas')
                      ->join("tbl_accesos", "tbl_acceso_areas.IdAcceso", "=", "tbl_accesos.IdAcceso")
                      ->where("tbl_accesos.IdTipoAcceso", "=", 1)
                      ->where("tbl_accesos.contrato_id", "=", $lintIdContrato)
                      ->where("tbl_acceso_areas.IdCentro", "=", $lintIdCentro)
                      ->count();


                  $centrosAc = \DB::table('tbl_acceso_activos_areas')
                      ->join("tbl_accesos_activos", "tbl_acceso_activos_areas.IdAccesoActivo", "=", "tbl_accesos_activos.IdAccesoActivo")
                      ->where("tbl_accesos_activos.IdTipoAcceso", "=", 1)
                      ->where("tbl_accesos_activos.contrato_id", "=", $lintIdContrato)
                      ->where("tbl_acceso_activos_areas.IdCentro", "=", $lintIdCentro)
                      ->count();

                  if ($centrosP > 0 && $centrosAc > 0) {
                       \DB::table('tbl_acceso_areas')
                           ->join("tbl_accesos", "tbl_acceso_areas.IdAcceso", "=", "tbl_accesos.IdAcceso")
                           ->where("tbl_accesos.IdTipoAcceso", "=", 1)
                          ->where("tbl_accesos.contrato_id", "=", $lintIdContrato)
                          ->where("tbl_acceso_areas.IdCentro", "=", $lintIdCentro)
                          ->delete();

                       \DB::table('tbl_acceso_activos_areas')
                           ->join("tbl_accesos_activos", "tbl_acceso_activos_areas.IdAccesoActivo", "=", "tbl_accesos_activos.IdAccesoActivo")
                           ->where("tbl_accesos_activos.IdTipoAcceso", "=", 1)
                          ->where("tbl_accesos_activos.contrato_id", "=", $lintIdContrato)
                          ->where("tbl_acceso_activos_areas.IdCentro", "=", $lintIdCentro)
                          ->delete();
                  }
                  else if ($centrosP > 0){
                      \DB::table('tbl_acceso_areas')
                          ->join("tbl_accesos", "tbl_acceso_areas.IdAcceso", "=", "tbl_accesos.IdAcceso")
                          ->where("tbl_accesos.IdTipoAcceso", "=", 1)
                          ->where("tbl_accesos.contrato_id", "=", $lintIdContrato)
                          ->where("tbl_acceso_areas.IdCentro", "=", $lintIdCentro)
                          ->delete();
                  }
                  else if ($centrosAc > 0){
                      \DB::table('tbl_acceso_activos_areas')
                          ->join("tbl_accesos_activos", "tbl_acceso_activos_areas.IdAccesoActivo", "=", "tbl_accesos_activos.IdAccesoActivo")
                          ->where("tbl_accesos_activos.IdTipoAcceso", "=", 1)
                          ->where("tbl_accesos_activos.contrato_id", "=", $lintIdContrato)
                          ->where("tbl_acceso_activos_areas.IdCentro", "=", $lintIdCentro)
                          ->delete();
                  }

                  \DB::table('tbl_contratos_centros')
                      ->where("tbl_contratos_centros.contrato_id", "=", $lintIdContrato)
                      ->where("tbl_contratos_centros.IdCentro", "=", $lintIdCentro)
                      ->delete();


              }
          }

          $larrCentrosUpdate = json_decode($request->centroupdate);
          if ($larrCentrosUpdate){
              foreach ($larrCentrosUpdate as $lintIdCentro => $lintIdTipoCentro){
                  \DB::table('tbl_contratos_centros')
                      ->where("tbl_contratos_centros.contrato_id","=",$lintIdContrato)
                      ->where("tbl_contratos_centros.IdCentro","=",$lintIdCentro)
                      ->update(array("IdTipoCentro"=>$lintIdTipoCentro));
              }
          }

        //Guardamos los centros operaciones
        $larrIdCentros = $request->bulktwo_IdCentro;
        $larrIdTipoCentros = $request->bulktwo_IdTipoCentro;
        if ($larrIdCentros) {
            foreach ($larrIdCentros as $lintKeyIdCentro => $lintIdCentro) {
                if ($lintIdCentro) {
                    \DB::table('tbl_contratos_centros')->insert(array("IdCentro" => $lintIdCentro,
                        "IdContratista" => $data['IdContratista'],
                        "contrato_id" => $lintIdContrato,
                        "IdTipoCentro" => $larrIdTipoCentros[$lintKeyIdCentro],
                        "entry_by" => $lintIdUser,
                        "createdOn" => $ldatFechaActual,
                        "entry_by_access" => $lintEntryByAccess));
                }
            }
        }

      }

      if ($larrAccessUser["requisitos"]['access']==1 && $larrSmartSave["tabrequisitos"]["change"]==1){
        //Guardamos los requisitos
        $larrIdTipoDocumento = $request->bulk_IdTipoDocumento;
        $larrEntidad=$request->bulk_Entidad;
        $larrIdRequisito=$request->bulk_IdRequisito;

        //ahora revisamos los requisitos que son necesarios para el servicio
        $lobjMyRequirements = new MyRequirements($lintIdContrato);

        $lobjRequirements = $lobjMyRequirements::getRequirements(1);

        //se filtran los requerimientos y se cargan los documentos
        foreach ($lobjRequirements as $larrRequirements) {
          $lobjRequirements = $lobjMyRequirements::Load($larrRequirements->IdRequisito, $lintIdContrato);
        }

        // PARCHE DANIEL  comente estas lineas y corrigió el problema de los grupos especificos y los documentos que se levantaban
        // foreach ($larrIdTipoDocumento as $lintKeyIdTipoDocumento => $lintIdTipoDocumento) {
        //     if ($lintIdTipoDocumento){
        //         //Configuramos los valores unicos
        //         $lintEntidad = $larrEntidad[$lintKeyIdTipoDocumento];
        //         if ($lintEntidad==1){
        //           $lintIdEntidad = $request->IdContratista;
        //         }else{
        //           $lintIdEntidad = $lintIdContrato;
        //         }

        //         //Creamos el documento
        //         $lobjDocumentos = new MyDocuments();
        //         $larrResultRequisito = $lobjDocumentos::save($lintIdTipoDocumento,
        //                                                      $lintEntidad,
        //                                                      $lintIdEntidad,
        //                                                      "",
        //                                                      $pintIdContratista= $request->IdContratista,
        //                                                      $pintContratoId=$lintIdContrato,
        //                                                      $pdatFechaEmision = $data['cont_fechaInicio']);
        //     }
        // }

        $lobjMyContrats::Accreditation();

      }

      if ($larrAccessUser["personas"]['access']==1 && $larrSmartSave["tabpersonas"]["change"]==1){
        $larrRequestPersonas = $request->bulk_IdPersona;
        $larrRequestRoles = $request->bulk_IdRol;
        $larrFechaInicioFaena = $request->bulk_FechaInicioFaena;
        foreach ($larrRequestPersonas as $lintKeyIdPersona => $lintIdPersona) {
          if ($lintIdPersona){
            if ($larrFechaInicioFaena[$lintKeyIdPersona]){
                $ldatFechaInicioFaena = \MyFormats::FormatoFecha($larrFechaInicioFaena[$lintKeyIdPersona]);
            }else{
                $ldatFechaInicioFaena = "";
            }
            $lintIdRol = $larrRequestRoles[$lintKeyIdPersona];
            $lobjAcreditacion = new Acreditacion($lintIdPersona);
            $larrResultadoPersonas = $lobjAcreditacion::AssignContract($lintIdContrato,
                                                                       $lintIdPersona,
                                                                       $lintIdRol,
                                                                       0,
                                                                       $ldatFechaInicioFaena);
          }
        }
      }

      if ($larrAccessUser["personascentros"]['access']==1){

        $larrAccessAdd = json_decode($request->accessadd);
        $larrAccessDelete = json_decode($request->accessdelete);

        if ($larrAccessAdd){
          foreach ($larrAccessAdd as $lintIdPersona => $larrAccesoAreas) {

            $lobjAcceso = \DB::table('tbl_accesos')
                                ->where('IdPersona', '=', $lintIdPersona)
                                ->where('IdTipoAcceso', '=', 1)
                                ->first();
            if (!$lobjAcceso){
              $larrDataAccesos = array('IdTipoAcceso' => 1,
                                       'IdPersona' => $lintIdPersona,
                                       'contrato_id' => $data['contrato_id'],
                                       'FechaInicio' => $data['cont_fechaInicio'],
                                       'FechaFinal' => $data['cont_fechaFin'],
                                       'IdEstatus' => 2,
                                       'IdEstatusUsuario' => 2,
                                       'createdOn' => $ldatFechaActual);
              $lintIdAcceso = \DB::table('tbl_accesos')->insertGetId($larrDataAccesos);
            }else{
              $lintIdAcceso = $lobjAcceso->IdAcceso;
              $larrDataAccesos = array("contrato_id" => $data['contrato_id'],
                                       "Fechainicio" => $data['cont_fechaInicio'],
                                       "FechaFinal" => $data['cont_fechaFin']);
              \DB::table('tbl_accesos')
                   ->where('IdAcceso',$lintIdAcceso)
                   ->update($larrDataAccesos);

            }
            foreach ($larrAccesoAreas as $lintIdAreaTrabajo ) {
                $larrDataInsertArea = array("IdAreaTrabajo"=>$lintIdAreaTrabajo,
                                            "IdAcceso"=>$lintIdAcceso,
                                            "entry_by"=>$lintIdUser);
                  \DB::table('tbl_acceso_areas')->insert($larrDataInsertArea);
            }
          }
        }

        if ($larrAccessDelete){
          foreach ($larrAccessDelete as $lintIdPersona => $larrAccesoAreas) {
              \DB::table('tbl_acceso_areas')
              ->whereExists(function ($query) use ($lintIdPersona) {
                $query->select(\DB::raw(1))
                      ->from('tbl_accesos')
                      ->whereRaw('tbl_accesos.idpersona = '.$lintIdPersona)
                      ->whereRaw('tbl_accesos.IdAcceso = tbl_acceso_areas.IdAcceso')
                      ->whereRaw('tbl_accesos.IdTipoAcceso = 1');
              })
              ->wherein("tbl_acceso_areas.IdAreaTrabajo",$larrAccesoAreas)
              ->delete();
          }
        }


      if ($larrAccessUser["activos"]['access']==1){
        $activoac = $request->activoac;
        if ($activoac){
              foreach ($activoac as $lidactivo => $laccess) {
                $data['IdPersona'] = $lidactivo;
                $lobjActivo = \DB::table('tbl_accesos_activos')
                                    ->where('IdActivoData', '=', $lidactivo)
                                    ->where('IdTipoAcceso', '=', 1)
                                    ->get();

                if (!$lobjActivo){
                    $IdAccesoActivo = \DB::table('tbl_accesos_activos')->insertGetId(
                     ['IdTipoAcceso' => 1, 'IdActivoData' => $lidactivo, 'contrato_id' => $data['contrato_id'], 'FechaInicio' => $data['cont_fechaInicio'], 'FechaFinal' => $data['cont_fechaFin'], 'IdEstatus' => 1, 'createdOn' => date("Y-m-d H:i:s"), 'entry_by' => '', 'updatedOn' => 'NULL' ]
                    );
                }else{
                  $IdAccesoActivo = $lobjActivo[0]->{'IdAccesoActivo'};
                  //Actualizamos la fecha del acceso:
                  $larrDataAccesos = array("Fechainicio" => $data['cont_fechaInicio'],
                                           "FechaFinal" => $data['cont_fechaFin'],
                                           "contrato_id" => $data['contrato_id']);
                  \DB::table('tbl_accesos_activos')
                       ->where('IdAccesoActivo',$IdAccesoActivo)
                       ->update($larrDataAccesos);

                  //Eliminamos los accesos
                  $lobjAreasDeTrabajo =  \DB::table('tbl_acceso_activos_areas')
                                              ->select('IdAreaTrabajo')
                                              ->where('IdAccesoActivo', '=', $IdAccesoActivo)
                                              ->get();
                  if ($lobjAreasDeTrabajo){
                    foreach ($lobjAreasDeTrabajo as $larrRow) {
                      if ( !(in_array($larrRow->{'IdAreaTrabajo'}, $laccess)) ){
                        \DB::table('tbl_acceso_activos_areas')
                             ->where('IdAreaTrabajo', '=', $larrRow->{'IdAreaTrabajo'})
                             ->where('IdAccesoActivo', '=', $IdAccesoActivo)
                             ->delete();

                      }
                    }
                  }
                  //Eliminamos los accesos
                }
                //

                foreach ($laccess as $areap ) {
                    if ($areap){
                      $consulta="INSERT INTO tbl_acceso_activos_areas (IdAccesoActivoArea, IdAreaTrabajo, IdAccesoActivo,IdEstatus,IdCentro)
                                           SELECT NULL as IdAccesoActivoArea,
                                                  '$areap' as IdAreaTrabajo,
                                                  '$IdAccesoActivo' as IdAccesoActivo,
                                                     '1' as IdEstatus,
                                                  NULL as IdCentro
                                           FROM dual
                                           WHERE NOT EXISTS ( SELECT IdAccesoActivo
                                                              FROM tbl_acceso_activos_areas
                                                              WHERE IdAreaTrabajo = '$areap'
                                                              AND   IdAccesoActivo = '$IdAccesoActivo')";
                      \DB::insert($consulta);
                    }
                }
              }
            }
          }

    }


    $datageneral = array("idcontrato"=>$lintIdContrato);
    $lobjAccesos = \DB::table('tbl_contratos_personas')
        ->select('tbl_contratos_personas.IdPersona','tbl_acceso_areas.IdAreaTrabajo')
        ->leftjoin("tbl_accesos","tbl_accesos.IdPersona","=", \DB::raw("tbl_contratos_personas.IdPersona AND tbl_accesos.IdTipoAcceso = 1"))
        ->leftjoin('tbl_acceso_areas', 'tbl_accesos.IdAcceso', '=', 'tbl_acceso_areas.IdAcceso')
        ->where('tbl_contratos_personas.contrato_id',$lintIdContrato)
        ->get();
    $larrResultadoAccesos = array();
    foreach ($lobjAccesos as $value) {
        $larrResultadoAccesos[$value->IdPersona][] = (string) $value->IdAreaTrabajo;
    }
    $datageneral["accesosactualizados"] = json_encode($larrResultadoAccesos);

    if ($larrSmartSave["tabcentros"]["change"]){
        $datageneral['selectCentros'] = \DB::table("tbl_contratos_centros")
        ->select(\DB::raw('tbl_contratos_centros.IdCentro as value'), \DB::raw('tbl_centro.Descripcion as display'))
        ->distinct()
        ->join("tbl_centro","tbl_centro.IdCentro","=","tbl_contratos_centros.IdCentro")
        ->where("tbl_contratos_centros.contrato_id","=",$lintIdContrato)
        ->orderBy("tbl_centro.Descripcion","ASC")
        ->get();
    }

    //$itemizado_id = \DB::table('tbl_contrato_itemizado')->insertGetId(['contrato_id'=>$lintIdContrato]);


    return response()->json(array(
        'status'=>'success',
        'message'=> \Lang::get('core.note_success'),
        'result'=>$datageneral
        //'itemizado_id'=>$itemizado_id
        ));


  }

  public function postAccesos( Request $request){

      $lintIdCentro = $request->idcentro;

	  $lobjAreasdeTrabajo = \DB::table('tbl_area_de_trabajo')
      ->where('IdCentro',$lintIdCentro)
      ->where(function($query) {
        $query->where('IdTipoAcceso','=',1)
              ->orwhere('IdTipoAcceso','=',3);
      })
      ->get();

      $lstrTable = '';
      $lstrTable .= '<table class="table table-striped " id="tablaPersonaCentro">';
      $lstrTable .= '    <thead>';
      $lstrTable .= '        <tr>';
      $lstrTable .= '            <th> </th>';
      $lstrTable .= '            <th> Persona </th>';
      $lstrTable .= '            <th> Rol </th>';
      foreach ($lobjAreasdeTrabajo as $larrAreasdetrabajo) {
          $lstrTable .= '            <th> '.$larrAreasdetrabajo->Descripcion.' <input type="checkbox" class="ids icheckbox_square-red" onchange="selecttodoarea(this.id, '.$larrAreasdetrabajo->IdAreaTrabajo.');" id="checkareaall'.$larrAreasdetrabajo->IdAreaTrabajo.'" /> </th>';
      }
      $lstrTable .= '        </tr>';
      $lstrTable .= '    </thead>';
      $lstrTable .= '    <tbody>';
      $lstrTable .= '    </tbody>';
      $lstrTable .= '</table>';

      return $lstrTable;


  }

  public function getPersonasaccesos(Request $request) {

      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = \Session::get('uid');
      $lintIdCentro = $request->idcentro;
      $lintIdContrato = $request->idcontrato;
      $larrResult = array();
      $larrResultTemp = array();
      $larrResultTempAreas = array();

      $lobjPersonas = \DB::table('tbl_contratos_subcontratistas')
      ->select(\DB::raw('count(*) as IdContratista'))
      ->where('tbl_contratos_subcontratistas.contrato_id','=',$lintIdContrato)
      ->first();
      $lobjPersonasSubcontrato = array();
      $larrRelationshipUser = array("relationship"=>"00","IdContratista"=>"");
      if ($lobjPersonas->IdContratista > 0) { //El contrato es compartido
          if ($lintLevelUser == 6){
            $larrRelationshipUser = \MySourcing::RelationshiplUser($lintIdContrato, $lintIdUser);
          }
      }

      $lobjContratosPersonas = \DB::table("tbl_contratos_personas")
      ->select("tbl_personas.IdPersona",
               \DB::raw("concat(tbl_personas.rut,' ', tbl_personas.Nombres, ' ', tbl_personas.Apellidos) as Persona"),
               \DB::raw("tbl_roles.Descripción as Rol"),
               \DB::raw("group_concat(tbl_acceso_areas.IdAreaTrabajo) as Acceso")
               )
      ->join('tbl_personas','tbl_personas.Idpersona', '=', 'tbl_contratos_personas.IdPersona')
      ->leftjoin('tbl_accesos','tbl_personas.Idpersona', '=', 'tbl_accesos.IdPersona')
      ->leftjoin('tbl_acceso_areas', 'tbl_acceso_areas.IdAcceso', '=', \DB::raw('tbl_accesos.IdAcceso AND tbl_acceso_areas.IdCentro = '.$lintIdCentro.' AND tbl_accesos.IdTipoAcceso = 1 '))
      ->join('tbl_roles','tbl_roles.IdRol', '=', 'tbl_contratos_personas.IdRol')
      ->where('tbl_contratos_personas.contrato_id','=',$lintIdContrato);
      if ($lobjPersonas->IdContratista > 0 && $larrRelationshipUser['relationship']!="00"){
        $lobjContratosPersonas = $lobjContratosPersonas->where('tbl_contratos_personas.IdContratista','=',$larrRelationshipUser['IdContratista']);
      }

      $lobjContratosPersonas = $lobjContratosPersonas->groupBy("tbl_personas.IdPersona","tbl_personas.rut", "tbl_personas.Nombres", "tbl_personas.Apellidos", "tbl_roles.Descripción")
      ->get();

      //aqui


      $lobjAreasdeTrabajo = \DB::table('tbl_area_de_trabajo')->where('IdCentro',$lintIdCentro)->where('IdTipoAcceso','<>',2)->get();
      $lstrIdAreaTrabajo = "";


      foreach ($lobjContratosPersonas as $larrContratosPersonas) {
        $larrResultTemp = array();
        $larrResultTemp[0] = '<input type="checkbox" class="ids checkpeopleall icheckbox_square-red" onchange="selecttodopersona(this.id, '.$larrContratosPersonas->IdPersona.');" id="checkpeopleall'.$larrContratosPersonas->IdPersona.'"  name="IdPersona[]" /> ';
        $larrResultTemp[1] = $larrContratosPersonas->Persona;
        $larrResultTemp[2] = $larrContratosPersonas->Rol;
        $larrAreasAcceso = explode(",",$larrContratosPersonas->Acceso);
        foreach ($lobjAreasdeTrabajo as $larrAreasdeTrabajo) {
          $lstrIdAreaTrabajo = $larrAreasdeTrabajo->IdAreaTrabajo;
          $lstrCheck = "";
          if (in_array($lstrIdAreaTrabajo, $larrAreasAcceso)){
            $lstrCheck = ' checked="checked" ';
          }
          $larrResultTempAreas[$lstrIdAreaTrabajo] = '<input type="checkbox" class="ids icheckbox_square-red checkareaall'.$lstrIdAreaTrabajo.' checkpeopleall'.$larrContratosPersonas->IdPersona.'" onchange="selectarea(this.id);" id="checkpeopleall_'.$larrContratosPersonas->IdPersona.'_'.$lstrIdAreaTrabajo.'" name="IdCentroT" id="'.$lstrIdAreaTrabajo.'" value="'.$lstrIdAreaTrabajo.'" '.$lstrCheck.' />';
        }
        $larrResultTemp = array_merge($larrResultTemp, $larrResultTempAreas);
        $larrResult[] = $larrResultTemp;
      }

      echo json_encode(array("data"=>$larrResult));

  }

  public function postViewpersonascentros($pintIdContrato, $parrPersonasContratos = "", $parrCentrosContrato = ""){

    $larrAccessUser = self::GetAccess();

    if (!$parrPersonasContratos){
      $larrPersonasContrato = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$pintIdContrato );
    }else{
      $larrPersonasContrato = $parrPersonasContratos;
    }

    if (!$parrCentrosContrato){
      $larrContratosCentros = array( "title" => "Centros", "master" => "contratos", "master_key" => "contrato_id", "module" => "contratoscentros", "table" => "tbl_contratos_centros", "key" => "contrato_id" );
      $larrCentrosContrato = $this->detailview($this->modelviewtwo ,  $larrContratosCentros ,$pintIdContrato );
    }else{
      $larrCentrosContrato = $parrCentrosContrato;
    }

    //Construimos las personas centro
    $datapersonascentro["subformpersonascentro"] = \DB::table('tbl_accesos')
    ->select('tbl_accesos.IdPersona','tbl_acceso_areas.IdAreaTrabajo')
    ->join('tbl_acceso_areas', 'tbl_accesos.IdAcceso', '=', 'tbl_acceso_areas.IdAcceso')
    ->where('tbl_accesos.contrato_id',$pintIdContrato)->get();
    $lintIdPersona = 0;
    $lstrPerconasCentro = "";
    foreach ($datapersonascentro["subformpersonascentro"] as $value) {
      if ($lintIdPersona!=$value->IdPersona){
        if ($lintIdPersona!=0){
          $lstrPerconasCentro .= ' ], ';
        }
        $lintIdPersona = $value->IdPersona;
        $lstrPerconasCentro .= '"'.$value->IdPersona.'":';
        $lstrPerconasCentro .= ' [ 0, '.$value->IdAreaTrabajo.', ';
      }else{
          $lstrPerconasCentro .= ' '.$value->IdAreaTrabajo.', ';
      }
    }
    if ($lintIdPersona!=0){
      $lstrPerconasCentro .= ' ] ';
    }
    $datapersonascentro["subformpersonascentroarray"] = $lstrPerconasCentro;
    $datapersonascentro["subformpersonascontrato"] = $larrPersonasContrato;
    $datapersonascentro['subformcentroscontrato'] = $larrCentrosContrato;
    $datapersonascentro['contrato_id'] = $pintIdContrato;
    echo view('contratos.form.personascentros.'.$larrAccessUser['personascentros']['view'],$datapersonascentro)->render();

  }

  public function postViewcentros($pintIdContrato, $parrPersonasContratos = "", $parrCentrosContrato = ""){

    $larrAccessUser = self::GetAccess();

    $lobjCentrosOperacionales = \DB::table('tbl_centro')
    ->select(\DB::raw('tbl_centro.IdCentro as value'), \DB::raw('tbl_centro.Descripcion as display'))
    ->orderBy("tbl_centro.Descripcion","ASC");
    if ($pintIdContrato){
      $lobjCentrosOperacionales = $lobjCentrosOperacionales->whereNotExists(function ($query) use ($pintIdContrato){
        $query->select(\DB::raw(1))
            ->from('tbl_contratos_centros')
            ->whereRaw('tbl_contratos_centros.IdCentro = tbl_centro.IdCentro')
            ->whereRaw('tbl_contratos_centros.contrato_id = '.$pintIdContrato);
      });
    }
      $datacentros['selectIdTipoCentro'] = \DB::table('tbl_centros_tipos')
          ->select(\DB::raw('tbl_centros_tipos.id as value'),
              \DB::raw('tbl_centros_tipos.nombre as display'))->get();
    $datacentros['selectCentrosOperacionales']  = $lobjCentrosOperacionales->get();

    $larrContratosCentros = array( "title" => "Centros", "master" => "contratos", "master_key" => "contrato_id", "module" => "contratoscentros", "table" => "tbl_contratos_centros", "key" => "contrato_id" );
    $larrCentrosContrato = $this->detailview($this->modelviewtwo ,  $larrContratosCentros ,$pintIdContrato );
    $datacentros['subformcentros'] = $larrCentrosContrato;
    echo  view('contratos.form.centros.'.$larrAccessUser['centros']['view'],$datacentros)->render();

  }

  public function postViewpersonas($pintIdContrato, $parrPersonasContratos = "", $parrCentrosContrato = ""){

    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');
    $larrAccessUser = self::GetAccess();

    $lobjPersonas = \DB::table('tbl_contratos_subcontratistas')
    ->select(\DB::raw('count(*) as IdContratista'))
    ->where('tbl_contratos_subcontratistas.contrato_id','=',$pintIdContrato)
    ->first();

    $lobjMyContrats = new MyContracts($pintIdContrato);
    $lobjContrato = $lobjMyContrats::getDatos();

    $datapersonas['IdContratista'] = $lobjContrato->IdContratista;
    $lintIdServicio = $lobjContrato->idservicio;


    $datapersonas['selectIdContrato'] = $lobjMyContrats::getPosiblesCambios();
    $datapersonas['subcontratos'] = $lobjPersonas->IdContratista;

    $datapersonas['selectIdSubContratista'] = \DB::table('tbl_subcontratistas')
        ->join("tbl_contratistas","tbl_subcontratistas.SubContratista","=","tbl_contratistas.IdContratista")
        ->leftjoin("tbl_contratos_subcontratistas","tbl_contratos_subcontratistas.IdSubContratista", "=", \DB::raw("tbl_subcontratistas.SubContratista AND tbl_contratos_subcontratistas.contrato_id = ".$pintIdContrato))
        ->select(\DB::raw('tbl_subcontratistas.SubContratista as value'),
                 \DB::raw('concat(tbl_contratistas.RUT,\' \',tbl_contratistas.RazonSocial) as display'),
                 \DB::raw(" case when tbl_contratos_subcontratistas.contrato_id is not null then 'selected' else '' end as isselect"))
        ->where('tbl_subcontratistas.IdContratista', '=', $datapersonas['IdContratista'])
        ->get();

    $datapersonas['selectIdContratista'] = \DB::table('tbl_contratistas')
        ->select(\DB::raw('tbl_contratistas.IdContratista as value'),
                 \DB::raw('concat(tbl_contratistas.RUT,\' \',tbl_contratistas.RazonSocial) as display'))
        ->where('IdEstatus','=',1)
        ->get();

    $larrRelationshipUser = array("relationship"=>"00","IdContratista"=>"");
    if ($lobjPersonas->IdContratista > 0) { //El contrato es compartido
      if ($lintLevelUser == 6){

        //Verificamos si el usuario es el dueño o uno de sus subcontratistas
        $larrRelationshipUser = \MySourcing::RelationshiplUser($pintIdContrato, $lintIdUser);

      }
    }

    //Construimos las personas
    $datapersonas['selectIdPersona'] = \DB::table("tbl_personas")
    ->select(\DB::raw('tbl_personas.IdPersona as value'),
             \DB::raw('concat(tbl_personas.RUT,\' \',tbl_personas.Nombres,\' \', tbl_personas.Apellidos) as display'))
    ->whereNotExists(function ($query) {
        $query->select(\DB::raw(1))
              ->from('tbl_contratos_personas')
              ->whereRaw('tbl_contratos_personas.IdPersona = tbl_personas.IdPersona');
    })
    ->orderBy("tbl_personas.Rut","ASC");
    if ($lintLevelUser==6) {
        $datapersonas['selectIdPersona']->where("tbl_personas.entry_by_access","=",$lintIdUser);
    }
    $datapersonas['anotaciones']=  \DB::table('tbl_concepto_anotacion')->get();
    $datapersonas['selectIdPersona'] = $datapersonas['selectIdPersona']->get();

    $datapersonas['selectIdRol'] = Roles::join("tbl_roles_servicios","tbl_roles_servicios.idrol","=","tbl_roles.idrol")
        ->where('tbl_roles_servicios.idservicio',$lintIdServicio)
        ->select("tbl_roles.IdRol as value",
                 "tbl_roles.Descripción as display"
                 )
        ->orderBy("display","asc")
        ->get();

    $datapersonas['lintIdUser'] = $lintIdUser;
    $datapersonas['lintLevelUser'] = $lintLevelUser;
    $datapersonas['relationshipuser'] = $larrRelationshipUser;
    $larrPersonasContrato = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$pintIdContrato );

    $lobjPersonasSubcontrato =  \DB::table("tbl_contratos_personas")
    ->select('tbl_contratistas.RazonSocial', \DB::raw("concat(tbl_personas.RUT, ' ', tbl_personas.Nombres, ' ', tbl_personas.Apellidos) as Persona"), \DB::raw('tbl_roles.Descripción as Rol'))
    ->join("tbl_personas","tbl_personas.IdPersona","=","tbl_contratos_personas.IdPersona")
    ->join("tbl_roles","tbl_contratos_personas.IdRol","=","tbl_roles.IdRol")
    ->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_contratos_personas.IdContratista")
    ->where("tbl_contratos_personas.contrato_id","=",$pintIdContrato)
    ->where("tbl_contratos_personas.IdContratista","!=",$larrRelationshipUser['IdContratista'])
    ->get();

      $datapersonas['cartaA'] = 0;
      $SubCont = \MyPeoples::EsSubcontratista($pintIdContrato);
      if ( $SubCont>0){

          //Verificamos que si es un subcontratista la carta de aprobacion este aprobada
          $lobjCartaAprobacion = \DB::table("tbl_documentos")
              ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento", "=", "tbl_tipos_documentos.IdTipoDocumento")
              ->where("tbl_documentos.Entidad","=","9")
              ->where("tbl_documentos.IdEntidad","=",$SubCont)
              ->where("tbl_documentos.contrato_id","=",$pintIdContrato)
              ->where("tbl_tipos_documentos.IdProceso","=",89)
              ->where("tbl_documentos.IdEstatus","!=",5)
              ->pluck("tbl_documentos.IdDocumento");

          if ($lobjCartaAprobacion){
              $datapersonas['cartaA'] = $lobjCartaAprobacion;
          }
      }

    $datapersonas['subformpersonas'] = $larrPersonasContrato;
    $datapersonas['personassubcontrato'] = $lobjPersonasSubcontrato;
    $datapersonas['relationshipuser'] = $larrRelationshipUser;

    echo view('contratos.form.personas.'.$larrAccessUser['personas']['view'],$datapersonas)->render();

  }
    public function postChangenpeople( Request $request) {

      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = \Session::get('uid');

      if($this->access['is_edit'] ==0) {
          return response()->json(array(
              'status'=>'error',
              'message'=> \Lang::get('core.note_restric')
          ));
          die;

      }

      $lintIdPersona = $request->input('IdPersona');
      $lintIdContrato = $request->input('contrato');
      $lintIdContratista = $request->input('contratista');
      $lintIdRol = $request->input('rol');
      $fecha = date('Y/m/d');

      $larrResult = \MyPeoples::ChangeContract($lintIdContrato, $lintIdContrato, $lintIdPersona, $lintIdRol, 0, '',$lintIdContratista);

      return response()->json(array(
                'status'=>'success',
                'message'=> \Lang::get('core.note_success')
            ));

    }

    public function postBorrar( Request $request)
    {

      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = \Session::get('uid');

        if($this->access['is_edit'] ==0) {
            return response()->json(array(
                'status'=>'error',
                'message'=> \Lang::get('core.note_restric')
            ));
            die;

        }

        if(count($request->input('IdPersona')) >=1)
        {

            $Id = $request->input('IdPersona');
            $IdCont = $request->input('contrato');
            $IdUser = \Session::get('uid');;
            $anot = $request->input('anotacion');

            $ldatFechaEfectiva = $request->input('fechaefectiva');
            $ldatFechaEfectiva = self::FormatoFecha($ldatFechaEfectiva);

            $razon = $request->input('razon');
            $fecha = date('Y/m/d');

            if ($razon==1){

                //Insertamos un tipo de documento de tipo Anexo de contrato
                $IdDoc = \DB::table('tbl_documentos')->insertGetId(['IdTipoDocumento' => 3,
                                                                    'Entidad' => 3,
                                                                    'IdEntidad'=> $Id,
                                                                    'Documento' => NULL,
                                                                    'DocumentoURL' => NULL,
                                                                    'FechaVencimiento' => NULL,
                                                                    'IdEstatus' => 1,
                                                                    'createdOn' => $fecha,
                                                                    'entry_by'=> $IdUser,
                                                                    'entry_by_access' => 0,
                                                                    'updatedOn'=> NULL,
                                                                    'FechaEmision'=> NULL,
                                                                    'Resultado'=> NULL ]);
                //Insertamos la anotación a la persona
                $IdAnotac = \DB::table('tbl_anotaciones')->insertGetId(['IdConceptoAnotacion' => $anot,
                                                                      'IdPersona' => $Id,
                                                                      'createdOn' => $fecha,
                                                                      'entry_by'=> $IdUser,
                                                                      'entry_by_access' => $IdUser,
                                                                      'updatedOn'=> NULL]);
                //Insertamos el movimiento de la persona
                $lintIdMovimientoPersona = \DB::table('tbl_movimiento_personal')
                                            ->insertGetId(["IdAccion" => 2,
                                                           "contrato_id" => $IdCont,
                                                           "IdPersona" => $Id,
                                                           "entry_by" => $lintIdUser]
                                                        );

                \DB::table('tbl_contratos_personas')->where('IdPersona', '=', $Id)->where('contrato_id', '=', $IdCont)->delete();

            }else{
                $lintResultLeaveAccess = \MyPeoples::LeaveAccess($Id);
                $lintIdResultLeave = \MyPeoples::LeaveContract($Id, $IdCont, $anot, $ldatFechaEfectiva);
            }
            return response()->json(array(
                'status'=>'success',
                'message'=> \Lang::get('core.note_success')
            ));

        } else {
            return response()->json(array(
                'status'=>'error',
                'message'=> \Lang::get('core.note_error')
            ));

        }

    }
    public function postExtender( Request $request)
    {

        $lintIdContrato = $request->input('contrato');
        $ldatFechaVencimiento = $request->input('fechaEx');
        $ldatFechaVencimiento = self::FormatoFecha($ldatFechaVencimiento);

        $lobjMyContract = new MyContracts($lintIdContrato);
        $larrResultado = $lobjMyContract::Extender($ldatFechaVencimiento);

        if ($ldatFechaVencimiento>=date('Y-m-d')){
          $lintIdEstatusDocumento = 1;
        }else{
          $lintIdEstatusDocumento = 2;
        }

        \DB::table('tbl_documentos')
        ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento","=", "tbl_tipos_documentos.IdTipoDocumento")
        ->where('tbl_documentos.Entidad','=',2)
        ->where('tbl_documentos.IdEntidad','=',$lintIdContrato)
        ->where('tbl_tipos_documentos.IdProceso','=',26)
        ->update(['FechaVencimiento' => $ldatFechaVencimiento, 'IdEstatusDocumento' => $lintIdEstatusDocumento]);

        return response()->json($larrResultado);

    }

    public function postDelete( Request $request)
    {

        if($this->access['is_remove'] ==0) {
            return response()->json(array(
                'status'=>'error',
                'message'=> \Lang::get('core.note_restric')
            ));
            die;

        }
        // delete multipe rows
        if(count($request->input('ids')) >=1)
        {
            $this->model->destroy($request->input('ids'));
            \DB::table('tbl_contratos_personas')->whereIn('contrato_id',$request->input('ids'))->delete();
            return response()->json(array(
                'status'=>'success',
                'message'=> \Lang::get('core.note_success_delete')
            ));
        } else {
            return response()->json(array(
                'status'=>'error',
                'message'=> \Lang::get('core.note_error')
            ));

        }

    }

    public static function display( )
    {
        $mode  = isset($_GET['view']) ? 'view' : 'default' ;
        $model  = new Contratos();
        $info = $model::makeInfo('contratos');

        $data = array(
            'pageTitle' =>  $info['title'],
            'pageNote'  =>  $info['note']

        );

        if($mode == 'view')
        {
            $id = $_GET['view'];
            $row = $model::getRow($id);
            if($row)
            {
                $data['row'] =  $row;
                $data['fields']         =  \SiteHelpers::fieldLang($info['config']['grid']);
                $data['id'] = $id;
                return view('contratos.public.view',$data);
            }

        } else {

            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $params = array(
                'page'      => $page ,
                'limit'     =>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
                'sort'      => 'contrato_id' ,
                'order'     => 'asc',
                'params'    => '',
                'global'    => 1
            );

            $result = $model::getRows( $params );
            $data['tableGrid']  = $info['config']['grid'];
            $data['rowData']    = $result['rows'];

            $page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
            $pagination = new Paginator($result['rows'], $result['total'], $params['limit']);
            $pagination->setPath('');
            $data['i']          = ($page * $params['limit'])- $params['limit'];
            $data['pagination'] = $pagination;
            return view('contratos.public.index',$data);
        }


    }

    function postSavepublic( Request $request)
    {

        $rules = $this->validateForm();
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $data = $this->validatePost('tbl_contrato');
             $this->model->insertRow($data , $request->input('contrato_id'));
            return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
        } else {

            return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
            ->withErrors($validator)->withInput();

        }

    }

    public function postDatospersona( Request $request)
    {
        $idCentro = $request->IdCentro;
        $this->data['areasT'] = \DB::table('tbl_area_de_trabajo')->where('IdCentro',$idCentro)->where('IdTipoAcceso','<>',2)->get();
        return $this->data;

    }

      public function postDatos( Request $request)
    {
        $idCentro = $request->IdCentro;
        $this->data['areasT'] = \DB::table('tbl_area_de_trabajo')->where('IdCentro',$idCentro)->get();
        return $this->data;

    }

    public function postDatacontratista(Request $request){
        $lintIdContratista = $request->id;

        $fetchMode = \DB::getFetchMode();
        \DB::setFetchMode(\PDO::FETCH_ASSOC);
        $lobjSubContratistas = \DB::table("tbl_subcontratistas")
        ->join("tbl_contratistas","tbl_contratistas.IdContratista","=","tbl_subcontratistas.SubContratista")
        ->where('tbl_subcontratistas.IdContratista', '=', $lintIdContratista)
        ->select(\DB::raw("tbl_contratistas.IdContratista as value"),\DB::raw("concat(tbl_contratistas.RUT,' ',tbl_contratistas.RazonSocial) as display"))
        ->get();
        \DB::setFetchMode($fetchMode);

        return response()->json(array(
            'status'=>'sucess',
            'valores'=>$lobjSubContratistas,
            'message'=>\Lang::get('core.note_sucess')
            ));
    }

    public function postUploaditems(Request $request){

        include '../app/Library/PHPExcel/IOFactory.php';
        include '../app/Library/PHPExcel/Cell.php';
        require_once '../app/Library/PHPExcel.php';

        //Cargamos las variables
        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUserLogin = \Session::get('uid');
        $lstrDestinationPath = "uploads/documents/";
        $lintIdContrato = $request->idcontrato;
        $lintIdTipo = $request->idtipo;

        $lobjFile = Input::file("FileDataItems");

        //Generamos el nuevo nombre del archivo
        $lstrFileName = $lobjFile->getClientOriginalName();
        $lstrExtension =$lobjFile->getClientOriginalExtension();
        $lintRand = rand(1000,100000000);
        $lstrFileNewName = strtotime(date('Y-m-d H:i:s')).'-'.$lintRand.'.'.$lstrExtension;
        $uploadSuccess = $lobjFile->move($lstrDestinationPath, $lstrFileNewName);

        //Abrimos el archivo
        $lstrFile = $lstrDestinationPath.$lstrFileNewName;

        try {
            $objPHPExcel = \PHPExcel_IOFactory::load($lstrFile);
        }catch(Exception $e) {
            die('Error loading file "'.pathinfo($lstrFile,PATHINFO_BASENAME).'": '.$e->getMessage());
        }
        $larrDataFile = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        $lintCountDataFile = count($larrDataFile);



        //Abrimos el archivo Excel
        $lobjFileResult = new \PHPExcel();
        $lobjFileResultPHP = \PHPExcel_IOFactory::createWriter($lobjFileResult, "Excel2007");

        $lintRowInit = 4;
        $larrFormato = array("ITEM"=>"A",
                 "DESCRIPCION"=>"B",
                 "UNIDAD"=>"C",
                 "MONTO"=>"D",
                 "CANTIDAD"=>"E",
                 "RESULTADO"=>"D"
                  );

        //Creamos el archivo de creación
        $lobjHoja = $lobjFileResult->getActiveSheet();
        $lobjHoja->setTitle('Resultado');
        $lobjHoja->getCell($larrFormato['ITEM'].'1')->setValue('ITEM');
        $lobjHoja->getCell($larrFormato['DESCRIPCION'].'1')->setValue('DESCRIPCION');
        $lobjHoja->getCell($larrFormato['UNIDAD'].'1')->setValue('UNIDAD');
        $lobjHoja->getCell($larrFormato['MONTO'].'1')->setValue('MONTO');
        $lobjHoja->getCell($larrFormato['CANTIDAD'].'1')->setValue('CANTIDAD');
        $lobjHoja->getCell($larrFormato['RESULTADO'].'1')->setValue('RESULTADO');
        $lintResultado = 1;

        $row = 3;
        $cart = array();

        $lastColumn = $objPHPExcel->getActiveSheet()->getHighestDataColumn();
        $lastColumn++;
        for ($column = 'A'; $column != $lastColumn; $column++) {
            $cell = $objPHPExcel->getActiveSheet()->getCell($column.$row);
            if ($cell->getValue()=="CANTIDAD"){
              array_push($cart, $column);
            }
        }


      for($i=$lintRowInit;$i<=$lintCountDataFile;$i++)
      {

          $lstrResultado = "";
          $lstrItem = trim($larrDataFile[$i][$larrFormato['ITEM']]);

          if (strlen($lstrItem)==0){
              continue;
          }

          $lstrDescripcion = str_replace("⅛","",str_replace("¼","",str_replace("⅜","",str_replace("½","",str_replace("¾","",str_replace("Ø","",str_replace('"','\"',trim($larrDataFile[$i][$larrFormato['DESCRIPCION']]))))))));

          if (isset($larrFormato['UNIDAD'])){
            $lstrUnd = trim($larrDataFile[$i][$larrFormato['UNIDAD']]);
            if (strlen($lstrUnd)>0){
              //buscamos la unidad en la base de datos de unidades
              $conslt = \DB::table('tbl_unidades')->select('IdUnidad')->where('Descripcion','=',$lstrUnd)->get();
              if (count($conslt)==0 ){
                return response()->json(array(
                  'status'=>'error',
                  'message'=> \Lang::get('core.note_success'),
                  'result'=>'')
                );
                  break;
              }

              else
              $lintUnidad = $conslt[0]->IdUnidad;
            }
            else
              $lintUnidad = 0;
          }

          $lintMonto = isset($larrFormato['MONTO'])?trim($larrDataFile[$i][$larrFormato['MONTO']]):0;
          $lintCantidad = 0;

          $larrItem = explode(".",$lstrItem);
          $lintCount = count($larrItem)-1;
          if ($lintCount>=0) {
            for ($m=0; $m <= $lintCount; $m++) {
              if($larrItem[$m]=="0"){
              unset($larrItem[$m]);
              $lintCount -= 1;
              }
            }
          }else{
            $larrItem[0]=$lstrItem;
            $lintCount=0;
          }

         $larrEstadoPago = array();
         $lintEstadoPago = 1;

          foreach($cart as $item) {
              $cell = $objPHPExcel->getActiveSheet()->getCell($item."1");
              $valorN = $cell->getFormattedValue();
              $cellC = $objPHPExcel->getActiveSheet()->getCell($item.$i);
              $ValorC = $cellC->getValue();
              $lintCantidad += $cellC->getValue();

              $ldatFechaPlan = isset($larrDataFile[1][$larrFormato['CANTIDAD']])?$valorN:"";
              if ($ldatFechaPlan){
                  if (\PHPExcel_Shared_Date::isDateTime($objPHPExcel->getActiveSheet()->getCell($item."1"))) {
                    $lstrFormat = $this->ExcelFormatToPHP($objPHPExcel->getActiveSheet()->getStyle($item."1")->getNumberFormat()->getFormatCode());
                    $fecha = \DateTime::createFromFormat($lstrFormat, $ldatFechaPlan);
                    $ldatFechaPlan =  $fecha->format('Y-m-d');

                  }else{
                      return response()->json(array(
                        'status'=>'error',
                        'message'=> \Lang::get('core.note_success'),
                        'result'=>$newfilename)
                      );
                  }
              }
              $larrEstadoPago[$lintEstadoPago] = array("fecha"=>$ldatFechaPlan,"cantidad"=>$ValorC,"monto"=>isset($larrDataFile[$i][$larrFormato['MONTO']])?$larrDataFile[$i][$larrFormato['MONTO']]:0);
              $lintEstadoPago += 1;
            }

            //Definimos los niveles, solo permitimos 5 niveles
            if ($lintCount==0){
              $this->larrItems[$larrItem[0]] = array("title"=>$lstrDescripcion,"children"=>array(),"unidad"=>$lintUnidad,"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
            }elseif ($lintCount==1){
              $this->larrItems[$larrItem[0]]["children"][$larrItem[1]] = array("title"=>$lstrDescripcion,"children"=>array(),"unidad"=>$lintUnidad,"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
            }elseif ($lintCount==2){
              $this->larrItems[$larrItem[0]]["children"][$larrItem[1]]["children"][$larrItem[2]] = array("title"=>$lstrDescripcion,"children"=>array(),"unidad"=>$lintUnidad,"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
            }elseif ($lintCount==3){
              $this->larrItems[$larrItem[0]]["children"][$larrItem[1]]["children"][$larrItem[2]]["children"][$larrItem[3]] = array("title"=>$lstrDescripcion,"children"=>array(),"unidad"=>$lintUnidad,"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
            }elseif ($lintCount==4){
              $this->larrItems[$larrItem[0]]["children"][$larrItem[1]]["children"][$larrItem[2]]["children"][$larrItem[3]]["children"][$larrItem[4]] = array("title"=>$lstrDescripcion,"children"=>array(),"unidad"=>$lintUnidad,"cantidad"=> $lintCantidad, "monto"=>$lintMonto, "plan"=>$larrEstadoPago);
            }

            if (!$lstrResultado){
                $lstrResultado = "01|Cargado satisfactoriamente";
            }else{
                $lintResultado += 1;
                $lobjHoja->getCell($larrFormato['ITEM'].$lintResultado)->setValue($larrDataFile[$i][$larrFormato['ITEM']]);
                $lobjHoja->getCell($larrFormato['DESCRIPCION'].$lintResultado)->setValue($larrDataFile[$i][$larrFormato['DESCRIPCION']]);
                $lobjHoja->getCell($larrFormato['UNIDAD'].$lintResultado)->setValue($larrDataFile[$i][$larrFormato['UNIDAD']]);
                $lobjHoja->getCell($larrFormato['RESULTADO'].$lintResultado)->setValue($lstrResultado);
            }

        }

       $this->SaveItimizado($lintIdContrato);

        $rand = rand(1000,100000000);
        $newfilename = "result-".strtotime(date('Y-m-d H:i:s')).'-'.$rand.'.xlsx';
        $lobjFileResultPHP->save($lstrDestinationPath.$newfilename);


        return response()->json(array(
          'status'=>'success',
          'message'=> \Lang::get('core.note_success'),
          'result'=>$newfilename)
        );

    }

    private function ExcelFormatToPHP($pstrFecha){
      $lstrResultado = "";
      switch ($pstrFecha) {
        case "mm-dd-yy":
          $lstrResultado = "m-d-y";
          break;
      }
      return $lstrResultado;
    }

    private function SaveItimizado($pintIdContrato){

      $lintIdAnterior = "";


      foreach ($this->larrItems as $lstrIdPosiciones => $larrPosiciones) {

        //guardo las posiciones
        $lobjPosiciones = \DB::table('tbl_contratos_items')
                             ->where('Descripcion', '=', $larrPosiciones['title'])
                             ->where('contrato_id','=',$pintIdContrato)
                             ->get();

        if (!$lobjPosiciones){
          $lintIdPosicion = \DB::table('tbl_contratos_items')->insertGetId(array("contrato_id"=>$pintIdContrato,
                                                                                 "Identificacion"=>$lstrIdPosiciones,
                                                                                 "Descripcion"=>$larrPosiciones['title'],
                                                                                 "IdUnidad"=>$larrPosiciones['unidad'],
                                                                                 "cantidad"=>$larrPosiciones['cantidad'],
                                                                                 "monto"=>$larrPosiciones['monto']));
        }else{
          $lintIdPosicion = $lobjPosiciones[0]->IdContratoItem;
        }

        foreach ($larrPosiciones['children'] as $lintIdItem => $larrItems) {

            //guardo las posiciones
            $lobjItems = \DB::table('tbl_contratos_items')
                                 ->where('Descripcion', '=', $larrItems['title'])
                                 ->where('contrato_id','=',$pintIdContrato)
                                 ->where('IdParent','=',$lintIdPosicion)
                                 ->get();
            if (!$lobjItems){
              $lintIdItems = \DB::table('tbl_contratos_items')->insertGetId(array("contrato_id"=>$pintIdContrato,
                                                                                  "Identificacion"=>$lintIdItem,
                                                                                  "IdParent"=> $lintIdPosicion,
                                                                                  "Descripcion"=>$larrItems['title'],
                                                                                  "IdUnidad"=>$larrItems['unidad'],
                                                                                  "cantidad"=>$larrItems['cantidad'],
                                                                                  "monto"=>$larrItems['monto']));
            }else{
              $lintIdItems = $lobjItems[0]->IdContratoItem;
              \DB::table('tbl_contratos_items')->where("IdContratoItem","=",$lobjItems[0]->IdContratoItem)->update(array(
                                                      "Descripcion"=>$larrItems['title'],
                                                      "IdUnidad"=>$larrItems['unidad'],
                                                      "cantidad"=>$larrItems['cantidad'],
                                                      "monto"=>$larrItems['monto']));
            }


            //luego de guardar las posiciones vamos a la tabla de plan para verificar si se debe guardar
            if ($lintIdItems ){
                foreach ($larrItems['plan'] as $lstrPlan => $larrPlan) {
                  $lobjItemsPlan = \DB::table('tbl_contratos_items_p')
                                     ->where('IdItem', '=', $lintIdItems)
                                     ->where('Mes','=',$larrPlan['fecha'])
                                     ->get();
                  if (!$lobjItemsPlan){
                      $lintIdPlan = \DB::table('tbl_contratos_items_p')->insertGetId(array("IdItem"=> $lintIdItems,
                                                                                      "Mes"=> $larrPlan['fecha'],
                                                                                      "contrato_id"=>$pintIdContrato,
                                                                                      "Cantidad"=> str_replace(",",".",$larrPlan['cantidad']),
                                                                                      "Monto"=> str_replace(",",".",$larrPlan['monto']),
                                                                                      "SubTotal"=> $larrPlan['cantidad']*$larrPlan['monto'] ));
                  }else{
                      $lintIdPlan = \DB::table('tbl_contratos_items_p')->where("IdItemPlan","=",$lobjItemsPlan[0]->IdItemPlan)
                                                                                ->update(array(
                                                                               "Cantidad"=> str_replace(",",".",$larrPlan['cantidad']),
                                                                               "Monto"=> str_replace(",",".",$larrPlan['monto']),
                                                                               "SubTotal"=> $larrPlan['cantidad']*$larrPlan['monto'] ));
                      $lintIdPlan = $lobjItemsPlan[0]->IdItemPlan;
                  }
                }
            }

            //origen
            foreach ($larrItems['children'] as $lintIdItem2 => $larrItems2) {

                //guardo las posiciones
                $lobjItems2 = \DB::table('tbl_contratos_items')
                                     ->where('Descripcion', '=', $larrItems2['title'])
                                     ->where('contrato_id','=',$pintIdContrato)
                                     ->where('IdParent','=',$lintIdItems)
                                     ->get();
                if (!$lobjItems2){
                  //echo "no existe otra vez".var_dump($larrItems2);
                  $lintIdItems2 = \DB::table('tbl_contratos_items')->insertGetId(array("contrato_id"=>$pintIdContrato,
                                                                                      "Identificacion"=>$lintIdItem2,
                                                                                      "IdParent"=> $lintIdItems,
                                                                                      "Descripcion"=>$larrItems2['title'],
                                                                                      "IdUnidad"=>$larrItems2['unidad'],
                                                                                      "cantidad"=>$larrItems2['cantidad'],
                                                                                      "monto"=>$larrItems2['monto']));
                }else{
                    $lintIdItems2 = $lobjItems2[0]->IdContratoItem;
                    \DB::table('tbl_contratos_items')->where("IdContratoItem","=",$lobjItems2[0]->IdContratoItem)->update(array(
                                                                      "Descripcion"=>$larrItems2['title'],
                                                                      "IdUnidad"=>$larrItems2['unidad'],
                                                                      "cantidad"=>$larrItems2['cantidad'],
                                                                      "monto"=>$larrItems2['monto']));
                }

                //luego de guardar las posiciones vamos a la tabla de plan para verificar si se debe guardar
                if ($lintIdItems2){
                    //echo "entro";
                    foreach ($larrItems2['plan'] as $lstrItem3 => $larrItems3) {
                      //echo "plan ".var_dump($larrItems3). " " . $lintIdItems2 . "<br/> ";
                      $lobjItemsPlan = \DB::table('tbl_contratos_items_p')
                                         ->where('IdItem', '=', $lintIdItems2)
                                         ->where('Mes','=',$larrItems3['fecha'])
                                         ->get();
                      if (!$lobjItemsPlan){
                          $lintIdPlan = \DB::table('tbl_contratos_items_p')->insertGetId(array("IdItem"=> $lintIdItems2,
                                                                                          "Mes"=> $larrItems3['fecha'],
                                                                                          "contrato_id"=>$pintIdContrato,
                                                                                          "Cantidad"=> str_replace(",",".",$larrItems3['cantidad']),
                                                                                          "Monto"=> str_replace(",",".",$larrItems3['monto']),
                                                                                          "SubTotal"=> $larrItems3['cantidad']*$larrItems3['monto'] ));
                      }else{
                          $lintIdPlan = \DB::table('tbl_contratos_items_p')->where("IdItemPlan","=",$lobjItemsPlan[0]->IdItemPlan)
                                                                                    ->update(array(
                                                                                   "Cantidad"=> str_replace(",",".",$larrItems3['cantidad']),
                                                                                   "Monto"=> str_replace(",",".",$larrItems3['monto']),
                                                                                   "SubTotal"=> $larrItems3['cantidad']*$larrItems3['monto'] ));
                          $lintIdPlan = $lobjItemsPlan[0]->IdItemPlan;
                      }
                    }
                }

            }
        }

      }

    }

    private function setLevel($lintLevel, $parrLevel){
      $parrLevel = array();
      return $parrLevel;
    }

  public function postDatainformacion(Request $request){
      $id = $request->id;
      $datos = \DB::table('tbl_contrato')
          ->join('tbl_contsegmento', 'tbl_contrato.segmento_id', '=', 'tbl_contsegmento.segmento_id')
          ->join('tb_users', 'tbl_contrato.admin_id', '=', 'tb_users.id')
          ->join('tbl_contgeografico', 'tbl_contrato.geo_id', '=', 'tbl_contgeografico.geo_id')
          ->join('tbl_contareafuncional', 'tbl_contrato.afuncional_id', '=', 'tbl_contareafuncional.afuncional_id')
		->join('tbl_contrato_tipogasto', 'tbl_contrato.id_tipogasto', '=', 'tbl_contrato_tipogasto.id_tipogasto')
		->join('tbl_contrato_extension', 'tbl_contrato.id_extension', '=', 'tbl_contrato_extension.id_extension')
          ->join('tbl_contclasecosto', 'tbl_contrato.claseCosto_id', '=', 'tbl_contclasecosto.claseCosto_id')
       ->select('tbl_contrato.contrato_id','tbl_contsegmento.seg_nombre','tb_users.first_name','tb_users.last_name','tbl_contgeografico.geo_nombre','tbl_contareafuncional.afuncional_nombre','tbl_contclasecosto.ccost_nombre', 'tbl_contrato_tipogasto.nombre_gasto','tbl_contrato_extension.nombre')
       ->where('contrato_id','=',$id)->get();

      return response()->json(array(
          'status'=>'sucess',
          'valores'=>$datos,
          'message'=>\Lang::get('core.note_sucess')
          ));
  }

  function postMasivo(Request $request, $id =0){

        //Proceso de carga masiva de personas
    $larrResult = \MyLoadbatch::LoadBach(2, Input::file("FileDataContracts"));
    return response()->json(array(
        'status'=>'success',
        'message'=> \Lang::get('core.note_success'),
        'result'=>$larrResult
        ));

  }

  private function FormatoFecha($pstrFecha){
      if ($pstrFecha){
        $larrFecha = explode("/", $pstrFecha);
        return $larrFecha[2].'-'.$larrFecha[1].'-'.$larrFecha[0];
      }
  }

    public  function postCompruebafiniquito(Request $request)
    {

        $IdPersona = $request->persona;

        $lobjDocumentosPendientes = \DB::table("tbl_documentos")
            ->where("tbl_documentos.Entidad","=","3")
            ->where("tbl_documentos.IdEntidad","=",$IdPersona)
            ->where("tbl_documentos.IdTipoDocumento","=",4)
            ->where("tbl_documentos.IdEstatus","!=",5)
            ->get();

        if ($lobjDocumentosPendientes){
            $resultado=1;
        }
        else{
            $resultado=0;
        }

        return response()->json(array(
            'status'=>'sucess',
            'valores'=>$resultado,
            'message'=>\Lang::get('core.note_sucess')
        ));

    }

    public  function postCompruebaaccesos(Request $request)
    {

        $lintIdContrato = $request->contrato;
        $lintIdCentro = $request->centro;

        $centrosP = \DB::table('tbl_acceso_areas')
            ->join("tbl_accesos", "tbl_acceso_areas.IdAcceso", "=", "tbl_accesos.IdAcceso")
            ->where("tbl_accesos.IdTipoAcceso", "=", 1)
            ->where("tbl_accesos.contrato_id", "=", $lintIdContrato)
            ->where("tbl_acceso_areas.IdCentro", "=", $lintIdCentro)
            ->count();

        $centrosAc = \DB::table('tbl_acceso_activos_areas')
            ->join("tbl_accesos_activos", "tbl_acceso_activos_areas.IdAccesoActivo", "=", "tbl_accesos_activos.IdAccesoActivo")
            ->where("tbl_accesos_activos.IdTipoAcceso", "=", 1)
            ->where("tbl_accesos_activos.contrato_id", "=", $lintIdContrato)
            ->where("tbl_acceso_activos_areas.IdCentro", "=", $lintIdCentro)
            ->count();


        if ($centrosP == 0 && $centrosAc == 0) {
            $resultado=0;
        }
        else{
            $resultado=1;
        }

        return response()->json(array(
            'status'=>'sucess',
            'valores'=>$resultado,
            'message'=>\Lang::get('core.note_sucess')
        ));

    }

}
