<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Encuestas;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect;
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\FontMetrics;

class EncuestasController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'encuestas';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();
		$this->model = new Encuestas();
		$this->modelview = new  \App\Models\Encuestasdetalle();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'			=> 	$this->info['title'],
			'pageNote'			=>  $this->info['note'],
			'pageModule'		=> 'encuestas',
			'pageUrl'			=>  url('encuestas'),
			'return' 			=> 	self::returnUrl()
		);

	}

	public function getIndex()
	{
		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');

		$this->data['access']		= $this->access;
		return view('encuestas.index',$this->data);
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
			'sort'		=> $sort ,
			'order'		=> $order,
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		// Get Query
		$results = $this->model->getRows( $params );

		// Build pagination setting
		$page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;
		$pagination = new Paginator($results['rows'], $results['total'], $params['limit']);
		$pagination->setPath('encuestas/data');

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
		return view('encuestas.table',$this->data);

	}


	function getUpdate(Request $request, $id = null)
	{

		$pos = strpos($id, '=');

		if ($pos === false){
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
		}
		else{
			$valor = explode('=', $id);
			$doc = $valor[1];
			$consulta =  \DB::table('tbl_encuestas')->where("IdDocumento","=",$doc)->get();
			if (count($consulta)>0)
			$id = $consulta[0]->IdEncuesta;

		}
		$row = $this->model->find($id);
		if($row)
		{
			$this->data['row'] 		=  $row;
			$this->data['respuestas']=  \DB::table('tbl_encuestas_detalle')
																		->join('tbl_preguntas','tbl_encuestas_detalle.IdPregunta', '=', 'tbl_preguntas.IdPregunta')
																		->where("IdEncuesta","=",$id)->get();

			$this->data['Encategorias']=  \DB::table('tbl_encuestas_categoria')
																		->join('tbl_preguncategorias','tbl_encuestas_categoria.IdCategoria', '=', 'tbl_preguncategorias.IdCategoria')
																		->where("IdEncuesta","=",$id)->get();


			$this->data['usuario']=  \DB::table('tb_users')->where("id","=",$row['entry_by'])->get();

		} else {
			$this->data['row'] 		= $this->model->getColumnTable('tbl_encuestas');
			$usuraio = \Session::get('uid');
			$this->data['usuario']=  \DB::table('tb_users')->where("id","=",$usuraio)->get();

		}


		if (isset($doc)){
				$entidad = \DB::table('tbl_documentos')
					 ->where("tbl_documentos.IdDocumento","=",$doc)
					 ->get();
						$this->data['entidad']=$entidad[0]->Entidad;
					 if ($entidad[0]->Entidad==1){

						 $this->data['info']= \DB::table('tbl_documentos')
			 													->join('tbl_encuestas_master','tbl_documentos.IdTipoDocumento', '=', 'tbl_encuestas_master.IdTipoDocumento')
			 													->join('tbl_contratistas','tbl_documentos.IdEntidad', '=', 'tbl_contratistas.IdContratista')
			 													->where("tbl_documentos.IdDocumento","=",$doc)
			 													 ->get();

					 }
					else if ($entidad[0]->Entidad==2) {

						$this->data['info']= \DB::table('tbl_documentos')
																->join('tbl_encuestas_master','tbl_documentos.IdTipoDocumento', '=', 'tbl_encuestas_master.IdTipoDocumento')
																->join('tbl_contrato','tbl_documentos.contrato_id', '=', 'tbl_contrato.contrato_id')
																->join('tbl_contratistas','tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
																->where("tbl_documentos.IdDocumento","=",$doc)
																 ->get();

						$this->data['admin']=  \DB::table('tb_users')->where("id","=",$this->data['info'][0]->admin_id)->get();

					}
					$this->data['categorias']=  \DB::table('tbl_encuestas_master_categoria')
					->join('tbl_preguncategorias','tbl_encuestas_master_categoria.IdCategoria', '=', 'tbl_preguncategorias.IdCategoria')
					->where("IdEncuestaMaster","=",$this->data['info'][0]->IdEncuestaMaster)
					->get();

					$this->data['preguntas']=  \DB::table('tbl_encuestas_master_detalle')
					->join('tbl_preguntas','tbl_encuestas_master_detalle.IdPregunta', '=', 'tbl_preguntas.IdPregunta')
					->where("IdEncuestaMaster","=",$this->data['info'][0]->IdEncuestaMaster)
					->orderby('tbl_preguntas.orden','asc')
					->get();

					$this->data['calificaciones']=  \DB::table('tbl_encuestas_master_calificacion')
					->join('tbl_preguncalificacion','tbl_encuestas_master_calificacion.IdCalificacion', '=', 'tbl_preguncalificacion.IdCalificacion')
					->where("IdEncuestaMaster","=",$this->data['info'][0]->IdEncuestaMaster)
					->get();

                    $this->data['titulo']=  \DB::table('tbl_encuestas_master')
                    ->select("tbl_tipos_documentos.Descripcion", "tbl_tipos_documentos.TextoExplicativo")
                    ->join('tbl_tipos_documentos','tbl_encuestas_master.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
                     ->where("IdEncuestaMaster","=",$this->data['info'][0]->IdEncuestaMaster)
                     ->first();

		}


		$this->data['setting'] 		= $this->info['setting'];
		$this->data['fields'] 		=  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subform'] = $this->detailview($this->modelview ,  $this->info['config']['subform'] ,$id );
		$this->data['id'] = $id;

		return view('encuestas.form',$this->data);
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
			return view('encuestas.view',$this->data);

		} else {

			return response()->json(array(
				'status'=>'error',
				'message'=> \Lang::get('core.note_error')
			));
		}
	}


	function postCopy( Request $request)
	{

	    foreach(\DB::select("SHOW COLUMNS FROM tbl_encuestas ") as $column)
        {
			if( $column->Field != 'IdEncuesta')
				$columns[] = $column->Field;
        }
		if(count($request->input('ids')) >=1)
		{

			$toCopy = implode(",",$request->input('ids'));


			$sql = "INSERT INTO tbl_encuestas (".implode(",", $columns).") ";
			$sql .= " SELECT ".implode(",", $columns)." FROM tbl_encuestas WHERE IdEncuesta IN (".$toCopy.")";
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

	public function getPrint(Request $request, $id){

		        //$id = $request->input("id");

						$row = $this->model->find($id);
						$this->data['row'] 		=  $row;

						$this->data['documentos']=  \DB::table('tbl_encuestas')
						                            ->select("tbl_documentos.entidad")
				                                    ->join('tbl_documentos','tbl_encuestas.IdDocumento', '=', 'tbl_documentos.IdDocumento')
				                                    ->where("tbl_encuestas.IdEncuesta","=",$id)->first();

						$this->data['respuestas']=  \DB::table('tbl_encuestas_detalle')
				                                  ->join('tbl_preguntas','tbl_encuestas_detalle.IdPregunta', '=', 'tbl_preguntas.IdPregunta')
				                                  ->where("IdEncuesta","=",$id)->get();

				    $this->data['Encategorias']=  \DB::table('tbl_encuestas_categoria')
				                                  ->join('tbl_preguncategorias','tbl_encuestas_categoria.IdCategoria', '=', 'tbl_preguncategorias.IdCategoria')
				                                  ->where("IdEncuesta","=",$id)->get();

						$this->data['categorias']=  \DB::table('tbl_encuestas_master_categoria')
						->join('tbl_preguncategorias','tbl_encuestas_master_categoria.IdCategoria', '=', 'tbl_preguncategorias.IdCategoria')
						->where("IdEncuestaMaster","=",$row['IdEncuestaMaster'])
						->get();

						$this->data['preguntas']=  \DB::table('tbl_encuestas_master_detalle')
						->join('tbl_preguntas','tbl_encuestas_master_detalle.IdPregunta', '=', 'tbl_preguntas.IdPregunta')
						->where("IdEncuestaMaster","=",$row['IdEncuestaMaster'])
						->get();

                        $this->data['titulo']=  \DB::table('tbl_encuestas_master')
                            ->select("tbl_tipos_documentos.Descripcion", "tbl_tipos_documentos.TextoExplicativo")
                            ->join('tbl_tipos_documentos','tbl_encuestas_master.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
                            ->where("IdEncuestaMaster","=",$row['IdEncuestaMaster'])
                            ->first();


				    $this->data['usuario']=  \DB::table('tb_users')->where("id","=",$row['entry_by'])->get();

						if ($this->data['documentos']->entidad==1){


			          $this->data['info']= \DB::table('tbl_encuestas')
											->select('tbl_contratistas.RUT','tbl_contratistas.RazonSocial')
			                                ->join('tbl_documentos','tbl_encuestas.IdDocumento', '=', 'tbl_documentos.IdDocumento')
			                                ->join('tbl_contratistas','tbl_documentos.IdEntidad', '=', 'tbl_contratistas.IdContratista')
			                                ->where("tbl_encuestas.IdEncuesta","=",$id)
			                                ->get();
			        }
			       else if ($this->data['documentos']->entidad==2) {

			         $this->data['info']= \DB::table('tbl_encuestas')
							 			->select('tbl_contratistas.RUT','tbl_contratistas.RazonSocial','tbl_contrato.admin_id','tbl_contrato.contrato_id','tbl_contrato.cont_nombre','tbl_contrato.cont_numero')
			                             ->join('tbl_documentos','tbl_encuestas.IdDocumento', '=', 'tbl_documentos.IdDocumento')
			                             ->join('tbl_contrato','tbl_documentos.contrato_id', '=', 'tbl_contrato.contrato_id')
			                             ->join('tbl_contratistas','tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
			                             ->where("tbl_encuestas.IdEncuesta","=",$id)
			                              ->get();

			         $this->data['admin']=  \DB::table('tb_users')->where("id","=",$this->data['info'][0]->admin_id)->get();

						}



		        $this->data['setting']      = $this->info['setting'];
		        $this->data['fields']       =  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		        $this->data['id'] = $id;

		        $lstrHtmlView =  view('encuestas.print',$this->data)->render();
		        $options = new Options();
				$options->set('isHtml5ParserEnabled', true);
				$options->set('isPhpEnabled', true);
				$options->set('isRemoteEnabled', true);
				$options->set('isJavascriptEnabled', true);
				$options->set('defaultFont', 'arial');
				$options->set('defaultPaperSize','letter');

		        $dompdf = new Dompdf($options);
				$dompdf->loadHtml($lstrHtmlView);
				$dompdf->render();
        		$lstrHtmlView = $dompdf->output();

		        header('Set-Cookie: fileDownload=true; path=/');
		        header('Cache-Control: max-age=60, must-revalidate');
		        header("Content-type: application/pdf");
		            header('Content-Disposition: attachment; filename="Encuesta_'.$id.'_'.date('Ymd_His').'.pdf"');
		        echo $lstrHtmlView;

    }

    function GeneraPDF( $pintIdEncuesta){

    	$id = $pintIdEncuesta;

						$row = $this->model->find($id);
						$this->data['row'] 		=  $row;

						$this->data['documentos']=  \DB::table('tbl_encuestas')
						                            ->select("tbl_documentos.entidad")
				                                    ->join('tbl_documentos','tbl_encuestas.IdDocumento', '=', 'tbl_documentos.IdDocumento')
				                                    ->where("tbl_encuestas.IdEncuesta","=",$id)->first();

						$this->data['respuestas']=  \DB::table('tbl_encuestas_detalle')
				                                  ->join('tbl_preguntas','tbl_encuestas_detalle.IdPregunta', '=', 'tbl_preguntas.IdPregunta')
				                                  ->where("IdEncuesta","=",$id)->get();

				    $this->data['Encategorias']=  \DB::table('tbl_encuestas_categoria')
				                                  ->join('tbl_preguncategorias','tbl_encuestas_categoria.IdCategoria', '=', 'tbl_preguncategorias.IdCategoria')
				                                  ->where("IdEncuesta","=",$id)->get();

						$this->data['categorias']=  \DB::table('tbl_encuestas_master_categoria')
						->join('tbl_preguncategorias','tbl_encuestas_master_categoria.IdCategoria', '=', 'tbl_preguncategorias.IdCategoria')
						->where("IdEncuestaMaster","=",$row['IdEncuestaMaster'])
						->get();

						$this->data['preguntas']=  \DB::table('tbl_encuestas_master_detalle')
						->join('tbl_preguntas','tbl_encuestas_master_detalle.IdPregunta', '=', 'tbl_preguntas.IdPregunta')
						->where("IdEncuestaMaster","=",$row['IdEncuestaMaster'])
						->get();

                        $this->data['titulo']=  \DB::table('tbl_encuestas_master')
                            ->select("tbl_tipos_documentos.Descripcion", "tbl_tipos_documentos.TextoExplicativo")
                            ->join('tbl_tipos_documentos','tbl_encuestas_master.IdTipoDocumento', '=', 'tbl_tipos_documentos.IdTipoDocumento')
                            ->where("IdEncuestaMaster","=",$row['IdEncuestaMaster'])
                            ->first();


				    $this->data['usuario']=  \DB::table('tb_users')->where("id","=",$row['entry_by'])->get();

						if ($this->data['documentos']->entidad==1){


			          $this->data['info']= \DB::table('tbl_encuestas')
											->select('tbl_contratistas.RUT','tbl_contratistas.RazonSocial')
			                                ->join('tbl_documentos','tbl_encuestas.IdDocumento', '=', 'tbl_documentos.IdDocumento')
			                                ->join('tbl_contratistas','tbl_documentos.IdEntidad', '=', 'tbl_contratistas.IdContratista')
			                                ->where("tbl_encuestas.IdEncuesta","=",$id)
			                                ->get();
			        }
			       else if ($this->data['documentos']->entidad==2) {

			         $this->data['info']= \DB::table('tbl_encuestas')
							 			->select('tbl_contratistas.RUT','tbl_contratistas.RazonSocial','tbl_contrato.admin_id','tbl_contrato.contrato_id','tbl_contrato.cont_nombre','tbl_contrato.cont_numero')
			                             ->join('tbl_documentos','tbl_encuestas.IdDocumento', '=', 'tbl_documentos.IdDocumento')
			                             ->join('tbl_contrato','tbl_documentos.contrato_id', '=', 'tbl_contrato.contrato_id')
			                             ->join('tbl_contratistas','tbl_contrato.IdContratista', '=', 'tbl_contratistas.IdContratista')
			                             ->where("tbl_encuestas.IdEncuesta","=",$id)
			                              ->get();

			         $this->data['admin']=  \DB::table('tb_users')->where("id","=",$this->data['info'][0]->admin_id)->get();

						}



		        $this->data['setting']      = $this->info['setting'];
		        $this->data['fields']       =  \AjaxHelpers::fieldLang($this->info['config']['forms']);
		        $this->data['id'] = $id;

		        $lstrHtmlView =  view('encuestas.print',$this->data)->render();

		        $lstrDirectory = public_path('uploads/documents');

	    		$lstrFileName = 'Encuesta_';
	    		$lstrFileExtension = 'pdf';
	    		$lintIdRand = rand(1000, 100000000);
	    		$lstrFullFileName = $lstrFileName.strtotime(date('Y-m-d H:i:s')) . '-' . $lintIdRand . '.'.$lstrFileExtension;

	    		$options = new Options();
	    		$options->set('isHtml5ParserEnabled', true);
	    		$options->set('isPhpEnabled', true);
	    		$options->set('isRemoteEnabled', true);
	    		$options->set('isJavascriptEnabled', true);
	    		//$options->set('defaultFont', 'arial');
	    		$dompdf = new Dompdf($options);
				$dompdf->loadHtml($lstrHtmlView);
				$dompdf->render();
		        $lobjDocumentoPDF = $dompdf->output();
		        //$dompdf->download();

		        if (!file_put_contents($lstrDirectory.'/'.$lstrFullFileName, $lobjDocumentoPDF)) {
		        		$lstrFullFileName = "";
		        };

		        return $lstrFullFileName;

    }

	function postSave( Request $request, $id =0)
	{

		 	$Documento = $request->IdDocumento;
		 	$limite = $request->counter;
		 	$IdPregunta = $request->bulk_IdPregunta;
		 	$Puntaje = 1; //$request->bulk_Puntaje;
		 	$Comentario = $request->bulk_Comentario;
		 	$Cal = $request->bulk_Calificacion;

			$Idcategoria = $request->CIdCategoria;
			$Evaluacion = $request->CEvaluacion;
			$Calificacion = $request->CCalificacion;
			$i=0;
			do{
					$a="asd".$i;
					$valor[$i] = $request->$a;
					$i++;
			}while($i<count($limite));

			$rules = $this->validateForm();
			$validator = Validator::make($request->all(), $rules);
			if ($validator->passes()) {
				$data = $this->validatePost('tbl_encuestas');
				$data['entry_by'] = \Session::get('uid');
				$data['createdOn'] = date('Y-m-d H:i:s');
				$id = $this->model->insertRow($data , $request->input('IdEncuesta'));
				//$this->detailviewsave( $this->modelview , $request->all() ,$this->info['config']['subform'] , $id) ;
			//Guardar Respuestas
			$i=0;
			foreach ($limite as $key => $value) {
				\DB::table('tbl_encuestas_detalle')->insert(array("IdEncuesta"=>$id,
																															 "IdPregunta"=>$IdPregunta[$key],
																															 "Puntaje"=>$Puntaje[$key],
																															 "Comentario"=>$Comentario[$key],
																															 "Calificacion"=>$valor[$i]));
				$i++;
			}
			/*
			\DB::table('tbl_encuestas_detalle')->insert(array("IdEncuesta"=>$id,
																														 "IdPregunta"=>2,
																														 "Puntaje"=>1,
																														 "Comentario"=>'',
																														 "Calificacion"=>$dos));
			*/

			//Guardar Valores por categorias
			/*
				foreach ($Valor as $index => $categoria) {
					\DB::table('tbl_encuestas_categoria')->insert(array("IdEncuesta"=>$id,
																																 "IdCategoria"=>$Idcategoria[$index],
																																 "Evaluacion"=>$Evaluacion[$index],
																																 "Calificacion"=>$Calificacion[$index]));

				}
				*/

				//$lstrNombreDocumento = self::GeneraPDF($id);
				$lstrNombreDocumento='';
				\DB::table('tbl_documentos')->where('IdDocumento', $Documento)->update(['IdEstatus' => 2, 'DocumentoURL'=> $lstrNombreDocumento]);

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
			\DB::table('tbl_encuestas_detalle')->whereIn('IdEncuesta',$request->input('ids'))->delete();
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
		$model  = new Encuestas();
		$info = $model::makeInfo('encuestas');

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
				return view('encuestas.public.view',$data);
			}

		} else {

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$params = array(
				'page'		=> $page ,
				'limit'		=>  (isset($_GET['rows']) ? filter_var($_GET['rows'],FILTER_VALIDATE_INT) : 10 ) ,
				'sort'		=> 'IdEncuesta' ,
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
			return view('encuestas.public.index',$data);
		}


	}

	function postSavepublic( Request $request)
	{

		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_encuestas');
			 $this->model->insertRow($data , $request->input('IdEncuesta'));
			return  Redirect::back()->with('messagetext','<p class="alert alert-success">'.\Lang::get('core.note_success').'</p>')->with('msgstatus','success');
		} else {

			return  Redirect::back()->with('messagetext','<p class="alert alert-danger">'.\Lang::get('core.note_error').'</p>')->with('msgstatus','error')
			->withErrors($validator)->withInput();

		}

	}


}
