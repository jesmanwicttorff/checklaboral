<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Buscadordocumental;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use Yajra\Datatables\Facades\Datatables;
use ZipArchive;

class BuscadordocumentalController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'buscadordocumental';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Buscadordocumental();

		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'buscadordocumental',
			'pageUrl'			=>  url('buscadordocumental'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('buscadordocumental.index',$this->data);
	}

	public function postData( Request $request)
	{
		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    	$lintIdUser = \Session::get('uid');
		/*
		//Recuperamos la lista de los tipos de documetos
		$lobjContratistas = \DB::table('tbl_tipos_documentos')
		->whereExists(function ($query) {
            $query->select(\DB::raw(1))
                  ->from('tbl_documentos')
                  ->whereRaw('tbl_documentos.IdTipoDocumento = tbl_tipos_documentos.IdTipoDocumento')
                  ->whereRaw('tbl_documentos.IdEstatus = 5');
        });
		//Recuperamos la lista de los tipos de documetos
		$lobjTipoDocumentos = \DB::table('tbl_tipos_documentos')
		->whereExists(function ($query) {
            $query->select(\DB::raw(1))
                  ->from('tbl_documentos')
                  ->whereRaw('tbl_documentos.IdTipoDocumento = tbl_tipos_documentos.IdTipoDocumento')
                  ->whereRaw('tbl_documentos.IdEstatus = 5');
        });
		//Recuperamos los contratistas
		$lobjContratistas = \DB::table('tbl_contratistas')
		->whereExists(function ($query) {
            $query->select(\DB::raw(1))
                  ->from('tbl_documentos')
                  ->whereRaw('tbl_documentos.entidad = 1')
                  ->whereRaw('tbl_documentos.IdEntidad = tbl_contratistas.IdContratista')
                  ->whereRaw('tbl_documentos.IdEstatus = 5');
        });
		//Recuperamos los contratos
		$lobjContratos = \DB::table('tbl_contrato')
		->whereExists(function ($query) {
            $query->select(\DB::raw(1))
                  ->from('tbl_documentos')
                  ->whereRaw('tbl_documentos.entidad = 2')
                  ->whereRaw('tbl_documentos.IdEntidad = tbl_contrato.contrato_id')
                  ->whereRaw('tbl_documentos.IdEstatus = 5');
        });
		//Recuperamos las personas
		$lobjPersonas = \DB::table('tbl_personas')
		->whereExists(function ($query) {
            $query->select(\DB::raw(1))
                  ->from('tbl_documentos')
                  ->whereRaw('tbl_documentos.entidad = 3')
                  ->whereRaw('tbl_documentos.IdEntidad = tbl_personas.IdPersona')
                  ->whereRaw('tbl_documentos.IdEstatus = 5');
        });
		*/

        $this->data['lintLevelUser']      = $lintLevelUser;
        $this->data['lintIdUser']      = $lintIdUser;
		$this->data['setting']      = $this->info['setting'];
        $this->data['tableGrid']    = $this->info['config']['grid'];
        $this->data['access']       = $this->access;
		return view('buscadordocumental.table',$this->data);
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
			$this->data['row'] 		=  $row;
		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_documentos');
		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);

		$this->data['id'] = $id;

		return view('buscadordocumental.form',$this->data);
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
			$this->data['access']		= $this->access;
			$this->data['setting'] 		= $this->info['setting'];
			$this->data['fields'] 		= \AjaxHelpers::fieldLang($this->info['config']['grid']);
			$this->data['subgrid']		= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
			return view('buscadordocumental.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}

	public function postShowlist( Request $request )
	{

		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
		$lintGroupUser = \MySourcing::GroupUser(\Session::get('uid'));
    	$lintIdUser = \Session::get('uid');
		$larrDataFilter = $request->input("data");
		$lintIdContratista = isset($larrDataFilter[1]['filtro'])?$larrDataFilter[1]['filtro']:'';
		$lintIdContrato = isset($larrDataFilter[2]['filtro'])?$larrDataFilter[2]['filtro']:'';
		$lintIdPersona = isset($larrDataFilter[3]['filtro'])?$larrDataFilter[3]['filtro']:'';
		$lintIdTipoDocumento = isset($larrDataFilter[4]['filtro'])?$larrDataFilter[4]['filtro']:'';
		$lintIdEstatus = isset($larrDataFilter[5]['filtro'])?$larrDataFilter[5]['filtro']:'';
		$ldatIdFechaEmision = isset($larrDataFilter[6]['filtro'])?$larrDataFilter[6]['filtro']:'';
		$ldatIdFechaVencimiento = isset($larrDataFilter[7]['filtro'])?$larrDataFilter[7]['filtro']:'';
		$Hist =$request->input('Hist');


		$lobjQuerDoc = \DB::table('tbl_documentos')
		->select(\DB::raw("'' as Accion"), "tbl_documentos.IdDocumento", \DB::raw("tbl_entidades.Entidad as DesEntidad"), \DB::raw("tbl_tipos_documentos.Descripcion as TipoDocumento"),
            "vw_entidades_detalle.Detalle",  \DB::raw("tbl_documentos_estatus.Descripcion as Estatus"), "tbl_documentos.FechaVencimiento", "tbl_documentos.FechaEmision","tbl_documentos.createdOn", "tbl_documentos.Resultado",
            \DB::raw("tbl_contrato.cont_numero as contrato"), "tbl_documentos.DocumentoURL", "tbl_documentos.IdEstatus","tbl_documentos.IdEntidad",
            "tbl_documentos.Entidad","tbl_documentos.contrato_id","tbl_documentos.IdTipoDocumento","tbl_documentos.Vencimiento",\DB::raw("'1' as validador"))
		->join("tbl_tipos_documentos","tbl_tipos_documentos.IdTipoDocumento","=","tbl_documentos.IdTipoDocumento")
		->join("tbl_documentos_estatus","tbl_documentos.IdEstatus","=","tbl_documentos_estatus.IdEstatus")
		->join("tbl_entidades","tbl_entidades.IdEntidad","=","tbl_documentos.Entidad")
		->leftjoin("tbl_contrato","tbl_documentos.contrato_id","=","tbl_contrato.contrato_id")
		->join('vw_entidades_detalle', function ($join) {
            $join->on('vw_entidades_detalle.IdEntidad', '=', 'tbl_documentos.IdEntidad')
                 ->on("vw_entidades_detalle.Entidad","=","tbl_documentos.Entidad");
        });

		if ($Hist){

            $lobjQueryT = \DB::table('tbl_documentos_rep_historico')
                ->select(\DB::raw("'' as Accion"), "tbl_documentos_rep_historico.IdDocumento", \DB::raw("tbl_entidades.Entidad as DesEntidad"),
                    \DB::raw("tbl_tipos_documentos.Descripcion as TipoDocumento"), "vw_entidades_detalle.Detalle",
                    \DB::raw("tbl_documentos_estatus.Descripcion as Estatus"), "tbl_documentos_rep_historico.FechaVencimiento", "tbl_documentos_rep_historico.FechaEmision", \DB::raw("'' as createdOn"), "tbl_documentos_rep_historico.Resultado",
                    \DB::raw("tbl_contrato.cont_numero as contrato"), "tbl_documentos_rep_historico.DocumentoURL","tbl_documentos_rep_historico.IdEstatus",
                    "tbl_documentos_rep_historico.IdEntidad","tbl_documentos_rep_historico.Entidad","tbl_documentos_rep_historico.contrato_id","tbl_documentos_rep_historico.IdTipoDocumento",\DB::raw("'' as Vencimiento"),\DB::raw("'2' as validador"))
                ->join("tbl_documentos_estatus","tbl_documentos_rep_historico.IdEstatus","=","tbl_documentos_estatus.IdEstatus")
                ->join("tbl_tipos_documentos","tbl_tipos_documentos.IdTipoDocumento","=","tbl_documentos_rep_historico.IdTipoDocumento")
                ->join("tbl_entidades","tbl_entidades.IdEntidad","=","tbl_documentos_rep_historico.Entidad")
                ->leftjoin("tbl_contrato","tbl_documentos_rep_historico.contrato_id","=","tbl_contrato.contrato_id")
                ->join('vw_entidades_detalle', function ($join) {
                    $join->on('vw_entidades_detalle.IdEntidad', '=', 'tbl_documentos_rep_historico.IdEntidad')
                        ->on("vw_entidades_detalle.Entidad","=","tbl_documentos_rep_historico.Entidad");
                });

            $lobjQuer = $lobjQuerDoc->union($lobjQueryT);

            $lobjQuery = \DB::table(\DB::raw("({$lobjQuer->toSql()}) as tbl_documentos"))->select(\DB::raw("*"));

        }
        else{
            $lobjQuery = \DB::table(\DB::raw("({$lobjQuerDoc->toSql()}) as tbl_documentos"))->select(\DB::raw("*"));
        }

		if ($lintIdPersona!=""){
			if ($lintIdPersona!="0"){
                    $lobjQuery->where("tbl_documentos.identidad", "=", $lintIdPersona);
                    $lobjQuery->where("tbl_documentos.entidad", "=", 3);
				if ($lintIdContrato!="" && $lintIdContrato!="0"){
					$lobjQuery->where("tbl_documentos.contrato_id","=",$lintIdContrato);
				}else if ($lintIdContratista!="" && $lintIdContratista!="0"){
					$lobjQuery->WhereExists(function ($query) use ($lintIdContratista) {
	                	$query->select(\DB::raw(1))
	                      ->from('tbl_contrato')
	                      ->whereRaw('tbl_contrato.contrato_id = tbl_documentos.contrato_id')
	                      ->whereRaw('tbl_contrato.IdContratista = '.$lintIdContratista);
		            });
				}
			}else{
				$lobjQuery->where("tbl_documentos.entidad","=",3);
				$lobjQuery->Where(function ($query) use ($lintIdContratista, $lintIdContrato, $lintIdPersona) {
					if ($lintIdContratista!="" && $lintIdContratista != "0"){
	        			$query->WhereExists(function ($querydos) use ($lintIdContratista) {
		                	$querydos->select(\DB::raw(1))
		                      ->from('tbl_contratos_personas')
		                      ->whereRaw('tbl_contratos_personas.IdPersona = tbl_documentos.IdEntidad')
                              ->whereRaw('tbl_contratos_personas.IdContratista = '.$lintIdContratista)
		                      ->whereRaw('tbl_documentos.Entidad = 3');
			            });
	        		}
	        		if ($lintIdContrato!="" && $lintIdContrato!="0"){
		        		$query->WhereExists(function ($querydos) use ($lintIdContrato) {
		                	$querydos->select(\DB::raw(1))
		                      ->from('tbl_contratos_personas')
		                      ->whereRaw('tbl_contratos_personas.IdPersona = tbl_documentos.IdEntidad')
		                      ->whereRaw('tbl_contratos_personas.contrato_id = '.$lintIdContrato)
		                      ->whereRaw('tbl_documentos.Entidad = 3');
		                });
		        	}
	        	});
			}
		}else if ($lintIdContrato!=""){
			if ($lintIdContrato!="0"){
				$lobjQuery->Where(function ($query) use ($lintIdContratista, $lintIdContrato, $lintIdPersona) {
	           		$query->orWhereExists(function ($querydos) use ($lintIdContrato) {
	                	$querydos->select(\DB::raw(1))
	                      ->from('tbl_contrato')
	                      ->whereRaw('tbl_contrato.contrato_id = tbl_documentos.IdEntidad')
	                      ->whereRaw('tbl_contrato.contrato_id = '.$lintIdContrato)
	                      ->whereRaw('tbl_documentos.Entidad = 2');
	                });
				    $query->orWhereExists(function ($querydos) use ($lintIdContrato) {
	                	$querydos->select(\DB::raw(1))
	                      ->from('tbl_contratos_personas')
	                      ->whereRaw('tbl_contratos_personas.IdPersona = tbl_documentos.IdEntidad')
	                      ->whereRaw('tbl_contratos_personas.contrato_id = '.$lintIdContrato)
	                      ->whereRaw('tbl_documentos.Entidad = 3');
	                });
	        	});
	        }else{
	        	if ($lintIdContratista!="") {
		        	$lobjQuery->Where(function ($query) use ($lintIdContratista, $lintIdContrato, $lintIdPersona) {

		        			$query->WhereExists(function ($querydos) use ($lintIdContratista) {
			                	$querydos->select(\DB::raw(1))
			                      ->from('tbl_contratistas')
			                      ->whereRaw('tbl_contrato.contrato_id = tbl_documentos.IdEntidad')
                                  ->whereRaw('tbl_contrato.IdContratista = '.$lintIdContratista)
			                      ->whereRaw('tbl_documentos.Entidad = 2');
				            });
			        		$query->orWhereExists(function ($querydos) use ($lintIdContratista) {
			                	$querydos->select(\DB::raw(1))
			                      ->from('tbl_contratos_personas')
			                      ->whereRaw('tbl_contratos_personas.IdPersona = tbl_documentos.IdEntidad')
			                      ->whereRaw('tbl_contratos_personas.IdContratista = '.$lintIdContratista)
			                      ->whereRaw('tbl_documentos.Entidad = 3');
			                });
		        	});
	        	}
	        }
		}else if ($lintIdContratista!=""){
			$lobjQuery->Where(function ($query) use ($lintIdContratista, $lintIdContrato, $lintIdPersona) {
           		$query->WhereExists(function ($querydos) use ($lintIdContratista) {
                	$querydos->select(\DB::raw(1))
                      ->from('tbl_contratistas')
                      ->whereRaw('tbl_contratistas.IdContratista = tbl_documentos.IdEntidad')
                      ->whereRaw('tbl_contratistas.IdContratista = '.$lintIdContratista)
                      ->whereRaw('tbl_documentos.Entidad = 1');
	            });
                $query->orWhereExists(function ($querydos) use ($lintIdContratista) {
                	$querydos->select(\DB::raw(1))
                      ->from('tbl_contrato')
                      ->whereRaw('tbl_contrato.contrato_id = tbl_documentos.IdEntidad')
                      ->whereRaw('tbl_contrato.IdContratista = '.$lintIdContratista)
                      ->whereRaw('tbl_documentos.Entidad = 2');
                });
			    $query->orWhereExists(function ($querydos) use ($lintIdContratista) {
                	$querydos->select(\DB::raw(1))
                      ->from('tbl_contratos_personas')
                      ->whereRaw('tbl_contratos_personas.IdPersona = tbl_documentos.IdEntidad')
                      ->whereRaw('tbl_contratos_personas.IdContratista = '.$lintIdContratista)
                      ->whereRaw('tbl_documentos.Entidad = 3');
                });
        	});
		}
		if ($lintIdTipoDocumento){
			$lobjQuery->where("tbl_documentos.IdTipoDocumento","=",$lintIdTipoDocumento);
		}
		if ($lintIdEstatus){
			if ($lintIdEstatus==6)
			{
				$lobjQuery->where("tbl_documentos.IdEstatusDocumento","=",2);
			}else{
			$lobjQuery->where("tbl_documentos.IdEstatus","=",$lintIdEstatus);
			}
		}

		if ($ldatIdFechaEmision){
			$larrEmisionDate = explode("|",$ldatIdFechaEmision);
			if ($larrEmisionDate[0] && $larrEmisionDate[1]){
                $larrEmisionDate[0] = \MyFormats::FormatoFecha($larrEmisionDate[0]);
                $larrEmisionDate[1] = \MyFormats::FormatoFecha($larrEmisionDate[1]);
				$lobjQuery->whereBetween("FechaEmision",$larrEmisionDate);
			}else{
				if ($larrEmisionDate[0]){
					$lobjQuery->where("FechaEmision",">=",\MyFormats::FormatoFecha($larrEmisionDate[0]));
				}else if ($larrEmisionDate[1]){
					$lobjQuery->where("FechaEmision","<=",\MyFormats::FormatoFecha($larrEmisionDate[1]));
				}
			}
		}
		if ($ldatIdFechaVencimiento){
			$larrVencimientoDate = explode("|",$ldatIdFechaVencimiento);
			if ($larrVencimientoDate[0] && $larrVencimientoDate[1]){
                $larrVencimientoDate[0] = \MyFormats::FormatoFecha($larrVencimientoDate[0]);
                $larrVencimientoDate[1] = \MyFormats::FormatoFecha($larrVencimientoDate[1]);
				$lobjQuery->whereBetween("FechaVencimiento",$larrVencimientoDate);
			}else{
				if ($larrVencimientoDate[0]){
					$lobjQuery->where("FechaVencimiento",">=",\MyFormats::FormatoFecha($larrVencimientoDate[0]));
				}else if ($larrVencimientoDate[1]){
					$lobjQuery->where("FechaVencimiento","<=",\MyFormats::FormatoFecha($larrVencimientoDate[1]));
				}
			}
		}

        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);

        if ($lintLevelUser==6 || $lintLevelUser==15) {
        	if ($lobjFiltro['contratos'] != "''"){
        		$lstrFiltroContratos = ' tbl_documentos.contrato_id IN ('.$lobjFiltro['contratos'].') ';
        	}else{
        		$lstrFiltroContratos = '';
        	}
        	//echo $lobjFiltro['contratistas'];
        	//break; != "''"
        	if ($lobjFiltro['contratistas'] != "''") {
        		if ($lstrFiltroContratos) {
        			$lstrFiltroContratista = ' OR ';
        		}else{
        			$lstrFiltroContratista = '';
        	    }
        		$lstrFiltroContratista .= '  ( tbl_documentos.entidad = 1 AND tbl_documentos.IdEntidad IN ('.$lobjFiltro['contratistas'].') ) ';
        	}else{
				$lstrFiltroContratista = '';
        	}
        	if ($lstrFiltroContratista || $lstrFiltroContratos){
        		//echo '( '.$lstrFiltroContratos.' '.$lstrFiltroContratista.' )'; break;
            	$lobjQuery->whereraw('( '.$lstrFiltroContratos.' '.$lstrFiltroContratista.' )');
            }else{
            	$lobjQuery->whereraw(' 1 = 2 ');
            }
        }else{
            $lobjQuery->whereraw('(tbl_documentos.contrato_id IN ('.$lobjFiltro['contratos'].') OR ( tbl_documentos.entidad = 1 AND tbl_documentos.IdEntidad IN ('.$lobjFiltro['contratistas'].') ))');
        }



        $lobjQuery->OrderBy("tbl_documentos.Entidad","ASC");
        $lobjQuery->OrderBy("tbl_documentos.IdEntidad","ASC");
        $lobjQuery->OrderBy("tbl_documentos.IdTipoDocumento","ASC");


		// Using Query Builder
		$lobjDataTable = Datatables::queryBuilder($lobjQuery)
		->editColumn('FechaVencimiento', function ($lobjDocumentos) {
		    if ($lobjDocumentos->Vencimiento){
                return \MyFormats::FormatDate($lobjDocumentos->FechaVencimiento);
            }else{
                return ('');
            }

        })
        ->editColumn('FechaEmision', function ($lobjDocumentos) {
            return \MyFormats::FormatDate($lobjDocumentos->FechaEmision);
        })
            ->editColumn('validador', function ($lobjDocumentos) {
                if ($lobjDocumentos->validador){
                    if ($lobjDocumentos->validador=="2"){
                        return "<i class=\"fa fa-folder-open-o fa-2x\"></i>";
                    }else{
                        return "";
                    }
                }else{
                    return $lobjDocumentos->validador;
                }
            })
        ->editColumn('TipoDocumento', function ($lobjDocumentos) {
        	if ($lobjDocumentos->DocumentoURL){
	        	if (strrpos($lobjDocumentos->DocumentoURL, "/")===false){
	            	return "<a href=\"".url("uploads/documents/".$lobjDocumentos->DocumentoURL)."\" download> ".$lobjDocumentos->TipoDocumento." <i class=\"fa fa-download\"></i> </a>  <a class=\"btn-view-pdf\" onclick=\"ViewPDF('".url("uploads/documents/".$lobjDocumentos->DocumentoURL)."', '".$lobjDocumentos->IdDocumento."', '".$lobjDocumentos->IdDocumento."'); return false;\" > <i class=\"fa fa-eye\"></i> </a>";
		        }else{
		        	return "<a href=\"".url($lobjDocumentos->DocumentoURL)."\" download> ".$lobjDocumentos->TipoDocumento." <i class=\"fa fa-download\"></i> </a> <a class=\"btn-view-pdf\" onclick=\"ViewPDF('".url($lobjDocumentos->DocumentoURL)."', '".$lobjDocumentos->IdDocumento."', '".$lobjDocumentos->IdDocumento."'); return false;\" >  <i class=\"fa fa-eye\"></i>  </a>";
		        }
	        }else{
	        	return $lobjDocumentos->TipoDocumento;
	        }
        })
        ->editColumn('Accion', function ($lobjDocumentos) {
        	if ($lobjDocumentos->DocumentoURL){
	        	if (strrpos($lobjDocumentos->DocumentoURL, "/")===false){
	            	return "<a class=\"btn btn-white\" onclick=\"ViewPDF('".url("uploads/documents/".$lobjDocumentos->DocumentoURL)."', '".$lobjDocumentos->IdDocumento."', '".$lobjDocumentos->IdDocumento."'); return false;\" > <i class=\"fa fa-eye\"></i> </a>";
		        }else{
		        	return "<a class=\"btn btn-white\" onclick=\"ViewPDF('".url($lobjDocumentos->DocumentoURL)."', '".$lobjDocumentos->IdDocumento."', '".$lobjDocumentos->IdDocumento."'); return false;\" >  <i class=\"fa fa-eye\"></i>  </a>";
		        }
	        }else{
	        	return '';
	        }
        })
        ->editColumn('DocumentoURL', function ($lobjDocumentos) {

	        if ($lobjDocumentos->DocumentoURL){
	        	return "<input type=\"checkbox\" value=\"".$lobjDocumentos->IdDocumento."\" id=\"checkbox_documents\" name=\"checkbox_documents[]\" />";
	        }else{
	        	return "";
	        }

        	/*
	        if (strrpos($lobjDocumentos->DocumentoURL, "/")===false && $lobjDocumentos->IdEstatus!=1){
	        	$larrDocumentoURL = explode("/",$lobjDocumentos->DocumentoURL);
	        	if (count($larrDocumentoURL)){
	        		$lstrNombreDocumento = $larrDocumentoURL[count($larrDocumentoURL)-1];
	        	}else{
	        		$lstrNombreDocumento = $lobjDocumentos->DocumentoURL;
	        	}
            	return "<input type=\"checkbox\" value=\"".url("uploads/documents/".$lobjDocumentos->DocumentoURL)."\" id=\"checkbox_documents\" name=\"checkbox_documents\" data-name=\"".$lstrNombreDocumento."\" />";
			}else if ($lobjDocumentos->IdEstatus!=1){
            	return "<input type=\"checkbox\" value=\"".url($lobjDocumentos->DocumentoURL)."\" id=\"checkbox_documents\" data-name=\"".$lobjDocumentos->DocumentoURL."\" name=\"checkbox_documents\" />";
            }else{
            	return "";
            }
            */
        })
		->make(true);

		//Realizamos parametrizaciones

		//
		return $lobjDataTable;

	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_documentos ") as $column)
        {
			if( $column->Field != 'IdDocumento')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_documentos (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_documentos WHERE IdDocumento IN (".$toCopy.")";
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

	function getComboselecttotal( Request $request)
	{

		if($request->ajax() == true && \Auth::check() == true)
		{
			$param = explode(':',$request->input('filter'));
			$parent = (!is_null($request->input('parent')) ? $request->input('parent') : null);
			$limit = (!is_null($request->input('limit')) ? $request->input('limit') : null);
			$rows = $this->model->getComboselect($param,$limit,$parent);
			$items = array();

			$items[] = array("0","Todos");

			$fields = explode("|",$param[2]);

			foreach($rows as $row)
			{
				$value = "";
				foreach($fields as $item=>$val)
				{
					if($val != "") $value .= $row->{$val}." ";
				}
				$items[] = array($row->{$param['1']} , $value);

			}

			return json_encode($items);
		} else {
			return json_encode(array('OMG'=>" Ops .. Cant access the page !"));
		}
	}

	function postGenerazip( Request $request ){

		$larrDocumentos = $request->checkbox_documents;

		if ($larrDocumentos){
			$lobjDocumentos = \DB::table("tbl_documentos")
			->select("tbl_documentos.IdDocumento", "tbl_documentos.DocumentoURL", "tbl_entidades.Entidad")
			->whereIn("tbl_documentos.IdDocumento",$larrDocumentos)
			->join("tbl_entidades","tbl_documentos.Entidad", "=", "tbl_entidades.IdEntidad")
			->get();
			if ($lobjDocumentos){
				$lstrNombreDocumentoZIP = "documentos_".date('Ymd_His').".zip";
				$lstrNombreDocumentoZIPFull = public_path("uploads/".$lstrNombreDocumentoZIP);
				$lobjZip = \Zipper::make($lstrNombreDocumentoZIPFull);
				foreach ($lobjDocumentos as $larrDocumento) {
					$larrNombreDocumento = explode("/",$larrDocumento->DocumentoURL);
		        	if (count($larrNombreDocumento)>1){
		        		$lstrDirectorioDocumento = "";
		        		$lstrNombreDocumento = $larrNombreDocumento[count($larrNombreDocumento)-1];
		        	}else{
		        		$lstrDirectorioDocumento = public_path("uploads/documents/");
		        		$lstrNombreDocumento = $larrDocumento->DocumentoURL;
		        	}
		        	if ($larrDocumento->DocumentoURL){
			        	if (file_exists(($lstrDirectorioDocumento.$larrDocumento->DocumentoURL))) {
							$lobjZip->folder($larrDocumento->Entidad)->add(($lstrDirectorioDocumento.$larrDocumento->DocumentoURL),$lstrNombreDocumento);
			        	}
		        	}

				}
				$lobjZip->close();

				if (file_exists(public_path("uploads/".$lstrNombreDocumentoZIP))) {
					return response()->json(array(
					'status'=>'success',
					'result'=>$lstrNombreDocumentoZIP,
					'message'=> \Lang::get('core.note_success')
					));
				}else{
					return response()->json(array(
					'status'=>'error',
					'result'=>'',
					'message'=> "No se encontró ningún documento"
					));
				}

			}
		}

		return response()->json(array(
				'status'=>'error',
				'result'=>'',
				'message'=> \Lang::get('core.note_error')
				));

	}

	function postSave( Request $request, $id =0)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_documentos');

			$id = $this->model->insertRow($data , $request->input('IdDocumento'));

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
		$model  = new Buscadordocumental();
		$info = $model::makeInfo('buscadordocumental');

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
				return view('buscadordocumental.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdDocumento' ,
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
			return view('buscadordocumental.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_documentos');
			 $this->model->insertRow($data , $request->input('IdDocumento'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}


}
