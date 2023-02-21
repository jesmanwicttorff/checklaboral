<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTriggersSetUsuarioContratistaInsAndUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `setIdContratistaContratoPersonaIn`;");
        DB::unprepared("DROP TRIGGER IF EXISTS `setIdContratistaContratoPersonaUp`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
