<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblKpisDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_kpis_detalles', function (Blueprint $table) {
            $table->decimal('Puntaje',11,2)->nullable(true)->change();
            $table->decimal('Resultado',11,2)->nullable(true)->change();
            $table->decimal('MetaSuperior',11,2)->nullable(true)->change();
            $table->decimal('MetaInferior',11,2)->nullable(true)->change();
            $table->integer('updated_by')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_kpis_detalles', function (Blueprint $table) {
            $table->integer('Puntaje')->nullable(true)->change();
            $table->integer('Resultado')->nullable(true)->change();
            $table->integer('MetaSuperior')->nullable(true)->change();
            $table->integer('MetaInferior')->nullable(true)->change();
            $table->integer('updated_by')->nullable(true)->change();
        });
    }
}
