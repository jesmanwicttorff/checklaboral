<?php

namespace App\Http\Controllers\ApiFront;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Http\Controllers\AccesosController;
use App\Models\Gruposespecificos;
use App\Models\Contratosservicios;
use App\Models\Tiposcontratospersonas;
use App\Models\Contratos;
use App\Models\Contratistas;

class contratistasController extends Controller
{

  public $module = 'contratistas';

  public function __construct() {

      parent::__construct();
      $this->model = new Contratistas();
      $this->info = $this->model->makeInfo( $this->module);
  		$this->access = $this->model->validAccess($this->info['id']);

  }

  public function postSave( Request $request, $id =0){

    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');

    if ($lintLevelUser == "15") { // Valida si el perfil es precontratista

      $lobjContratistas = \DB::table('tbl_contratistas')
      ->where('tbl_contratistas.entry_by_access','=',$lintIdUser)
      ->get(); //Consulta si el usuario tiene ya una empresa creada

      if ($lobjContratistas){ // Si tiene una empresa creada no le permitimos crear más
        return response()->json(array(
          'message'	=> 'No puede crear más de un registro contratista',
          'status'	=> 'error'
        ));
      }

    }

    $subcontrarista = $request->IdSubContratista;
    $rules = $this->validateForm();
    $validator = Validator::make($request->all(), $rules);
    if ($validator->passes()) {
      $data = $this->validatePost('tbl_contratistas');

      if (empty($data['entry_by_access'])){
          $data['entry_by_access'] = $lintIdUser;
      }

      if($request->has('mandante') and $request->input('mandante')==1){
        $data['is_mandante'] = 1;
      }else{
        $data['is_mandante'] = 0;
      }

      //validamos que el rut ya no exista
      if (isset($data['RUT'])){
          $lobjContrato = \DB::table('tbl_contratistas')
            ->where('tbl_contratistas.RUT', '=', $data['RUT'])
            ->where('tbl_contratistas.IdContratista', '!=', $request->input('IdContratista'))
            ->get();
          if ($lobjContrato){
              return response()->json(array(
                'message'   => "El rut ya se encuentra asignado",
                'status'    => 'error'
             ));
          }
      }
      if($request->giroIds){
        $giros = $request->giroIds;
        $cantidadGiros = count($giros) >= 4 ? 4 : count($giros);
        for ($i=1; $i <= $cantidadGiros; $i++) {
          $data['IdGiro_'.$i] = (int) $giros[$i-1];
        }
      }
      $id = $this->model->insertRow($data , $request->input('IdContratista'));

      //$this->detailviewsave( $this->modelview , $request->all() ,$this->info['config']['subform'] , $id) ;
      if (!(empty($subcontrarista))) {
        \DB::table('tbl_subcontratistas')->where('IdContratista', '=', $id)->delete();
        foreach ($subcontrarista as $sub) {
        $Id = \DB::table('tbl_subcontratistas')->insertGetId(
            ['IdContratista' => $id, 'SubContratista' => $sub]);
        }
      }else{
        \DB::table('tbl_subcontratistas')->where('IdContratista', '=', $id)->delete();
      }
      return response()->json(array(
        'status'=>'success',
        'message'=> \Lang::get('core.note_success')
      ));
    } else {
      $message = $this->validateListError(  $validator->getMessageBag()->toArray() );
      return response()->json(array(
        'message'	=> $message,
        'status'	=> 'error'
      ));
    }

  }

  public function postCompruebanumerorut(Request $request){
   $lstrRut = $request->rut;

   $larrResultado = array();
   $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
   $lintIdUser = \Session::get('uid');

   $lobjContratista = \DB::table('tbl_contratistas')
       ->where('tbl_contratistas.RUT', '=', $lstrRut)
       ->get();

   if ($lobjContratista){
      $larrResultado = array('status'=>'error',
        'message'=>'RUT ya registrado',
        'code'=> '1'
       );
    }else{
      $larrResultado = array('status'=>'sucess',
        'message'=>'',
        'code'=> '0'
       );
    }

    return response()->json($larrResultado);
  }

  public function getPais(Request $request,$tipo=null,$idContratista=null){
    $codigoPais = $request->CodigoPais;
    $paises = \DB::table('tbl_paises')->where('CodigoPais',$codigoPais)->orderBy('Nombre','asc')->get();
    if(!is_null($tipo)){
      if(!is_null($idContratista)){
        $paises = \DB::table('tbl_paises')
          ->join('tbl_contratistas','tbl_paises.IdPais','=','tbl_contratistas.Pais')
          ->where('tbl_contratistas.IdContratista',$idContratista)
          ->value('tbl_paises.IdPais');
      }
      return $paises;
    }
    return response()->json($paises);
  }

  public function getSubcontratistas(Request $request,$tipo=null){
    $id = $request->IdContratista;
		if (isset($id)){
		    $datos = \DB::table('tbl_contratistas')->select('IdContratista','RUT','RazonSocial')->where('IdContratista', '!=', $id)->where('IdEstatus','=',1)->get();
        if(!is_null($tipo)){
          return $datos;
        }
    }
		else{
		    $datos = \DB::table('tbl_contratistas')->select('IdContratista','RUT','RazonSocial')->where('IdEstatus','=',1)->get();
        if(!is_null($tipo)){
          return $datos;
        }

    }
    return response()->json($datos);
  }

  public function getGiros($tipo=null){
    $giros = DB::table('tbl_giros')->orderBy('Descripcion','asc')->get();
    if(!is_null($tipo)){
      return $giros;
    }
    return response()->json($giros);
  }

  public function getRegion(Request $request,$tipo=null,$idContratista=null){

    if($request->has('idPais')){
      $id=$request->idPais;
    }else{
      $id=42; //chile
    }

    $regiones = \DB::table('dim_region')->where('idPais',$id)->orderBy('nombre','asc')->get();

    if(!is_null($tipo)){
      if(!is_null($idContratista)){
        $regiones = \DB::table('dim_region')
          ->join('tbl_contratistas','tbl_contratistas.IdRegion','=','dim_region.id')
          ->where('tbl_contratistas.IdContratista',$idContratista)
          ->value('dim_region.id');
      }
      return $regiones;
    }

    return response()->json($regiones);

  }

  public function getProvincia(Request $request,$idContratista=null){

    if($request->has('IdRegion')){
      $id=$request->IdRegion;
      $provincia = \DB::table('dim_provincia')->where('idRegion',$id)->orderBy('nombre','asc')->get();
    }else{
      $provincia = \DB::table('dim_provincia')->orderBy('nombre','asc')->get();
    }

    if(!is_null($idContratista)){
      $provincia = \DB::table('dim_provincia')
        ->join('tbl_contratistas','tbl_contratistas.IdProvincia','=','dim_provincia.id')
        ->where('tbl_contratistas.IdContratista',$idContratista)
        ->value('dim_provincia.id');
      return $provincia;
    }

    return response()->json($provincia);

  }

  public function getComuna(Request $request,$idContratista=null){

    if($request->has('IdProvincia')){
      $id=$request->IdProvincia;
      $comuna = \DB::table('dim_comuna')->where('idProvincia',$id)->orderBy('nombre','asc')->get();
    }else{
      $comuna = \DB::table('dim_comuna')->orderBy('nombre','asc')->get();
    }

    if(!is_null($idContratista)){
      $comuna = \DB::table('dim_comuna')
        ->join('tbl_contratistas','tbl_contratistas.IdComuna','=','dim_comuna.id')
        ->where('tbl_contratistas.IdContratista',$idContratista)
        ->value('dim_comuna.id');
      return $comuna;
    }

    return response()->json($comuna);

  }

  public function getActividadContratista($tipo=null,$idContratista=null){
    $actividades = \DB::table('tbl_contratista_categoria')->orderBy('nombre_categoria','asc')->get();

    if(!is_null($tipo)){
      if(!is_null($idContratista)){
        $actividades = \DB::table('tbl_contratista_categoria')
          ->join('tbl_contratistas','tbl_contratista_categoria.id_categoria','=','tbl_contratistas.id_categoria')
          ->where('tbl_contratistas.IdContratista',$idContratista)
          ->value('tbl_contratista_categoria.nombre_categoria');
      }
      return $actividades;
    }

    return response()->json($actividades);
  }

  public function creaContratista(Request $request){
    $idContratista = $request->IdContratista;
    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));

    if ($lintLevelUser == "15") { // Valida si el perfil es precontratista

			$lobjContratistas = \DB::table('tbl_contratistas')
			->where('tbl_contratistas.entry_by_access','=',$lintIdUser)
			->get(); //Consulta si el usuario tiene ya una empresa creada

			if ($lobjContratistas){ // Si tiene una empresa creada no le permitimos crear mÃ¡s
				return response()->json(array(
					'message'	=> 'No puede crear mÃ¡s de un registro contratista',
					'status'	=> 'error'
				));
			}

		}

    $request->CodigoPais = 'CL';
    $data['pais'] = self::getPais($request,1);
    $data['actividad'] = self::getActividadContratista(1);
    $data['giros'] = self::getGiros(1);
    $data['region'] = self::getRegion($request,1);
    $data['subContratistas'] = self::getSubcontratistas($request,1);
    $data['asignadoA'] = \DB::table('tb_users')
		->select('tb_users.id as value', \DB::raw("concat(tb_users.first_name, ' ', tb_users.last_name) as display"))
		->join('tb_groups','tb_groups.group_id','=','tb_users.group_id')
		//->wherein('tb_groups.level',['6','15'])
		->whereNotExists(function ($query) use ($idContratista) {
            $query->select(\DB::raw(1))
                  ->from('tbl_contratistas')
                  ->whereRaw('tbl_contratistas.entry_by_access = tb_users.id');
                  if ($idContratista){
                  	$query->whereRaw('tbl_contratistas.IdContratista != '.$idContratista);
                  }
        })
    ->orderby('display','asc')
		->get();

    return response()->json($data);
  }

  public function getList(){

    $lintIdUser = \Session::get('uid');
    $lintGroupUser = \MySourcing::GroupUser($lintIdUser);
    $listadoContratistas['acceso']=true;
    $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
    $lcontratistas = explode(',',$lobjFiltro['contratistas']);
    $contratistas = Contratistas::whereIn('IdContratista',$lcontratistas)->with('user')->get();
    $cant_id = count($contratistas); // Consigo la cantidad de registro contratista
    $list=[];
    $list2=[];
    $data = [];

    if($contratistas){

      foreach ($contratistas as $key => $contratista) {

        $giro1 = $contratista->giro1 ? ['IdGiro' => $contratista->giro1->IdGiro, 'Descripcion' => $contratista->giro1->Descripcion  ] : [];
        $giro2 = $contratista->giro2 ? ['IdGiro' => $contratista->giro2->IdGiro, 'Descripcion' => $contratista->giro2->Descripcion  ] : [];
        $giro3 = $contratista->giro3 ? ['IdGiro' => $contratista->giro3->IdGiro, 'Descripcion' => $contratista->giro3->Descripcion  ] : [];
        $giro4 = $contratista->giro4 ? ['IdGiro' => $contratista->giro4->IdGiro, 'Descripcion' => $contratista->giro4->Descripcion  ] : [];
        $girosId = [];
        if(!empty($giro1)) array_push($girosId, $giro1['IdGiro'] );
        if(!empty($giro2)) array_push($girosId, $giro2['IdGiro'] );
        if(!empty($giro3)) array_push($girosId, $giro3['IdGiro'] );
        if(!empty($giro4)) array_push($girosId, $giro4['IdGiro'] );
        $firstName = $contratista->user && $contratista->user->first_name ? $contratista->user->first_name : 'Sin Nombre';
        $lastName =  $contratista->user && $contratista->user->last_name ? $contratista->user->last_name : 'Sin Apellido';
        $email = $contratista->user && $contratista->user->email ? $contratista->user->email : 'Sin correo';
        $request = new \Illuminate\Http\Request();
        $list2[$key] = [
          'Actividad_Principal'=> self::getActividadContratista(1,$contratista->IdContratista),
          'Actividad_Principal_id'=>$contratista->id_categoria,
          'Giro_1' =>$giro1,
          'Giro_2' =>$giro2,
          'Giro_3' =>$giro3,
          'Giro_4' =>$giro4,
          'GirosId'=> $girosId,
          'Representante_Legal' =>$contratista->Representante,
          'Representante_Rut' =>$contratista->RepresentanteRut,
          'Representante_Fono' =>$contratista->RepresentanteFono,
          'Representante_Email' =>$contratista->RepresentanteEmail,
          'Direccion' =>$contratista->Direccion,
          'Fono' =>$contratista->Fono,
          'Email' =>$contratista->Email,
          'PaginaWeb' =>$contratista->PaginaWeb,
          'pais'=> self::getPais($request,1,$contratista->IdContratista),
          'region'=> self::getRegion($request,1,$contratista->IdContratista),
          'provincia'=> self::getProvincia($request,$contratista->IdContratista),
          'comuna'=> self::getComuna($request,$contratista->IdContratista),
          'asignadoA'=> $contratista->entry_by_access ? $contratista->entry_by_access : "No esta Asigando",
          'mandante'=>$contratista->is_mandante
        ];
        $list[$key] = [
          'IdContratista' => $contratista->IdContratista,
          'RUT' => $contratista->RUT,
          'RazonSocial' => $contratista->RazonSocial,
          'NombreFantasia' => $contratista->NombreFantasia,
          'Tamano' => self::tamano($contratista->tamano),
          'TamanoId' => $contratista->tamano,
          'Estatus' => $contratista->IdEstatus == 1 ? 'Activo' : 'Inactivo',
          'ContratosActivos' => $contratista->contratos->where('cont_estado', '1')->count('contrato_id'),
          'ContratosInactivos' => $contratista->contratos->where('cont_estado', '2')->count('contrato_id'),
          'UsuarioPrincipal' => $firstName.' '.$lastName.' '.$email,
          'FechaCreacion' => date('d/m/Y', strtotime($contratista->createdOn)),
          'infoExtendida' => $list2[$key]
        ];
      }
    }

    $succes=false;
    $data = '';
    if($this->access['is_view']){
      $succes=true;
      $data = $list;
    }

    return response()->json([
      'success'=> $succes,
      'code'=> 200,
      'data' => $data
      ]);

  }

  public function EliminarContratista(request $request){

    $arr =  json_decode($request->getContent());

    if(count($arr->ids) >=1)
		{
          // Busco el id contratista si tiene contrato
          $lobjContratistaCont = \DB::table('tbl_contrato')
          ->whereIn('tbl_contrato.IdContratista',$arr->ids)
          ->get();

          if ($lobjContratistaCont){ // Si la contratista tiene contratos asociado, no permite eliminar
            return response()->json(array(
              'message'	=> 'No puede eliminar la contratista, verifique que no tenga contrato asociados',
              'status'	=> 'error'
            ));
          }

          // Busco el id contratista si tiene Documentos
          $lobjContratistaDoc = \DB::table('tbl_documentos')
            ->whereIn('tbl_documentos.IdContratista',$arr->ids)
          ->get();

          if ($lobjContratistaDoc){ // Si la contratista tiene documentos asociado, no permite eliminar
            return response()->json(array(
              'message'	=> 'No puede eliminar la contratista, verifique que no tenga documentos asociados',
              'status'	=> 'error'
            ));
          }

            \DB::table('tbl_contratistas')->whereIn('IdContratista',$arr->ids)->delete();
            return response()->json(array(
            'status'=>'success',
            'message'=> \Lang::get('Eliminados exitosamente!!!')
          ));

    } else {

      return response()->json(array(
        'status'=>'error',
        'message'=> \Lang::get('Debe seleccionar algun contratista')
      ));
    }

  }

  public function tamano($tamano){

    $tamaños =[0 => 'PYME', 1 => 'Pequeña', 2 => 'Mediana', 3 => 'Grande'];
    $value = array_get($tamaños, $tamano);
    return $value;

  }

}
