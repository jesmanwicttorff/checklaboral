<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpEstadoDeDocumentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(" 
        CREATE DEFINER=`root`@`localhost` PROCEDURE `pr_Estado_de_Documentos` (IN contratos VARCHAR(256))
        BEGIN 
        SELECT IdEstatus,descripcion,sum(q) AS cantidad FROM (
            #Contrato
            SELECT d1.IdEstatus,e1.descripcion,COUNT(d1.idDocumento) q 
            FROM tbl_documentos d1 
                left join tbl_documentos_estatus as e1 on e1.IdEstatus=d1.IdEstatus
            WHERE  d1.IdEstatus in (1,2,5,3)  
                and FIND_IN_SET(d1.contrato_id, contratos)
            group by d1.IdEstatus,e1.descripcion
            UNION ALL
            SELECT '8','Vencido',COUNT(c.idDocumento) q 
            FROM tbl_documentos AS c
            WHERE c.IdEstatusDocumento=2
                and FIND_IN_SET(c.contrato_id, contratos)
            GROUP BY 1
            UNION ALL
            #Contratista
            SELECT d2.IdEstatus,e2.descripcion,COUNT(d2.idDocumento) q 
            FROM tbl_documentos d2 
            LEFT JOIN tbl_documentos_estatus AS e2 ON e2.IdEstatus=d2.IdEstatus
            WHERE d2.idEntidad IN (
                SELECT c1.IdContratista 
                FROM tbl_contrato AS c1 
                WHERE FIND_IN_SET(c1.contrato_id, contratos)
                ) 
                AND d2.entidad=1
                and d2.IdEstatus not in (4,7,99)
            GROUP BY d2.IdEstatus,e2.descripcion
            UNION ALL
            #Personas
            SELECT d3.IdEstatus,e3.descripcion,COUNT(d3.idDocumento) q 
            FROM tbl_documentos d3 
                left join tbl_documentos_estatus as e3 on e3.IdEstatus=d3.IdEstatus
            where d3.Entidad=3 
                and FIND_IN_SET(d3.contrato_id, contratos)
                and d3.IdEstatus not in (4,7,99)
            group by d3.IdEstatus,e3.descripcion
        ) as j
        group by 1,2
        order by 1;
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
    }
}
