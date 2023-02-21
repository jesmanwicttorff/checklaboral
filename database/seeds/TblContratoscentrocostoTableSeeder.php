<?php

use Illuminate\Database\Seeder;
use App\Models\Contratoscentrocosto;


class TblContratoscentrocostoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $listadoData = \DB::table('tbl_contrato')->select('contrato_id','claseCosto_id')->get();


        foreach ($listadoData as $lisData) {
            DB::table('tbl_contratos_centrocosto')->insert([
                ['contrato_id' => $lisData->contrato_id, 'centrocosto_id' => $lisData->claseCosto_id]]);
        }


    }
}
