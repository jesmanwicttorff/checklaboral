<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTipoIdentificacionToPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_personas', function (Blueprint $table) {
            $table->integer('IdTipoIdentificacion')->default(1)->after('IdPersona');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('tbl_personas', function (Blueprint $table) {
            $table->dropcolumn('IdTipoIdentificacion');
        });
    }
}
