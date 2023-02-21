<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblPersonasAcreditacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_personas_acreditacion', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idpersona');
            $table->integer('entry_by');
            $table->date('acreditacion');
            $table->integer('idestatus');
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
        Schema::drop('tbl_personas_acreditacion');
    }
}
