<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Contratos;
use App\Models\Centros;
use App\Models\Personasmaestro;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use DB;
use App\Models\checklaboral\Contratomaestro;

class DashboardcontratosController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'Dashboardcontratos';
	static $per_page	= '10';

	public function __construct()
	{

		parent::__construct();

		$this->model = new Contratos();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'dashboardcontratos',
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

		$periodo = Contratomaestro::max('periodo');

		$contrato_vigente = Contratos::where('cont_estado','!=',2)->count();
		$contrato_personas = Contratos::join('tbl_contratos_personas','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')->where('tbl_contrato.cont_estado','!=',2)->count();
		$contrato_pcf = DB::table('tbl_personas_maestro')->select('id')->where('estatus','Finiquitado')->where('periodo',$periodo)->count();

		$clasificacion = DB::select(DB::raw("SELECT tg.nombre_gasto AS tipo, tbl_contrato_extension.nombre, COUNT(tbl_contrato_extension.id_extension) AS cuenta
			from `tbl_contrato`
			inner join `tbl_contratos_personas` on `tbl_contrato`.`contrato_id` = `tbl_contratos_personas`.`contrato_id`
			inner join `tbl_contrato_extension` on `tbl_contrato_extension`.`id_extension` = `tbl_contrato`.`id_extension`
			INNER JOIN tbl_contrato_tipogasto tg ON tg.id_tipogasto = tbl_contrato.id_tipogasto
			group by `tbl_contrato_extension`.`nombre`" ));

		$listadocentros = DB::table('tbl_centro')
				->join('tbl_contratos_centros','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
				->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
				->where('tbl_contrato.cont_estado',1)
				->select('tbl_centro.IdCentro','tbl_centro.Descripcion')
				->distinct('tbl_centro.IdCentro')
				->get();

		$cantidad_faenas = count($listadocentros);
		$contratistas = DB::table('tbl_contrato')->join('tbl_contratistas','tbl_contratistas.idcontratista','=','tbl_contrato.idcontratista')->select('tbl_contratistas.idcontratista','tbl_contratistas.razonsocial')->where('tbl_contrato.cont_estado',1)->distinct()->get();
		$listadocontratos = DB::table('tbl_contrato')->select('contrato_id','cont_nombre','cont_numero')->where('tbl_contrato.cont_estado',1)->get();

		$contratosxfaena = DB::select(DB::raw("SELECT COUNT(cc.contrato_id) AS cuentacontratos, tbl_centro.Descripcion FROM tbl_contrato c
			JOIN tbl_contratos_centros cc ON c.contrato_id=cc.contrato_id
			JOIN tbl_centro ON cc.IdCentro=tbl_centro.IdCentro
			WHERE c.cont_estado=1
			GROUP BY cc.IdCentro"));

		$trabajadoresxfaena = DB::select(DB::raw("SELECT COUNT(cp.IdPersona) as cuenta, c.Descripcion FROM tbl_contratos_personas cp
			JOIN tbl_contratos_centros cc ON cc.contrato_id=cp.contrato_id
			JOIN tbl_centro c ON c.IdCentro=cc.IdCentro
			GROUP BY cc.IdCentro"));

		$listadoadc = DB::table('tbl_contrato')
			->join('tb_users','tb_users.id','=','tbl_contrato.admin_id')
			->where('tbl_contrato.cont_estado',1)
			->select('tb_users.first_name','tb_users.last_name','tb_users.id')
			->distinct('tb_users.id')
			->get();

		$trabajadoresxcontrato = DB::select(DB::raw("SELECT COUNT(c.contrato_id) AS cuenta, c.cont_numero, c.cont_nombre FROM tbl_contrato c
			JOIN tbl_contratos_personas cp ON c.contrato_id=cp.contrato_id
			WHERE c.cont_estado=1
			GROUP BY c.contrato_id"));

		$cantidad_adc = count($listadoadc);

		$filtro_inicial['centros'] = $listadocentros;
		$filtro_inicial['contratistas'] = $contratistas;
		$filtro_inicial['contratos'] =	$listadocontratos;
		$filtro_inicial['adc'] = $listadoadc;

		return view ('dashboardcontratos.index')
			->with('opcion_seleccionada',[0])
			->with('contrato_vigente',$contrato_vigente)
			->with('contrato_personas',$contrato_personas+$contrato_pcf)
			->with('filtro_inicial',$filtro_inicial)
			->with('cantidad_faenas',$cantidad_faenas)
			->with('trabajadoresxfaena',$trabajadoresxfaena)
			->with('contratosxfaena',$contratosxfaena)
			->with('cantidad_adc',$cantidad_adc)
			->with('trabajadoresxcontrato',$trabajadoresxcontrato);
	}

	public function postIndex( Request $request )
	{

		$flag[1]=false;
		$flag[2]=false;
		$flag[3]=false;
		$flag[4]=false;

		if($request->has('contratistas')){
			$flag[1]=true;
			$opcion_selected[1] = $request->contratistas;
			$id_contratista = $request->contratistas;
		}
		if($request->has('centrofiltro')){
			$flag[2]=true;
			$opcion_selected[2] = $request->centrofiltro;
			$id_centro = $request->centrofiltro;
		}
		if($request->has('contratofiltro')){
			$flag[3]=true;
			$opcion_selected[3] = $request->contratofiltro;
			$id_contrato = $request->contratofiltro;
		}
		if($request->has('adcfiltro')){
			$flag[4]=true;
			$opcion_selected[4] = $request->adcfiltro;
			$id_adc = $request->adcfiltro;
		}

		if(!$flag[1] and !$flag[2] and !$flag[3] and !$flag[4]){
			return redirect()->back();
		}

		$periodo = Contratomaestro::max('periodo');

		$contrato_vigente = Contratos::join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contratista))$contrato_vigente->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$contrato_vigente->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_contrato))$contrato_vigente->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_adc))$contrato_vigente->where('tbl_contrato.admin_id',$id_adc);
			$contrato_vigente = $contrato_vigente->distinct('tbl_contrato.contrato_id')->count('tbl_contrato.contrato_id');

		$contrato_personas = Contratos::join('tbl_contratos_personas','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
			->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contratista))$contrato_personas->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$contrato_personas->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_contrato))$contrato_personas->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_adc))$contrato_personas->where('tbl_contrato.admin_id',$id_adc);
			$contrato_personas = $contrato_personas->distinct('tbl_contratos_personas.IdPersona')->count('tbl_contratos_personas.IdPersona');


		$contrato_pcf = Personasmaestro::join('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_personas_maestro.contrato_id')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->select('tbl_personas_maestro.id')
			->where('tbl_personas_maestro.estatus','Finiquitado')
			->where('tbl_personas_maestro.periodo',$periodo);
			if(isset($id_contratista))$contrato_pcf->where('tbl_personas_maestro.idcontratista',$id_contratista);
			if(isset($id_centro))$contrato_pcf->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_contrato))$contrato_pcf->where('tbl_personas_maestro.contrato_id',$id_contrato);
			if(isset($id_adc))$contrato_pcf->where('tbl_contrato.admin_id',$id_adc);
			$contrato_pcf = $contrato_pcf
			->distinct('tbl_personas_maestro.id')->count('tbl_personas_maestro.id');


		$listadocentros = Centros::join('tbl_contratos_centros','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->select('tbl_centro.IdCentro','tbl_centro.Descripcion')
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$listadocentros->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$listadocentros->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$listadocentros->where('tbl_centro.IdCentro',$id_centro);
			if(isset($id_adc))$listadocentros->where('tbl_contrato.admin_id',$id_adc);
			$listadocentros = $listadocentros
			->distinct('tbl_centro.IdCentro')
			->get();

		$cantidad_faenas = count($listadocentros);

		$contratosxfaena = Contratos::join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->join('tbl_centro','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
			->select('tbl_centro.Descripcion',DB::raw("COUNT(*) as cuentacontratos"))
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$contratosxfaena->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$contratosxfaena->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$contratosxfaena->where('tbl_centro.IdCentro',$id_centro);
			if(isset($id_adc))$contratosxfaena->where('tbl_contrato.admin_id',$id_adc);
			$contratosxfaena = $contratosxfaena ->groupBy('tbl_contratos_centros.IdCentro')->get();


		$trabajadoresxfaena = Contratos::join('tbl_contratos_personas','tbl_contratos_personas.contrato_id','=','tbl_contrato.contrato_id')
			->join('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_contratos_personas.contrato_id')
			->join('tbl_centro','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
			->select('tbl_centro.Descripcion',DB::raw("COUNT(*) as cuenta"))
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$trabajadoresxfaena->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$trabajadoresxfaena->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$trabajadoresxfaena->where('tbl_centro.IdCentro',$id_centro);
			if(isset($id_adc))$trabajadoresxfaena->where('tbl_contrato.admin_id',$id_adc);
			$trabajadoresxfaena = $trabajadoresxfaena ->groupBy('tbl_contratos_centros.IdCentro')->get();

		$listadoadc = Contratos::join('tb_users','tb_users.id','=','tbl_contrato.admin_id')
			->join('tbl_contratos_centros','tbl_contratos_centros.idcontratista','=','tbl_contrato.idcontratista')
			->select('tb_users.first_name','tb_users.last_name','tb_users.id as id')
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$listadoadc->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$listadoadc->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$listadoadc->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_adc))$listadoadc->where('tbl_contrato.admin_id',$id_adc);
			$listadoadc = $listadoadc
			->distinct('tb_users.id')
			->get();

		$listadocontratos = Contratos::select('tbl_contrato.contrato_id','tbl_contrato.cont_nombre','tbl_contrato.cont_numero')
			->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$listadocontratos->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$listadocontratos->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$listadocontratos->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_adc))$listadocontratos->where('tbl_contrato.admin_id',$id_adc);
			$listadocontratos = $listadocontratos->get();

		$contratistas = Contratos::join('tbl_contratistas','tbl_contratistas.idcontratista','=','tbl_contrato.idcontratista')
			->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->select('tbl_contratistas.idcontratista','tbl_contratistas.razonsocial')
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$contratistas->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$contratistas->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$contratistas->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_adc))$contratistas->where('tbl_contrato.admin_id',$id_adc);
			$contratistas = $contratistas
			->distinct()->get();

		$trabajadoresxcontrato = Contratos::join('tbl_contratos_personas','tbl_contratos_personas.contrato_id','=','tbl_contrato.contrato_id')
			->join('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_contrato.contrato_id')
			->select('tbl_contrato.cont_numero','tbl_contrato.cont_nombre', DB::raw("COUNT(*) as cuenta"))
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$trabajadoresxcontrato->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$trabajadoresxcontrato->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$trabajadoresxcontrato->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_adc))$trabajadoresxcontrato->where('tbl_contrato.admin_id',$id_adc);
			$trabajadoresxcontrato = $trabajadoresxcontrato
			->groupBy('tbl_contrato.contrato_id')->get();

		$cantidad_adc = count($listadoadc);

		$filtro_inicial['centros'] = $listadocentros;
		$filtro_inicial['contratistas'] = $contratistas;
		$filtro_inicial['contratos'] =	$listadocontratos;
		$filtro_inicial['adc'] = $listadoadc;

		return view ('dashboardcontratos.index')
			->with('opcion_seleccionada',$opcion_selected)
			->with('contrato_vigente',$contrato_vigente)
			->with('contrato_personas',$contrato_personas+$contrato_pcf)
			->with('filtro_inicial',$filtro_inicial)
			->with('cantidad_faenas',$cantidad_faenas)
			->with('trabajadoresxfaena',$trabajadoresxfaena)
			->with('contratosxfaena',$contratosxfaena)
			->with('cantidad_adc',$cantidad_adc)
			->with('trabajadoresxcontrato',$trabajadoresxcontrato);
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
		return view('dashboardcontratos.form',$this->data);
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$this->data['access']		= $this->access;
		return view('dashboardcontratos.view',$this->data);
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
