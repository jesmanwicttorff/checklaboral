<?php namespace App\Http\Controllers;

use App\Http\Controllers;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller {

	public function __construct()
	{
		parent::__construct();
        $this->data = array(
            'pageTitle' =>  CNF_APPNAME,
            'pageNote'  =>  'Welcome to Dashboard',

        );
	}

	public function getIndex( Request $request )
	{

		$lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
		$lintIduser = \Session::get('uid');
		$lobjUsuario = \DB::table('tb_users')
						->select('tbl_dashboard.vista')
		                ->join('tb_groups','tb_users.group_id','=','tb_groups.group_id')
		                ->join('tbl_dashboard','tbl_dashboard.id','=','tb_groups.iddashboard')
		                ->where('tb_users.id','=', $lintIduser)
		                ->first();
		$this->data['lstrEmail'] = "consulta".env('APP_NAME')."@sourcing.cl";
		$this->data['lstrTelefono'] = env('APP_SUPORTPHONE');

		$lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
		$this->data['lobjFiltro'] = $lobjFiltro;

		if ($lobjUsuario->vista!="abastible"
			&& $lobjUsuario->vista!="transbank"
			&& $lobjUsuario->vista!="brotec"
			&& $lobjUsuario->vista!="tec"
			&& $lobjUsuario->vista!="msmin"
			&& $lobjUsuario->vista!="cchc"
			&& $lobjUsuario->vista!="ccu"
			&& $lobjUsuario->vista!="andina"
			&& $lobjUsuario->vista!="fepasa"){

			//Consulta del libro de obras
			$lstrQueryLod = ' SELECT tbl_tickets_tipos.IdTicketTipo,
									tbl_tickets_tipos.Descripcion as Tipo,
									tbl_tickets_tipos.IdEstatus as Estado,
									SUM(countAbiertos) as countAbiertos,
									SUM(countCerrados) as countCerrados,
									SUM(Thread) as Thread,
									SUM(ThreadViews) as ThreadViews,
									(SUM(Thread) - SUM(ThreadViews)) as ThreadNotViews
							FROM tbl_tickets_tipos
								LEFT JOIN ( SELECT tbl_contrato.contrato_id,
												tbl_tickets.IdTipo,
												COUNT(tbl_tickets_thread.IdTicketThread) as Thread,
												COUNT(DISTINCT(tbl_tickets_vistas.IdTicketThread)) as ThreadViews,
												COUNT(DISTINCT(case when tbl_tickets.IdEstatus = 1 then tbl_tickets.IdTicket end)) as countAbiertos,
												COUNT(DISTINCT(case when tbl_tickets.IdEstatus = 2 then tbl_tickets.IdTicket end)) as countCerrados
											FROM tbl_contrato
												INNER JOIN tbl_tickets ON tbl_tickets.contrato_id = tbl_contrato.contrato_id
												LEFT JOIN tbl_tickets_thread ON tbl_tickets.IdTicket = tbl_tickets_thread.IdTicket
												LEFT JOIN tbl_tickets_vistas ON tbl_tickets_thread.IdTicketThread = tbl_tickets_vistas.IdTicketThread AND tbl_tickets_vistas.entry_by = '.$lintIduser;
			$lstrQueryLod .= " WHERE tbl_contrato.cont_estado = 1";


	        $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);

	        $lstrQueryLod .= " AND tbl_contrato.contrato_id IN (".$lobjFiltro['contratos'].') ';

			$lstrQueryLod .= ' GROUP BY tbl_contrato.contrato_id, tbl_tickets.IdTipo) as tbl_tickets ON tbl_tickets.IdTipo = tbl_tickets_tipos.IdTicketTipo
			                   GROUP BY tbl_tickets_tipos.IdTicketTipo,
			                            tbl_tickets_tipos.Descripcion,
										tbl_tickets_tipos.IdEstatus';

			$lobjLo = \DB::select($lstrQueryLod);
			$larrLo = array( "countAbiertos"=>0,
			                 "countCerrados"=>0,
			                 "ThreadStatus"=>0,
			                 "Thread"=>0,
			                 "ThreadViews"=>0,
			                 "ThreadNotViews"=>0,
			                 "tooltipOpen" => "<table>",
			                 "tooltipNotViews" => "<table>",
			                 "tooltipClose" => "<table>",
			                 "tooltipAllthread" => "<table>",
			                 );
			foreach ($lobjLo as $larrValue) {
	                            $larrLo["ThreadStatus"] = $larrValue->Estado;
				    $larrLo["countAbiertos"] += $larrValue->countAbiertos;
				    $larrLo["countCerrados"] += $larrValue->countCerrados;
				    $larrLo["Thread"] += $larrValue->Thread;
				    $larrLo["ThreadViews"] += $larrValue->ThreadViews;
				    $larrLo["ThreadNotViews"] += $larrValue->ThreadNotViews;

					#echo $larrLo["ThreadStatus"]."</p>";

				//control de tipologia hacia los abiertos y con comunicaciones creadas en TOOLTIP.
				if($larrLo["ThreadStatus"] != 0 ){
	                            $larrLo["tooltipOpen"] .= '<tr>
								<td class=\'text-left\'>'.$larrValue->Tipo.'</td>
								<td style=\'padding-left:5px;\'>'.($larrValue->countAbiertos?$larrValue->countAbiertos:0).'</td>
								</tr>';
				}else {  }

				if($larrLo["ThreadStatus"] != 0 ){
				    $larrLo["tooltipNotViews"] .= '<tr>
	                                                            <td class=\'text-left\'>'.$larrValue->Tipo.'</td>
	                                                            <td style=\'padding-left:5px;\'>'.($larrValue->ThreadNotViews?$larrValue->ThreadNotViews:0).'</td>
	                                                            </tr>';
				}else {  }

	                            $larrLo["tooltipClose"] .= '<tr><td class=\'text-left\'>'.$larrValue->Tipo.'</td><td style=\'padding-left:5px;\'>'.($larrValue->countCerrados?$larrValue->countCerrados:0).'</td></tr>';
				    $larrLo["tooltipAllthread"] .= '<tr><td class=\'text-left\'>'.$larrValue->Tipo.'</td><td style=\'padding-left:5px;\'>'.($larrValue->Thread?$larrValue->Thread:0).'</td></tr>';

			}
			$larrLo["tooltipOpen"] .= "</table>";
			$larrLo["tooltipNotViews"] .= "</table>";
			$larrLo["tooltipClose"] .= "</table>";
			$larrLo["tooltipAllthread"] .= "</table>";
			$larrData['larrLo'] = $larrLo;
			$this->data['htmllo'] = view('dashboard.notificaciones.lo',$larrData);

			$this->data['online_users'] = \DB::table('tb_users')->orderBy('last_activity','desc')->limit(10)->get();
			$this->data['active'] = '';
			$this->data['sesion_contratos'] = $sesion_contratos;
			$this->data['sesion_contratistas'] = $sesion_contratistas;

	        //asignamos las viriables para el gráfico
	    	$this->data['DataReporte'] = array("reg"=>null,
						             'seg' => null,
						             'area' => null,
						             'ind' => null,
						             'year' => null,
						             'mes' => null,
						             'id'=>null
	    	);
	    	$lobjMyReports = new \MyReports($this->data['DataReporte']);
	    	$larrDataRender = $lobjMyReports::getGlobal("global",null,null,1);
	    	$larrDataRender = json_encode($larrDataRender);
			$this->data['larrDataRender'] = $larrDataRender;

			//recuperamos la lista de contratos
			$lobjContratos = \DB::table('tbl_contrato')
		    ->select("tbl_contrato.contrato_id","tbl_contratistas.Rut", "tbl_contratistas.RazonSocial", "tbl_contrato.cont_numero", "tbl_contrato.cont_nombre")
		    ->join("tbl_contratistas", "tbl_contrato.IdContratista","=","tbl_contratistas.IdContratista")
		    ->where("tbl_contrato.cont_FechaFin",">",date('Y-m-d'));


	        $lcontratos = explode(',',$lobjFiltro['contratos']);

	        $lobjContratos = $lobjContratos->wherein("tbl_contrato.contrato_id",$lcontratos);


			$lobjContratos = $lobjContratos->get();
			$lstrListaContrato = "";
			$lstrListaContrato .= "<table class='table table-striped table-bordered table-hover dataTable tblcontrato' style='border: solid 0px;' class='table table-striped'>";
			$lstrListaContrato .= "<thead><tr><th>Estado*</th><th><center> Contrato</th></tr></thead>";
			foreach ($lobjContratos as $larrContrato) {
				$lobjResultado = $lobjMyReports::getGlobal("global",null,null,1,$larrContrato->contrato_id);
				$lstrListaContrato .= '<tr>';
				$lstrListaContrato .= '  <td>';
				$lstrListaContrato .= '    <div class="circle_green" style="background-color:'.$lobjResultado['general']['color'].'" >';
				$lstrListaContrato .= $lobjResultado['general']['value'].''.$lobjResultado['general']['unid'];
				$lstrListaContrato .= '    </div>';
				$lstrListaContrato .= '  </td>';
				$lstrListaContrato .= '<td>';
				$lstrListaContrato .= '<a href="reportdetcontrato?id='.$larrContrato->contrato_id.'&mes='.'&year='.'">';
				$lstrListaContrato .= $larrContrato->RazonSocial." - ".$larrContrato->cont_numero;
				$lstrListaContrato .= '</a>';
				$lstrListaContrato .= '</td>';
				$lstrListaContrato .= '</tr>';
			}
			$lstrListaContrato .= '</tbody>';
			$lstrListaContrato .= '</table>';
			$this->data['ListaContratos'] = $lstrListaContrato;
			//recuperamos la lista de contratos

			//Vemos que dashboard tiene configurado el usuario
			$lintIdDashboard = 'index';
	        $lintGroupUser = \MySourcing::GroupUser(\Session::get('uid'));
    	}

		if ($lobjUsuario){
            if($lobjUsuario->vista=="precontratista"){
                $this->data['ListaDocumentosT'] = 'CALL pr_Estado_de_Documentos("'.$lobjFiltro['contratistas'].'", "'.$lobjFiltro['contratos'].'")';
                $this->data['ListaDocumentosP'] = 'CALL pr_Estado_de_Documentos_propios("'.$lobjFiltro['contratistas'].'", "'.$lobjFiltro['contratos'].'", '.$lintGroupUser.')';
            }

			$lintIdDashboard = $lobjUsuario->vista;
		}
		$this->data['info'] = (object) [
			'titulo'=>'CheckLaboral',
			'subTitulo'=>'Plataforma de Acreditación, Control de Accesos y Control Laboral',
			'intro'=>'Si es su primera vez aquí, le recomendamos leer el manual disponible en el menú lateral izquierdo:',
			'subIntro'=>'En el menú lateral izquierdo ir a '
		];
		$this->data['pcontrolado'] = \DB::table('tbl_periodo_controlado')->get();
        return view('dashboard.'.$lintIdDashboard,$this->data);

	}
}
