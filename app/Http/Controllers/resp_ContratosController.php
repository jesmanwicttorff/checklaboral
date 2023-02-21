<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Contratos;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;

class ContratosController extends Controller {

    protected $layout = "layouts.main";
    protected $data = array();
    public $module = 'contratos';
    static $per_page    = '10';

    public function __construct()
    {
        parent::__construct();
        $this->model = new Contratos();
        $this->modelview = new  \App\Models\Contratospersonas();
        $this->modelviewtwo = new  \App\Models\Contratoscentros();


        $this->info = $this->model->makeInfo( $this->module);
        $this->access = $this->model->validAccess($this->info['id']);

        $this->data = array(
            'pageTitle'         =>  $this->info['title'],
            'pageNote'          =>  $this->info['note'],
            'pageModule'        => 'contratos',
            'pageUrl'           =>  url('contratos'),
            'return'            =>  self::returnUrl()
        );

    }

    public function getIndex()
    {
        if($this->access['is_view'] ==0)
            return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

        $this->data['access']       = $this->access;
        return view('contratos.index',$this->data);
    }

    public function postData( Request $request)
    {

        $sort = (!is_null($request->input('sort')) ? $request->input('sort') : $this->info['setting']['orderby']);
        $order = (!is_null($request->input('order')) ? $request->input('order') : $this->info['setting']['ordertype']);
        // End Filter sort and order for query
        // Filter Search for query
        $filter = '';
        if(!is_null($request->input('search')))
        {
            $search =   $this->buildSearch('maps');
            $filter = $search['param'];
            $this->data['search_map'] = $search['maps'];
        }

        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');
        if ($lintLevelUser==4){
          //Aplicamos un filtro especial para solo los contratos relaciados a ese administrador
          $filter .= " AND tbl_contrato.contrato_id IN (select contrato_id from tbl_contrato where tbl_contrato.admin_id = ".$lintIdUser.") ";
        }
        if ($lintLevelUser==6){
          //Aplicamos un filtro especial para solo los contratos relaciados a ese administrador
          $filter .= " AND (tbl_contrato.entry_by_access = ".$lintIdUser." OR tbl_contrato.contrato_id IN (select tbl_contratos_subcontratistas.contrato_id from tbl_contratistas inner join tbl_contratos_subcontratistas on tbl_contratos_subcontratistas.IdSubContratista = tbl_contratistas.IdContratista where entry_by_access = ".$lintIdUser.") )  ";
        }

        $page = $request->input('page', 1);
        $params = array(
            'page'      => $page ,
            'limit'     => (!is_null($request->input('rows')) ? filter_var($request->input('rows'),FILTER_VALIDATE_INT) : $this->info['setting']['perpage'] ) ,
            'sort'      => $sort ,
            'order'     => $order,
            'params'    => $filter,
            'global'    => (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
        );
        $paramstwo = array(
            'page'      => $page ,
            'limit'     => "",
            'sort'      => "",
            'order'     => "",
            'params'    => "",
            'global'    => (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
        );
        // Get Query
        $results = $this->model->getRows( $params );



        // Build pagination setting
        $page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
        $pagination = new Paginator($results['rows'], $results['total'], $params['limit']);
        $pagination->setPath('contratos/data');

        $this->data['param']        = $params;
        $this->data['rowData']      = $results['rows'];

        // Build Pagination
        $this->data['pagination']   = $pagination;
        // Build pager number and append current param GET
        $this->data['pager']        = $this->injectPaginate();
        // Row grid Number
        $this->data['i']            = ($page * $params['limit'])- $params['limit'];
        // Grid Configuration

        $this->data['tableGrid']    = $this->info['config']['grid'];

        $this->data['tableForm']    = $this->info['config']['forms'];
        $this->data['colspan']      = \SiteHelpers::viewColSpan($this->info['config']['grid']);
        // Group users permission
        $this->data['access']       = $this->access;
        // Detail from master if any
        $this->data['setting']      = $this->info['setting'];

        // Master detail link if any
        $this->data['subgrid']  = (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
        // Render into template
        return view('contratos.table',$this->data);

    }


    function getUpdate(Request $request, $id = null)
 {

    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');
    $lintIdUserContract = 0;

  if($id =='')
  {
   if($this->access['is_add'] ==0 )
   return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
  }

  if($id !='')
  {
   if($this->access['is_edit'] ==0 )
   return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
  }

  $row = $this->model->find($id);
  if($row)
  {
   $this->data['row']   =  $row;

   $this->data['subContratista'] = \DB::table('tbl_contratos_subcontratistas')->Select('IdSubContratista')->where('contrato_id',$row['contrato_id'])->get();

   $this->data['rowRContratistas'] = \DB::table('tbl_documentos')
    ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
    ->join('tbl_contratistas', 'tbl_contratistas.IdContratista', '=', 'tbl_documentos.IdEntidad')
    ->select('tbl_documentos.IdDocumento','tbl_documentos.IdRequisito','tbl_documentos.Entidad','tbl_documentos.IdTipoDocumento',\DB::raw("case when tbl_documentos.IdTipoDocumento=1 then concat(tbl_contratistas.RUT,' ',tbl_contratistas.RazonSocial,' ',tbl_tipos_documentos.Descripcion) else tbl_tipos_documentos.Descripcion end as Descripcion"))
    ->where('tbl_documentos.Entidad',1)
    ->where('tbl_documentos.contrato_id', '=', $row['contrato_id'])
    ->get();

   $this->data['rowRContratos'] = \DB::table('tbl_documentos')
    ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
               ->select('tbl_documentos.IdDocumento','tbl_documentos.IdRequisito','tbl_documentos.Entidad','tbl_documentos.IdTipoDocumento','tbl_tipos_documentos.Descripcion')
   ->where('tbl_documentos.Entidad',2)
   ->where('tbl_documentos.contrato_id', '=', $row['contrato_id'])
   ->get();

   $this->data['rowRCentros'] = \DB::table('tbl_documentos')
    ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
    ->join('tbl_centro', 'tbl_centro.IdCentro', '=', 'tbl_documentos.IdEntidad')
               ->select('tbl_documentos.IdDocumento','tbl_documentos.IdRequisito','tbl_documentos.Entidad','tbl_documentos.IdTipoDocumento', \DB::raw('CONCAT(tbl_centro.Descripcion," ", tbl_tipos_documentos.Descripcion) as Descripcion'))
   ->where('tbl_documentos.Entidad',6)
   ->where('tbl_documentos.contrato_id', '=', $row['contrato_id'])
   ->get();

   $this->data['rowContrCentros'] =  \DB::table('tbl_contratos_centros')
     ->join('tbl_centro', 'tbl_contratos_centros.IdCentro', '=', 'tbl_centro.IdCentro')
      ->select('tbl_contratos_centros.contrato_id','tbl_contratos_centros.IdCentro','tbl_centro.Descripcion')
    ->where('contrato_id', '=', $row['contrato_id'])
   ->get();

     $lintIdUserContract = $row->{'entry_by_access'};
  
  } else {
   $this->data['row']   = $this->model->getColumnTable('tbl_contrato');

   $this->data['rowRContratistas'] = \DB::table('tbl_requisitos')
    ->join('tbl_tipos_documentos', 'tbl_requisitos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
               ->join('tbl_entidades', 'tbl_requisitos.Entidad', '=', 'tbl_entidades.IdEntidad')
               ->select('tbl_requisitos.IdRequisito','tbl_requisitos.Entidad','tbl_requisitos.IdTipoDocumento','tbl_entidades.Entidad AS EntidadV','tbl_tipos_documentos.Descripcion')
   ->where('tbl_requisitos.Entidad',1)->get();

   $this->data['rowRContratos'] = \DB::table('tbl_requisitos')
    ->join('tbl_tipos_documentos', 'tbl_requisitos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
               ->join('tbl_entidades', 'tbl_requisitos.Entidad', '=', 'tbl_entidades.IdEntidad')
                   ->select('tbl_requisitos.IdRequisito','tbl_requisitos.Entidad','tbl_requisitos.IdTipoDocumento','tbl_entidades.Entidad AS EntidadV','tbl_tipos_documentos.Descripcion')
   ->where('tbl_requisitos.Entidad',2)->get();

   $this->data['rowRCentros'] = \DB::table('tbl_requisitos')
    ->join('tbl_tipos_documentos', 'tbl_requisitos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
               ->join('tbl_entidades', 'tbl_requisitos.Entidad', '=', 'tbl_entidades.IdEntidad')
               ->select('tbl_requisitos.IdRequisito','tbl_requisitos.Entidad','tbl_requisitos.IdTipoDocumento','tbl_entidades.Entidad AS EntidadV','tbl_tipos_documentos.Descripcion')
   ->where('tbl_requisitos.Entidad',6)->get();
  }

  
   $this->data['anotaciones']=  \DB::table('tbl_concepto_anotacion')->get();
   $this->data['rowPersonasAreas'] = \DB::table('tbl_accesos')
   ->join('tbl_acceso_areas', 'tbl_accesos.IdAcceso', '=', 'tbl_acceso_areas.IdAcceso')
   ->select('tbl_accesos.IdPersona','tbl_acceso_areas.IdAreaTrabajo')
   ->where('tbl_accesos.contrato_id',$id)->get();

   $this->data['areasT'] = \DB::table('tbl_area_de_trabajo')->get();

   //Verificamos el perfil, si es contratista o si es subcontratista

   if ($lintLevelUser==6){
     if ($lintIdUser!=$lintIdUserContract && $lintIdUserContract > 0 ){ //subcontratista
      $this->data['PersonasCont'] = \DB::table('tbl_contratos_personas')
               ->join('tbl_personas', 'tbl_contratos_personas.IdPersona', '=', 'tbl_personas.IdPersona')
               ->join('tbl_roles', 'tbl_contratos_personas.IdRol', '=', 'tbl_roles.IdRol')
               ->select('tbl_contratos_personas.IdPersona','tbl_contratos_personas.contrato_id', 'tbl_personas.Rut', 'tbl_personas.Nombres', 'tbl_personas.Apellidos', 'tbl_roles.IdRol', 'tbl_roles.descripción AS Roles')
               ->where("tbl_contratos_personas.contrato_id",$id)
               ->where("tbl_personas.entry_by_access",$lintIdUser)
               ->get();
     }else{ //contratista
        $this->data['PersonasCont'] = \DB::table('tbl_contratos_personas')
               ->join('tbl_personas', 'tbl_contratos_personas.IdPersona', '=', 'tbl_personas.IdPersona')
               ->join('tbl_roles', 'tbl_contratos_personas.IdRol', '=', 'tbl_roles.IdRol')
               ->select('tbl_contratos_personas.IdPersona','tbl_contratos_personas.contrato_id', 'tbl_personas.Rut', 'tbl_personas.Nombres', 'tbl_personas.Apellidos', 'tbl_roles.IdRol', 'tbl_roles.descripción AS Roles')
               ->where("tbl_contratos_personas.contrato_id",$id)
               ->get();
     }

   } else {
      $this->data['PersonasCont'] = \DB::table('tbl_contratos_personas')
               ->join('tbl_personas', 'tbl_contratos_personas.IdPersona', '=', 'tbl_personas.IdPersona')
               ->join('tbl_roles', 'tbl_contratos_personas.IdRol', '=', 'tbl_roles.IdRol')
               ->select('tbl_contratos_personas.IdPersona','tbl_contratos_personas.contrato_id', 'tbl_personas.Rut', 'tbl_personas.Nombres', 'tbl_personas.Apellidos', 'tbl_roles.IdRol', 'tbl_roles.descripción AS Roles')
               ->where("tbl_contratos_personas.contrato_id",$id)
               ->get();
    }

  $this->data['setting']   = $this->info['setting'];
  $this->data['fields']   =  \AjaxHelpers::fieldLang($this->info['config']['forms']);

  $this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );


  //Construimos basados en sximo un nuevo subformulario
  $larrContratosCentros = array( "title" => "Centros", "master" => "contratos", "master_key" => "contrato_id", "module" => "contratoscentros", "table" => "tbl_contratos_centros", "key" => "contrato_id" );
  $this->data['subformtwo'] = $this->detailview($this->modelviewtwo ,  $larrContratosCentros ,$id );

  $lobjContratoPersonas =  \DB::table('tbl_contratos_personas')
                                        ->where('contrato_id', '=', $id)
                                        ->where('entry_by_access', '!=', $lintIdUser)
                                        ->groupBy('entry_by_access')
                                        ->select('entry_by_access')
                                        ->get();
  if ($lobjContratoPersonas){                                        
  $this->data['countcountratistas'] =count($lobjContratoPersonas);
}else{
  $this->data['countcountratistas'] =0;
}


  $this->data['id'] = $id;

  return view('contratos.form',$this->data);
 }

    public function getShow( $id = null)
    {

        if($this->access['is_detail'] ==0)
            return Redirect::to('dashboard')
                ->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

        $row = $this->model->getRow($id);
        if($row)
        {
            $this->data['row'] =  $row;

            $this->data['id'] = $id;
            $this->data['access']       = $this->access;
            $this->data['setting']      = $this->info['setting'];
            $this->data['fields']       = \AjaxHelpers::fieldLang($this->info['config']['grid']);
            $this->data['subgrid']      = (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
            return view('contratos.view',$this->data);

        } else {

            return response()->json(array(
                'status'=>'error',
                'message'=> \Lang::get('core.note_error')
            ));
        }
    }


    function postCopy( Request $request)
    {

        foreach(\DB::select("SHOW COLUMNS FROM tbl_contrato ") as $column)
        {
            if( $column->Field != 'contrato_id')
                $columns[] = $column->Field;
        }
        if(count($request->input('ids')) >=1)
        {

            $toCopy = implode(",",$request->input('ids'));


            $sql = "INSERT INTO tbl_contrato (".implode(",", $columns).") ";
            $sql .= " SELECT ".implode(",", $columns)." FROM tbl_contrato WHERE contrato_id IN (".$toCopy.")";
            \DB::select($sql);
            return response()->json(array(
                'status'=>'success',
                'message'=> \Lang::get('core.note_success')
            ));

        } else {
            return response()->json(array(
                'status'=>'success',
                'message'=> 'Please select row to copy'
            ));
        }


    }

    function postSave( Request $request, $id =0)
    {


        $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
        $lintIdUser = \Session::get('uid');
        $personas = $request->IdPersona;
        $IdAreaTrabajoP = $request->IdAreaTrabajoP;
        $LisTipoDocumento = $request->bulk_IdTipoDocumento;
        $createdOn = date('Y-m-d H:i:s');
        $FechaInicio = $request->cont_fechaInicio;
        $FechaFinal = $request->cont_fechaFin;
        $IdCentroT = $request->IdCentroT;
        $subcontrarista = $request->IdSubContratista;
        $documentos = $request->bulk_Documento;
        $EntryBy = $request->entry_by;
        $EntryByAccess = $request->entry_by_access;
        $access = $request->access;
        $Entidad=$request->bulk_Entidad;
        $IdRequisito=$request->bulk_IdRequisito;
        $IdTipoDocumento=$request->bulk_IdTipoDocumento;
        $rules = $this->validateForm();
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {

            $data = $this->validatePost('tbl_contrato');
            
            //**Guardamos los datos del contrato
            $id = $this->model->insertRow($data , $request->input('contrato_id'));
            //**Guardamos los datos del contrato

            //si el usuario no es contratista
            if ($lintLevelUser!=6){

              //**Guardamos los subcontratistas
              if (!(empty($subcontrarista))) {
                \DB::table('tbl_contratos_subcontratistas')->where('contrato_id', '=', $id)->delete();
                foreach ($subcontrarista as $sub) {
                $Id = \DB::table('tbl_contratos_subcontratistas')->insertGetId(
                    ['contrato_id' => $id, 'IdSubContratista' => $sub]);
                }
              }else{
                \DB::table('tbl_contratos_subcontratistas')->where('contrato_id', '=', $id)->delete();
              }

              //**Guardamos los centros operaciones
              $larrContratosCentros = array( "title" => "Centros", "master" => "contratos", "master_key" => "contrato_id", "module" => "contratoscentros", "table" => "tbl_contratos_centros", "key" => "contrato_id" );
              $this->detailviewsave( $this->modelviewtwo , $request->all() , $larrContratosCentros , $id,'two') ;

              //**Guardamos los requisitos
              $docs =  \DB::table('tbl_documentos')
                        ->select('IdDocumento')
                        ->where('contrato_id', '=', $request->contrato_id)
                        ->get();
              if (count($docs)>0 ) {
                foreach ($docs as $value){
                  $array[] = $value->IdDocumento;
                }
                if (!(empty($documentos))){
                  foreach ($array as $valor) {
                    if (!(in_array($valor, $documentos))){
                      \DB::table('tbl_documentos')->where('IdDocumento', '=', $valor)->whereIn('Entidad',[1,2,6])->delete();
                    }
                  }
                }
                for($i = 0; $i<count($IdTipoDocumento); $i++){
                  $lintIdTipoDocumento=$IdTipoDocumento[$i];
                  if ($lintIdTipoDocumento){
                    $lstrEntidad = $Entidad[$i];
                    $lintIdRequisito=$IdRequisito[$i];
                    $lintIdTipoDocumento=$IdTipoDocumento[$i];
                    $lintIdRequisito = $lintIdRequisito?$lintIdRequisito:'NULL';
                    if ($lstrEntidad==1){
                      $lintIdEntidad=$request->IdContratista;
                    }else{
                     $lintIdEntidad=$id;
                    }
                    if ($lstrEntidad==6){
                      $consulta = "insert into tbl_documentos
                             select NULL as IdDocumento,
                                $lintIdRequisito as IdRequisito,
                                '$lintIdTipoDocumento' as IdTipoDocumento,
                                '$lstrEntidad' as Entidad,
                                tbl_contratos_centros.IdCentro as IdEntidad,
                                NULL as Documento,
                                NULL as DocumentoURL,
                                NULL as DocumentoTexto,
                                NULL as FechaVencimiento,
                                1 as IdEstatus,
                                '$createdOn' as createdOn,
                                '$EntryBy' as entry_by,
                                NULL as entry_by_access,
                                NULL as updatedOn,
                                NULL as FechaEmision,
                                NULL as Resultado,
                                '$id' as contrato_id
                              from tbl_contratos_centros
                              where tbl_contratos_centros.contrato_id = '$id'
                              and tbl_contratos_centros.IdTipoCentro = 1
                              and not exists ( SELECT *
                                      FROM tbl_documentos
                                      WHERE tbl_documentos.IdTipoDocumento = '$lintIdTipoDocumento'
                                      AND tbl_documentos.Entidad = '$lstrEntidad'
                                      AND tbl_documentos.IdEntidad = tbl_contratos_centros.IdCentro
                                      AND tbl_documentos.contrato_id = '$id')";
                    }else{
                        $consulta = "insert into tbl_documentos
                             select NULL as IdDocumento,
                                    $lintIdRequisito as IdRequisito,
                                    '$lintIdTipoDocumento' as IdTipoDocumento,
                                    '$lstrEntidad' as Entidad,
                                    '$lintIdEntidad' as IdEntidad,
                                    NULL as Documento,
                                    NULL as DocumentoURL,
                                    NULL as DocumentoTexto,
                                    NULL as FechaVencimiento,
                                    1 as IdEstatus,
                                    '$createdOn' as createdOn,
                                    '$EntryBy' as entry_by,
                                    NULL as entry_by_access,
                                    NULL as updatedOn,
                                    NULL as FechaEmision,
                                    NULL as Resultado,
                                    '$id' as contrato_id
                             from dual
                             where not exists ( SELECT *
                                                FROM tbl_documentos
                                                WHERE tbl_documentos.IdTipoDocumento = '$lintIdTipoDocumento'
                                                AND tbl_documentos.Entidad = '$lstrEntidad'
                                                AND tbl_documentos.IdEntidad = '$lintIdEntidad')";

                    }
                    \DB::insert($consulta);
                  }
                }
              }

              //*Guardamos un documento F30-1 por cada subcontratista
              $lstrQuery = "INSERT INTO tbl_documentos
                             SELECT DISTINCT NULL as IdDocumento,
                                    NULL as IdRequisito,
                                    '1' as IdTipoDocumento,
                                    '1' as Entidad,
                                    tbl_contratos_subcontratistas.IdSubContratista as IdEntidad,
                                    NULL as Documento,
                                    NULL as DocumentoURL,
                                    NULL as DocumentoTexto,
                                    NULL as FechaVencimiento,
                                    1 as IdEstatus,
                                    now() as createdOn,
                                    $lintIdUser as entry_by,
                                    tbl_contratistas.entry_by as entry_by_access,
                                    NULL as updatedOn,
                                    NULL as FechaEmision,
                                    NULL as Resultado,
                                    tbl_contratos_subcontratistas.contrato_id as contrato_id
                            FROM tbl_contratos_subcontratistas
                            INNER JOIN tbl_contratistas ON tbl_contratos_subcontratistas.IdSubContratista = tbl_contratistas.IdContratista
                            INNER JOIN tbl_documentos on tbl_contratos_subcontratistas.contrato_id = tbl_documentos.contrato_id and tbl_documentos.IdTipoDocumento = 1
                            WHERE tbl_contratos_subcontratistas.contrato_id = '$id' 
                            AND NOT EXISTS (SELECT b.IdTipoDocumento 
                                              FROM   tbl_documentos b
                                              WHERE  b.IdEntidad = tbl_contratos_subcontratistas.IdSubContratista
                                              AND    b.Entidad = 1
                                              AND    b.IdTipoDocumento = 1
                                              AND    b.contrato_id = tbl_contratos_subcontratistas.contrato_id)
                            AND   EXISTS  ( SELECT  c.*
                                    FROM tbl_documentos c
                                    WHERE c.contrato_id = tbl_documentos.contrato_id
                                    AND c.Entidad = 6
                                    AND c.IdTipoDocumento = 1);";
              \DB::insert($lstrQuery);

            }

            //**Guardamos los datos de la persona
            $modelpeople = $this->modelview;
            $requestpeople = $request->all(); 
            $detailpeople = $this->info['config']['subform'];
            $bulkLabel = "";
            $lbolValidate = false;
            if ($lintLevelUser==6){
              if ($lintIdUser!=$EntryByAccess){
                \DB::table($detailpeople['table'])
                     ->where($detailpeople['key'],$requestpeople[$detailpeople['key']])
                     ->where("entry_by_access",$lintIdUser)
                     ->delete();
              }else{
                \DB::table($detailpeople['table'])
                     ->where($detailpeople['key'],$requestpeople[$detailpeople['key']])
                     ->delete();
              }
            }else{
              \DB::table($detailpeople['table'])
                     ->where($detailpeople['key'],$requestpeople[$detailpeople['key']])
                     ->delete();
            }
            $infopeople = $modelpeople->makeInfo( $detailpeople['module'] );
            $strpeople = $infopeople['config']['forms'];
            $datapeople = array($detailpeople['master_key'] => $id );
            $total = isset($requestpeople['counter'.$bulkLabel])?count($requestpeople['counter'.$bulkLabel]):0;
            for($i=0; $i<$total;$i++){
              $lbolValidate = false;
              foreach($strpeople as $f){
                $field = $f['field'];
                if($f['view'] ==1){
                  if(isset($requestpeople['bulk'.$bulkLabel.'_'.$field][$i])) {
                    if ($requestpeople['bulk'.$bulkLabel.'_'.$field][$i]!=""){
                      if ( $f['field']!='entry_by' && $f['field']!='entry_by_access' ){
                        $lbolValidate = true;
                      }
                      $datapeople[$f['field']] = $requestpeople['bulk'.$bulkLabel.'_'.$field][$i];
                    }
                  }
                }
              } 
              if ($lbolValidate){
                $datapeople['entry_by'] = \Session::get('uid');
                \DB::table($detailpeople['table'])->insert($datapeople);
              }
            } 
            //**Guardamos los datos de la persona

            //**Guardamos los datos de los accesos
            if ($access){
              //var_dump($access);
              foreach ($access as $lidpersona => $laccess) {
                $data['IdPersona'] = $lidpersona;
                //echo " idpersona: ".$data['IdPersona'];
                $lobjPersona = \DB::table('tbl_accesos')
                                    ->where('IdPersona', '=', $lidpersona)
                                    ->where('IdTipoAcceso', '=', 1)
                                    ->get();

                if (!$lobjPersona){
                  $IdAcceso = \DB::table('tbl_accesos')->insertGetId(
                     ['IdTipoAcceso' => 1, 'IdPersona' => $lidpersona, 'contrato_id' => $id, 'FechaInicio' => $data['cont_fechaInicio'], 'FechaFinal' => $data['cont_fechaFin'], 'IdEstatus' => 2, 'createdOn' => date("Y-m-d H:i:s"), 'entry_by' => '', 'updatedOn' => 'NULL', 'IdSolicitudAcceso' => 'NULL' ]
                  );
                }else{
                  $IdAcceso = $lobjPersona[0]->{'IdAcceso'};
                  //Actualizamos la fecha del acceso:
                  $larrDataAccesos = array("Fechainicio" => $FechaInicio,
                                           "FechaFinal" => $FechaFinal);
                  \DB::table('tbl_accesos')
                       ->where('IdAcceso',$IdAcceso)
                       ->update($larrDataAccesos);
                  //Eliminamos los accesos 
                  $lobjAreasDeTrabajo =  \DB::table('tbl_acceso_areas')
                                              ->select('IdAreaTrabajo')
                                              ->where('IdAcceso', '=', $IdAcceso)
                                              ->get();
                  if ($lobjAreasDeTrabajo){
                    foreach ($lobjAreasDeTrabajo as $larrRow) {
                      //echo in_array($larrRow->{'IdAreaTrabajo'}, $laccess)." area: ".$larrRow->{'IdAreaTrabajo'}." valor:".var_dump($laccess);
                      if ( !(in_array($larrRow->{'IdAreaTrabajo'}, $laccess)) ){
                        \DB::table('tbl_acceso_areas')
                             ->where('IdAreaTrabajo', '=', $larrRow->{'IdAreaTrabajo'})
                             ->where('IdAcceso','=',$IdAcceso)
                             ->delete();
                        //echo "delete from tbl_acceso_areas where IdAreaTrabajo = ".$larrRow->{'IdAreaTrabajo'}." AND IdAcceso = ".$IdAcceso."; </br>";
                      }
                    }
                  }
                  //Eliminamos los accesos 
                }
                //
                foreach ($laccess as $areap ) {
                    if ($areap){
                      $consulta="INSERT INTO tbl_acceso_areas
                                           SELECT NULL as IdAccesoArea,
                                                  '$areap' as IdAreaTrabajo,
                                                  '$IdAcceso' as IdAcceso,
                                                  NULL as IdCentro,
                                                  '1' as IdEstatus
                                           FROM dual
                                           WHERE NOT EXISTS ( SELECT IdAcceso
                                                              FROM tbl_acceso_areas
                                                              WHERE IdAreaTrabajo = '$areap'
                                                              AND   IdAcceso = '$IdAcceso')";
                      \DB::insert($consulta);
                    }
                }
              }
            }

            return response()->json(array(
                'status'=>'success',
                'message'=> \Lang::get('core.note_success')
                ));

        } else {

            $message = $this->validateListError(  $validator->getMessageBag()->toArray() );
            return response()->json(array(
                'message'   => $message,
                'status'    => 'error'
            ));
        }

    }

    public function postBorrar( Request $request)
    {

        if($this->access['is_edit'] ==0) {
            return response()->json(array(
                'status'=>'error',
                'message'=> \Lang::get('core.note_restric')
            ));
            die;

        }

        if(count($request->input('IdPersona')) >=1)
        {

            $Id = $request->input('IdPersona');
            $IdCont = $request->input('contrato');
            $IdUser = \Session::get('uid');;
            $anot = $request->input('anotacion');
            $razon = $request->input('razon');
            $fecha = date('Y/m/d');

            if ($razon==2){

                  $IdDoc = \DB::table('tbl_documentos')->insertGetId(
            ['IdTipoDocumento' => 4, 'Entidad' => 3, 'IdEntidad'=> $Id, 'Documento' => NULL, 'DocumentoURL' => NULL, 'FechaVencimiento' => NULL, 'IdEstatus' => 1, 'createdOn' => $fecha, 'entry_by'=> $IdUser, 'entry_by_access' => 0, 'updatedOn'=> NULL, 'FechaEmision'=> NULL, 'Resultado'=> NULL ]);


                  $IdAnotac = \DB::table('tbl_anotaciones')->insertGetId(
            ['IdConceptoAnotacion' => $anot, 'IdPersona' => $Id, 'createdOn' => $fecha, 'entry_by'=> $IdUser, 'entry_by_access' => $IdUser, 'updatedOn'=> NULL ]);

                \DB::table('tbl_personas')->where('IdPersona', $Id)->update(['entry_by_access' => 0]);
            }
            else{
              $consulta = \DB::table('tbl_documentos')
                  ->where('IdTipoDocumento', '=', 3)
                  ->where('IdEstatus', '=', 1)
                  ->where('IdEntidad', '=', $IdCont)
                  ->get();

                      if (sizeof($consulta)==0) {
                                      $IdDoc = \DB::table('tbl_documentos')->insertGetId(
            ['IdTipoDocumento' => 3, 'Entidad' => 2, 'IdEntidad'=> $IdCont, 'Documento' => NULL, 'DocumentoURL' => NULL, 'FechaVencimiento' => NULL, 'IdEstatus' => 1, 'createdOn' => $fecha, 'entry_by'=> $IdUser, 'entry_by_access' => 0, 'updatedOn'=> NULL, 'FechaEmision'=> NULL, 'Resultado'=> NULL ]);
                      }

              $IdAnotac = \DB::table('tbl_anotaciones')->insertGetId(
            ['IdConceptoAnotacion' => $anot, 'IdPersona' => $Id, 'createdOn' => $fecha, 'entry_by'=> $IdUser, 'entry_by_access' => $IdUser, 'updatedOn'=> NULL ]);
            }

            \DB::table('tbl_contratos_personas')->where('IdPersona', '=', $Id)->where('contrato_id', '=', $IdCont)->delete();

            $acce = \DB::table('tbl_accesos')
                  ->select('IdAcceso')
                  ->where('IdPersona', '=', $Id)
                  ->where('contrato_id', '=', $IdCont)
                  ->get();

              if (sizeof($acce)>0) {
                   $valor = $acce[0]->IdAcceso;
                             \DB::table('tbl_acceso_areas')->where('IdAcceso', '=', $valor)->delete();
                              \DB::table('tbl_accesos')->where('IdAcceso', '=', $valor)->delete();
              }

            return response()->json(array(
                'status'=>'success',
                'message'=> \Lang::get('core.note_success')
            ));

        } else {
            return response()->json(array(
                'status'=>'error',
                'message'=> \Lang::get('core.note_error')
            ));

        }

    }

    public function postDelete( Request $request)
    {

        if($this->access['is_remove'] ==0) {
            return response()->json(array(
                'status'=>'error',
                'message'=> \Lang::get('core.note_restric')
            ));
            die;

        }
        // delete multipe rows
        if(count($request->input('ids')) >=1)
        {
            $this->model->destroy($request->input('ids'));
            \DB::table('tbl_contratos_personas')->whereIn('contrato_id',$request->input('ids'))->delete();
            return response()->json(array(
                'status'=>'success',
                'message'=> \Lang::get('core.note_success_delete')
            ));
        } else {
            return response()->json(array(
                'status'=>'error',
                'message'=> \Lang::get('core.note_error')
            ));

        }

    }

    public static function display( )
    {
        $mode  = isset($_GET['view']) ? 'view' : 'default' ;
        $model  = new Contratos();
        $info = $model::makeInfo('contratos');

        $data = array(
            'pageTitle' =>  $info['title'],
            'pageNote'  =>  $info['note']

        );

        if($mode == 'view')
        {
            $id = $_GET['view'];
            $row = $model::getRow($id);
            if($row)
            {
                $data['row'] =  $row;
                $data['fields']         =  \SiteHelpers::fieldLang($info['config']['grid']);
                $data['id'] = $id;
                return view('contratos.public.view',$data);
            }

        } else {

            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $params = array(
                'page'      => $page ,
                'limit'     =>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
                'sort'      => 'contrato_id' ,
                'order'     => 'asc',
                'params'    => '',
                'global'    => 1
            );

            $result = $model::getRows( $params );
            $data['tableGrid']  = $info['config']['grid'];
            $data['rowData']    = $result['rows'];

            $page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
            $pagination = new Paginator($result['rows'], $result['total'], $params['limit']);
            $pagination->setPath('');
            $data['i']          = ($page * $params['limit'])- $params['limit'];
            $data['pagination'] = $pagination;
            return view('contratos.public.index',$data);
        }


    }

    function postSavepublic( Request $request)
    {

        $rules = $this->validateForm();
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $data = $this->validatePost('tbl_contrato');
             $this->model->insertRow($data , $request->input('contrato_id'));
            return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
        } else {

            return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
            ->withErrors($validator)->withInput();

        }

    }

    public function postDatos( Request $request)
    {
        $idCentro = $request->IdCentro;
        $this->data['areasT'] = \DB::table('tbl_area_de_trabajo')->where('IdCentro',$idCentro)->get();
        return $this->data;

    }

    public function postDatacontratista(Request $request){
        $id = $request->id;
        if (strlen($id)>0){
            $sub = \DB::table('tbl_subcontratistas')->select('SubContratista')->where('IdContratista', '=', $id)->get();
            if (count($sub)>0){
                foreach ($sub as $value){
                 $array[] = $value->SubContratista;
            }

             $datos = \DB::table('tbl_contratistas')->select('IdContratista','RUT','RazonSocial')->whereIn('IdContratista',$array)->get();
            }
            else{
                $datos = "";
            }


        }
        else{
         $datos = \DB::table('tbl_contratistas')->select('IdContratista','RUT','RazonSocial')->where('IdContratista','=',$id)->get();
        }

        return response()->json(array(
            'status'=>'sucess',
            'valores'=>$datos,
            'message'=>\Lang::get('core.note_sucess')
            ));
    }


    public function postDatainformacion(Request $request){
        $id = $request->id;

         $datos = \DB::table('tbl_contrato')
            ->join('tbl_contsegmento', 'tbl_contrato.segmento_id', '=', 'tbl_contsegmento.segmento_id')
            ->join('tb_users', 'tbl_contrato.admin_id', '=', 'tb_users.id')
            ->join('tbl_contgeografico', 'tbl_contrato.geo_id', '=', 'tbl_contgeografico.geo_id')
            ->join('tbl_contareafuncional', 'tbl_contrato.afuncional_id', '=', 'tbl_contareafuncional.afuncional_id')
            ->join('tbl_contclasecosto', 'tbl_contrato.claseCosto_id', '=', 'tbl_contclasecosto.claseCosto_id')
         ->select('tbl_contrato.contrato_id','tbl_contsegmento.seg_nombre','tb_users.first_name','tb_users.last_name','tbl_contgeografico.geo_nombre','tbl_contareafuncional.afuncional_nombre','tbl_contclasecosto.ccost_nombre')
         ->where('contrato_id','=',$id)->get();

        return response()->json(array(
            'status'=>'sucess',
            'valores'=>$datos,
            'message'=>\Lang::get('core.note_sucess')
            ));
    }

}
