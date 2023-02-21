<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSpEliminarTrabajadorInsercionEnTabla extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::unprepared("DROP PROCEDURE IF EXISTS `EliminarTrabajador`;");
        DB::unprepared("
        CREATE PROCEDURE `EliminarTrabajador`(
            IN `Rut` VARCHAR(50),
            IN `NumeroContrato` VARCHAR(50)
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
        declare pid varchar(50);
        declare cid varchar(50);
        declare contP int;
        declare contraP int;
        declare movP int;
        declare docs int;
        declare docsHis int;
        declare perAcred int;

        set contraP = 0;
        set movP = 0;
        set docs = 0;

        select IdPersona into pid from tbl_personas p where p.RUT=Rut;
        select count(IdPersona) into contP from tbl_personas p where p.RUT=Rut;
        select contrato_id into cid from tbl_contrato c where c.cont_numero=NumeroContrato;
        if pid then
                if cid then
                select count(IdContratosPersonas) into contraP from tbl_contratos_personas where IdPersona=pid and contrato_id=cid;
                delete from tbl_contratos_personas where IdPersona=pid and contrato_id=cid;
                select count(IdMovimientoPersonal) into movP from tbl_movimiento_personal  where IdPersona=pid and contrato_id=cid and IdAccion=1;
                delete from tbl_movimiento_personal  where IdPersona=pid and contrato_id=cid and IdAccion=1;
                   select count(IdDocumento) into docs from tbl_documentos where entidad=3 and IdEntidad=pid;
                          delete from tbl_documentos where entidad=3 and IdEntidad=pid;
                   select count(IdDocumento) into docsHis from tbl_documentos_rep_historico where entidad=3 and IdEntidad=pid;
                          delete from tbl_documentos_rep_historico where entidad=3 and IdEntidad=pid;
                        select count(id) into perAcred from tbl_personas_acreditacion WHERE idpersona = pid;
                                       DELETE FROM tbl_personas_acreditacion WHERE idpersona = pid;

                         update tbl_personas set entry_by=1, entry_by_access=null where IdPersona=pid;
                         INSERT INTO tbl_trabajador_eliminado_registro (IdPersona,RUT,numero_contrato,contrato_id,doc_borrados,doc_hist_borrados,persAcre_borrados,eliminated_at) VALUES (pid, Rut, NumeroContrato, cid,docs,docsHis,perAcred,NOW());
                SELECT contraP as 'Registros eliminados de contratos personas', movP as 'Registros eliminados de movimiento personal',  docs as 'Registros de Documentos eliminados' , docsHis as 'Registros de Documentos historicos eliminados' , perAcred as 'Registros en tbl_personas_acreditadas' , contP as 'registros de personas actualizados' ;
              else
                  select 'No se encontro registro del contrato';
              end if;
         else
           select 'No se encontro registro de la persona solicitada';
         end if;
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
        //
        DB::unprepared("DROP PROCEDURE IF EXISTS `EliminarTrabajador`;");
        DB::unprepared("
        CREATE DEFINER=`root`@`localhost` PROCEDURE `EliminarTrabajador`(
            IN `Rut` VARCHAR(50),
            IN `NumeroContrato` VARCHAR(50)
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
                                      declare pid varchar(50);
                                      declare cid varchar(50);
                                      declare contP int;
                                      declare contraP int;
                                      declare movP int;
                                      declare docs int;
                                      declare docsHis int;
                                      declare perAcred int;

                                      set contraP = 0;
                                      set movP = 0;
                                      set docs = 0;

                                      select IdPersona into pid from tbl_personas p where p.RUT=Rut;
                                      select count(IdPersona) into contP from tbl_personas p where p.RUT=Rut;
                                      select contrato_id into cid from tbl_contrato c where c.cont_numero=NumeroContrato;
                                      if pid then
                                              if cid then
                                              select count(IdContratosPersonas) into contraP from tbl_contratos_personas where IdPersona=pid and contrato_id=cid;
                                              delete from tbl_contratos_personas where IdPersona=pid and contrato_id=cid;
                                              select count(IdMovimientoPersonal) into movP from tbl_movimiento_personal  where IdPersona=pid and contrato_id=cid and IdAccion=1;
                                              delete from tbl_movimiento_personal  where IdPersona=pid and contrato_id=cid and IdAccion=1;
                                                 select count(IdDocumento) into docs from tbl_documentos where entidad=3 and IdEntidad=pid;
                                                        delete from tbl_documentos where entidad=3 and IdEntidad=pid;
                                                 select count(IdDocumento) into docsHis from tbl_documentos_rep_historico where entidad=3 and IdEntidad=pid;
                                                        delete from tbl_documentos_rep_historico where entidad=3 and IdEntidad=pid;
                                              		select count(id) into perAcred from tbl_personas_acreditacion WHERE idpersona = pid;
															 		DELETE FROM tbl_personas_acreditacion WHERE idpersona = pid;

                                                       update tbl_personas set entry_by=1, entry_by_access=null where IdPersona=pid;
                                              SELECT contraP as 'Registros eliminados de contratos personas', movP as 'Registros eliminados de movimiento personal',  docs as 'Registros de Documentos eliminados' , docsHis as 'Registros de Documentos historicos eliminados' , perAcred as 'Registros en tbl_personas_acreditadas' , contP as 'registros de personas actualizados' ;
                                            else
                                                select 'No se encontro registro del contrato';
                                            end if;
                                       else
                                         select 'No se encontro registro de la persona solicitada';
                                       end if;
                                END
        ");
    }
}
