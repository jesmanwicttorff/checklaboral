<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyAfpTrabajador extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('afp_trabajador', function (Blueprint $table) {
          $table->string('rut',15)->change();
          $table->string('nombre',200)->change();
      });

      Schema::table('mutual_trabajador', function (Blueprint $table) {
          $table->string('rut',15)->change();
          $table->string('nombre',200)->change();
      });

      Schema::table('caja_trabajador', function (Blueprint $table) {
          $table->string('rut',15)->change();
          $table->string('nombre',200)->change();
      });

      Schema::table('fonasa_trabajador', function (Blueprint $table) {
          $table->string('rut',15)->change();
          $table->string('nombre',200)->change();
      });

      Schema::table('ips_trabajador', function (Blueprint $table) {
          $table->string('rut',15)->change();
          $table->string('nombre',200)->change();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('afp_trabajador', function (Blueprint $table) {
        $table->string('rut',45)->change();
      });
    }
}
