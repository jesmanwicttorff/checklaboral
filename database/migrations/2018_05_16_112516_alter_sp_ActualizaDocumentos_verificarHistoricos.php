<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSpActualizaDocumentosVerificarHistoricos extends Migration
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
        
                UPDATE tbl_documentos
                SET IdEstatusDocumento = 2
                WHERE tbl_documentos.IdDocumento IN (
                SELECT *
                FROM (
                SELECT tbl_documentos.IdDocumento 
                FROM tbl_documentos
                INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.Vigencia = 1
                WHERE tbl_documentos.FechaVencimiento < DATE(NOW())
                AND IFNULL(tbl_documentos.IdEstatus,1) = 5
                AND NOT EXISTS (SELECT 1 FROM tbl_documentos m WHERE m.iddocumento = tbl_documentos.IdDocumentoRelacion AND m.FechaVencimiento > tbl_documentos.FechaVencimiento)
                AND IFNULL(tbl_documentos.IdEstatusDocumento,1) != 2
                AND IFNULL(tbl_documentos.FechaVencimiento,'0000-00-00') NOT IN ('0000-00-00', '0000-00-00 00:00:00')) AS a);
                
                UPDATE tbl_documentos_rep_historico
                SET IdEstatusDocumento = 2
                WHERE tbl_documentos_rep_historico.IdDocumentoH IN (
                SELECT *
                FROM (
                SELECT tbl_documentos_rep_historico.IdDocumentoH 
                FROM tbl_documentos_rep_historico
                INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos_rep_historico.IdTipoDocumento AND tbl_tipos_documentos.Vigencia = 1
                WHERE tbl_documentos_rep_historico.FechaVencimiento < DATE(NOW())
                AND IFNULL(tbl_documentos_rep_historico.IdEstatusDocumento,1) != 2
                AND IFNULL(tbl_documentos_rep_historico.FechaVencimiento,'0000-00-00') NOT IN ('0000-00-00', '0000-00-00 00:00:00')) AS a);
                
                INSERT INTO tbl_documentos_log(`id`,`IdDocumento`,`IdAccion`,`observaciones`,`entry_by`,`createdOn`)
                SELECT NULL AS id,
                   IdDocumento,
                   20 as IdAccion,
                   NULL AS observaciones,
                   1 entry_by,
                   NOW() AS createdOn
                FROM (
                SELECT tbl_documentos.IdDocumento 
                FROM tbl_documentos
                INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento AND tbl_tipos_documentos.Vigencia = 1
                WHERE tbl_documentos.FechaVencimiento < DATE(NOW())
                AND IFNULL(tbl_documentos.IdEstatus,1) = 5
                AND NOT EXISTS (SELECT 1 FROM tbl_documentos m WHERE m.iddocumento = tbl_documentos.IdDocumentoRelacion AND m.FechaVencimiento > tbl_documentos.FechaVencimiento)
                AND IFNULL(tbl_documentos.IdEstatusDocumento,1) != 2
                AND IFNULL(tbl_documentos.FechaVencimiento,'0000-00-00') NOT IN ('0000-00-00', '0000-00-00 00:00:00')) AS a;
                
                UPDATE tbl_documentos
                SET IdEstatusDocumento = 1
                WHERE tbl_documentos.IdDocumento IN (SELECT * 
                FROM (
                SELECT tbl_documentos.IdDocumento
                FROM tbl_documentos
                INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento = tbl_documentos.IdTipoDocumento  AND tbl_tipos_documentos.Vigencia = 1
                WHERE (tbl_documentos.FechaVencimiento >= DATE(NOW()) OR EXISTS (SELECT 1 FROM tbl_documentos m WHERE m.iddocumento = tbl_documentos.IdDocumentoRelacion AND m.FechaVencimiento > tbl_documentos.FechaVencimiento) )
                AND IFNULL(tbl_documentos.IdEstatus,1) = 5
                AND IFNULL(tbl_documentos.IdEstatusDocumento,1) != 1
                AND IFNULL(tbl_documentos.FechaVencimiento,'0000-00-00') NOT IN ('0000-00-00', '0000-00-00 00:00:00')) AS a);
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
    }
}
