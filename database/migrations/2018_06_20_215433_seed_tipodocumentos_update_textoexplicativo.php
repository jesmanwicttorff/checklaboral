<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedTipodocumentosUpdateTextoexplicativo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('tbl_tipos_documentos')
            ->where('Descripcion',"Evaluación de proveedores de servicios")
            ->update(['TextoExplicativo' => "De acuerdo a las siguientes opciones usted puede evaluar a los distintos proveedores de productos y/o servicios que utiliza en su área habitualmente:"]);

        \DB::table('tbl_tipos_documentos')
            ->where('Descripcion',"Evaluación de Administrador de Contrato")
            ->update(['TextoExplicativo' => "Las siguientes preguntas tienen por objetivo evaluar el desempeño y manejo del Administrador de contrato del servicio mencionado en esta solicitud"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('tbl_tipos_documentos')
            ->where('Descripcion',"Temporal")
            ->update(['TextoExplicativo' => "Evaluación de proveedores de servicios"]);

        \DB::table('tbl_tipos_documentos')
            ->where('Descripcion',"Evaluación de Administrador de Contrato")
            ->update(['TextoExplicativo' => ""]);
    }
}
