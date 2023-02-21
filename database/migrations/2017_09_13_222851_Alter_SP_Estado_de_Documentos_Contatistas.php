<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSPEstadoDeDocumentosContatistas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		DB::unprepared("DROP PROCEDURE IF EXISTS `pr_Estado_de_Documentos_Contatistas`;");
        DB::unprepared(" 
			CREATE DEFINER=`root`@`localhost` PROCEDURE `pr_Estado_de_Documentos_Contatistas`( IN contatistas VARCHAR(256) )
			BEGIN 
			if (CHAR_LENGTH(contatistas)>0) then
				SELECT IdEstatus,descripcion,sum(q) AS cantidad FROM (
					#Contrato
					SELECT d1.IdEstatus,e1.descripcion,COUNT(d1.idDocumento) q 
					FROM tbl_documentos d1 
						left join tbl_documentos_estatus as e1 on e1.IdEstatus=d1.IdEstatus
						INNER JOIN tbl_tipos_documentos td1 ON d1.IdTipoDocumento = td1.IdTipoDocumento
						INNER JOIN tbl_contrato cn1 ON d1.contrato_id = cn1.contrato_id
					WHERE  d1.IdEstatus in (1,2,5,3)  
						and FIND_IN_SET(cn1.IdContratista, contatistas)
					group by d1.IdEstatus,e1.descripcion
				UNION ALL
					SELECT '8','Vencido',COUNT(c.idDocumento) q 
					FROM tbl_documentos AS c
						INNER JOIN tbl_contrato cn2 ON c.contrato_id = cn2.contrato_id
					WHERE c.IdEstatusDocumento=2
						and FIND_IN_SET(cn2.IdContratista, contatistas)
					GROUP BY 1

				) as j
				group by 1,2
				order by 1;
			else
				SELECT IdEstatus,descripcion,sum(q) AS cantidad FROM (
					#Contrato
					SELECT d1.IdEstatus,e1.descripcion,COUNT(d1.idDocumento) q 
					FROM tbl_documentos d1 
						left join tbl_documentos_estatus as e1 on e1.IdEstatus=d1.IdEstatus
						INNER JOIN tbl_tipos_documentos td1 ON d1.IdTipoDocumento = td1.IdTipoDocumento
						INNER JOIN tbl_contrato cn1 ON d1.contrato_id = cn1.contrato_id
					WHERE  d1.IdEstatus in (1,2,5,3)  
					GROUP BY d1.IdEstatus,e1.descripcion
				UNION ALL
					SELECT '8','Vencido',COUNT(c.idDocumento) q 
					FROM tbl_documentos AS c
						INNER JOIN tbl_contrato cn2 ON c.contrato_id = cn2.contrato_id
					WHERE c.IdEstatusDocumento=2
					GROUP BY 1

				) as j
				group by 1,2
				order by 1;
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
		DB::unprepared("DROP PROCEDURE IF EXISTS `pr_Estado_de_Documentos_Contatistas`;");
        DB::unprepared(" 
			CREATE DEFINER=`root`@`localhost` PROCEDURE `pr_Estado_de_Documentos_Contatistas`( IN contatistas VARCHAR(256) )
			BEGIN 
				SELECT IdEstatus,descripcion,sum(q) AS cantidad FROM (
					#Contrato
					SELECT d1.IdEstatus,e1.descripcion,COUNT(d1.idDocumento) q 
					FROM tbl_documentos d1 
						left join tbl_documentos_estatus as e1 on e1.IdEstatus=d1.IdEstatus
						INNER JOIN tbl_tipos_documentos td1 ON d1.IdTipoDocumento = td1.IdTipoDocumento
						INNER JOIN tbl_contrato cn1 ON d1.contrato_id = cn1.contrato_id
					WHERE  d1.IdEstatus in (1,2,5,3)  
						and FIND_IN_SET(cn1.IdContratista, contatistas)
					group by d1.IdEstatus,e1.descripcion
				UNION ALL
					SELECT '8','Vencido',COUNT(c.idDocumento) q 
					FROM tbl_documentos AS c
						INNER JOIN tbl_contrato cn2 ON c.contrato_id = cn2.contrato_id
					WHERE c.IdEstatusDocumento=2
						and FIND_IN_SET(cn2.IdContratista, contatistas)
					GROUP BY 1

				) as j
				group by 1,2
				order by 1;
            END
        ");
    }
}
