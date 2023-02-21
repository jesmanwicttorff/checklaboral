<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedTblContratoEdpEstados extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

      \DB::table('tbl_contrato_edp_estados')->truncate();

      $larrData = array('valor'=>'En Preparacion');
              \DB::table('tbl_contrato_edp_estados')->insert($larrData);
              $larrData = array('valor'=>'Enviado');
                      \DB::table('tbl_contrato_edp_estados')->insert($larrData);
                      $larrData = array('valor'=>'Aceptado');
                              \DB::table('tbl_contrato_edp_estados')->insert($larrData);
                              $larrData = array('valor'=>'Rechazado');
                                      \DB::table('tbl_contrato_edp_estados')->insert($larrData);
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
