<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReporteAccesoFaenaEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ReporteAccesoFaenaEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia Reporte Acceso CCU Faena por correo';

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
      \Log::info("Ejecutamos Cron reporte accesos CCU (EMAIL): ".date('Y-m-d H:i'));
      $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();

      if($sitio->Valor=='CCU'){
        app('App\Http\Controllers\PersonasController')->sendPersonaEmail();
      }
    }
}
