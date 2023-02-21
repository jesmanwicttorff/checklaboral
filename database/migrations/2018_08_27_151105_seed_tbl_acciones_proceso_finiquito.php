<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Acciones;

class SeedTblAccionesProcesoFiniquito extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $listadoAcciones = array(["IdAccion"=>22,"Nombre"=>"Contrato finiquitado", "Descripcion"=>" Se concluyó el proceso de finiquito del contrato"]);

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
        //
    }
}
