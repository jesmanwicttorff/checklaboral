<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblContratoItemizadoSublineasAddDecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        \DB::statement("ALTER TABLE `tbl_contrato_itemizado_sublineas` CHANGE COLUMN `montoLinea` `montoLinea` DECIMAL(14,1) NOT NULL AFTER `unidadMedidad_id`");

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
