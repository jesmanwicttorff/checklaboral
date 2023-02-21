<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVwUsersAsociacion extends Migration
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
            VIEW `vw_users_asociacion` AS
                SELECT
                    DISTINCT id,first_name,last_name,email
                FROM
                    tb_users
                inner join
                    tb_assoccgroup on tb_users.group_id=tb_assoccgroup.group_id;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS vw_users_asociacion');
    }
}
