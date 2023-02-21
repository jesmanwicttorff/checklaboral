<?php namespace App\Library;

use App\Models\Personas;
use App\Models\Documentos;
use App\Models\Acreditacioncontrato as Contratosacreditacion;
use App\Library\MyContracts;
use App\Library\Acreditacion;

class AcreditacionActivos{

	static private $gintIdActivo;
	static protected $gobjActivo;
	static private $gintIdUser;
	static private $gintIdLevelUser;

	public function __construct($pintIdActivoData){
		self::$gintIdActivo = $pintIdActivoData;
		self::$gobjActivo = \DB::table('tbl_activos_data')->join('tbl_activos_data_detalle','tbl_activos_data.IdActivoData','=','tbl_activos_data_detalle.IdActivoData')->where('tbl_activos_data_detalle.IdActivoData',$pintIdActivoData)->orderBy('tbl_activos_data_detalle.IdActivoDetalle','asc')->first();
		self::$gintIdUser = \Session::get('uid');
		self::$gintIdLevelUser = \MySourcing::LevelUser(\Session::get('uid'));
	}

	static public function Accreditation($estatus){

		$activo = self::$gobjActivo;
		if($activo and $estatus==1){
			$idactivodata = $activo->IdActivoData;
			$documentos = \DB::table('tbl_documentos_activos')->where('idactivodata',$idactivodata)->get();
			$flag=true;

			foreach ($documentos as $documento) {
				$doc = \DB::table('tbl_documentos')->join('tbl_tipos_documentos','tbl_documentos.IdTipoDocumento','=','tbl_tipos_documentos.IdTipoDocumento')
						->where('IdDocumento',$documento->iddocumento)
						->where('tbl_tipos_documentos.Acreditacion',1)
						->first();
				if($doc)
				if($doc->IdEstatus!=5 or $doc->IdEstatusDocumento==2){
					$flag=false;
				}
			}
			if($flag){
				\DB::table('tbl_historial_acreditacion_activos')->insertGetId(['numero'=>$activo->Valor, 'acreditacion'=>date('Y-m-d'), 'IdEstatus'=>1,'contrato_id'=>$activo->contrato_id, 'fecha'=>date('Y-m-d H:i'),'entry_by'=>self::$gintIdUser]);
			}
		}

		if($activo and $estatus==2){
			$idactivodata = $activo->IdActivoData;
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
				\DB::table('tbl_historial_acreditacion_activos')->insertGetId(['numero'=>$activo->Valor, 'acreditacion'=>date('Y-m-d'), 'IdEstatus'=>2,'contrato_id'=>$activo->contrato_id, 'fecha'=>date('Y-m-d H:i'),'entry_by'=>self::$gintIdUser]);
			}
		}

	}

}
