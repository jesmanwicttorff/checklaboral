<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblcontratosItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create("tbl_contratos_items",function(Blueprint $table){
            $table->increments("IdContratoItem");
            $table->integer("IdParent");
            $table->string("Identificacion",45);
            $table->string("Descripcion",128);
            $table->integer("IdUnidad");
            $table->decimal("Cantidad",11,2);
            $table->decimal("Monto",11,2);
            $table->decimal("SubTotal",11,2);
            $table->integer("contrato_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("tbl_contratos_items");
    }
}
