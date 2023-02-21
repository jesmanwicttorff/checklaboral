<?php

namespace App\Http\Controllers\ApiFront;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Http\Controllers\AccesosController;
use App\Models\Gruposespecificos;
use App\Models\Contratistas;
use App\Models\Contratos;
use App\Models\Edp;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PDF;
use ZipArchive;

class edpController extends Controller
{

  public static $estados = [
    'Aceptado' => 3
  ];

  public function getTiposDocumentosEDP(Request $request){
    $validator = Validator::make($request->all(), [
        'contrato_id' => 'required|numeric'
    ]);
    if($validator->fails()){
      return response()->json(['message'=>'error']);
    }
    $contrato_id = $request->contrato_id;
    $documentos = DB::table('tbl_contrato_itemizado')->select('tiposDocumentos_id')->where('contrato_id',$contrato_id)->first();

    $tok = strtok($documentos->tiposDocumentos_id, ",");

    while ($tok !== false) {
        $tipoDoc[] = \DB::table('tbl_tipos_documentos')->where('idTipoDocumento',$tok)->select('Descripcion','idTipoDocumento')->first();
        $tok = strtok(",");
    }

    return response()->json($tipoDoc,200);
  }

  public function getEdpLineas(Request $request){
    $validator = Validator::make($request->all(), [
        'contrato_id' => 'required|numeric',
        'edp_id' => 'required|numeric'
    ]);
    $itemizado['message']=array();
    $itemizado['status']='';
    if($validator->fails()){
      array_push($itemizado['message'],'se necesita el contrato_id');
      return response()->json($itemizado,200); die;
    }
    $perfil_id = \Session::get('uid');
    /*
    if(!app('App\Http\Controllers\ApiFront\variosController')->checkAccessProperty($perfil_id,'aprobacion')){
      array_push($itemizado['message'],'No Tiene permisos para revisar este EDP');
      $itemizado['status']='nok';
      return response()->json($itemizado,200); die;
    }
    */
    $contrato_id = $request->contrato_id;
    $edp_id = $request->edp_id;

    $itemizado['config'] = DB::table('tbl_contrato_itemizado')
        ->leftjoin('tbl_contrato_itemizado_moneda','tbl_contrato_itemizado.moneda_id','=','tbl_contrato_itemizado_moneda.moneda_id')
        ->leftjoin('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contrato_itemizado.contrato_id')
        ->leftjoin('tbl_contrato_itemizado_condicionpago','tbl_contrato_itemizado.condicionpago_id','=','tbl_contrato_itemizado_condicionpago.condicionpago_id')
        ->where('tbl_contrato_itemizado.contrato_id',$contrato_id)
        ->select('tbl_contrato_itemizado.itemizado_id','tbl_contrato_itemizado_moneda.valor as moneda','tbl_contrato_itemizado_moneda.moneda_id','tbl_contrato_itemizado.MontoTotal as cont_montoTotal','tbl_contrato_itemizado_condicionpago.valor as condicionpago','tbl_contrato_itemizado_condicionpago.condicionpago_id','tbl_contrato_itemizado.tipoLinea','tbl_contrato_itemizado.tiposDocumentos_id','tbl_contrato_itemizado.reajuste');


      $existeEDP = Edp::where('contrato_id',$contrato_id)->where('edp_id',$edp_id)->first();

      if($existeEDP){
        $edp_numero = $existeEDP->numero;
        $itemizado['config'] = $itemizado['config']
        ->leftjoin('tbl_contrato_edp','tbl_contrato_itemizado.contrato_id','=','tbl_contrato_edp.contrato_id')
        ->leftjoin('tbl_contrato_edp_estados','tbl_contrato_edp.estado_id','=','tbl_contrato_edp_estados.edpEstado_id')
        ->addSelect('tbl_contrato_edp.nombre_edp','tbl_contrato_edp.numero','tbl_contrato_edp.fechaIngreso','tbl_contrato_edp.observacion','tbl_contrato_edp_estados.valor as edpEstado','tbl_contrato_itemizado.MontoTotal','tbl_contrato_edp.montoTotal as MontoTotalEdp' )
        ->where('tbl_contrato_edp.edp_id',$edp_id);

        $itemizado['mandatoryDocumentsFiles'] = \DB::table('tbl_contrato_edp_mandatory_files')->where('edp_id',$edp_id)->get();

      }

      $itemizado['config'] = $itemizado['config']->first();

      if($existeEDP){

        $edpsgenerados = Edp::where('contrato_id',$contrato_id)->count();

        if($existeEDP->estado_id==self::$estados['Aceptado']){
          if(!$request->has('btn')){
            $edp_numero++;
          }
          $itemizado['config']->numero = $edp_numero;
        }else{
          $itemizado['config']->numero = $edpsgenerados;
        }
      }

      $itemizado_id = $itemizado['config']->itemizado_id;
      if(isset($itemizado['config']->MontoTotal)){
        $montoTotal = $itemizado['config']->MontoTotal;
        $montoEdps = Edp::where('contrato_id',$contrato_id)->whereIn('estado_id',[2,3])->sum('montoTotal');
        $itemizado['config']->MontoTotal = number_format($montoTotal - $montoEdps,1,'.','');
      }else{
        $montoTotal = $itemizado['config']->cont_montoTotal;
      }

      if(!$itemizado['config']){
          array_push($itemizado['message'],'sin estado de pago para contrato');
          return response()->json($itemizado,200); die;
      }

      $contratista = \DB::table('tbl_contrato')
        ->join('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
        ->where('tbl_contrato.contrato_id',$contrato_id)
        ->first();

      if($contratista)$itemizado['config']->contratista = $contratista->RUT." ".$contratista->RazonSocial;

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
        $itemized[$i]['subLinea'] = \DB::table('tbl_contrato_itemizado_sublineas')
          ->join('tbl_contrato_itemizado_unidades_medida','tbl_contrato_itemizado_sublineas.unidadMedidad_id','=','tbl_contrato_itemizado_unidades_medida.medida_id')
          ->leftJoin('tbl_contrato_edp_sublineas','tbl_contrato_itemizado_sublineas.sublinea_id','=','tbl_contrato_edp_sublineas.itemizado_sublinea_id')
          ->select('tbl_contrato_itemizado_sublineas.nombre','tbl_contrato_edp_sublineas.cantidad','tbl_contrato_edp_sublineas.montoLinea','tbl_contrato_itemizado_unidades_medida.valor as medida','tbl_contrato_itemizado_unidades_medida.valor','tbl_contrato_itemizado_unidades_medida.medida_id','tbl_contrato_itemizado_sublineas.montoLinea','tbl_contrato_itemizado_sublineas.documentacion')
          ->where('tbl_contrato_itemizado_sublineas.linea_id',$linea->linea_id)
          ->where('tbl_contrato_edp_sublineas.edp_id',$edp_id)
          ->distinct()
          ->get();
        $i++;
      }

      if($existeEDP){
        $itemizado['documentosLineas'] = \DB::table('tbl_contrato_edp_mandatory_files')->where('edp_id',$edp_id)->where('linea_id','<>',0)->get();
      }

      $itemizado['lineas']=$itemized;

      $adicionales_edp = \DB::table('tbl_contrato_edp_adicionales')->where('edp_id',$edp_id)->get();
      $itemizado['adicionales_edp'] = $adicionales_edp;

      $adicionales_itemizado = \DB::table('tbl_contrato_itemizado_conf_adicional')
                                  ->join('tbl_contrato_itemizado_conf_adicional_motivo','tbl_contrato_itemizado_conf_adicional.motivo_id','=','tbl_contrato_itemizado_conf_adicional_motivo.motivo_id')
                                  ->join('tbl_contrato_itemizado_conf_adicional_type', 'tbl_contrato_itemizado_conf_adicional_type.type_id' ,'=','tbl_contrato_itemizado_conf_adicional.type_id')
                                  ->select('tbl_contrato_itemizado_conf_adicional.monto','tbl_contrato_itemizado_conf_adicional.motivo_id','tbl_contrato_itemizado_conf_adicional.type_id','tbl_contrato_itemizado_conf_adicional_motivo.valor as motivoValor','tbl_contrato_itemizado_conf_adicional_type.valor as typeValor')
                                  ->where('itemizado_id',$itemizado_id)
                                  ->get();

      $itemizado['adicionales_itemizado'] = $adicionales_itemizado;

      $edps = Edp::where('contrato_id',$contrato_id)->where('estado_id',self::$estados['Aceptado'])->orderBy('edp_id','asc')->get();
      $suma=0;
      $i=0;
      $itemizado['desglose'][$i]['acumulado'] = 0;
      $itemizado['desglose'][$i]['total'] = $montoTotal;
      $itemizado['desglose'][$i]['esteEdp'] = $existeEDP->montoTotal;
      $i=1;
      foreach ($edps as $edp) {
        $suma = $suma+$edp->montoTotal;
        $itemizado['desglose'][$i]['acumulado'] = $suma;
        $itemizado['desglose'][$i]['total'] = $montoTotal - $suma;
        $itemizado['desglose'][$i]['esteEdp'] = $edp->montoTotal;
        $i++;
      }

      $aprobadores = \DB::table('tbl_contrato_itemizado_etapas_flujo')->where('itemizado_id',$itemizado_id)->count();
      $itemizado['elevado'] = false;
      if($aprobadores>0){
        $itemizado['elevado'] = true;
      }

      return response()->json($itemizado,200);

  }

  public function getCheckEDP(Request $request){
    $contrato_id = $request->contrato_id;
    $currentEDPs = Edp::where('contrato_id',$contrato_id)->orderBy('edp_id','asc')->get();
    $flag=false;
    $edp_id = 0;
    $status = "";
    $consumo = 0;

    foreach ($currentEDPs as $edp) {
        if($edp->estado_id==1 or $edp->estado_id==4){
          $flag=true;
          if($edp->fechaIngreso!='0000-00-00'){
            $flag=false;
          }
        }
        $edp_id = $edp->edp_id;

        if($edp->estado_id==2){
          $status = "enviado";
        }

        if($edp->estado_id==3){
          $consumo = $edp->montoTotal + $consumo;
        }
    }

    $itemizado = \DB::table('tbl_contrato_itemizado')->where('contrato_id',$contrato_id)->first();
    $item = "nok";
    $disponible = true;

    if($itemizado){
      if($itemizado->moneda_id != 0){
        $item = "ok";
      }

      if($consumo >= $itemizado->MontoTotal){
        $disponible = false;
      }
    }

    if($flag){
      return response()->json(['edp'=>'abierto','itemizado'=>$item,'status'=>$status,'edpID'=>$edp_id,'disponible'=>$disponible]);
    }else{
      return response()->json(['edp'=>'cerrado','itemizado'=>$item,'status'=>$status,'edpID'=>$edp_id,'disponible'=>$disponible]);
    }
  }

  public function getEDPRevisar(Request $request){
    $validator = Validator::make($request->all(), [
        'contrato_id' => 'required|numeric'
    ]);
    $itemizado['message']=array();
    if($validator->fails()){
      array_push($itemizado['message'],'se necesita el contrato_id');
      return response()->json($itemizado,200); die;
    }
      $contrato_id = $request->contrato_id;

      $itemizado['config'] = DB::table('tbl_contrato_itemizado')
        ->leftjoin('tbl_contrato_itemizado_moneda','tbl_contrato_itemizado.moneda_id','=','tbl_contrato_itemizado_moneda.moneda_id')
        ->leftjoin('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contrato_itemizado.contrato_id')
        ->leftjoin('tbl_contrato_itemizado_condicionpago','tbl_contrato_itemizado.condicionpago_id','=','tbl_contrato_itemizado_condicionpago.condicionpago_id')
        ->where('tbl_contrato_itemizado.contrato_id',$contrato_id)
        ->select('tbl_contrato_itemizado.itemizado_id','tbl_contrato_itemizado_moneda.valor as moneda','tbl_contrato_itemizado_moneda.moneda_id','tbl_contrato.cont_montoTotal','tbl_contrato_itemizado_condicionpago.valor as condicionpago','tbl_contrato_itemizado_condicionpago.condicionpago_id','tbl_contrato_itemizado.tipoLinea','tbl_contrato_itemizado.tiposDocumentos_id');

      $existeEDP = Edp::where('contrato_id',$contrato_id)->first();
      if($existeEDP){
        $edp_numero = $existeEDP->numero;
        $itemizado['config'] = $itemizado['config']->leftjoin('tbl_contrato_edp','tbl_contrato_itemizado.contrato_id','=','tbl_contrato_edp.contrato_id')->addSelect('tbl_contrato_edp.nombre_edp','tbl_contrato_edp.numero','tbl_contrato_edp.fechaIngreso');
      }else{
        $itemizado['config'] = $itemizado['config']->addSelect(\DB::raw('1 as numero'));
      }

      $itemizado['config'] = $itemizado['config']->first();

      if(!$itemizado['config']){
          array_push($itemizado['message'],'sin itemizado para contrato');
          return response()->json($itemizado,200); die;
      }

      $lineas = \DB::table('tbl_contrato_itemizado_lineas')->where('itemizado_id',$itemizado['config']->itemizado_id)->get();
      if(!$lineas){
          array_push($itemizado['message'],'sin lineas para el itemizado');
          return response()->json($itemizado,200); die;
      }

      $i=0;
      foreach ($lineas as $linea) {
        $itemized[$i]['subLineas'] = \DB::table('tbl_contrato_edp_sublineas')->where('linea_id',$linea->linea_id)->orderBy('edp_sublinea_id','asc')->get();
        $i++;
      }

      $itemizado['lineas']=$itemized;

      return response()->json($itemizado,200);

  }

  public function getListadoEDP(){

    $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
    $lcontratos = explode(',',$lobjFiltro['contratos']);

    $edps = Edp::whereIn('contrato_id',$lcontratos)->get();

    return response()->json($edps);
  }


  public function postStoreEDPFile(Request $request){

    $hoy = date('Y-m-d');
    $edp_id = $request->EDPId;

    if($request->type=='mandatoryFile'){
      $lobjArchivoDocumento = $request->file;
      if(!empty($lobjArchivoDocumento)){
        $checkFileExist = \DB::table('tbl_contrato_edp_mandatory_files')->where('edp_id',$edp_id)->where('tipoDocumento_id',$request->documentTypeId)->first();
        $destinationPath = './uploads/documents/edp/'.$edp_id."/mandatory/";
        $filename = $lobjArchivoDocumento->getClientOriginalName();
        $extension =$lobjArchivoDocumento->getClientOriginalExtension(); //if you need extension of the file
        $rand = rand(1000,100000000);
        $newfilename = strtotime(date('Y-m-d H:i:s')).'-'.$rand.'.'.$extension;
        $uploadSuccess = $lobjArchivoDocumento->move($destinationPath, $newfilename);
        if( $uploadSuccess ) {
          if($checkFileExist){
            \DB::table('tbl_contrato_edp_mandatory_files')->where('edp_id',$edp_id)->where('tipoDocumento_id',$request->documentTypeId)->update(['filename'=>$newfilename, 'updated_at'=>$hoy]);
          }else{
            \DB::table('tbl_contrato_edp_mandatory_files')->insert(['edp_id'=>$edp_id,'filename'=>$newfilename, 'created_at'=>$hoy,'tipoDocumento_id'=>$request->documentTypeId]);
          }
        }
      }
    }
    if($request->type=='lineFile'){
      $lobjArchivoDocumento = $request->file;
      if(!empty($lobjArchivoDocumento)){
        $checkFileExist = \DB::table('tbl_contrato_edp_mandatory_files')->where('edp_id',$edp_id)->where('subLinea_id',$request->subLineId+1)->where('linea_id',$request->lineId+1)->first();
        $destinationPath = './uploads/documents/edp/'.$edp_id."/optional/";
        $filename = $lobjArchivoDocumento->getClientOriginalName();
        $extension =$lobjArchivoDocumento->getClientOriginalExtension(); //if you need extension of the file
        $rand = rand(1000,100000000);
        $newfilename = strtotime(date('Y-m-d H:i:s')).'-'.$rand.'.'.$extension;
        $uploadSuccess = $lobjArchivoDocumento->move($destinationPath, $newfilename);
        if( $uploadSuccess ) {
          if($checkFileExist){
            \DB::table('tbl_contrato_edp_mandatory_files')->where('edp_id',$edp_id)->where('subLinea_id',$request->subLineId+1)->where('linea_id',$request->lineId+1)->update(['filename'=>$newfilename, 'updated_at'=>$hoy]);
          }else{
            \DB::table('tbl_contrato_edp_mandatory_files')->insert(['edp_id'=>$edp_id,'filename'=>$newfilename, 'created_at'=>$hoy,'subLinea_id'=>$request->subLineId+1,'linea_id'=>$request->lineId+1]);
          }
        }
      }
    }

    if($uploadSuccess){
      return response()->json(['status'=>'ok',200]);
    }else{
      return response()->json(['status'=>'nok',200]);
    }

  }

  public function postStoreEDP(Request $request){
    if($request->isMethod('post')){
      $user_id = \Session::get('uid');
      $hoy = date('Y-m-d H:i');
      $data = json_decode($request->json);
      $contrato_id = $data->creationForm->EDPContract->contrato_id;
      $buttonUsed = $data->buttonUsed;

      $validaContrato = \DB::table('tbl_contrato')
        ->join('tbl_contrato_itemizado','tbl_contrato.contrato_id','=','tbl_contrato_itemizado.contrato_id')
        ->where('tbl_contrato.cont_estado',1)
        ->where('tbl_contrato.contrato_id',$contrato_id)
        ->first();
      if(!$validaContrato){
        return response()->json(['message'=>"Contrato no habilitado",'status'=>'nok'],200);die;
      }

      $itemizado_id = $validaContrato->itemizado_id;
      $nombreEDP = '';
      if(isset($data->creationForm->EDPName)){
        $nombreEDP = $data->creationForm->EDPName;
      }
      $fechaIngreso = date('Y-m-d');
      $observaciones = $data->observation;
      $elevated = $data->elevated;
      $elevado = 0;
      if($elevated){
        $elevado = \Session::get('uid');
      }
      $montoTotal = $data->breakdown->totalExpense;

      $EDP = Edp::where('contrato_id',$contrato_id)->orderBy('edp_id','desc')->first();

      if($EDP){
        $numero = $EDP->numero;
      }else{
        $numero = 0;
      }

      DB::beginTransaction();

      try{

        if($buttonUsed=='Enviar'){
          /*
          $estado = self::validaEDP($data);
          if($estado['condicion']){
            return response()->json(['message'=>$estado['message'],'status'=>'nok']); die;
          }
          */
          $hoy = date('Y-m-d H:i');
          //$numero++;
          $estado=2; //enviado

          if($EDP){

            if($EDP->estado_id==1 || $EDP->estado_id==4){
              if($elevated){
                \DB::table('tbl_contrato_edp')
                  ->where('edp_id',$EDP->edp_id)
                  ->update(['nombre_edp'=>$nombreEDP,'fechaEnvio'=>$hoy,'observacion'=>$observaciones,'estado_id'=>$estado,'montoTotal'=>$montoTotal,'elevado_user_id'=>$elevado]);
              }else{
                \DB::table('tbl_contrato_edp')
                  ->where('edp_id',$EDP->edp_id)
                  ->update(['nombre_edp'=>$nombreEDP,'fechaEnvio'=>$hoy,'observacion'=>$observaciones,'estado_id'=>$estado,'montoTotal'=>$montoTotal]);
              }

              $lineasedp = \DB::table('tbl_contrato_itemizado_lineas')->where('itemizado_id',$itemizado_id)->get();
              foreach ($lineasedp as $linea) {

                $linea_id = $linea->linea_id;
                $sublineasItemizado = \DB::table('tbl_contrato_itemizado_sublineas')->where('linea_id',$linea_id)->get();

                $i=0;
                foreach ($sublineasItemizado as $sublineaItemizado) {
                  $cantidad=0;
                  if(isset($data->lines[0]->subLineas[$i]->cantidad)){
                    $cantidad = $data->lines[0]->subLineas[$i]->cantidad;
                  }

                  $sublinea_edp = \DB::table('tbl_contrato_edp_sublineas')
                    ->where('linea_id',$linea_id)
                    ->where('edp_id',$EDP->edp_id)
                    ->where('itemizado_sublinea_id',$sublineaItemizado->sublinea_id)
                    ->first();

                  if($sublinea_edp){
                    $sublinea_edp_id = \DB::table('tbl_contrato_edp_sublineas')
                      ->where('linea_id',$linea_id)
                      ->where('itemizado_sublinea_id',$sublineaItemizado->sublinea_id)
                      ->where('edp_id',$EDP->edp_id)
                      ->update([
                        'cantidad'=>$cantidad,
                        'montoLinea'=>$data->lines[0]->subLineas[$i]->valor_unitario
                      ]);
                  }else{
                    \DB::table('tbl_contrato_edp_sublineas')->insert(['linea_id'=>$linea_id,'cantidad'=>$cantidad,'montoLinea'=>$data->lines[0]->subLineas[$i]->valor_unitario,'itemizado_sublinea_id'=>$sublineaItemizado->sublinea_id,'edp_id'=>$EDP->edp_id]);
                  }
                  $i++;
                }

              }

              \DB::table('tbl_contrato_edp_adicionales')->where('edp_id',$EDP->edp_id)->delete();
              $adicionales = $data->additional;
              foreach ($adicionales as $adicional) {
                \DB::table('tbl_contrato_edp_adicionales')->insert(['edp_id'=>$EDP->edp_id,'motivo_id'=>$adicional->reason,'monto'=>$adicional->amount,'porcentaje'=>$adicional->percentage]);
              }
              \DB::table('tbl_contrato_edp_movimientos')->insert(['edp_id'=>$EDP->edp_id,'user_id'=>$user_id,'created_at'=>$hoy,'movimiento'=>"Envia EDP"]);
            }else{
              $numero++;
              $edp_id = Edp::insertGetId(['contrato_id'=>$contrato_id,'nombre_edp'=>$nombreEDP,'fechaIngreso'=>$fechaIngreso,'fechaEnvio'=>$hoy,'numero'=>$numero,'observacion'=>$observaciones,'estado_id'=>$estado,'montoTotal'=>$montoTotal,'elevado_user_id'=>$elevado]);

              $lineasedp = \DB::table('tbl_contrato_itemizado_lineas')->where('itemizado_id',$itemizado_id)->get();

              foreach ($lineasedp as $linea) {

                $linea_id = $linea->linea_id;
                $sublineasItemizado = \DB::table('tbl_contrato_itemizado_sublineas')->where('linea_id',$linea_id)->get();
                $i=0;
                foreach ($sublineasItemizado as $sublineaItemizado) {
                  $cantidad=0;
                  if(isset($data->lines[0]->subLineas[$i]->cantidad)){
                    $cantidad = $data->lines[0]->subLineas[$i]->cantidad;
                  }
                  if(!isset($sublinea_edp_id)){
                    \DB::table('tbl_contrato_edp_sublineas')->insert(['linea_id'=>$linea_id,'cantidad'=>$cantidad,'montoLinea'=>$data->lines[0]->subLineas[$i]->valor_unitario,'itemizado_sublinea_id'=>$sublineaItemizado->sublinea_id,'edp_id'=>$edp_id]);
                  }
                  $i++;
                }

              }

              \DB::table('tbl_contrato_edp_adicionales')->where('edp_id',$edp_id)->delete();
              $adicionales = $data->additional;
              foreach ($adicionales as $adicional) {
                \DB::table('tbl_contrato_edp_adicionales')->insert(['edp_id'=>$edp_id,'motivo_id'=>$adicional->reason,'monto'=>$adicional->amount,'porcentaje'=>$adicional->percentage]);
              }
            }

          }
          //fin else btn enviar
        }else{
          $estado=1; // EN preparacion

          $last_edp = \DB::table('tbl_contrato_edp')
            ->where('contrato_id',$contrato_id)
            ->orderBy('edp_id','desc')
            ->first();

          if($last_edp->estado_id==3){

            $numero = $last_edp->numero + 1;

            $edp_id = \DB::table('tbl_contrato_edp')
            ->insertGetId([
              'contrato_id'=>$contrato_id,
              'nombre_edp'=>$nombreEDP,
              'fechaIngreso'=>$fechaIngreso,
              'numero'=>$numero,
              'observacion'=>$observaciones,
              'estado_id'=>$estado,
              'montoTotal'=>$montoTotal]);
          }else{
            $edp_id = $last_edp->edp_id;
            \DB::table('tbl_contrato_edp')
              ->where('contrato_id',$contrato_id)
              ->where('edp_id',$edp_id)
              ->update(['nombre_edp'=>$nombreEDP,'fechaIngreso'=>$fechaIngreso,'observacion'=>$observaciones,'numero'=>$numero,'estado_id'=>$estado,'montoTotal'=>$montoTotal]);
          }

          \DB::table('tbl_contrato_edp_movimientos')->insert(['edp_id'=>$edp_id,'user_id'=>$user_id,'created_at'=>$hoy,'movimiento'=>"Guarda EDP"]);

          $lineasedp = \DB::table('tbl_contrato_itemizado_lineas')->where('itemizado_id',$itemizado_id)->get();

          foreach ($lineasedp as $linea) {
            $linea_id = $linea->linea_id;
            $sublineasItemizado = \DB::table('tbl_contrato_itemizado_sublineas')->where('linea_id',$linea_id)->get();

            $i=0;
            foreach ($sublineasItemizado as $sublineaItemizado) {
              $cantidad=0;
              if(isset($data->lines[0]->subLineas[$i]->cantidad)){
                $cantidad = $data->lines[0]->subLineas[$i]->cantidad;
              }

              $sublinea_edp = \DB::table('tbl_contrato_edp_sublineas')
                ->where('linea_id',$linea_id)
                ->where('edp_id',$edp_id)
                ->where('itemizado_sublinea_id',$sublineaItemizado->sublinea_id)
                ->first();

              if($sublinea_edp){
                \DB::table('tbl_contrato_edp_sublineas')
                  ->where('linea_id',$linea_id)
                  ->where('itemizado_sublinea_id',$sublineaItemizado->sublinea_id)
                  ->where('edp_id',$edp_id)
                  ->update([
                    'cantidad'=>$cantidad,
                    'montoLinea'=>$data->lines[0]->subLineas[$i]->valor_unitario
                  ]);
              }else{
                \DB::table('tbl_contrato_edp_sublineas')->insert(['linea_id'=>$linea_id,'cantidad'=>$cantidad,'montoLinea'=>$data->lines[0]->subLineas[$i]->valor_unitario,'itemizado_sublinea_id'=>$sublineaItemizado->sublinea_id,'edp_id'=>$edp_id]);
              }
              $i++;
            }

          }
          /* adicionales*/
          \DB::table('tbl_contrato_edp_adicionales')->where('edp_id',$edp_id)->delete();
          $adicionales = $data->additional;
          foreach ($adicionales as $adicional) {
            \DB::table('tbl_contrato_edp_adicionales')->insert(['edp_id'=>$edp_id,'motivo_id'=>$adicional->reason,'monto'=>$adicional->amount,'porcentaje'=>$adicional->percentage]);
          }
        }
      DB::commit();
      }catch(\Exception $e){
        DB::rollBack();
        return response()->json($e->getMessage(),400);
      }

      return response()->json(['message'=>"EDP guardado correctamente",'status'=>'ok']);

    }else{
      return response()->json('Consulta mal realizada',400);
    }
  }

  function validaEDP($data){
    $flag['condicion'] = false;
    if($data->breakdown->totalExpense==0){
      $flag['condicion'] = true;
      $flag['message']="Monto Total no puede ser cero (0)";
    }
    $edp_id = $data->edpID;
    $contrato_id = $data->creationForm->EDPContract->contrato_id;
    $obligatoryFiles = \DB::table('tbl_contrato_itemizado')->where('contrato_id',$contrato_id)->select('tiposDocumentos_id');
    $filesCount = count(explode(',',$obligatoryFiles));

    $existFilesEDP = \DB::table('tbl_contrato_edp_mandatory_files')->where('edp_id',$edp_id)->count();

    if($filesCount!=$existFilesEDP){
      $flag['condicion'] = true;
      $flag['message']="Faltan Archivos Obligatorios";
    }

    return $flag;
  }

  public function postDeleteEdp(Request $request){

    $edp_id = $request->edp_id;

    $lintGroupUser = \Session::get('gid');
    $estado = app('App\Http\Controllers\ApiFront\variosController')->checkAccessProperty($lintGroupUser,'eliminacion',null,$edp_id);

    if(!$estado){
      return response()->json(['message'=>"perfil no autorizado para eliminar",'status'=>false]); die;
    }

    $accion = Edp::whereIn('edp_id',[$request->input('edp_id')])
      ->where('estado_id','<>',self::$estados['Aceptado'])
      ->delete();
    if($accion) $message= "EDP borrado";
    else $message = "Falla al borrar EDP";

    return response()->json(['message'=>$message]);

  }


  public function postReviewConfirmation(Request $request){

    $groupid = \Session::get('gid');
    $user_id = \Session::get('uid');
    $hoy = date('Y-m-d H:i');
    $data = json_decode($request->getContent());
    $edp_id = $data->config->edp_id;

    $estado = app('App\Http\Controllers\ApiFront\variosController')->checkAccessProperty($groupid,'aprobacion',null,$edp_id);

    if(!$estado){
      return response()->json(['message'=>"perfil no autorizado",'status'=>false]); die;
    }

    if($data->buttonUsed == 'Rechazar'){
      $estado = 4; //Rechazado
      $message = "Rechazado";
      $mov = "Rechaza EDP";
    }else{
      $estado = 3;
      $message = "Aprobado";
      $mov = "Aprueba EDP";
    }

    Edp::where('edp_id',$edp_id)->update(["estado_id"=>$estado]);
    \DB::table('tbl_contrato_edp_movimientos')->insert(['edp_id'=>$edp_id,'user_id'=>$user_id,'created_at'=>$hoy,'movimiento'=>$mov]);

    return response()->json(["message"=>"EDP $message correctamente","status"=>"ok"]);
  }

  public function getDataDashboardEdp(Request $request){

    $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
    $lcontratos = explode(',',$lobjFiltro['contratos']);

    $edps = \DB::table('tbl_contrato_edp')
      ->join('tbl_contrato','tbl_contrato_edp.contrato_id','=','tbl_contrato.contrato_id')
      ->join('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
      ->join('tbl_contrato_itemizado','tbl_contrato.contrato_id','=','tbl_contrato_itemizado.contrato_id')
      ->select(\DB::raw("SUM(tbl_contrato_edp.montoTotal) as acumulado"),'tbl_contrato_edp.*','tbl_contrato.*','tbl_contratistas.*','tbl_contrato_itemizado.*')
      ->whereIn('tbl_contrato_edp.contrato_id',$lcontratos);

    if(isset($request->contrato_id)){
      $edps = $edps->where('tbl_contrato_edp.contrato_id',$request->contrato_id);
      $edps = $edps->groupBy('tbl_contrato_edp.edp_id');
    }
    else{
      $edps = $edps->groupBy('tbl_contrato.contrato_id');
    }

    $edps = $edps->get();

    $pendientes = 0;
    $aprobados = 0;
    $rechazados = 0;
    $observados = 0;
    $data['contratos']=array();
    $data['edps']=array();

    foreach ($edps as $edp) {

      array_push($data['contratos'],['contrato'=>$edp->cont_nombre, 'contratista'=>$edp->RazonSocial,'montoAcumulado'=>$edp->acumulado,'contrato_id'=>$edp->contrato_id,'nombreEdp'=>$edp->nombre_edp]);

      $presupuesto = $edp->MontoTotal;
      $mesIncioContrato = $edp->cont_fechaInicio;
      $mesFinContrato = $edp->cont_fechaFin;

    }

    $edps = \DB::table('tbl_contrato_edp')
      ->join('tbl_contrato','tbl_contrato_edp.contrato_id','=','tbl_contrato.contrato_id')
      ->join('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
      ->join('tbl_contrato_itemizado','tbl_contrato.contrato_id','=','tbl_contrato_itemizado.contrato_id')
      ->whereIn('tbl_contrato_edp.contrato_id',$lcontratos);

    if(isset($request->contrato_id)){
      $edps = $edps->where('tbl_contrato_edp.contrato_id',$request->contrato_id);
    }

    $edps = $edps->get();
    foreach ($edps as $edp) {
      if($edp->estado_id==2 or $edp->estado_id==1){
        $pendientes++;
      }
      if($edp->estado_id==3){
        $aprobados++;
      }
      if($edp->estado_id==4){
        $rechazados++;
      }
      array_push($data['edps'],['mes'=>$edp->fechaEnvio, 'aprobado'=>$edp->montoTotal]);
    }

    $fechainicial = new \DateTime($mesIncioContrato);
    $fechafinal = new \DateTime($mesFinContrato);
    $diferencia = $fechainicial->diff($fechafinal);
    $meses = ( $diferencia->y * 12 ) + $diferencia->m;

    $presupuesto = $presupuesto/$meses;

    $data['pendientes'] = $pendientes;
    $data['aprobados'] = $aprobados;
    $data['rechazados'] = $rechazados;
    $data['observados'] = $observados;
    $data['presupuesto'] = $presupuesto;
    $data['meses'] = $meses;
    $data['fechaInicio'] = $mesIncioContrato;
    $data['fechaFin'] = $mesFinContrato;

    return response()->json($data);

  }

  public function CrearArchivo($data){

    $lintIdUser = \Session::get('uid');
		$donwloadBy = \DB::table('tb_users')->where('id', $lintIdUser)->first();
		$hoy=date('Y-m-d H:i');
    $random=date('YmdHi');
    $contrato = Contratos::find($data['contrato_id']);
    $acumuladoTotal=0;
    $cantidadTotal=0;
    $acumuladoMonto = [];
    $acumuladoCantidad = [];
    if($contrato){
      $contratista = Contratistas::where('IdContratista',$contrato->IdContratista)->first();
      $contratoEdp = $contrato->edps()->where('edp_id', $data['edp_id'])->first();
      $contratoItemizado = $contrato->contratoItemizado()->first();
      $contratoItemizadoLineas = $contratoItemizado->itemizadoLineas()->with('itemizadoSublineas')->get();
      if($contratoEdp->numero > 1){
        $EdpAnteriores = $contrato->edps()->where('numero','<', $contratoEdp->numero)->get();
        $acumulado=[];
        $cantidad=[];
        foreach ($EdpAnteriores as $key => $EdpAnterior) {
          foreach ($EdpAnterior->edpSublineas as $key2 => $edpSublinea) {
            $acumuladoMonto[$key][$key2] = ($key > 0 ? $acumuladoMonto[$key-1][$key2] : 0) + ($edpSublinea->montoLinea * $edpSublinea->cantidad);
            $acumuladoCantidad[$key][$key2] = ($key > 0 ? $acumuladoCantidad[$key-1][$key2] : 0) + ($edpSublinea->cantidad);
          }
        }
        $acumuladoTotal= last($acumuladoMonto);
        $cantidadTotal= last($acumuladoCantidad);
       /* TODO: falta sacar el porcentaje por linea y los totale en la sumatoria la division sale en el excel */
        //dd($cantidad, $acumulado);
      }

      //dd(last($acumulado));
    }
    /*TODO: hay que mandar un error */
     //dd($contratoItemizadoLineas[0]['itemizadoSublineas']);
    /*foreach ($contratoItemizadoLineas[0]['itemizadoSublineas'] as $key => $itemizadoSublinea) {
      $itemizadoSublinea->edpSublinea);

    }*/
     $data = [
       'random' => $random,
       'contrato' => $contrato,
       'contratista' => $contratista,
       'contratoEdp'  => $contratoEdp,
       'contratoItemizado' => $contratoItemizado,
       'contratoItemizadoLineas' => $contratoItemizadoLineas,
       'acumuladoTotal' => $acumuladoTotal,
       'cantidadTotal' =>  $cantidadTotal,
       'acumuladoMonto' => $acumuladoMonto,
       'acumuladoCantidad' => $acumuladoCantidad,
       'entry_by' => $donwloadBy,
     ];
   	 $crearExcel = self::CrearArchivoConFormatoExcel($data);

    if($crearExcel){
      $archivo = base_path('storage/app/public/archivo_'.$random.'.xlsx');
      $archivoZip = base_path('storage/app/public/archivo_'.$random.'.zip');
      $zip = new ZipArchive();
      $res = $zip->open($archivoZip, ZipArchive::CREATE);
      if($res === true){
        $zip->addFile($archivo, 'archivo_'.$random.'.xlsx');
        $zip->close();
      }
      return $response = [
        'archivoZip' => $archivoZip,
        'usuario' => $data['entry_by'],
        'data' => $data
      ];
    }
  }
  public function CrearArchivoConFormatoExcel($data){

   /* $spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$spreadsheet->getActiveSheet()->setTitle('EDP');
		$spreadsheet->setActiveSheetIndex(0);*/
    $spreadsheet = IOFactory::load(public_path('formats/plantilla_edp.xlsx'));
    $sheet = $spreadsheet->getActiveSheet();
    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
		$drawing->setName('Logo');
		$drawing->setDescription('Logo');
		$drawing = self::ReportesLogo($drawing); //funcion que pone el logo segun el cliente
		$drawing->setCoordinates('A1');
		$drawing->setHeight(90);
		$drawing->setResizeProportional(true);
		$drawing->setOffsetX(30);
		$drawing->setWorksheet($spreadsheet->getActiveSheet());

    $sheet->getColumnDimension('A')->setWidth(15);
		$sheet->getColumnDimension('B')->setWidth(15);
		$sheet->getColumnDimension('C')->setWidth(15);
		$sheet->getColumnDimension('D')->setWidth(15);
		$sheet->getColumnDimension('E')->setWidth(15);
		$sheet->getColumnDimension('F')->setWidth(15);
		$sheet->getColumnDimension('G')->setWidth(15);
		$sheet->getColumnDimension('H')->setWidth(15);
		$sheet->getColumnDimension('I')->setWidth(15);
		$sheet->getColumnDimension('J')->setWidth(15);
		$sheet->getColumnDimension('K')->setWidth(15);
		$sheet->getColumnDimension('L')->setWidth(15);
		$sheet->getColumnDimension('M')->setWidth(15);
		$sheet->getColumnDimension('N')->setWidth(15);
		$sheet->getColumnDimension('O')->setWidth(15);
		$sheet->getColumnDimension('P')->setWidth(15);

    $sheet->setCellValue('A2', $data['contratoEdp']->nombre_edp);
    $sheet->setCellValue('C5', $data['contratista']->RazonSocial);
    $sheet->setCellValue('C8', $data['contratista']->RUT);
    $sheet->setCellValue('G5', date('Y-m', strtotime($data['contratoEdp']->fechaIngreso)));
    $sheet->setCellValue('G6', date('Y-m-d', strtotime($data['contratoEdp']->fechaIngreso)));
    $sheet->setCellValue('G7', $data['contratoItemizado']->moneda->valor);

    $j=16;
    $jInicio = $j;
    $sumaMontoLinea = 0;
    $sumaMontoTotal = 0;
    $sumaActual = 0;

    foreach ($data['contratoItemizadoLineas'] as $key => $itemizadolinea) {
      foreach ($itemizadolinea['itemizadoSublineas'] as $key2 => $sublinea) {

        $sheet->getStyle('A'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('O'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$j)->getNumberFormat()->setFormatCode('0.0');
        $spreadsheet->getActiveSheet()->getStyle('A'.$j)->getFont()->setBold(true);
        $item = $key2 === 0 ? $key + 1 : number_format((float) $key + 1 + ($key2/10) , 1, '.','');

        $sheet->setCellValue('A'.$j, $item);
        $spreadsheet->getActiveSheet()->mergeCells('B'.$j.':E'.$j);
        $sheet->setCellValue('B'.$j, $sublinea->nombre);
        $sheet->setCellValue('F'.$j, $sublinea->unidad->valor);
        $sheet->setCellValue('G'.$j, $sublinea->cantidad);
        $sheet->setCellValue('H'.$j, $sublinea->montoLinea);
        $sheet->setCellValue('I'.$j, ($sublinea->cantidad * $sublinea->montoLinea));
        $sheet->setCellValue('J'.$j, round(((($data['acumuladoTotal'][$key2]) / ($sublinea->cantidad * $sublinea->montoLinea)))*100, 2).' %');
        $sheet->setCellValue('K'.$j, $data['cantidadTotal'][$key2]);
        $sheet->setCellValue('L'.$j, $data['acumuladoTotal'][$key2]);
        $sheet->setCellValue('M'.$j, '');
        $sheet->setCellValue('N'.$j, '');
        $sheet->setCellValue('O'.$j, $sublinea->edpSublinea->cantidad);
        $sheet->setCellValue('P'.$j, ($sublinea->edpSublinea->cantidad * $sublinea->edpSublinea->montoLinea));
        $sumaMontoLinea += ($sublinea->cantidad * $sublinea->montoLinea);
        $sumaMontoTotal +=  $data['acumuladoTotal'][$key2];
        $sumaActual +=  ($sublinea->edpSublinea->cantidad * $sublinea->edpSublinea->montoLinea);
        $j++;
      }
    }
    $sheet->getStyle('A'.$jInicio.':P'.($j-1))->applyFromArray(self::Styles('allBordes'));

    $sheet->setCellValue('I'.$j, $sumaMontoLinea);
    $sheet->setCellValue('J'.$j, round(($sumaMontoTotal/$sumaMontoLinea)*100 ,2).'%');
    $sheet->setCellValue('L'.$j, $sumaMontoTotal);
    $sheet->setCellValue('P'.$j, $sumaActual);
    $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getFont()->setBold(true);
    $spreadsheet->getActiveSheet()->getStyle('J'.$j)->getFont()->setBold(true);
    $spreadsheet->getActiveSheet()->getStyle('L'.$j)->getFont()->setBold(true);
    $spreadsheet->getActiveSheet()->getStyle('P'.$j)->getFont()->setBold(true);
    $sheet->getStyle('J'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('I'.$j)->applyFromArray(self::Styles('allBordes'));
    $sheet->getStyle('J'.$j)->applyFromArray(self::Styles('allBordes'));
    $sheet->getStyle('L'.$j)->applyFromArray(self::Styles('allBordes'));
    $sheet->getStyle('P'.$j)->applyFromArray(self::Styles('allBordes'));

    $j+=2;
    $sheet->getStyle('M'.$j.':P'.$j)->applyFromArray(self::Styles('allBordes'));
    $spreadsheet->getActiveSheet()->mergeCells('M'.$j.':P'.$j);
    $sheet->getStyle('M'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue('M'.$j, $data['contratoItemizado']->moneda->valor.'  '.$sumaActual);
    $spreadsheet->getActiveSheet()->getStyle('M'.$j)->getFont()->setBold(true);

    //Aprobaciones

    //Obervaciones
    $spreadsheet->getActiveSheet()->getStyle('A'.$j)->getFont()->setBold(true);
    $sheet->setCellValue('A'.$j, 'OBSERVACIONES');
    $jinicio =($j+1);
    $j++;
    $sheet->setCellValue('A'.$j, $data['contratoEdp']->observacion);
    $sheet->getStyle('A'.$jinicio)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
    $j+=1;
    $spreadsheet->getActiveSheet()->mergeCells('A'.$jinicio.':J'.$j);
    $sheet->getStyle('A'.$jinicio.':J'.($j))->applyFromArray(self::Styles('allBordes'));

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="archivo_'.$data['random'].'.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		//$writer->save('php://output');
		$writer->save(storage_path('app/public/archivo_'.$data['random'].'.xlsx'));
	  return true;
  }

  public function ReportesLogo($drawing){
		$cliente = \DB::table('tbl_configuraciones')->where('nombre','CNF_APPNAME')->first();
		  	switch ($cliente->Valor) {
				case 'CCU':
					$drawing->setPath(base_path('public/images/ccu.png'));
					break;
				case 'Ohl Industrial':
					$drawing->setPath(base_path('public/images/logoohl.png'));
					break;
				default:
					$drawing->setPath(base_path('public/images/Logoscheck-05.png'));
					break;
			}
		 $drawing->setHeight(70);
		 //$drawing->setOffsetX(30);
		 return($drawing);
	}

	public function Styles($style){

		switch ($style) {
			case 'allBordes':
				$allBordes = [
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
							'color' => ['rgb' => '1E3246'],
						],
					],
				];
				return $allBordes;
				break;
			case 'boldSize':
				$boldSize = [
					'font' => [
						'bold' => true,
						'size' => 14,
						'color' => array('rgb' => '000000'),
					],
				];
				return $boldSize;
				break;
      }
	}

  public function postEnviarArchivoPorCorreo(Request $request){

      $emailInput = $request->email;
      $ccInpunt = $request->cc;
      $messageInpunt = $request->message;

      try {

        $data = [
          'contrato_id'   => $request->contrato_id,
          'edp_id'     => $request->edp_id,
        ];
        $response = self::CrearArchivo($data);
        $archivoZip = $response['archivoZip'];
        $email = $response['usuario'] ? $response['usuario']->email : CNF_EMAIL;
        \Mail::send('emails.edp',['data'=>$response['data'],'messageUsuario'=>$messageInpunt], function ($m) use ($archivoZip, $email,$emailInput,$ccInpunt){
          $m->from(CNF_EMAIL);
          $destinatarios = [$email];
          $m->to($emailInput);
          $m->subject("Estados de Pago");
          if(isset($ccInpunt) and $ccInpunt!=''){
            $m->cc($ccInpunt);
          }
          $m->bcc($destinatarios);
          $m->attach($archivoZip);
        });
        return response()->json([
          'status'=>'ok',200,
        ]);
      } catch (\Exception $e) {
        return response()->json($e->getMessage(),400);
      }

  }

  public function getHistorialMovimientosEdp(Request $request){
    $validator = Validator::make($request->all(), [
        'contrato_id' => 'required|numeric',
        'edp_id' => 'required|numeric'
    ]);

    if($validator->fails()){
      return response()->json(['message'=>'error']);
    }

    $contrato_id = $request->contrato_id;
    $edp_id = $request->edp_id;

    $acciones = \DB::table("tbl_contrato_edp_movimientos")
      ->join('tbl_contrato_edp','tbl_contrato_edp.edp_id','=','tbl_contrato_edp_movimientos.edp_id')
      ->where("tbl_contrato_edp.contrato_id","=",$contrato_id)
      ->where('tbl_contrato_edp_movimientos.edp_id',$edp_id)
      ->orderBy("tbl_contrato_edp_movimientos.mov_id",'desc')
      ->get();

    return response()->json($acciones);
  }

}
