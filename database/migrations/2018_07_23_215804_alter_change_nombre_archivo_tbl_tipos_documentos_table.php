<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterChangeNombreArchivoTblTiposDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_tipos_documentos', function (Blueprint $table) {
            $table->string('nombre_archivo',190)->change();
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
            $table->string('nombre_archivo',50)->change();
        });
    }
}
