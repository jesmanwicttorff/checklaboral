<?php namespace App\Http\Controllers\checklaboral;

use App\Http\Controllers\controller;
use App\Library\MyCheckLaboral;
use App\Models\checklaboral\Reportefaena;
use App\Models\Centros;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;


class ReportefaenaController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'reportefaena';
	static $per_page	= '10';

	public function __construct()
	{
		parent::__construct();

		$this->model = new Reportefaena();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'reportefaena',
			'return'	=> self::returnUrl()

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

		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    	$lintIdUser = \Session::get('uid');

    	$lstrFechaFinal = \DB::table('tbl_contrato_maestro')->select('periodo')->max('periodo');

    	$lobjFiltro = \MySourcing::getFiltroUsuario(2,1);

		$larrParametros = $request->input('parametros');
		$lobjConsulta = \MySourcing::ConvierteConsultaFiltro($larrParametros);

		$lobjDatos = $lobjConsulta->select(\DB::raw('count(distinct(tbl_contrato_maestro.contrato_id)) as Contratos'),
			                              \DB::raw('count(distinct(tbl_contrato_maestro.idcontratista)) as Contratistas'),
			                          	  \DB::raw('count(distinct(tbl_personas_maestro.IdPersona)) as Dotacion'),
			                          	  \DB::raw('sum(tbl_contrato_maestro.costo_laboral) as CostoLaboral'),
			                          	  \DB::raw('sum(tbl_contrato_maestro.pasivo_laboral) as PasivoLaboral'),
																		\DB::raw('sum(tbl_contrato_maestro.porcentaje_ol) as PorcentajeOl'),
																		\DB::raw('sum(tbl_contrato_maestro.porcentaje_op) as PorcentajeOp')
																		)
						->join('tbl_contrato_maestro','tbl_contrato_maestro.contrato_id','=','tbl_contrato.contrato_id')
						->leftjoin('tbl_personas_maestro','tbl_personas_maestro.contrato_id','=','tbl_contrato_maestro.contrato_id')
						->leftjoin('tbl_personas','tbl_personas.idpersona','=','tbl_personas_maestro.idpersona')
		                ->where(function ($query) use ($lobjFiltro) {
		                		$query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos'])
		                		      ->orwhereIn("tbl_contrato.IdContratista",$lobjFiltro['contratistas']);
		                		})
		                ->first();
		$this->data = $lobjDatos;

		$lobjConsulta = \MySourcing::ConvierteConsultaFiltro($larrParametros);
		$lobjDatosGenero = $lobjConsulta->select(\DB::raw('sum(case when tbl_personas.sexo = 1 then 1 else 0 end) as Masculino'),
			                               \DB::raw('sum(case when tbl_personas.sexo = 2 then 1 else 0 end) as Femenino'),
			                          	   \DB::raw('sum(case when tbl_personas.id_nac = 22 then 1 else 0 end) as Chilena'),
										   \DB::raw('sum(case when tbl_personas.id_nac != 22 then 1 else 0 end) as Extranjero'))
						->join('tbl_personas_maestro','tbl_personas_maestro.contrato_id','=','tbl_contrato.contrato_id')
						->leftjoin('tbl_personas','tbl_personas.idpersona','=','tbl_personas_maestro.idpersona')
		                ->where(function ($query) use ($lobjFiltro) {
		                		$query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos'])
		                		      ->orwhereIn("tbl_contrato.IdContratista",$lobjFiltro['contratistas']);
		                		})
		                ->first();
		$this->data['Genero'] = array( 'Masculino'=>$lobjDatosGenero->Masculino, 'Femenino' => $lobjDatosGenero->Femenino );
		$this->data['Nacionalidad'] = array( 'Chilena'=>$lobjDatosGenero->Chilena, 'Extranjero' => $lobjDatosGenero->Extranjero );

		$lobjConsulta = \MySourcing::ConvierteConsultaFiltro($larrParametros);
		$lobjListadoContrato = $lobjConsulta->select('tbl_centro.IdCentro as IdFaena', 'tbl_centro.Descripcion as Faena')->distinct()
						->join('tbl_contrato_maestro','tbl_contrato_maestro.contrato_id','=','tbl_contrato.contrato_id')
						->join('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_contrato_maestro.contrato_id')
						->join('tbl_centro',function($tabla){
								$tabla->on('tbl_contratos_centros.IdCentro', '=', 'tbl_centro.IdCentro')
								      ->where('tbl_contratos_centros.IdTipoCentro', '=', 1);
						})
		                ->where(function ($query) use ($lobjFiltro) {
		                		$query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos'])
		                		      ->orwhereIn("tbl_contrato.IdContratista",$lobjFiltro['contratistas']);
		                		})
		                ->orderBy('tbl_centro.Descripcion','asc')
		                ->get();
    	$this->data['ListadoContratos'] = $lobjListadoContrato;

    	$lobjConsulta = \MySourcing::ConvierteConsultaFiltro($larrParametros);
		$lobjDatosGrafico = $lobjConsulta->select('dim_tiempo.NMes3L',
			                               \DB::raw('sum(tbl_contrato_maestro.documentacion)/sum(tbl_contrato_maestro.dotacion) as documentacion'))
						->join('tbl_contrato_maestro','tbl_contrato.contrato_id','=','tbl_contrato_maestro.contrato_id')
						->join('dim_tiempo','dim_tiempo.fecha','=','tbl_contrato_maestro.periodo')
		                ->where(function ($query) use ($lobjFiltro) {
		                		$query->whereIn("tbl_contrato.contrato_id",$lobjFiltro['contratos'])
		                		      ->orwhereIn("tbl_contrato.IdContratista",$lobjFiltro['contratistas']);
		                		})
		                ->groupBy('dim_tiempo.NMes3L')
		                ->get();
		$lobjDatosGrafico = collect($lobjDatosGrafico)->transform(function($larrValue, $lintKey){
			return [$larrValue->NMes3L, floatval($larrValue->documentacion)];
	                           })->toArray();
		$this->data['LineaTiempo'] = $lobjDatosGrafico;

		if ($lstrFechaFinal){
    		$lobjLinea = \DB::table('dim_tiempo')
    		              ->select('dim_tiempo.NMes3L')
    		              ->whereraw('fecha <= \''.$lstrFechaFinal.'\'')
    		              ->whereraw('fecha >= DATE_SUB(\''.$lstrFechaFinal.'\', INTERVAL 6 MONTH)')
    		              ->where('dia',1)
    		              ->groupBy('dim_tiempo.anio', 'dim_tiempo.mes', 'dim_tiempo.NMes3L')
    		              ->orderBy('dim_tiempo.anio','asc')
    		              ->orderBy('dim_tiempo.mes','asc')
    		              ->pluck('dim_tiempo.NMes3L');
    		$lobjFechaFinal = '';
    	}

		$this->data['LineaTiempoEtiquetas'] = $lobjLinea;

		return view('checklaboral.reportefaena.index',$this->data);
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

		$this->data['access']		= $this->access;
		return view('checklaboral.reportefaena.form',$this->data);
	}

	public function getShow(Request $request, $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

		$larrParametros = $request->input('parametros');

		//Vamos sumando más indicadores
		$lobjCheckLaboral = new MyCheckLaboral();
		$this->data = $lobjCheckLaboral->LoadData($larrParametros,$id);

		$lobjCentros = Centros::find($id);
		if ($lobjCentros){
			$this->data['Faena'] = $lobjCentros->Descripcion;
		}else{
			$this->data['Faena'] = "";
		}

		$this->data['id'] = $id;

		return view('checklaboral.reportefaena.index',$this->data);
	}

	function postSave( Request $request)
	{


	}

	public function postDelete( Request $request)
	{

		if($this->access['is_remove'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

	}


}
