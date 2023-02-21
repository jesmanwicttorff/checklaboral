<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Acciones;

class SeedAccionesContratos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $listadoAcciones = array(["Nombre"=>"Registro de empresa subcontratista", "Descripcion"=>"Registro de empresa subcontratista al contrato"],
            ["Nombre"=>"Retiro de empresa subcontratista", "Descripcion"=>"Retiro de empresa subcontratista del contrato"]);

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
        \DB::table('tbl_acciones')->where('Nombre', '=', 'Registro de empresa subcontratista')->delete();
        \DB::table('tbl_acciones')->where('Nombre', '=', 'Retiro de empresa subcontratista')->delete();
    }
}
