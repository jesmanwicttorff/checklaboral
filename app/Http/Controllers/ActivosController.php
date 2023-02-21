<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Activos;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect, PDF;
use App\Models\Documentos;
use DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ActivosController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'activos';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Activos();
		$this->modelview = new  \App\Models\Activovalor();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'activos',
			'pageUrl'			=>  url('activos'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('activos.index',$this->data);
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
			$search = 	$this->buildSearch('maps');
			$filter = $search['param'];
			$this->data['search_map'] = $search['maps'];
		}


		$page = $request->input('page', 1);
		$params = array(
			'page'		=> $page ,
			'limit'		=> (!is_null($request->input('rows')) ? filter_var($request->input('rows'),FILTER_VALIDATE_INT) : $this->info['setting']['perpage'] ) ,
			'sort'                  => 'contrato_id',
			/*'sort'		=> $sort ,*/
			'order'		=> $order,
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		// Get Query
		$results = $this->model->getRows( $params );

		// Build pagination setting
		$page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
		$pagination = new Paginator($results['rows'], $results['total'], $params['limit']);
		$pagination->setPath('activos/data');

		$this->data['param']		= $params;
		$this->data['rowData']		= $results['rows'];
		// Build Pagination
		$this->data['pagination']	= $pagination;
		// Build pager number and append current param GET
		$this->data['pager'] 		= $this->injectPaginate();
		// Row grid Number
		$this->data['i']			= ($page * $params['limit'])- $params['limit'];
		// Grid Configuration
		$this->data['tableGrid'] 	= $this->info['config']['grid'];
		$this->data['tableForm'] 	= $this->info['config']['forms'];
		$this->data['colspan'] 		= \SiteHelpers::viewColSpan($this->info['config']['grid']);
		// Group users permission
		$this->data['access']		= $this->access;
		// Detail from master if any
		$this->data['setting'] 		= $this->info['setting'];

		// Master detail link if any
		$this->data['subgrid']	= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array());
		// Render into template
		return view('activos.table',$this->data);

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

 		$contID = explode("contrato=", $id);
		$row = $this->model->find($id);

		if($row)
		{
			$this->data['row'] 		=  $row;

			 $this->data['rowDatoUni']   =  \DB::table('tbl_activos_detalle')
 			 							    ->select('tbl_activos_data_detalle.Valor')
                                            ->join('tbl_activos_data_detalle', 'tbl_activos_detalle.IdActivoDetalle', '=', 'tbl_activos_data_detalle.IdActivoDetalle')
                                            ->where('IdActivoData',$id)
                                            ->where('Unico', '=', 'SI')
                                            ->get();

                                               $this->data['rowDataCont'] =  \DB::table('tbl_contrato')
			    ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
			    ->select('tbl_contratistas.RUT','tbl_contratistas.RazonSocial','tbl_contrato.cont_numero')
			    ->where('contrato_id', '=', $row['contrato_id'])
			    ->get();

		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_activos_data');
			$this->data['row']['contrato_id']  = $contID[1];

			    $this->data['rowDataCont'] =  \DB::table('tbl_contrato')
			    ->join('tbl_contratistas', 'tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
			    ->select('tbl_contratistas.RUT','tbl_contratistas.RazonSocial','tbl_contrato.cont_numero')
			    ->where('contrato_id', '=', $contID[1])
			    ->get();


		}
		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;

		return view('activos.form',$this->data);
	}

	public function getShowlist( Request $request ) {
		    // Get Query
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

            $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
            $filter .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';

		    $params = array(
		      'page'    => '',
		      'limit'   => '',
		      'sort'    => '' ,
		      'order'   => $order,
		      'params'  => $filter,
		      'global'  => (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		    );
		    // Get Query
		    $results = $this->model->getRows( $params );

		    $larrResult = array();
		    $larrResultTemp = array();
		    $i = 0;

		    foreach ($results['rows'] as $row) {

			//foreach ($results as $row) {
		      //$id = $row->IdActivoData;
				$id = $row->contrato_id;
		      $contrato = $row->contrato_id;

		      $larrResultTemp = array('id'=> ++$i,
		                    'checkbox'=>'<input type="checkbox" class="ids" name="ids[]" value="'.$id.'" /> '
		                    );

		     foreach ($this->info['config']['grid'] as  $index => $field) {
		        if($field['view'] =='1') {
		          $limited = isset($field['limited']) ? $field['limited'] :'';
		          if (\SiteHelpers::filterColumn($limited )){
		            $value = \SiteHelpers::formatRows($row->{$field['field']}, $field , $row);
		            $larrResultTemp[$field['field']] = $value;
		          }
		        }
		      }


		      $larrResultTemp['tablaFila'] = \DB::table('tbl_activos_data')->select('IdActivo','IdActivoData')->where("contrato_id",$contrato)->orderBy("IdActivo","desc")->get();


		        $larrResultTemp['tabla'] = \DB::table('tbl_activos_data')
		                 ->join('tbl_activos', 'tbl_activos_data.IdActivo', '=', 'tbl_activos.IdActivo')
		              ->select('tbl_activos_data.IdActivo','tbl_activos_data.contrato_id','Descripcion')
		             ->groupBy('tbl_activos.IdActivo')
		              ->where("contrato_id",$contrato)->get();

		      $larrResultTemp['tablaControl'] = \DB::table('tbl_activos_data')->select('IdActivoData')->where("contrato_id",$contrato)->orderBy("IdActivoData","asc")->get();

		      $larrResultTemp['tablaHead'] = \DB::table('tbl_activos_detalle')->select('IdActivo','Etiqueta')->where('IdEstatus','=','1')->orderBy('OrdenForm','asc')->get();

		      $larrResultTemp['tablaBody'] = \DB::table('tbl_activos_data_detalle')
		        ->join('tbl_activos_data', 'tbl_activos_data_detalle.IdActivoData', '=', 'tbl_activos_data.IdActivoData')
		        ->select('tbl_activos_data.IdActivoData','tbl_activos_data.IdActivo','Valor')
		          ->orderBy("IdActivoDetalle","asc")
		        ->where("contrato_id",$contrato)->get();

		           foreach ($larrResultTemp['tablaFila'] as  $index => $cuerpo) {
		           	    $larrResultTemp['actions'][$index] = '';
		           	    if($this->access['is_edit'] ==1) {
									$onclick = " onclick=\"confirmar($cuerpo->IdActivoData); return false; \"" ;

								$larrResultTemp['actions'][$index] = ' <a href="'.\URL::to('activos/update/'.$cuerpo->IdActivoData).'" onclick="ajaxViewDetail(\'#activos\',this.href); return false; " class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_edit').'"><i class="fa  fa-edit"></i></a><a href="javascript://ajax" '.$onclick.' class="btn btn-xs btn-white tips" title="Desvincular"><i class="fa  fa-trash-o"></i></a>';
								if($this->access['is_excel'] ==1){
									$larrResultTemp['actions'][$index] .= '<a href="activos/credencialActivo/'.$cuerpo->IdActivoData.'" class="btn btn-xs btn-white tips" title="Credencial"><i class="icon-vcard"></i></a>';
								}
						
						}
		      }

		      $lstrBoton = ' <a href="'.\URL::to('activos/update/contrato='.$row->contrato_id).'" onclick="ajaxViewDetail(\'#activos\',this.href); return false; " class="btn btn-xs btn-white tips" title="'.\Lang::get('core.btn_create').'"><i class="fa fa-plus-circle"></i></a>';

			$larrResultTemp['action'] = $lstrBoton;

		      $larrResult[] = $larrResultTemp;
		    }

		    echo json_encode(array("data"=>$larrResult));
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
			return view('activos.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_activos_data ") as $column)
        {
			if( $column->Field != 'IdActivoData')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_activos_data (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_activos_data WHERE IdActivoData IN (".$toCopy.")";
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

		$limite = $request->counter;
		$IdActivoDetalle = $request->bulk_IdActivoDetalle;
		$Valor = $request->bulk_Valor;
		$foto = $request->foto;
		if(isset($foto)){
			array_push($Valor, $foto);
			$NumItems = count($Valor);
		}

		$EntryBy = \Session::get('uid');
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_activos_data');

			$id = $this->model->insertRow($data , $request->input('IdActivoData'));

			/*levanta solicitudes*/
			$contrato_id = $request->input('contrato_id');
			$idactivo = $request->input('IdActivo');
			$entry_by = \DB::table('tbl_contrato')->where('contrato_id',$contrato_id)->value('entry_by_access');
			$IdContratista = \DB::table('tbl_contrato')->where('contrato_id',$contrato_id)->value('IdContratista');
			$fechaemision = Date('Y-m-01');

			$requisitos = \DB::table('tbl_requisitos')
				->join('tbl_requisitos_detalles','tbl_requisitos.IdRequisito','=','tbl_requisitos_detalles.IdRequisito')
				->select('tbl_requisitos.idrequisito','tbl_requisitos.idtipodocumento','tbl_requisitos.entidad')->where('tbl_requisitos.entidad',10)->where('tbl_requisitos_detalles.IdEntidad',$idactivo)->get();

			foreach ($requisitos as $requisito) {
				$doc = new Documentos;
				$doc->IdRequisito = $requisito->idrequisito;
				$doc->IdTipoDocumento = $requisito->idtipodocumento;
				$doc->Entidad = $requisito->entidad;
				$doc->IdEntidad = $id;
				$doc->IdEstatus = 1;
				$doc->entry_by = $entry_by;
				$doc->entry_by_access = $entry_by;
				$doc->contrato_id = $contrato_id;
				$doc->IdContratista = $IdContratista;
				$doc->FechaEmision = $fechaemision;
				$doc->save();

				$lastid = $doc->IdDocumento;

				\DB::table('tbl_documentos_activos')->insert(['iddocumento'=>$lastid,'idactivodata'=>$id]);
			}

			/*fin levante*/

			$i=0;
			if(count($IdActivoDetalle)>0)
			foreach ($IdActivoDetalle as $key => $value) {

				if(isset($foto)){
					if($NumItems===++$i){
						$data_fotos = \DB::table('tbl_activos_detalles_fotos')->select('IdActivoDetalle','valor_foto')->where('IdActivoDetalle',$IdActivoDetalle[$key])->get();
						if(count($data_fotos)>0){
							\DB::table('tbl_activos_detalles_fotos')->where('IdActivoData',$id)->where('IdActivoDetalle',$IdActivoDetalle[$key])->update(['valor_foto'=>$Valor[$key]]);
						}else{
							if(isset($key) and isset($Valor[$key])){
								\DB::table('tbl_activos_detalles_fotos')->insert(array("IdActivoData"=>$id,
																	 "IdActivoDetalle"=>$IdActivoDetalle[$key],
																	 "valor_foto"=>LOAD_FILE($Valor[$key])));
							}
						}
					}
				}

				$data_detalle = \DB::table('tbl_activos_data_detalle')->select('IdActivoData','IdActivoDetalle','Valor')->where('IdActivoData',$id)->where('IdActivoDetalle',$IdActivoDetalle[$key])->get();
				if(count($data_detalle)>0){
					\DB::table('tbl_activos_data_detalle')->where('IdActivoData',$id)->where('IdActivoDetalle',$IdActivoDetalle[$key])->update(['Valor'=>$Valor[$key], "entry_by"=>$EntryBy]);
				}else{
					if(isset($key) and isset($Valor[$key])){
						\DB::table('tbl_activos_data_detalle')->insert(array("IdActivoData"=>$id,
																	 "IdActivoDetalle"=>$IdActivoDetalle[$key],
																	 "Valor"=>trim($Valor[$key]),
																	 "entry_by"=>$EntryBy));
					}
				}
			}


			return response()->json(array(
				'status'=>'success',
				'message'=> \Lang::get('core.note_success')
			));
		}
		else{

			$rules = $this->validateForm();
			$validator = Validator::make($request->all(), $rules);
			if ($validator->passes()) {
				$data = $this->validatePost('tbl_activos_data');

				$id = $this->model->insertRow($data , $request->input('IdActivoData'));
				$this->detailviewsave( $this->modelview , $request->all() ,$this->info['config']['subform'] , $id) ;
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
			\DB::table('tbl_activos_data_detalle')->whereIn('IdActivoData',$request->input('ids'))->delete();
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

	public function postBorrar( Request $request)
	{

		if(count($request->input('IdActivo')) >=1)
		{
			   $Id = $request->input('IdActivo');
			\DB::table('tbl_activos_data')
				->where('IdActivoData', '=', $Id)
				->update(["contrato_id"=>null]);

			$docs = \DB::table('tbl_documentos_activos')->where('idactivodata',$Id)->get();
			if(count($docs)>0)
			foreach ($docs as $doc) {
				Documentos::where('IdDocumento',$doc->iddocumento)->delete();
			}
			\DB::table('tbl_documentos_activos')->where('idactivodata',$Id)->delete();

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

	public static function display( )
	{
		$mode  = isset($_GET['view']) ? 'view' : 'default' ;
		$model  = new Activos();
		$info = $model::makeInfo('activos');

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
				return view('activos.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdActivoData' ,
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
			return view('activos.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_activos_data');
			 $this->model->insertRow($data , $request->input('IdActivoData'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}
	public function getPrint(Request $request){

		        $id = $request->input("id");

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

		        $params = array(
		        	'params'    => " AND IdActivoData = ".$id,
		            'global'    => (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		        );
		        $row = $this->model->getRows( $params );

		        if(isset($row['rows'][0]))
		        {

		            $this->data['row']      =  $row['rows'][0];

		            $this->data['rowActivo']   =  \DB::table('tbl_activos_data')
 			 ->select('tbl_activos.Descripcion','tbl_contrato.cont_nombre','tbl_contrato.cont_numero')
                                       ->join('tbl_contrato', 'tbl_activos_data.contrato_id', '=', 'tbl_contrato.contrato_id')
                                       ->join('tbl_activos', 'tbl_activos_data.IdActivo', '=', 'tbl_activos.IdActivo')
                                        ->where('IdActivoData',$id)->get();

                                        $this->data['rowActivoInfo']   =  \DB::table('tbl_activos_detalle')
 			 ->select('tbl_activos_detalle.Etiqueta','tbl_activos_data_detalle.Valor')
                                       ->join('tbl_activos_data_detalle', 'tbl_activos_detalle.IdActivoDetalle', '=', 'tbl_activos_data_detalle.IdActivoDetalle')
                                        ->where('IdActivoData',$id)->get();

                                        $this->data['rowDatoUni']   =  \DB::table('tbl_activos_detalle')
 			 ->select('tbl_activos_detalle.IdActivo','tbl_activos_data_detalle.IdActivoData','tbl_activos_data_detalle.Valor')
                                       ->join('tbl_activos_data_detalle', 'tbl_activos_detalle.IdActivoDetalle', '=', 'tbl_activos_data_detalle.IdActivoDetalle')
                                        ->where('IdActivoData',$id)
                                        ->where('Unico', '=', 'SI')
                                        ->get();

		        } else {
		            $this->data['row']      = $this->model->getColumnTable('tbl_activos_data');

		        }
		        $this->data['setting']      = $this->info['setting'];
		        $this->data['fields']       =  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		        $this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		        $this->data['id'] = $id;

		        $lstrHtmlView =  view('activos.print',$this->data)->render();
		        $lstrPDF = PDF::load($lstrHtmlView)->output();

		        header('Set-Cookie: fileDownload=true; path=/');
		        header('Cache-Control: max-age=60, must-revalidate');
		        header("Content-type: application/pdf");
		            header('Content-Disposition: attachment; filename="Activo_'.$id.'_'.date('Ymd_His').'.pdf"');
		        echo $lstrPDF;

    }

	public  function postDataactivo(Request $request)
	{
		$Idactivo = $request->activo;

		$IdActivoData =  $request->IdActivoData;

 		$rowTipoActivo =  \DB::table('tbl_activos_detalle')->where('IdActivo',$Idactivo)
 		                       ->orderBy("tbl_activos_detalle.OrdenForm","asc")
 		                       ->orderBy("tbl_activos_detalle.IdActivoDetalle","asc")
 		                       ->get();

 		$rowValores = \DB::table('tbl_activos_data_detalle')->where('IdActivoData',$IdActivoData)->get();

 		 $rowListaTipoActivo =  \DB::table('tbl_activos_detalle')
 		 ->select('tbl_activos_detalles_listas.IdActivoDetalle','tbl_activos_detalles_listas.Etiqueta','tbl_activos_detalles_listas.Valor')
                    ->join('tbl_activos_detalles_listas', 'tbl_activos_detalle.IdActivoDetalle', '=', 'tbl_activos_detalles_listas.IdActivoDetalle')
                    ->where('IdActivo',$Idactivo)->get();

		return response()->json(array(
			'status'=>'sucess',
			'valores'=>$rowTipoActivo,
			'lista'=>$rowListaTipoActivo,
			'info'=>$rowValores,
			'message'=>\Lang::get('core.note_sucess')
			));
	}

	public  function postDatacomprueba(Request $request)
	{
		$Idactivo = $request->activo;
		$Valor = $request->valor;

 		$rowTipoActivo =  \DB::table('tbl_activos_data_detalle')
 		->where('Valor', '=', $Valor)
 		->get();

 		if (count($rowTipoActivo)>0){
 			$IdActivo = $rowTipoActivo[0]->IdActivoData;

 			$rowExiste =  \DB::table('tbl_activos_data')
	 		->where('IdActivoData', '=', $IdActivo)
	 		->get();

	 		if ($rowExiste[0]->contrato_id ==null){
	 			$rowdatos =  \DB::table('tbl_activos_data_detalle')
		 		->where('IdActivoData', '=', $IdActivo)
		 		->get();
		 		$rowTipoActivo ='';
	 		}
	 		else{
	 			$rowdatos = 0;
	 		}

 		}
 		else
 			$rowdatos = 0;

		return response()->json(array(
			'status'=>'sucess',
			'valores'=>$rowTipoActivo,
			'datos'=>$rowdatos,
			'message'=>\Lang::get('core.note_sucess')
			));

	}

	public function postDatatipoactivo(Request $request){

		$id = $request->id;

		$datos = \DB::table('tbl_activos')->select('IdActivo','Descripcion')->where('IdEstatus', '=', 1)->get();

		return response()->json(array(
			'status'=>'sucess',
			'valores'=>$datos,
			'message'=>\Lang::get('core.note_sucess')
			));
	}

	public function getCredencialActivo($id){

		//Obtengo el idactivo el cual me ayudar a encontrar cual es el activo del detalle
		$idActivo = DB::TABLE('tbl_activos_data')
						->SELECT('tbl_activos_data.IdActivo')
						->WHERE('tbl_activos_data.IdActivoData',$id)
						->first();
		//con el activo detalle puedo ver cual es el activo detalle que tiene la patente
		$idActivoDetalle = DB::table('tbl_activos_detalle')
								->select('tbl_activos_detalle.IdActivoDetalle')
								->where('tbl_activos_detalle.IdActivo',$idActivo->IdActivo )
								->whereIn('tbl_activos_detalle.Etiqueta',['Patente', 'Patente o Núm. Serie'])
								->first();

		$datosCredencial = DB::TABLE('tbl_activos_data')
							->SELECT('tbl_activos_data_detalle.Valor','tbl_activos.Descripcion')
							->JOIN('tbl_activos_data_detalle','tbl_activos_data.IdActivoData','=','tbl_activos_data_detalle.IdActivoData' )
							->JOIN('tbl_activos','tbl_activos_data.IdActivo','=','tbl_activos.IdActivo')
							->WHERE('tbl_activos_data_detalle.IdActivoDetalle',$idActivoDetalle->IdActivoDetalle)
							->where('tbl_activos_data.IdActivoData',$id)
							->FIRST();

		   $qr= QrCode::size(170)->generate($datosCredencial->Valor);	
		   $pdf = PDF::loadView('activos.credencialActivo',array('patente' => $datosCredencial->Valor, 'qr' => $qr, 'activo' => $datosCredencial->Descripcion))
						
		   				->setOption('page-width', '215.9')
					 ->setOption('page-height', '139.7')
					 	->setOption('margin-top',30)
						->setOption('margin-bottom',5)
						->setOption('margin-left',5)
						->setOption('margin-right',5);
						
				  //muestro el pdf por pantalla
				return $pdf->download('Credencial_'.$datosCredencial->Descripcion.'_'.$datosCredencial->Valor.'.pdf');
				   
  				//return view('activos.credencialActivo',array('patente' => $datosCredencial->Valor, 'qr' => $qr, 'activo' => $datosCredencial->Descripcion));
  
	}


}
