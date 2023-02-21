<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTbmEncuestasDropColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

      Schema::table('tbm_encuestas', function ($table) {
        if (Schema::hasColumn('tbm_encuestas', 'contrato_id')) {
          $table->dropColumn('contrato_id');
        }
        if (Schema::hasColumn('tbm_encuestas', 'contrato_id')) {
          $table->dropColumn('IdContratista');
        }
      });

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
