<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Models\Dashboardpersonas;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Validator, Input, Redirect ;
use DB;
use App\Models\checklaboral\Contratomaestro;
use App\Models\Contratos;
use App\Models\Centros;
use App\Models\Personas;
use App\Models\Personasmaestro;

class DashboardpersonasController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();
	public $module = 'dashboardpersonas';
	static $per_page	= '10';

	public function __construct()
	{

		parent::__construct();

		$this->model = new Dashboardpersonas();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);

		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'dashboardpersonas',
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

		$pc = DB::table('tbl_contrato')->join('tbl_contratos_personas','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')->where('tbl_contrato.cont_estado','!=',2)->count();
		$pcf = DB::table('tbl_personas_maestro')->select('id')->where('estatus','Finiquitado')->where('periodo',$periodo)->count();
		$personasContrato = $pc+$pcf;

		$paccessos = DB::table('tbl_personas')->join('tbl_accesos','tbl_accesos.idpersona','=','tbl_personas.IdPersona')->count();

		$personas_edades = DB::select(DB::raw("SELECT
			SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) < 18 THEN 1 ELSE 0 END) AS edad0,
			SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) > 17 AND DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(fechaNacimiento, '00-%m-%d')) < 30 THEN 1 ELSE 0 END) AS edad1,
			SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) > 29 AND DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(fechaNacimiento, '00-%m-%d')) < 40 THEN 1 ELSE 0 END) AS edad2,
			SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) > 39 AND DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(fechaNacimiento, '00-%m-%d')) < 50 THEN 1 ELSE 0 END) AS edad3,
			SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) > 49 AND DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(fechaNacimiento, '00-%m-%d')) < 60 THEN 1 ELSE 0 END) AS edad4,
			SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) > 59 THEN 1 ELSE 0 END) AS edad5,
			SUM(CASE WHEN tbl_personas.fechaNacimiento is null then 1 else 0 end) as edad6 FROM tbl_personas join tbl_contratos_personas ON tbl_contratos_personas.IdPersona = tbl_personas.IdPersona join tbl_contrato c on c.contrato_id=tbl_contratos_personas.contrato_id where c.cont_estado!=2"));

		$personas_genero = DB::select(DB::raw("SELECT SUM(CASE WHEN tbl_personas.Sexo = 1 THEN 1 ELSE 0 END) AS personasM, SUM(CASE WHEN tbl_personas.Sexo = 2 THEN 1 ELSE 0 END) AS personasF FROM tbl_personas join tbl_contratos_personas ON tbl_contratos_personas.IdPersona = tbl_personas.IdPersona join tbl_contrato c on c.contrato_id=tbl_contratos_personas.contrato_id where c.cont_estado!=2"));

		$paises = DB::select(DB::raw('SELECT b.Pais, count(a.IdPersona) as count
			FROM tbl_personas as a
			LEFT JOIN tbl_nacionalidad as b ON a.id_Nac = b.id_Nac
			JOIN tbl_contratos_personas ON tbl_contratos_personas.IdPersona = a.IdPersona
			join tbl_contrato c on c.contrato_id=tbl_contratos_personas.contrato_id where c.cont_estado!=2
			GROUP BY b.Pais'));

		$centros = DB::select(DB::raw('SELECT c.Descripcion, COUNT(b.IdPersona) AS COUNT, c.IdCentro
			FROM tbl_personas AS a
			INNER JOIN tbl_contratos_personas AS b ON a.IdPersona = b.IdPersona
			JOIN tbl_contratos_centros AS cc ON cc.contrato_id = b.contrato_id
			INNER JOIN tbl_centro AS c ON c.IdCentro = cc.IdCentro
			join tbl_contrato con on con.contrato_id=b.contrato_id where con.cont_estado!=2
			GROUP BY c.Descripcion'));

		$gerencias = DB::select(DB::raw('SELECT d.afuncional_nombre, COUNT(a.IdPersona) AS COUNT
			FROM tbl_personas AS a
			INNER JOIN tbl_contratos_personas AS b ON a.IdPersona = b.IdPersona
			INNER JOIN tbl_contrato AS c ON b.contrato_id = c.contrato_id
			INNER JOIN tbl_contareafuncional AS d ON d.afuncional_id = c.afuncional_id
			where c.cont_estado!=2
			GROUP BY d.afuncional_nombre'));

		$segmentos = DB::select(DB::raw('SELECT d.seg_nombre, COUNT(a.IdPersona) AS count
			FROM tbl_personas AS a
			INNER JOIN tbl_contratos_personas AS b ON a.IdPersona = b.IdPersona
			INNER JOIN tbl_contrato AS c ON b.contrato_id = c.contrato_id
			INNER JOIN tbl_contsegmento d ON d.segmento_id = c.segmento_id
			where c.cont_estado!=2
			GROUP BY d.seg_nombre'));

		$listadocentros = DB::table('tbl_centro')
			->join('tbl_contratos_centros','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->where('tbl_contrato.cont_estado',1)
			->select('tbl_centro.IdCentro','tbl_centro.Descripcion')
			->distinct('tbl_centro.IdCentro')
			->get();
		$contratistas = DB::table('tbl_contrato')->join('tbl_contratistas','tbl_contratistas.idcontratista','=','tbl_contrato.idcontratista')->select('tbl_contratistas.idcontratista','tbl_contratistas.razonsocial')->where('tbl_contrato.cont_estado',1)->distinct()->get();
		$listadocontratos = DB::table('tbl_contrato')->select('contrato_id','cont_nombre','cont_numero')->where('tbl_contrato.cont_estado',1)->get();
		$listadoadc = DB::table('tbl_contrato')
			->join('tb_users','tb_users.id','=','tbl_contrato.admin_id')
			->where('tbl_contrato.cont_estado',1)
			->select('tb_users.first_name','tb_users.last_name','tb_users.id')
			->distinct('tb_users.id')
			->get();

		$filtro_inicial['centros'] = $listadocentros;
		$filtro_inicial['contratistas'] = $contratistas;
		$filtro_inicial['contratos'] =	$listadocontratos;
		$filtro_inicial['adc'] = $listadoadc;

		return view('dashboardpersonas.index',$this->data)
					->with('opcion_seleccionada',[0])
					->with('personasContrato',$personasContrato)
					->with('paccessos',$paccessos)
					->with('personas_edades',$personas_edades)
					->with('centros',$centros)
					->with('personas_genero',$personas_genero)
					->with('filtro_inicial',$filtro_inicial)
					->with('gerencias',$gerencias)
					->with('segmentos',$segmentos)
					->with('paises',$paises);

	}

	public function postIndex(Request $request)
	{

		$flag[1]=false;
		$flag[2]=false;
		$flag[3]=false;
		$flag[4]=false;
		$conta=0;

		$periodo = Contratomaestro::max('periodo');

		if($request->has('contratistas')){
			$flag[1]=true; $conta++;
			$opcion_selected[1] = $request->contratistas;
			$id_contratista = $request->contratistas;
		}
		if($request->has('centrofiltro')){
			$flag[2]=true; $conta++;
			$opcion_selected[2] = $request->centrofiltro;
			$id_centro = $request->centrofiltro;
		}
		if($request->has('contratofiltro')){
			$flag[3]=true; $conta++;
			$opcion_selected[3] = $request->contratofiltro;
			$id_contrato = $request->contratofiltro;
		}
		if($request->has('adcfiltro')){
			$flag[4]=true; $conta++;
			$opcion_selected[4] = $request->adcfiltro;
			$id_adc = $request->adcfiltro;
		}

		if(!$flag[1] and !$flag[2] and !$flag[3] and !$flag[4]){
			return redirect()->back();
		}

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

		$pc = Contratos::join('tbl_contratos_personas','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
			->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contratista))$pc->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$pc->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_contrato))$pc->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_adc))$pc->where('tbl_contrato.admin_id',$id_adc);
			$pc = $pc->distinct('tbl_contratos_personas.IdPersona')->count('tbl_contratos_personas.IdPersona');

		$pcf = Personasmaestro::join('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_personas_maestro.contrato_id')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->select('tbl_personas_maestro.id')
			->where('tbl_personas_maestro.estatus','Finiquitado')
			->where('tbl_personas_maestro.periodo',$periodo);
			if(isset($id_contratista))$pcf->where('tbl_personas_maestro.idcontratista',$id_contratista);
			if(isset($id_centro))$pcf->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_contrato))$pcf->where('tbl_personas_maestro.contrato_id',$id_contrato);
			if(isset($id_adc))$pcf->where('tbl_contrato.admin_id',$id_adc);
			$pcf = $pcf
			->distinct('tbl_personas_maestro.id')->count('tbl_personas_maestro.id');

		$personasContrato = $pc+$pcf;

		$paccessos = Personas::join('tbl_accesos','tbl_accesos.idpersona','=','tbl_personas.IdPersona')
			->join('tbl_personas_maestro','tbl_personas_maestro.IdPersona','=','tbl_personas.IdPersona')
			->join('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_personas_maestro.contrato_id')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id');
			if(isset($id_contratista))$paccessos->where('tbl_personas_maestro.idcontratista',$id_contratista);
			if(isset($id_centro))$paccessos->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_contrato))$paccessos->where('tbl_personas_maestro.contrato_id',$id_contrato);
			if(isset($id_adc))$paccessos->where('tbl_contrato.admin_id',$id_adc);
			$paccessos = $paccessos
			->count();

		$personas_edades = Personas::join('tbl_contratos_personas','tbl_personas.IdPersona','=','tbl_contratos_personas.IdPersona')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
			->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->select(
				DB::raw("SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) < 18 THEN 1 ELSE 0 END) AS edad0"),DB::raw("SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) > 17 AND DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(fechaNacimiento, '00-%m-%d')) < 30 THEN 1 ELSE 0 END) AS edad1"),DB::raw("SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) > 29 AND DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(fechaNacimiento, '00-%m-%d')) < 40 THEN 1 ELSE 0 END) AS edad2"), DB::raw("SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) > 39 AND DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(fechaNacimiento, '00-%m-%d')) < 50 THEN 1 ELSE 0 END) AS edad3"), DB::raw("SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) > 49 AND DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(fechaNacimiento, '00-%m-%d')) < 60 THEN 1 ELSE 0 END) AS edad4"),  DB::raw("SUM(CASE WHEN DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(tbl_personas.fechaNacimiento, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(tbl_personas.fechaNacimiento, '00-%m-%d')) > 59 THEN 1 ELSE 0 END) AS edad5"), DB::raw("SUM(CASE WHEN tbl_personas.fechaNacimiento is null then 1 else 0 end) as edad6")
				)
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$personas_edades->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$personas_edades->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$personas_edades->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_adc))$personas_edades->where('tbl_contrato.admin_id',$id_adc);
			$personas_edades = $personas_edades->get();

		$personas_genero = Personas::join('tbl_contratos_personas','tbl_personas.IdPersona','=','tbl_contratos_personas.IdPersona')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
			->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->select(DB::raw("SUM(CASE WHEN tbl_personas.Sexo = 1 THEN 1 ELSE 0 END) AS personasM"), DB::raw("SUM(CASE WHEN tbl_personas.Sexo = 2 THEN 1 ELSE 0 END) AS personasF"))
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$personas_genero->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$personas_genero->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$personas_genero->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_adc))$personas_genero->where('tbl_contrato.admin_id',$id_adc);
			$personas_genero = $personas_genero->get();

		$paises = Personas::join('tbl_nacionalidad', 'tbl_personas.id_Nac','=','tbl_nacionalidad.id_Nac')
			->join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
			->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->select('tbl_nacionalidad.Pais',DB::raw("COUNT(*) as count"))
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$paises->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$paises->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$paises->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_adc))$paises->where('tbl_contrato.admin_id',$id_adc);
			$paises = $paises->groupBy('tbl_nacionalidad.Pais')->get();

		$centros = Personas::join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
			->join('tbl_contratos_centros','tbl_contratos_personas.contrato_id','=','tbl_contratos_centros.contrato_id')
			->join('tbl_centro','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
			->select('tbl_centro.Descripcion', DB::raw("COUNT(*) as COUNT"), 'tbl_centro.IdCentro')
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$centros->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$centros->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$centros->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_adc))$centros->where('tbl_contrato.admin_id',$id_adc);
			$centros = $centros->groupBy('tbl_centro.Descripcion')->get();

		$gerencias = Personas::join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
			->join('tbl_contareafuncional','tbl_contareafuncional.afuncional_id','=','tbl_contrato.afuncional_id')
			->join('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_contrato.contrato_id')
			->select('tbl_contareafuncional.afuncional_nombre', DB::raw("COUNT(*) as COUNT"))
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$gerencias->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$gerencias->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$gerencias->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_adc))$gerencias->where('tbl_contrato.admin_id',$id_adc);
			$gerencias = $gerencias->groupBy('tbl_contareafuncional.afuncional_nombre')->get();

		$segmentos = Personas::join('tbl_contratos_personas','tbl_contratos_personas.IdPersona','=','tbl_personas.IdPersona')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_personas.contrato_id')
			->join('tbl_contsegmento','tbl_contsegmento.segmento_id','=','tbl_contrato.segmento_id')
			->join('tbl_contratos_centros','tbl_contratos_centros.contrato_id','=','tbl_contrato.contrato_id')
			->select('tbl_contsegmento.seg_nombre', DB::raw("COUNT(*) as count"))
			->where('tbl_contrato.cont_estado',1);
			if(isset($id_contrato))$segmentos->where('tbl_contrato.contrato_id',$id_contrato);
			if(isset($id_contratista))$segmentos->where('tbl_contrato.idcontratista',$id_contratista);
			if(isset($id_centro))$segmentos->where('tbl_contratos_centros.IdCentro',$id_centro);
			if(isset($id_adc))$segmentos->where('tbl_contrato.admin_id',$id_adc);
			$segmentos = $segmentos->groupBy('tbl_contsegmento.seg_nombre')->get();

			$filtro_inicial['centros'] = $listadocentros;
			$filtro_inicial['contratistas'] = $contratistas;
			$filtro_inicial['contratos'] =	$listadocontratos;
			$filtro_inicial['adc'] = $listadoadc;

		return view('dashboardpersonas.index',$this->data)
					->with('opcion_seleccionada',$opcion_selected)
					->with('personasContrato',$personasContrato)
					->with('paccessos',$paccessos)
					->with('personas',0)
					->with('personas_edades',$personas_edades)
					->with('centros',$centros)
					->with('filtro_inicial',$filtro_inicial)
					->with('personas_genero',$personas_genero)
					->with('gerencias',$gerencias)
					->with('segmentos',$segmentos)
					->with('paises',$paises);
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
		return view('dashboardpersonas.form',$this->data);
	}

	public function getShow( $id = null)
	{

		if($this->access['is_detail'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');


		$this->data['access']		= $this->access;
		return view('dashboardpersonas.view',$this->data);
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

	public function getResetFiltros(){

		$listadocentros = DB::table('tbl_centro')
			->join('tbl_contratos_centros','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
			->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
			->where('tbl_contrato.cont_estado',1)
			->select('tbl_centro.IdCentro as id','tbl_centro.Descripcion')
			->distinct('tbl_centro.IdCentro')
			->get();
		$contratistas = DB::table('tbl_contrato')->join('tbl_contratistas','tbl_contratistas.idcontratista','=','tbl_contrato.idcontratista')->select('tbl_contratistas.idcontratista as id','tbl_contratistas.razonsocial as Descripcion')->where('tbl_contrato.cont_estado',1)->distinct()->get();
		$listadocontratos = DB::table('tbl_contrato')->select('contrato_id as id','cont_nombre as Descripcion','cont_numero')->where('cont_estado',1)->get();
		$listadoadc = DB::table('tbl_contrato')
			->join('tb_users','tb_users.id','=','tbl_contrato.admin_id')
			->join('tbl_contratos_centros','tbl_contratos_centros.idcontratista','=','tbl_contrato.idcontratista')
			->where('tbl_contrato.cont_estado',1)
			->select('tb_users.first_name as Descripcion','tb_users.last_name','tb_users.id as id')
			->distinct('tb_users.id')
			->get();

			$filtros['centrofiltro']				= $listadocentros;
			$filtros['listadocontratistas']	= $contratistas;
			$filtros['contratofiltro']			= $listadocontratos;
			$filtros['adcfiltro']						= $listadoadc;

				return response()->json(array(
					'status'		=> 'success',
					'filtros'		=> json_encode($filtros)
				));

	}

	public function getFiltro2(Request $request){

		$tipo = $request->tipo;
		$id = $request->val;
		$seleccionados = $request->selec;

		$selec1 = explode(',',$seleccionados);
		$selec2 = explode(':',$selec1[0]);
		if(isset($selec1[1]))$selec3 = explode(':',$selec1[1]);
		if(isset($selec1[2]))$selec4 = explode(':',$selec1[2]);

		if($selec2[0]=='contratofiltro'){$id_contrato = $selec2[1];}
		if($selec2[0]=='adcfiltro'){$id_adc = $selec2[1];}
		if($selec2[0]=='centrofiltro'){$id_centro = $selec2[1];}
		if($selec2[0]=='listadocontratistas'){$id_contratista = $selec2[1];}
		if(isset($selec3)){
			if($selec3[0]=='contratofiltro'){$id_contrato = $selec3[1];}
			if($selec3[0]=='adcfiltro'){$id_adc = $selec3[1];}
			if($selec3[0]=='centrofiltro'){$id_centro = $selec3[1];}
			if($selec3[0]=='listadocontratistas'){$id_contratista = $selec3[1];}
		}
		if(isset($selec4)){
			if($selec4[0]=='contratofiltro'){$id_contrato = $selec4[1];}
			if($selec4[0]=='adcfiltro'){$id_adc = $selec4[1];}
			if($selec4[0]=='centrofiltro'){$id_centro = $selec4[1];}
			if($selec4[0]=='listadocontratistas'){$id_contratista = $selec4[1];}
		}

		switch ($tipo) {
			case 'contratista': $id_contratista=$id; break;
			case 'contrato' 	: $id_contrato=$id; break;
			case 'centro'			:	$id_centro=$id; break;
			case 'adc'				: $id_adc=$id; break;
		}

			$listadocentros = Centros::join('tbl_contratos_centros','tbl_centro.IdCentro','=','tbl_contratos_centros.IdCentro')
				->join('tbl_contrato','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
				->select('tbl_centro.IdCentro as id','tbl_centro.Descripcion')
				->where('tbl_contrato.cont_estado',1);
				if(isset($id_contrato))$listadocentros->where('tbl_contrato.contrato_id',$id_contrato);
				if(isset($id_contratista))$listadocentros->where('tbl_contrato.idcontratista',$id_contratista);
				if(isset($id_centro))$listadocentros->where('tbl_centro.IdCentro',$id_centro);
				if(isset($id_adc))$listadocentros->where('tbl_contrato.admin_id',$id_adc);
				$listadocentros = $listadocentros
				->distinct('tbl_centro.IdCentro')
				->get();

			$listadoadc = Contratos::join('tb_users','tb_users.id','=','tbl_contrato.admin_id')
				->join('tbl_contratos_centros','tbl_contratos_centros.idcontratista','=','tbl_contrato.idcontratista')
				->select('tb_users.first_name as Descripcion','tb_users.last_name','tb_users.id as id')
				->where('tbl_contrato.cont_estado',1);
				if(isset($id_contrato))$listadoadc->where('tbl_contrato.contrato_id',$id_contrato);
				if(isset($id_contratista))$listadoadc->where('tbl_contrato.idcontratista',$id_contratista);
				if(isset($id_centro))$listadoadc->where('tbl_contratos_centros.IdCentro',$id_centro);
				if(isset($id_adc))$listadoadc->where('tbl_contrato.admin_id',$id_adc);
				$listadoadc = $listadoadc
				->distinct('tb_users.id')
				->get();

			$contratistas = Contratos::join('tbl_contratistas','tbl_contratistas.idcontratista','=','tbl_contrato.idcontratista')
				->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
				->select('tbl_contratistas.idcontratista as id','tbl_contratistas.razonsocial as Descripcion')
				->where('tbl_contrato.cont_estado',1);
				if(isset($id_contrato))$contratistas->where('tbl_contrato.contrato_id',$id_contrato);
				if(isset($id_contratista))$contratistas->where('tbl_contrato.idcontratista',$id_contratista);
				if(isset($id_centro))$contratistas->where('tbl_contratos_centros.IdCentro',$id_centro);
				if(isset($id_adc))$contratistas->where('tbl_contrato.admin_id',$id_adc);
				$contratistas = $contratistas
				->distinct()->get();

			$listadocontratos = Contratos::select('tbl_contrato.contrato_id as id','tbl_contrato.cont_nombre as Descripcion','tbl_contrato.cont_numero')
				->join('tbl_contratos_centros','tbl_contrato.contrato_id','=','tbl_contratos_centros.contrato_id')
				->where('tbl_contrato.cont_estado',1);
				if(isset($id_contrato))$listadocontratos->where('tbl_contrato.contrato_id',$id_contrato);
				if(isset($id_contratista))$listadocontratos->where('tbl_contrato.idcontratista',$id_contratista);
				if(isset($id_centro))$listadocontratos->where('tbl_contratos_centros.IdCentro',$id_centro);
				if(isset($id_adc))$listadocontratos->where('tbl_contrato.admin_id',$id_adc);
				$listadocontratos = $listadocontratos->get();

			$filtros['centrofiltro']				= $listadocentros;
			$filtros['listadocontratistas'] = $contratistas;
			$filtros['adcfiltro']						= $listadoadc;
			$filtros['contratofiltro']			= $listadocontratos;


				return response()->json(array(
					'status'		=> 'success',
					'filtros'		=> json_encode($filtros)
				));

	}
}
