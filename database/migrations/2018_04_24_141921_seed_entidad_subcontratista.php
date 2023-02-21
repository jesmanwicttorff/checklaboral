<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedEntidadSubcontratista extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('tbl_entidades')->insert(
            ['Entidad' => 'Subcontratista']
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('tbl_entidades')->where('Entidad', '=', 'Subcontratista')->delete();
    }
}
