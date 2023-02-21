<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpAutorenovacionContrato extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(" 
        CREATE DEFINER=`root`@`localhost` PROCEDURE `AutorenovacionContrato` ()
        BEGIN 
        DECLARE fechafin date; 
        DECLARE nuevafechafin date; 
        DECLARE idcontrato int; 
        DECLARE obser varchar(100); 
        DECLARE otr varchar(100); 
        DECLARE observacion varchar(200); 
        
        DECLARE cur CURSOR FOR 
        Select contrato_id,cont_fechaFin,case
          WHEN plazoautorenovacion = 1 THEN DATE_ADD(cont_fechaFin, INTERVAL 1 YEAR)
            WHEN plazoautorenovacion = 2 THEN DATE_ADD(cont_fechaFin, INTERVAL 2 YEAR)
            WHEN plazoautorenovacion = 3 THEN DATE_ADD(cont_fechaFin, INTERVAL 3 YEAR)
            WHEN plazoautorenovacion = 4 THEN DATE_ADD(cont_fechaFin, INTERVAL 4 YEAR)
            WHEN plazoautorenovacion = 5 THEN DATE_ADD(cont_fechaFin, INTERVAL 5 YEAR)
            END as fecha_fin
        from tbl_contrato 
        where autorenovacion=1 and cont_fechaFin<NOW();
        
            OPEN cur; 
                read_loop: LOOP
                    FETCH cur INTO idcontrato ,fechafin ,nuevafechafin; 
                    
                    update tbl_contrato set cont_fechaFin=nuevafechafin WHERE contrato_id=idcontrato;
        
                    update tbl_accesos
                    INNER JOIN tbl_contratos_personas on tbl_accesos.IdPersona=tbl_contratos_personas.IdPersona AND tbl_accesos.contrato_id = tbl_contratos_personas.contrato_id
                    set FechaFinal=nuevafechafin
                    where tbl_accesos.contrato_id=idcontrato
                    and tbl_accesos.IdTipoAcceso=1;
        
                    update tbl_documentos
                    INNER JOIN tbl_tipos_documentos on tbl_documentos.IdTipoDocumento=tbl_tipos_documentos.IdTipoDocumento
                    set FechaVencimiento=nuevafechafin
                    where tbl_documentos.IdEntidad = idcontrato
                    and tbl_documentos.Entidad=2
                    and tbl_tipos_documentos.IdProceso=26;
                    
                    SET obser = CONCAT('Extension desde', ' ', fechafin);
                    SET otr = CONCAT(' hasta', ' ', nuevafechafin);
                    SET observacion = CONCAT(obser, '', otr);
    
                    insert into tbl_contratos_acciones ( contrato_id, accion_id, observaciones, entry_by) VALUES (
                            idcontrato, 9, observacion,1);
                END LOOP;           
            CLOSE cur; 
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
        DB::unprepared("DROP PROCEDURE IF EXISTS `AutorenovacionContrato`;");
    }
}
