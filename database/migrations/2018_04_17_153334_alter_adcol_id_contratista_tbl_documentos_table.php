<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAdcolIdContratistaTblDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_documentos', function (Blueprint $table) {
            $table->integer('IdContratista')->nullable()->after('contrato_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_documentos', function (Blueprint $table) {
            $table->dropcolumn('IdContratista');
        });
    }
}
