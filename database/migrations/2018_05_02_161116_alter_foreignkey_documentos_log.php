<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterForeignkeyDocumentosLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_documentos_log', function ($table) {
            $table->dropForeign('tbl_documentos_log_iddocumento_foreign');

            $table->foreign('IdDocumento')
                ->references('IdDocumento')->on('tbl_documentos')
                ->onDelete('NO ACTION');
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
