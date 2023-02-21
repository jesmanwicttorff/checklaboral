<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSpAlertasCampoRedundanteIdDocumento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `prActualizaAlertasXtipoDoc`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` PROCEDURE `prActualizaAlertasXtipoDoc`(
    IN aIdDocumento INT, 
    IN aIdTipoDocumento INT, 
    IN aEntidad INT, 
    IN aIdEntidad INT,
    IN aIdEstatus INT, 
    IN aContrato_id INT,
    IN aCreado datetime, 
    IN aActualizado datetime, 
    IN aVencido datetime)
BEGIN
    declare aCreado1 int;
    declare aActualizado1 int;
    declare aVencido1 int; 
    DECLARE umbralBajo INT DEFAULT 0;
    DECLARE umbralAlto INT DEFAULT 0;
    
    DECLARE veces INT DEFAULT 0;

    DECLARE taa INT DEFAULT 0;
    DECLARE pra INT DEFAULT 0;
    DECLARE um INT DEFAULT 0;
    DECLARE cr INT DEFAULT 2;
    DECLARE pr INT DEFAULT 0;
    DECLARE ta INT DEFAULT 0;
    DECLARE td INT DEFAULT 0;
    DECLARE id_terceros INT DEFAULT 0;
    DECLARE idAlertaMaster INT default 0;
    declare aId_terceros int default 0;
    DECLARE mn CHAR(200);
    DECLARE done1, done4, done3, no_more_rows INT DEFAULT FALSE;
    DECLARE cur3 CURSOR FOR 
        SELECT id
        FROM  tb_users as u
        left join tb_groups as g on u.group_id=g.group_id
        WHERE g.group_id=pr
        ORDER BY id DESC;
    DECLARE cur2 CURSOR FOR 
        SELECT id
        FROM  tb_users as u
        left join tb_groups as g on u.group_id=g.group_id
        WHERE g.group_id=pr
        ORDER BY id ASC;
    
    DECLARE cur1 CURSOR FOR 
    SELECT 
        m.id_alerta_master, umbral, a.tbl_alerta_crit_id_crit, m.tbl_alerta_tpo_tipo_alerta, CONCAT(z.desc_crit,' - ',alerta_texto_mensaje), m.tbl_tipos_documentos_IdTipoDocumento, a.tb_groups_group_id
        FROM tbl_alerta_detalle as a 
        inner join tbl_alerta_master as m ON m.id_alerta_master=a.id_alerta_master
        inner join tbl_alerta_crit as c ON a.tbl_alerta_crit_id_crit=c.id_crit 
        left join tbl_tipos_documentos as td on m.tbl_tipos_documentos_IdTipoDocumento=td.IdTipoDocumento
        left join tbl_alerta_crit as z on z.id_crit=a.tbl_alerta_crit_id_crit
        WHERE m.tbl_tipos_documentos_IdTipoDocumento=aIdTipoDocumento
        and m.tbl_alerta_tpo_tipo_alerta=1 
        ORDER BY m.id_alerta_master,tbl_tipos_documentos_IdTipoDocumento, tb_groups_group_id,umbral ASC;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done1 = 1;

    select  datediff( CURDATE(), aCreado ) into aCreado1 ;
    select  datediff(CURDATE(), aActualizado) into aActualizado1;
    select  datediff(aVencido, CURDATE()) into aVencido1;

    
    DELETE FROM tb_notification where IdDocumento=aIdDocumento;
    DELETE FROM tbl_alertas where documento_id=aIdDocumento;

    insert into bitacoraalertas values (0,concat(now(),' - Ejecutando prActualizaAlertasXtipoDoc ',aIdDocumento));
    OPEN cur1;
    read_loop: LOOP
        FETCH cur1 INTO idAlertaMaster, um, cr, ta, mn, td, pr;
        IF done1 THEN leave read_loop; END IF;
        SET veces = veces+1;
        
        
        IF taa = -1 THEN
            SET taa = ta;
        END IF;
        IF taa != ta OR pra != pr THEN
            SET taa = ta;
            SET pra = pr;
            SET umbralAlto = 0;
        END IF;
        SET umbralBajo = umbralAlto;
        SET umbralAlto = um;

        
        
        
        IF umbralBajo = 0 THEN
            IF (pr=6 or pr=4) THEN

insert into bitacoraalertas values (0,concat(now(),' Vencimiento 0 - ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                        case when pr=4 then
                                ifnull(cc.admin_id,c2.admin_id)
                            else
                                d.entry_by_access
                        end,
                        d.IdDocumento,
                    ta, 
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                    END as mensaje_alerta
                    ,td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdDocumento=aIdDocumento
                    AND d.IdEstatus=5       
                    AND datediff(d.FechaVencimiento, CURDATE()) < umbralAlto and datediff(d.FechaVencimiento, CURDATE()) > 0;
                
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, d.IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                    END as mensaje_alerta
                    ,NOW(), idAlertaMaster,
                    CASE WHEN pr=1 THEN 1
                        else d.entry_by_access
                    END
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdDocumento=aIdDocumento
                    AND d.IdEstatus=5       
                    AND datediff(d.FechaVencimiento, CURDATE()) < umbralAlto and datediff(d.FechaVencimiento, CURDATE()) > 0;
            ELSE
                
                block2Vencimiento0: BEGIN
                    DECLARE curV2 CURSOR FOR 
                        SELECT id
                        FROM  tb_users as u
                        left join tb_groups as g on u.group_id=g.group_id
                        WHERE g.group_id=pr
                        ORDER BY id ASC;
                    DECLARE CONTINUE handler for not found SET no_more_rows := true;
                    SET no_more_rows := false;
                    OPEN curV2;
                    read_loopV2: loop
                    FETCH curV2 INTO Id_terceros;
                        IF no_more_rows THEN leave read_loopV2; END IF;
                        if no_more_rows then
                            close curV2;
                            leave read_loopV2; 
                        END IF;

    insert into bitacoraalertas values (0,concat(now(),' Vencimiento Resto 0 - ',veces,' ',Id_terceros,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                        
                        INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                        SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                            Id_terceros,
                            d.IdDocumento,
                            ta,
                            NOW(), 
                            d.FechaVencimiento, 
                            NOW(), 
                            NOW(),
                            CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            END as mensaje_alerta
                            ,td
                        FROM `tbl_documentos` as d
                            LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                            LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                            LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                            left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                            LEFT JOIN tb_users ON c.entry_by = tb_users.id
                            left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                            left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                            left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                        WHERE d.IdTipoDocumento=td  
                            AND d.IdDocumento=aIdDocumento
                            AND d.IdEstatus=5       
                            AND datediff(d.FechaVencimiento, CURDATE()) < umbralAlto and datediff(d.FechaVencimiento, CURDATE()) > 0;
                        
                        insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                        SELECT idAlertaMaster,ta, d.IdDocumento,td,
                            Id_terceros, 
                            mn, 
                            CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            END as mensaje_alerta
                            ,NOW(), idAlertaMaster, id_terceros
                        FROM `tbl_documentos` as d
                            LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                            LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                            LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                            left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                            LEFT JOIN tb_users ON c.entry_by = tb_users.id
                            left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                            left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                            left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                        WHERE d.IdTipoDocumento=td  
                            AND d.IdDocumento=aIdDocumento
                            AND d.IdEstatus=5       
                            AND datediff(d.FechaVencimiento, CURDATE()) < umbralAlto and datediff(d.FechaVencimiento, CURDATE()) > 0;
                    END LOOP read_loopV2;
                END block2Vencimiento0;
            END if;
        END if;
        IF umbralBajo != 0 THEN
            IF (pr=6 or pr=4) THEN

insert into bitacoraalertas values (0,concat(now(),' Vencimiento 1 - ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                        case when pr=4 then
                                ifnull(cc.admin_id,c2.admin_id)
                            else
                                d.entry_by_access
                        end,
                        d.IdDocumento,
                    ta,
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                    END as mensaje_alerta
                    ,td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdDocumento=aIdDocumento
                    AND d.IdEstatus=5       
                    AND datediff(d.FechaVencimiento, CURDATE()) > umbralBajo
                    AND datediff(d.FechaVencimiento, CURDATE()) <= umbralAlto;
                
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, d.IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                    END as mensaje_alerta
                    ,NOW(), idAlertaMaster,
                    CASE WHEN pr=1 THEN 1
                        else d.entry_by_access
                    END
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdDocumento=aIdDocumento
                    AND d.IdEstatus=5       
                    AND datediff(d.FechaVencimiento, CURDATE()) >= umbralBajo
                    AND datediff(d.FechaVencimiento, CURDATE()) <= umbralAlto;
            ELSE
                
                block2VencimientoV12: BEGIN
                    DECLARE curV12 CURSOR FOR 
                        SELECT id
                        FROM  tb_users as u
                        left join tb_groups as g on u.group_id=g.group_id
                        WHERE g.group_id=pr
                        ORDER BY id ASC;
                    DECLARE CONTINUE handler for not found SET no_more_rows := true;
                    SET no_more_rows := false;
                    OPEN curV12;
                    read_loopV12: loop
                    FETCH curV12 INTO Id_terceros;
                        IF no_more_rows THEN leave read_loopV12; END IF;
                        if no_more_rows then
                            close curV12;
                            leave read_loopV12; 
                        END IF;

    insert into bitacoraalertas values (0,concat(now(),' Vencimiento 1 - ',Id_terceros,' ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                        
                        INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                        SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                            Id_terceros, 
                            d.IdDocumento,
                            ta,
                            NOW(), 
                            d.FechaVencimiento, 
                            NOW(), 
                            NOW(),
                            CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            END as mensaje_alerta
                            ,td
                        FROM `tbl_documentos` as d
                            LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                            LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                            LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                            left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                            LEFT JOIN tb_users ON c.entry_by = tb_users.id
                            left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                            left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                            left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                        WHERE d.IdTipoDocumento=td  
                            AND d.IdDocumento=aIdDocumento
                            AND d.IdEstatus=5       
                            AND datediff(d.FechaVencimiento, CURDATE()) >= umbralBajo
                            AND datediff(d.FechaVencimiento, CURDATE()) <= umbralAlto;
                        
                        insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                        SELECT idAlertaMaster,ta, d.IdDocumento,td,
                            Id_terceros, 
                            mn, 
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        END as mensaje_alerta
                            ,NOW(), idAlertaMaster, id_terceros
                        FROM `tbl_documentos` as d
                            LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                            LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                            LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                            left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                            LEFT JOIN tb_users ON c.entry_by = tb_users.id
                            left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                            left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                            left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                        WHERE d.IdTipoDocumento=td  
                            AND d.IdDocumento=aIdDocumento
                            AND d.IdEstatus=5       
                            AND datediff(d.FechaVencimiento, CURDATE()) >= umbralBajo
                            AND datediff(d.FechaVencimiento, CURDATE()) <= umbralAlto;
                    END LOOP read_loopV12;
                END block2VencimientoV12;
            END if;
        END if;
    END LOOP read_loop;
    close cur1;
    
    blockNoVencimientos:BEGIN
        DECLARE cur3 CURSOR FOR 
        SELECT m.id_alerta_master, umbral, a.tbl_alerta_crit_id_crit, m.tbl_alerta_tpo_tipo_alerta, CONCAT(z.desc_crit,' - ',alerta_texto_mensaje), m.tbl_tipos_documentos_IdTipoDocumento, a.tb_groups_group_id
            FROM tbl_alerta_detalle as a 
            inner join tbl_alerta_master as m ON m.id_alerta_master=a.id_alerta_master
            inner join tbl_alerta_crit as c ON a.tbl_alerta_crit_id_crit=c.id_crit 
            left join tbl_tipos_documentos as td on m.tbl_tipos_documentos_IdTipoDocumento=td.IdTipoDocumento
            left join tbl_alerta_crit as z on z.id_crit=a.tbl_alerta_crit_id_crit
        WHERE tbl_alerta_tpo_tipo_alerta!=1
            and m.tbl_tipos_documentos_IdTipoDocumento=aIdTipoDocumento
            ORDER BY m.id_alerta_master,tb_groups_group_id,umbral desc;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done3 = 1;
    SET umbralAlto = 0;
    SET veces = 0;
    SET taa = -1;
    OPEN cur3;
    read_loop3: LOOP
        SET veces = veces+1;
        FETCH cur3 INTO idAlertaMaster, um, cr, ta, mn, td, pr;
        IF done3 THEN leave read_loop3; END IF;
        IF taa = -1 THEN
            SET taa = ta;
        END IF;
        IF taa != ta OR pra != pr THEN
            SET taa = ta;
            SET pra = pr;
            SET umbralAlto = 0;
        END IF;
        SET umbralBajo = umbralAlto;
        SET umbralAlto = um;
        

        
        
        
        
        if ta=2 and umbralBajo!=0 then
            IF pr=6 or pr=4 THEN

insert into bitacoraalertas values (0,concat(now(),' - Por Cargar1 ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    d.IdDocumento,
                    ta, 
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                    td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=1   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.createdOn) >= umbralBajo
                    AND datediff(CURDATE(),d.createdOn) <= umbralAlto;
                
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, d.IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                    NOW(), idAlertaMaster,
                    CASE WHEN pr=1 THEN 1
                        else d.entry_by_access
                    END
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=1   
                    AND d.IdDocumento=aIdDocumento
                    and d.contrato_id is not null
                    AND datediff(CURDATE(),d.createdOn) >= umbralBajo
                    AND datediff(CURDATE(),d.createdOn) <= umbralAlto;
            ELSE
                
                blockC12: BEGIN
                DECLARE curC12 CURSOR FOR 
                    SELECT id
                    FROM  tb_users as u
                    left join tb_groups as g on u.group_id=g.group_id
                    WHERE g.group_id=pr
                    ORDER BY id ASC;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_rows = 1;
                SET no_more_rows := false;
                OPEN curC12;
                read_loopC12: loop
                    FETCH curC12 INTO Id_terceros;
                    IF no_more_rows THEN leave read_loopC12; END IF;
                    if aId_terceros < id_terceros then
                        set aId_terceros = id_terceros;
                    else
                        leave read_loopC12; 
                    END IF;

insert into bitacoraalertas values (0,concat(now(),' - Por Cargar2 ',veces,'|',Id_terceros,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                    
                    INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            id_terceros
                    end,
                        d.IdDocumento,
                        ta,
                        NOW(), 
                        d.FechaVencimiento, 
                        NOW(), 
                        NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                        td
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=1   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.createdOn) >= umbralBajo
                        AND datediff(CURDATE(),d.createdOn) <= umbralAlto;
                    
                    insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                    SELECT idAlertaMaster,ta, d.IdDocumento,td,
                        case when pr=4 then
                                ifnull(cc.admin_id,c2.admin_id)
                            else
                                id_terceros
                        end,
                        mn, 
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                        NOW(), idAlertaMaster, id_terceros
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=1   
                        AND d.IdDocumento=aIdDocumento
                        and d.contrato_id is not null
                        AND datediff(CURDATE(),d.createdOn) >= umbralBajo
                        AND datediff(CURDATE(),d.createdOn) <= umbralAlto;
                END LOOP read_loopC12;
                CLOSE curC12;
                END blockC12;
            END IF;
        end if;
        if ta=2 and umbralBajo=0 then
            
            
            
            IF pr=6 or pr=4  THEN
insert into bitacoraalertas values (0,concat(now(),' - Por Cargar, Contratista/Administrador EA, 0 / ',pr,'|',umbralBajo,'|',umbralAlto));
                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    d.IdDocumento,
                    ta, 
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                    td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON  d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=1   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.createdOn) > umbralAlto;
                
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, d.IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                    NOW(), idAlertaMaster,
                    d.entry_by
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=1   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.createdOn) > umbralAlto;
            ELSE
                
                blockC2: BEGIN
                DECLARE stop INT DEFAULT 0;
                DECLARE curC2 CURSOR FOR 
                    SELECT id
                    FROM  tb_users as u
                    left join tb_groups as g on u.group_id=g.group_id
                    WHERE g.group_id=pr
                    ORDER BY id ASC;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_rows = 1;
                SET no_more_rows := false;
                OPEN curC2;
                read_loopC2: LOOP
                    FETCH curC2 INTO Id_terceros;
                    IF no_more_rows THEN leave read_loopC2; END IF;

                    
insert into bitacoraalertas values (0,concat(now(),' - Por Cargar, resto ',stop, id_terceros,'|',umbralBajo,'|',umbralAlto));

                    INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            id_terceros
                    end,
                        d.IdDocumento,
                        ta,
                        NOW(), 
                        d.FechaVencimiento, 
                        NOW(), 
                        NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                        td
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=1 
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.createdOn) > umbralAlto;

                    if pr=1 then
                        set stop = 1;
                    end if;
                    
                    insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                    SELECT idAlertaMaster,ta, d.IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            id_terceros
                    end,
                        mn, 
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                        NOW(), idAlertaMaster, id_terceros
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=1   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.createdOn) > umbralAlto;
                END LOOP read_loopC2;
                CLOSE curC2;
                END blockC2;
            END IF;
        end if;
        
        
        
        if ta=3 and umbralBajo!=0  then
            IF pr=6 or pr=4 THEN

insert into bitacoraalertas values (0,concat(now(),' - Por Aprobar1 ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    d.IdDocumento,
                    ta, 
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    END as mensaje_alerta
                    ,td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=2   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.updatedOn) > umbralBajo
                    AND datediff(CURDATE(),d.updatedOn) <= umbralAlto;

            
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, d.IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    END as mensaje_alerta
                    ,NOW(), idAlertaMaster,
                    case WHEN pr=6 THEN c.entry_by_access 
                        WHEN pr=4 THEN c.admin_id 
                        WHEN pr=1 THEN d.entry_by_access
                    end
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=2   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.updatedOn) >= umbralBajo
                    AND datediff(CURDATE(),d.updatedOn) <= umbralAlto;
            ELSE
                
                blockA12: BEGIN
                DECLARE no_more_rowsA INT DEFAULT FALSE;
                DECLARE curA12 CURSOR FOR 
                    SELECT id
                    FROM  tb_users as u
                    left join tb_groups as g on u.group_id=g.group_id
                    WHERE g.group_id=pr
                    ORDER BY id ASC;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_rowsA = 1;
                SET no_more_rows := false;
                OPEN curA12;
                read_loopA12: LOOP
                    FETCH curA12 INTO Id_terceros;
                    IF no_more_rowsA THEN leave read_loopA12; END IF;
insert into bitacoraalertas values (0,concat(now(),' - Por Aprobar1 ',veces,' ',Id_terceros,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));
                    
                    INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                        id_terceros, 
                        d.IdDocumento,
                        ta,
                        NOW(), 
                        d.FechaVencimiento, 
                        NOW(), 
                        NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        END as mensaje_alerta
                        ,td
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=2   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.updatedOn) > umbralBajo
                        AND datediff(CURDATE(),d.updatedOn) <= umbralAlto;
                    
                    insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                    SELECT idAlertaMaster,ta, d.IdDocumento,td,
                        id_terceros, mn,  
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        END as mensaje_alerta
                        ,NOW(), idAlertaMaster, id_terceros
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.contrato_id=d.contrato_id
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=2   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.updatedOn) >= umbralBajo
                        AND datediff(CURDATE(),d.updatedOn) <= umbralAlto;
                END LOOP read_loopA12;
                CLOSE curA12;
                END blockA12;
            END IF;
        end if;
        if ta=3 and umbralBajo=0  then
            
            
            
            IF pr=6 or pr=4 THEN
insert into bitacoraalertas values (0,concat(now(),' - Por Aprobar0 ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));
                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    d.IdDocumento,
                    ta, 
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    END as mensaje_alerta
                    ,td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=2   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.updatedOn) > umbralAlto;
            
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, d.IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    END as mensaje_alerta
                    ,NOW(), idAlertaMaster,
                    case WHEN pr=6 THEN c.entry_by_access 
                        WHEN pr=4 THEN ifnull(cc.admin_id,c2.admin_id)
                        WHEN pr=1 THEN d.entry_by_access
                    end
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON  d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=2   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.updatedOn) > umbralAlto;
            ELSE
                
                aprobar0resto: BEGIN
                DECLARE curA2 CURSOR FOR 
                    SELECT id
                    FROM  tb_users as u
                    left join tb_groups as g on u.group_id=g.group_id
                    WHERE g.group_id=pr
                    ORDER BY id ASC;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_rows = 1;
                SET no_more_rows := false;
                OPEN curA2;
                read_loopA2: LOOP
                    FETCH curA2 INTO Id_terceros;
                    IF no_more_rows THEN leave read_loopA2; END IF;
                    if aId_terceros < id_terceros then
                        set aId_terceros = id_terceros;
                    else
                        leave read_loopA2; 
                    END IF;
insert into bitacoraalertas values (0,concat(now(),' - Por Aprobar, Resto, 0 / ',pr, ' ',Id_terceros,'|',umbralBajo,'|',umbralAlto));
                    
                    INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                        id_terceros, 
                        d.IdDocumento,
                        ta,
                        NOW(), 
                        d.FechaVencimiento, 
                        NOW(), 
                        NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        END as mensaje_alerta
                        ,td
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON  d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.contrato_id=d.contrato_id
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=2   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.updatedOn) > umbralAlto;
                    
                    insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                    SELECT idAlertaMaster,ta, d.IdDocumento,td,
                        id_terceros, mn,  
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        END as mensaje_alerta
                        ,NOW(), idAlertaMaster, id_terceros
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON  d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.contrato_id=d.contrato_id
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=2   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.updatedOn) > umbralAlto;
                END LOOP read_loopA2;
                CLOSE curA2;
                END aprobar0resto;
            END IF;
        end if;
    END LOOP read_loop3;
    CLOSE cur3;
    END blockNoVencimientos;
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `prActualizaAlertasXtipoDoc`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` PROCEDURE `prActualizaAlertasXtipoDoc`(
    IN aIdDocumento INT, 
    IN aIdTipoDocumento INT, 
    IN aEntidad INT, 
    IN aIdEntidad INT,
    IN aIdEstatus INT, 
    IN aContrato_id INT,
    IN aCreado datetime, 
    IN aActualizado datetime, 
    IN aVencido datetime)
BEGIN
    declare aCreado1 int;
    declare aActualizado1 int;
    declare aVencido1 int;
    DECLARE umbralBajo INT DEFAULT 0;
    DECLARE umbralAlto INT DEFAULT 0;
    
    DECLARE veces INT DEFAULT 0;

    DECLARE taa INT DEFAULT 0;
    DECLARE pra INT DEFAULT 0;
    DECLARE um INT DEFAULT 0;
    DECLARE cr INT DEFAULT 2;
    DECLARE pr INT DEFAULT 0;
    DECLARE ta INT DEFAULT 0;
    DECLARE td INT DEFAULT 0;
    DECLARE id_terceros INT DEFAULT 0;
    DECLARE idAlertaMaster INT default 0;
    declare aId_terceros int default 0;
    DECLARE mn CHAR(200);
    DECLARE done1, done4, done3, no_more_rows INT DEFAULT FALSE;
    DECLARE cur3 CURSOR FOR 
        SELECT id
        FROM  tb_users as u
        left join tb_groups as g on u.group_id=g.group_id
        WHERE g.group_id=pr
        ORDER BY id DESC;
    DECLARE cur2 CURSOR FOR 
        SELECT id
        FROM  tb_users as u
        left join tb_groups as g on u.group_id=g.group_id
        WHERE g.group_id=pr
        ORDER BY id ASC;
    
    DECLARE cur1 CURSOR FOR 
    SELECT 
        m.id_alerta_master, umbral, a.tbl_alerta_crit_id_crit, m.tbl_alerta_tpo_tipo_alerta, CONCAT(z.desc_crit,' - ',alerta_texto_mensaje), m.tbl_tipos_documentos_IdTipoDocumento, a.tb_groups_group_id
        FROM tbl_alerta_detalle as a 
        inner join tbl_alerta_master as m ON m.id_alerta_master=a.id_alerta_master
        inner join tbl_alerta_crit as c ON a.tbl_alerta_crit_id_crit=c.id_crit 
        left join tbl_tipos_documentos as td on m.tbl_tipos_documentos_IdTipoDocumento=td.IdTipoDocumento
        left join tbl_alerta_crit as z on z.id_crit=a.tbl_alerta_crit_id_crit
        WHERE m.tbl_tipos_documentos_IdTipoDocumento=aIdTipoDocumento
        and m.tbl_alerta_tpo_tipo_alerta=1 
        ORDER BY m.id_alerta_master,tbl_tipos_documentos_IdTipoDocumento, tb_groups_group_id,umbral ASC;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done1 = 1;

    select  datediff( CURDATE(), aCreado ) into aCreado1 ;
    select  datediff(CURDATE(), aActualizado) into aActualizado1;
    select  datediff(aVencido, CURDATE()) into aVencido1;

    
    DELETE FROM tb_notification where IdDocumento=aIdDocumento;
    DELETE FROM tbl_alertas where documento_id=aIdDocumento;

    insert into bitacoraalertas values (0,concat(now(),' - Ejecutando prActualizaAlertasXtipoDoc ',aIdDocumento));
    OPEN cur1;
    read_loop: LOOP
        FETCH cur1 INTO idAlertaMaster, um, cr, ta, mn, td, pr;
        IF done1 THEN leave read_loop; END IF;
        SET veces = veces+1;
        
        
        IF taa = -1 THEN
            SET taa = ta;
        END IF;
        IF taa != ta OR pra != pr THEN
            SET taa = ta;
            SET pra = pr;
            SET umbralAlto = 0;
        END IF;
        SET umbralBajo = umbralAlto;
        SET umbralAlto = um;

        
        
        
        IF umbralBajo = 0 THEN
            IF (pr=6 or pr=4) THEN

insert into bitacoraalertas values (0,concat(now(),' Vencimiento 0 - ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                        case when pr=4 then
                                ifnull(cc.admin_id,c2.admin_id)
                            else
                                d.entry_by_access
                        end,
                        d.IdDocumento,
                    ta, 
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                    END as mensaje_alerta
                    ,td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdDocumento=aIdDocumento
                    AND d.IdEstatus=5       
                    AND datediff(d.FechaVencimiento, CURDATE()) < umbralAlto and datediff(d.FechaVencimiento, CURDATE()) > 0;
                
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, d.IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                    END as mensaje_alerta
                    ,NOW(), idAlertaMaster,
                    CASE WHEN pr=1 THEN 1
                        else d.entry_by_access
                    END
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdDocumento=aIdDocumento
                    AND d.IdEstatus=5       
                    AND datediff(d.FechaVencimiento, CURDATE()) < umbralAlto and datediff(d.FechaVencimiento, CURDATE()) > 0;
            ELSE
                
                block2Vencimiento0: BEGIN
                    DECLARE curV2 CURSOR FOR 
                        SELECT id
                        FROM  tb_users as u
                        left join tb_groups as g on u.group_id=g.group_id
                        WHERE g.group_id=pr
                        ORDER BY id ASC;
                    DECLARE CONTINUE handler for not found SET no_more_rows := true;
                    SET no_more_rows := false;
                    OPEN curV2;
                    read_loopV2: loop
                    FETCH curV2 INTO Id_terceros;
                        IF no_more_rows THEN leave read_loopV2; END IF;
                        if no_more_rows then
                            close curV2;
                            leave read_loopV2; 
                        END IF;

    insert into bitacoraalertas values (0,concat(now(),' Vencimiento Resto 0 - ',veces,' ',Id_terceros,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                        
                        INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                        SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                            Id_terceros,
                            d.IdDocumento,
                            ta,
                            NOW(), 
                            d.FechaVencimiento, 
                            NOW(), 
                            NOW(),
                            CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            END as mensaje_alerta
                            ,td
                        FROM `tbl_documentos` as d
                            LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                            LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                            LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                            left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                            LEFT JOIN tb_users ON c.entry_by = tb_users.id
                            left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                            left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                            left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                        WHERE d.IdTipoDocumento=td  
                            AND d.IdDocumento=aIdDocumento
                            AND d.IdEstatus=5       
                            AND datediff(d.FechaVencimiento, CURDATE()) < umbralAlto and datediff(d.FechaVencimiento, CURDATE()) > 0;
                        
                        insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                        SELECT idAlertaMaster,ta, d.IdDocumento,td,
                            Id_terceros, 
                            mn, 
                            CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            END as mensaje_alerta
                            ,NOW(), idAlertaMaster, id_terceros
                        FROM `tbl_documentos` as d
                            LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                            LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                            LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                            left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                            LEFT JOIN tb_users ON c.entry_by = tb_users.id
                            left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                            left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                            left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                        WHERE d.IdTipoDocumento=td  
                            AND d.IdDocumento=aIdDocumento
                            AND d.IdEstatus=5       
                            AND datediff(d.FechaVencimiento, CURDATE()) < umbralAlto and datediff(d.FechaVencimiento, CURDATE()) > 0;
                    END LOOP read_loopV2;
                END block2Vencimiento0;
            END if;
        END if;
        IF umbralBajo != 0 THEN
            IF (pr=6 or pr=4) THEN

insert into bitacoraalertas values (0,concat(now(),' Vencimiento 1 - ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                        case when pr=4 then
                                ifnull(cc.admin_id,c2.admin_id)
                            else
                                d.entry_by_access
                        end,
                        d.IdDocumento,
                    ta,
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                    END as mensaje_alerta
                    ,td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdDocumento=aIdDocumento
                    AND d.IdEstatus=5       
                    AND datediff(d.FechaVencimiento, CURDATE()) > umbralBajo
                    AND datediff(d.FechaVencimiento, CURDATE()) <= umbralAlto;
                
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, d.IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                    END as mensaje_alerta
                    ,NOW(), idAlertaMaster,
                    CASE WHEN pr=1 THEN 1
                        else d.entry_by_access
                    END
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdDocumento=aIdDocumento
                    AND d.IdEstatus=5       
                    AND datediff(d.FechaVencimiento, CURDATE()) >= umbralBajo
                    AND datediff(d.FechaVencimiento, CURDATE()) <= umbralAlto;
            ELSE
                
                block2VencimientoV12: BEGIN
                    DECLARE curV12 CURSOR FOR 
                        SELECT id
                        FROM  tb_users as u
                        left join tb_groups as g on u.group_id=g.group_id
                        WHERE g.group_id=pr
                        ORDER BY id ASC;
                    DECLARE CONTINUE handler for not found SET no_more_rows := true;
                    SET no_more_rows := false;
                    OPEN curV12;
                    read_loopV12: loop
                    FETCH curV12 INTO Id_terceros;
                        IF no_more_rows THEN leave read_loopV12; END IF;
                        if no_more_rows then
                            close curV12;
                            leave read_loopV12; 
                        END IF;

    insert into bitacoraalertas values (0,concat(now(),' Vencimiento 1 - ',Id_terceros,' ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                        
                        INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                        SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                            Id_terceros, 
                            d.IdDocumento,
                            ta,
                            NOW(), 
                            d.FechaVencimiento, 
                            NOW(), 
                            NOW(),
                            CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                                WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            END as mensaje_alerta
                            ,td
                        FROM `tbl_documentos` as d
                            LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                            LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                            LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                            left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                            LEFT JOIN tb_users ON c.entry_by = tb_users.id
                            left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                            left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                            left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                        WHERE d.IdTipoDocumento=td  
                            AND d.IdDocumento=aIdDocumento
                            AND d.IdEstatus=5       
                            AND datediff(d.FechaVencimiento, CURDATE()) >= umbralBajo
                            AND datediff(d.FechaVencimiento, CURDATE()) <= umbralAlto;
                        
                        insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                        SELECT idAlertaMaster,ta, IdDocumento,td,
                            Id_terceros, 
                            mn, 
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',td.Descripcion,' del Contratista ',co.RazonSocial,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            WHEN d.Entidad=2 THEN CONCAT('Documento ',td.Descripcion,' del Contrato ',c.cont_proveedor,' ',c.cont_numero,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',td.Descripcion,' de la Persona ',p.RUT,' ',p.Nombres,' ',p.Apellidos,', asociado a contrato ',ifnull(cc.cont_numero,' '),' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                            WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',c.cont_numero,' y el ',ce.Descripcion,' con vencimiento en los siguientes ',datediff(d.FechaVencimiento, CURDATE()),' días.')
                        END as mensaje_alerta
                            ,NOW(), idAlertaMaster, id_terceros
                        FROM `tbl_documentos` as d
                            LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                            LEFT JOIN `tbl_contrato` as c ON d.`Entidad` = 2 AND d.`IdEntidad` = c.`contrato_id`
                            LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                            left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                            LEFT JOIN tb_users ON c.entry_by = tb_users.id
                            left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                            left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                            left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                        WHERE d.IdTipoDocumento=td  
                            AND d.IdDocumento=aIdDocumento
                            AND d.IdEstatus=5       
                            AND datediff(d.FechaVencimiento, CURDATE()) >= umbralBajo
                            AND datediff(d.FechaVencimiento, CURDATE()) <= umbralAlto;
                    END LOOP read_loopV12;
                END block2VencimientoV12;
            END if;
        END if;
    END LOOP read_loop;
    close cur1;
    
    blockNoVencimientos:BEGIN
        DECLARE cur3 CURSOR FOR 
        SELECT m.id_alerta_master, umbral, a.tbl_alerta_crit_id_crit, m.tbl_alerta_tpo_tipo_alerta, CONCAT(z.desc_crit,' - ',alerta_texto_mensaje), m.tbl_tipos_documentos_IdTipoDocumento, a.tb_groups_group_id
            FROM tbl_alerta_detalle as a 
            inner join tbl_alerta_master as m ON m.id_alerta_master=a.id_alerta_master
            inner join tbl_alerta_crit as c ON a.tbl_alerta_crit_id_crit=c.id_crit 
            left join tbl_tipos_documentos as td on m.tbl_tipos_documentos_IdTipoDocumento=td.IdTipoDocumento
            left join tbl_alerta_crit as z on z.id_crit=a.tbl_alerta_crit_id_crit
        WHERE tbl_alerta_tpo_tipo_alerta!=1
            and m.tbl_tipos_documentos_IdTipoDocumento=aIdTipoDocumento
            ORDER BY m.id_alerta_master,tb_groups_group_id,umbral desc;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done3 = 1;
    SET umbralAlto = 0;
    SET veces = 0;
    SET taa = -1;
    OPEN cur3;
    read_loop3: LOOP
        SET veces = veces+1;
        FETCH cur3 INTO idAlertaMaster, um, cr, ta, mn, td, pr;
        IF done3 THEN leave read_loop3; END IF;
        IF taa = -1 THEN
            SET taa = ta;
        END IF;
        IF taa != ta OR pra != pr THEN
            SET taa = ta;
            SET pra = pr;
            SET umbralAlto = 0;
        END IF;
        SET umbralBajo = umbralAlto;
        SET umbralAlto = um;
        

        
        
        
        
        if ta=2 and umbralBajo!=0 then
            IF pr=6 or pr=4 THEN

insert into bitacoraalertas values (0,concat(now(),' - Por Cargar1 ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    d.IdDocumento,
                    ta, 
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                    td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=1   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.createdOn) >= umbralBajo
                    AND datediff(CURDATE(),d.createdOn) <= umbralAlto;
                
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, d.IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                    NOW(), idAlertaMaster,
                    CASE WHEN pr=1 THEN 1
                        else d.entry_by_access
                    END
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=1   
                    AND d.IdDocumento=aIdDocumento
                    and d.contrato_id is not null
                    AND datediff(CURDATE(),d.createdOn) >= umbralBajo
                    AND datediff(CURDATE(),d.createdOn) <= umbralAlto;
            ELSE
                
                blockC12: BEGIN
                DECLARE curC12 CURSOR FOR 
                    SELECT id
                    FROM  tb_users as u
                    left join tb_groups as g on u.group_id=g.group_id
                    WHERE g.group_id=pr
                    ORDER BY id ASC;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_rows = 1;
                SET no_more_rows := false;
                OPEN curC12;
                read_loopC12: loop
                    FETCH curC12 INTO Id_terceros;
                    IF no_more_rows THEN leave read_loopC12; END IF;
                    if aId_terceros < id_terceros then
                        set aId_terceros = id_terceros;
                    else
                        leave read_loopC12; 
                    END IF;

insert into bitacoraalertas values (0,concat(now(),' - Por Cargar2 ',veces,'|',Id_terceros,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                    
                    INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            id_terceros
                    end,
                        d.IdDocumento,
                        ta,
                        NOW(), 
                        d.FechaVencimiento, 
                        NOW(), 
                        NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                        td
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=1   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.createdOn) >= umbralBajo
                        AND datediff(CURDATE(),d.createdOn) <= umbralAlto;
                    
                    insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                    SELECT idAlertaMaster,ta, IdDocumento,td,
                        case when pr=4 then
                                ifnull(cc.admin_id,c2.admin_id)
                            else
                                id_terceros
                        end,
                        mn, 
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                        NOW(), idAlertaMaster, id_terceros
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=1   
                        AND d.IdDocumento=aIdDocumento
                        and d.contrato_id is not null
                        AND datediff(CURDATE(),d.createdOn) >= umbralBajo
                        AND datediff(CURDATE(),d.createdOn) <= umbralAlto;
                END LOOP read_loopC12;
                CLOSE curC12;
                END blockC12;
            END IF;
        end if;
        if ta=2 and umbralBajo=0 then
            
            
            
            IF pr=6 or pr=4  THEN
insert into bitacoraalertas values (0,concat(now(),' - Por Cargar, Contratista/Administrador EA, 0 / ',pr,'|',umbralBajo,'|',umbralAlto));
                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    d.IdDocumento,
                    ta, 
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                    td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON  d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=1   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.createdOn) > umbralAlto;
                
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                    NOW(), idAlertaMaster,
                    d.entry_by
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=1   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.createdOn) > umbralAlto;
            ELSE
                
                blockC2: BEGIN
                DECLARE stop INT DEFAULT 0;
                DECLARE curC2 CURSOR FOR 
                    SELECT id
                    FROM  tb_users as u
                    left join tb_groups as g on u.group_id=g.group_id
                    WHERE g.group_id=pr
                    ORDER BY id ASC;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_rows = 1;
                SET no_more_rows := false;
                OPEN curC2;
                read_loopC2: LOOP
                    FETCH curC2 INTO Id_terceros;
                    IF no_more_rows THEN leave read_loopC2; END IF;

                    
insert into bitacoraalertas values (0,concat(now(),' - Por Cargar, resto ',stop, id_terceros,'|',umbralBajo,'|',umbralAlto));

                    INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            id_terceros
                    end,
                        d.IdDocumento,
                        ta,
                        NOW(), 
                        d.FechaVencimiento, 
                        NOW(), 
                        NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                        td
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=1 
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.createdOn) > umbralAlto;

                    if pr=1 then
                        set stop = 1;
                    end if;
                    
                    insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                    SELECT idAlertaMaster,ta, IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            id_terceros
                    end,
                        mn, 
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(cc.cont_numero,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.createdOn),' días en estado Por Cargar. Proceder a su carga en Solicitudes') 
                        end,
                        NOW(), idAlertaMaster, id_terceros
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=1   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.createdOn) > umbralAlto;
                END LOOP read_loopC2;
                CLOSE curC2;
                END blockC2;
            END IF;
        end if;
        
        
        
        if ta=3 and umbralBajo!=0  then
            IF pr=6 or pr=4 THEN

insert into bitacoraalertas values (0,concat(now(),' - Por Aprobar1 ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));

                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    d.IdDocumento,
                    ta, 
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    END as mensaje_alerta
                    ,td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=2   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.updatedOn) > umbralBajo
                    AND datediff(CURDATE(),d.updatedOn) <= umbralAlto;

            
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    END as mensaje_alerta
                    ,NOW(), idAlertaMaster,
                    case WHEN pr=6 THEN c.entry_by_access 
                        WHEN pr=4 THEN c.admin_id 
                        WHEN pr=1 THEN d.entry_by_access
                    end
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=2   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.updatedOn) >= umbralBajo
                    AND datediff(CURDATE(),d.updatedOn) <= umbralAlto;
            ELSE
                
                blockA12: BEGIN
                DECLARE no_more_rowsA INT DEFAULT FALSE;
                DECLARE curA12 CURSOR FOR 
                    SELECT id
                    FROM  tb_users as u
                    left join tb_groups as g on u.group_id=g.group_id
                    WHERE g.group_id=pr
                    ORDER BY id ASC;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_rowsA = 1;
                SET no_more_rows := false;
                OPEN curA12;
                read_loopA12: LOOP
                    FETCH curA12 INTO Id_terceros;
                    IF no_more_rowsA THEN leave read_loopA12; END IF;
insert into bitacoraalertas values (0,concat(now(),' - Por Aprobar1 ',veces,' ',Id_terceros,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));
                    
                    INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                        id_terceros, 
                        d.IdDocumento,
                        ta,
                        NOW(), 
                        d.FechaVencimiento, 
                        NOW(), 
                        NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        END as mensaje_alerta
                        ,td
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=2   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.updatedOn) > umbralBajo
                        AND datediff(CURDATE(),d.updatedOn) <= umbralAlto;
                    
                    insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                    SELECT idAlertaMaster,ta, IdDocumento,td,
                        id_terceros, mn,  
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        END as mensaje_alerta
                        ,NOW(), idAlertaMaster, id_terceros
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.contrato_id=d.contrato_id
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=2   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.updatedOn) >= umbralBajo
                        AND datediff(CURDATE(),d.updatedOn) <= umbralAlto;
                END LOOP read_loopA12;
                CLOSE curA12;
                END blockA12;
            END IF;
        end if;
        if ta=3 and umbralBajo=0  then
            
            
            
            IF pr=6 or pr=4 THEN
insert into bitacoraalertas values (0,concat(now(),' - Por Aprobar0 ',veces,'|',idAlertaMaster,'|', um,'|', cr, '|',ta, '|',mn, '|',td,'|', pr,'|',umbralBajo,'|',umbralAlto));
                
                INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    d.IdDocumento,
                    ta, 
                    NOW(), 
                    d.FechaVencimiento, 
                    NOW(), 
                    NOW(),
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    END as mensaje_alerta
                    ,td
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=2   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.updatedOn) > umbralAlto;
            
                insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                SELECT idAlertaMaster,ta, IdDocumento,td,
                    case when pr=4 then
                            ifnull(cc.admin_id,c2.admin_id)
                        else
                            d.entry_by_access
                    end,
                    mn, 
                    CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                    END as mensaje_alerta
                    ,NOW(), idAlertaMaster,
                    case WHEN pr=6 THEN c.entry_by_access 
                        WHEN pr=4 THEN ifnull(cc.admin_id,c2.admin_id)
                        WHEN pr=1 THEN d.entry_by_access
                    end
                FROM `tbl_documentos` as d
                    LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                    LEFT JOIN `tbl_contrato` as c ON  d.`IdEntidad` = c.`contrato_id`
                    LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                    left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                    LEFT JOIN tb_users ON c.entry_by = tb_users.id
                    left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                    left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                    left join tbl_contrato as c2 on c2.IdContratista=d.IdEntidad
                    left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                WHERE d.IdTipoDocumento=td  
                    AND d.IdEstatus=2   
                    AND d.IdDocumento=aIdDocumento
                    AND datediff(CURDATE(),d.updatedOn) > umbralAlto;
            ELSE
                
                aprobar0resto: BEGIN
                DECLARE curA2 CURSOR FOR 
                    SELECT id
                    FROM  tb_users as u
                    left join tb_groups as g on u.group_id=g.group_id
                    WHERE g.group_id=pr
                    ORDER BY id ASC;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_rows = 1;
                SET no_more_rows := false;
                OPEN curA2;
                read_loopA2: LOOP
                    FETCH curA2 INTO Id_terceros;
                    IF no_more_rows THEN leave read_loopA2; END IF;
                    if aId_terceros < id_terceros then
                        set aId_terceros = id_terceros;
                    else
                        leave read_loopA2; 
                    END IF;
insert into bitacoraalertas values (0,concat(now(),' - Por Aprobar, Resto, 0 / ',pr, ' ',Id_terceros,'|',umbralBajo,'|',umbralAlto));
                    
                    INSERT INTO tbl_alertas (alertas_id, tipo_alerta, id_crit, contrato_id, contratista_id, persona_id, documento_id, id_mensaje, fecha_ini, fecha_fin, fec_ult_alerta, fecha_registro, mensaje_alerta, IdTipoDocumento)
                    SELECT idAlertaMaster, ta, cr, cp.contrato_id, cp.IdContratista, 
                        id_terceros, 
                        d.IdDocumento,
                        ta,
                        NOW(), 
                        d.FechaVencimiento, 
                        NOW(), 
                        NOW(),
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        END as mensaje_alerta
                        ,td
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON  d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.contrato_id=d.contrato_id
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=2   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.updatedOn) > umbralAlto;
                    
                    insert into tb_notification (id,tipo_alerta,IdDocumento,idTipoDocumento, userid, title, note, created, id_alerta_master, entry_by)
                    SELECT idAlertaMaster,ta, IdDocumento,td,
                        id_terceros, mn,  
                        CASE WHEN d.Entidad=1 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contratista ',ifnull(co.RazonSocial,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=2 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' del Contrato ',ifnull(c.cont_proveedor,' '),' ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad>2 and d.Entidad<5 THEN CONCAT('Documento ',ifnull(td.Descripcion,' '),' de la Persona ',ifnull(p.RUT,' '),' ',ifnull(p.Nombres,' '),' ',ifnull(p.Apellidos,' '),', asociado a contrato ',ifnull(c.cont_numero,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        WHEN d.Entidad=6 THEN CONCAT('Documento F30-1 del Contrato ',ifnull(c.cont_numero,' '),' y el ',ifnull(ce.Descripcion,' '),' con ',datediff(CURDATE(),d.updatedOn),' días en estado Por Aprobar. Proceder a su revisi&oacute;n en Gesti&oacute;n de Solicitudes o consultar con &Aacute;rea de Terceros')
                        END as mensaje_alerta
                        ,NOW(), idAlertaMaster, id_terceros
                    FROM `tbl_documentos` as d
                        LEFT JOIN `tbl_contratistas` as co ON d.`Entidad` = 1 AND d.`IdEntidad` = co.`IdContratista`
                        LEFT JOIN `tbl_contrato` as c ON  d.`IdEntidad` = c.`contrato_id`
                        LEFT JOIN  `tbl_personas` as p ON d.`Entidad` = 3 AND d.`IdEntidad` = p.`IdPersona`
                        left join tbl_centro as ce on d.`Entidad` = 6 AND ce.IdCentro=d.IdEntidad
                        LEFT JOIN tb_users ON c.entry_by = tb_users.id
                        left join tbl_contratos_personas as cp on cp.IdPersona=p.IdPersona
                        left join tbl_contrato as cc on cc.contrato_id=cp.contrato_id
                        left join tbl_contrato as c2 on c2.contrato_id=d.contrato_id
                        left join `tbl_tipos_documentos` as td on td.IdTipoDocumento=d.IdTipoDocumento
                    WHERE d.IdTipoDocumento=td  
                        AND d.IdEstatus=2   
                        AND d.IdDocumento=aIdDocumento
                        AND datediff(CURDATE(),d.updatedOn) > umbralAlto;
                END LOOP read_loopA2;
                CLOSE curA2;
                END aprobar0resto;
            END IF;
        end if;
    END LOOP read_loop3;
    CLOSE cur3;
    END blockNoVencimientos;
END");
    }
}
