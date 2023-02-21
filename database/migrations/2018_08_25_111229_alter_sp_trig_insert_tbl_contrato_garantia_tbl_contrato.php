<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSpTrigInsertTblContratoGarantiaTblContrato extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `trg_AI_crea_ctto`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `trg_AI_crea_ctto` AFTER INSERT ON `tbl_contrato` FOR EACH ROW BEGIN
    
    
    declare lintDocumentoGarantia int;
    
     INSERT INTO tb_msg_sistemas(user_id,contrato_id,tpo_alerta)
     VALUES (new.entry_by_access, new.contrato_id,15);
    
    IF NEW.IdGarantia THEN
    SET lintDocumentoGarantia := (SELECT IdDocumento FROM tbl_tipos_de_garantias INNER JOIN tbl_tipos_documentos ON tbl_tipos_documentos.IdTipoDocumento =  tbl_tipos_de_garantias.IdDocumento WHERE IdTipoGarantia = NEW.IdGarantia);
        IF lintDocumentoGarantia  THEN
       INSERT INTO tbl_documentos (`IdDocumento`, `IdRequisito`, `IdTipoDocumento`, `Entidad`, `IdEntidad`, `Documento`, `DocumentoURL`,
                 `DocumentoTexto`,`FechaVencimiento`,`IdEstatus`,`createdOn`,`entry_by`,`entry_by_access`,`updatedOn`,
                 `FechaEmision`,`Resultado`,`contrato_id`)
       SELECT NULL as IdDocumento,
           NULL, 
           lintDocumentoGarantia,
           2 as Entidad,
           NEW.contrato_id as IdEntidad,
           NULL as Documento,
           NULL as DocumentoURL,
           NULL as DocumentoTexto,
           NULL as FechaVencimiento,
           1 IdEstatus,
           now() as createdOn,
           
           
        NEW.entry_by,
        NEW.entry_by_access,
        null as updatedOn,
        NULL as FechaEmision,
           NULL as Resultado,
           NEW.contrato_id
        FROM dual
        WHERE NOT EXISTS (SELECT * FROM tbl_documentos
                WHERE tbl_documentos.IdTipoDocumento = lintDocumentoGarantia
                AND tbl_documentos.Entidad = 2
                AND tbl_documentos.IdEntidad = NEW.contrato_id);
        END IF;
    END IF;
    
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `trg_AI_crea_ctto`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` TRIGGER `trg_AI_crea_ctto` AFTER INSERT ON `tbl_contrato` FOR EACH ROW BEGIN
    
    
    declare lintDocumentoGarantia int;
    
     INSERT INTO tb_msg_sistemas(user_id,contrato_id,tpo_alerta)
     VALUES (new.entry_by_access, new.contrato_id,15);
    
    IF NEW.IdGarantia THEN
        SET lintDocumentoGarantia := (SELECT IdDocumento FROM tbl_tipos_de_garantias WHERE IdTipoGarantia = NEW.IdGarantia);
        IF lintDocumentoGarantia  THEN
             INSERT INTO tbl_documentos (`IdDocumento`, `IdRequisito`, `IdTipoDocumento`, `Entidad`, `IdEntidad`, `Documento`, `DocumentoURL`,
                                 `DocumentoTexto`,`FechaVencimiento`,`IdEstatus`,`createdOn`,`entry_by`,`entry_by_access`,`updatedOn`,
                                 `FechaEmision`,`Resultado`,`contrato_id`)
             SELECT NULL as IdDocumento,
                   NULL, 
                   lintDocumentoGarantia,
                   2 as Entidad,
                   NEW.contrato_id as IdEntidad,
                   NULL as Documento,
                   NULL as DocumentoURL,
                   NULL as DocumentoTexto,
                   NULL as FechaVencimiento,
                   1 IdEstatus,
                   now() as createdOn,
                   
                   
                NEW.entry_by,
                NEW.entry_by_access,
                null as updatedOn,
                NULL as FechaEmision,
                   NULL as Resultado,
                   NEW.contrato_id
              FROM dual
              WHERE NOT EXISTS (SELECT * FROM tbl_documentos
                                WHERE tbl_documentos.IdTipoDocumento = lintDocumentoGarantia
                                AND tbl_documentos.Entidad = 2
                                AND tbl_documentos.IdEntidad = NEW.contrato_id);
        END IF;
    END IF;
    
END");

    }
}
