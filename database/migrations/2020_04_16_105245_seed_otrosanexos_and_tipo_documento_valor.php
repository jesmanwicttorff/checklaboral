<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedOtrosanexosAndTipoDocumentoValor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $tipoDocumento = DB::table('tbl_tipos_documentos')->where('IdProceso', 3)->first();
        if($tipoDocumento){
            $tipoDocumento = $tipoDocumento->IdTipoDocumento;
            $IdTipoValor = DB::table('tbl_tipo_documento_valor')->insertGetId([
                'IdTipoDocumento' => $tipoDocumento,
                'Etiqueta' => 'Otros Anexos',
                'TipoValor' => 'Texto',
                'Requerido' => 'No',
                'Solicitar' => 0
            ]);
            if($IdTipoValor){
                DB::table('tbl_otros_anexos')->insertGetId([
                    'anexo' => 'Suspensión de Contrato (Ley Protección al Empleo)',
                    'estatus' => 1,
                ]);
            }
            if($IdTipoValor){
                DB::table('tbl_otros_anexos')->insertGetId([
                    'anexo' => 'Reducción Temporal de Jornada (Ley Protección al Empleo)',
                    'estatus' => 1,
                ]);
            }
        }
        DB::table('tbl_acciones')->insertGetId([
            'Nombre' => 'anexo',
            'Descripcion' => 'Se genero un anexo',
        ]);

       

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
