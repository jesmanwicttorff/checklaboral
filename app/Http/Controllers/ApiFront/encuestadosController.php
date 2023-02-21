<?php

namespace App\Http\Controllers\ApiFront;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Http\Controllers\AccesosController;
use App\Models\Gruposespecificos;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class encuestadosController extends Controller
{

  public function resultadoNotaEncuesta( Request $request){

     $contrato_id = $request->contrato_id;
     $list=[];

     $encuestas = \DB::table('tbl_documentos')
      ->select('tbl_documentos.IdDocumento','tbl_documentos.IdTipoDocumento','tbm_encuestas_documentos.encuesta_id','tbl_kpi.kpi_id')
      ->join('tbm_encuestas_documentos','tbm_encuestas_documentos.IdDocumento','=','tbl_documentos.IdDocumento')
      ->join('tbm_encuestas','tbm_encuestas.encuesta_id','=','tbm_encuestas_documentos.encuesta_id')
      ->join('tbl_kpi','tbl_kpi.encuesta_id','=','tbm_encuestas.encuesta_id')
      ->where('tbl_documentos.contrato_id', $contrato_id)
      ->where('tbl_documentos.Entidad','=', 2)
      ->whereRaw('tbm_encuestas_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento')
      ->whereRaw('tbm_encuestas_documentos.contrato_id = tbl_documentos.contrato_id')
      ->groupBy('tbl_documentos.IdDocumento')
      ->get();

    $j=0;
     if(count($encuestas)>0){
       foreach ($encuestas as $encuesta) {
          $notaexistente = \DB::table('tbl_encuesta_cuatrimestre')->where('contrato_id',$contrato_id)->where('encuesta_id',$encuesta->encuesta_id)->where('kpi_id',$encuesta->kpi_id)->first();
          if(!$notaexistente){
            $tableData = \DB::table("tbl_documento_valor")
            ->selectRaw("tbm_encuestas_categorias_preguntas.categoriaPregunta_id as IdCategoria, tbl_documento_valor.valor,tbm_relacion_encuesta_categoria_pregunta.ponderacion as ponderado_pregunta,tbm_encuestas_categorias_preguntas.tituloCategoria as categoria")
            ->join("tbm_relacion_encuesta_categoria_pregunta","tbm_relacion_encuesta_categoria_pregunta.pregunta_id","=","tbl_documento_valor.IdTipoDocumentoValor")
            ->join("tbm_encuestas_categorias_preguntas","tbm_encuestas_categorias_preguntas.categoriaPregunta_id","=","tbm_relacion_encuesta_categoria_pregunta.categoriaPregunta_id")
            ->join('tbm_encuestas_documentos','tbm_encuestas_documentos.encuesta_id','=','tbm_relacion_encuesta_categoria_pregunta.encuesta_id')
            ->join('tbl_documentos','tbl_documentos.IdDocumento','=','tbl_documento_valor.IdDocumento')
            ->where("tbm_relacion_encuesta_categoria_pregunta.encuesta_id","=",$encuesta->encuesta_id)
            ->where('tbm_encuestas_documentos.contrato_id',$contrato_id)
            ->where('tbl_documentos.contrato_id',$contrato_id)
            ->where('tbl_documentos.IdDocumento',$encuesta->IdDocumento)
            ->groupBy('tbl_documento_valor.IdDocumentoValor')
            ->get();

            $size = count($tableData);

            if($size==0){
              return response()->json([
                'status'=>'error',
                'message'=>'No hay información de notas para este contrato'
              ]);
            }

            // Calculo de la calificacion por categoria y nota final
            $notaFinalCategoria=0;
            $notaFinal=0;
            $idUltimaCategoria= null;
            $idCategoriaActual = null;
            $calificacion=0;
            $categoriaData = array();

            $idcat = 0;
            foreach ($tableData as $item) {
              if($idcat!=$item->IdCategoria){
                array_push($categoriaData,['id'=>$item->IdCategoria]);
                $idcat=$item->IdCategoria;
              }
            }

            foreach ($categoriaData as $id) {
              foreach($tableData as $item){

                $respuestaValor = $item->valor;
                $ponderado = $item->ponderado_pregunta;
                $idcategoria = $item->IdCategoria;

                  if($idcategoria == $id['id']){
                    $calificacion = ($respuestaValor * ($ponderado/100));
                    $notaFinalCategoria += $calificacion;
                  }

              }

              $notaFinal += $notaFinalCategoria;
              $year = date('Y');
              $fecha = date('Y-m-d');

              \DB::table('tbl_encuesta_cuatrimestre')->insert(["contrato_id"=>$contrato_id,
              "kpi_id"=>$encuesta->kpi_id, "notafinal" =>$notaFinalCategoria,"categoria_id" =>$id['id'],
              "periodo" =>$year,"IdTipoDocumento"=>$encuesta->IdTipoDocumento,"encuesta_id"=>$encuesta->encuesta_id,"fechaCreate"=>$fecha,
              "fechaUpdate"=>$fecha]);

              $notaFinalCategoria =0;
           }
         }
         $EncuestaCuatrimestre = \DB::table('tbl_encuesta_cuatrimestre')
             ->selectRaw("tbl_kpi.descripcion as KPI , tbl_kpi.porcentaje as ponderadoKpi, tbm_encuestas_categorias_preguntas.tituloCategoria as categoria, sum(tbl_encuesta_cuatrimestre.notafinal) as notafinal ")
             ->join("tbm_encuestas_categorias_preguntas","tbm_encuestas_categorias_preguntas.categoriaPregunta_id","=","tbl_encuesta_cuatrimestre.categoria_id")
             ->join("tbl_kpi","tbl_kpi.kpi_id","=","tbl_encuesta_cuatrimestre.kpi_id")
             ->join("tbl_kpi_encuesta","tbl_kpi_encuesta.encuesta_id","=","tbl_encuesta_cuatrimestre.encuesta_id")
             ->where("tbl_encuesta_cuatrimestre.contrato_id",$request->contrato_id)
             ->where('tbl_encuesta_cuatrimestre.encuesta_id',$encuesta->encuesta_id)
             ->orderBy("tbl_encuesta_cuatrimestre.categoria_id","asc")
             ->orderBy("tbl_encuesta_cuatrimestre.kpi_id","asc")
             ->get();

         //$periodo = self::getCuatrimestre(date('m-d'));
         $periodo = self::getCuatrimestre($encuesta->encuesta_id);

         foreach ($EncuestaCuatrimestre as $key => $doc_nota){
           $list[$j] = [
             'Cuatrimestre'=> $periodo,
             'NombreKpi' => $doc_nota->KPI,
             'Ponderacion_Kpi' => $doc_nota->ponderadoKpi,
             'Categoria' => $doc_nota->categoria,
             'NotaFinal' => round($doc_nota->notafinal,1)
           ];

           }
           $j++;
       }
     }else{
       return response()->json([
       'status'=>'error',
       'message'=>'no hay información para este contrato'
       ]);
     }

     return response()->json([
       'status'=>'success',
       'code'=> 200,
       'KPi_List' => $list,
     ]);

  }

  public static function getCuatrimestre($encuesta_id){

    $encuesta = \DB::table('tbm_encuestas')->where('encuesta_id',$encuesta_id)->first();
    $periodicidad = $encuesta->periodicidad;
    $periodo = $encuesta->periodo;

    //esto para soportar distintos tipos de periodicidad
    //por ahora solo Cuatrimestral
    switch ($periodicidad) {
      case '1':
        return $periodo;
        break;

      default:
        // code...
        break;
    }

  }

  public function listKpi(Request $request){
    $contrato_id=$request->contrato_id;
    $consultaKpi = \DB::table('tbl_kpi')->select('tbl_kpi.kpi_id','tbl_kpi.descripcion','tbl_kpi.porcentaje')->where('contrato_id',$contrato_id)->get();

    if($consultaKpi) {
      foreach ($consultaKpi as $key => $kpi){
        $list[$key] = [
          'kpi_id' => $kpi->kpi_id,
          'descripcion' => $kpi->descripcion,
          'porcentaje' => $kpi->porcentaje
        ];
      }
      return response()->json([
        'success'=>true,
        'code'=> 200,
        'Kpi'=> $list
        ]);
    }else{
      return response()->json(array(
        'status'=>'error',
        'message'=> 'No hay Kpi asociado'
      ));
    }
  }

  public function NotaFinalRanking(Request $request){
    $year =  date('Y');
    $idcont = 0;
    $calificacionFinal=0;
    $notaFinal=0;
    $ranking=1;
    $contratoData=array();
    $Data=array();
    $Data2=array();
    $objRanking=array();
    $posicion=1;
    $ranking=array();
    $calificacionMaxima=0;

    $contraobj =\DB::table('tbl_contrato')
      ->where('tbl_contrato.ContratoPrueba','<>',1)
      ->where('tbl_contrato.cont_estado',1)
      ->count();

    // busco la nota final del contrato solicitado
    $QueryEncuestaCuatrimestre =\DB::table('tbl_encuesta_cuatrimestre')
      ->selectRaw("sum(notafinal) as notafinal")
      ->where('tbl_encuesta_cuatrimestre.contrato_id',"=",$request->contrato_id)
      ->where('tbl_encuesta_cuatrimestre.periodo',"=",$year)
      ->first();
      $nota1 =$QueryEncuestaCuatrimestre->notafinal;
      if($nota1!=NULL){

            $objRankingGeneral =\DB::table('tbl_encuesta_cuatrimestre')
            ->selectRaw("contrato_id,notafinal")
            ->whereRaw('tbl_encuesta_cuatrimestre.periodo = '.$year)
            ->groupBy('contrato_id')
            ->orderBy("tbl_encuesta_cuatrimestre.contrato_id","asc")
            ->get();

            foreach ($objRankingGeneral as $item) {
              if($idcont!=$item->contrato_id){
                array_push($contratoData,['id'=>$item->contrato_id]);
                $idcont=$item->contrato_id;
              }
            }
         $calificacionFinal=0;
            foreach ($contratoData as $id) {
              foreach($objRankingGeneral as $item){
                $nota = $item->notafinal;
                $contrato_id = $item->contrato_id;
                  if($contrato_id == $id['id'])
                  {
                    $calificacionFinal = $nota + $calificacionFinal;
                    array_push($Data,['contrato_id'=>$id['id'],'nota'=>$calificacionFinal]);
                  }else{
                    $calificacionFinal=0;
                  }
              }
            }
            $size = count($Data);
             for($i=0;$i<$size;$i++) {
               if($i<$size-1){
               if($Data[$i]['contrato_id'] == $Data[$i+1]['contrato_id'])
               {
                 if($Data[$i]['nota'] < $Data[$i+1]['nota']){
                   $notaMax=$Data[$i+1]['nota'];
              }else{
                $notaMax=$Data[$i]['nota'];
              }
              array_push($Data2,['nota'=>$notaMax]);
            }else {
              $notaMax=$Data[$i]['nota'];
              array_push($Data2,['nota'=>$notaMax]);
            }
          }
        }
        $tam = count($Data2);
        /// Ordenamos las notas de mayor a menor
     usort($Data2, function($a, $b)
     {
      return strcmp($b["nota"], $a["nota"]);
    });
    $i=0;
    $objRanking['nota'] = $Data2;
    for($i=0;$i<$tam;$i++) { // calculo el ranking
      if($objRanking['nota'][$i]== 10){
          $posicion=1;
        array_push($ranking,['posicion'=>$posicion,$objRanking['nota'][$i]]);
        $posicion= $posicion+1;
      }else{
      array_push($ranking,['posicion'=>$posicion,$objRanking['nota'][$i]]);
      $posicion= $posicion+1;
      }
    }

    for($i=0;$i<$tam;$i++){
      if($ranking[$i][0]['nota']==$nota1){
        $calificacionMaxima = $nota1;
        $posicion = $ranking[$i]['posicion'];
      }
    }
    return response()->json([
      'success'=>true,
      'code'=> 200,
      'CantidadContratos' => $contraobj,
      'Ranking'=> $posicion,
      'NotaFinal'=>round($calificacionMaxima)
    ]);

  }else{
    return response()->json(array(
      'status'=>'error',
      'message'=> 'Este contrato no tiene encuesta'
    ));
    }

  }

  public function getComentariosEncuesta(Request $request){
    $contrato_id = $request->contrato_id;
    $ano = date('Y');

    $encuestas = \DB::table('tbm_encuestas_documentos')
      ->join('tbl_documentos','tbl_documentos.IdDocumento','=','tbm_encuestas_documentos.IdDocumento')
      ->join('tbm_encuestas','tbm_encuestas.encuesta_id','=','tbm_encuestas_documentos.encuesta_id')
      ->where('tbl_documentos.FechaEmision','>',$ano."-01-01")
      ->where('tbl_documentos.FechaEmision','<',$ano."-12-31")
      ->where('tbm_encuestas_documentos.contrato_id',$contrato_id)->get();
    $coment=[];
    foreach ($encuestas as $encuesta) {
      array_push($coment,['comentario'=>$encuesta->observacion]);
    }

    return response()->json($coment);

  }

  public function getQr(Request $request){
    $url = url('user/login/validate?contrato='.base64_encode($request->contrato_id));
    $qr= QrCode::size(150)->color(0,128,255)->generate($url);
    return $qr;
  }

  //esto hay que hacerlo nuevamente, es lo más rapido que se me ocurre
  public function postSaveComentario(Request $request){
    $contrato_id = $request->contrato_id;
    $encuestas = \DB::table('tbm_encuestas_documentos')->where('contrato_id',$contrato_id)->orderBy('encuesta_id','asc')->get();
    $ob[1] = $request->text1;
    $ob[2] = $request->text2;
    $ob[3] = $request->text3;

    $i=1;
    foreach ($encuestas as $encuesta) {
      $data = \DB::table('tbm_encuestas')->where('encuesta_id',$encuesta->encuesta_id)->first();
      if($data->observacion=='' or is_null($data->observacion)){
        \DB::table('tbm_encuestas')->where('encuesta_id',$encuesta->encuesta_id)->update(['observacion'=>$ob[$i],'updated_at'=>date('Y-m-d H:i')]);
      }
      $i++;
    }

    return response()->json([
      'status'=>'success',
      'message'=>'Comentarios guardados'
    ]);

  }

}
