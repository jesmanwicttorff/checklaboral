<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblDiferenciasCalculoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tbl_diferencias_calculo', function (Blueprint $table) {
          $table->increments('id');
          $table->date('periodo');
          $table->integer('contrato_id');
          $table->string('cont_numero');
          $table->integer('IdPersona');
          $table->string('rut');
          $table->string('nombre');
          $table->decimal('sueldo_base',10,2)->default(0.0);
          $table->decimal('gratificacion_legal',10,2)->default(0.0);
          $table->decimal('horas_extras',10,2)->default(0.0);
          $table->decimal('otros_imponibles',10,2)->default(0.0);
          $table->decimal('no_imponible',10,2)->default(0.0);
          $table->decimal('impuesto',10,2)->default(0.0);
          $table->decimal('otros_descuentos',10,2)->default(0.0);
          $table->decimal('ol_diferencia_calculo',10,2)->default(0.0);
          $table->decimal('ol_diferencia_pago',10,2)->default(0.0);
          $table->decimal('afp',10,2)->default(0.0);
          $table->decimal('ahorro_voluntario',10,2)->default(0.0);
          $table->decimal('salud',10,2)->default(0.0);
          $table->decimal('salud_voluntario',10,2)->default(0.0);
          $table->decimal('ccaf',10,2)->default(0.0);
          $table->decimal('afc',10,2)->default(0.0);
          $table->decimal('trabajo_pesado',10,2)->default(0.0);
          $table->decimal('subtotal_previsiones',10,2)->default(0.0);
          $table->decimal('sis',10,2)->default(0.0);
          $table->decimal('afc_empleador',10,2)->default(0.0);
          $table->decimal('trabajo_pesado_empleador',10,2)->default(0.0);
          $table->decimal('mutualidad',10,2)->default(0.0);
          $table->decimal('subtotal_previsiones_empleador',10,2)->default(0.0);
          $table->decimal('op_diferencia_calculo',10,2)->default(0.0);
          $table->decimal('op_diferencia_pago',10,2)->default(0.0);
          $table->decimal('sl_diferencia_calculo',10,2)->default(0.0);
          $table->decimal('sl_diferencia_pago',10,2)->default(0.0);
          $table->decimal('ias',10,2)->default(0.0);
          $table->decimal('vacaciones',10,2)->default(0.0);
          $table->decimal('otros',10,2)->default(0.0);
          $table->decimal('fl_diferencia_calculo',10,2)->default(0.0);
          $table->decimal('fl_diferencia_pago',10,2)->default(0.0);
          $table->decimal('calculado',10,2)->default(0.0);
          $table->decimal('pagado',10,2)->default(0.0);
          $table->decimal('cl_diferencia_calculo',10,2)->default(0.0);
          $table->decimal('cl_diferencia_pago',10,2)->default(0.0);
          $table->decimal('diferencia_favor_trabajador',10,2)->default(0.0);
          $table->decimal('diferencia_favor_empleador',10,2)->default(0.0);
          $table->decimal('limite',10,2)->default(0.0);
          $table->integer('IdEstatus');
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
        Schema::drop('tbl_diferencias_calculo');
    }
}
