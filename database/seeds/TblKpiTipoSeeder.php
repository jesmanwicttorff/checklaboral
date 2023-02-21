<?php

use Illuminate\Database\Seeder;
use App\Models\TblKpisTipo;

class TblKpiTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $larrKpiTipos = array(["Nombre"=>"Directo Mayor o Igual", "RangoSuperior" => 1, "RangoInferior" =>1, "entry_by" => 1], 
	                          ["Nombre"=>"Directo Menor o Igual", "RangoSuperior" => 1, "RangoInferior" =>1, "entry_by" => 1], 
	                          ["Nombre"=>"Rango Mayor o Igual", "RangoSuperior" => 0, "RangoInferior" =>1, "entry_by" => 1],
	                          ["Nombre"=>"Rango Menor o igual", "RangoSuperior" => 0, "RangoInferior" =>1, "entry_by" => 1]);
    	foreach ($larrKpiTipos as $KpiTipos) {
    		TblKpisTipo::create($KpiTipos);
    	}
    }
}
