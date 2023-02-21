<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumentosAnexosAddFechaCambio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table("tbl_documentos_anexos",function(Blueprint $table){
            
           $table->dateTime('fecha_cambio')->nullable()->after('IdRol');
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
        Schema::table("tbl_documentos_anexos",function(Blueprint $table){
            
            $table->dropColumn('fecha_cambio');
         });
        
    }
}
