<?php

namespace App\Http\Controllers\ApiFront;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Http\Controllers\AccesosController;
use App\Models\Gruposespecificos;
use App\Models\Edplog;

class variosController extends Controller
{

  public function getMandante ($tipo=null){
    $mandante = DB::table('tbl_configuraciones')->where('Nombre','CNF_APPNAME')->value('Valor');

    if(!is_null($tipo)){
      return $mandante;
    }

    return response()->json(["value"=>$mandante],200);
    exit;
  }

  public function getCSFR($tipo=null){
    $token = csrf_token();

    if(!is_null($tipo)){
      return $token;
    }

    return response()->json(["value"=>$token],200);
    exit;
  }

  public function postBuscar(Request $request)
  {
    $param = $request->param;

    $listadoContratos = DB::table('tbl_contrato')
      ->leftJoin('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
      ->leftJoin('tbl_contratos_centros', 'tbl_contrato.contrato_id', '=', 'tbl_contratos_centros.contrato_id')
      ->leftJoin('tbl_centro', 'tbl_centro.IdCentro', '=', 'tbl_contratos_centros.IdCentro')
      ->leftJoin('tb_users', 'tb_users.id', '=', 'tbl_contrato.admin_id')
      //->whereIn('tbl_contrato.contrato_id',[$lobjFiltro['contratos']])
      ->where('tbl_contrato.cont_estado', 1)
      ->whereRaw("tb_users.first_name like '%$param%' or tbl_contrato.cont_numero like '%$param%' or tbl_centro.Descripcion like '%$param%'")
      ->select('tbl_centro.Descripcion', 'tbl_contrato.contrato_id', 'tbl_contrato.cont_nombre');


    $listadoContratos = $listadoContratos->get();

    return response()->json($listadoContratos);
  }

  public function Log($pintIdAccion, $pstrObservacion = "")
  {

    $lobjDocumentoLog = new Edplog;

    $lobjDocumentoLog->save();

    return array(
      'code' => 1,
      'status' => 'success',
      'result' => $lobjDocumentoLog,
      'message' => \Lang::get('core.note_success')
    );
  }

  public function checkAccessProperty($lintGroupUser, $accion = null,$contrato_id = null,$edp_id = null)
    {
      if(is_null($contrato_id)){
        $contrato_id = \DB::table('tbl_contrato_edp')->where('edp_id',$edp_id)->value('contrato_id');
      }
      $flag = false;
      switch ($accion) {
        case 'eliminacion':
          $etapa =  DB::table('tbl_contrato_itemizado_etapas')->where('perfil_id', $lintGroupUser)->where('etapa','eliminacion')->where('contrato_id',$contrato_id)->first();
          if($etapa){
            $flag=true;
          }

          break;
        case 'aprobacion':
          $etapa =  DB::table('tbl_contrato_itemizado_etapas')->where('perfil_id', $lintGroupUser)->where('etapa','aprobacion')->where('contrato_id',$contrato_id)->first();
          if($etapa){
            $flag=true;
          }
          break;
        case 'creacion':
          $etapa =  DB::table('tbl_contrato_itemizado_etapas')->where('perfil_id', $lintGroupUser)->where('etapa','creacion')->first();
          if($etapa){
            $flag=true;
          }
          break;

        default:
          $flag = false;
          break;
      }
      return $flag;
    }

  public function getPerfiles(){
    $perfiles = \DB::table('tb_groups')->select('name','level','group_id')->where('group_id','>',1)->get();
    $usuarios = \DB::table('tb_groups')->join('tb_users','tb_groups.group_id','=','tb_users.group_id')->select('name','tb_users.group_id','id','first_name','last_name')->get();
    $data['perfiles']=$perfiles;
    $data['usuarios']=$usuarios;
    return response()->json($data);
  }

  public function getPeoplesCanApprove(Request $request)
  {

    $contrato_id = $request->contrato_id;
    $itemizado_id = $request->itemizado_id;

    $peoples = \DB::table('tb_users')
      ->join('tbl_contrato_itemizado_etapas','tbl_contrato_itemizado_etapas.perfil_id','=','tb_users.group_id')
      ->join('tbl_contrato_itemizado_etapas_flujo', 'tb_users.id', '=', 'tbl_contrato_itemizado_etapas_flujo.user_id')
      ->select('tb_users.id', 'tb_users.first_name', 'tb_users.last_name')
      ->groupBy('tb_users.id')
      ->where('tbl_contrato_itemizado_etapas.etapa', 'like', '%aprobacion%')
      ->where('tbl_contrato_itemizado_etapas.contrato_id',$contrato_id)
      ->where('tbl_contrato_itemizado_etapas_flujo.itemizado_id',$itemizado_id)
      ->get();

    return response()->json($peoples);
  }

  public function normalizeItemizado()
  {
    $contratos = \DB::table('tbl_contrato')
      ->leftJoin('tbl_contrato_itemizado', 'tbl_contrato.contrato_id', '=', 'tbl_contrato_itemizado.contrato_id')
      ->select('tbl_contrato.contrato_id')
      ->whereRaw('tbl_contrato_itemizado.contrato_id is null')
      ->get();

    $hoy = date('Y-m-d H:i');

    foreach ($contratos as $contrato) {
      \DB::table('tbl_contrato_itemizado')->insertGetId(['contrato_id'=>$contrato->contrato_id,'created_at'=>$hoy]);
    }

  }

  public function getMenuVue(){

    $group_id = \Session::get('gid');
    $menus = \DB::table('tbl_menuvue')
      ->where('active',1)
      ->where(function ($query) use ($group_id){
        $query->orWhere('group_id','LIKE',"%,$group_id,%")
              ->orWhere('group_id','LIKE',"$group_id,%")
              ->orWhere('group_id','LIKE',"%,$group_id");
      })
      ->orderBy('orden','asc')
      ->get();

    $data = [];
    $childs=[];

    foreach ($menus as $menu) {
      foreach ($menus as $parent) {
        if($menu->menu_id==$parent->parent_id){
          array_push($childs, [
            'name'=>$parent->name,
            'icon'=>$parent->icon,
            'vueRouter'=>$parent->vueRouter,
            'laravelRouter'=>$parent->laravelRouter
          ]);
        }
      }
      if($menu->parent_id==0){
        array_push($data, [
          'name'=>$menu->name,
          'icon'=>$menu->icon,
          'vueRouter'=>$menu->vueRouter,
          'laravelRouter'=>$menu->laravelRouter,
          'childs'=>$childs
        ]);
      }
      $childs=[];

    }

    return array('menuStructure'=>$data);

  }

  public function filtroBuscador(){
    $data = [];
    $data = \DB::table('tbl_contrato')
      ->select('tbl_contrato.contrato_id','tbl_contrato.cont_nombre','tbl_contrato.cont_numero','tbl_contratistas.RazonSocial as contratista','tbl_contratistas.IdContratista','tb_users.first_name as adc_nombre','tb_users.last_name as adc_apellido','tbl_centro.IdCentro','tbl_centro.Descripcion as centro')
      ->join('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
      ->join('tb_users','tbl_contrato.admin_id','=','tb_users.id')
      ->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
      ->join('tbl_centro','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
      ->where('tbl_contrato.cont_estado',1)
      ->get();

    $succes = false;
    if(count($data)>0){
      $succes = true;
    }

    return response()->json([
      'success'=> $succes,
      'code'=> 200,
      'data' => $data
      ]);

  }

}
