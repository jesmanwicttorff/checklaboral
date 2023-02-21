<?php

namespace App\Http\Controllers\ApiFront;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;

class itemizadoController extends Controller
{

  public function getItemizadoLineas(Request $request){
    $validator = Validator::make($request->all(), [
        'contrato_id' => 'required|numeric'
    ]);
    $itemizado['message']=array();
    if($validator->fails()){
      array_push($itemizado['message'],'se necesita el contrato_id');
      return response()->json($itemizado,200); die;
    }
    $perfil_id = \Session::get('uid');
    /*
    if(!app('App\Http\Controllers\ApiFront\variosController')->checkAccessProperty($perfil_id,'creacion')){
      array_push($itemizado['message'],'No Tiene permiso para crear estados de pago');
      $itemizado['status']='nok';
      return response()->json($itemizado,200); die;
    }*/
      $contrato_id = $request->contrato_id;

      $itemizado['config'] = DB::table('tbl_contrato_itemizado')
        ->leftjoin('tbl_contrato_itemizado_moneda','tbl_contrato_itemizado.moneda_id','=','tbl_contrato_itemizado_moneda.moneda_id')
        ->leftjoin('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contrato_itemizado.contrato_id')
        ->leftjoin('tbl_contrato_itemizado_condicionpago','tbl_contrato_itemizado.condicionpago_id','=','tbl_contrato_itemizado_condicionpago.condicionpago_id')
        ->where('tbl_contrato_itemizado.contrato_id',$contrato_id)
        ->select('tbl_contrato_itemizado.itemizado_id','tbl_contrato_itemizado_moneda.valor as moneda','tbl_contrato_itemizado_moneda.moneda_id','tbl_contrato.cont_montoTotal','tbl_contrato_itemizado_condicionpago.valor as condicionpago','tbl_contrato_itemizado_condicionpago.condicionpago_id','tbl_contrato_itemizado.tipoLinea','tbl_contrato_itemizado.tiposDocumentos_id','tbl_contrato_itemizado.reajuste','tbl_contrato_itemizado.MontoTotal');


        $itemizado['config'] = $itemizado['config']->addSelect(\DB::raw('1 as numero'));
        $itemizado['estado']=false;
        $hoy = date('Y-m-d H:i');
        $existeEDP = \DB::table('tbl_contrato_edp')->where('contrato_id',$contrato_id)->first();

        $itemizado['config'] = $itemizado['config']->first();

        $itemizado['estado']=false;
        if($existeEDP){
          $edp_id = $existeEDP->edp_id;
        }else{
          $edp_id = \DB::table('tbl_contrato_edp')->insertGetId(['contrato_id'=>$contrato_id,'numero'=>1,'estado_id'=>1]);
        }
        $itemizado['config']->edp_id = $edp_id;


      if(isset($itemizado['config']->MontoTotal)){
        $montoTotal = $itemizado['config']->MontoTotal;
      }else{
        $montoTotal = 0;
      }

      if(!$itemizado['config']){
          array_push($itemizado['message'],'sin itemizado para contrato');
          return response()->json($itemizado,200); die;
      }

      foreach ($itemizado['config'] as $key => $value) {
        if(is_null($value)){
          $itemizado['config']->$key=0;
        }
      }

        $lineas = \DB::table('tbl_contrato_itemizado_lineas')->where('itemizado_id',$itemizado['config']->itemizado_id)->get();
        if(!$lineas){
            array_push($itemizado['message'],'sin lineas para el itemizado');
            return response()->json($itemizado,200); die;
        }

        $i=0;
        foreach ($lineas as $linea) {
          $itemized[$i]['principal'] = $linea;
          $itemized[$i]['subLinea'] = \DB::table('tbl_contrato_itemizado_sublineas')->join('tbl_contrato_itemizado_unidades_medida','tbl_contrato_itemizado_sublineas.unidadMedidad_id','=','tbl_contrato_itemizado_unidades_medida.medida_id')->where('linea_id',$linea->linea_id)->get();
          $i++;
        }

      $itemizado['lineas']=$itemized;

      $edps = \DB::table('tbl_contrato_edp')->where('contrato_id',$contrato_id)->where('estado_id',3)->orderBy('edp_id','asc')->get();

      $suma=0;
      $i=0;
      $itemizado['desglose'][$i]['acumulado'] = 0;
      $itemizado['desglose'][$i]['total'] = $montoTotal;
      $itemizado['desglose'][$i]['esteEdp'] = 0;

      $i=1;
      foreach ($edps as $edp) {
        $suma = $suma+$edp->montoTotal;
        $itemizado['desglose'][$i]['acumulado'] = $suma;
        $itemizado['desglose'][$i]['total'] = $montoTotal - $suma;
        $itemizado['desglose'][$i]['esteEdp'] = $edp->montoTotal;
        $i++;
      }

      $request->replace(['itemizado_id' => $itemizado['config']->itemizado_id]);
      $itemizado['adicionales'] = self::getAdicionales($request,1);

      return response()->json($itemizado,200);

  }

  public function postStoreItemizado(Request $request){

    if($request->isMethod('post')){
      $data = json_decode($request->getContent());
      $contrato_id = $data->ConfiguracionGeneral->contrato_id;

      $existeItemizado = \DB::table('tbl_contrato')
        ->join('tbl_contrato_itemizado','tbl_contrato.contrato_id','=','tbl_contrato_itemizado.contrato_id')
        ->where('tbl_contrato.contrato_id',$contrato_id)->first();

      if(!$existeItemizado){
        return response()->json(['message'=>"Error",'status'=>'nok']); die;
      }

      //chequeamos que se guardo previamente el flujo de aprobacion
      $flujoAprobacion = \DB::table('tbl_contrato_itemizado_etapas')->where('contrato_id',$contrato_id)->first();
      if(!$flujoAprobacion){
        return response()->json(['message'=>"Error, Debe crear la configuracion de etapas",'status'=>'nok']); die;
      }

      $moneda_id = $data->ConfiguracionGeneral->moneda_id;
      $condicionPago_id = $data->ConfiguracionGeneral->condicionpago_id;
      $tiposDocumentos = implode(",",$data->ConfiguracionGeneral->tiposDocumentos_id);
      $tipoLinea = strtolower($data->ConfiguracionGeneral->tipoLinea);
      $montoTotal = str_replace(',','.',$data->ConfiguracionGeneral->montoTotal);
      $itemizado_id = $existeItemizado->itemizado_id;
      $reajuste = $data->ConfiguracionGeneral->checkReajuste;

      DB::beginTransaction();

      try{

        $hoy = date('Y-m-d H:i');

         \DB::table('tbl_contrato_itemizado')
          ->where('itemizado_id',$itemizado_id)
          ->update([
            'moneda_id'=>$moneda_id,
            'condicionPago_id'=>$condicionPago_id,
            'tiposDocumentos_id'=>$tiposDocumentos,
            'tipoLinea'=>$tipoLinea,
            'MontoTotal'=>$montoTotal,
            'updated_at'=>$hoy,
            'reajuste'=>$reajuste
          ]);

          $lineasExistentes = \DB::table('tbl_contrato_itemizado_lineas')->where('itemizado_id',$itemizado_id)->get();
          \DB::table('tbl_contrato_itemizado_lineas')->where('itemizado_id',$itemizado_id)->delete();
          if($lineasExistentes){
              foreach ($lineasExistentes as $lineaExistente) {
                \DB::table('tbl_contrato_itemizado_sublineas')->where('linea_id',$lineaExistente->linea_id)->delete();
              }
          }

          foreach ($data->lineas_generales as $linea) {

            $linea_id = \DB::table('tbl_contrato_itemizado_lineas')->insertGetId([
              'itemizado_id'=>$itemizado_id,
              'tipoCobro_id'=>$linea->tipoCobro_id,
              'montoLimite'=>$linea->montolimite
            ]);

            foreach ($linea->subLineas as $sublinea) {
              $subLinea_id = \DB::table('tbl_contrato_itemizado_sublineas')->insertGetId([
                'linea_id'=>$linea_id,
                'nombre'=>$sublinea->nombre,
                'cantidad'=>$sublinea->cantidad,
                'unidadMedidad_id'=>$sublinea->unidad_de_medida,
                'documentacion'=>$sublinea->documentacion,
                'montoLinea'=>$sublinea->valor_unitario,
                'centroCosto'=>$sublinea->centro_costo
              ]);
            }
          }
        DB::commit();
      }catch(\Exception $e){
        DB::rollBack();
        return response()->json($e->getMessage(),400);
      }

      return response()->json(['message'=>"itemizado guardado correctamente",'itemizado_id'=>$itemizado_id]);

    }else{
      return response()->json('Consulta mal realizada',400);
    }
  }

  public function postStoreItemizadoAdicional(Request $request){

    if($request->isMethod('post')){
      $datas = json_decode($request->getContent());
    }
    $itemizado_id = $datas->itemizado_id;
    foreach ($datas->configData as $data) {
      switch ($data->name) {
        case 'incentivo':
          if(isset($data->reason)){$motivo_id = $data->reason;}else{$motivo_id=0;}
          $monto = $data->amount;
          $type = 1;
          break;
        case 'Descuentos':
          if(isset($data->reason)){$motivo_id = $data->reason;}else{$motivo_id=0;}
          $monto = $data->amount;
          $type = 2;
          break;
        case 'Retenciones':
          if(isset($data->reason)){$motivo_id = $data->reason;}else{$motivo_id=0;}
          $monto = $data->amount;
          $type = 3;
          break;
        case 'Multas':
          if(isset($data->reason)){$motivo_id = $data->reason;}else{$motivo_id=0;}
          $monto = $data->amount;
          $type = 4;
          break;

        default:
          // code...
          break;
      };
      if($motivo_id!=0){
        \DB::table('tbl_contrato_itemizado_conf_adicional')->insertGetId(['itemizado_id'=>$itemizado_id,'motivo_id'=>$motivo_id,'monto'=>$monto,'type_id'=>$type]);
      }
    }

    return response()->json(['message'=>'configuración adicional guardada'],200);
  }

  public function getItemizadoMotivos($tipo=null){
    $motivos = \DB::table('tbl_contrato_itemizado_conf_adicional_motivo')->get();
    if(!is_null($tipo)){
      return $motivos;
    }
    return response()->json($motivos);
  }

  public function getItemizadoType(){
    $motivos = \DB::table('tbl_contrato_itemizado_conf_adicional_type')->get();
    return response()->json($motivos);
  }

  public function getUnidadMedida($tipo=null){
    $motivos = \DB::table('tbl_contrato_itemizado_unidades_medida')->get();
    if(!is_null($tipo)){
      return $motivos;
    }
    return response()->json($motivos);
  }

  public function getAdicionales(Request $request,$tipo=null){
      $itemizado_id = $request->itemizado_id;
      $adicionales = \DB::table('tbl_contrato_itemizado_conf_adicional')
        ->join('tbl_contrato_itemizado_conf_adicional_motivo','tbl_contrato_itemizado_conf_adicional.motivo_id','=','tbl_contrato_itemizado_conf_adicional_motivo.motivo_id')
        ->where('tbl_contrato_itemizado_conf_adicional.motivo_id','<>','')->where('itemizado_id',$itemizado_id)->orderBy('type_id','asc')->get();

      $i=0;$j=0;

      $data['configData'][1]['type_id']='1';
      $data['configData'][1]['group'][$j][]='';
      $data['configData'][2]['type_id']='2';
      $data['configData'][2]['group'][$j][]='';
      $data['configData'][3]['type_id']='3';
      $data['configData'][3]['group'][$j][]='';
      $data['configData'][4]['type_id']='4';
      $data['configData'][4]['group'][$j][]='';

      foreach ($adicionales as $adicional) {
        if($adicional->type_id!=$i){
          $i++;$j=0;
        }
        $data['configData'][$i]['type_id']=$adicional->type_id;
        $data['configData'][$i]['group'][$j]['adicional_id']=$adicional->adicional_id;
        $data['configData'][$i]['group'][$j]['valor']=$adicional->valor;
        $data['configData'][$i]['group'][$j]['motivo_id']=$adicional->motivo_id;
        $data['configData'][$i]['group'][$j]['amount']=$adicional->monto;
        $j++;
      }

      if(isset($tipo)){
        return $data;
      }
      return response()->json($data);
  }

  public function postDeleteAdicional(Request $request){
    $adicional_id = $request->adicional_id;

    $borrado = \DB::table('tbl_contrato_itemizado_conf_adicional')->where('adicional_id',$adicional_id)->delete();
    if($borrado){
      return response()->json(['status'=>'ok','message'=>'Configuración adicional borrada'],200);
    }else{
      return response()->json(['status'=>'nok','message'=>'No se pudo borrar la configuración adicional'],200);
    }
  }

  static public function getEstado($estado){
    $e = \DB::table('tbl_contrato_edp_estados')->where('valor',$estado)->first();
    if($e){
      return $e->edpEstado_id;
    }else{
      return 0;
    }
  }

  public function postSaveApprovalFlow(Request $request){
    $data = json_decode($request->getContent());

    $superAdminId = 1;
    $itemizado_id = $data->itemizedId;
    $contrato_id = $data->contractId;
    $creacion = $data->creationSubmissionStage;
    $edicion = $data->editingStage;
    $aprobacion = $data->approvalStage;
    $eliminacion = $data->eliminationStage;
    $approvalFlow = $data->approvalFlow;
    if(count($creacion->selectedProfiles)==0){
      return response()->json(['status'=>'nok','message'=>'Falta perfil de creación']);
    }
    if(count($edicion->selectedProfiles)==0){
      return response()->json(['status'=>'nok','message'=>'Falta perfil de edición']);
    }
    if(count($aprobacion->selectedProfiles)==0){
      return response()->json(['status'=>'nok','message'=>'Falta perfil de aprobación']);
    }
    if(count($eliminacion->selectedProfiles)==0){
      return response()->json(['status'=>'nok','message'=>'Falta perfil de eliminación']);
    }

    \DB::beginTransaction();
      \DB::table('tbl_contrato_itemizado_etapas')
        ->where('contrato_id',$contrato_id)
        ->where('itemizado_id',$itemizado_id)
        ->delete();

      \DB::table('tbl_contrato_itemizado_etapas_flujo')
        ->where('itemizado_id',$itemizado_id)
        ->delete();

      \DB::table('tbl_contrato_itemizado_etapas')->insert(['etapa'=>'creacion','perfil_id'=>$superAdminId,'contrato_id'=>$contrato_id,'itemizado_id'=>$itemizado_id]);
      foreach ($creacion->selectedProfiles as $dato) {
        \DB::table('tbl_contrato_itemizado_etapas')->insert(['etapa'=>'creacion','perfil_id'=>$dato->group_id,'contrato_id'=>$contrato_id,'itemizado_id'=>$itemizado_id]);
      }

      \DB::table('tbl_contrato_itemizado_etapas')->insert(['etapa'=>'edicion','perfil_id'=>$superAdminId,'contrato_id'=>$contrato_id,'itemizado_id'=>$itemizado_id]);
      foreach ($edicion->selectedProfiles as $dato) {
        \DB::table('tbl_contrato_itemizado_etapas')->insert(['etapa'=>'edicion','perfil_id'=>$dato->group_id,'contrato_id'=>$contrato_id,'itemizado_id'=>$itemizado_id]);
      }

      \DB::table('tbl_contrato_itemizado_etapas')->insert(['etapa'=>'aprobacion','perfil_id'=>$superAdminId,'contrato_id'=>$contrato_id,'itemizado_id'=>$itemizado_id]);
      foreach ($aprobacion->selectedProfiles as $dato) {
        \DB::table('tbl_contrato_itemizado_etapas')->insert(['etapa'=>'aprobacion','perfil_id'=>$dato->group_id,'contrato_id'=>$contrato_id,'itemizado_id'=>$itemizado_id]);
      }

      \DB::table('tbl_contrato_itemizado_etapas')->insert(['etapa'=>'eliminacion','perfil_id'=>$superAdminId,'contrato_id'=>$contrato_id,'itemizado_id'=>$itemizado_id]);
      foreach ($eliminacion->selectedProfiles as $dato) {
        \DB::table('tbl_contrato_itemizado_etapas')->insert(['etapa'=>'eliminacion','perfil_id'=>$dato->group_id,'contrato_id'=>$contrato_id,'itemizado_id'=>$itemizado_id]);
      }

      foreach ($approvalFlow as $aprobador) {
        if(isset($aprobador->value)){
          \DB::table('tbl_contrato_itemizado_etapas_flujo')->insert(['itemizado_id'=>$itemizado_id,'user_id'=>$aprobador->value->id]);
        }
      }
    DB::commit();

    return response()->json(['status'=>'ok','message'=>'Configuración guardada']);
  }

  public function getApprovalFlow(Request $request){
    $contrato_id = $request->contrato_id;
    $itemizado_id = $request->itemizado_id;
    $etapas = \DB::table('tbl_contrato_itemizado_etapas')
      ->leftJoin('tbl_contrato_itemizado_etapas_flujo','tbl_contrato_itemizado_etapas.itemizado_id','=','tbl_contrato_itemizado_etapas_flujo.itemizado_id')
      ->where('tbl_contrato_itemizado_etapas.contrato_id',$contrato_id)
      ->where('tbl_contrato_itemizado_etapas.itemizado_id',$itemizado_id)
      ->select('tbl_contrato_itemizado_etapas.etapa','tbl_contrato_itemizado_etapas.perfil_id','tbl_contrato_itemizado_etapas.propiedades','tbl_contrato_itemizado_etapas_flujo.user_id')
      ->get();

    return response()->json($etapas);

  }

  public function getCondicionPago(Request $request,$tipo=null){

    if($request->has('contrato_id')){
      $contrato_id = $request->contrato_id;
      $condiciones = DB::table('tbl_contrato_itemizado_condicionpago')
        ->join('tbl_contrato_itemizado','tbl_contrato_itemizado.condicionPago_id','=','tbl_contrato_itemizado_condicionpago.condicionpago_id')
        ->where('tbl_contrato_itemizado.contrato_id',$contrato_id)
        ->select('tbl_contrato_itemizado_condicionpago.valor')
        ->get();
    }else{
      $condiciones = DB::table('tbl_contrato_itemizado_condicionpago')->get();
    }

    if(!is_null($tipo)){
      return $condiciones;
    }

    return response()->json($condiciones);
  }

  public function getMoneda(Request $request,$tipo=null){

    if($request->has('contrato_id')){

      $contrato_id = $request->contrato_id;

      $monedas = DB::table('tbl_contrato_itemizado_moneda')
        ->join('tbl_contrato_itemizado','tbl_contrato_itemizado.moneda_id','=','tbl_contrato_itemizado_moneda.moneda_id')
        ->where('tbl_contrato_itemizado.contrato_id',$contrato_id)
        ->select('tbl_contrato_itemizado_moneda.valor','tbl_contrato_itemizado_moneda.moneda_id')
        ->get();
    }else{
      $monedas = DB::table('tbl_contrato_itemizado_moneda')->get();
    }

    if(!is_null($tipo)){
      return $monedas;
    }

    return response()->json($monedas);
  }

  public function creaItemizado(){
    $request = new \Illuminate\Http\Request();
    $data['motivos'] = self::getItemizadoMotivos(1);
    $data['unidadMedida'] = self::getUnidadMedida(1);
    $data['condicionPago'] = self::getCondicionPago($request,1);
    $data['moneda'] = self::getMoneda($request,1);
    $data['tiposDocumentos'] = app('App\Http\Controllers\ApiFront\documentosController')->getTiposDocumentos(1);

    return response()->json($data);
  }

}
