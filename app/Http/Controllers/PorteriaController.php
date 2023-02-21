<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Porteria;
use App\Models\Tipoactivos;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use App\Library\MyAccess;
use Validator, Input, Redirect ;
use App\Models\Contratospersonas;
use App\Models\Contratos;

class PorteriaController extends Controller {

    protected $layout = "layouts.main";
    protected $data = array();
    public $module = 'porteria';
    static $per_page    = '10';

    public function __construct()
    {

        parent::__construct();

        $this->model = new Porteria();
        $this->mdltipoactivos = new Tipoactivos();

        $this->info = $this->model->makeInfo( $this->module);
        $this->access = $this->model->validAccess($this->info['id']);

        $this->data = array(
            'pageTitle' =>  $this->info['title'],
            'pageNote'  =>  $this->info['note'],
            'pageModule'=> 'porteria',
            'return'    => self::returnUrl()

        );

        \App::setLocale(CNF_LANG);
        if (defined('CNF_MULTILANG') && CNF_MULTILANG == '1') {

        $lang = (\Session::get('lang') != "" ? \Session::get('lang') : CNF_LANG);
        \App::setLocale($lang);
        }



    }

    public function getIndex( Request $request )
    {

        if($this->access['is_view'] ==0)
            return Redirect::to('dashboard')
                ->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

        $sort = (!is_null($request->input('sort')) ? $request->input('sort') : 'IdAcceso');
        $order = (!is_null($request->input('order')) ? $request->input('order') : 'asc');
        // End Filter sort and order for query
        // Filter Search for query
        $filter = '';
        if(!is_null($request->input('search')))
        {
            $search =   $this->buildSearch('maps');
            $filter = $search['param'];
            $this->data['search_map'] = $search['maps'];
        }

        //Recuperamos los datos generales de porterÃ­a
        $page = $request->input('page', 1);
        $params = array(
            'page'      => $page ,
            'limit'     => (!is_null($request->input('rows')) ? filter_var($request->input('rows'),FILTER_VALIDATE_INT) : static::$per_page ) ,
            'sort'      => $sort ,
            'order'     => $order,
            'params'    => $filter,
            'global'    => (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
        );
        // Get Query
        $results = $this->model->getRows( $params );

        // Build pagination setting
        $page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
        $pagination = new Paginator($results['rows'], $results['total'], $params['limit']);
        $pagination->setPath('porteria');

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
        $this->data['fields'] =  \AjaxHelpers::fieldLang($this->info['config']['grid']);
        // Master detail link if any
        $this->data['subgrid']  = (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
        // Render into template
        //return view('porteria.index',$this->data);
        $this->data['row'] = $this->model->getColumnTable('tbl_accesos');

        $this->data['fields'] =  \AjaxHelpers::fieldLang($this->info['config']['forms']);

        //Recuperamos los datos generales de los tipos de activos
        $filter = " AND tbl_activos.IdEstatus = 1 AND tbl_activos.ControlaAcceso = 1 ";
        $params = array(
            'params'    => $filter,
            'global'    => (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
        );
        // Get Query
        $results = $this->mdltipoactivos->getRows( $params );
        $this->data['rowDataActivos'] = $results['rows'];

        $this->data['rowActUni']   =  \DB::table('tbl_activos')
            ->join('tbl_activos_detalle', 'tbl_activos.IdActivo', '=', 'tbl_activos_detalle.IdActivo')
            ->where('tbl_activos.IdEstatus', '=', '1')
            ->where('tbl_activos.ControlaAcceso', '=', '1')
            ->where('tbl_activos_detalle.Unico', '=', 'SI')
            ->take(1)
            ->get();


        //Recuperamos las etiquetas de los campos
        $this->data['Campos'] = array();
        foreach ($this->data['tableGrid'] as $t) {
            $this->data['Campos'][$t['field']] = \SiteHelpers::activeLang($t['field'],(isset($t['language'])? $t['language']: array()));
        }

        $sitio = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_APPNAME')->first();
        $this->data['sitio']=$sitio->Valor;

        return view('porteria.form',$this->data);
    }

    function getUpdate(Request $request, $id = null)
    {

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
            $this->data['row'] =  $row;
        } else {
            $this->data['row'] = $this->model->getColumnTable('tbl_accesos');
        }
        $this->data['fields'] =  \AjaxHelpers::fieldLang($this->info['config']['forms']);

        $this->data['id'] = $id;
        return view('porteria.form',$this->data);
    }

    public function getShow( Request $request, $id = null)
    {

        if($this->access['is_detail'] ==0)
        return Redirect::to('dashboard')
            ->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

        $row = $this->model->getRow($id);
        if($row)
        {
            $this->data['row'] =  $row;
            $this->data['fields']       =  \SiteHelpers::fieldLang($this->info['config']['grid']);
            $this->data['id'] = $id;
            $this->data['access']       = $this->access;
            $this->data['subgrid']  = (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
            $this->data['fields'] =  \AjaxHelpers::fieldLang($this->info['config']['grid']);
            return view('porteria.view',$this->data);
        } else {
            return Redirect::to('porteria')->with('messagetext','Record Not Found !')->with('msgstatus','error');
        }
    }

    function postCopy( Request $request)
    {
        foreach(\DB::select("SHOW COLUMNS FROM tbl_accesos ") as $column)
        {
            if( $column->Field != 'IdAcceso')
                $columns[] = $column->Field;
        }

        if(count($request->input('ids')) >=1)
        {
            $toCopy = implode(",",$request->input('ids'));
            $sql = "INSERT INTO tbl_accesos (".implode(",", $columns).") ";
            $sql .= " SELECT ".implode(",", $columns)." FROM tbl_accesos WHERE IdAcceso IN (".$toCopy.")";
            \DB::select($sql);
            return Redirect::to('porteria')->with('messagetext',\Lang::get('core.note_success'))->with('msgstatus','success');
        } else {

            return Redirect::to('porteria')->with('messagetext','Please select row to copy')->with('msgstatus','error');
        }

    }

    public  function postValidaacceso(Request $request) {

        $lintEntidad = $request->entidad?$request->entidad:1;
        $lstrSubEntidad = $request->subentidad?$request->subentidad:'';
        $lstrValorConsulta = str_replace('-','',$request->valorconsulta);
        $lintIdArea = $request->area;
        $uen_id = $request->uen;

        $ldatFecha = date("Y-m-d H:i:s");

        $larrResult = MyAccess::CheckAccess($lintEntidad,0,$lstrValorConsulta,$lintIdArea,$uen_id);

        if ($larrResult['code']!=0 and $larrResult['code']!=4){

            $lintIdEntidad = $larrResult['result']['Data']->IdEntidad;
            $lintIdAccessor = $larrResult['result']['IdAcceso'];

            //Complementamos la información
            if ($lintEntidad==1){ //Es una persona
                if($larrResult['result']['Data']->IdTipoAcceso == 1){
                    $larrResult["data"] = \DB::table('tbl_personas')
                        ->select('tbl_personas.Rut',
                            'tbl_personas.Nombres',
                            'tbl_personas.Apellidos',
                            'tbl_personas.ArchivoFoto',
                            'tbl_contrato.contrato_id',
                            'tbl_contrato.cont_numero',
                            'tbl_contratistas.RazonSocial',
                            \DB::raw("CONCAT(tb_users.first_name,' ',tb_users.last_name) as Administrador"),
                            'tbl_contrato_estatus.BloqueaAcceso'
                        )
                        ->leftJoin('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
                        ->leftJoin('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
                        ->leftJoin('tbl_contrato_estatus','tbl_contrato_estatus.id','=','tbl_contrato.cont_estado')
                        ->leftJoin('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
                        ->leftJoin('tb_users','tbl_contrato.admin_id','=','tb_users.id')
                        ->where('tbl_personas.IdPersona', '=', $lintIdEntidad)
                        ->first();
                }else {
                    $larrResult["data"] = \DB::table('tbl_accesos')
                        ->select(
                            \DB::raw("tbl_accesos.data_rut Rut"),
                            \DB::raw("tbl_accesos.data_nombres Nombres"),
                            \DB::raw("tbl_accesos.data_apellidos Apellidos"),
                            \DB::raw("'' as ArchivoFoto"),
                            \DB::raw("'' as contrato_id"),
                            \DB::raw("'' as cont_numero"),
                            \DB::raw("tbl_contratistas.RazonSocial"),
                            \DB::raw("'N/A' as Administrador"),
                            \DB::raw("0 as BloqueaAcceso")
                        )
                        ->LeftJoin('tbl_personas','tbl_accesos.data_rut','=','tbl_personas.RUT')
                        ->LeftJoin('tbl_contratistas','tbl_personas.entry_by_access','=','tbl_contratistas.entry_by_access')
                        ->where('tbl_accesos.IdAcceso', '=', $lintIdAccessor)
                        ->first();

                }
            }else{ //Es un activo
                    $larrResult["data"] =  \DB::table('tbl_activos_data')
                    ->select('tbl_activos_detalle.Etiqueta',
                             'tbl_activos_data_detalle.Valor',
                             'tbl_contrato.contrato_id',
                             'tbl_contrato.cont_numero',
                             'tbl_contratistas.RazonSocial',
                             \DB::raw("CONCAT(tb_users.first_name,' ',tb_users.last_name) as Administrador"),
                            'tbl_contrato_estatus.BloqueaAcceso'
                             )
                    ->join('tbl_activos_data_detalle', 'tbl_activos_data.IdActivoData', '=', 'tbl_activos_data_detalle.IdActivoData')
                    ->join('tbl_activos_detalle', 'tbl_activos_detalle.IdActivoDetalle', '=', 'tbl_activos_data_detalle.IdActivoDetalle')
                    ->leftJoin('tbl_contrato','tbl_contrato.contrato_id','=','tbl_activos_data.contrato_id')
                    ->leftJoin('tbl_contrato_estatus','tbl_contrato_estatus.id','=','tbl_contrato.cont_estado')
                    ->leftJoin('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
                    ->leftJoin('tb_users','tbl_contrato.admin_id','=','tb_users.id')
                    ->where('tbl_activos_data.IdActivo', '=', $lstrSubEntidad)
                    ->where('tbl_activos_data.IdActivoData', '=', $lintIdEntidad)
                    ->orderBy("tbl_activos_detalle.OrdenForm","asc")
                    ->orderBy("tbl_activos_detalle.IdActivoDetalle","asc")
                    ->get();
            }

            if ($larrResult['code']==1){ //registramos el acceso
                //$larrResult['RegisterAccess'] = MyAccess::RegisterAccess($lintEntidad,$lstrSubEntidad,$lintIdEntidad, $lstrValorConsulta, $lintIdArea, $lintIdAccessor);
                $larrResult['RegisterAccess'] = '';
            }else{ //si no, buscamos el motivo del rechazo
                $larrResult['RegisterAccess'] = MyAccess::CheckAccessLog($lintEntidad,$lstrSubEntidad,$lintIdEntidad, $lstrValorConsulta, $lintIdArea, $lintIdAccessor);

                 $data = array();

                if ($lintEntidad==1 and $lintIdEntidad != 0){ //Es una persona

                    if ($larrResult["data"]->BloqueaAcceso==1){

                        $larrResult['code'] = 5;
                        $data = "Estatus del contrato comercial no permite acceso";

                    }else{

                        //Buscamos los documentos que le faltan a la persona
                        $lobjPersonas = \DB::table('tbl_accesos')
                        ->where('tbl_accesos.IdAcceso',$lintIdAccessor)
                        ->join('tbl_documentos',function($table){
                            $table->on('tbl_documentos.identidad','=','tbl_accesos.IdPersona')
                                ->on('tbl_documentos.contrato_id','=','tbl_accesos.contrato_id');
                        })
                        ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
                        ->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
                        ->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
                        ->distinct()
                        ->select('tbl_tipos_documentos.Descripcion',\DB::raw(' DATE_FORMAT(tbl_documentos.FechaVencimiento, "%d/%m/%Y") as FechaVencimiento'),'tbl_tipos_documentos.BloqueaAcceso','tbl_documentos.IdEstatus',
                            \DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
                            \DB::raw('(CASE WHEN ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 AND tbl_tipos_documentos.Vigencia = 1 THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'tbl_entidades.Entidad')
                        ->where('tbl_documentos.IdEntidad', '=', $lintIdEntidad)
                        ->where('tbl_documentos.Entidad', '=', "3")
                        ->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
                        ->where(function($query) {
                            $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
                                  ->orWhere(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "!=", 1);
                        })
                        ->whereNotExists(function ($query) {
                            $query->select(\DB::raw(1))
                                ->from('tbl_documentos as doc ')
                                ->whereRaw('doc.IdDocumento = tbl_documentos.IdDocumentoRelacion')
                                ->Where(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "=", 1)
                                ->whereIn('doc.IdEstatus',[4,5]);
                        })
                        ->orderBy("tbl_documentos.Entidad")
                        ->get();
                        foreach ($lobjPersonas as $larrPersonas) {
                            array_push($data, $larrPersonas);
                        }

                        //Buscamos los documentos que le faltan al contrato
                        $lobjContratos = \DB::table('tbl_documentos')
                        ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
                        ->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
                        ->join('tbl_contratos_personas', 'tbl_contratos_personas.contrato_id', '=', 'tbl_documentos.IdEntidad')
                        ->join('tbl_personas', 'tbl_personas.IdPersona', '=', 'tbl_contratos_personas.IdPersona')
                        ->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
                        ->distinct()
                        ->select('tbl_tipos_documentos.Descripcion',\DB::raw(' DATE_FORMAT(tbl_documentos.FechaVencimiento, "%d/%m/%Y") as FechaVencimiento'),'tbl_tipos_documentos.BloqueaAcceso', 'tbl_documentos.IdEstatus',
                            \DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
                            \DB::raw('(CASE WHEN ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 AND tbl_tipos_documentos.Vigencia = 1 THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'tbl_entidades.Entidad')
                        ->where('tbl_documentos.Entidad', '=', 2)
                        ->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
                        ->where('tbl_personas.IdPersona', '=', $lintIdEntidad)
                        ->where(function($query) {
                            $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
                                  ->orWhere(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "!=", 1);
                        })
                        ->whereNotExists(function ($query) {
                            $query->select(\DB::raw(1))
                                ->from('tbl_documentos as doc ')
                                ->whereRaw('doc.IdDocumento = tbl_documentos.IdDocumentoRelacion')
                                ->Where(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "=", 1)
                                ->whereIn('doc.IdEstatus',[4,5]);
                        })
                        ->orderBy("tbl_documentos.Entidad")
                        ->get();
                        foreach ($lobjContratos as $larrContratos) {
                            array_push($data, $larrContratos);
                        }

                        //Buscamos los documentos que le faltan al contratista
                        $lobjContratistas = \DB::table('tbl_documentos')
                        ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
                        ->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
                        ->join('tbl_contratos_personas', 'tbl_contratos_personas.IdContratista', '=', 'tbl_documentos.IdEntidad')
                        ->join('tbl_personas', 'tbl_personas.IdPersona', '=', 'tbl_contratos_personas.IdPersona')
                        ->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
                        ->distinct()
                        ->select('tbl_tipos_documentos.Descripcion',\DB::raw(' DATE_FORMAT(tbl_documentos.FechaVencimiento, "%d/%m/%Y") as FechaVencimiento'),'tbl_tipos_documentos.BloqueaAcceso','tbl_documentos.IdEstatus',
                            \DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
                            \DB::raw('(CASE WHEN ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 AND tbl_tipos_documentos.Vigencia = 1 THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'tbl_entidades.Entidad')
                        ->where('tbl_documentos.Entidad', '=', 1)
                        ->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
                        ->where('tbl_personas.IdPersona', '=', $lintIdEntidad)
                        ->where(function($query) {
                            $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
                                  ->orWhere(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "!=", 1);
                        })
                        ->whereNotExists(function ($query) {
                            $query->select(\DB::raw(1))
                                ->from('tbl_documentos as doc ')
                                ->whereRaw('doc.IdDocumento = tbl_documentos.IdDocumentoRelacion')
                                ->Where(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "=", 1)
                                ->whereIn('doc.IdEstatus',[4,5]);
                        })
                        ->orderBy("tbl_documentos.Entidad")
                        ->get();
                        foreach ($lobjContratistas as $larrContratistas) {
                            array_push($data, $larrContratistas);
                        }

                        //Buscamos los documentos que le faltan al subcontratista
                        $lobjSubContratistas = \DB::table('tbl_documentos')
                            ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
                            ->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
                            ->join('tbl_contratos_personas', function ($join) {
                                $join->on('tbl_contratos_personas.contrato_id', '=', 'tbl_documentos.contrato_id')
                                    ->on("tbl_contratos_personas.IdContratista", "=", "tbl_documentos.IdEntidad");
                            })
                            ->join('tbl_personas', 'tbl_personas.IdPersona', '=', 'tbl_contratos_personas.IdPersona')
                            ->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
                            ->distinct()
                            ->select('tbl_tipos_documentos.Descripcion',\DB::raw(' DATE_FORMAT(tbl_documentos.FechaVencimiento, "%d/%m/%Y") as FechaVencimiento'),'tbl_tipos_documentos.BloqueaAcceso','tbl_documentos.IdEstatus',
                                \DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
                                \DB::raw('(CASE WHEN tbl_documentos.FechaVencimiento < NOW() AND tbl_tipos_documentos.Vigencia = 1 THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'tbl_entidades.Entidad')
                            ->where('tbl_documentos.Entidad', '=', 9)
                            ->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
                            ->where('tbl_personas.IdPersona', '=', $lintIdEntidad)
                            ->where(function($query) {
                                $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
                                    ->orWhere(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "!=", 1);
                            })
                            ->whereNotExists(function ($query) {
                                $query->select(\DB::raw(1))
                                    ->from('tbl_documentos as doc ')
                                    ->whereRaw('doc.IdDocumento = tbl_documentos.IdDocumentoRelacion')
                                    ->Where(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "=", 1)
                                    ->whereIn('doc.IdEstatus',[4,5]);
                            })
                            ->orderBy("tbl_documentos.Entidad")
                            ->get();
                        foreach ($lobjSubContratistas as $larrSubContratistas) {
                            array_push($data, $larrSubContratistas);
                        }

                    }

                }else{

                     $lobjActivos = \DB::table('tbl_documentos')
                    ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
                    ->join('vw_entidades', 'vw_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
                    ->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
                    ->select('tbl_tipos_documentos.Descripcion',\DB::raw(' DATE_FORMAT(tbl_documentos.FechaVencimiento, "%d/%m/%Y") as FechaVencimiento'),'tbl_tipos_documentos.BloqueaAcceso','tbl_documentos.IdEstatus',
                        \DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
                        \DB::raw('(CASE WHEN ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'vw_entidades.Entidad')
                    ->where('tbl_documentos.IdEntidad', '=', $lintIdEntidad)
                    ->where('tbl_documentos.Entidad', '=', $lstrSubEntidad)
                    ->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
                    ->where(function($query) {
                        $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
                              ->orWhere(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "!=", 1);
                    })
                    ->orderBy("tbl_documentos.Entidad")
                    ->get();
                    foreach ($lobjActivos as $larrActivos) {
                        array_push($data, $larrActivos);
                    }

                      //Buscamos los documentos que le faltan al contrato
                        $lobjContratos = \DB::table('tbl_documentos')
                        ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
                        ->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
                        ->join('tbl_activos_data', 'tbl_activos_data.contrato_id', '=', 'tbl_documentos.IdEntidad')
                        ->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
                        ->distinct()
                        ->select('tbl_tipos_documentos.Descripcion',\DB::raw(' DATE_FORMAT(tbl_documentos.FechaVencimiento, "%d/%m/%Y") as FechaVencimiento'),'tbl_tipos_documentos.BloqueaAcceso', 'tbl_documentos.IdEstatus',
                            \DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
                            \DB::raw('(CASE WHEN ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 <= NOW() AND tbl_tipos_documentos.Vigencia = 1 THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'tbl_entidades.Entidad')
                        ->where('tbl_documentos.Entidad', '=', 2)
                        ->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
                        ->where('tbl_activos_data.IdActivoData', '=', $lintIdEntidad)
                        ->where(function($query) {
                            $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
                                  ->orWhere(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "!=", 1);
                        })
                        ->orderBy("tbl_documentos.Entidad")
                        ->get();
                        foreach ($lobjContratos as $larrContratos) {
                            array_push($data, $larrContratos);
                        }

                        //Buscamos los documentos que le faltan al contratista
                        $lobjContratistas = \DB::table('tbl_documentos')
                        ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
                        ->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
                        ->join('tbl_contrato', 'tbl_contrato.IdContratista', '=', 'tbl_documentos.IdEntidad')
                        ->join('tbl_activos_data', 'tbl_activos_data.contrato_id', '=', 'tbl_contrato.contrato_id')
                        ->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
                        ->distinct()
                        ->select('tbl_tipos_documentos.Descripcion',\DB::raw(' DATE_FORMAT(tbl_documentos.FechaVencimiento, "%d/%m/%Y") as FechaVencimiento'),'tbl_tipos_documentos.BloqueaAcceso','tbl_documentos.IdEstatus',
                            \DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
                            \DB::raw('(CASE WHEN ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 < NOW() AND tbl_tipos_documentos.Vigencia = 1 THEN CONCAT("Documento Vencido el : ", tbl_documentos.FechaVencimiento) ELSE "" END) AS estaF'), 'tbl_entidades.Entidad')
                        ->where('tbl_documentos.Entidad', '=', 1)
                        ->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
                        ->where('tbl_activos_data.IdActivoData', '=', $lintIdEntidad)
                        ->where(function($query) {
                            $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
                                  ->orWhere(\DB::raw('ifnull(tbl_documentos.IdEstatusDocumento,1)'), "!=", 1);
                        })
                        ->orderBy("tbl_documentos.Entidad")
                        ->get();
                        foreach ($lobjContratistas as $larrContratistas) {
                            array_push($data, $larrContratistas);
                        }

                }

                $larrResult["Motivo"] = $data;

            }

        }else{
            $lintIdPersona = \DB::table('tbl_personas')->where('RUT',$request->valorconsulta)->value('IdPersona');
            if($lintIdPersona>0){
              //vemos si la persona tiene la marca de acreditado
                $lobjContratoPersona = Contratospersonas::where('IdPersona',$lintIdPersona)->first();
                $lintContratoId = $lobjContratoPersona->contrato_id;

                //vemos si el contrato necesita Acreditacion
                $lobjContrato = Contratos::where('contrato_id',$lintContratoId)->first();
                $cliente = \DB::table('tbl_configuraciones')->where('Nombre','CNF_APPNAME')->value('Valor');
                if($lobjContrato->acreditacion==1){
                    $acreditadoC = \DB::table('tbl_contratos_acreditacion')->where('contrato_id',$lintContratoId)->orderBy('id', 'desc')->select('idestatus','acreditacion')->first();
                    if($acreditadoC->idestatus==1 and !is_null($acreditadoC->acreditacion)){
                        if($lobjContratoPersona->acreditacion==1){
                            $acreditadoP = \DB::table('tbl_personas_acreditacion')->where('idpersona',$lintIdPersona)->where('contrato_id',$lintContratoId)->orderBy('id', 'desc')->select('idestatus','acreditacion')->first();
                            if($acreditadoP)
                            if($acreditadoP->idestatus==1 and !is_null($acreditadoP->acreditacion)){
                                $larrResult['code']=1;
                                $larrResult['msgcode']="Tiene acceso";
                                if($cliente=='CCU'){

                                  if($lobjContratoPersona->controllaboral==1){

                                    $contrato_uen_ct = \DB::table('tbl_contrato_centrotrabajo')
                                      ->join('tbl_uen_ct','tbl_uen_ct.uenct_id','=','tbl_contrato_centrotrabajo.uen_ct_id')
                                      ->where('tbl_contrato_centrotrabajo.contrato_id',$lintContratoId)
                                      ->where('tbl_uen_ct.uen_id',$uen_id)
                                      ->where('tbl_uen_ct.ct_id',$lintIdArea)
                                      ->first();

                                    if(!$contrato_uen_ct){
                                      $larrResult['code']=4;
                                      $larrResult['msgcode']="No Tiene acceso";
                                    }
                                  }else{
                                    $larrResult['code']=4;
                                    $larrResult['msgcode']="No Tiene acceso";
                                  }
                                }
                            }else{
                                $larrResult['code']=4;
                                $larrResult['msgcode']="No Tiene acceso";
                            }
                        }else{
                            $larrResult['code']=1;
                            $larrResult['msgcode']="Tiene acceso";
                        }
                    }else{
                         $larrResult['code']=4;
                                $larrResult['msgcode']="No Tiene acceso";
                    }
                }else{
                    $larrResult['code']=1;
                                $larrResult['msgcode']="Tiene acceso";
                }

                $larrResult['result']["Data"] =(object) ['IdTipoAcceso' => '3'];

                $larrResult["data"] = \DB::table('tbl_personas')
                        ->select('tbl_personas.Rut',
                            'tbl_personas.Nombres',
                            'tbl_personas.Apellidos',
                            'tbl_personas.ArchivoFoto',
                            'tbl_contrato.contrato_id',
                            'tbl_contrato.cont_numero',
                            'tbl_contratistas.RazonSocial',
                            \DB::raw("CONCAT(tb_users.first_name,' ',tb_users.last_name) as Administrador"),
                            'tbl_contrato_estatus.BloqueaAcceso'
                        )
                        ->leftJoin('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
                        ->leftJoin('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
                        ->leftJoin('tbl_contrato_estatus','tbl_contrato_estatus.id','=','tbl_contrato.cont_estado')
                        ->leftJoin('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
                        ->leftJoin('tb_users','tbl_contrato.admin_id','=','tb_users.id')
                        ->where('tbl_personas.IdPersona', '=', $lintIdPersona)
                        ->first();
                    }else{

                      $activos = \DB::table('tbl_activos_data_detalle')->where('Valor',$request->valorconsulta)->first();
              				if($activos){

              					$idactivodata = $activos->IdActivoData;
              					$documentos = \DB::table('tbl_documentos_activos')->where('idactivodata',$idactivodata)->get();
              					$flag=false;

              					foreach ($documentos as $documento) {
              						$doc = \DB::table('tbl_documentos')->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
              								->where('IdDocumento',$documento->iddocumento)
              								->where('tbl_tipos_documentos.Acreditacion',1)
              								->first();
              						if($doc)
              						if($doc->IdEstatus!=5 or $doc->IdEstatusDocumento==2){
              							$flag=true;
              						}
              					}

              					if($flag){
                          $larrResult['code']=4;
                          $larrResult['msgcode']="No Tiene acceso";
              					}else{

                          $larrResult['code']=1;
                          $larrResult['msgcode']="Tiene acceso";
              					}
                        $larrResult["data"] =  \DB::table('tbl_activos_data')
                        ->select('tbl_activos_detalle.Etiqueta',
                                 'tbl_activos_data_detalle.Valor',
                                 'tbl_contrato.contrato_id',
                                 'tbl_contrato.cont_numero',
                                 'tbl_contratistas.RazonSocial',
                                 \DB::raw("CONCAT(tb_users.first_name,' ',tb_users.last_name) as Administrador"),
                                'tbl_contrato_estatus.BloqueaAcceso'
                                 )
                        ->join('tbl_activos_data_detalle', 'tbl_activos_data.IdActivoData', '=', 'tbl_activos_data_detalle.IdActivoData')
                        ->join('tbl_activos_detalle', 'tbl_activos_detalle.IdActivoDetalle', '=', 'tbl_activos_data_detalle.IdActivoDetalle')
                        ->leftJoin('tbl_contrato','tbl_contrato.contrato_id','=','tbl_activos_data.contrato_id')
                        ->leftJoin('tbl_contrato_estatus','tbl_contrato_estatus.id','=','tbl_contrato.cont_estado')
                        ->leftJoin('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
                        ->leftJoin('tb_users','tbl_contrato.admin_id','=','tb_users.id')
                        ->where('tbl_activos_data.IdActivo', '=', $lstrSubEntidad)
                        ->where('tbl_activos_data.IdActivoData', '=', $idactivodata)
                        ->orderBy("tbl_activos_detalle.OrdenForm","asc")
                        ->orderBy("tbl_activos_detalle.IdActivoDetalle","asc")
                        ->get();
                      }else{
                        $larrResult['code']=0;
                        $larrResult['msgcode']="No Tiene acceso";
                      }

                    }
                        $larrResult['RegisterAccess'] = '';
        }

        return $larrResult;

	}

	function postSave( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tb_porteria');

			$id = $this->model->insertRow($data , $request->input('IdAcceso'));

			if(!is_null($request->input('apply')))
			{
				$return = 'porteria/update/'.$id.'?return='.self::returnUrl();
			} else {
				$return = 'porteria?return='.self::returnUrl();
			}

			// Insert logs into database
			if($request->input('IdAcceso') =='')
			{
				\SiteHelpers::auditTrail( $request , 'New Data with ID '.$id.' Has been Inserted !');
			} else {
				\SiteHelpers::auditTrail($request ,'Data with ID '.$id.' Has been Updated !');
			}

			return Redirect::to($return)->with('messagetext',\Lang::get('core.note_success'))->with('msgstatus','success');

		} else {

			return Redirect::to('porteria/update/'. $request->input('IdAcceso'))->with('messagetext',\Lang::get('core.note_error'))->with('msgstatus','error')
			->withErrors($validator)->withInput();
		}

	}

	public function postDelete( Request $request)
	{

		if($this->access['is_remove'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
		// delete multipe rows
		if(count($request->input('ids')) >=1)
		{
			$this->model->destroy($request->input('ids'));

			\SiteHelpers::auditTrail( $request , "ID : ".implode(",",$request->input('ids'))."  , Has Been Removed Successfull");
			// redirect
			return Redirect::to('porteria')
        		->with('messagetext', \Lang::get('core.note_success_delete'))->with('msgstatus','success');

		} else {
			return Redirect::to('porteria')
        		->with('messagetext','No Item Deleted')->with('msgstatus','error');
		}

	}

	public static function display( )
	{
		$mode  = isset($_GET['view']) ? 'view' : 'default' ;
		$model  = new Porteria();
		$info = $model::makeInfo('porteria');

		$data = array(
			'pageTitle'	=> 	$info['title'],
			'pageNote'	=>  $info['note']

		);

		if($mode == 'view')
		{
			$id = $_GET['view'];
			$row = $model::getRow($id);
			if($row)
			{
				$data['row'] =  $row;
				$data['fields'] 		=  \SiteHelpers::fieldLang($info['config']['grid']);
				$data['id'] = $id;
				return view('porteria.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdAcceso' ,
				'order'		=> 'asc',
				'params'	=> '',
				'global'	=> 1
			);

			$result = $model::getRows( $params );
			$data['tableGrid'] 	= $info['config']['grid'];
			$data['rowData'] 	= $result['rows'];

			$page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
			$pagination = new Paginator($result['rows'], $result['total'], $params['limit']);
			$pagination->setPath('');
			$data['i']			= ($page * $params['limit'])- $params['limit'];
			$data['pagination'] = $pagination;
			return view('porteria.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_accesos');
			 $this->model->insertRow($data , $request->input('IdAcceso'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}

    /*
		public  function postValidarrut(Request $request)
	{
		$rut = $request->rut;
		$area = $request->area;
		$actual = date("Y-m-d H:i:s");


		    $users = \DB::table('tbl_personas')
			->select(	'tbl_personas.IdPersona',
						'tbl_personas.Nombres',
						'tbl_personas.Apellidos',
						'tbl_personas.ArchivoFoto',
						'tbl_accesos.IdTipoAcceso',
						'tbl_accesos.Observacion',
						'tbl_accesos.IdEstatus as acceso',
						'tbl_acceso_areas.IdEstatus as area',
						'tbl_accesos.IdAcceso'	,
						'tbl_accesos.contrato_id',
						'tbl_acceso_areas.IdAreaTrabajo',
						'tbl_contrato.cont_proveedor',
						\DB::raw("CONCAT(tb_users.first_name,' ',tb_users.last_name) as Administrador")
					)
			->leftJoin('tbl_accesos', 'tbl_accesos.IdPersona', '=', 'tbl_personas.IdPersona')
        	->leftJoin('tbl_acceso_areas','tbl_accesos.IdAcceso', '=',\DB::raw('tbl_acceso_areas.IdAcceso AND tbl_acceso_areas.IdAreaTrabajo = '.$area))
			->leftJoin('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
			->leftJoin('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
			->leftJoin('tb_users','tbl_contrato.admin_id','=','tb_users.id')
			->where(\DB::raw("REPLACE(RUT,'-','')"), '=', $rut)
			->get();
			$acce = $users[0]->IdAcceso;
			$per = $users[0]->IdPersona;
			$cont = $users[0]->contrato_id;

			foreach ($users as $larrUsers) {
				if ($larrUsers->acceso==1){
				  if ($larrUsers->IdAreaTrabajo==$area){
				  	$larrPersona[] = $larrUsers;
				    return response()->json(array(
				        'status'=>'sucess',
				        'valores'=>$larrPersona,
				        'message'=>\Lang::get('core.note_sucess')
				    ));
				  }
				}
			}

			if ($users[0]->acceso!=1) {

			    $data = array();

			    //Buscamos los documentos que le faltan a la persona
				$lobjPersonas = \DB::table('tbl_documentos')
				->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
				->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
				->leftJoin('tbl_accesos', 'tbl_documentos.contrato_id', '=', 'tbl_accesos.contrato_id')
				->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
				->select('tbl_tipos_documentos.Descripcion','tbl_documentos.FechaVencimiento','tbl_tipos_documentos.BloqueaAcceso','tbl_documentos.IdEstatus',
					\DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
					\DB::raw('(CASE WHEN tbl_documentos.FechaVencimiento <= NOW() THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'tbl_entidades.Entidad')
				->where('tbl_accesos.IdAcceso', '=', $acce)
				->where('tbl_documentos.IdEntidad', '=', $per)
				->where('tbl_documentos.Entidad', '=', "3")
				->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
				->where(function($query) {
	                $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
	                      ->OrWhere('tbl_documentos.FechaVencimiento', '<', 'NOW()');
	            })
	            ->orderBy("tbl_documentos.Entidad")
				->get();
				foreach ($lobjPersonas as $larrPersonas) {
					array_push($data, $larrPersonas);
				}

				//Buscamos los documentos que le faltan al contrato
				$lobjContratos = \DB::table('tbl_documentos')
				->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
				->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
				->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
				->select('tbl_tipos_documentos.Descripcion','tbl_documentos.FechaVencimiento','tbl_tipos_documentos.BloqueaAcceso', 'tbl_documentos.IdEstatus',
					\DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
					\DB::raw('(CASE WHEN tbl_documentos.FechaVencimiento <= NOW() THEN "Documento Vencido el : " ELSE 0 END) AS estaF'), 'tbl_entidades.Entidad')
				->where('tbl_documentos.contrato_id', '=', $cont)
				->where('tbl_documentos.Entidad', '=', 2)
				->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
				->where(function($query) {
	                $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
	                      ->OrWhere('tbl_documentos.FechaVencimiento', '<', 'NOW()');
	            })
	            ->orderBy("tbl_documentos.Entidad")
				->get();
				foreach ($lobjContratos as $larrContratos) {
					array_push($data, $larrContratos);
				}

				//Buscamos los documentos que le faltan al contratista
				$lobjContratistas = \DB::table('tbl_documentos')
				->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
				->join('tbl_entidades', 'tbl_entidades.IdEntidad', '=', 'tbl_documentos.entidad')
				->leftJoin('tbl_documentos_estatus', 'tbl_documentos.IdEstatus', '=', 'tbl_documentos_estatus.IdEstatus')
				->select('tbl_tipos_documentos.Descripcion','tbl_documentos.FechaVencimiento','tbl_tipos_documentos.BloqueaAcceso','tbl_documentos.IdEstatus',
					\DB::raw('ifnull(tbl_documentos_estatus.descripcion," no especificado ") AS esta'),
					\DB::raw('(CASE WHEN tbl_documentos.FechaVencimiento < NOW() THEN CONCAT("Documento Vencido el : ", tbl_documentos.FechaVencimiento) ELSE "" END) AS estaF'), 'tbl_entidades.Entidad')
				->where('tbl_documentos.contrato_id', '=', $cont)
				->where('tbl_documentos.Entidad', '=', 1)
				->where('tbl_tipos_documentos.BloqueaAcceso', '=', 'SI')
				//->whereNotIn('tbl_documentos.IdEstatus', [4,5])
				->where(function($query) {
	                $query->whereNotIn('tbl_documentos.IdEstatus', [4,5])
	                      ->OrWhere('tbl_documentos.FechaVencimiento', '<', 'NOW()');
	            })
	            ->orderBy("tbl_documentos.Entidad")
				->get();
				foreach ($lobjContratistas as $larrContratistas) {
					array_push($data, $larrContratistas);
				}

			    return response()->json(array(
					'status'=>'sucess',
					'valores'=>$users,
					'razon'=>$data,
					'message'=>\Lang::get('core.note_sucess')
				));
			}else{
				return response()->json(array(
				    'status'=>'sucess',
				    'valores'=>$users,
				    'message'=>\Lang::get('core.note_sucess')
				));
			}

	}
    */

	public  function postCompruebarut(Request $request)
	{
		$rut = $request->rut;
		#echo $rut."<br>";
		$personas = \DB::table('tbl_personas')
		->select("Nombres","Apellidos","ArchivoFoto","RUT")
		->where(\DB::raw("REPLACE(RUT,'-','')"), "=", $rut)
		->get();

		#var_dump($personas);

		return response()->json(array(
			'status'=>'sucess',
			'valores'=>$personas,
			'message'=>\Lang::get('core.note_sucess')
			));
	}

  function getComboselectct( Request $request)
	{

		if($request->ajax() == true && \Auth::check() == true)
		{
      $uen_id = $request->input('ct_id');
			$items = array();

      $cts = \DB::table('tbl_uen_ct')->join('tbl_centro_trabajo','tbl_uen_ct.ct_id','=','tbl_centro_trabajo.ct_id')->where('tbl_uen_ct.uen_id',$uen_id)->get();
      foreach ($cts as $ct) {
        array_push($items,[$ct->ct_id,$ct->descripcion]);
      }
			return json_encode($items);
		} else {
			return json_encode(array('OMG'=>" Ops"));
		}
	}

}
