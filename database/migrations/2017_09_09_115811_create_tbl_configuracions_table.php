<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblConfiguracionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_configuraciones', function (Blueprint $table) {
            $table->increments('IdConfiguracion');
            $table->string('Nombre',46)->unique();
            $table->string('Descripcion',128)->nullable(true);
            $table->string('Valor');
            $table->integer('entry_by');
            $table->integer('updated_by')->nullable(true);
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
        Schema::drop('tbl_configuraciones');
    }
}
