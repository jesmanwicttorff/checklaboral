<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPrEstadoDeDocumentosFiltroSp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `pr_Estado_de_Documentos`;");
        DB::unprepared("
            CREATE DEFINER=`root`@`localhost` PROCEDURE `pr_Estado_de_Documentos`( IN contratistas VARCHAR(256), IN contratos VARCHAR(256) )
            BEGIN 
                select tbl_documentos.IdEstatus, 
                           tbl_documentos_estatus.descripcion, 
                           sum(ifnull(tbl_documentos.cantidad,0)) as cantidad
                from (select case when ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 then 8 else tbl_documentos.IdEstatus end as IdEstatus, count(*) as cantidad
                         from tbl_documentos
                         where ( FIND_IN_SET(tbl_documentos.contrato_id, contratos) OR ( tbl_documentos.Entidad = 1 AND FIND_IN_SET(tbl_documentos.identidad, contratistas) ) )
                         and tbl_documentos.IdEstatus in (1,2,5,3)
                         group by case when ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 then 8 else tbl_documentos.IdEstatus end) as tbl_documentos
                inner join tbl_documentos_estatus on tbl_documentos.IdEstatus = tbl_documentos_estatus.IdEstatus
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
        DB::unprepared("DROP PROCEDURE IF EXISTS `pr_Estado_de_Documentos`;");
        DB::unprepared("
            CREATE DEFINER=`root`@`localhost` PROCEDURE `pr_Estado_de_Documentos`( IN contratos VARCHAR(256) )
            BEGIN 
                if (contratos>0) then
                    select tbl_documentos.IdEstatus, 
                               tbl_documentos_estatus.descripcion, 
                               sum(ifnull(tbl_documentos.cantidad,0)) as cantidad
                    from (select case when ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 then 8 else tbl_documentos.IdEstatus end as IdEstatus, count(*) as cantidad
                             from tbl_documentos
                             where FIND_IN_SET(tbl_documentos.contrato_id, contratos)
                             and tbl_documentos.IdEstatus in (1,2,5,3)
                             group by case when ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 then 8 else tbl_documentos.IdEstatus end) as tbl_documentos
                    inner join tbl_documentos_estatus on tbl_documentos.IdEstatus = tbl_documentos_estatus.IdEstatus
                    group by tbl_documentos.IdEstatus, tbl_documentos_estatus.descripcion
                    order by tbl_documentos_estatus.IdEstatus asc;
                else
                    select tbl_documentos.IdEstatus, 
                               tbl_documentos_estatus.descripcion, 
                               sum(ifnull(tbl_documentos.cantidad,0)) as cantidad
                    from (select case when ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 then 8 else tbl_documentos.IdEstatus end as IdEstatus, count(*) as cantidad
                             from tbl_documentos
                             where tbl_documentos.IdEstatus in (1,2,5,3)
                             group by case when ifnull(tbl_documentos.IdEstatusDocumento,1) = 2 then 8 else tbl_documentos.IdEstatus end) as tbl_documentos
                    inner join tbl_documentos_estatus on tbl_documentos.IdEstatus = tbl_documentos_estatus.IdEstatus
                    group by tbl_documentos.IdEstatus, tbl_documentos_estatus.descripcion
                    order by tbl_documentos_estatus.IdEstatus asc;
                end if;
            END
        ");
    }
}
