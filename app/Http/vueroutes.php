<?php

// evaluacion  
Route::get('/evaluacion', function () {
    return view('layouts.appvue2');
});
Route::get('/resumen-evaluacion', function () {
    return view('layouts.appvue2');
});

// contratos 

Route::get('/contratos/crear', function () {
    return view('layouts.appvue2');
});

Route::get('/contratos/dashboard', function () {
    return view('layouts.appvue2');
});

Route::get('/contratos/dashboard/kpi', function () {
    return view('layouts.appvue2');
});

Route::get('/contratos/details/adc', function () {
    return view('layouts.appvue2');
});


// contratistas 

Route::get('/contratistas', function () {
    return view('layouts.appvue2');
});


// estados de pago 
Route::get('/estadosdepago', function () {
    return view('layouts.appvue2');
});

Route::get('/estadosdepago/listado', function () {
    return view('layouts.appvue2');
});

Route::get('/estadosdepago/editar', function () {
    return view('layouts.appvue2');
});

Route::get('/estadosdepago/revision', function () {
    return view('layouts.appvue2');
});

Route::get('/estadosdepago/dashboard', function () {
    return view('layouts.appvue2');
});

Route::get('/estadosdepago/dashboard/contratista', function () {
    return view('layouts.appvue2');
});

Route::get('/estadosdepago/dashboard/adc', function () {
    return view('layouts.appvue2');
});

Route::get('/estadosdepago/dashboard/historico', function () {
    return view('layouts.appvue2');
});


//seguridad
Route::get('/usuarios', function () {
    return view('layouts.appvue2');
});

Route::get('/perfiles', function () {
    return view('layouts.appvue2');
});

//Herramientas
Route::get('/tiposdedocumentos', function () {
    return view('layouts.appvue2');
});
Route::get('/fechas-dashboard', function () {
    return view('layouts.appvue2');
});
Route::get('/procesos-cierre', function () {
    return view('layouts.appvue2');
});
?>

