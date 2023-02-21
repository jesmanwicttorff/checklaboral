<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedTblSexo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      $larrData = array('tipo'=>'Hombre');
              \DB::table('tbl_sexo')->insert($larrData);

      $larrData = array('tipo'=>'Mujer');
              \DB::table('tbl_sexo')->insert($larrData);
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
