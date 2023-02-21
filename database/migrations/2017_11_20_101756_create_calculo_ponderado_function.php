<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCalculoPonderadoFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS `fnCalculoPonderado`;");
        DB::unprepared("CREATE DEFINER=`root`@`localhost` FUNCTION `fnCalculoPonderado`(lstrIndicador VARCHAR(5), lintPromedioEvaluacion decimal(10,3)) RETURNS decimal(10,3)
                        BEGIN
                          DECLARE lintResultado decimal(10,3);
                          DECLARE lintDiferencia decimal(10,3);
                          
                            IF lstrIndicador='evp' THEN
                              
                                SET lintDiferencia = (lintPromedioEvaluacion - 70);
                                IF lintPromedioEvaluacion >= 70 THEN 
                                    SET lintResultado = 75 + ((25/30)*lintDiferencia);
                                ELSE 
                                    SET lintResultado = 75 + ((75/70)*lintDiferencia);
                                END IF;
                            
                            ELSEIF lstrIndicador='esf' THEN
                            
                                SET lintDiferencia = (lintPromedioEvaluacion - 50);
                                IF lintPromedioEvaluacion > 50 THEN 
                                    SET lintResultado = 75 - ((75/50)*lintDiferencia);
                                ELSE
                                  SET lintResultado = 75 - ((25/50)*lintDiferencia);
                                END IF;
                                
                            ELSEIF lstrIndicador='dym' THEN
                            
                                SET lintDiferencia = (lintPromedioEvaluacion - 30);
                                IF lintPromedioEvaluacion > 30 THEN 
                                    SET lintResultado = 75 - ((75/70)*lintDiferencia);
                                ELSE
                                  SET lintResultado = 75 - ((25/30)*lintDiferencia);
                                END IF;
                                
                            ELSEIF lstrIndicador='obl' THEN
                            
                                SET lintDiferencia = (lintPromedioEvaluacion - 16);
                                IF lintPromedioEvaluacion >= 16 THEN 
                                    SET lintResultado = 75 - ((75/84)*lintDiferencia);
                                ELSE 
                                    SET lintResultado = 75 - ((25/16)*lintDiferencia);
                                END IF;
                            
                            ELSEIF lstrIndicador='mit' THEN 
                                
                                SET lintDiferencia = (lintPromedioEvaluacion - 1);
                                IF lintPromedioEvaluacion > 1 THEN 
                                    SET lintResultado = 75 - ((75/99)*lintDiferencia);
                                ELSE 
                                    SET lintResultado = 75 - ((25/1)*lintDiferencia);
                                END IF;
                                
                            ELSEIF lstrIndicador='tri' THEN 
                                
                                SET lintDiferencia = (lintPromedioEvaluacion - 1);
                                IF lintPromedioEvaluacion > 1 THEN 
                                    SET lintResultado = 75 - ((75/99)*lintDiferencia);
                                ELSE 
                                    SET lintResultado = 75 - ((25/1)*lintDiferencia);
                                END IF;
                                
                            ELSEIF lstrindicador = 'acc' THEN
                                
                                SET lintDiferencia = (lintPromedioEvaluacion-2.6);
                                IF lintPromedioEvaluacion > 5.2 THEN
                                    SET lintResultado = 0;
                                ELSE
                                    IF lintPromedioEvaluacion > 2.6 THEN
                                        SET lintResultado = 75 - ((75/2.6)*lintDiferencia);
                                    ELSE
                                      SET lintResultado = 75 - ((25/2.6)*lintDiferencia);
                                    END IF;
                                END IF;
                            
                            ELSEIF lstrindicador = 'gra' THEN
                                
                                SET lintDiferencia = (lintPromedioEvaluacion-31.1);
                                IF lintPromedioEvaluacion > 60 THEN
                                    SET lintResultado = 0;
                                ELSE
                                    IF lintPromedioEvaluacion > 31.1 THEN
                                        SET lintResultado = 75 - ((75/31.1)*lintDiferencia);
                                    ELSE
                                      SET lintResultado = 75 - ((25/31.1)*lintDiferencia);
                                    END IF;
                                END IF;

                            ELSE 
                                SET lintResultado = lintPromedioEvaluacion;
                          END IF;
                          RETURN lintResultado;
                        END;
                       ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS `fnCalculoPonderado`;");
    }
    
}
