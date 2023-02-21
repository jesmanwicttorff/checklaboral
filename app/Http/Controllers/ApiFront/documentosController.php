<?php

namespace App\Http\Controllers\ApiFront;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Http\Controllers\AccesosController;
use App\Models\Gruposespecificos;

class documentosController extends Controller
{

  public function postSaveDoc(Request $request){
    $request = (clone request());
    $response = app('App\Http\Controllers\DocumentosController')->postSave($request);

    return $response;
  }

  public function getTiposDocumentos($tipo=null){
    $documentos = DB::table('tbl_tipos_documentos')->select('IdTipoDocumento','Entidad','Descripcion','IdFormato')->get();

    if(isset($tipo)){
      return $documentos;
    }

    return response()->json($documentos,200);
  }
}
