<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblPersonasMaestroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_personas_maestro', function (Blueprint $table) {
            $table->increments('id');
            $table->date('periodo');
            $table->integer('idpersona');
            $table->integer('contrato_id');
            $table->integer('idcontratista');
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
        Schema::drop('tbl_personas_maestro');
    }
}
