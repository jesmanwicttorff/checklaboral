<?php

namespace App\Http\Controllers\ApiFront;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Core\Users;
use App\Models\Core\Groups;
use App\Models\Contratos;
use App\Models\Assoccgroup;
use App\Models\Assocc;
use Validator, Input, Redirect ;
use Mail;
use App\Traits\Helper;

class usuariosController extends Controller
{
	use Helper;

	public $module 	= 'users';

	public function __construct()	{
		parent::__construct();
			$this->model = new Users();
			$this->info = $this->model->makeInfo( $this->module);
			$this->access = $this->model->validAccess($this->info['id']);
	}

	public function getList(){

		$users = $this->model->all();
		$list = [];
		if($this->access['is_view'] != 1){
			return response()->json([
				'success' => false,
				'code' => 401,
				'message' => 'No tienes Acceso a esta pagina '
			],401);
		}
		if($users){
				foreach ($users as $key => $user) {
						$list[$key] = [
								'id' => $user->id,
								'Grupo' => isset($user->group) ? $user->group->name : NULL,
								'Nombre' => isset($user->first_name) ? $user->first_name : NULL,
								'Apellido' => isset($user->last_name) ? $user->last_name: null,
								'Email' => isset($user->email) ? $user->email : null,
								'estado' => $user->active == 1  ? 'Activo' : 'Inactivo',
								'FechaEditado' => isset($user->updated_at) ? $this->cambiarFechaDeBaseParaFront($user->updated_at) : null,
								'FechaCreado' => isset($user->created_at) ? $this->cambiarFechaDeBaseParaFront($user->created_at) : null,
						];
				}
				return response()->json([
					'success'=> true,
					'code'=> 200,
					'data' => $list,
					'accesos' => $this->access,
					'message' => 'Lista de usuarios enviada correctamente'
				]);

		}else{
			return response()->json([
				'success' => false,
				'code' => 400,
				'data' => null,
				'accesos' => $this->access,
				'message' => 'Error en la consulta, no se encontraron registros de usuarios'
			],400);
		}
	}

	public function eliminarUsuario(request $request){

		$data = json_decode($request->getContent());
		$lintGroupId = \Session::get('gid');
		if(count($data->ids) >=1){
			if($lintGroupId == 1) {
				\DB::table('tb_users')->whereIn('id',$data->ids)->delete();
				return response()->json(array(
						'status'=>'success',
						'code'=> 200,
						'ids'=>$data->ids,
						'message'=> 'Usuarios eliminados exitosamente!'
				));
			}else{
				return response()->json(array(
					'status'=>'error',
					'code'=> 401,
					'message'=> 'No tiene permisos para eliminar'
				));
			}
		}else{
			return response()->json(array(
				'status'=>'error',
				'code'=> 400,
				'message'=> 'Debe seleccionar al menos un usuario'
			));
		}
	}

	public function datosCrearUsuario(){

		$groups = Groups::select('group_id','name')->get();
		$contratos = Contratos::select('contrato_id','cont_nombre','cont_numero','cont_proveedor')->get();
		$data = [
			'groups' => $groups,
			'contratos' => $contratos
		];

		return response()->json(array(
			'status'=>'success',
			'code'=> 200,
			'data'=>$data,
			'message'=> 'Data enviada correctamente'
		));
		
	}

	public function CrearYEditarUsuario(Request $request){
		
		/*if($this->access['is_add'] != 1 || $this->access['is_edit'] != 1){
			return response()->json([
				'success' => false,
				'code' => 401,
				'message' => 'No tienes Acceso a esta pagina '
			],401);
		}*/
		$user = Users::find($request->input('id'));
		$request->all();
		$assocData = [
			'contratos' => $request->to,
			'group' => $request->group_id,
			'subgroup' => $request->subgroup_id
		];
		$rules = $this->validateForm();
		$rules['email'] = 'required|email|unique:tb_users,email,'.$request->input('id');
		$rules['first_name'] = 'required|string';
		$rules['last_name'] = 'required|string';
		$rules['active'] = 'required|boolean';
		$rules['username'] = 'required|string|unique:tb_users,username,'.$request->input('id');
		if($request->input('password') !=''){
			$rules['password'] ='required|between:6,20|confirmed';
			$rules['password_confirmation'] ='required|between:6,20';			
		}
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			
			$data = $this->validatePost('tb_users');
			if(is_null($user)){
				$data['password'] = \Hash::make(Input::get('password'));
			}else{
				if(Input::get('password') !=''){
					$data['password'] = \Hash::make(Input::get('password'));
				} else {
					unset($data['password']);
				}
			}
			if(is_null($user)){
				$saveMethod = self::crearUsuario($data);
			}else{
				$saveMethod = self::editarUsuario($data, $user);
			}
			if($saveMethod['success']){
				if(self::associacionUsuario($assocData, $saveMethod['id'], $saveMethod['entry_by'] )){
					return response()->json(array(
						'status'=>'success',
						'code'=> 200,
						'data'=> $saveMethod['id'],
						'message'=> $saveMethod['mensaje']
					));
				}else{
					return response()->json([
						'success' => false,
						'code' => 400,
						'message' => '¡UPS! ha Ocurrio un error al asociar al usuario, comuniquese con el administrador del sistema'
					],400);
				}
				
			}else{
				return response()->json([
					'success' => false,
					'code' => 400,
					'message' => '¡UPS! ha Ocurrio un error al insertar los datos, comuniquese con el administrador del sistema'
				],400);
			}
		}else{
			if ($validator->fails()){
				return response()->json([
					'success' => false,
					'code' => 400,
					'message' => 'Error de validacion de formulario',
					'errors' => $validator->errors()
					//$this->validateListError(  $validator->getMessageBag()->toArray() )
				],400);
			}
		}
		
	}

	public function crearUsuario($data){

		$userSave = $this->model;
		$data['entry_by'] = 1 /* TODO: Session::get('uid')*/;
		$id = $userSave->insertGetId($data);
		$mensaje = 'Se creo el usuario correctamente';
		if($id){
			return [
				'mensaje' => $mensaje,
				'id' => $id,
				'entry_by' => $data['entry_by'],
				'success' => true
			];
		}else{
			return [
				'success' => false
			];
		}
	}

	public function editarUsuario($data, $user){

		$userSave = Users::find($user->id);
		$data['entry_by'] = $user->entry_by;
		$data['id'] = null;
		$mensaje = 'Se Modifico el usuario correctamente';
		if($userSave->update($data)){
			return [
				'mensaje' => $mensaje,
				'id' => $user->id,
				'entry_by' => $data['entry_by'],
				'success' => true
			];
		}else{
			return [
				'success' => false
			];
		}
	}

	public function associacionUsuario($assocData, $id, $entryBy){

		$contratos = $assocData['contratos'];
		$assoccgroup = new Assoccgroup;
		$Idassoccgroup = $assoccgroup->insertGetId(['group_id' => $assocData['group'], 'subgroup_id' => $assocData['subgroup'], 'entry_by' => 1 /* TODO: Session::get('uid')*/]);
		$assocc =  new Assocc;
		$permisos = $assocc->where('user_id', $id)->get();

		if (count($permisos)>0 ){
			// Almaceno el resulrado de la consulta en un vector
			foreach ($permisos as $value){
			$array[] = $value->contrato_id;
		}
		if (!(empty($contratos))){
			foreach ($array as $valor) {
				if (!(in_array($valor, $contratos)))
					\DB::table('tb_assocc')->where('contrato_id', '=', $valor)->where('user_id', '=', $id)->delete();
			}
		}else{
			\DB::table('tb_assocc')->where('user_id', '=', $id)->delete();
		}

		}
		if (!(empty($contratos))){
			foreach ($contratos as $valor) {	
				if (count($permisos)>0 ){
					if (!(in_array($valor, $array))){
						$contratista =  \DB::table('tbl_contrato')
						->select('IdContratista')
						->where('contrato_id', '=', $valor)
						->get();

						\DB::table('tb_assocc')->insertGetId([
							'idAssoccGroup' => $Idassoccgroup,
							'user_id' => $id,
							'contrato_id' => $valor,
							'contratista_id' => $contratista[0]->IdContratista,
							'entry_by' => $entryBy
						]);

					}
				}else{
					$contratista =  \DB::table('tbl_contrato')
						->select('IdContratista')
						->where('contrato_id', '=', $valor)
						->get();

					\DB::table('tb_assocc')->insertGetId([
						'idAssoccGroup' => $Idassoccgroup,
						'user_id' => $id,
						'contrato_id' => $valor,
						'contratista_id' => $contratista[0]->IdContratista,
						'entry_by' => $entryBy
					]);
				}
			}
		}
		return true;

	}
}
