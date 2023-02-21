<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterChgcolAcreditacionTblContratosAcreditacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contratos_acreditacion', function (Blueprint $table) {
            $table->date('acreditacion')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_contratos_acreditacion', function (Blueprint $table) {
            $table->date('acreditacion')->change();
        });
    }
}
