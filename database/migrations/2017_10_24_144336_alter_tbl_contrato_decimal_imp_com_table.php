<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblContratoDecimalImpComTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contrato', function (Blueprint $table) {
            $table->decimal('impacto',11,2)->change();
            $table->decimal('complejidad',11,2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_contrato', function (Blueprint $table) {
            $table->integer('impacto')->change();
            $table->integer('complejidad')->change();
        });
    }
}
