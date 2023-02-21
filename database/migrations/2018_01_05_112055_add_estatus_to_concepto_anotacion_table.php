<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEstatusToConceptoAnotacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_concepto_anotacion', function (Blueprint $table) {
             $table->integer('IdEstatus')->default(1)->after('Descripcion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_concepto_anotacion', function (Blueprint $table) {
            $table->dropcolumn('IdEstatus');
        });
    }
}
