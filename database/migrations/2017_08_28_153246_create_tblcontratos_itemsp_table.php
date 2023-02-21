<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblcontratosItemspTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create("tbl_contratos_items_p",function(Blueprint $table){
            $table->increments("IdItemPlan");
            $table->integer('IdItem');
            $table->date('Mes');
            $table->decimal('Cantidad',11,2);
            $table->decimal('Monto',11,2);
            $table->decimal('SubTotal',11,2);
            $table->integer('contrato_id');
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
        Schema::drop("tbl_contratos_items_p");
    }
}
