<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVwUsersGroup extends Migration
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
            VIEW `vw_users_group` AS
                SELECT
                    tb_groups.*,
                    tb_assoccgroup.group_id AS group_id_filtro
                FROM
                    tb_assoccgroup
                inner join
                tb_groups on tb_assoccgroup.subgroup_id = tb_groups.group_id;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS vw_kpi_activos');
    }
}
