<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class f30Tesoreria extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:f30tesoreria';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //
        $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
      
        if($sitio->Valor=='Abastible'){
          app('App\Http\Controllers\DocumentosController')->f30Tesoreria();
        }else{
            echo "No es un comando para este sitio";
        }
    }
}
