<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;

class MaestroMovil extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MaestroMovil';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza Maestro Movil para el periodo en curso';

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
      \Log::info("Ejecutamos Cron Actualizacion MaestroMovil: ".date('Y-m-d H:i'));
      $email = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_EMAIL')->first();

        \Session::put('uid',1);
        \Session::put('CNF_EMAILAR',0);
        \Session::put('automatico',1);
        \Session::put('CNF_EMAIL',$email->Valor);

        $periodo = new \DateTime();
        $meses = 1;
        $idtipodocfiniquito = \DB::table('tbl_tipos_documentos')->where('IdProceso',4)->value('IdTipoDocumento');

        for($i=1;$i<=$meses;$i++) {
          $personas = \DB::table('tbl_personas')
            ->join('tbl_movimiento_personal','tbl_personas.IdPersona','=','tbl_movimiento_personal.IdPersona')
            ->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_movimiento_personal.contrato_id')
            ->where('tbl_movimiento_personal.IdAccion',1)
            ->where('tbl_movimiento_personal.FechaEfectiva','<=',$periodo->format('Y-m-31'))
            ->where('tbl_contrato.cont_fechaFin','>',$periodo->format('Y-m-01'))
            ->where('tbl_contrato.ContratoPrueba','<>',1)
            ->groupBy('tbl_personas.IdPersona','tbl_movimiento_personal.contrato_id')
            ->get();
          foreach ($personas as $persona) {
            $personaMaestro = \DB::table('tbl_personas_maestro_movil')
              ->where('idpersona',$persona->IdPersona)->where('periodo',$periodo->format('Y-m-01'))->where('contrato_id',$persona->contrato_id)->first();
              if(!$personaMaestro){
                $estadoFiniquito = \DB::table('tbl_documentos_rep_historico')
                  ->join('tbl_movimiento_personal','tbl_movimiento_personal.IdPersona','=','tbl_documentos_rep_historico.identidad')
                  ->where('tbl_documentos_rep_historico.IdTipoDocumento',$idtipodocfiniquito)
                  ->where('tbl_movimiento_personal.IdAccion',2)
                  ->where('tbl_documentos_rep_historico.identidad',$persona->IdPersona)
                  ->where('tbl_documentos_rep_historico.contrato_id',$persona->contrato_id)
                  ->where('tbl_movimiento_personal.contrato_id',$persona->contrato_id)
                  ->where('tbl_movimiento_personal.FechaEfectiva','<=',$periodo->format('Y-m-31'))
                  ->first();
                $estadoUltimoMovimiento = \DB::table('tbl_movimiento_personal')->where('IdPersona',$persona->IdPersona)->where('contrato_id',$persona->contrato_id)->orderBy('IdMovimientoPersonal','desc')->limit(1)->first();
                $debePapeles = \DB::table('tbl_documentos')->where('IdEntidad',$persona->IdPersona)->where('Entidad',3)->where('contrato_id',$persona->contrato_id)->where('IdEstatus','<>',5)->first();
                if($estadoFiniquito and $persona->FechaEfectiva <= $periodo->format('Y-m-31')){
                  if($estadoUltimoMovimiento->IdAccion==1){
                    if($estadoUltimoMovimiento->FechaEfectiva>=$estadoFiniquito->FechaEfectiva){
                      \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva]);
                    }
                  }else{
                    if($debePapeles){
                      \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$estadoFiniquito->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Finiquitado','FechaEfectiva'=>$estadoFiniquito->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
                    }else{
          						$check = \DB::table('tbl_personas_maestro_movil')->where('Estatus','Finiquitado')->where('idpersona',$persona->IdPersona)->where('contrato_id',$estadoFiniquito->contrato_id)->first();
          						if(!$check){
          							\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$estadoFiniquito->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Finiquitado','FechaEfectiva'=>$estadoFiniquito->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
          						}
          					}
                  }
                }else{
                  $estadoFiniquito = \DB::table('tbl_documentos')
                    ->join('tbl_movimiento_personal','tbl_movimiento_personal.IdPersona','=','tbl_documentos.identidad')
                    ->where('tbl_documentos.IdTipoDocumento',$idtipodocfiniquito)
                    ->where('tbl_movimiento_personal.IdAccion',2)
                    ->where('tbl_documentos.identidad',$persona->IdPersona)
                    ->where('tbl_documentos.contrato_id',$persona->contrato_id)
                    ->where('tbl_movimiento_personal.FechaEfectiva','<=',$periodo->format('Y-m-31'))
                    ->first();

                    if($estadoFiniquito){
                      if($estadoFiniquito->IdEstatus==5){
                        if($estadoUltimoMovimiento->IdAccion==1){
                          \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva]);
                        }else{
                          if($debePapeles){
                            \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Finiquitado','FechaEfectiva'=>$estadoFiniquito->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
                          }else{
          									$check = \DB::table('tbl_personas_maestro_movil')->where('Estatus','Finiquitado')->where('idpersona',$persona->IdPersona)->where('contrato_id',$estadoFiniquito->contrato_id)->first();
          									if(!$check){
          										\DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$estadoFiniquito->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Finiquitado','FechaEfectiva'=>$estadoFiniquito->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
          									}
          								}
                        }
                      }else{
                        $contratosPersona = \DB::table('tbl_contratos_personas')->where('IdPersona',$persona->IdPersona)->where('contrato_id',$persona->contrato_id)->first();
                        if($estadoUltimoMovimiento->IdAccion==1){
                          if($estadoUltimoMovimiento->FechaEfectiva>=$estadoFiniquito->FechaEfectiva and $contratosPersona){
                            if($persona->FechaEfectiva=='0000-00-00'){
          										$fechaInicioFaena=$contratosPersona->FechaInicioFaena;
          									}else{
        											$fechaInicioFaena = $persona->FechaEfectiva;
        										}
                            \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$fechaInicioFaena]);
                          }else{
                            \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Baja Observada','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
                          }
                        }else{
                          if($estadoUltimoMovimiento->FechaEfectiva>=$periodo->format('Y-m-01')){
                            \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva]);
                          }else{
                            \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$estadoFiniquito->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Baja Observada','FechaEfectiva'=>$estadoFiniquito->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$estadoFiniquito->FechaEfectiva]);
                          }
                        }
                      }
                    }else{
                      $contratosPersona = \DB::table('tbl_contratos_personas')->where('IdPersona',$persona->IdPersona)->where('contrato_id',$persona->contrato_id)->first();
                      if($estadoUltimoMovimiento->IdAccion==1){
                        if($contratosPersona){
                            \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva]);
                        }else{
                            \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Baja Observada','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$persona->FechaEfectiva]);
                        }
                      }else{
                        if($estadoUltimoMovimiento->FechaEfectiva>=$periodo->format('Y-m-31')){
                          \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Vigente','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva]);
                        }else{
                          \DB::table('tbl_personas_maestro_movil')->insert(['periodo'=>$periodo->format('Y-m-01'),'idpersona'=>$persona->IdPersona,'contrato_id'=>$persona->contrato_id,'idcontratista'=>$persona->IdContratista,'created_at'=>date('Y-m-d H:i'),'Estatus'=>'Baja Observada','FechaEfectiva'=>$persona->FechaEfectiva,'FechaInicioFaena'=>$persona->FechaEfectiva,'FechaFinFaena'=>$persona->FechaEfectiva]);
                        }
                      }
                    }
                }
              }
          }
        }

      \Log::info("FIN Cron Actualizacion MaestroMovil: ".date('Y-m-d H:i'));
    }
}
