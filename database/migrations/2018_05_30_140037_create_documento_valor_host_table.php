<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentoValorHostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_documento_valor_historico', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('IdDocumento');
            $table->integer('IdTipoDocumentoValor');
            $table->string('Valor',100);
            $table->integer('idCargado');
            $table->integer('entry_by')->nullable();
            $table->integer('entry_by_access')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_documento_valor_historico');
    }
}
