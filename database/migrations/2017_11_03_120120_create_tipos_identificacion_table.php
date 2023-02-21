<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTiposIdentificacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('tbl_tipos_identificacion', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('IdTipoIdentificacion');
            $table->string('Descripcion',128);
            $table->integer('ValorxDefecto');
            $table->integer('entry_by');
            $table->integer('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::drop('tbl_tipos_identificacion');
    }
}
