<?php

namespace App\Http\Controllers\ApiFront;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Personas;
use App\Models\Contratos;
use App\Models\Contratospersonas;
use App\Models\Roles;
use App\Models\Tiposcontratospersonas;
use App\Models\OtrosAnexos;
use App\Models\MovimientoPersonal;
use App\Library\MyDocumentsSettlementPerson;
use App\Library\MyDocumentsContractPersonAnnexed;
use App\Library\MyCheckLaboral;
use App\Library\MyRequirements;
use DB;
use Carbon\Carbon;
use App\Traits\People;
use App\Traits\Helper;

class personasController extends Controller
{
  use People, Helper;

  public $module = 'personas';
  static protected $gobjPersona;

  public function __construct() {
      parent::__construct();
      $this->model = new Personas();
      $this->info = $this->model->makeInfo( $this->module);
  		$this->access = $this->model->validAccess($this->info['id']);
  }

  public function eliminaPersona(request $request){

    $arr =  json_decode($request->getContent());

    if(count($arr->ids) >=1){
      // Busco el id persona  si tiene contrato Persona
      $lobjContratoContPer = \DB::table('tbl_contratos_personas')
      ->whereIn('tbl_contratos_personas.IdPersona',$arr->ids)
      ->get();

      if ($lobjContratoContPer){ // Si la persona tiene contratos asociado en tbl_contrato_persona, no permite eliminar
        return response()->json(array(
          'message'	=> 'No puede eliminar la Persona, verifique que no tenga contrato asociados',
          'status'	=> 'error'
        ));
      }


      // Busco el id persona en la tabla tbl_documentos
      $lobjDocPer = \DB::table('tbl_documentos')
        ->where('tbl_documentos.Entidad','=',3)
        ->whereIn('tbl_documentos.IdEntidad',$arr->ids)
        ->get();

      if ($lobjDocPer){ // Si la persona  tiene registro en la tabla tbl_documentos, no permite eliminar
        return response()->json(array(
          'message'	=> 'No puede eliminar, verifique que no tenga documentos asociados',
          'status'	=> 'error'
        ));
      }

        // Busco el id persona en la tabla tbl_documentos_rep_historico
        $lobjDocHistPer = \DB::table('tbl_documentos_rep_historico')
          ->where('tbl_documentos_rep_historico.Entidad','=',3)
          ->whereIn('tbl_documentos_rep_historico.IdEntidad',$arr->ids)
          ->get();

        if ($lobjDocHistPer){ // Si la persona  tiene registro en la tabla tbl_documentos Historico, no permite eliminar
          return response()->json(array(
            'message'	=> 'No puede eliminar, verifique que no tenga documentos historicos asociados',
            'status'	=> 'error'
          ));
        }

        // Busco el id persona en la tabla tbl_personas_acreditacion
        $lobjAcrediPer = \DB::table('tbl_personas_acreditacion')
        ->whereIn('tbl_personas_acreditacion.IdPersona',$arr->ids)
        ->get();

      if ($lobjAcrediPer){ // Si la persona  tiene registro en la tabla tbl_personas_acreditacion, no permite eliminar
        return response()->json(array(
          'message'	=> 'No puede eliminar, verifique que no este acreditado',
          'status'	=> 'error'
        ));
      }

        // Si cumple con todos los requerimientos borro el registro en tbl_persona y tbl_anotaciones

        \DB::table('tbl_anotaciones')->whereIn('IdPersona',$arr->ids)->delete();
        $this->model->destroy($arr->ids); // Elimino registro tbl_personas
        return response()->json(array(
        'status'=>'success',
        'message'=> 'Eliminados exitosamente!!!'
      ));

    } else {
      return response()->json(array(
        'status'=>'error',
        'message'=> 'Debe seleccionar alguna Persona'
      ));
    }
  }

  public function getList(){

    $lintIdUser = \Session::get('uid');
    $lintGroupUser = \MySourcing::GroupUser($lintIdUser);
    $lintLevelUser = \MySourcing::LevelUser($lintIdUser);
    $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
    $lcontratos = explode(',',$lobjFiltro['contratos']);
    $personas = Personas::where('IdEstatus', 1);
    if($lintLevelUser==1 || $lintLevelUser==7){
        $personas = $personas;
    }elseif($lintLevelUser==20 || $lintLevelUser==21 || $lintLevelUser==22 || $lintLevelUser==23 || $lintLevelUser==24 || $lintLevelUser==25){
        $personas = $personas->whereHas('Contratospersonas', function ($query) use($lcontratos ) {
            $query->whereIn('contrato_id', $lcontratos);
        })
        ->orWhere('entry_by', $lintIdUser);
    }else{
         $personas = $personas->whereHas('Contratospersonas', function ($query) use($lcontratos ) {
                $query->whereIn('contrato_id', $lcontratos);
        })
        ->orWhere('entry_by_access', $lintIdUser);
    }
    $personas = $personas->get();
    $list=[];

    if($personas){
      foreach ($personas as $key => $persona) {
        $nombre = 'Sin nombre';
        $apellido = 'Sin apellido';
        $rol = "Sin rol";
        $ContratoNombre = 'Sin Asociar';
        $ContratoNum = 'Sin Asociar';
        $contratoId = NULL;
        if(isset($persona->Contratospersonas)){
          if(isset($persona->Contratospersonas->Rol)){
            $rol = $persona->Contratospersonas->Rol->Descripción;
          }
          if(isset($persona->Contratospersonas->contrato)){
            $ContratoNum = $persona->Contratospersonas->contrato->cont_numero;
            $ContratoNombre = $persona->Contratospersonas->contrato->cont_nombre;
            $contratoId = $persona->Contratospersonas->contrato->contrato_id;
          }
        }
        if(isset($persona->entryBy)){
          $nombre = $persona->entryBy->first_name;
          $apellido = $persona->entryBy->last_name;
        }
        $list[$key] = [
          'contrato_id'=>$contratoId,
          'IdPersona' => $persona->IdPersona,
          'Foto' => $persona->Foto,
          'ContratoNum' => strtoupper($ContratoNum),
          'ContratoNombre' => strtoupper($ContratoNombre),
          'RUT' => $persona->RUT,
          'Nombres'=> strtoupper($persona->Nombres),
          'Apellidos'=> strtoupper($persona->Apellidos),
          'Rol' => strtoupper($rol),
          'Nacimiento' => date('d/m/Y', strtotime($persona->FechaNacimiento)),
          'Nacionalidad' => strtoupper($persona->nacionalidad->nacionalidad),
          'createdOn' => date('d/m/Y', strtotime($persona->createdOn)),
          'updatedOn' => date('d/m/Y', strtotime($persona->updatedOn)),
          'discapacidad' => $persona->discapacidad == 1 ? 'SI' : 'NO',
          'pensionado' => $persona->pensionado == 1 ? 'SI' : 'NO',
          'info' => [
            'Direccion' => $persona->Direccion,
            'Sexo' => $persona->Sexo == 1 ? 'Hombre' : 'Mujer',
            'EstadoCivil' => self::estadoCivil($persona->EstadoCivil),
            'Estado' => $persona->IdEstatus == 1 ? 'Activo' : 'Inactivo',
            'entryBy' => $nombre.' '.$apellido,
            'paisTelefono_id' => $persona->paisTelefono_id,
            'codigoAreaTelefono_id'=>$persona->codigoAreaTelefono_id,
            'telefono'=>$persona->telefono,
            'contactoEmergencia'=>$persona->contactoEmergencia
          ]
        ];
      }
    }else{
      return response()->json(
        ['success' => false,
         'code' => 400,
         'documentos' => null,
         'message' => 'Error en la consulta, no se encontraron registros'
        ],400);
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

  static public function DatosHistorico($pintIdEntidad,$pintIdEstatus){

    // se inserta la data en la tabla de tbl_documentos_rep_historico
    $consulta = "INSERT INTO tbl_documentos_rep_historico(`IdDocumentoH`,`IdDocumento`,`IdTipoDocumento`,`Entidad`,`IdEntidad`,`Documento`,`DocumentoURL`,`FechaEmision`,`FechaAprobacion`,`FechaVencimiento`,`IdEstatus`,`IdEstatusDocumento`,`Resultado`,`load_by`,`approv_by`,`contrato_id`,`IdContratista`)
                              SELECT NULL as IdDocumentoH,
                                    IdDocumento,
                                    IdTipoDocumento,
                                    Entidad,
                                    IdEntidad,
                                    Documento,
                                    DocumentoURL,
                                    FechaEmision,
                  now() as FechaAprobacion,
                                    FechaVencimiento,
                                    '$pintIdEstatus' as IdEstatus,
                                    IdEstatusDocumento,
                                    Resultado,
                                    entry_by,
                                entry_by_access as approv_by,
                                    contrato_id,
                                    IdContratista
                              FROM tbl_documentos
                              WHERE IdDocumento='$pintIdEntidad'";
    \DB::insert($consulta);

    $consultaDV = "INSERT INTO tbl_documento_valor_historico(`id`,`IdDocumento`,`IdTipoDocumentoValor`,`Valor`,`idCargado`,`entry_by`,`entry_by_access`)
              SELECT NULL as id,
                            tbl_documento_valor.IdDocumento,
                            tbl_documento_valor.IdTipoDocumentoValor,
                            tbl_documento_valor.Valor,
                            tbl_documento_valor.idCargado,
                            tbl_documento_valor.entry_by,
                            tbl_documento_valor.entry_by_access
          FROM tbl_documento_valor
          INNER JOIN tbl_documentos on tbl_documento_valor.IdDocumento=tbl_documentos.IdDocumento
          WHERE tbl_documentos.IdDocumento='$pintIdEntidad'";

    \DB::insert($consultaDV);


    $consultaH = "INSERT INTO tbl_documentos_log_historico(`id`,`IdDocumento`,`IdAccion`,`DocumentoURL`,`observaciones`,`entry_by`,`createdOn`)
              SELECT NULL as id,
                tbl_documentos_log.IdDocumento,
                tbl_documentos_log.IdAccion,
                tbl_documentos_log.DocumentoURL,
                tbl_documentos_log.observaciones,
                tbl_documentos_log.entry_by,
                tbl_documentos_log.createdOn
          FROM tbl_documentos_log
          INNER JOIN tbl_documentos on tbl_documentos_log.IdDocumento=tbl_documentos.IdDocumento
          WHERE tbl_documentos.IdDocumento='$pintIdEntidad'";

    \DB::insert($consultaH);

    \DB::table('tbl_documentos_log')->where('IdDocumento', '=', $pintIdEntidad)->delete();
    \DB::table('tbl_documento_valor')->where('IdDocumento', '=', $pintIdEntidad)->delete();
    \DB::table('tbl_documentos')->where('IdDocumento', '=', $pintIdEntidad)->delete();
  }

  public function desasociarPersona(request $request){

    $array =  json_decode($request->getContent());
    if(count($array->persona_id) == 1 && count($array->contrato_id) == 1){
      $persona_id = $array->persona_id;
      $contrato_id = $array->contrato_id;

      \DB::beginTransaction();
      try{
        \DB::table('tbl_contratos_personas')->where('IdPersona',$persona_id)->where('contrato_id',$contrato_id)->delete();
        \DB::table('tbl_movimiento_personal')->where('IdPersona', $persona_id)->where('contrato_id',$contrato_id)->where('IdAccion',1)->delete();
        \DB::table('tbl_documentos')->where('Entidad',3)->where('IdEntidad', $persona_id)->where('contrato_id',$contrato_id)->delete();
        \DB::table('tbl_documentos_rep_historico')->where('Entidad','=',3)
        ->where('IdEntidad', $persona_id)
        ->where('contrato_id', $contrato_id)->delete();
        \DB::table('tbl_personas_maestro_movil')->where('idpersona', $persona_id)->where('contrato_id',$contrato_id)->delete();
        \DB::table('tbl_personas_acreditacion')->where('IdPersona', $persona_id)
        ->where('contrato_id',$contrato_id)->delete();
        \DB::table('tbl_personas')->where('IdPersona', $persona_id)
                                    ->update(['entry_by' => 1,'entry_by_access'=> 0]);
        $persona = \DB::table('tbl_personas')->where('IdPersona', $persona_id)->first();
        \DB::table('tbl_trabajador_eliminado_registro')->insert(['IdPersona'=> $persona_id,'contrato_id'=>$contrato_id,'eliminated_at'=>date('Y-m-d H:i'),'RUT'=>$persona->RUT]);

        \DB::commit();

        return response()->json(array(
          'status'=>'success',
          'message'=> 'Desasociado exitosamente!!!')
        );
      }catch(\Exception $e){
        DB::rollback();
        return response()->json(array(
          'status'=>'error',
          'message'=> 'No se ha podido Desasociar')
        );
      }
    } else {
      return response()->json(array(
        'status'=>'error',
        'message'=> 'No se ha podido Desasociar')
      );
    }
  }

  function valida_rut($rut)
      {
      if(strlen($rut) > 10)
      {
      return false;
      }

      if(strpos($rut, '-') === false)
      {
      return false;
      }

      $array_rut_sin_guion = explode('-',$rut); // separamos el la cadena del digito verificador.
      $rut_sin_guion = $array_rut_sin_guion[0]; // la primera cadena
      $digito_verificador = $array_rut_sin_guion[1];// el digito despues del guion.

      if(is_numeric($rut_sin_guion)== false)
      {
        return false;
      }
      if ($digito_verificador != 'k' and $digito_verificador != 'K')
      {
          if(is_numeric($digito_verificador)== false)
            {
            return false;
            }
      }
      $cantidad = strlen($rut_sin_guion); //8 o 7 elementos
      for ( $i = 0; $i < $cantidad; $i++)//pasamos el rut sin guion a un vector
          {
          $rut_array[$i] = $rut_sin_guion{$i};
      }

      $i = ($cantidad-1);
      $x=$i;
      for ($ib = 0; $ib < $cantidad; $ib++)// ingresamos los elementos del ventor rut_array en otro vector pero al reves.
          {
          $rut_reverse[$ib]= $rut_array[$i];

          $rut_reverse[$ib];
      $i=$i-1;
          }

        $i=2;
        $ib=0;
        $acum=0;
        do
          {
      if( $i > 7 )
        {
        $i=2;
        }
        $acum = $acum + ($rut_reverse[$ib]*$i);
        $i++;
        $ib++;
        }while ( $ib <= $x);

      $resto = $acum%11;
      $resultado = 11-$resto;
      if ($resultado == 11) { $resultado=0; }
      if ($resultado == 10) { $resultado='k'; }
      if ($digito_verificador == 'k' or $digito_verificador =='K') { $digito_verificador='k';}

      if ($resultado == $digito_verificador)
          {
      return true;
      }
      else
      {
      return false;
      }
      }

  public function postSave( Request $request, $id =0){

    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	  $lintIdUser = \Session::get('uid');
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);
		$pintIdTipoIdentificacion =  $request->IdTipoIdentificacion;
		if (isset($request->FechaNacimiento)) {  $_POST['FechaNacimiento'] = self::FormatoFecha($_POST['FechaNacimiento']);   }
		if ($validator->passes()) {
			$data = $this->validatePost('tbl_personas');
      $fechaNac = $data['FechaNacimiento'];
      $yearActual = date('Y');
      $mesActual = date('m');
      $diaActual = date('d');
      $year  = substr($fechaNac,0,4);
      $month = substr($fechaNac,5,2);
      $day   = substr($fechaNac,8);
      $calculo1 = $year + $month + $day; // sumamos año,mes y dia de Nacimiento
      $calculo2 = $yearActual + $month + $day; // sumamos año actual + mes de Nac y dia de Nac
      $edad = $calculo2 - $calculo1;
      if($mesActual<$month){
        $edad =$edad -1;
      }
      if($mesActual==$month){
        if($diaActual<$day){
        $edad =$edad -1;
        }
      }

			//Validamos en que momento debemos actualizar o no el entry_by_access
			if ($lintLevelUser==6 || $lintLevelUser==4){
				if (empty($data['entry_by_access'])) {
					$data['entry_by_access'] = $lintIdUser;
				}
			}

			if (!isset($data['Sexo'])) {
				$data['Sexo'] = 0;
			}
			if (!isset($data['EstadoCivil'])) {
				$data['EstadoCivil'] = 0;
			}
      // Validamos la edad si es mayor a 18 años
      if($edad < 18) {
        return response()->json(array(
          'message'	=> "La edad de esta persona debe ser mayor a 18 años",
          'status'	=> 'error'
      ));
      }
        //Validamos el RUT
			if (isset($data['RUT']) && $pintIdTipoIdentificacion == 1) {
         $validoRut = self::valida_rut($data['RUT']);
         if (self::valida_rut($data['RUT'])== false)
            {
              return response()->json(array(
                'message'	=> "El Rut es Incorrecto, recuerde colocar el guion seguido el digito verificador",
                'status'	=> 'error'
            ));
            }
			}
			if(!isset($data['paisTelefono_id'])){
				$data['paisTelefono_id'] = $request->paisTelefono_id;
			}
			if(!isset($data['codigoAreaTelefono_id'])){
				$data['codigoAreaTelefono_id'] = $request->codigoAreaTelefono_id;
			}
			if(!isset($data['telefono'])){
				$data['telefono'] = $request->telefono;
			}
			if(!isset($data['contactoEmergencia'])){
				$data['contactoEmergencia'] = $request->contactoEmergencia;
			}

      if(empty($data['id_Nac'])){
				$data['id_Nac'] = $request->pais;
			}
      if(empty($data['entry_by'])){
				$data['entry_by'] = $request->entry_by_access;
			}
      $data['createdOn']= date('Y-m-d');
      $data['updatedOn']= date('Y-m-d');

      $lobjPersona = \DB::table("tbl_personas")
          ->where("IdPersona","!=",$request->IdPersona)
          ->where("IdTipoIdentificacion","=",$pintIdTipoIdentificacion)
          ->where("Rut","=",$data['RUT'])
          ->get();
      if ($lobjPersona){
          return response()->json(array(
              'message'	=> "Esta identificación ya la tiene otra persona",
              'status'	=> 'error'
          ));
      }


			$id = $this->model->insertRow($data , $request->input('IdPersona'));

			/* +++++++++++++++++++  Modulo de Discapacidad +++++++++++++++++++*/
			//se obtiene el id de tipo de documento con id proceso 142
			$idTipoDocumento = \DB::table('tbl_tipos_documentos')->where('IdProceso',142)->first();
			if($idTipoDocumento){
				$idTipoDocumento = $idTipoDocumento->IdTipoDocumento;
				$discapacidad = self::LevantarDocumentosPersonas($data['discapacidad'],$request->input('IdPersona'),$idTipoDocumento, 142);
			}
			/* +++++++++++++++++++ END Modulo de Discapacidad +++++++++++++++++++*/

			/* +++++++++++++++++++  Modulo de Pensionado +++++++++++++++++++*/
			//se obtiene el id de tipo de documento con id proceso 143
			$idTipoDocumento2 = \DB::table('tbl_tipos_documentos')->where('IdProceso',143)->first();
			if($idTipoDocumento2){

				$idTipoDocumento2 = $idTipoDocumento2->IdTipoDocumento;

				$pensionado = self::LevantarDocumentosPersonas($data['pensionado'],$request->input('IdPersona'),$idTipoDocumento2, 143);
			}
			/* +++++++++++++++++++ END Modulo de Pensionado +++++++++++++++++++*/

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

  private function FormatoFecha($pstrFecha){
    if ($pstrFecha){
      $larrFecha = explode("/", $pstrFecha);
      return $larrFecha[2].'-'.$larrFecha[1].'-'.$larrFecha[0];
    }
  }

  public function LevantarDocumentosPersonas($valor,$IdPersona,$idTipoDocumento,$idProceso){
  	if($valor == 1){
  		//vemos si la persona esta asignada a un contrato y en ese caso obtenemos los datos del contrato y del contratista
  		$contratoPersonas = \DB::table('tbl_contratos_personas')->where('IdPersona', $IdPersona)->first();
  		if($contratoPersonas){
  			$idContratista = $contratoPersonas->IdContratista;
  			$idContrato = $contratoPersonas->contrato_id;
  		}else{
  			$idContratista = "";
  			$idContrato = "";
  		}
  		//se valida si el documento existe
  		$existeDoc = \DB::table('tbl_documentos')
  						->where('IdEntidad', $IdPersona)
  						->where('Entidad', 3)
  						->where('IdTipoDocumento', $idTipoDocumento)
  						->first();
  		if(!$existeDoc){
  			$existeDocH = \DB::table('tbl_documentos_rep_historico')
  						->where('IdEntidad', $IdPersona)
  						->where('Entidad', 3)
  						->where('IdTipoDocumento', $idTipoDocumento)
  						->first();

  			if($existeDocH){
  				//si el documento es del mismo contrato lo pasamos a documentos sino simplemente lo eliminamos y creamos otra solicitud
  				if($idContrato == $existeDocH->contrato_id){
  					//Pasar documento del historico a la tabla tbl_documentos
  					$lobjDocumentos = new MyDocuments();
  					$larrResultado = $lobjDocumentos::FromStore($existeDocH->IdDocumento);
  					if($idProceso == 143){
  						$existePensionado = \DB::table('tbl_pensionados')->where('IdPersona',$IdPersona)->first();
  						if($existePensionado){
  							$existePensionado->contrato_id = $idContrato;
  							$existePensionado->IdContratista = $idContratista;
  							$existePensionado->save();
  						}else{
  							\DB::table('tbl_pensionados')->insert([
  								'IdPersona' => $IdPersona,
  								'contrato_id' => $idContrato,
  								'IdContratista' => $idContratista,
  								'Fecha'	 => date('Y-m-d H:i:s')
  							]);
  						}
  					}
  				}else{
  					//eliminamos el documento
  					\DB::table('tbl_documentos_rep_historico')->where('IdDocumentoH',$existeDocH->IdDocumentoH)->delete();
  					//creamos una nueva solicitud
  					$lobjDocumentos = new MyDocuments();
  					$larrResultado = $lobjDocumentos::Save($idTipoDocumento,3,$IdPersona,"",$idContratista, $idContrato);
  				}
  			}else{
  				//sino esta se crea un nuevo documento
  				$lobjDocumentos = new MyDocuments();
  				$larrResultado = $lobjDocumentos::Save($idTipoDocumento,3,$IdPersona,"",$idContratista, $idContrato);
  			}
  		}
  	}else{
  		//si el documento esta y se le quita el check se envia al historico.
  		$existeDoc = \DB::table('tbl_documentos')
  						->where('IdEntidad', $IdPersona)
  						->where('Entidad', 3)
  						->where('IdTipoDocumento', $idTipoDocumento)
  						->first();
  		if($existeDoc){
  			$existeDocA = \DB::table('tbl_documentos')
  						->where('IdEntidad', $IdPersona)
  						->where('Entidad', 3)
  						->where('IdTipoDocumento', $idTipoDocumento)
  						->where('IdEstatus', 5)
  						->first();
  			if($existeDocA){
  				$lobjDocumentos = new MyDocuments($existeDoc->IdDocumento);
  				$larrResultado = $lobjDocumentos::Store();
  			}else{
  				\DB::table('tbl_documentos')->where('IdDocumento',$existeDoc->IdDocumento)->delete();
  			}
  		}
  		if($idProceso == 143){
  			$existePensionado = \DB::table('tbl_pensionados')->where('IdPersona',$IdPersona)->delete();
  		}
  	}
  }

  static public function UpdateMaestroMovil($pintIdPersona, $pintIdContrato, $pdatFechaEfectiva, $strAccion, $estado) {

    $lintIdContratista = \DB::table('tbl_contrato')->where('contrato_id',$pintIdContrato)->value('IdContratista');
    $lintContratoPrueba = \DB::table('tbl_contrato')->where('contrato_id',$pintIdContrato)->value('ContratoPrueba');

    //ignora contratos de prueba
    if($lintContratoPrueba==0){
      $periodo = new \DateTime($pdatFechaEfectiva);
      if($pdatFechaEfectiva>$periodo->format('Y-m-01')){
        $periodo = $periodo->modify('+1 month');
      }
      $periodo = $periodo->format('Y-m-01');
      $periodoOriginal = $periodo;
      $hoy = new \DateTime();
      $hoy = $hoy->format('Y-m-01');

      $pmv =\DB::table('tbl_personas_maestro_movil')
              ->where('idpersona',$pintIdPersona)
              ->where('contrato_id',$pintIdContrato)
              ->where('idcontratista',$lintIdContratista);

      if($strAccion=='LeaveContract'){
        $ldatFechaEfectiva = $pmv->where('periodo',$periodo)->value('FechaEfectiva');
        $leaveUpdate = $pmv->where('periodo',$periodo)->update(['Estatus'=>$estado,'updated_at'=>date('Y-m-d H:i'),'FechaEfectiva'=>$pdatFechaEfectiva,'FechaAnterior'=>$ldatFechaEfectiva,'FechaFinFaena'=>$ldatFechaEfectiva]);

        if($periodo<$hoy){
          do{
            $periodo = new \DateTime($periodo);
            $periodo = $periodo->modify('+1 month');
            $periodo = $periodo->format('Y-m-01');
            \DB::table('tbl_personas_maestro_movil')
                    ->where('idpersona',$pintIdPersona)
                    ->where('contrato_id',$pintIdContrato)
                    ->where('idcontratista',$lintIdContratista)
                    ->where('periodo',$periodo)
                    ->update(['Estatus'=>$estado,'updated_at'=>date('Y-m-d H:i'),'FechaFinFaena'=>$ldatFechaEfectiva]);
            }while($periodo<$hoy);
        }
      }

      if($strAccion=='LeaveContractApproved'){
        $leaveUpdate = $pmv->where('periodo',$periodo)->update(['Estatus'=>$estado,'updated_at'=>date('Y-m-d H:i')]);
        if($periodo<$hoy){
          do{
            $periodo = new \DateTime($periodo);
            $periodo = $periodo->modify('+1 month');
            $periodo = $periodo->format('Y-m-01');
            \DB::table('tbl_personas_maestro_movil')
                    ->where('idpersona',$pintIdPersona)
                    ->where('contrato_id',$pintIdContrato)
                    ->where('idcontratista',$lintIdContratista)
                    ->where('periodo',$periodo)
                    ->update(['Estatus'=>$estado,'updated_at'=>date('Y-m-d H:i')]);
          }while($periodo<$hoy);
        }
      }

      if($strAccion=='LeaveContractApprovedFinish'){
        $leaveUpdate = $pmv->where('periodo',$periodo)->update(['Estatus'=>$estado,'updated_at'=>date('Y-m-d H:i')]);
        $periodo = new \DateTime($periodo);
        $periodo = $periodo->modify('+1 month');
        $periodo = $periodo->format('Y-m-01');
        \DB::table('tbl_personas_maestro_movil')
                ->where('idpersona',$pintIdPersona)
                ->where('contrato_id',$pintIdContrato)
                ->where('idcontratista',$lintIdContratista)
                ->where('periodo',$periodo)
                ->delete();
        if($periodo<$hoy){
          do{
            $periodo = new \DateTime($periodo);
            $periodo = $periodo->modify('+1 month');
            $periodo = $periodo->format('Y-m-01');
            \DB::table('tbl_personas_maestro_movil')
                    ->where('idpersona',$pintIdPersona)
                    ->where('contrato_id',$pintIdContrato)
                    ->where('idcontratista',$lintIdContratista)
                    ->where('periodo',$periodo)
                    ->delete();
          }while($periodo<$hoy);
        }
      }

      if($strAccion=='AssignContract'){
        if($periodo<$hoy){
          do{
            \DB::table('tbl_personas_maestro_movil')->insert([
              'periodo'=>$periodo,
              'idpersona'=>$pintIdPersona,
              'contrato_id'=>$pintIdContrato,
              'idcontratista'=>$lintIdContratista,
              'created_at'=>date('Y-m-d H:i'),
              'Estatus'=>$estado,
              'FechaEfectiva'=>$pdatFechaEfectiva,
              'FechaInicioFaena'=>$pdatFechaEfectiva
            ]);
            $periodo = new \DateTime($periodo);
            $periodo = $periodo->modify('+1 month');
            $periodo = $periodo->format('Y-m-01');
            }while($periodo<$hoy);
        }else{
          \DB::table('tbl_personas_maestro_movil')->insert([
            'periodo'=>$periodo,
            'idpersona'=>$pintIdPersona,
            'contrato_id'=>$pintIdContrato,
            'idcontratista'=>$lintIdContratista,
            'created_at'=>date('Y-m-d H:i'),
            'Estatus'=>$estado,
            'FechaEfectiva'=>$hoy,
            'FechaInicioFaena'=>$pdatFechaEfectiva
          ]);
        }
        $cliente = \DB::table('tbl_configuraciones')->where('nombre','CNF_APPNAME')->first();
        if($cliente->Valor != 'Transbank'){
          \DB::table('tbl_documentos')
            ->join('tbl_tipos_documentos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_documentos.IdTipoDocumento')
            ->where('tbl_documentos.contrato_id',$pintIdContrato)
            ->where('tbl_documentos.IdEstatus',5)
            ->where('tbl_tipos_documentos.RelacionPersona',1)
            ->where('tbl_documentos.FechaEmision',$periodoOriginal)
            ->update(['tbl_documentos.IdEstatus'=>3,'Resultado'=>'posterior a la aprobación de este documento se modificó la nómina del personal para este mes, por lo tanto debe volver a proporcionar documento, asegurándose que incluya a toda la nómina para el periodo']);
        }
      }
    }

  }

  static public function RegisterMovePeople($pintIdPersona, $pintIdMovimiento, $pintIdContrato, $pdatFechaEfectiva = null, $pintMotivo = 0) {

    if($pdatFechaEfectiva==null){
      $pdatFechaEfectiva=date('Y-m-d');
    }
    $lintIdMovimientoPersona = \DB::table('tbl_movimiento_personal')
    ->insertGetId([
      "IdAccion" => $pintIdMovimiento,
      "contrato_id" => $pintIdContrato,
      "IdPersona" => $pintIdPersona,
      "FechaEfectiva" => $pdatFechaEfectiva,
      "Motivo" => $pintMotivo,
      "entry_by" => \Session::get('uid')]
    );
  }

  static public function ResetDocumento($pintIdPersona, $pintConPermanencia=1, $pstrContrato = ''){

    $lobjPersona = \DB::table('tbl_personas')
    ->where("tbl_personas.IdPersona","=",$pintIdPersona)
    ->first();
    if ($lobjPersona){

      //Eliminamos los documentos valores de los documentos que no importan
      $lobjDocumentosValorDistintos = \DB::table('tbl_documento_valor')
      ->whereExists(function($query) use ($pintIdPersona) {
        $query->select(\DB::raw(1))
              ->from('tbl_documentos')
              ->whereraw('tbl_documentos.iddocumento = tbl_documento_valor.iddocumento')
              ->whereraw('tbl_documentos.Entidad = 3')
              ->whereraw('tbl_documentos.IdEntidad = '.$pintIdPersona)
              ->whereraw('tbl_documentos.IdEstatus != 5')
              ->whereraw('exists (select 1 from tbl_tipos_documentos where tbl_documentos.IdTipoDocumento = tbl_tipos_documentos.IdTipoDocumento and ifnull(tbl_tipos_documentos.ControlCheckLaboral,0) != 1)');
      })->delete();

      if($pintConPermanencia!=3){
        $lobjDocumentosDistintos = \DB::table('tbl_documentos')
        ->where("tbl_documentos.IdEntidad","=",$pintIdPersona)
        ->where("tbl_documentos.Entidad","=",3)
        ->where("tbl_documentos.IdEstatus","!=",5)
        ->whereExists(function($query) {
            $query->select(\DB::raw(1))
            ->from('tbl_tipos_documentos')
            ->whereraw('tbl_documentos.IdTipoDocumento = tbl_tipos_documentos.IdTipoDocumento')
            ->whereraw('ifnull(tbl_tipos_documentos.ControlCheckLaboral,0) != 1');
        })
        ->delete();
      }

      $lobjDocumentos = \DB::table('tbl_documentos')
      ->where("tbl_documentos.IdEntidad","=",$pintIdPersona)
      ->where("tbl_documentos.Entidad","=",3);
      if ($pintConPermanencia==1){
        $lobjDocumentos->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('tbl_tipos_documentos')
                      ->whereRaw('tbl_tipos_documentos.idtipodocumento = tbl_documentos.idtipodocumento')
                      ->whereRaw('tbl_tipos_documentos.Permanencia = 0');
        });

      }elseif ($pintConPermanencia==2){
        $lobjDocumentos->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('tbl_tipos_documentos')
                      ->whereRaw('tbl_tipos_documentos.idtipodocumento = tbl_documentos.idtipodocumento')
                      ->whereRaw('tbl_tipos_documentos.ControlCheckLaboral != 1');
        });

      }else if($pintConPermanencia==3){
        $lobjDocumentos->where("tbl_documentos.IdEstatus",5);
        $lobjDocumentos->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('tbl_tipos_documentos')
                      ->whereRaw('tbl_tipos_documentos.idtipodocumento = tbl_documentos.idtipodocumento');
        });
      }
      if ($pstrContrato) {
        $lobjDocumentos->whereRaw("tbl_documentos.IdTipoDocumento NOT IN (SELECT tbl_tipos_documentos.IdTipoDocumento from tbl_tipos_documentos where tbl_tipos_documentos.IdProceso = ".$pstrContrato.")");
      }


        $lobjDataDoc = $lobjDocumentos->get();


        if (count($lobjDataDoc)>0){

            foreach ($lobjDataDoc as $larrDocumentoR) {

              self::DatosHistorico($larrDocumentoR->IdDocumento,5);
            }
        }

    }else{
      return response()->json(array(
        'status'=>'error',
        'message'=> 'No existe la Persona')
      );
    }

  }

  static public function desvinculaDesasociaPersona(request $request){

    $data =  json_decode($request->getContent());

    $lobjContrato = \DB::table("tbl_contratos_personas")
    ->select("tbl_contrato.IdContratista","tbl_contrato.contrato_id", "tbl_contratos_personas.IdPersona", "tbl_contratos_personas.IdRol", "tbl_contratos_personas.createdOn", "tbl_contrato.entry_by_access")
    ->join("tbl_contrato","tbl_contrato.contrato_id","=","tbl_contratos_personas.contrato_id")
    ->where("tbl_contratos_personas.IdPersona","=",$data->persona_id);
    if (!empty($data->contrato_id)) {
      $lobjContrato = $lobjContrato->where("tbl_contratos_personas.contrato_id","=",$data->contrato_id);
    }
    $consulta = $lobjContrato->first();

    $FechaActual = date('Y-m-d h:i:s');
    $FechaEmision = date('Y-m')."-01";
    $IdUsuario = \Session::get('uid');
    $pdatFechaEfectiva = isset($data->fechaEfectiva) ? (new \DateTime($data->fechaEfectiva))->format('Y-m-d') : '0000-00-00';
    $pintIdAnotacion = $data->anotacionConcepto_id;

    if ($consulta){ // Verificamos si la persona tiene contrato

      $lintIdContrato = $consulta->contrato_id;
      $lintIdContratista = $consulta->IdContratista;
      $lintEntryBy = $consulta->entry_by_access;

      \DB::beginTransaction();

      self::UpdateMaestroMovil($data->persona_id,$lintIdContrato,$pdatFechaEfectiva,'LeaveContract','Baja Observada');

      //Resolvemos que hacer con los documentos
      //se incluye opcion 3 para pasar todos al historico menos el contrato de trabajo
      $larrResultDocument = self::ResetDocumento($data->persona_id,3,"21");

      //Insertamos el finiquito del trabajador
      $lobjMyDocuments = new MyDocumentsSettlementPerson();
      $lobjMyDocuments::save("","",$data->persona_id, "", $lintIdContratista, $lintIdContrato, $pdatFechaEfectiva, $pintIdAnotacion);

      //Registramos la salida de la persona del contrato
      $lintResultadoRegister = self::RegisterMovePeople($data->persona_id, 2, $lintIdContrato, $pdatFechaEfectiva);

      //Eliminamos el registro de la persona
      $lintResultado = \DB::table('tbl_contratos_personas')
      ->where("tbl_contratos_personas.IdPersona","=",$data->persona_id)
      ->where("tbl_contratos_personas.contrato_id","=",$lintIdContrato)
      ->delete();

      //Quitamos relacion de la persona
      $lintResultadoUpdatePersona = \DB::table("tbl_personas")
      ->where("tbl_personas.IdPersona","=",$data->persona_id)
      ->update(array("entry_by_access"=>0, "discapacidad"=>0, "pensionado"=>0));

      //Asociamos la anotacion a la persona
      $lintResultadoAnotacion = \DB::table("tbl_anotaciones")
      ->insertGetId(array("IdConceptoAnotacion"=>$pintIdAnotacion, "IdPersona" => $data->persona_id, "entry_by"=>$IdUsuario , "entry_by_access"=>0, "createdOn" => $FechaActual));

      //Verificamos si afecta al maestro
      $lobjMyCheckLaboral = new MyCheckLaboral();
      $lobjMyCheckLaboral::LeavePeople($data->persona_id,$lintIdContratista,$lintIdContrato, $pdatFechaEfectiva);

      //eliminamos el documento de discapacidad y el de pensionado si no esta en estatus aprobado

      $lobjDocumentosValorDistintos = \DB::table('tbl_documentos')
       ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
       ->where('tbl_documentos.Entidad', 3)
       ->where('tbl_documentos.IdEntidad', $data->persona_id)
       ->where('tbl_documentos.IdEstatus','!=', 5)
       ->whereIn('tbl_tipos_documentos.IdProceso', [142,143])
       ->delete();

      //si la persona esta en la tabla pensionados la borramos
      $existePensionado = \DB::table('tbl_pensionados')->where('IdPersona',$data->persona_id)->delete();

      \DB::commit();

      return response()->json(array(
        'status'=>'success',
        'message'=> 'Persona desvinculada exitosamente!!!')
      );

    }else{
      return response()->json(array(
        'status'=>'error',
        'message'=> 'La persona no esta asignada a ningun contrato')
      );
    }
  }

  public function getShowdocuments(request $request){

    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $IdUser = \Session::get('uid');
    $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
    $lcontratos = explode(',',$lobjFiltro['contratos']);
    $IdPersona = $request->input('persona_id');
    $persona = Personas::where('IdPersona', $IdPersona)->first();
    if($persona){

      $documentos = $persona->documentos()
                  ->whereIn('contrato_id', $lcontratos)
                  ->when($lintLevelUser == 1, function ($query){
                    return $query->where(function ($query) {
                        $query->where("IdEstatus", "!=", 5)
                          ->orwhere(function ($query) {
                            $query->where("IdEstatus", "=", 5)
                                ->where("IdEstatusDocumento", "!=", "1");

                          })
                          ->orwhere(function ($query) {
                            $query->where("IdEstatus", 5)
                                  ->where("Vencimiento", 1)
                                  ->whereHas('TipoDocumento',function ($query) {
                                      $query->where('Vigencia', 1);
                                  });
                          });
                    });
                  })
                  ->when($lintLevelUser != 1, function ($query){
                    return $query->whereNotIn('IdEstatus',[5,7]);
                      $query->where("IdEstatus", "!=", 5)
                      ->orwhere(function ($query) {
                        $query->where("IdEstatus", "=", 5)
                            ->where("IdEstatusDocumento", "!=", "1");

                      })
                      ->orwhere(function ($query) {
                        $query->where("IdEstatus", 5)
                              ->where("Vencimiento", 1)
                              ->whereHas('TipoDocumento',function ($query) {
                                  $query->where('Vigencia', 1);
                              });
                      });
                  })
                  ->whereExists(function ($query) {
                    $lintGroupUser = \MySourcing::GroupUser(\Session::get('uid'));
                      $query->select(\DB::raw(1))
                            ->from('tbl_tipo_documento_perfil')
                            ->whereRaw('tbl_tipo_documento_perfil.IdPerfil = '.$lintGroupUser)
                            ->whereRaw('tbl_tipo_documento_perfil.IdTipoDocumento = tbl_documentos.IdTipoDocumento');
                  })
                  ->get();

      $list=[];
      $i = 0;
      $fechaActual = Carbon::now();
      if($documentos){
        foreach ($documentos as $key => $documento) {
          $flag = false;
          $periodo = $documento->FechaEmision ? \MyFormats::FormatDate($documento->FechaEmision) : null;
          $vencimiento = $documento->FechaVencimiento ? \MyFormats::FormatDate($documento->FechaVencimiento) : null;
          $DiasParaVencimiento = Carbon::parse($documento->FechaVencimiento)->addDays(1)->diffInDays($fechaActual);
          if($documento->IdEstatus == 5){
            if($documento->TipoDocumento->Vigencia == 1){
              if($documento->Vencimiento == 1){
                if($DiasParaVencimiento < $documento->TipoDocumento->DiasVencimiento){
                  $flag = true;
                  $action = 'porVencer';
                }elseif($documento->IdEstatusDocumento == 2){
                  $flag = true;
                  $action = 'vencido';
                }
              }
            }
          }elseif($documento->IdEstatus == 1){
            $flag = true;
            $action = 'cargar';
          }elseif($documento->IdEstatus == 7){
            $flag = true;
            $action = 'asociar';
          }elseif($documento->IdEstatus == 3){
            $flag = true;
            $action = 'rechazado';
          }
          if($flag){
            $list[$i] = [
              'IdDocumento' => $documento->IdDocumento,
              'Documento' => $documento->TipoDocumento->Descripcion,
              'Periodo' => $periodo,
              'Vencimiento' => $vencimiento,
              'Estatus' => $documento->estatu->Descripcion,
              'IdEstatus' => $documento->IdEstatus,
              'action'=> $action
            ];
            $i++;
          }
        }
        return response()->json([
          'success'=> true,
          'code'=> 200,
          'documentos' => $list,
          'message' => 'ok'
        ],200);
      }else{
        return response()->json(
          ['success' => false,
            'code' => 400,
            'documentos' => null,
            'message' => 'Error en la consulta'
          ],400);
      }

    }else{
      return response()->json(
        ['success' => false,
          'code' => 400,
          'documentos' => null,
          'message' => 'Error en la consulta, no se encontraron registros de la persona'
        ],400);
    }
  }

  public function estadoCivil($estadoCivil){

    $estados =[ 1 => 'Soltero', 2 => 'Casado', 3 => 'Divorciado', 4 => 'Viudo'];
    $value = array_get($estados, $estadoCivil);
    return $value;
  }

  public function getDataCambioContractual(request $request){

    $lintIdUser = \Session::get('uid');
    $lintGroupUser = \MySourcing::GroupUser($lintIdUser);
    $lintLevelUser = \MySourcing::LevelUser($lintIdUser);
    $lobjFiltro = \MySourcing::getFiltroUsuario(1,1);
    $lcontratos = explode(',',$lobjFiltro['contratos']);
    $contratoPersona = Contratospersonas::where('IdPersona', $request->persona_id )->first();
    if($contratoPersona){
      $idContratista = $contratoPersona->IdContratista;
      $contratos = Contratos::whereIn('contrato_id', $lcontratos)->where('IdContratista', $idContratista)->select('contrato_id', 'cont_numero', 'cont_nombre' )->get();
      $roles = Roles::select('IdRol','Descripción')->get();
      $tiposContratos = Tiposcontratospersonas::select('id','Nombre')->get();
      $otrosAnexos = OtrosAnexos::select('id','anexo')->get();

      $data = [
        'contratos' => $contratos,
        'tiposContratos' => $tiposContratos,
        'roles' => $roles,
        'otrosAnexos' => $otrosAnexos,
        'fechaVencimiento'  => $contratoPersona->FechaVencimiento,
        'FechaInicioFaena'  => $contratoPersona->FechaInicioFaena,
        'dataActual' => [
          'contrato' => $contratoPersona->contrato_id,
          'tipoContrato' => $contratoPersona->IdTipoContrato,
          'rol' => $contratoPersona->IdRol
        ]
      ];
        return response()->json([
        'success'=> true,
        'code'=> 200,
        'cambios' => $data,
        'message' => 'ok'
      ],200);

    }else{

      return response()->json([
        'success'=> true,
        'code'=> 200,
        'cambios' => Null,
        'message' => 'La persona no esta asignada a un contrato'
      ],400);
    }
  }

  public function postCambiosContractual(request $request){

    $data =  json_decode($request->getContent());

    if($data){
      $idPersona = $data->persona_id;
      $persona = $this->getObjetoPersona($idPersona);
      if($persona){
        $contratoPersona = $this->getObjetoContratoPersona($idPersona);
        if($contratoPersona){
          $larrCambios = [];
          $lbolGeneraAnexo = false;
          $fechaCambio = isset($data->contrato_fecha_change) ? $this->ponerFormatoFecha('d/m/Y', $data->contrato_fecha_change) : null;
          $idContratoNuevo = isset($data->contrato_id_change) ? $data->contrato_id_change : null;
          $idTipoContratoNuevo = isset($data->idtipocontrato_change) ? $data->idtipocontrato_change : null;
          $fechaVencimientoNuevo = isset($data->fechavencimiento_change) ?  $this->ponerFormatoFecha('d/m/Y', $data->fechavencimiento_change) : null;
          $idRolNuevo = isset($data->idrol_change) ? $data->idrol_change : null;
          $idOtrosAnexos = isset($data->otrostipos_change) ? $data->otrostipos_change : null;
          $otros = isset($data->otros_change) ? $data->otros_change : null;
          $fechaInicioFaenaNueva = isset($data->fechainiciofaena_change) ? $this->ponerFormatoFecha('d/m/Y', $data->fechainiciofaena_change) : null;
          $cambioInicioFaena = $fechaInicioFaenaNueva ? true : false;
          $lobjConfiguracion = $this->configuracionAnexo($contratoPersona->contrato_id);
          $extensionTipoContrato = $this->extensionTipoContrato($contratoPersona->IdTipoContrato,$lobjConfiguracion->id_extension);
          //cambio de contrato
          if ($contratoPersona->contrato_id != $idContratoNuevo && $idContratoNuevo){
            $larrCambios['contrato_id'] = $idContratoNuevo;
            $larrCambios['fecha_cambio'] = $fechaCambio;
            if ($lobjConfiguracion->CambioContrato){
              if ($extensionTipoContrato){
                $lbolGeneraAnexo = true;
              }
            }
          }
          //cambio de tipo de contrato
          //TODO: esto colocarlo al aprobar $docsVencidos = \DB::table('tbl_documentos')->where('Vencimiento',1)->where('Entidad',3)->where('IdEntidad',$lobjDatosContractuales->IdPersona)->where('contrato_id',$lobjDatosContractuales->contrato_id)->update(['Vencimiento'=>0]);
          if ($contratoPersona->IdTipoContrato != $idTipoContratoNuevo && $idTipoContratoNuevo){
            $larrCambios['IdTipoContrato'] = $idTipoContratoNuevo;
            if ($lobjConfiguracion && $lobjConfiguracion->CambioTipoContrato){
              $lbolGeneraAnexo = true;
            }
          }

          //fecha de vencimiento
          if ($contratoPersona->FechaVencimiento != $fechaVencimientoNuevo && $fechaVencimientoNuevo){
            $larrCambios['FechaVencimiento'] = $fechaVencimientoNuevo;
            if ($lobjConfiguracion->CambioFecha){
              $lbolGeneraAnexo = true;
            }
          }
          if ($contratoPersona->IdRol != $idRolNuevo && $idRolNuevo){
            $larrCambios['IdRol'] = $idRolNuevo;
            if ($lobjConfiguracion->CambioRol){
              $lbolGeneraAnexo = true;
            }
          }
          if ($idOtrosAnexos){
            $larrCambios['OtrosAnexos'] = $idOtrosAnexos;
            $lbolGeneraAnexo = true;
          }
          if ($otros){
            $larrCambios['Otros'] = $otros;
            $lbolGeneraAnexo = true;
          }
          if($cambioInicioFaena){
            if($fechaInicioFaenaNueva && $fechaInicioFaenaNueva != $contratoPersona->FechaInicioFaena){

              if(self::cambioFechaInicioFaena($contratoPersona, $fechaInicioFaenaNueva)){
                return response()->json([
                  'success'=> true,
                  'code'=> 200,
                  'message' => 'Se ha cambiado la fecha de inicio de faena correctamente.'
                ],200);
              }else{
                return response()->json([
                  'success'=> false,
                  'code'=> 400,
                  'cambios' => Null,
                  'message' => 'Error no se pudo hacer el cambio de inicio de faena.'
                ],400);
              }
            }else{
              return response()->json([
                'success'=> true,
                'code'=> 200,
                'message' => 'La fecha de cambio de inicio de faena es la misma a la anterior.'
              ],200);
            }
          }
          if($lbolGeneraAnexo){
            $persona->DocumentoContractual->DocumentosRelacionados()->where('IdEstatus','!=',5)->delete();
            $lobjMyDocumentAnexo = new MyDocumentsContractPersonAnnexed();
            $lstrResult = $lobjMyDocumentAnexo::Create($persona, $larrCambios);
          }
          return response()->json([
            'success'=> true,
            'code'=> 200,
            'message' => 'Se ha generado el Anexo correctamente'
          ],200);

        }else{
          return response()->json([
            'success'=> false,
            'code'=> 400,
            'cambios' => Null,
            'message' => 'La persona no esta asignada a un contrato'
          ],400);
        }
      }else{
        return response()->json([
          'success'=> false,
          'code'=> 400,
          'cambios' => Null,
          'message' => 'La persona no esta registrada en el sistema'
        ],400);
      }

    }else{
      return response()->json([
        'success'=> false,
        'code'=> 400,
        'cambios' => Null,
        'message' => 'No se recibe data del formulario'
      ],400);
    }
  }

  static public function AssignContract(Request $request) {

    $data = json_decode($request->getContent());

    $pintIdContrato = $data->contrato_id;
    $pintIdPersona = $data->persona_id;
    $pintIdRol = $data->rol_id;
    $pdatFechaInicioFaena = $data->FechaInicioFaena;

    self::$gobjPersona = Personas::with('Contratospersonas')->find($pintIdPersona);

    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');
    $ldatFechaActual = date('Y-m-d H:i:s');
    if ($pdatFechaInicioFaena){
      $ldatFechaEmision = new \DateTime($pdatFechaInicioFaena);
      $ldatFechaEmision = $ldatFechaEmision->format('Y-m').'-01';
    }else{
      $ldatFechaActual = date('Y-m').'-01';
    }

    //Buscamos la información del contrato
    $lobjContrato = Contratos::find($pintIdContrato);

    //Si existe seguimos
    if ($lobjContrato){

        //Determinamos el entry_by_access
        if ($lintLevelUser==6 && $lintIdUser!=$lobjContrato->entry_by_access){
          $lintEntryByAccess = $lintIdUser;
        }else{
          $lintEntryByAccess = $lobjContrato->entry_by_access;
        }

        $SubCont = self::EsSubcontratista($pintIdContrato);

        if ( $SubCont>0){
          //Verificamos que si es un subcontratista la carta de aprobacion este aprobada
          $lobjCartaAprobacion = \DB::table("tbl_documentos")
              ->join("tbl_tipos_documentos","tbl_documentos.IdTipoDocumento", "=", "tbl_tipos_documentos.IdTipoDocumento")
              ->where("tbl_documentos.Entidad","=","9")
              ->where("tbl_documentos.IdEntidad","=",$SubCont)
              ->where("tbl_documentos.contrato_id","=",$pintIdContrato)
              ->where("tbl_tipos_documentos.IdProceso","=",89)
              ->where("tbl_documentos.IdEstatus","!=",5)
              ->get();

          if ($lobjCartaAprobacion){
              return array("status" => "success", "code"=>4, "message"=>"El subcontatista no tiene la carta de aceptación aprobada", "result"=>$lobjContrato);
          }
      }

      //Verificamos que la fecha de inicio no sea anterior a la del contrato
      if ($lobjContrato->cont_fechaInicio > $ldatFechaEmision) {
        return response()->json(array("status" => "error", "code"=>5, "message"=>"La fecha de inicio en faena es menor a la fecha de inicio del contrato comercial", "result"=>[]));
      }

      \DB::beginTransaction();

      $contr = self::BuscaContratista($pintIdContrato);
      if ($contr){
        $contr = $contr[0];
      }

      //Validamos que la persona no se encuentre asignada a otro contrato
      if (self::$gobjPersona->Contratospersonas === null){

        self::RestoreHistorico($pintIdContrato, $pintIdPersona, $pintIdRol, $ldatFechaEmision);

        $lobjContratosPersonas = new Contratospersonas();
        $lobjContratosPersonas->contrato_id = $pintIdContrato;
        $lobjContratosPersonas->IdContratista = $contr;
        $lobjContratosPersonas->IdRol = $pintIdRol;
        $lobjContratosPersonas->IdPersona = self::$gobjPersona->IdPersona;
        $lobjContratosPersonas->FechaInicioFaena = $ldatFechaEmision;
        $lobjContratosPersonas->entry_by = $lintIdUser;
        $lobjContratosPersonas->entry_by_access = $lintEntryByAccess;
        $lobjContratosPersonas->acreditacion = $lobjContrato->acreditacion;
        $lobjContratosPersonas->controllaboral = $lobjContrato->controllaboral;

        //Guardamos a la persona en el contrato
        self::$gobjPersona->Contratospersonas()->save($lobjContratosPersonas);
        self::$gobjPersona->load('Contratospersonas');

        //Creamos la relacion de la persona con el contratista
        $lintResultadoUpdatePersona = \DB::table("tbl_personas")
        ->where("tbl_personas.IdPersona","=",$pintIdPersona)
        ->update(array("entry_by_access"=>$lintEntryByAccess));

        //Se levantan los documentos
        $lobjMyRequirements = new MyRequirements($pintIdContrato);
        $lobjRequirements = $lobjMyRequirements::getRequirements(3); // 3 = evento asignación de persona

        $lintAcreditacion = self::$gobjPersona->Contratospersonas->Acreditacion;
        $lintControlChecklLaboral = self::$gobjPersona->Contratospersonas->ControlCheckLaboral;

        foreach ($lobjRequirements as $larrRequirements) {

          if ( ( $larrRequirements->TipoDocumento->Acreditacion && $lintAcreditacion) || ($larrRequirements->TipoDocumento->ControlCheckLaboral && $lintControlChecklLaboral) || ( !$larrRequirements->Acreditacion && !$larrRequirements->ControlCheckLaboral ) ){
            $lobjRequirements = $lobjMyRequirements::Load($larrRequirements->IdRequisito, $pintIdContrato, $pintIdPersona);
          }

        }

        if (self::$gobjPersona->DocumentoContractual) {
          self::$gobjPersona->Contratospersonas->IdDocumento = self::$gobjPersona->DocumentoContractual->IdDocumento;
          self::$gobjPersona->Contratospersonas->IdEstatus = self::$gobjPersona->DocumentoContractual->IdEstatus;
          //Buscamos el IdTipoContrato
          $lobjTipoContrato = self::$gobjPersona->DocumentoContractual->Documentovalor()->where('tbl_documento_valor.IdTipoDocumentoValor',127)->join('tbl_tipos_contratos_personas','tbl_tipos_contratos_personas.Nombre','=','tbl_documento_valor.valor')->select('tbl_tipos_contratos_personas.id')->first();
          if ($lobjTipoContrato){
            self::$gobjPersona->Contratospersonas->IdTipoContrato = $lobjTipoContrato->id;
          }
          self::$gobjPersona->Contratospersonas->FechaVencimiento = self::$gobjPersona->DocumentoContractual->FechaVencimiento;
          self::$gobjPersona->Contratospersonas->save();
        }

        $lobjCheckLaboral = new MyCheckLaboral();
        $lobjCheckLaboral::UpdateDocument($pintIdContrato);

        //Registramos el movimiento
        self::RegisterMovePeople($pintIdPersona, 1, $pintIdContrato, $pdatFechaInicioFaena);

        self::UpdateMaestroMovil($pintIdPersona, $pintIdContrato, $pdatFechaInicioFaena,'AssignContract','Vigente');

        /*  +++++ DISCAPACIDAD ++++++++ */
        $persona = \DB::table("tbl_personas")->where('IdPersona', $pintIdPersona)->first();
        $discapacidad = $persona->discapacidad;
        if($discapacidad){
          $docDisca = \DB::table("tbl_documentos")
            ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
            ->where('tbl_documentos.Entidad', 3)
            ->where('tbl_documentos.IdEntidad', $pintIdPersona)
            ->where('tbl_tipos_documentos.IdProceso', 142)
            ->first();

          if($docDisca){
            //Si la persona tiene creado el documento de discapacidad y ademas discapacidad esta en 1 se actualiza el contratista, el cntrato y el entry_by_access
           $update = \DB::table("tbl_documentos")
             ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
             ->where('tbl_documentos.Entidad', 3)
             ->where('tbl_documentos.IdEntidad', $pintIdPersona)
             ->where('tbl_tipos_documentos.IdProceso', 142)
             ->update(array("tbl_documentos.entry_by"=> $lintIdUser,
                           "tbl_documentos.entry_by_access" => $lintEntryByAccess,
                           "tbl_documentos.FechaEmision" => $ldatFechaEmision,
                           "tbl_documentos.contrato_id" =>  $lobjContrato->contrato_id,
                           "tbl_documentos.idcontratista" =>  $contr,
                           "tbl_documentos.updatedOn" => new \DateTime()));
          }else{
            //se obtiene el id de tipo de documento con id proceso 142
            $idTipoDocumento = \DB::table('tbl_tipos_documentos')->where('IdProceso',142)->first();
            $idTipoDocumento = $idTipoDocumento->IdTipoDocumento;
            //no se tiene el documento asi que se levanta la solicitud.
            $lobjDocumentos = new MyDocuments();
						$larrResultado = $lobjDocumentos::Save($idTipoDocumento,3,$pintIdPersona,"",$contr, $lobjContrato->contrato_id);
          }
        }
         // Pensionado
        $pensionado = $persona->pensionado;
        if($pensionado){
          $docPen = \DB::table("tbl_documentos")
            ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
            ->where('tbl_documentos.Entidad', 3)
            ->where('tbl_documentos.IdEntidad', $pintIdPersona)
            ->where('tbl_tipos_documentos.IdProceso', 143)
            ->first();

          if($docPen){
            //Si la persona tiene creado el documento de pensionado y ademas pensionado esta en 1 se actualiza el contratista, el cntrato y el entry_by_access
          $update = \DB::table("tbl_documentos")
            ->join('tbl_tipos_documentos', 'tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
            ->where('tbl_documentos.Entidad', 3)
            ->where('tbl_documentos.IdEntidad', $pintIdPersona)
            ->where('tbl_tipos_documentos.IdProceso', 143)
            ->update(array("tbl_documentos.entry_by"=> $lintIdUser,
                          "tbl_documentos.entry_by_access" => $lintEntryByAccess,
                          "tbl_documentos.FechaEmision" => $ldatFechaEmision,
                          "tbl_documentos.contrato_id" =>  $lobjContrato->contrato_id,
                          "tbl_documentos.idcontratista" =>  $contr,
                          "tbl_documentos.updatedOn" => new \DateTime()));
          }else{
            //se obtiene el id de tipo de documento con id proceso 143
            $idTipoDocumento = \DB::table('tbl_tipos_documentos')->where('IdProceso',143)->first();
            $idTipoDocumento = $idTipoDocumento->IdTipoDocumento;
            //no se tiene el documento asi que se levanta la solicitud.
            $lobjDocumentos = new MyDocuments();
            $larrResultado = $lobjDocumentos::Save($idTipoDocumento,3,$pintIdPersona,"",$contr, $lobjContrato->contrato_id);
          }
        }


        \DB::commit();
        /* esto es una peticion de CCU que se está trabajando todavia
        $gobjPersona = self::$gobjPersona;
        $contratista = \DB::table('tbl_contratistas')->where('IdContratista',$lobjContrato->IdContratista)->first();
        $servicio = \DB::table('tbl_contrato')->join('tbl_servicios','tbl_contrato.idservicio','=','tbl_servicios.IdServicio')->where('tbl_contrato.contrato_id',$lobjContrato->contrato_id)->first();
        $rol = \DB::table('tbl_contratos_personas')->join('tbl_roles','tbl_roles.IdRol','=','tbl_contratos_personas.IdRol')->where('tbl_contratos_personas.contrato_id',$lobjContrato->contrato_id)->first();
        $usuarioContrato = \DB::table('tbl_contrato')->join('tb_users','tbl_contrato.usuarioContrato','=','tb_users.id')->where('tbl_contrato.contrato_id',$lobjContrato->contrato_id)->first();
        if($usuarioContrato){
          \Mail::send('emails.assignContract',
            ['lobjPersona'=>$gobjPersona,
             'lobjContrato'=>$lobjContrato,
             'lobjContratista'=>$contratista,
             'lobjServicio'=>$servicio,
             'lobjRol'=>$rol,
             'lobjUsuario'=>["usuario"=>$usuarioContrato->first_name." ".$usuarioContrato->last_name, "perfil"=>'Usuario Contrato']
           ], function ($m) use ($usuarioContrato){
            $email = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_EMAIL')->first();
            $m->from($email->Valor);
            $m->to($usuarioContrato->email)->subject("[Asignación Contrato]");
            //$m->to("gneira@sourcing.cl")->subject("[Asignación Contrato]");
          });
        }
        $usuarioADC = \DB::table('tbl_contrato')->join('tb_users','tbl_contrato.admin_id','=','tb_users.id')->where('tbl_contrato.contrato_id',$lobjContrato->contrato_id)->first();
        if($usuarioADC){
          \Mail::send('emails.assignContract',
            ['lobjPersona'=>$gobjPersona,
             'lobjContrato'=>$lobjContrato,
             'lobjContratista'=>$contratista,
             'lobjServicio'=>$servicio,
             'lobjRol'=>$rol,
             'lobjUsuario'=>["usuario"=>$usuarioContrato->first_name." ".$usuarioContrato->last_name, "perfil"=>'Administrador Contrato']
           ], function ($m) use ($usuarioADC){
            $email = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_EMAIL')->first();
            $m->from($email->Valor);
            $m->to($usuarioADC->email)->subject("[Asignación Contrato]");
            //$m->to("gneira@sourcing.cl")->subject("[Asignación Contrato]");
          });
        }

        $usuarioContratista = \DB::table('tbl_contrato')
          ->join('tbl_contratistas','tbl_contrato.IdContratista','=','tbl_contratistas.IdContratista')
          ->join('tb_users','tbl_contratistas.entry_by_access','=','tb_users.id')
          ->where('tbl_contrato.contrato_id',$lobjContrato->contrato_id)->first();
        if($usuarioContratista){
          \Mail::send('emails.assignContract',
            ['lobjPersona'=>$gobjPersona,
             'lobjContrato'=>$lobjContrato,
             'lobjContratista'=>$contratista,
             'lobjServicio'=>$servicio,
             'lobjRol'=>$rol,
             'lobjUsuario'=>["usuario"=>$usuarioContrato->first_name." ".$usuarioContrato->last_name, "perfil"=>'Usuario Contratista']
           ], function ($m) use ($usuarioContratista){
            $email = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_EMAIL')->first();
            $m->from($email->Valor);
            $m->to($usuarioContratista->email)->subject("[Asignación Contrato]");
            //$m->to("gneira@sourcing.cl")->subject("[Asignación Contrato]");
          });
        }

        $usuarioPrevencionista = \DB::table('tbl_contrato')
          ->join('tbl_groups_levels_assoc_contract','tbl_contrato.contrato_id','=','tbl_groups_levels_assoc_contract.contrato_id')
          ->join('tb_users','tb_users.id','=','tbl_groups_levels_assoc_contract.user_id')
          ->where('tbl_contrato.contrato_id',$lobjContrato->contrato_id)
          ->first();
        if($usuarioPrevencionista){
          \Mail::send('emails.assignContract',
            ['lobjPersona'=>$gobjPersona,
             'lobjContrato'=>$lobjContrato,
             'lobjContratista'=>$contratista,
             'lobjServicio'=>$servicio,
             'lobjRol'=>$rol,
             'lobjUsuario'=>["usuario"=>$usuarioContrato->first_name." ".$usuarioContrato->last_name, "perfil"=>'Prevencionista']
           ], function ($m) use ($usuarioPrevencionista){
            $email = \DB::table('tbl_configuraciones')->select('Valor')->where('Nombre','CNF_EMAIL')->first();
            $m->from($email->Valor);
            $m->to($usuarioPrevencionista->email)->subject("[Asignación Contrato]");
            //$m->to("gneira@sourcing.cl")->subject("[Asignación Contrato]");
          });
        }
        */
        return array("status" => "success", "code"=>1,"message"=>"Persona asignada satisfactoriamente", "result"=>self::$gobjPersona->Contratospersonas);
      }else{
        \DB::rollback();
        return array("status" => "success", "code"=>3,"message"=>"La persona ya se encuentra asignada a un contrato", "result"=>self::$gobjPersona->Contratospersonas);
      }
    }else{
      return array("status" => "success", "code"=>0,"message"=>"El contrato no existe", "result"=>'');
    }

  }

  static public function EsSubcontratista($pintIdContrato){
    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');

    $lobjSubContratista = \DB::table('tbl_contratistas')
      ->join("tbl_contratos_subcontratistas","tbl_contratistas.IdContratista","=","tbl_contratos_subcontratistas.IdSubContratista")
      ->where("tbl_contratistas.entry_by_access","=",$lintIdUser)
      ->where("tbl_contratos_subcontratistas.contrato_id","=",$pintIdContrato)
      ->pluck("tbl_contratistas.IdContratista");

    if ($lobjSubContratista){
        return $lobjSubContratista;
    }else{
        return 0;
    }
  }

  static public function BuscaContratista($pintIdContrato){
    $lintLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
    $lintIdUser = \Session::get('uid');
    if ($lintLevelUser==6){
      $empresa = \DB::table('tbl_contrato')->where('contrato_id', '=', $pintIdContrato)->pluck('IdContratista');
      $contratista = \DB::table('tbl_contratistas')
        ->join("tbl_contrato","tbl_contratistas.IdContratista","=","tbl_contrato.IdContratista")
        ->where("tbl_contratistas.entry_by_access","=",$lintIdUser)
        ->where("tbl_contrato.contrato_id","=",$pintIdContrato)
        ->pluck("tbl_contratistas.IdContratista");

      if ($empresa==$contratista){
          $resultado = $contratista;
      }else{
          $resultado = self::EsSubcontratista($pintIdContrato);
      }
    }else{
        $resultado = \DB::table('tbl_contrato')->where('contrato_id', '=', $pintIdContrato)->pluck('IdContratista');
    }
    return $resultado;
  }

  static public function RestoreHistorico($pintIdContrato,$pintIdPersona,$pintIdRol,$pdatFechaEfectiva = ""){
    $consulta = "select tbl_documentos_rep_historico.*,tbl_requisitos.IdRequisito
                    FROM tbl_documentos_rep_historico
                                INNER JOIN tbl_tipos_documentos on tbl_documentos_rep_historico.IdTipoDocumento=tbl_tipos_documentos.IdTipoDocumento
                                INNER JOIN tbl_requisitos on tbl_tipos_documentos.IdTipoDocumento=tbl_requisitos.IdTipoDocumento
                                WHERE tbl_documentos_rep_historico.IdEntidad='$pintIdPersona' and tbl_documentos_rep_historico.Entidad='3'
                                and tbl_tipos_documentos.Permanencia=1
                                and tbl_documentos_rep_historico.IdEstatus!=9
                    and exists ( SELECT *
                                      FROM tbl_requisitos
                                      INNER JOIN tbl_requisitos_detalles on tbl_requisitos.IdRequisito=tbl_requisitos_detalles.IdRequisito
                                      WHERE tbl_requisitos_detalles.IdEntidad = '$pintIdRol'
                                      AND tbl_documentos_rep_historico.IdTipoDocumento = tbl_requisitos.IdTipoDocumento)
                     and not EXISTS (SELECT *
                                      FROM tbl_documentos
                                      WHERE tbl_documentos.IdEntidad = '$pintIdPersona' and tbl_documentos_rep_historico.Entidad='3'
                                      AND tbl_documentos_rep_historico.IdTipoDocumento = tbl_documentos.IdTipoDocumento)
                    union
                    select tbl_documentos_rep_historico.*,tbl_requisitos.IdRequisito
                    FROM tbl_documentos_rep_historico
                    INNER JOIN tbl_tipos_documentos on tbl_documentos_rep_historico.IdTipoDocumento=tbl_tipos_documentos.IdTipoDocumento
                    INNER JOIN tbl_requisitos on tbl_tipos_documentos.IdTipoDocumento=tbl_requisitos.IdTipoDocumento
                    where tbl_documentos_rep_historico.Entidad = 3
                    and IdEntidad='$pintIdPersona'
                    and tbl_tipos_documentos.Permanencia=1
                    and tbl_documentos_rep_historico.IdEstatus!=9
                    and  tbl_tipos_documentos.IdTipoDocumento in ( SELECT tbl_requisitos.IdTipoDocumento
                                                          FROM tbl_requisitos
                                                          WHERE tbl_requisitos.Entidad = 3
                                                          AND tbl_requisitos.IdTipoDocumento NOT IN (2,8,64))
                    and not EXISTS (SELECT *
                                      FROM tbl_documentos
                                      WHERE tbl_documentos.IdEntidad = '$pintIdPersona' and tbl_documentos_rep_historico.Entidad='3'
                                      AND tbl_documentos_rep_historico.IdTipoDocumento = tbl_documentos.IdTipoDocumento)
                    union
                    select tbl_documentos_rep_historico.*,tbl_requisitos.IdRequisito
                    FROM tbl_documentos_rep_historico
                                INNER JOIN tbl_tipos_documentos on tbl_documentos_rep_historico.IdTipoDocumento=tbl_tipos_documentos.IdTipoDocumento
                                INNER JOIN tbl_requisitos on tbl_tipos_documentos.IdTipoDocumento=tbl_requisitos.IdTipoDocumento
                    WHERE tbl_tipos_documentos.IdTipoDocumento = 64
                                                AND tbl_documentos_rep_historico.Entidad = 3
                                    AND tbl_documentos_rep_historico.IdEntidad = '$pintIdPersona'
                                    and tbl_documentos_rep_historico.IdEstatus!=9
                      AND EXISTS (SELECT  tbl_personas.*
                         FROM tbl_personas
                         WHERE tbl_personas.IdPersona = '$pintIdPersona'
                                  AND tbl_personas.id_Nac not in (21,22))
                      and not EXISTS (SELECT *
                                      FROM tbl_documentos
                                      WHERE tbl_documentos.IdEntidad = '$pintIdPersona' and tbl_documentos_rep_historico.Entidad='3'
                                      AND tbl_documentos_rep_historico.IdTipoDocumento = tbl_documentos.IdTipoDocumento)";

    $lobjData = \DB::select($consulta);

    if (count($lobjData)>0) {
      foreach ($lobjData as $value) {
          $arrayDocs[] = $value->IdDocumento;
      }
      $lobjDataBitacora = \DB::table('tbl_documentos_log_historico')->whereIn('IdDocumento', $arrayDocs)->get();
      $lobjDataDocumentoV = \DB::table('tbl_documento_valor_historico')->whereIn('IdDocumento', $arrayDocs)->get();
    }

    foreach ($lobjData as $lisData) {
      if ($pdatFechaEfectiva){
        $ldatFechaEfectiva = $pdatFechaEfectiva;
      }else{
        $ldatFechaEfectiva = $lisData->FechaEmision;
      }

      $LintIdDoc = \DB::table('tbl_documentos')->insertGetId(
          ['IdDocumentoRelacion'=> NULL, 'IdRequisito'=> $lisData->IdRequisito,
              'IdTipoDocumento' => $lisData->IdTipoDocumento, 'Entidad' => $lisData->Entidad,
              'IdEntidad' => $lisData->IdEntidad, 'Documento' => $lisData->Documento,
              'DocumentoURL' => $lisData->DocumentoURL, 'DocumentoTexto'=> NULL,
              'FechaVencimiento' => $lisData->FechaVencimiento, 'IdEstatus'=>'5',
              'IdEstatusDocumento' => $lisData->IdEstatusDocumento, 'createdOn'=> new \DateTime(),
              'entry_by' => $lisData->load_by, 'entry_by_access' => $lisData->approv_by,
              'updatedOn'=> NULL, 'FechaEmision'=> $ldatFechaEfectiva, 'Resultado'=> '-',
              'contrato_id' => $pintIdContrato, 'IdContratista' => $lisData->IdContratista,'estado_carga'=>'0']);

        if (count($lobjDataDocumentoV)>0) {
            foreach ($lobjDataDocumentoV as $lisDataV) {
                if ($lisData->IdDocumento==$lisDataV->IdDocumento){
                    \DB::table('tbl_documento_valor')->insert(
                        ['IdDocumento' => $LintIdDoc, 'IdTipoDocumentoValor' => $lisDataV->IdTipoDocumentoValor,
                            'Valor' => $lisDataV->Valor, 'idCargado' => $lisDataV->idCargado,
                            'entry_by' => $lisDataV->entry_by, 'entry_by_access' => $lisDataV->entry_by_access]);
                    \DB::table('tbl_documento_valor_historico')->where('IdDocumento', '=', $lisDataV->IdDocumento)->delete();
                }
            }
        }

        if (count($lobjDataBitacora)>0) {
            foreach ($lobjDataBitacora as $lisDataB) {
                if ($lisData->IdDocumento==$lisDataB->IdDocumento) {
                    \DB::table('tbl_documentos_log')->insert(
                        ['IdDocumento' => $LintIdDoc, 'IdAccion' => $lisDataB->IdAccion,
                            'DocumentoURL' => $lisDataB->DocumentoURL, 'observaciones' => $lisDataB->observaciones,
                            'entry_by' => $lisDataB->entry_by, 'createdOn' => $lisDataB->createdOn]);

                    \DB::table('tbl_documentos_log_historico')->where('IdDocumento', '=', $lisDataB->IdDocumento)->delete();
                }
            }
        }

        \DB::table('tbl_documentos_rep_historico')->where('IdDocumento', '=', $lisData->IdDocumento)->delete();
    }
  }

  public function listAnotaciones(){
    $lobjAnotaciones = \DB::table('tbl_concepto_anotacion')->where('IdEstatus', '=', 1)->get();

    foreach ($lobjAnotaciones as $key => $anotacion) {
      $list[$key] = [
        'IdConceptoAnotacion' => $anotacion->IdConceptoAnotacion,
        'Descripcion' => $anotacion->Descripcion
      ];
    }

    return response()->json([
      'success'=> true,
      'code'=> 200,
      'Anotaciones' => $list
      ]);
  }

  public function listRoles(Request $request){
    $contrato_id =  $request->contrato_id;
    $lobjRoles =  Roles::join("tbl_roles_servicios","tbl_roles_servicios.idrol","=","tbl_roles.idrol")
  		              ->join("tbl_contrato","tbl_contrato.idservicio","=","tbl_roles_servicios.idservicio")
  		              ->where('tbl_contrato.contrato_id',$contrato_id)
  		              ->select("tbl_roles.IdRol as rol_id","tbl_roles.Descripción as Descripcion")
  		              ->orderBy("Descripcion","asc")
  		              ->get();

    if (count($lobjRoles)){

    }else{
      $lobjRoles = Roles::select("tbl_roles.IdRol as rol_id","tbl_roles.Descripción as Descripcion")
               ->orderBy("Descripcion","asc")
               ->get();
    }

    foreach ($lobjRoles as $key => $role){
        $list[$key] = [
        'IdRol' => $role->rol_id,
        'Descripcion' => $role->Descripcion
      ];
    }

    return response()->json([
      'success'=> true,
      'code'=> 200,
      'roles' => $list
      ]);
  }

  public function cambioFechaInicioFaena($contratoPersona, $fechaInicioFaenaNueva){

    $idPersona = $contratoPersona->IdPersona;
    $persona = $this->getObjetoPersona($idPersona);

    try {

      \DB::beginTransaction();

      $fechaAnterior = isset($contratoPersona->FechaInicioFaena) ? $contratoPersona->FechaInicioFaena : null;
      Contratospersonas::where('idPersona', $idPersona)->where('contrato_id',$contratoPersona->contrato_id)->update(['FechaInicioFaena'=>$fechaInicioFaenaNueva]);
      $movimientoPersonal = $persona->movimientosPersonales->where('IdAccion',"1")->sortByDesc('FechaEfectiva')->first();
      MovimientoPersonal::find($movimientoPersonal->IdMovimientoPersonal)->update(['FechaEfectiva'=>$fechaInicioFaenaNueva]);


      if($fechaInicioFaenaNueva < $contratoPersona->FechaInicioFaena){
        //levanta Solicitudes
        $requisitos = MyRequirements::getRequirements(3,1);
        foreach ($requisitos as $requisito) {
          MyRequirements::Load($requisito->IdRequisito,$contratoPersona->contrato_id,$idPersona);
        }
        $requisitos1 = MyRequirements::getRequirements(3,2);
        foreach ($requisitos1 as $requisito) {
          MyRequirements::Load($requisito->IdRequisito,$contratoPersona->contrato_id,$idPersona);
        }
        $requisitos3 = MyRequirements::getRequirements(3,3);
        foreach ($requisitos3 as $requisito) {
          MyRequirements::Load($requisito->IdRequisito,$contratoPersona->contrato_id,$idPersona);
        }
      }else{
        //eliminamos las solicitudes de documentos de carga mensual TODO: cambiar despues como el caso de arriba

        $documentosMensual = \DB::table('tbl_documentos')
                ->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
                ->join('tbl_requisitos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_requisitos.IdTipoDocumento')
                ->join('tbl_personas','tbl_documentos.IdEntidad','=','tbl_personas.IdPersona')
                ->where('tbl_documentos.Entidad',3)
                ->where('tbl_documentos.IdEstatus','!=', 5)
                ->where('tbl_requisitos.IdEvento',3)
                ->where('tbl_personas.IdPersona', $idPersona)
                ->where('tbl_documentos.contrato_id', $contratoPersona->contrato_id)
                ->where('tbl_tipos_documentos.Periodicidad', 1)
                ->whereIn('tbl_tipos_documentos.vigencia', [2])
                ->whereBetween('FechaEmision', [$fechaAnterior, $fechaInicioFaenaNueva])
                ->delete();

      }
      //se cambia la fecha de emision de los docuementos de carga unica por el periodo del nuevo inicio de faena
      $ldatFechaEmision = new \DateTime($fechaInicioFaenaNueva);
      $ldatFechaEmision = $ldatFechaEmision->format('Y-m').'-01';
      //cambia la fecha de emision de los documentos de carga unica.
      //TODO:ambiar esto consulta para cuando tenga tiempo hay que hacer funciones en traits que traigan documentos de personas, de contratos y de contratistas para asi reutilizar codigo
              $consulta = \DB::table('tbl_documentos')
                ->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
                ->join('tbl_requisitos','tbl_tipos_documentos.IdTipoDocumento','=','tbl_requisitos.IdTipoDocumento')
                ->join('tbl_personas','tbl_documentos.IdEntidad','=','tbl_personas.IdPersona')
                ->where('tbl_documentos.Entidad',3)
                ->where('tbl_requisitos.IdEvento',3)
                ->where('tbl_personas.IdPersona', $idPersona)
                ->where('tbl_tipos_documentos.Periodicidad','<>', '1')
                ->update(['tbl_documentos.FechaEmision' => $ldatFechaEmision]);

      \DB::commit();
      return true;

    } catch (\Exception $e) {

      \DB::rollback();
      return false;
    }

  }

  public function getNacionalidades(){
    $nacionalidades = \DB::table("tbl_nacionalidad")
    ->where("sexo",2)
    ->get();
    return $nacionalidades;
  }

  public function getContratistas(){
    $contratistas = \DB::select(\DB::raw("select id,first_name,last_name from `vw_usuarios_contratistas` order by `vw_usuarios_contratistas`.`first_name` asc"));
    return $contratistas;
  }

  public function getTelefono(){
    $data['pais'] = \DB::table('tbl_paises')->where('prefijo','<>','null')->get();
    $data['area'] = \DB::table('dim_region')->where('codigoArea','<>','null')->get();
    return response()->json($data);
  }

} // fin de la clase

?>
