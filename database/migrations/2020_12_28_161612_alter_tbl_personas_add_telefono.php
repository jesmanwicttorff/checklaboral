<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPersonasAddTelefono extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('tbl_personas', function (Blueprint $table) {
          $table->integer('paisTelefono_id')->default(0);
          $table->integer('codigoAreaTelefono_id')->default(0);
          $table->string('telefono',20)->default('');
          $table->string('contactoEmergencia',50);
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
