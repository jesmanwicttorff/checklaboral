<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedTblTipodoc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $object = array(
          "IdFormato"=>'application/vnd.ms-excel',
          "group_id"=>0,
          "Entidad"=>1,
          "Descripcion"=>'Formulario datos trabajadores',
          "nombre_archivo"=>'file_excel_datos',
          "ControlCheckLaboral"=>0,
          "TextoExplicativo"=>'Formulario datos trabajadores',
          "Permanencia"=>0,
          "Vigencia"=> 3,
          "Periodicidad"=> 0,
          "BloqueaAcceso"=> 'NO',
          "Acreditacion"=> 0,
          "Tipo"=> 1,
          "MultipleDocumentos"=> 0,
          "IdProceso"=> 140,
          "DiasVencimiento"=> 0,
          "entry_by"=> 1,
          "Ponderado"=> 10,
          "RelacionPersona"=>0
        );

        $lintId = \DB::table('tbl_tipos_documentos')->insertGetId($object);
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
