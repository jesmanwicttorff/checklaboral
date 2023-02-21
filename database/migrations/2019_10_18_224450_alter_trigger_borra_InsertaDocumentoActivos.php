<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriggerBorraInsertaDocumentoActivos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `InsertaDocumentoActivos`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `InsertaDocumentoActivos`;");
        DB::unprepared("CREATE TRIGGER `InsertaDocumentoActivos` AFTER INSERT ON `tbl_activos_data` FOR EACH ROW
                        BEGIN
                         declare int_entry_by int;
   select entry_by_access
   into int_entry_by
   from tbl_contrato
   where tbl_contrato.contrato_id = NEW.contrato_id;

INSERT INTO tbl_documentos (`IdDocumento`, `IdRequisito`, `IdTipoDocumento`, `Entidad`, `IdEntidad`, `Documento`, `DocumentoURL`,
                                 `DocumentoTexto`,`FechaVencimiento`,`IdEstatus`,`createdOn`,`entry_by`,`entry_by_access`,`updatedOn`,
                                 `FechaEmision`,`Resultado`,`contrato_id`)
 SELECT NULL as IdDocumento,
       tbl_requisitos.IdRequisito,
       IdTipoDocumento,
       Entidad,
       NEW.contrato_id as IdEntidad,
       NULL as Documento,
       NULL as DocumentoURL,
       NULL as DocumentoTexto,
       NULL as FechaVencimiento,
       1 IdEstatus,
       now() as createdOn,
       int_entry_by,
       int_entry_by,
       null as updatedOn,
       NULL as FechaEmision,
       NULL as Resultado,
       NEW.contrato_id
  FROM tbl_requisitos
  WHERE tbl_requisitos.Entidad = 10;
  
  
insert into tbl_documentos_activos (iddocumento,idactivo) values (last_insert_id(), new.IdActivo);
  
END");

    }
}
