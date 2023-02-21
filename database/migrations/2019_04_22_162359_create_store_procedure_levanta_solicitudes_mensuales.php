<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreProcedureLevantaSolicitudesMensuales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS `spLevantaSolicitudesPeriodicas`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` PROCEDURE `spLevantaSolicitudesPeriodicas`( pintPeriodicidad INT, pintIdProceso INT )
        BEGIN
          
            /* pintPeriodicidad '1' => 'Mensual' ,  '2' => 'Trimestral' , '3' => 'Semestral' , '4' => 'Anual' */
            
              /* Insertamos los requisitos a contratistas */
                insert into tbl_documentos(IdRequisito, 
                             IdTipoDocumento,
                             Entidad,
                             IdEntidad,
                             Vencimiento,
                             FechaVencimiento,
                             IdEstatus,
                             IdEstatusDocumento,
                             createdOn, 
                             entry_by,
                             entry_by_access,
                             FechaEmision,
                             contrato_id,
                             IdContratista) 
                select tbl_requisitos.IdRequisito, 
                             tbl_requisitos.IdTipoDocumento, 
                             tbl_requisitos.Entidad,
                             tbl_requisitos.IdEntidad as IdEntidad,
                             0 as Vencimiento,
                             null as FechaVencimiento,
                             1 as IdEstatus,
                             1 as IdEstatusDocumento,
                             now() as createdOn,
                             1 as entry_by,
                             tbl_requisitos.entry_by_access,
                             CONCAT(DATE_FORMAT(CURDATE(),'%Y-%m'),'-01') as FechaEmision,
                             tbl_requisitos.contrato_id,
                             tbl_requisitos.IdContratista
                from (select tbl_requisitos.*, 
                                         tbl_contrato.idcontratista as IdEntidad,
                                         tbl_contrato.entry_by_access, 
                                         tbl_contrato.contrato_id, 
                                         tbl_contrato.idcontratista 
                                         from tbl_contrato, tbl_requisitos 
                                         where tbl_requisitos.entidad = 1
                                         and tbl_contrato.cont_estado = 1) as tbl_requisitos
                inner join tbl_tipos_documentos on tbl_tipos_documentos.idTipoDocumento = tbl_requisitos.IdTipoDocumento
                where tbl_tipos_documentos.Vigencia = 2
                and tbl_tipos_documentos.Periodicidad = pintPeriodicidad
                and tbl_tipos_documentos.IdProceso = ifnull(pintIdProceso,tbl_tipos_documentos.IdProceso)
                and not exists (select 1 
                                                from tbl_documentos 
                                                where tbl_documentos.IdTipoDocumento = tbl_requisitos.IdTipoDocumento
                                                and tbl_documentos.FechaEmision = CONCAT(DATE_FORMAT(CURDATE(),'%Y-%m'),'-01')
                                                and tbl_documentos.Entidad = tbl_requisitos.Entidad
                                                and tbl_documentos.IdEntidad = tbl_requisitos.IdEntidad);
            
              /* Insertamos los requisitos a contrato */
                insert into tbl_documentos(IdRequisito, 
                             IdTipoDocumento,
                             Entidad,
                             IdEntidad,
                             Vencimiento,
                             FechaVencimiento,
                             IdEstatus,
                             IdEstatusDocumento,
                             createdOn, 
                             entry_by,
                             entry_by_access,
                             FechaEmision,
                             contrato_id,
                             IdContratista) 
                select tbl_requisitos.IdRequisito, 
                             tbl_requisitos.IdTipoDocumento, 
                             tbl_requisitos.Entidad,
                             tbl_requisitos.contrato_id as IdEntidad,
                             0 as Vencimiento,
                             null as FechaVencimiento,
                             1 as IdEstatus,
                             1 as IdEstatusDocumento,
                             now() as createdOn,
                             1 as entry_by,
                             tbl_requisitos.entry_by_access,
                             CONCAT(DATE_FORMAT(CURDATE(),'%Y-%m'),'-01') as FechaEmision,
                             tbl_requisitos.contrato_id,
                             tbl_requisitos.IdContratista
                from (select tbl_requisitos.*, tbl_contrato.entry_by_access, tbl_contrato.contrato_id, tbl_contrato.idcontratista from tbl_contrato, tbl_requisitos where tbl_requisitos.entidad = 2 and tbl_contrato.cont_estado = 1) as tbl_requisitos
                inner join tbl_tipos_documentos on tbl_tipos_documentos.idTipoDocumento = tbl_requisitos.IdTipoDocumento
                where tbl_tipos_documentos.Vigencia = 2
                and tbl_tipos_documentos.Periodicidad = pintPeriodicidad
                and tbl_tipos_documentos.IdProceso = ifnull(pintIdProceso,tbl_tipos_documentos.IdProceso)
                and not exists (select 1 
                                                from tbl_documentos 
                                                where tbl_documentos.IdTipoDocumento = tbl_requisitos.IdTipoDocumento
                                                and tbl_documentos.FechaEmision = CONCAT(DATE_FORMAT(CURDATE(),'%Y-%m'),'-01')
                                                and tbl_documentos.Entidad = tbl_requisitos.Entidad
                                                and tbl_documentos.IdEntidad = tbl_requisitos.contrato_id);
                                            
                                            
                /* insertamos los documentos a pesronas */
                insert into tbl_documentos(IdRequisito, 
                             IdTipoDocumento,
                             Entidad,
                             IdEntidad,
                             Vencimiento,
                             FechaVencimiento,
                             IdEstatus,
                             IdEstatusDocumento,
                             createdOn, 
                             entry_by,
                             entry_by_access,
                             FechaEmision,
                             contrato_id,
                             IdContratista) 
                select tbl_requisitos.IdRequisito, 
                             tbl_requisitos.IdTipoDocumento, 
                             tbl_requisitos.Entidad,
                             tbl_requisitos.IdEntidad as IdEntidad,
                             0 as Vencimiento,
                             null as FechaVencimiento,
                             1 as IdEstatus,
                             1 as IdEstatusDocumento,
                             now() as createdOn,
                             1 as entry_by,
                             tbl_requisitos.entry_by_access,
                             CONCAT(DATE_FORMAT(CURDATE(),'%Y-%m'),'-01') as FechaEmision,
                             tbl_requisitos.contrato_id,
                             tbl_requisitos.IdContratista
                from (select tbl_requisitos.*, 
                                         tbl_personas.idpersona as IdEntidad,
                                         tbl_personas.entry_by_access, 
                                         tbl_contrato.contrato_id, 
                                         tbl_contrato.idcontratista 
                                         from tbl_personas, tbl_contratos_personas, tbl_contrato, tbl_requisitos 
                                         where tbl_requisitos.entidad = 3
                                         and tbl_personas.IdPersona = tbl_contratos_personas.IdPersona
                                         and tbl_contratos_personas.contrato_id = tbl_contrato.contrato_id
                                         and tbl_contrato.cont_estado = 1) as tbl_requisitos
                inner join tbl_tipos_documentos on tbl_tipos_documentos.idTipoDocumento = tbl_requisitos.IdTipoDocumento
                where tbl_tipos_documentos.Vigencia = 2
                and tbl_tipos_documentos.Periodicidad = pintPeriodicidad
                and tbl_tipos_documentos.IdProceso = ifnull(pintIdProceso,tbl_tipos_documentos.IdProceso)
                and not exists (select 1 
                                                from tbl_documentos 
                                                where tbl_documentos.IdTipoDocumento = tbl_requisitos.IdTipoDocumento
                                                and tbl_documentos.FechaEmision = CONCAT(DATE_FORMAT(CURDATE(),'%Y-%m'),'-01')
                                                and tbl_documentos.Entidad = tbl_requisitos.Entidad
                                                and tbl_documentos.IdEntidad = tbl_requisitos.IdEntidad);
        
        END;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS `spLevantaSolicitudesPeriodicas`;");
    }
}
