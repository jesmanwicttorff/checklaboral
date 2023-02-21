<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumentoValor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('tbl_documento_valor', function (Blueprint $table) {
          $table->string('Valor',500)->change();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('tbl_documento_valor', function (Blueprint $table) {
          $table->string('Valor',100)->change();
      });
    }
}
