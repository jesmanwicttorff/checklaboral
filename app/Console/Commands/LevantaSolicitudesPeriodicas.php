<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use App\Library\MyRequirements;

class LevantaSolicitudesPeriodicas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levante';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Levanta solicitudes de documentos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      \Log::info("Ejecutamos Cron solicitudes mensuales: ".date('Y-m-d H:i'));
      $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
      $email = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_EMAIL')->first();

    //  $this->info("Inicio: ".date('H:i'));

        \Session::put('uid',1);
        \Session::put('CNF_EMAILAR',0);
        \Session::put('automatico',1);
        \Session::put('CNF_EMAIL',$email->Valor);
        //evento: asigna personas a contratos, periodicidad:mensual
        $requisitos = MyRequirements::getRequirements(3,1);
        foreach ($requisitos as $requisito) {
          MyRequirements::Load($requisito->IdRequisito);
        }
        //evento: asigna personas a contratos, periodicidad:trimestral
        $requisitos = MyRequirements::getRequirements(3,2);
        foreach ($requisitos as $requisito) {
          MyRequirements::Load($requisito->IdRequisito);
        }
        //evento: asigna personas a contratos, periodicidad:semestral
        $requisitos = MyRequirements::getRequirements(3,3);
        foreach ($requisitos as $requisito) {
          MyRequirements::Load($requisito->IdRequisito);
        }

        //evento:creacion contratos, periodicidad:mensual
        $requisitos = MyRequirements::getRequirements(1,1);
        foreach ($requisitos as $requisito) {
          MyRequirements::Load($requisito->IdRequisito);
        }
        //evento:creacion contratos, periodicidad:trimestral
        $requisitos = MyRequirements::getRequirements(1,2);
        foreach ($requisitos as $requisito) {
          MyRequirements::Load($requisito->IdRequisito);
        }
        //evento:creacion contratos, periodicidad:semestral
        $requisitos = MyRequirements::getRequirements(1,3);
        foreach ($requisitos as $requisito) {
          MyRequirements::Load($requisito->IdRequisito);
        }

        //evento:asignacion activo a contratos, periodicidad:mensual
        $requisitos = MyRequirements::getRequirements(5,1);
        foreach ($requisitos as $requisito) {
          MyRequirements::Load($requisito->IdRequisito);
        }


      //$this->info("Fin: ".date('H:i'));
      \Log::info("FIN Cron solicitudes mensuales: ".date('Y-m-d H:i'));
    }
}
