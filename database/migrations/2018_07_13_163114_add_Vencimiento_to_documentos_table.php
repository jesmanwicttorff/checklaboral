<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVencimientoToDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_documentos', function (Blueprint $table) {
            $table->boolean('Vencimiento')->after('DocumentoTexto')->default(false);
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
            $table->dropcolumn('Vencimiento');
        });
    }
}
