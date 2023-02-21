<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscapacidadToTblPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_personas', function (Blueprint $table) {
            $table->enum('discapacidad', [1, 0])->default(0)->comment('1 tiene discapacidad, 0 no tiene');
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
            $table->dropColumn('discapacidad');
        });
    }
}
