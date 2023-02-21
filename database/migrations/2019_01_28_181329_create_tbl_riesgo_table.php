<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRiesgoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_riesgo', function (Blueprint $table) {
            $table->increments('id');
            $table->date('periodo');
            $table->string('cont_numero',20);
            $table->integer('contrato_id');
            $table->integer('dotacion');
            $table->integer('ingreso');
            $table->integer('egreso');
            $table->integer('porcentaje_rotacion');
            $table->integer('variable_1');
            $table->integer('riesgo_rotacion');
            $table->integer('horas_diarias');
            $table->integer('horas_totales');
            $table->integer('horas_vacaciones');
            $table->integer('horas_licencias');
            $table->integer('horas_otros_ausentismo');
            $table->integer('porcentaje_ausentimo');
            $table->integer('variable_2');
            $table->integer('riesgo_ausentismo');
            $table->integer('rc_monto_impago');
            $table->integer('rc_numero_impago');
            $table->integer('indicador_comercial');
            $table->integer('mayor_400');
            $table->integer('riesgo_comercial');
            $table->integer('patrimonio');
            $table->integer('ri_numero_impago');
            $table->integer('ri_monto_impago');
            $table->integer('indicador_impuesto');
            $table->integer('mayor_2');
            $table->integer('riesgo_impuesto');
            $table->integer('riesgo');
            $table->integer('entry_by');
            $table->integer('updated_by');
            $table->timestamps();
            $table->index(['periodo']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_riesgo');
    }
}
