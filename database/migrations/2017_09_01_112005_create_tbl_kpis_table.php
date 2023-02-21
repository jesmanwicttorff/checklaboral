<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblKpisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_kpis', function (Blueprint $table) {
            $table->increments('IdKpi');
            $table->integer('contrato_id')->nullable(false);
            $table->integer('IdTipo')->unsigned();
            $table->string('Descripcion',128)->nullable(false);
            $table->string('IdUnidad',10)->default('%')->nullable(false)->enum('choices', ['%', 'Unid']);
            $table->string('Formula',128)->nullable(true);
            $table->integer('RangoSuperior')->nullable(true);
            $table->integer('RangoInferior')->nullable(true);
            $table->integer('IdEstatus')->default(1);
            $table->integer('entry_by')->nullable(false);
            $table->integer('updated_by');
            $table->timestamps();
            $table->foreign('contrato_id')->references('contrato_id')->on('tbl_contrato');
            $table->foreign('IdTipo')->references('IdTipo')->on('tbl_kpis_tipos');
            $table->foreign('entry_by')->references('id')->on('tb_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tbl_kpis');
    }
}
