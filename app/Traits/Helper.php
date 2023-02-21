<?php

namespace App\Traits;
use App\Models\Personas;
use App\Models\Contratospersonas;
use App\Models\Contratos;
use App\Models\Extensiontiposcontratos;
use Carbon\Carbon;


trait Helper {

    public function ponerFormatoFecha($formatoActual,$fecha){
        /* 
            Cambia la fecha obtenida por input del front con un formato x el cual se pasa por 
            una Variable y se le cambia al formato requerido en la base de datos 0000-00-00
        */
        $fecha = Carbon::createFromFormat($formatoActual,$fecha);
        $fecha = $fecha->toDateString();
        return $fecha;

    }
    public function cambiarFechaDeBaseParaFront($fecha){

        $fechaInicio = new Carbon($fecha);
        $fechaFin = $fechaInicio->format('d-m-Y');
        return $fechaFin;

    }
}