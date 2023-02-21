<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSpEliminarTrabajador extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1)	Pide el RUT del trabajador y el número de contrato de donde se quiere eliminar.
        //     a.	Elimine el registro de asociación con ese contrato en tbl_contratos_personas >> esto lo quita del contrato
        //     b.	Elimine el registro de ingreso (1) a ese contrato de tbl_movimiento_personal >> esto normaliza los registros de movimiento
        //     c.	Elimine los documentos asociados a esa entidad (entidad 3; identidad=idpersona) en tbl_documentos >> esto elimina documentos basura de la base
        //     d.	Cambie el entry_by y el entry_by_access a 1 en tbl_personas >> esto quita al trabajador del listado de personal del contratista, de lo contrario lo seguirá viendo y se confunden

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
                                                       update tbl_personas set entry_by=1, entry_by_access=null where IdPersona=pid;
                                              SELECT contraP as 'Registros eliminados de contratos personas', movP as 'Registros eliminados de movimiento personal',  docs as 'Registros de Documentos eliminados' , docsHis as 'Registros de Documentos historicos eliminados' , contP as 'registros de personas actualizados' ;
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
    }
}
