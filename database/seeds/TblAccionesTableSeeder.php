<?php

use Illuminate\Database\Seeder;
use App\Models\Acciones;

class TblAccionesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $listadoAcciones = array(["Nombre"=>"Creacion de contrato", "Descripcion"=>" Se Crea un contrato"],
            ["Nombre"=>"Extension de fecha", "Descripcion"=>"Se extiende la fecha del contrado"],
            ["Nombre"=>"Cambio de estatus", "Descripcion"=>"Se Cambia el estatus del contrato"],
            ["Nombre"=>"Cambio de tipo de contrato", "Descripcion"=>"Se cambia el tipo de contrato"]);


        foreach ($listadoAcciones as $lisAcciones) {
            Acciones::create($lisAcciones);
        }
    }
}
