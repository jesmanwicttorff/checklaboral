<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;


class BackupAccesos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RespaldoAccesos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Respalda los accesos de los empleados diariamente';

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
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
      \Log::info("Ejecutamos Cron: ".date('Y-m-d H:i'));
      $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();

      if($sitio->Valor=='Coca-Cola Andina'){
        \MySourcing::setConfiguracion();
        \MyLoadbatch::AccesosDiarios();
      }

    }
}
