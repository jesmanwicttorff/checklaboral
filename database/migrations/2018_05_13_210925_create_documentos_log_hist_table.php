<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentosLogHistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_documentos_log_historico', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('IdDocumento');
            $table->integer('IdAccion');
            $table->string('DocumentoURL',128)->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('entry_by');
            $table->timestamp('createdOn')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('IdAccion')->references('IdAccion')->on('tbl_acciones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_documentos_log_historico');
    }
}
