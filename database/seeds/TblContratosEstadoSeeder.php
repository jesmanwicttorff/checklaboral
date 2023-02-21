<?php

use Illuminate\Database\Seeder;
use App\Models\TblContratosEstado;

class TblContratosEstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$larrContratosEstado = array(["Descripcion"=>"Activo", "entry_by" => 1], 
    		                         ["Descripcion"=>"Inactivo", "entry_by" => 1], 
    		                         ["Descripcion"=>"Cancelado", "entry_by" => 1],
    		                         ["Descripcion"=>"Finalizado", "entry_by" => 1]);
    	foreach ($larrContratosEstado as $ContratosEstado) {
    		TblContratosEstado::create($ContratosEstado);
    	}
    	

    }
}
