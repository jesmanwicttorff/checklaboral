<?php
	Route::group(['namespace' => 'ApiFront', 'prefix' => 'apifront/v1' ], function(){
	Route::get('/menu','variosController@getMenuVue');
	Route::get('/dashcontratos','contratosController@dataDash');
	Route::get('/listadoContratos','contratosController@listadoContratos');
	Route::post('/borrarContratos','contratosController@postDelete');
	Route::get('/listadoContratistas','contratosController@listadoContratistas');
	Route::get('/listadoGrupos','contratosController@listadoGrupos');
	Route::post('/listadoServicios','contratosController@listadoServicios');
	Route::get('/getMandante','variosController@getMandante');
	Route::get('/getGarantias','contratosController@getGarantias');
	Route::get('/getCentros','contratosController@getCentros');
	Route::get('/getGerencia','contratosController@getGerencia');
	Route::get('/getSubGerencia','contratosController@getSubgerencia');
	Route::get('/getADC','contratosController@getADC');
	Route::get('/getTipoGasto','contratosController@getTipoGasto');
	Route::get('/getClaseCosto','contratosController@getClaseCosto');
	Route::get('/getExtension','contratosController@getExtension');
    Route::get('/getCSFR','variosController@getCSFR');
	Route::get('/creaContrato','contratosController@getCreaContrato');
	Route::get('/contratos/getPeoples','contratosController@getPeoples');
	Route::get('/getAccionesContrato','contratosController@getAccionesContrato');
	Route::post('/postDocumentosPendientes','contratosController@postDocumentosPendientes');
	Route::post('/contratos/finiquitar','contratosController@postFiniquitarContrato');
	Route::post('/contratos/cambiar','contratosController@postCambioContractual');
	Route::post('/documentos/postSaveDoc','documentosController@postSaveDoc');
	Route::post('/contrato/saveapi','contratosController@postSave');
	Route::get('/documentos/getTiposDocumentos','documentosController@getTiposDocumentos');
	Route::get('/itemizado/getTiposDocumentos','edpController@getTiposDocumentosEDP');
	Route::post('/contrato/itemizado','itemizadoController@postStoreItemizado');
	Route::post('/contrato/postMontoTotal','contratosController@postMontoTotal');
	Route::post('/contrato/buscar','variosController@postBuscar');
	Route::get('/contrato/itemizado/moneda','itemizadoController@getMoneda');
	Route::get('/contrato/itemizado/condicionPago','itemizadoController@getCondicionPago');
	Route::get('/itemizado/lineas','itemizadoController@getItemizadoLineas');
	Route::get('/edp/check','edpController@getCheckEDP');
	Route::get('/edp/lineas','edpController@getEdpLineas');
	Route::get('/edp/listado','edpController@getListadoEDP');
	Route::post('/edp/saveEDP','edpController@postStoreEDP');
	Route::post('/edp/saveEDPFile','edpController@postStoreEDPFile');
	Route::post('/edp/enviarArchivoPorCorreo','edpController@postEnviarArchivoPorCorreo');
	Route::post('/documents/attr','contratosController@getUploadsAttr');
	Route::get('/itemizado/motivos','itemizadoController@getItemizadoMotivos');
	Route::get('/itemizado/types','itemizadoController@getItemizadoType');
	Route::post('/itemizado/StoreItemizadoAdicional','itemizadoController@postStoreItemizadoAdicional');
	Route::get('/itemizado/getAdicionales','itemizadoController@getAdicionales');
	Route::get('/itemizado/unidadMedida','itemizadoController@getUnidadMedida');
	Route::post('/edp/deleteEdp','edpController@postDeleteEdp');
	Route::get('/perfiles','variosController@getPerfiles');
	Route::get('/perfiles/aprobacion','variosController@getPeoplesCanApprove');
	Route::post('/contrato/storePeople','contratosController@postStorePeople');
	Route::get('/inicializa/itemizado','variosController@normalizeItemizado');
	Route::get('/contratos/getactivepeople','contratosController@getActivePeople');
	Route::post('/edp/revisar','edpController@getEDPRevisar');
	Route::get('/agrupaItemizado','contratosController@agrupaItemizado');
	Route::post('/itemizado/deleteAdicional','itemizadoController@postDeleteAdicional');
	Route::post('/itemizado/saveApprovalFlow','itemizadoController@postSaveApprovalFlow');
	Route::get('/itemizado/creaItemizado','itemizadoController@creaItemizado');
	Route::get('/itemizado/getApprovalFlow','itemizadoController@getApprovalFlow');
	Route::post('/edp/reviewConfirmation','edpController@postReviewConfirmation');
	Route::get('/contrato/dashboard/adc','contratosController@getDataDashboardContratoAdc');
	Route::get('/contrato/detail/adc','contratosController@getDataDashboardContratoAdcDetail');
	Route::get('/edp/dashboard','edpController@getDataDashboardEdp');
	Route::get('/edp/historialmovimientos','edpController@getHistorialMovimientosEdp');
	Route::post('/contrato/savekpi','contratosController@postSaveKpi');

	/* contratistasController */
	Route::post('/contratistas/EliminarContratista','contratistasController@EliminarContratista');
	Route::post('/contratistas/save','contratistasController@postSave');
	Route::get('/contratista/creaContratista','contratistasController@creaContratista');
	Route::get('/contratista/listadoContratistas','contratistasController@getList');
	Route::get('/contratista/compruebanumerorut','contratistasController@postCompruebanumerorut');
	Route::get('/contratista/comuna','contratistasController@getComuna');
	Route::get('/contratista/provincia','contratistasController@getProvincia');
	/* fin contratistasController */

	/* personasController */
	Route::post('/personas/eliminaPersona','personasController@eliminaPersona');
	/* modal eliminar  */
	Route::post('/personas/desasociarPersona','personasController@desasociarPersona');
	Route::post('/personas/desvinculaPersona','personasController@desvinculaDesasociaPersona');
	Route::post('/personas/creaPersona','personasController@postSave');
	Route::get('/persona/listadoPersonas','personasController@getList');
	Route::get('/persona/documentos','personasController@getShowdocuments');
	Route::get('/persona/dataCambios','personasController@getDataCambioContractual');
	Route::post('/persona/cambioContractual','personasController@postCambiosContractual');
	Route::post('/persona/asginarContrato','personasController@AssignContract');
	Route::get('/persona/listAnotaciones','personasController@listAnotaciones');
	Route::get('/persona/listadoRoles','personasController@listRoles');
	
	Route::get('/persona/nacionalidades','personasController@getNacionalidades');
	Route::get('/persona/contratistas','personasController@getContratistas');
	Route::get('/persona/telefono','personasController@getTelefono');

	/*** Encuestados Controller */
	Route::get('/encuestados/notaEncuesta','encuestadosController@resultadoNotaEncuesta');
	Route::get('/encuestados/notafinalranking','encuestadosController@NotaFinalRanking');
	Route::get('/encuestados/listKpi','encuestadosController@listKpi');
	Route::get('/encuestados/getComentariosEncuesta','encuestadosController@getComentariosEncuesta');
	Route::get('/encuestados/getqr','encuestadosController@getQr');
	Route::post('/encuestados/saveComment','encuestadosController@postSaveComentario');

	/***  Usuarios */
	Route::get('/usuarios/listadoUsuarios','usuariosController@getList');
	Route::post('/usuarios/eliminaUsuario','usuariosController@eliminarUsuario');
	Route::post('/usuarios/creaUsuario','usuariosController@CrearYEditarUsuario');
	Route::get('/usuarios/creaUsuarioData','usuariosController@datosCrearUsuario');

	Route::get('/filtro','variosController@filtroBuscador');
});


	Route::group(['namespace' => 'ApiFront'], function () {

});

    Route::group(['namespace' => 'ApisClientes','prefix' => 'abt/v1'], function () {
	Route::any('/accesoAcreditado', 'acreditacionController@postAcceso');
});
