<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContratosPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_contratos_plan', function (Blueprint $table) {
            $table->increments('IdContratoPlan');
            $table->integer('contrato_id');
            $table->string('Descripcion',45);
            $table->integer('IdTipo');
            $table->integer('entry_by');
            $table->integer('entry_by_access');
            $table->string('ColorFondo',45);
            $table->string('ColorBorde',45);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_contratos_plan');
    }
}
