<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPrEstadoDeDocumentosPropiosFiltroSp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `pr_Estado_de_Documentos_propios`;");
        DB::unprepared("
            CREATE DEFINER=`root`@`localhost` PROCEDURE `pr_Estado_de_Documentos_propios`( IN contratistas VARCHAR(256), IN contratos VARCHAR(256),IN pintIdPerfil INTEGER )
            BEGIN 
                select tbl_documentos.IdEstatus, 
				   tbl_documentos_estatus.descripcion, 
				   sum(ifnull(tbl_documentos.cantidad,0)) as cantidad
		from (select case when ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 then 8 else tbl_documentos.IdEstatus end as IdEstatus, count(*) as cantidad
				 from tbl_documentos
				 where ( FIND_IN_SET(tbl_documentos.contrato_id, contratos) OR ( tbl_documentos.Entidad = 1 AND FIND_IN_SET(tbl_documentos.identidad, contratistas) ) )
				 and tbl_documentos.IdEstatus in (1,2,5,3)
                 and ( (tbl_documentos.IdEstatus = 2 and exists (select 1 from tbl_perfil_aprobacion where tbl_perfil_aprobacion.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_perfil_aprobacion.group_id = pintIdPerfil ) )
                 or (tbl_documentos.IdEstatus != 2 and exists (select 1 from tbl_tipo_documento_perfil where tbl_tipo_documento_perfil.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_tipo_documento_perfil.IdPerfil = pintIdPerfil ) ) )
				 group by case when ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 then 8 else tbl_documentos.IdEstatus end) as tbl_documentos
		right join (select * from tbl_documentos_estatus where tbl_documentos_estatus.IdEstatus in (1,2,5,3,8)) as tbl_documentos_estatus on tbl_documentos.IdEstatus = tbl_documentos_estatus.IdEstatus
		group by tbl_documentos.IdEstatus, tbl_documentos_estatus.descripcion
		order by tbl_documentos_estatus.IdEstatus asc;
            
            END
            ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `pr_Estado_de_Documentos_propios`;");
        DB::unprepared("
            CREATE DEFINER=`root`@`localhost` PROCEDURE `pr_Estado_de_Documentos_propios`( IN contratistas VARCHAR(256), IN contratos VARCHAR(256),IN pintIdPerfil INTEGER )
            BEGIN 
                select tbl_documentos.IdEstatus, 
				   tbl_documentos_estatus.descripcion, 
				   sum(ifnull(tbl_documentos.cantidad,0)) as cantidad
		from (select case when ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 then 8 else tbl_documentos.IdEstatus end as IdEstatus, count(*) as cantidad
				 from tbl_documentos
				 where ( FIND_IN_SET(tbl_documentos.contrato_id, contratos) OR ( tbl_documentos.Entidad = 1 AND FIND_IN_SET(tbl_documentos.identidad, contratistas) ) )
				 and tbl_documentos.IdEstatus in (1,2,5,3)
                 and ( (tbl_documentos.IdEstatus = 2 and exists (select 1 from tbl_perfil_aprobacion where tbl_perfil_aprobacion.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_perfil_aprobacion.group_id = pintIdPerfil ) )
                 or (tbl_documentos.IdEstatus != 2 and exists (select 1 from tbl_tipo_documento_perfil where tbl_tipo_documento_perfil.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_tipo_documento_perfil.IdPerfil = pintIdPerfil ) ) )
				 group by case when ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 then 8 else tbl_documentos.IdEstatus end) as tbl_documentos
		right join (select * from tbl_documentos_estatus where tbl_documentos_estatus.IdEstatus in (1,2,5,3,8)) as tbl_documentos_estatus on tbl_documentos.IdEstatus = tbl_documentos_estatus.IdEstatus
		group by tbl_documentos.IdEstatus, tbl_documentos_estatus.descripcion
		order by tbl_documentos_estatus.IdEstatus asc;
            
            END
            ");
    }
}
