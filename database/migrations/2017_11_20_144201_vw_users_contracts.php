<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VwUsersContracts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE
                 OR REPLACE ALGORITHM = UNDEFINED
                DEFINER = `root`@`localhost`
                SQL SECURITY DEFINER
            VIEW `vw_users_contracts` AS
                SELECT
                    tbl_contrato.contrato_id,cont_numero,cont_proveedor,tb_assocc.user_id as usuario
                FROM
                    tb_assocc
                inner join
                    tbl_contrato on tb_assocc.contrato_id=tbl_contrato.contrato_id;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       DB::statement('DROP VIEW IF EXISTS vw_users_contracts');
    }
}
