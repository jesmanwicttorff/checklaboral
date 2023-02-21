<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedInsertEstatusIniciales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $larrData = array(
            'id'=>1,
            'nombre'=>'Activo',
            'IdEstatus'=>1,
            'BloqueaAcceso'=>0,
            'BloqueaCambios'=>0,
            'BloqueaVinculacion'=>0,
            'BloqueaDesvinculacion'=>0,
            'BloqueaLibroObra'=>0,
            'entry_by'=>1,
            'updated_by'=>1);
        DB::table('tbl_contrato_estatus')->insert($larrData);
        $larrData = array(
            'id'=>2,
            'nombre'=>'Inactivo',
            'IdEstatus'=>1,
            'BloqueaAcceso'=>1,
            'BloqueaCambios'=>0,
            'BloqueaVinculacion'=>1,
            'BloqueaDesvinculacion'=>1,
            'BloqueaLibroObra'=>1,
            'entry_by'=>1,
            'updated_by'=>1);
        DB::table('tbl_contrato_estatus')->insert($larrData);
        $larrData = array(
            'id'=>3,
            'nombre'=>'Vencido',
            'IdEstatus'=>1,
            'BloqueaAcceso'=>1,
            'BloqueaCambios'=>0,
            'BloqueaVinculacion'=>1,
            'BloqueaDesvinculacion'=>0,
            'BloqueaLibroObra'=>0,
            'entry_by'=>1,
            'updated_by'=>1);
        DB::table('tbl_contrato_estatus')->insert($larrData);
        $larrData = array(
            'id'=>4,
            'nombre'=>'Proceso de renovación',
            'IdEstatus'=>1,
            'BloqueaAcceso'=>0,
            'BloqueaCambios'=>0,
            'BloqueaVinculacion'=>0,
            'BloqueaDesvinculacion'=>0,
            'BloqueaLibroObra'=>0,
            'entry_by'=>1,
            'updated_by'=>1);
        DB::table('tbl_contrato_estatus')->insert($larrData);
        $larrData = array(
            'id'=>5,
            'nombre'=>'Proceso de finiquito',
            'IdEstatus'=>1,
            'BloqueaAcceso'=>1,
            'BloqueaCambios'=>0,
            'BloqueaVinculacion'=>1,
            'BloqueaDesvinculacion'=>0,
            'BloqueaLibroObra'=>0,
            'entry_by'=>1,
            'updated_by'=>1);
        DB::table('tbl_contrato_estatus')->insert($larrData);
        $larrData = array(
            'id'=>6,
            'nombre'=>'Finiquitado anticipadamente',
            'IdEstatus'=>1,
            'BloqueaAcceso'=>1,
            'BloqueaCambios'=>1,
            'BloqueaVinculacion'=>1,
            'BloqueaDesvinculacion'=>1,
            'BloqueaLibroObra'=>1,
            'entry_by'=>1,
            'updated_by'=>1);
        DB::table('tbl_contrato_estatus')->insert($larrData);
        $larrData = array(
            'id'=>7,
            'nombre'=>'Finiquitado por vencimiento',
            'IdEstatus'=>1,
            'BloqueaAcceso'=>1,
            'BloqueaCambios'=>1,
            'BloqueaVinculacion'=>1,
            'BloqueaDesvinculacion'=>1,
            'BloqueaLibroObra'=>1,
            'entry_by'=>1,
            'updated_by'=>1);
        DB::table('tbl_contrato_estatus')->insert($larrData);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tbl_contrato_estatus')->whereIn("id",[1,2,3,4,5,6,7])->delete();
    }
}
