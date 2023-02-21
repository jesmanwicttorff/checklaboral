<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblTiposDocumentosAttr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create("tbl_tipos_documentos_attr",function(Blueprint $table){
          $table->increments('IdTipoDocumento');
          $table->integer('Prioridad');
          $table->decimal('Ponderacion',3,1);
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
