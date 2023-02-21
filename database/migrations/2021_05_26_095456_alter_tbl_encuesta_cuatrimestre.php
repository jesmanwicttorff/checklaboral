<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblEncuestaCuatrimestre extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('tbl_encuesta_cuatrimestre', function ($table) {
        $table->decimal('notafinal', 3,1)->change();
        $table->index('contrato_id');
        $table->index('encuesta_id');
        $table->index('IdTipoDocumento');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
