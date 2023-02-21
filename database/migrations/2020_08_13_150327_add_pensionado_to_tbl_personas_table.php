<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPensionadoToTblPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_personas', function (Blueprint $table) {
            //
            $table->enum('pensionado', [1, 0])->default(0)->comment('1 es pensionado, 0 no lo es');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_personas', function (Blueprint $table) {
            //
            $table->dropColumn('pensionado');
        });
    }
}
