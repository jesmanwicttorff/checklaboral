<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(TbModuleSeeder::class);
        $this->call(TblConfiguracionSeeder::class);
        $this->call(TblContratosEstadoSeeder::class);
        $this->call(TblKpiTipoSeeder::class);

        Model::reguard();
    }
}
