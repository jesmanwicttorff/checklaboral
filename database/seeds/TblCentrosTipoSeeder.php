<?php

use Illuminate\Database\Seeder;
use App\Models\TblCentrosTipo;

class TblCentrosTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$larrContratosEstado = array(["nombre"=>"Faena", "descripcion"=>"", "entry_by" => 1], 
    		                         ["nombre"=>"No Faena", "descripcion"=>"", "entry_by" => 1]
    		                         );
    	foreach ($larrContratosEstado as $ContratosEstado) {
    		TblCentrosTipo::create($ContratosEstado);
    	}

    }
}
