<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Acciones;


class SeedAccionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // En caso de haber corrido el seeder de la rama de extension de dontrato
        $listadoAcciones = array(["Nombre"=>"Cargado", "Descripcion"=>" Se Carga el documento"],
            ["Nombre"=>"Aprobado", "Descripcion"=>"Se Aprueba el documento"],
            ["Nombre"=>"Rechazado", "Descripcion"=>"Se Rechaza el documento"],
            ["Nombre"=>"Temporal", "Descripcion"=>"Se coloca como temporal"],
            ["Nombre"=>"Vuelto a cargar", "Descripcion"=>"Se carga el documento otra vez"]);

        /*
        $listadoAcciones = array(["Nombre"=>"Creacion de contrato", "Descripcion"=>" Se Crea un contrato"],
            ["Nombre"=>"Extension de fecha", "Descripcion"=>"Se extiende la fecha del contrado"],
            ["Nombre"=>"Cambio de estatus", "Descripcion"=>"Se Cambia el estatus del contrato"],
            ["Nombre"=>"Cambio de tipo de contrato", "Descripcion"=>"Se cambia el tipo de contrato"],
            ["Nombre"=>"Cargado", "Descripcion"=>" Se Carga el documento"],
            ["Nombre"=>"Aprobado", "Descripcion"=>"Se Aprueba el documento"],
            ["Nombre"=>"Rechazado", "Descripcion"=>"Se Rechaza el documento"],
            ["Nombre"=>"Temporal", "Descripcion"=>"Se coloca como temporal"],
            ["Nombre"=>"Vuelto a cargar", "Descripcion"=>"Se carga el documento otra vez"]
        );
*/


        foreach ($listadoAcciones as $lisAcciones) {
            Acciones::create($lisAcciones);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
