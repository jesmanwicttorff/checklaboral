<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterActualizaDocumentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `ActualizaDocumentos`;");
        DB::unprepared("
            CREATE DEFINER=`root`@`localhost` PROCEDURE `ActualizaDocumentos`()
            BEGIN 
                update tbl_documentos
                set IdEstatusDocumento = 2
                where tbl_documentos.IdDocumento in (
                select *
                from (
                select tbl_documentos.IdDocumento 
                from tbl_documentos
                inner join tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_tipos_documentos.Vigencia = 1
                where tbl_documentos.FechaVencimiento < date(now())
                and ifnull(tbl_documentos.IdEstatus,1) = 5
                and not exists (select 1 from tbl_documentos m where m.iddocumento = tbl_documentos.IdDocumentoRelacion and m.FechaVencimiento > tbl_documentos.FechaVencimiento)
                and ifnull(tbl_documentos.IdEstatusDocumento,1) != 2
                and ifnull(tbl_documentos.FechaVencimiento,'0000-00-00') NOT IN ('0000-00-00', '0000-00-00 00:00:00')) as a);
                
                update tbl_documentos
                set IdEstatusDocumento = 1
                where tbl_documentos.IdDocumento in (select * 
                from (
                select tbl_documentos.IdDocumento
                from tbl_documentos
                inner join tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento  and tbl_tipos_documentos.Vigencia = 1
                where (tbl_documentos.FechaVencimiento >= date(now()) or exists (select 1 from tbl_documentos m where m.iddocumento = tbl_documentos.IdDocumentoRelacion and m.FechaVencimiento > tbl_documentos.FechaVencimiento) )
                and ifnull(tbl_documentos.IdEstatus,1) = 5
                and ifnull(tbl_documentos.IdEstatusDocumento,1) != 1
                and ifnull(tbl_documentos.FechaVencimiento,'0000-00-00') NOT IN ('0000-00-00', '0000-00-00 00:00:00')) as a);
            
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
        DB::unprepared("DROP PROCEDURE IF EXISTS `ActualizaDocumentos`;");
        DB::unprepared("
            CREATE DEFINER=`root`@`localhost` PROCEDURE `ActualizaDocumentos`()
            BEGIN 
                update tbl_documentos
                set IdEstatusDocumento = 2
                where tbl_documentos.IdDocumento in (
                select *
                from (
                select tbl_documentos.IdDocumento 
                from tbl_documentos
                inner join tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento and tbl_tipos_documentos.Vigencia = 1
                where tbl_documentos.FechaVencimiento < date(now())
                and ifnull(tbl_documentos.IdEstatus,1) = 5
                and not exists (select 1 from tbl_documentos m where m.iddocumento = tbl_documentos.IdDocumentoRelacion and m.FechaVencimiento > tbl_documentos.FechaVencimiento)
                and ifnull(tbl_documentos.IdEstatusDocumento,1) != 2
                and ifnull(tbl_documentos.FechaVencimiento,'0000-00-00') NOT IN ('0000-00-00', '0000-00-00 00:00:00')) as a);
                
                update tbl_documentos
                set IdEstatusDocumento = 1
                where tbl_documentos.IdDocumento in (select * 
                from (
                select tbl_documentos.IdDocumento
                from tbl_documentos
                inner join tbl_tipos_documentos on tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento  and tbl_tipos_documentos.Vigencia = 1
                where (tbl_documentos.FechaVencimiento >= date(now()) or exists (select 1 from tbl_documentos m where m.iddocumento = tbl_documentos.IdDocumentoRelacion and m.FechaVencimiento > tbl_documentos.FechaVencimiento) )
                and ifnull(tbl_documentos.IdEstatus,1) = 5
                and ifnull(tbl_documentos.IdEstatusDocumento,1) != 1
                and ifnull(tbl_documentos.FechaVencimiento,'0000-00-00') NOT IN ('0000-00-00', '0000-00-00 00:00:00')) as a);
            
            END
            ");
    }
}
