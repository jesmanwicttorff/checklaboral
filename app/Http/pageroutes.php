<?php
Route::get('service', 'HomeController@index');
Route::get('about-us', 'HomeController@index');
Route::get('contact-us', 'HomeController@index');
Route::get('privacy', 'HomeController@index');
Route::get('toc', 'HomeController@index');
Route::get('backend', 'HomeController@index');
Route::get('dashboardpersonas/getresetfiltros','DashboardpersonasController@getResetFiltros');
Route::get('dashboardpersonas/getFiltro2','DashboardpersonasController@getFiltro2');
Route::post('accesos/GeneraInformeAccesosDispositivo','AccesosController@getGeneraInformeAccesosDispositivo');
Route::get('accesos/tarjetaacceso','AccesosController@getTarjetaAcceso');
Route::get('accesos/tarjetaaccesoccu','AccesosController@getTarjetaAccesoCCU');
Route::get('acreditacion/DescargarInforme','AcreditacionController@getDescargaInforme');
Route::get('personas/DescargarPersonasVigentes','PersonasController@getDescargarPersonasVigentes');
Route::get('noconformidades/DescargarDocumentoPersonas/{id}','NoconformidadesController@getDescargarDocumentoPersonas');
Route::post('accesos/GeneraInformeDiario','AccesosController@getGeneraInformeDiario');
Route::post('accesos/GeneraInformeMensual','AccesosController@getGeneraInformeMensual');
Route::post('accesos/GeneraInformeAccesos','AccesosController@getGeneraInformeAccesos');
Route::get('acreditacion/credencialTrabajador/{id}','AcreditacionController@getCredencialTrabajador');
Route::get('activos/credencialActivo/{id}','ActivosController@getCredencialActivo');
Route::get('acreditacion/DescargarInformeDocumentos','AccesosController@sendDocsVencidosConsolidado');
Route::get('accesos/informe21dias','AccesosController@informeAccesosSinAccesos');
Route::get('reportespersonalizados/ReporteEmpresas','ReportespersonalizadosController@reporteEmpresas');
Route::get('reportespersonalizados/ReportePersonas','ReportespersonalizadosController@reportePersonas');
Route::get('encuestados/pdf/{idDocumento}','EncuestadosController@encuesta');
?>
