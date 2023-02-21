<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedTblDiferenciasCalculoPieMaestro extends Migration
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
                  'grupo' => 'obligaciones_laborales',
                  'nombre'=> 'Sueldo Base',
                  'nombre_campo'=> 'sueldo_base',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_laborales',
                  'nombre'=> 'Gratificaciones',
                  'nombre_campo'=> 'gratificacion_legal',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_laborales',
                  'nombre'=> 'Hrs. Extras',
                  'nombre_campo'=> 'horas_extras',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_laborales',
                  'nombre'=> 'Otros Haberes Imponibles',
                  'nombre_campo'=> 'otros_imponibles',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_laborales',
                  'nombre'=> 'Haberes no imponibles',
                  'nombre_campo'=> 'no_imponible',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_laborales',
                  'nombre'=> 'Impuestos',
                  'nombre_campo'=> 'impuesto',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_laborales',
                  'nombre'=> 'Otros descuentos',
                  'nombre_campo'=> 'otros_descuentos',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_previsionales',
                  'nombre'=> 'AFP',
                  'nombre_campo'=> 'afp',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_previsionales',
                  'nombre'=> 'Salud',
                  'nombre_campo'=> 'salud',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_previsionales',
                  'nombre'=> 'AFC Trabajador',
                  'nombre_campo'=> 'afc',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_previsionales',
                  'nombre'=> 'Trabajo Pesado',
                  'nombre_campo'=> 'trabajo_pesado',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_previsionales',
                  'nombre'=> 'SIS',
                  'nombre_campo'=> 'sis',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_previsionales',
                  'nombre'=> 'AFC Empleador',
                  'nombre_campo'=> 'afc_empleador',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_previsionales',
                  'nombre'=> 'Mutualidad',
                  'nombre_campo'=> 'mutualidad',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'obligaciones_previsionales',
                  'nombre'=> 'Ahorro Voluntario',
                  'nombre_campo'=> 'ahorro_voluntario',
                  'id_estatus'=> 0
            ),
            array(
                  'grupo' => 'obligaciones_previsionales',
                  'nombre'=> 'Salud Voluntario',
                  'nombre_campo'=> 'salud_voluntario',
                  'id_estatus'=> 0
            ),
            array(
                  'grupo' => 'obligaciones_previsionales',
                  'nombre'=> 'CCAF',
                  'nombre_campo'=> 'ccaf',
                  'id_estatus'=> 0
            ),
            array(
                  'grupo' => 'obligaciones_previsionales',
                  'nombre'=> 'Trabajo Pesado Empleador',
                  'nombre_campo'=> 'trabajo_pesado_empleador',
                  'id_estatus'=> 0
            ),
            array(
                  'grupo' => 'finiquito',
                  'nombre'=> 'IAS',
                  'nombre_campo'=> 'ias',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'finiquito',
                  'nombre'=> 'Vacaciones',
                  'nombre_campo'=> 'vacaciones',
                  'id_estatus'=> 1
            ),
            array(
                  'grupo' => 'finiquito',
                  'nombre'=> 'Otros',
                  'nombre_campo'=> 'otros',
                  'id_estatus'=> 1
            )
          );
          DB::table('tbl_diferencias_calculo_pie_maestro')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tbl_diferencias_calculo_pie_maestro')->delete();
    }
}
