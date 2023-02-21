<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedTblPeriodoControlado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = array(
            array(
                  'apertura' => 'Viernes 29-03-2019 09:00',
                  'cierre'=> 'Lunes 22-04-2019 17:00',
                  'informe_final'=> 'Viernes 03-05-2019',
                  'periodo_controlado'=> 'Documentos Marzo'
            ),
            array(
                  'apertura' => 'Viernes 03-05-2019 09:00',
                  'cierre'=> 'Jueves 23-05-2019 17:00',
                  'informe_final'=> 'Martes 04-06-2019',
                  'periodo_controlado'=> 'Documentos Abril'
            ),
            array(
                  'apertura' => 'Martes 04-06-2019 09:00',
                  'cierre'=> 'Lunes 24-06-2019 17:00',
                  'informe_final'=> 'Jueves 04-07-2019',
                  'periodo_controlado'=> 'Documentos Mayo'
            ),
            array(
                  'apertura' => 'Jueves 04-07-2019 09:00',
                  'cierre'=> 'Martes 23-07-2019 17:00',
                  'informe_final'=> 'Viernes 02-08-2019',
                  'periodo_controlado'=> 'Documentos Junio'
            ),
            array(
                  'apertura' => 'Viernes 02-08-2019 09:00',
                  'cierre'=> 'Jueves 22-08-2019 17:00',
                  'informe_final'=> 'Miércoles 04-09-2019',
                  'periodo_controlado'=> 'Documentos Julio'
            ),
            array(
                  'apertura' => 'Miércoles 04-09-2019 09:00',
                  'cierre'=> 'Miércoles 25-09-2019 17:00',
                  'informe_final'=> 'Lunes 07-10-2019',
                  'periodo_controlado'=> 'Documentos Agosto'
            ),

          );
        DB::table('tbl_periodo_controlado')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tbl_periodo_controlado')->delete();
    }
}
