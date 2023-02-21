<?php

namespace App\Library;

use Carbon\Carbon;

class MyAccess
{
	/**
	*
	*
	* @author Diego Díaz - SCP
	* @package MyAccess
	*/
    static public function CheckAccessLog($pintIdTipoEntidad, $pintIdTipoSubEntidad, $pintIdEntidad, $pstrValorConsulta, $pintIdAreaTrabajo, $pintIdAcceso){
    	\DB::enableQueryLog();
    	$ldatInit = Carbon::today();
    	$ldatEnd = Carbon::now();
    	$lobjAccessLog = \DB::table('tbl_accesos_log')
               ->select("tbl_accesos_log.IdTipoAcceso","tbl_accesos_log.createdOn", \DB::raw("tbl_centro.Descripcion as Centro"), \DB::raw("tbl_area_de_trabajo.Descripcion as Area"))
               ->join("tbl_area_de_trabajo","tbl_area_de_trabajo.IdAreaTrabajo","=","tbl_accesos_log.IdAreaTrabajo")
               ->join("tbl_centro","tbl_area_de_trabajo.IdCentro","=","tbl_centro.IdCentro")
               ->where("tbl_accesos_log.IdTipoEntidad","=",$pintIdTipoEntidad)
				    	 ->where("tbl_accesos_log.IdTipoEntidad","=",$pintIdTipoEntidad)
				    	 ->where("tbl_accesos_log.IdTipoSubEntidad","=",$pintIdTipoSubEntidad);
               if ($pintIdTipoEntidad==1 && !is_null($pstrValorConsulta) ){
                $lobjAccessLog = $lobjAccessLog->where(\DB::raw("REPLACE(tbl_accesos_log.data_rut,'-','')"),"=",$pstrValorConsulta);
               }else{
				    	   $lobjAccessLog = $lobjAccessLog->where("tbl_accesos_log.IdEntidad","=",$pintIdEntidad);
				    	 }
               if (!is_null($pintIdAcceso)) {
                  $lobjAccessLog = $lobjAccessLog->where("tbl_accesos_log.IdAcceso","=",$pintIdAcceso);
               }
               $lobjAccessLog = $lobjAccessLog->where("tbl_accesos_log.createdOn",">",$ldatInit)
				    	 ->where("tbl_accesos_log.createdOn","<",$ldatEnd)
				    	 ->orderby('tbl_accesos_log.IdAccesoLog','DESC')
				    	 ->take(1)
				    	 ->get();
		if ($lobjAccessLog) {
      $lobjAccessLog[0]->createdOn = \MyFormats::FormatDateTime($lobjAccessLog[0]->createdOn);
      if ($lobjAccessLog[0]->IdTipoAcceso==1) {
          $lintIdAcceso = 2;
      }else{
          $lintIdAcceso = 1;
      }
			return array("code"=>1,"msgcode"=>"", "result"=>$lintIdAcceso, "data" => $lobjAccessLog[0]);
		}else{
			return array("code"=>1,"msgcode"=>"", "result"=>1, "data"=>"");
		}

    }

    static public function RegisterAccess($pintIdTipoEntidad, $pintIdTipoSubEntidad, $pintIdEntidad, $pstrValorConsulta, $pintIdAreaTrabajo, $pintIdAcceso){

        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');

        $lobjCheck = self::CheckAccessLog($pintIdTipoEntidad, $pintIdTipoSubEntidad, $pintIdEntidad, $pstrValorConsulta, $pintIdAreaTrabajo, null);

        if ($lobjCheck['code']==1){
            $larrData = array("IdTipoEntidad"=>$pintIdTipoEntidad,
                              "IdTipoSubEntidad"=>$pintIdTipoSubEntidad,
                              "IdEntidad"=>$pintIdEntidad,
                              "IdAreaTrabajo"=>$pintIdAreaTrabajo,
                              "createdOn"=>Carbon::now(),
                              "entry_by"=>$lintIdUser,
                              "IdTipoAcceso"=>$lobjCheck['result'],
                              "IdAcceso" => $pintIdAcceso
                              );

            $lintIdAccessLog = \DB::table("tbl_accesos_log")
                               ->insert($larrData);
        }

        return $lobjCheck;
    }

    static public function CheckAccess($pintIdTipoEntidad, $pintIdTipoSubEntidad, $pstValorEntidad, $pintIdAreaTrabajo,$uen_id=null){

      $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
        //Chequeamos si la entidad tiene acceso
        if ($pintIdTipoEntidad==1){ //es personas

          if($sitio->Valor=='CCU'){
            $lobjAccess2 = \DB::table('tbl_accesos')
            ->select('tbl_accesos.IdTipoAcceso',
                    \DB::raw('tbl_accesos.IdEstatusUsuario as IdEstatus'),
                    'tbl_accesos.IdAcceso',
                    'tbl_uen_ct.ct_id as IdAreaTrabajo',
                    \DB::raw('tbl_accesos.IdAcceso as IdEntidad'),
                    "tbl_accesos.Observacion",
                    \DB::raw("case 	when tbl_accesos.IdTipoAcceso = 1 then 'Trabajador'
                                    when tbl_accesos.IdTipoAcceso = 2 then 'Visitante'
                                    when tbl_accesos.IdTipoAcceso = 3 then 'Provisional'
                                    else '-' end as TipoAcceso"), 'tbl_accesos.FechaInicio', 'tbl_accesos.FechaFinal')
            ->leftJoin('tbl_acceso_areas','tbl_accesos.IdAcceso', '=',  \DB::raw('tbl_acceso_areas.IdAcceso'))
            ->leftJoin('tbl_uen_ct','tbl_uen_ct.uenct_id','=','tbl_acceso_areas.IdAreaTrabajo')
            ->where("tbl_accesos.FechaInicio", "<=", date('Y-m-d') )
            ->where(\DB::raw("REPLACE(tbl_accesos.data_rut,'-','')"), '=', $pstValorEntidad)
            ->where('tbl_uen_ct.ct_id',$pintIdAreaTrabajo)
            ->where('tbl_uen_ct.uen_id',$uen_id)
            ->where("tbl_accesos.FechaInicio", "<=", date('Y-m-d') )
            ->where("tbl_accesos.IdTipoAcceso","!=",1);

            $lobjAccess = \DB::table('tbl_personas')
            ->select(	'tbl_accesos.IdTipoAcceso',
                \DB::raw('tbl_accesos.IdEstatusUsuario as IdEstatus'),
                'tbl_accesos.IdAcceso',
                'tbl_uen_ct.ct_id as IdAreaTrabajo',
                \DB::raw('tbl_personas.IdPersona as IdEntidad'),
                "tbl_accesos.Observacion",
                \DB::raw("case 	when tbl_accesos.IdTipoAcceso = 1 then 'Trabajador'
                                when tbl_accesos.IdTipoAcceso = 2 then 'Visitante'
                                when tbl_accesos.IdTipoAcceso = 3 then 'Provisional' else '-'
                                end as TipoAcceso"), 'tbl_accesos.FechaInicio', 'tbl_accesos.FechaFinal')
            ->leftJoin('tbl_accesos', 'tbl_accesos.IdPersona', '=', 'tbl_personas.IdPersona')
            ->leftJoin('tbl_acceso_areas','tbl_accesos.IdAcceso', '=',  \DB::raw('tbl_acceso_areas.IdAcceso'))
            ->leftJoin('tbl_uen_ct','tbl_uen_ct.uenct_id','=','tbl_acceso_areas.IdAreaTrabajo')
            ->where(\DB::raw("REPLACE(tbl_personas.RUT,'-','')"), '=', $pstValorEntidad)
            ->where('tbl_uen_ct.ct_id',$pintIdAreaTrabajo)
            ->where('tbl_uen_ct.uen_id',$uen_id)
            ->where("tbl_accesos.FechaInicio", "<=", date('Y-m-d') )
            ->where("tbl_accesos.IdTipoAcceso","=",1)
            ->union($lobjAccess2)
			      ->orderBy("IdTipoAcceso","ASC")
            ->get();
          }else{
            $lobjAccess2 = \DB::table('tbl_accesos')
            ->select('tbl_accesos.IdTipoAcceso',
                    \DB::raw('tbl_accesos.IdEstatusUsuario as IdEstatus'),
                    'tbl_accesos.IdAcceso',
                    'tbl_acceso_areas.IdAreaTrabajo',
                    \DB::raw('tbl_accesos.IdAcceso as IdEntidad'),
                    "tbl_accesos.Observacion",
                    \DB::raw("case 	when tbl_accesos.IdTipoAcceso = 1 then 'Trabajador'
                                    when tbl_accesos.IdTipoAcceso = 2 then 'Visitante'
                                    when tbl_accesos.IdTipoAcceso = 3 then 'Provisional'
                                    else '-' end as TipoAcceso"), 'tbl_accesos.FechaInicio', 'tbl_accesos.FechaFinal')
            ->leftJoin('tbl_acceso_areas','tbl_accesos.IdAcceso', '=',  \DB::raw('tbl_acceso_areas.IdAcceso AND tbl_acceso_areas.IdAreaTrabajo = '.$pintIdAreaTrabajo))
            ->where("tbl_accesos.FechaInicio", "<=", date('Y-m-d') )
            ->where(\DB::raw("REPLACE(tbl_accesos.data_rut,'-','')"), '=', $pstValorEntidad)
            ->where("tbl_accesos.FechaInicio", "<=", date('Y-m-d') )
            ->where("tbl_accesos.IdTipoAcceso","!=",1);

            $lobjAccess = \DB::table('tbl_personas')
            ->select(	'tbl_accesos.IdTipoAcceso',
                \DB::raw('tbl_accesos.IdEstatusUsuario as IdEstatus'),
                'tbl_accesos.IdAcceso',
                'tbl_acceso_areas.IdAreaTrabajo',
                \DB::raw('tbl_personas.IdPersona as IdEntidad'),
                "tbl_accesos.Observacion",
                \DB::raw("case 	when tbl_accesos.IdTipoAcceso = 1 then 'Trabajador'
                                when tbl_accesos.IdTipoAcceso = 2 then 'Visitante'
                                when tbl_accesos.IdTipoAcceso = 3 then 'Provisional' else '-'
                                end as TipoAcceso"), 'tbl_accesos.FechaInicio', 'tbl_accesos.FechaFinal')
            ->leftJoin('tbl_accesos', 'tbl_accesos.IdPersona', '=', 'tbl_personas.IdPersona')
            ->leftJoin('tbl_acceso_areas','tbl_accesos.IdAcceso', '=',  \DB::raw('tbl_acceso_areas.IdAcceso AND tbl_acceso_areas.IdAreaTrabajo = '.$pintIdAreaTrabajo))
            ->where(\DB::raw("REPLACE(tbl_personas.RUT,'-','')"), '=', $pstValorEntidad)
            ->where("tbl_accesos.FechaInicio", "<=", date('Y-m-d') )
            ->where("tbl_accesos.IdTipoAcceso","=",1)
            ->union($lobjAccess2)
			      ->orderBy("IdTipoAcceso","ASC")
            ->get();
          }

        }else{ //es activo
            $lobjAccess = \DB::table('tbl_activos_detalle')
                          ->select('tbl_accesos_activos.IdTipoAcceso', 'tbl_accesos_activos.IdEstatus','tbl_accesos_activos.IdAccesoActivo', 'tbl_acceso_activos_areas.IdAreaTrabajo', \DB::raw('tbl_activos_data.IdActivoData as IdEntidad'),\DB::raw("'' as Observacion"), \DB::raw("'' as TipoAcceso") )
                          ->join('tbl_activos_data','tbl_activos_detalle.IdActivo','=','tbl_activos_data.IdActivo')
                          ->join('tbl_activos_data_detalle','tbl_activos_detalle.IdActivoDetalle','=',\DB::raw('tbl_activos_data_detalle.IdActivoDetalle and tbl_activos_data.IdActivoData = tbl_activos_data_detalle.IdActivoData'))
                          ->leftJoin('tbl_accesos_activos', 'tbl_accesos_activos.IdActivoData', '=', 'tbl_activos_data.IdActivoData')
                          ->leftJoin('tbl_acceso_activos_areas','tbl_accesos_activos.IdAccesoActivo','=',\DB::raw('tbl_acceso_activos_areas.IdAccesoActivo AND tbl_acceso_activos_areas.IdAreaTrabajo = '.$pintIdAreaTrabajo))
                          ->where('tbl_activos_detalle.Unico','=','SI')
                          ->where('tbl_activos_data_detalle.valor','=',$pstValorEntidad)
                          ->orderBy("tbl_accesos_activos.IdAccesoActivo","DESC")
                          ->get();
        }

        //Recorremos el arreglo buscando al menos una acceso activo
        $lintResult = 0;
        $lintIdEntidad = 0;
        $fechaActual = date('Y-m-d');
        $flag=false;
        $flag2=false;
        if ($lobjAccess){
            foreach ($lobjAccess as $larrAccess) {
              if ($pintIdTipoEntidad==1){
                if ($larrAccess->IdEstatus==1 || $larrAccess->IdEstatus==2){
                    $lintResult = 1;
                    $lintIdAcceso = $larrAccess->IdAcceso;
                    if ($larrAccess->IdAreaTrabajo==$pintIdAreaTrabajo){
                      if($larrAccess->FechaInicio<=$fechaActual and $larrAccess->FechaFinal>=$fechaActual){
                        $flag=true; $flag2=false; break;
                        /*
                        return array(
                            'code'=>'1',
                            'msgcode'=>"Existe la Persona y tiene acceso al area",
                            'result'=>array("IdEntidad"=>$lintIdEntidad,"IdAcceso"=>$lintIdAcceso,"Data"=>$larrAccess)
                        );
                        */
                      }
                      else{
                        $flag2=true;
                        /*
                        return array(
                            'code'=>'4',
                            'msgcode'=>"Pase Vencido",
                            'result'=>array("IdEntidad"=>$lintIdEntidad,"IdAcceso"=>$lintIdAcceso,"Data"=>$larrAccess)
                        );
                        */
                      }
                    }
                }
              }
              else{
                $lintIdEntidad=2;
                if($lintIdEntidad==0){
                    $lintIdEntidad = 0;
                    if ($larrAccess->IdEstatus==1 || $larrAccess->IdEstatus==3){
                        $lintResult = 1;
                        $lintIdAcceso = $larrAccess->IdAcceso;
                        if ($larrAccess->IdAreaTrabajo==$pintIdAreaTrabajo){
                            return array(
                                'code'=>'1',
                                'msgcode'=>"Existe y tiene acceso al area",
                                'result'=>array("IdEntidad"=>$lintIdEntidad,"IdAcceso"=>$lintIdAcceso,"Data"=>$larrAccess)
                            );
                        }
                    }
                }else {
                    if ($larrAccess->IdEstatus==1 || $larrAccess->IdEstatus==2){
                        $lintResult = 1;
                        $lintIdAcceso = $larrAccess->IdAccesoActivo;
                        if ($larrAccess->IdAreaTrabajo==$pintIdAreaTrabajo){
                            return array(
                                'code'=>'1',
                                'msgcode'=>"Existe y tiene acceso al area",
                                'result'=>array("IdEntidad"=>$lintIdEntidad,"IdAcceso"=>$lintIdAcceso,"Data"=>$larrAccess)
                            );
                        }
                    }
                }
            }
          }

          if($flag){
            return array(
                'code'=>'1',
                'msgcode'=>"Existe la Persona y tiene acceso al area",
                'result'=>array("IdEntidad"=>$lintIdEntidad,"IdAcceso"=>$lintIdAcceso,"Data"=>$larrAccess)
            );
          }

          if($flag2){
            return array(
                'code'=>'4',
                'msgcode'=>"Pase Vencido",
                'result'=>array("IdEntidad"=>$lintIdEntidad,"IdAcceso"=>$lintIdAcceso,"Data"=>$larrAccess)
            );

          }

          if ($pintIdTipoEntidad==1){
            if ($lintResult==0){
              if ($lobjAccess[0]->IdTipoAcceso) {
                  $lintIdAcceso = $larrAccess->IdAcceso;
                return array(
                       'code'=>'2',
                       'msgcode'=>"Existe la persona pero sin acceso aprobado",
                       'result'=>array("IdEntidad"=>$lintIdEntidad,"IdAcceso"=>$lintIdAcceso,"Data"=>$lobjAccess[0])
                       );
              }else{
                  $lintIdAcceso = $larrAccess->IdAcceso;
                return array(
                       'code'=>'4',
                       'msgcode'=>"Existe la persona pero no existe el acceso",
                       'result'=>array("IdEntidad"=>$lintIdEntidad,"IdAcceso"=>$lintIdAcceso,"Data"=>$lobjAccess[0])
                       );
              }
            }else{
                $lintIdAcceso = $larrAccess->IdAcceso;
                return array(
                       'code'=>'3',
                       'msgcode'=>"Existe tiene acceso pero no al area",
                       'result'=>array("IdEntidad"=>$lintIdEntidad,"IdAcceso"=>$lintIdAcceso,"Data"=>$lobjAccess[0])
                       );
            }
          }else{
            $lintIdAcceso = $larrAccess->IdAccesoActivo;
            return array(
                   'code'=>'4',
                   'msgcode'=>"Existe activo, no tiene acceso",
                   'result'=>array("IdEntidad"=>$lintIdEntidad,"IdAcceso"=>$lintIdAcceso,"Data"=>$lobjAccess[0])
                   );
          }
        }
        return array(
               'code'=>'0',
               'msgcode'=>"No existe",
               'result'=>array()
               );
    }

}
?>
