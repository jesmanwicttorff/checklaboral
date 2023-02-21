<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionTiposContratosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_extension_tipos_contratos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('IdExtension');
            $table->integer('IdTiposContratoPersona');
            $table->timestamp('createdOn');
            $table->timestamp('updatedOn');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_extension_tipos_contratos');
    }
}
