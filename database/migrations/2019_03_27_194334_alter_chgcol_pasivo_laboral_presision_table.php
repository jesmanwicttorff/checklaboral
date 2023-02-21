<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterChgcolPasivoLaboralPresisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contrato_maestro', function (Blueprint $table) {
            $table->decimal('pasivo_laboral',12,2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_contrato_maestro', function (Blueprint $table) {
            $table->decimal('pasivo_laboral',10,2)->change();
        });
    }
}
