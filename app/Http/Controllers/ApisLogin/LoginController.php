<?php

namespace App\Http\Controllers\ApisLogin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Validator;
use App\Models\Contacto;
use Input;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Mail;
use DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    //APIS new dashboard

    Public function postSigninApi( Request $request){
      $data = $request->json()->all();
      if(isset($data['user']) && isset($data['password'])){

        $jwt_token = null;
    		if (!$jwt_token = JWTAuth::attempt(['email'=>$data['user'],'password'=>$data['password'],'active'=>1])) {
    			return  response()->json([
    				'success' => false,
    				'message' => 'El usuario no pudo ser autenticado',
            'code' => '401.1'
    			], 401);
    		}

        $row = \DB::table('tb_users')
            ->join('tb_groups','tb_users.group_id','=','tb_groups.group_id')
            ->where('tb_users.email','=', $data['user'])
            ->first();

            \Session::put('uid', $row->id);
            \Session::put('gid', $row->group_id);
            if(\Session::get('lang') =='')
              {
                \Session::put('lang', 'es');
              }
        return  response()->json(["success" => true, 'code' => '200', 'message' => 'El usuario fue autenticado satisfactoriamente', 'result'=>['token'=>$jwt_token,'name'=>$row->first_name,'roles'=>$row->name]]);
      }else{
        return response()->json(["success" => false, 'code' => '400', 'message' => 'Se esperaban campos requeridos'],400);
      }
    }

    public function postValidate(Request $request){
      $head = $request->header('Authorization');
      if(isset($head)){
        return  response()->json(["success" => true, 'code' => '200', 'message' => 'El usuario se encuentra autenticado'],200);
      }else{
        return  response()->json(["success" => false, 'code' => '401', 'message' => 'El usuario no pudo ser autenticado'],401);
      }
    }

    public function lostPassword(Request $request) {

        $data = $request->json()->all();
        if(isset($data['user'])){
            $user = User::where('email', $data['user'])->first();
            if($user){
                $token = Str::random(64);
                $user->reminder = $token;
                if($user->save()){

                        $mail = Mail::send('user.emails.auth.passwordReminder', ['token' => $token, 'email' => $user->email], function ($m) use ($user){
                        $m->from(CNF_EMAIL);
                        $destinatarios = $user->email;
                        $m->to($destinatarios);
                        $m->subject("Correo de recuperacion de Contraseña");
                    });
                    if($mail){
                        return response()->json([
                            "success" => true,
                            "code" => 200,
                            "message" => "Se envió el email satifactoriamente con el token y link de reinicio de clave."
                        ],200);
                    }
                }
            }else{
                return response()->json([
                    "success" => false,
                    "code" => 400,
                    "message" => "No se encontró ningún usuario."
                ],400);
            }
        }else{
            return response()->json([
                "success" => false,
                "code" => 400,
                "message" => "Se esperaban campos requeridos"
            ],400);
        }
    }

    public function resetPassword(Request $request){

        $data = $request->json()->all();

        if(isset($data['user']) && isset($data['password']) && isset($data['token']) && isset($data['passwordConfirmation']) ){
            $user = User::where('email', $data['user'])->first();
            if($user){
                $validacion = Validator::make($data, [
                    'password' => 'required_with:passwordConfirmation|same:passwordConfirmation|min:4',
                    'passwordConfirmation' => 'required'
                ]);
                if(!$validacion->fails()){

                    if($data['token'] === $user->reminder){
                        $user->password = Hash::make($data['password']);
                        if($user->save()){
                            return response()->json([
                                "success" => true,
                                "code" => 200,
                                "message" => "Se reinició la clave satifactoriamente.."
                            ],200);
                        }else{
                            return response()->json([
                                "success" => false,
                                "code" => 400,
                                "message" => "Error al insertar."
                            ],400);
                        }
                    }else{
                        return response()->json([
                            "success" => false,
                            "code" => 400,
                            "message" => "El token de reinicio no se encuentra o no pertenece al usuario."
                        ],400);
                    }
                }else{
                    return response()->json([
                        "success" => false,
                        "code" => 400,
                        "message" => "La confirmación de la clave es incorrecta."
                    ],400);
                }
            }else{
                return response()->json([
                    "success" => false,
                    "code" => 400,
                    "message" => "No se encontró ningún usuario."
                ],400);
            }
        }else{
            return response()->json([
                "success" => false,
                "code" => 400,
                "message" => "Se esperaban campos requeridos"
            ],400);
        }

    }

    public function dashboard(){

        $loginConf = DB::table('tbl_login_conf')->first();

        if($loginConf){
            $background = $loginConf->background;
            if($background){
                return response()->json([
                    "success" => true,
                    "code" => 200,
                    "message" => "Se recibe la imagen satisfactoriamente.",
                    "result" => ["background" => $background]
                ],200);
            }
        }else{
            return response()->json([
                "success" => false,
                "code" => 404,
                "message" => "No se encontró la imagen"
            ],404);
        }
    }

    public function support(){

        $loginConf = DB::table('tbl_login_conf')->first();

        if($loginConf){
            $supportEmail = $loginConf->supportEmail;
            $supportPhone = $loginConf->supportPhone;
            return response()->json([
                "success" => true,
                "code" => 200,
                "message" => "Se recuperan los datos de contacto del cliente..",
                "result" => ["supportEmail" => $supportEmail , 'supportPhone' => $supportPhone ]
            ],200);
        }else{
            return response()->json([
                "success" => false,
                "code" => 404,
                "message" => "No se encontró los datos de contacto del cliente"
            ],404);
        }
    }

    public function contact(Request $request){
        $data = $request->json()->all();
        if(isset($data['name']) &&  isset($data['email']) && isset($data['message'])){
            $validacion = Validator::make($data, [
                'name' => 'required|min:3',
                'email' => 'required|min:3',
                "message" => 'required|min:5',
            ]);
            if(!$validacion->fails()){
                $contacto = new Contacto;
                $contacto->nombre = $data['name'];
                $contacto->email = $data['email'];
                $contacto->message = $data['message'];
                if($contacto->save()){
                    return response()->json([
                        "success" => true,
                        "code" => 200,
                        "message" => "Su mensaje ha sido enviado satisfactoriamente"
                    ],200);
                }else{
                    return response()->json([
                        "success" => false,
                        "code" => 400,
                        "message" => "Error al enviar el mensaje"
                    ],400);
                }
            }else{
                return response()->json([
                    "success" => false,
                    "code" => 400,
                    "message" => "Error en los datos enviados"
                ],400);
            }
        }else{
            return response()->json([
                "success" => false,
                "code" => 400,
                "message" => "Se esperaban campos requeridos"
            ],400);
        }
    }

}
