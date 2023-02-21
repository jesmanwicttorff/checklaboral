<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSstToTblTiposDocumentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_tipos_documentos', function (Blueprint $table) {
            $table->enum('sst', [1, 0])->default(0)->comment('1 es sst, 0 no lo es');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_tipos_documentos', function (Blueprint $table) {
            $table->dropColumn('sst');
        });
    }
}
