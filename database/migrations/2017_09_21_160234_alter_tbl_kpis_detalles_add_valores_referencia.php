<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblKpisDetallesAddValoresReferencia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_kpis_detalles', function (Blueprint $table) {
            $table->decimal('RangoInferior',11,2)->nullable(true)->after('Resultado');
            $table->decimal('RangoSuperior',11,2)->nullable(true)->after('Resultado');
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
            $table->dropcolumn('RangoInferior');
            $table->dropcolumn('RangoSuperior');
        });
    }
}
