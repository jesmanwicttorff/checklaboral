<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterChgcolPasivoLaboralPresisionMayorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contrato_maestro', function (Blueprint $table) {
            $table->decimal('costo_laboral',15,2)->change();
            $table->decimal('pasivo_laboral',15,2)->change();
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
            $table->decimal('costo_laboral',12,2)->change();
            $table->decimal('pasivo_laboral',12,2)->change();
        });
    }
}
