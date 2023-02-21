<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblTipoDocumentoData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('tbl_tipo_documento_data', function (Blueprint $table) {
          $table->string('Valor',500)->change();
          $table->string('Display',500)->change();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('tbl_tipo_documento_data', function (Blueprint $table) {
          $table->string('Valor',150)->change();
          $table->string('Display',150)->change();
      });
    }
}
