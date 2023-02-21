<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblPersonasMaestroMovil extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('tbl_personas_maestro_movil', function (Blueprint $table) {
          $table->increments('id');
          $table->date('periodo');
          $table->integer('idpersona');
          $table->integer('contrato_id');
          $table->integer('idcontratista')->nullable();
          $table->dateTime('created_at')->nullable();
          $table->dateTime('updated_at')->nullable();
          $table->string('Estatus',20);
          $table->date('FechaEfectiva');
          $table->date('FechaAnterior');
          $table->index('periodo');
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
