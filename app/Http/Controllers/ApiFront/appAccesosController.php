<?php

namespace App\Http\Controllers\ApiFront;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class appAccesosController extends Controller
{

  public function identity(Request $request){
        $validator = Validator::make($request->all(), [
         'identity' => 'required|string',
         'type' => 'required|string',
         'user' => 'required|string',
         'device' => 'required|string'
        ]);
        if ($validator->fails()) {
         $result = array(
           "success"=>false,
           "code"=>401,
           "msg"=>"Faltan campos que son requeridos",
           "result"=>$validator->errors()
         );
         return response()->json($result, 401);
        }else{
          $data = $request->json()->all();
          $request = new \Illuminate\Http\Request();
          $request->replace(['rut' => $data['identity']]);
          $acceso = new AccesosController;
          $dato = $acceso->getUsuarioAccesoAcreditado($request);
          $response = json_decode($dato->content());
          $hoy = new \DateTime();
          $hoy = $hoy->format('Y-m-d');
          $lastAccess = \DB::table('tbl_accesos_dispositivo_app')->where('identity',$data['identity'])->where('createdAt','like',$hoy."%")->where('statusAccess','ok')->orderBy('id','desc')->first();
          $success = true;
          $code = 200;
          $msg = "Datos recuperados satisfactoriamente";
          $datos = array(
            "identityCode" => "",
            "identity" => "",
            "name" => "",
            "photo" => "",
            "typeAccess" => "",
            "statusAccess" => "",
            "statusPerson" => "",
            "reason" => array(),
            "lastAccess" => ''
          );
          $datos['identity']=$data['identity'];
          $name = \DB::table('tbl_personas')->where('RUT',$data['identity'])->first();
          if($name){
            $datos['name']=$name->Nombres." ".$name->Apellidos;
            $statusPerson = \DB::table('tbl_personas_acreditacion')->where('idpersona',$name->IdPersona)->orderBy('id','desc')->first();
            if($statusPerson){
              if($statusPerson->acreditacion!='' and $statusPerson->idestatus==1){
                $datos['statusPerson']='ok';
              }else{
                $datos['statusPerson']='nok';
              }
            }
          }
          if($response->acreditacion==1){
            $datos['statusAccess']="ok";
            if(!$name){
              $datos['statusPerson']='ok';
            }
          }else{
            $datos['statusAccess']="nok";
            if(!$name){
              $datos['statusPerson']='nok';
            }
            $datos['reason']=[$response->message];
          }

          if($lastAccess){
            $datos['lastAccess']=$lastAccess->type;
          }

          $result = array(
            "success"=>$success,
            "code"=>$code,
            "msg"=>$msg,
            "result"=> $datos
          );
        }
        return response()->json($result, 200);

    }

  public function postPush(Request $request){
      $validator = Validator::make($request->all(), [
        'identity' => 'required|string',
        'type' => 'required|string',
        'user' => 'required|string',
        'device' => 'required|string',
        'typeAccess' => 'string',
        'statusAccess' => 'required|string',
        'statusPerson' => 'required|string',
        'authorizationUser' => 'string',
        'observation' => 'string',
        'reason' => 'array',
        'createdAt' => 'required|string',
      ]);
      if ($validator->fails()) {
        $result = array(
          "success"=>false,
          "code"=>400,
          "msg"=>"Faltan campos que son requeridos",
          "result"=>$validator->errors()
        );
        return response()->json($result, 400);
      }else{
        try{
          if(!empty($request->reason)){
            $razon = $request->reason[0];
          }else{
            $razon='';
          }
          DB::table('tbl_accesos_dispositivo_app')->insert([
                'identity' => $request->identity,
                'type' => $request->type,
                'user'=> $request->user,
                'device'=> $request->device,
                'typeAccess'=> $request->typeAccess,
                'statusPerson'=> $request->statusPerson,
                'statusAccess'=> $request->statusAccess,
                'autorizationUser'=> $request->authorizationUser,
                'observation'=> $request->observation,
                'reason'=> $razon,
                'createdAt' => $request->createdAt
          ]);
          $success=true;
        }catch(\Exception $e){
          $success=false;
          \Log::info($e);
        }
        $result = array(
          "success"=>$success,
          "code"=>200,
          "msg"=>"Se realizó el registro satisfactoriamente",
          "result"=> array()
        );
        return response()->json($result, 200);
      }

  }

  public function postBranchesList(Request $request){
    $objfaenas = \DB::table('tbl_centro')
    ->select('descripcion')
    ->get();

    $faenas = collect($objfaenas)->map(function($item){
      return $item->descripcion;
    });

    $response = array(
      "success" => true,
      "code" => 200,
      "msg" => "Lista de faenas recuperada satisfactoriamente",
      "result" => array(
      "data" => $faenas
      )
    );
    return response()->json($response, 200);

  }

  public function postCounters(Request $request){
      $validator = Validator::make($request->all(), [
        'user' => 'required|string',
        'device' => 'required|string',
      ]);
      if ($validator->fails()) {
        $response = array(
          "success"=>false,
          "code"=>401,
          "msg"=>"Faltan campos que son requeridos",
          "result"=>$validator->errors()
        );
        return response()->json($response, 401);
      }else{
        // Se registran los contadores
        try{
          foreach ($request->counters as $counter) {
            DB::table('tbl_accesos_dispositivo_app_counters')->insert([
                  'user' => $request->user,
                  'device' => $request->device,
                  'countersOk'=> $counter['inSuccess'],
                  'countersNok'=> $counter['inUnsuccess'],
                  'counterOutOk'=> $counter['outSuccess'],
                  'createdAt'=> $counter['date']
            ]);
          }
          $success=true;
        }catch(\Exception $e){
          $success=false;
          \Log::info($e);
        }
        $response = array(
          "success"=>$success,
          "code"=>200,
          "msg"=>"Se registraron los contadores correctamente.",
          "result"=> array()
        );
        return response()->json($response, 200);
      }

  }

  public function postPull(Request $request){
      $validator = Validator::make($request->all(), [
        'user' => 'required|string',
        'device' => 'required|string',
        'lastSync' => 'string'
      ]);

      if ($validator->fails()) {
        $result = array(
          "success"=>false,
          "code"=>400,
          "msg"=>"Faltan campos que son requeridos",
          "result"=>$validator->errors()
        );
        return response()->json($result, 400);
      }else{
        $data = $request->json()->all();
        $date = new \DateTime();

        $personas = \DB::table('tbl_personas')
                  ->join('tbl_contratos_personas','tbl_personas.IdPersona','=','tbl_contratos_personas.IdPersona')
                  ->get();

        $i=0;
        $result[$i]['identityCode']=0;
        $result[$i]['identity']="";
        $result[$i]['name']="";
        $result[$i]['typeAccess']="";
        $result[$i]['statusAccess']="";
        $result[$i]['statusPerson']="";
        $result[$i]['reason']=array();

        if($personas){
          foreach ($personas as $persona) {
            $request = new \Illuminate\Http\Request();
            $request->replace(['rut' => $persona->RUT]);
            $acceso = new AccesosController;
            $dato = $acceso->getUsuarioAccesoAcreditado($request);
            $response = json_decode($dato->content());
            $result[$i]['identityCode']=1;
            $result[$i]['identity']=$persona->RUT;
            $result[$i]['name']=$persona->Nombres." ".$persona->Apellidos;
            $result[$i]['typeAccess']="";
            $statusPerson = \DB::table('tbl_personas_acreditacion')->where('idpersona',$persona->IdPersona)->orderBy('id','desc')->first();
            if($statusPerson){
              if($statusPerson->acreditacion!='' and $statusPerson->idestatus==1){
                $datos['statusPerson']='ok';
              }else{
                $datos['statusPerson']='nok';
              }
            }
            if($response->acreditacion==1){
              $result[$i]['statusAccess']="ok";
            }else {
              $result[$i]['statusAccess']="nok";
              $result[$i]['reason']=[$response->message];
            }
            $i++;
          }
        }

        $activos = \DB::table('tbl_activos')
          ->join('tbl_activos_data','tbl_activos.IdActivo','=','tbl_activos_data.IdActivo')
          ->join('tbl_contrato','tbl_activos_data.contrato_id','=','tbl_contrato.contrato_id')
          ->join('tbl_activos_data_detalle','tbl_activos_data_detalle.IdActivoData','=','tbl_activos_data.IdActivoData')
          ->select("tbl_activos_data_detalle.Valor")
          ->where('tbl_contrato.cont_estado','!=',2)
          ->groupBy('tbl_activos_data_detalle.IdActivoData')
          ->get();

        if($activos){
          foreach ($activos as $activo) {
            $request = new \Illuminate\Http\Request();
            $request->replace(['rut' => $activo->Valor]);
            $acceso = new AccesosController;
            $dato = $acceso->getUsuarioAccesoAcreditado($request);
            $response = json_decode($dato->content());
            $result[$i]['identityCode']=1;
            $result[$i]['identity']=$activo->Valor;
            $result[$i]['name']=$activo->Valor;
            $result[$i]['typeAccess']="";
            if($response->acreditacion==1){
              $result[$i]['statusAccess']="ok";
              $result[$i]['statusPerson']="ok";
            }else {
              $result[$i]['statusAccess']="nok";
              $result[$i]['statusPerson']="nok";
              $result[$i]['reason']=[$response->message];
            }
            $i++;
          }
        }

        $response = array(
          "success" => true,
          "code" => 200,
          "msg" => "Lista recuperada satisfactoriamente",
          "result" => array(
            "lastSync" => $date,
            "count" => count($result),
            "data" => $result
          )
        );
        return response()->json($response, 200);
      }

  }

}
?>
