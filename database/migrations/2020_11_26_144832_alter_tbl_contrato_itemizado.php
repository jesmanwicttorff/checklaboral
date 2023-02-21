<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblContratoItemizado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('tbl_contrato_itemizado', function (Blueprint $table) {
          $table->dateTime('created_at')->after('montoTotal');
          $table->dateTime('updated_at');
          $table->unique('contrato_id')->change();
          $table->index('moneda_id')->change();
          $table->index('condicionPago_id')->change();
          //$table->decimal('MontoTotal',20,0)->change(); esto está dando problemas
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
