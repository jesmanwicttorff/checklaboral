<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Acciones;

class SeedAccionRenovacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = ['Nombre' => 'Renovado', 'Descripcion' => 'Se Renueva el Documento'];
        Acciones::create($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('tbl_acciones')->where('Nombre', '=', 'Renovado')->delete();
    }
}
