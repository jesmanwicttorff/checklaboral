<?php

namespace App\Http\Controllers\ApiFront;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Http\Controllers\AccesosController;
use App\Models\Gruposespecificos;
use App\Models\Contratosservicios;
use App\Library\MyContracts;
use App\Library\MyDocuments;
use App\Library\MyCheckLaboral;
use App\Library\Acreditacion;
use App\Library\AcreditacionContrato;
use App\Models\Tiposcontratospersonas;
use App\Models\Contratos;

class contratosController extends Controller
{
  protected $data = array();
  public $module = 'contratos';

  public function __construct() {

      parent::__construct();
      $this->model = new Contratos();

      $this->info = $this->model->makeInfo( $this->module);
      $this->access = $this->model->validAccess($this->info['id']);

      $this->data = array(
          'pageTitle'         =>  $this->info['title'],
          'pageNote'          =>  $this->info['note'],
          'pageModule'        => 'contratos'
      );

  }

  public function postSave(Request $request, $id =0){
    $validator = Validator::make($request->all(), [
            'cont_numero' => 'required',
            'cont_nombre' => 'required',
            'cont_fechaInicio' => 'required',
            'cont_fechaFin' => 'required',
            'IdContratista'=>'required',
            'idgrupoespecifico'=>'required',
            'idservicio'=>'required',
            'geo_id'=>'required',
            'afuncional_id'=>'required',
            'segmento_id'=>'required',
            'bulktwo_IdCentro'=>'required',
            'bulktwo_IdTipoCentro'=>'required'
        ]);

    if ($validator->fails()) {
        return response()->json(['status'=>'nok','message'=>"faltan campos"],400);
    }

    $data = $this->validatePost('tbl_contrato');

    $lintIdUser = \Session::get('uid');
    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));

    $larrSmartSave = $request->smartsave;
    $ldatFechaActual = date('Y-m-d H:i:s');
    $lintIdContrato = $request->contrato_id;

    $original = $_POST['cont_fechaInicioContrato'];
    $_POST['cont_fechaInicioContrato'] = self::FormatoFecha($_POST['cont_fechaInicio']);
    $_POST['cont_fechaInicio'] = self::FormatoFecha($original);
    $_POST['cont_fechaFin'] = self::FormatoFecha($_POST['cont_fechaFin']);

    if (isset($_POST['idservicio'])){
      $lobjContratosservicios = Contratosservicios::find($_POST['idservicio']);
      if ($lobjContratosservicios){
        $data['cont_proveedor'] = $lobjContratosservicios->name;
      }else{
        $data['cont_proveedor'] = "";
      }
    }

    $data['idgrupoespecifico']=$request->idgrupoespecifico;
    $data['idservicio']=$request->idservicio;
    $data['ContratoPrueba']=$request->cont_prueba;
    $data['cont_fechaInicioContrato']=$request->cont_fechaInicioContrato;

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

    if ($lintLevelUser==6 && $lintIdUser!=$data['entry_by_access']){
      $lintEntryByAccess = $lintIdUser;
    }else{
      $lintEntryByAccess = $data['entry_by_access'];
    }

    if (isset($request->cont_numero)){
      $lobjContrato = \DB::table('tbl_contrato')
          ->where('tbl_contrato.cont_numero', '=', $request->cont_numero)
          ->where('tbl_contrato.contrato_id', '!=', $lintIdContrato)
          ->get();
      if ($lobjContrato){
          return response()->json(array(
              'message'   => "El número de contrato ya se encuentra asignado",
              'status'    => 'error'
           ));
      }
    }

    if ($request->cont_fechaInicio > $request->cont_fechaFin){
        return response()->json(array(
            'message'	=> 'No se puede crear registro con fecha de inicio mayor a la fecha fin',
            'status'	=> 'error'
        ));
    }

    //edicion contrato
    if($request->isEditModeActive===true){
      $data['updatedOn'] = date('Y-m-d H:i');
      //Verificamos si se cambia el estatus para almacenar la accion
      $informacion =   \DB::table('tbl_contrato')->select('cont_estado','id_extension')->where('contrato_id', '=', $lintIdContrato)->first();
      if($informacion){
        if ($request->cont_estado!=$informacion->cont_estado){
            if ($request->cont_estado==1)
                $Observ ="El Estatus del contrato cambio de Inactivo a Activo";
            else
                $Observ ="El Estatus del contrato cambio de Activo a Inactivo";
            self::Registraractividad($lintIdContrato,10,$Observ);
        }
        if ($request->id_extension!=$informacion->id_extension){

            $tipoN =   \DB::table('tbl_contrato_extension')->select('nombre')->where('id_extension', '=', $request->id_extension)->first();
            $tipoO =   \DB::table('tbl_contrato_extension')->select('nombre')->where('id_extension', '=', $informacion->id_extension)->first();

            $Observ ="El tipo de contrato cambio de ".$tipoO->nombre." a ". $tipoN->nombre;

            self::Registraractividad($lintIdContrato,11,$Observ);
        }
      }

      Contratos::where('contrato_id')->update($data);

    }else{
      //creacion contrato
      // las fechas solo son modificables en la creacion
      $data['cont_fechaInicioContrato'] = $_POST['cont_fechaInicioContrato'];
      $data['cont_fechaInicio'] = $_POST['cont_fechaInicio'];
      $data['cont_fechaFin'] = $_POST['cont_fechaFin'];
      $data['createdOn'] = date('Y-m-d H:i');
      $data['updatedOn'] = date('Y-m-d H:i');
      $lintIdContrato = Contratos::insertGetId($data);
      $lobjMyContrats = New AcreditacionContrato($lintIdContrato);
      self::Registraractividad($lintIdContrato,8);

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
              }else{
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
            }else{
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

    }

    $larrIdCentros = $request->bulktwo_IdCentro;
    $larrIdTipoCentros = $request->bulktwo_IdTipoCentro;
    $existCentro = \DB::table('tbl_contratos_centros')
      ->where('IdContratista',$data['IdContratista'])
      ->where('contrato_id',$lintIdContrato)
      ->first();

    if ($larrIdCentros and !$existCentro) {
        \DB::table('tbl_contratos_centros')->insert(array("IdCentro" => $larrIdCentros,
            "IdContratista" => $data['IdContratista'],
            "contrato_id" => $lintIdContrato,
            "IdTipoCentro" => $larrIdTipoCentros,
            "entry_by" => $lintIdUser,
            "createdOn" => $ldatFechaActual,
            "entry_by_access" => $lintEntryByAccess));
    }
    if($larrIdCentros and $existCentro){
      \DB::table('tbl_contratos_centros')
        ->where('IdContratista',$data['IdContratista'])
        ->where('contrato_id',$lintIdContrato)
        ->where('IdCentro',$larrIdCentros)
        ->update([
          "IdTipoCentro" => $larrIdTipoCentros,
          "entry_by" => $lintIdUser,
          "createdOn" => $ldatFechaActual,
          "entry_by_access" => $lintEntryByAccess,
          "IdCentro" => $larrIdCentros
        ]);
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

    $itemizado_id = \DB::table('tbl_contrato_itemizado')->where('contrato_id',$lintIdContrato)->first();
    if(!$itemizado_id){
      $hoy = date('Y-m-d H:i');
      $itemizado_id = \DB::table('tbl_contrato_itemizado')->insertGetId(['contrato_id'=>$lintIdContrato,'created_at'=>$hoy]);
    }

    return response()->json(array(
        'status'=>'success',
        'message'=> \Lang::get('core.note_success'),
        'result'=>$datageneral,
        'itemizado_id'=>$itemizado_id
        ));

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

  private function FormatoFecha($pstrFecha){
      if ($pstrFecha){
        $larrFecha = explode("/", $pstrFecha);
        return $larrFecha[2].'-'.$larrFecha[1].'-'.$larrFecha[0];
      }
  }

  public function dataDash(request $request){
    $contrato_id = $request->contrato_id;

    $lobjCheckLaboral = new MyCheckLaboral();
		$dataCheck = $lobjCheckLaboral->LoadData('',null,$contrato_id);

    $periodo_mes = $request->mes;
    $periodo_ano = $request->ano;

    //$numero_indicadores = DB::table('indicadores_dash_contrato')->where('perfil')->count();
    $numero_indicadores = 5;

    $indicador[1][]=[
      'etiqueta'=>'Financiero',
      'valor'=> '50%'
    ];
    $indicador[1][]=[
      'etiqueta'=>'KPI',
      'valor'=> '99%'
    ];
    $indicador[1][]=[
      'etiqueta'=>'Dotación',
      'valor'=> '50%'
    ];
    $indicador[1][]=[
      'etiqueta'=>'SSO',
      'valor'=> '97%'
    ];
    $indicador[1][]=[
      'etiqueta'=>'RRLL',
      'valor'=> $dataCheck->documentacion_mes_actual
    ];

    $indicador[2][]=[
      'etiqueta'=>'Gasto Acum',
      'valor1'=> '50%',
      'valor2'=> 'KUSD$ 16.024'
    ];
    $indicador[2][]=[
      'etiqueta'=>'Gasto Acum',
      'valor1'=> '50%',
      'valor2'=> 'KUSD$ 16.024'
    ];
    $indicador[2][]=[
      'etiqueta'=>'Gasto Acum',
      'valor1'=> '50%',
      'valor2'=> 'KUSD$ 16.024'
    ];
    $indicador[2][]=[
      'etiqueta'=>'Gasto Acum',
      'valor1'=> '50%',
      'valor2'=> 'KUSD$ 16.024'
    ];
    $indicador[2][]=[
      'etiqueta'=>'Gasto Acum',
      'valor1'=> '50%',
      'valor2'=> 'KUSD$ 16.024'
    ];

    return $indicador;
  }


  /* Lista la info de todos los contratos x perfil de usuario */
  public function listadoContratos(request $request){
    $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
    $lcontratos = explode(',',$lobjFiltro['contratos']);
    $lintIdUser = \Session::get('uid');
    $lintGroupUser = \MySourcing::GroupUser($lintIdUser);
    $listadoContratos['acceso']=true;

    if(!self::hasAccessModule($lintGroupUser)){
      $listadoContratos['acceso']=false;
    }

    $contrato_id=0;
    $faena_id=0;
    $listadosKPI=[];

    if($request->has('contrato_id')){
      $contrato_id = $request->contrato_id;
    }

    if($request->has('faena_id')){
      $faena_id = $request->faena_id;
    }

    $listadoContratos = DB::table('tbl_contrato')
      ->leftJoin('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
      ->leftJoin('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
      ->leftJoin('tbl_centros_tipos','tbl_contratos_centros.IdTipoCentro','=','tbl_centros_tipos.id')
      ->leftJoin('tbl_centro','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
      ->leftJoin('tb_users','tb_users.id','=','tbl_contrato.admin_id')
      ->whereIn('tbl_contrato.contrato_id',$lcontratos)
      //->where('tbl_contrato.cont_estado',1)
      ->select('tbl_contrato.*','tbl_contratistas.RazonSocial',\DB::raw("concat(tb_users.first_name,' ',tb_users.last_name) as adc"),
        DB::raw("(SELECT COUNT(DISTINCT tbl_contratos_personas.IdPersona)
					     FROM tbl_contratos_personas
					     WHERE tbl_contratos_personas.contrato_id = tbl_contrato.contrato_id) as cant_pers"),'tbl_centros_tipos.id as idtipocentro','tbl_centros_tipos.nombre as tipoFaena','tbl_centro.Descripcion as faena' );

    if($contrato_id>0){
      $listadoContratos = $listadoContratos
                          ->leftJoin('tbl_tipos_de_garantias','tbl_tipos_de_garantias.IdTipoGarantia','=','tbl_contrato.IdGarantia')
                          ->leftJoin('tbl_contratos_grupos_especificos','tbl_contratos_grupos_especificos.id','=','tbl_contrato.idgrupoespecifico')
                          ->leftJoin('tbl_contratos_servicios','tbl_contratos_servicios.id','=','tbl_contrato.idservicio')
                          ->leftJoin('tbl_contgeografico','tbl_contgeografico.geo_id','=','tbl_contrato.geo_id')
                          ->leftJoin('tbl_contareafuncional','tbl_contareafuncional.afuncional_id','=','tbl_contrato.afuncional_id')
                          ->leftJoin('tbl_contrato_itemizado','tbl_contrato.contrato_id','=','tbl_contrato_itemizado.contrato_id')
                          ->leftjoin('tbl_contclasecosto','tbl_contclasecosto.claseCosto_id','=','tbl_contrato.claseCosto_id')
                          ->addSelect('tbl_tipos_de_garantias.Descripcion as garantia','tbl_contratos_grupos_especificos.name as grupoespecifico','tbl_contratos_servicios.name as servicio'
                          ,'tbl_contgeografico.geo_nombre as gerencia','tbl_contareafuncional.afuncional_nombre','tbl_centro.Descripcion as centro','tbl_contratos_centros.IdCentro','tbl_contrato_itemizado.itemizado_id',\DB::raw("if(tbl_contclasecosto.claseCosto_id is null,0,1)"))
                          ->where('tbl_contrato.contrato_id',$contrato_id);

      $listadosKPI = \DB::table('tbl_kpi')->where('contrato_id',$contrato_id)->get();

    }

    if($faena_id>0){
      $listadoContratos = $listadoContratos->where('tbl_centro.IdCentro',$faena_id);
    }

    $listadoContratos = $listadoContratos->get();

    $succes=false;
    $data = '';
    if($this->access['is_view']){
      $succes=true;
      $data = $listadoContratos;
    }

    return response()->json([
      'success'=> $succes,
      'code'=> 200,
      'data' => $data,
      'kpis' => $listadosKPI
      ]);
  }

  static function hasAccessModule($lintGroupUser){
    return true;
  }

  public function postDelete(request $request){ //ids
    $request = (clone request());
    $response = app('App\Http\Controllers\ContratosController')->postDelete($request);
    return response()->json($response);
  }

  public function postDocumentosPendientes(Request $request){
    if($request->has('contrato_id')){
      $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
      $lintIdUser = \Session::get('uid');
      $lintGroupUser = \MySourcing::GroupUser(\Session::get('uid'));
      //$lintLevelUser=1;
      //$lintIdUser=1;
      //$lintGroupUser=1;

      $lintIdContrato = $request->input('contrato_id');
      $larrContratista = \DB::table('tbl_contratistas')
                             ->select('tbl_contratistas.IdContratista')
                             ->join('tbl_contrato','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
                             ->where('tbl_contrato.contrato_id','=',$lintIdContrato)
                             ->first();
      $lintIdContratista = $larrContratista->IdContratista;

      $lobjDocumentos = \DB::table('tbl_documentos')
        ->select("tbl_documentos.IdDocumento","tbl_documentos.IdTipoDocumento", \DB::raw("tbl_tipos_documentos.Descripcion as TipoDocumento"), "tbl_documentos.FechaEmision", "tbl_documentos.FechaVencimiento", "tbl_documentos.IdEstatus", \DB::raw("tbl_documentos_estatus.Descripcion as Estatus"))
        ->join("tbl_tipos_documentos","tbl_tipos_documentos.IdTipoDocumento","=","tbl_documentos.IdTipoDocumento")
        ->join("tbl_documentos_estatus","tbl_documentos_estatus.IdEstatus","=","tbl_documentos.IdEstatus")
        ->where(function ($query) use ($lintIdContratista, $lintIdContrato) {
            $query->where("tbl_documentos.IdEntidad","=",$lintIdContrato)
                  ->where("tbl_documentos.Entidad","=",2);/* se comenta, requisito de irina no mostrar documentos de contratistas
                  ->orwhere(function($query) use ($lintIdContratista) {
                      $query->where("tbl_documentos.IdEntidad","=",$lintIdContratista)
                            ->where("tbl_documentos.Entidad","=", 1);
                  });*/
        })
        ->where(function ($query) {
            $query->where("tbl_documentos.IdEstatus", "!=", 5)
                ->orwhere(function ($query) {
                    $query->where("tbl_documentos.IdEstatus", "=", 5)
                    ->where("tbl_documentos.IdEstatusDocumento", "!=", "1");
                });
        })
        ->whereExists(function ($query) use ($lintGroupUser) {
            $query->select(\DB::raw(1))
                  ->from('tbl_tipo_documento_perfil')
                  ->whereRaw('tbl_tipo_documento_perfil.IdPerfil = '.$lintGroupUser)
                  ->whereRaw('tbl_tipo_documento_perfil.IdTipoDocumento = tbl_documentos.IdTipoDocumento');
        })
        ->get();

      return response()->json($lobjDocumentos,200);

    }else{
      return response()->json('Consulta mal realizada',400);
    }
  }

  public function postFiniquitarContrato(Request $request){
    if($request->has('contrato_id')){
      $lintIdContrato = $request->contrato_id;
      $lobjContrato = new MyContracts($lintIdContrato);
      $larrResultado = $lobjContrato::Settlement();
      return response()->json($larrResultado,200);
    }else{
      return response()->json('Consulta mal realizada',400);
    }

  }

  public function postCambioContractual(Request $request){
    if($request->has('contrato_id')){
      $ldatFechaContrato = $request->input('fecha');

      if ($ldatFechaContrato){
          $ldatFechaContrato = self::FormatoFecha($ldatFechaContrato);
      }

      $lintIdContrato = $request->contrato_id;
      $lobjContrato = new MyContracts($lintIdContrato);
      $lintIdDocumento = \DB::table('tbl_documentos')->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')->where('tbl_documentos.IdEntidad',$lintIdContrato)->where('tbl_documentos.Entidad',2)->where('tbl_tipos_documentos.IdProceso',99)->value('IdDocumento');

      $larrResultado = app('App\Http\Controllers\ContratosController')->ChangeContract($lintIdContrato, $lintIdDocumento, $ldatFechaContrato);

      return response()->json($larrResultado,200);
    }else{
      return response()->json('Consulta mal realizada',400);
    }

  }

  /*listado contratistas x perfil usuario */
  public function listadoContratistas(){
    $contratistas = \DB::table('tbl_contratistas')
    ->select(\DB::raw('tbl_contratistas.IdContratista as value'), \DB::raw('concat(tbl_contratistas.RUT,\' \',tbl_contratistas.RazonSocial) as display'))
    ->where('IdEstatus','=',1)
    ->get();

    return $contratistas;
  }


  public function listadoGrupos(){
    $grupos = Gruposespecificos::select(\DB::raw('id as value'),\DB::raw('name as display'))
                                      ->orderBy('display','asc')
                                      ->get();
    return $grupos;
  }

  public function listadoServicios (Request $request) {
        $lintIdGrupoEspecifico = $request->input('grupoespecifico');

        $lobjContratosServicios = Contratosservicios::select(\DB::raw('id as value'),
                                                                 \DB::raw('name as display'),
                                                                 "IdEstatus")
                                      ->orderBy('display','asc')
                                      ->where('idgrupoespecifico',$lintIdGrupoEspecifico)
                                      ->get();
        return $lobjContratosServicios;

  }

  public function getGarantias(){
    $garantias = \DB::table('tbl_tipos_de_garantias')
      ->select(\DB::raw('tbl_tipos_de_garantias.IdTipoGarantia as value'), \DB::raw('tbl_tipos_de_garantias.Descripcion as display'), "tbl_tipos_de_garantias.SinMonto", "tbl_tipos_de_garantias.IdEstatus")
      ->get();

    return $garantias;
  }

  public function getCentros(){
    $centros = \DB::table('tbl_centro')->select(\DB::raw('tbl_centro.IdCentro as value'),DB::raw('tbl_centro.Descripcion as display'))->orderBy('Descripcion')->get();
    return $centros;
  }

  public function getGerencia(){
    $gerencia = \DB::table("tbl_contgeografico")
    ->select(\DB::raw('tbl_contgeografico.geo_id as value'), \DB::raw('tbl_contgeografico.geo_nombre as display'))
    ->orderBy("tbl_contgeografico.geo_nombre","ASC")
    ->get();

    return $gerencia;
  }

  public function getSubgerencia(){
    $subgerencia = \DB::table("tbl_contareafuncional")
    ->select(\DB::raw('tbl_contareafuncional.afuncional_id as value'), \DB::raw('tbl_contareafuncional.afuncional_nombre as display'))
    ->orderBy("tbl_contareafuncional.afuncional_nombre","ASC")
    ->get();

    return $subgerencia;
  }

  public function getADC(){
    $adc = \DB::table("tb_users")
    ->select(\DB::raw('tb_users.id as value'), \DB::raw('concat(tb_users.first_name,\' \',tb_users.last_name) as display'))
    ->join("tb_groups","tb_groups.group_id","=","tb_users.group_id")
    ->where("tb_groups.level","=",4)
    ->get();
    return $adc;
  }

  public function getTipoGasto(){
    $gasto = \DB::table("tbl_contrato_tipogasto")
    ->select(\DB::raw('tbl_contrato_tipogasto.id_tipogasto as value'), \DB::raw('tbl_contrato_tipogasto.nombre_gasto as display'))
    ->orderBy("tbl_contrato_tipogasto.nombre_gasto","ASC")
    ->get();

    return $gasto;
  }

  public function getClaseCosto(){
    $clasecosto= \DB::table("tbl_contclasecosto")
        ->select(\DB::raw('tbl_contclasecosto.claseCosto_id as value'), \DB::raw("CONCAT(tbl_contclasecosto.ccost_numero, ' ', tbl_contclasecosto.ccost_nombre) as display"),\DB::raw(" ''  as isselect"))
        ->orderBy("tbl_contclasecosto.ccost_nombre","ASC")
        ->get();
    return $clasecosto;
  }

  public function getExtension(){
    $extension = \DB::table("tbl_contrato_extension")
    ->select(\DB::raw('tbl_contrato_extension.id_extension as value'), \DB::raw('tbl_contrato_extension.nombre as display'))
    ->orderBy("tbl_contrato_extension.nombre","ASC")
    ->get();

    return $extension;
  }

  public function getCreaContrato(){
    $data['contratistas'] = self::listadoContratistas();
    $data['listadoGrupos'] = self::listadoGrupos();
    $data['garantias'] = self::getGarantias();
    $data['centros'] = self::getCentros();
    $data['gerencia'] = self::getGerencia();
    $data['subgerencia'] = self::getSubgerencia();
    $data['adc'] = self::getADC();
    $data['tipogasto'] = self::getTipoGasto();
    $data['clasecosto'] = self::getClaseCosto();
    $data['extension'] = self::getExtension();
    $data['mandante'] = app('App\Http\Controllers\ApiFront\variosController')->getMandante(1);
    $data['csrf'] = app('App\Http\Controllers\ApiFront\variosController')->getCSFR(1);

    $lintGroupUser = \MySourcing::GroupUser(\Session::get('uid'));
  	$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));

    $data['levelUser'] = $lintLevelUser;
    $data['groupuser'] = $lintGroupUser;

    return response()->json($data);
  }

  public function getPeoples(Request $request){

    if($request->has('contrato_id')){
      $contrato_id = $request->input('contrato_id');
    }else{
      $contrato_id = 0;
    }

      $people = \DB::table('tbl_personas')
        ->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
        ->join('tbl_roles','tbl_roles.IdRol','=','tbl_contratos_personas.IdRol')
        ->select('RUT','Nombres','Apellidos','Descripción as rol','FechaInicioFaena','tbl_personas.IdPersona','tbl_roles.IdRol');

      if($contrato_id>0){
        $people = $people->where('contrato_id',$contrato_id);
      }

        $people = $people->get();

      return response()->json($people);
  }


  public function getActivePeople(Request $request){
    if($request->has('contrato_id')){
      $contrato_id = $request->input('contrato_id');
    }else{
      $contrato_id = 0;
    }

    $people = \DB::table('tbl_personas')
      ->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
      ->join('tbl_roles','tbl_roles.IdRol','=','tbl_contratos_personas.IdRol')
      ->select('RUT','Nombres','Apellidos','Descripción as rol','FechaInicioFaena','tbl_personas.IdPersona','tbl_roles.IdRol')
      ->where('tbl_contratos_personas.contrato_id','<>',$contrato_id)
      ->get();

    return response()->json($people);
  }

  public function postStorePeople(Request $request){
    if($request->has('contrato_id')){
      $contrato_id = $request->input('contrato_id');
    }else{
      return response()->json(['message'=>'Error asociando a persona'],200);
    }

    $id_persona = $request->IdPersona;
    if($id_persona <= 0){
      return response()->json(['message'=>'Error asociando a persona'],200);
    }

    $ldatFechaInicioFaena = $request->FechaInicioFaena;
    $id_rol = $request->id_rol;

    $lobjDocumentosPendientes = \DB::table("tbl_documentos")
        ->where("tbl_documentos.Entidad","=","3")
        ->where("tbl_documentos.IdEntidad","=",$id_persona)
        ->where("tbl_documentos.IdTipoDocumento","=",4)
        ->where("tbl_documentos.IdEstatus","!=",5)
        ->get();

    if ($lobjDocumentosPendientes){
        return response()->json(['message'=>'Error asociando: Persona tiene finiquito pendiente'],200);
    }

    $lobjAcreditacion = new Acreditacion($id_persona);
    $larrResultadoPersonas = $lobjAcreditacion::AssignContract($contrato_id,
                                                               $id_persona,
                                                               $id_rol,
                                                               0,
                                                               $ldatFechaInicioFaena);

    if($larrResultadoPersonas['code']==1){
      $message = "Persona asociada a contrato correctamente";
      $status = "ok";
    }elseif($larrResultadoPersonas['code']==3){
      $message = "La persona ya se encuentra asociada a un contrato";
      $status = "nok";
    }else{
      $message = "No se puede asignar está persona a este contrato";
      $status = "nok";
    }

    return response()->json(['message'=>$message,'status'=>$status],200);

  }

  public function getAccionesContrato(Request $request){

    if($request->has('contrato_id')){
      $contrato_id = $request->input('contrato_id');
    }else{
      $contrato_id = 0;
    }

    $acciones = \DB::table("tbl_contratos_acciones")
        ->select("tbl_contratos_acciones.createdOn","tbl_contratos_acciones.observaciones", "tb_users.first_name", "tb_users.last_name", "tb_users.avatar", "tbl_acciones.nombre", "tbl_acciones.descripcion")
        ->join("tbl_acciones","tbl_contratos_acciones.accion_id", "=", "tbl_acciones.IdAccion")
        ->join("tb_users","tb_users.id", "=", "tbl_contratos_acciones.entry_by")
        ->where("tbl_contratos_acciones.contrato_id","=",$contrato_id)
        ->orderBy("tbl_contratos_acciones.id",'desc')
        ->get();

    return response()->json($acciones);
  }

  public function getUploadsAttr(Request $req){
    $iddocumento = $req->iddocumento;
    $doc = DB::table('tbl_documentos')->join('tbl_tipos_documentos','tbl_documentos.idTipoDocumento','=','tbl_tipos_documentos.idTipoDocumento')->where('IdDocumento',$iddocumento)->first();

    $lobjDocumento = new MyDocuments($iddocumento);
		$lobjDatosDocumento = $lobjDocumento->getDatos();
		$row = $lobjDatosDocumento;

    $attr=array();
    $i=0;
    $flag=false;
    if($doc){
      switch ($doc->IdProceso) {
        case '1':
          $attr[$i]['type']="date";
          $attr[$i]['label']="Fecha Emisión Documento";
          $attr[$i]['required']='yes';
          $flag=true;
          break;
        case '4':
          $attr[$i]['type']="select";
          $attr[$i]['label']="Causa Desvinculación";
          $attr[$i]['required']='yes';
          $lobjAnotaciones = \DB::table('tbl_concepto_anotacion')->where('IdEstatus', '=', 1)->get();
          $j=0;
          foreach ($lobjAnotaciones as $larrAnotaciones){
            $attr[$i]['opciones'][$j]['select_value'] = $larrAnotaciones->IdConceptoAnotacion;
            $attr[$i]['opciones'][$j]['select_display'] = $larrAnotaciones->Descripcion;
            $j++;
          }
          $flag=true;
          break;
        case '21':
          $attr[$i]['type']="select";
          $attr[$i]['label']="Tipo Contrato";
          $attr[$i]['required']='yes';
          $lobjAnotaciones = Tiposcontratospersonas::Active()->get();
          $j=0;
          foreach ($lobjAnotaciones as $larrAnotaciones){
            $attr[$i]['opciones'][$j]['select_value'] = $larrAnotaciones->id;
            $attr[$i]['opciones'][$j]['select_display'] = $larrAnotaciones->Descripcion;
            $j++;
          }
          $flag=true;
          break;
        case '26':
          $attr[$i]['type']="date";
          $attr[$i]['label']="Fecha Vencimiento";
          $attr[$i]['required']='yes';
          $flag=true;
          break;

        default:
          // code...
          break;
      }
    }

    if($flag) $i++;

    if($row)
    foreach ($row->TipoDocumento->Valores as $p):
      if($p->TipoValor=="Fecha"){
        $attr[$i]['type']="date";
      }elseif($p->TipoValor=="Texto"){
        $attr[$i]['type']="input";
      }elseif ($p->TipoValor=="Numérico") {
        $attr[$i]['type']="number";
      }elseif ($p->TipoValor=="Radio") {
        $attr[$i]['type']="radio";
      }else{
        $attr[$i]['type']="input";
      }
      $attr[$i]['label']=$p->Etiqueta;
      if($p->Requerido=='SI'){
        $attr[$i]['required']='yes';
      }else{
        $attr[$i]['required']='no';
      }

      if($p->TipoValor=="Select Option"){
        $attr[$i]['type']="select";
        $listaValores = \DB::table('tbl_tipo_documento_data')->where('IdTipoDocumentoValor',$p->IdTipoDocumentoValor)->get();
        $j=0;
          foreach ($listaValores as $val) :
            $attr[$i]['opciones'][$j]['select_value']=$val->Valor;
            $attr[$i]['opciones'][$j]['select_display']=$val->Display;
            $j++;
          endforeach;
      }
      $attr[$i]['bulk_IdTipoDocumentoValor']=$p->IdTipoDocumentoValor;

      $i++;
    endforeach;

    return response()->json($attr);

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
  //itemizado

  public function postMontoTotal(Request $request){
    $contrato_id = $request->contrato_id;

    $monto = \DB::table('tbl_contrato')->where('contrato_id',$contrato_id)->value('cont_montoTotal');

    return response()->json($monto);
  }


  public function getDataDashboardContratoAdc(Request $request){

    $idUsuario = \Session::get('uid');
    $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
    $lcontratos = explode(',',$lobjFiltro['contratos']);

    $dataContrato = \DB::table('tbl_contrato')
      ->join('tb_users','tbl_contrato.admin_id','=','tb_users.id')
      ->whereIn('tbl_contrato.contrato_id',$lcontratos)
      ->get();

    $administrado = 0;
    $activo = 0;
    $inactivos = 0;
    $vencidos = 0;
    $i=0;

    $hoy = date('Y-m-d');

    foreach ($dataContrato as $contrato) {
      $administrado++;
      if($contrato->cont_estado==1){$activo++;}
      if($contrato->cont_estado==2){$inactivos++;}
      if($contrato->cont_estado==3){$vencidos++;}

      $data['administrado']=$administrado;
      $data['activo']=$activo;
      $data['inactivo']=$inactivos;
      $data['vencidos']=$vencidos;

      $edpsContrato = \DB::table('tbl_contrato_edp')->where('contrato_id',$contrato->contrato_id)->get();

      if($edpsContrato){
        $data['edps'] = $edpsContrato;
      }
      if($contrato->cont_fechaFin > $hoy){
        $data['porfinalizar'][$i] = ["nombre"=>$contrato->cont_nombre,"fecha"=>$contrato->cont_fechaFin];
      }

      $i++;
    }

    return response()->json($data);

  }

  public function getDataDashboardContratoAdcDetail(Request $request){

    $idUsuario = \Session::get('uid');
    $contrato_id = $request->contrato_id;

    $dataContrato = \DB::table('tbl_contrato')
      ->where('contrato_id',$contrato_id)
      ->first();

    $dataEdp = \DB::table('tbl_contrato_edp')->where('contrato_id',$contrato_id)->get();

    $hoy = date('Y-m-d');
    $edpsAprobados=0;
    $edpsRechazados=0;
    $i=0;

    $data['cont_numero']=$dataContrato->cont_numero;
    $data['cont_nombre']=$dataContrato->cont_nombre;
    $data['cont_fechaInicio']=$dataContrato->cont_fechaInicio;
    $data['cont_fechaFin']=$dataContrato->cont_fechaFin;
    $data['montoDisponible']="100000";
    $data['porcentajeAvance']=30;
    $data['ultimosEdp']=array();
    $data['aprobado']=array();

    $fechainicial = new \DateTime($data['cont_fechaInicio']);
    $fechafinal = new \DateTime($data['cont_fechaFin']);
    $diferencia = $fechainicial->diff($fechafinal);
    $meses = ( $diferencia->y * 12 ) + $diferencia->m;

    if($meses==0){$meses=1;}

    $sum = 0;
    foreach ($dataEdp as $edp) {

      if($edp->estado_id==3){
        $edpsAprobados++;
        $sum = $sum + $edp->montoTotal;
        array_push($data['aprobado'], $edp->montoTotal);
      }

      if($edp->estado_id==4){
        $edpsRechazados++;
      }

      array_push($data['ultimosEdp'],['numero'=>$edp->numero,'nombre'=>$edp->nombre_edp,'montoEdp'=>$edp->montoTotal,'fecha'=>$edp->fechaEnvio]);

      $i++;
    }

    $data['presupuesto']=$dataContrato->cont_montoTotal/$meses;

    $data['edpsAprobados'] = $edpsAprobados;
    $data['edpsRechazados'] = $edpsRechazados;

    return response()->json($data);

  }

  public function postSaveKpi(Request $request){
    $kpi_id = $request->kpi_id;
    $descripcion = $request->descripcion;
    $porcentaje = $request->porcentaje;
    $contrato_id = $request->contrato_id;

    if($kpi_id!='null'){
      //update
      \DB::table('tbl_kpi')->where('kpi_id',$kpi_id)->update(['descripcion'=>$descripcion,'porcentaje'=>$porcentaje]);
      return response()->json(['status'=>'success','message'=>'kpi actualizado']);
    }else{
      //nuevo registro
      $id = \DB::table('tbl_kpi')->insertGetId(['descripcion'=>$descripcion,'porcentaje'=>$porcentaje,'contrato_id'=>$contrato_id]);
      if($id){
        return response()->json(['status'=>'success','message'=>'kpi actualizado']);
      }else{
        return response()->json(['error'=>'success','message'=>'kpi NO actualizado']);
      }
    }
  }

}
