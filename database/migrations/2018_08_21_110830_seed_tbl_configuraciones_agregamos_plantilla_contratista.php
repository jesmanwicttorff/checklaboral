<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedTblConfiguracionesAgregamosPlantillaContratista extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $lobjDatos = array("Nombre"=>"CNF_TEMPLATE_CONTRATISTA",
                           "Descripcion"=>"Identifica cual es la plantilla del contratista",
                           "Valor"=>"form",
                           "entry_by" => 1,
                           "created_at" => date('Y-m-d H:i:s'));

        \DB::table('tbl_configuraciones')->insert($lobjDatos);
        $lobjDatos = array("Nombre"=>"CNF_TEMPLATE_CONTRATO",
                           "Descripcion"=>"Identifica cual es la plantilla para el contrato",
                           "Valor"=>"general",
                           "entry_by" => 1,
                           "created_at" => date('Y-m-d H:i:s'));    
        \DB::table('tbl_configuraciones')->insert($lobjDatos);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('tbl_configuraciones')->where("Nombre","CNF_TEMPLATE_CONTRATISTA")->delete();
        \DB::table('tbl_configuraciones')->where("Nombre","CNF_TEMPLATE_CONTRATO")->delete();
    }
}
