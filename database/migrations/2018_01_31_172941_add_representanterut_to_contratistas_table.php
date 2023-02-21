<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRepresentanterutToContratistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contratistas', function (Blueprint $table) {
            $table->string('RepresentanteRut',11)->nullable()->after('Representante');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_contratistas', function (Blueprint $table) {
            $table->dropcolumn('RepresentanteRut');
        });
    }
}
