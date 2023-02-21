<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedDashboard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      $data = array(
          array(
                'nombre' => 'Tecsoluciones',
                'descripcion'=> 'Dashboard Tecsoluciones',
                'vista'=> 'tec',                
          ),
        );
        DB::table('tbl_dashboard')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //DB::table('tbl_dashboard')->delete();
    }
}
