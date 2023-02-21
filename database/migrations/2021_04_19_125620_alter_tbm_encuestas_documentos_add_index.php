<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTbmEncuestasDocumentosAddIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('tbm_encuestas_documentos', function($table){
        $table->index('contrato_id');
        $table->index('encuesta_id');
        $table->index('IdDocumento');
        $table->index('IdTipoDocumento');
        $table->integer('status');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
