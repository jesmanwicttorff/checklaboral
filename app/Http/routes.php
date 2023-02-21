<?php

use App\Library\MyRequest;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/



/*API ACREDITACION */
Route::get('acreditacion/api/v1', 'AccesosController@getUsuarioAccesoAcreditado');
Route::post('acreditacion/api/data', 'AccesosController@postDataAccesosFaena');
/* FIN API */

Route::get('/', 'HomeController@index');
Route::controller('home', 'HomeController');

Route::controller('/user', 'UserController');
include('pageroutes.php');
include('vueroutes.php');
include('moduleroutes.php');

Route::get('/restric', function () {

	return view('errors.blocked');
});

Route::resource('sximoapi', 'SximoapiController');
Route::group(['middleware' => 'auth'], function () {

	Route::get('core/elfinder', 'Core\ElfinderController@getIndex');
	Route::post('core/elfinder', 'Core\ElfinderController@getIndex');
	Route::controller('/dashboard', 'DashboardController');
	Route::controllers([
		'core/users'		=> 'Core\UsersController',
		'notification'		=> 'NotificationController',
		'core/logs'			=> 'Core\LogsController',
		'core/pages' 		=> 'Core\PagesController',
		'core/groups' 		=> 'Core\GroupsController',
		'core/template' 	=> 'Core\TemplateController',
	]);

	Route::get('procesaf301/{fecha}', function ($fecha) {

		$lintIdUser = \Session::get('uid');
		if ($lintIdUser == 1) { //si es superadmin

			$lobjDocumentosF301 = \DB::table('tbl_documentos')
				->where('IdTipoDocumento', 1)
				->where('FechaEmision', $fecha)
				->where('IdEstatus', 5)
				->get();

			foreach ($lobjDocumentosF301 as $larrDocumentosF301) {

				$lobjDocumentos = (new MyRequest($larrDocumentosF301->IdDocumento))->getClass();
				$larrResult = $lobjDocumentos->approve();
			}
		}
	});
});

Route::group(['middleware' => 'auth', 'middleware' => 'sximoauth'], function () {

	Route::controllers([
		'sximo/menu'		=> 'Sximo\MenuController',
		'sximo/config' 		=> 'Sximo\ConfigController',
		'sximo/module' 		=> 'Sximo\ModuleController',
		'sximo/tables'		=> 'Sximo\TablesController',
		'sximo/code'		=> 'Sximo\CodeController'
	]);
});

Route::group(['namespace' => 'ApisLogin', 'prefix' => 'api/v1' ], function(){
	Route::post('/user/login','LoginController@postSigninApi');
	Route::post('/user/validate', 'LoginController@postValidate');
});

Route::group(['namespace' => 'ApiFront', 'prefix' => 'api/v1' ], function(){
	Route::post('/branches/list','appAccesosController@postBranchesList');
});

//apis app movil
Route::group(['middleware' => ['auth:api'], 'namespace' => 'ApiFront', 'prefix' => 'api/v1' ], function(){
	Route::post('/search/identity','appAccesosController@identity');
	Route::post('/push','appAccesosController@postPush');
	Route::post('/pull/counters','appAccesosController@postCounters');
	Route::post('/pull','appAccesosController@postPull');
});
/*
inicializa maestro movil 20-08-2020

por definicion:
1) en el maestro una persona está finiquitada si su finiquito está aprobado pero debe algún papel
2) en el maestro baja observada es cuando el finiquito está en estado distinto de 5
3) se toma el inicio desde la fecha 2019-01-01
4) está funcion solo se utiliza para inicializar, luego el maestro es alterado con el uso de la plataforma (MyPeoples.php, MyDocuments.php)
*/

Route::get('inicializamaestro',function(){
    \DB::table("tbl_personas_maestro_movil")->truncate();
    $periodo = new \DateTime('2019-01-01');
    $fechaActual = new \DateTime();
  $ldatInterval = $fechaActual->diff($periodo);
	$meses = ($ldatInterval->format("%Y")* 12 + $ldatInterval->format("%m"));
	$idtipodocfiniquito = \DB::table('tbl_tipos_documentos')->where('IdProceso',4)->value('IdTipoDocumento');

	for($i=1;$i<=($meses+1);$i++) {
		$personas = \DB::table('tbl_personas')
			->join('tbl_movimiento_personal','tbl_personas.IdPersona','=','tbl_movimiento_personal.IdPersona')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_movimiento_personal.contrato_id')
			->where('tbl_movimiento_personal.IdAccion',1)
			->where('tbl_movimiento_personal.FechaEfectiva','<=',$periodo->format('Y-m-31'))
			->where('tbl_contrato.cont_fechaFin','>',$periodo->format('Y-m-01'))
			->where('tbl_contrato.ContratoPrueba','<>',1)
			->groupBy('tbl_personas.IdPersona','tbl_movimiento_personal.contrato_id')
			->get();
		foreach ($personas as $persona) {
			$estadoFiniquito = \DB::table('tbl_documentos_rep_historico')
				->join('tbl_movimiento_personal','tbl_movimiento_personal.IdPersona','=','tbl_documentos_rep_historico.identidad')
				->where('tbl_documentos_rep_historico.IdTipoDocumento',$idtipodocfiniquito)
				->where('tbl_movimiento_personal.IdAccion',2)
				->where('tbl_documentos_rep_historico.identidad',$persona->IdPersona)
				->where('tbl_documentos_rep_historico.contrato_id',$persona->contrato_id)
				->where('tbl_movimiento_personal.contrato_id',$persona->contrato_id)
				->where('tbl_movimiento_personal.FechaEfectiva','<=',$periodo->format('Y-m-31'))
				->first();
			$estadoUltimoMovimiento = \DB::table('tbl_movimiento_personal')->where('IdPersona',$persona->IdPersona)->where('contrato_id',$persona->contrato_id)->orderBy('IdMovimientoPersonal','desc')->limit(1)->first();
			$debePapeles = \DB::table('tbl_documentos')->where('IdEntidad',$persona->IdPersona)->where('Entidad',3)->where('contrato_id',$persona->contrato_id)->where('IdEstatus','<>',5)->first();
			if($estadoFiniquito and $persona->FechaEfectiva <= $periodo->format('Y-m-31')){
				if($estadoUltimoMovimiento->IdAccion==1){
					if($estadoUltimoMovimiento->FechaEfectiva>=$estadoFiniquito->FechaEfectiva){
						\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva]);
					}
				}else{
					if($debePapeles){
						\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$estadoFiniquito->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Finiquitado','FechaEfectiva'=>$estadoFiniquito->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
					}else{
						$check = \DB::table('tbl_personas_maestro_movil')->where('Estatus','Finiquitado')->where('idpersona',$persona->IdPersona)->where('contrato_id',$estadoFiniquito->contrato_id)->first();
						if(!$check){
							\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$estadoFiniquito->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Finiquitado','FechaEfectiva'=>$estadoFiniquito->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
						}
					}
				}
			}else{
				$estadoFiniquito = \DB::table('tbl_documentos')
					->join('tbl_movimiento_personal','tbl_movimiento_personal.IdPersona','=','tbl_documentos.identidad')
					->where('tbl_documentos.IdTipoDocumento',$idtipodocfiniquito)
					->where('tbl_movimiento_personal.IdAccion',2)
					->where('tbl_documentos.identidad',$persona->IdPersona)
					->where('tbl_documentos.contrato_id',$persona->contrato_id)
					->where('tbl_movimiento_personal.FechaEfectiva','<=',$periodo->format('Y-m-31'))
					->first();

					if($estadoFiniquito){
						if($estadoFiniquito->IdEstatus==5){
							if($estadoUltimoMovimiento->IdAccion==1){
								\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva]);
							}else{
								if($debePapeles){
										\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Finiquitado','FechaEfectiva'=>$estadoFiniquito->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
								}else{
									$check = \DB::table('tbl_personas_maestro_movil')->where('Estatus','Finiquitado')->where('idpersona',$persona->IdPersona)->where('contrato_id',$estadoFiniquito->contrato_id)->first();
									if(!$check){
										\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$estadoFiniquito->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Finiquitado','FechaEfectiva'=>$estadoFiniquito->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
									}
								}
							}
						}else{
							$contratosPersona = \DB::table('tbl_contratos_personas')->where('IdPersona',$persona->IdPersona)->where('contrato_id',$persona->contrato_id)->first();
							if($estadoUltimoMovimiento->IdAccion==1){
								if($estadoUltimoMovimiento->FechaEfectiva>=$estadoFiniquito->FechaEfectiva and $contratosPersona){
									if($persona->FechaEfectiva=='0000-00-00'){
										$fechaInicioFaena=$contratosPersona->FechaInicioFaena;
									}else{
										$fechaInicioFaena = $persona->FechaEfectiva;
									}
									\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$fechaInicioFaena,'FechaInicioFaena'=>$fechaInicioFaena]);
								}else{
									\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Baja Observada','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
								}
							}else{
								if($estadoUltimoMovimiento->FechaEfectiva>=$periodo->format('Y-m-01')){
									if($contratosPersona){
										if($persona->FechaEfectiva=='0000-00-00'){
											$fechaInicioFaena=$contratosPersona->FechaInicioFaena;
										}else{
											$fechaInicioFaena = $persona->FechaEfectiva;
										}
									}else{
										$fechaInicioFaena = $persona->FechaEfectiva;
									}
									\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$fechaInicioFaena,'FechaInicioFaena'=>$fechaInicioFaena]);
								}else{
									\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Baja Observada','FechaEfectiva'=>$estadoFiniquito->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
								}
							}
						}
					}else{
						$contratosPersona = \DB::table('tbl_contratos_personas')->where('IdPersona',$persona->IdPersona)->where('contrato_id',$persona->contrato_id)->first();
						if($estadoUltimoMovimiento->IdAccion==1){
							if($contratosPersona){
									\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva]);
							}else{
									\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Baja Observada','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$persona->FechaEfectiva]);
							}
						}else{
							if($estadoUltimoMovimiento->FechaEfectiva>=$periodo->format('Y-m-31')){
								\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva]);
							}else{
								\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Baja Observada','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$persona->FechaEfectiva]);
							}
						}
					}
			}
		}
		$periodo = $periodo->modify('+1 month');
	}

	echo "ok";
});

include('apisfront.php');
