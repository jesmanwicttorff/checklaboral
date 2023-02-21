<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedAgregamosDashboard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $lobjModule = array("nombre" => "mlp",
                            "descripcion" => "Dashboard diseñado para el superadmin de mlp",
                            "created_at" => date("Y-m-d H:i:s"),
                            "vista" => "mlp"
        );
        \DB::table('tbl_dashboard')->insert($lobjModule);

        $lobjModule = array("nombre" => "mlpadm",
                            "descripcion" => "Dashboard diseñado para el administrador de mlp",
                            "created_at" => date("Y-m-d H:i:s"),
                            "vista" => "mlpadm"
        );
        \DB::table('tbl_dashboard')->insert($lobjModule);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('tbl_dashboard')->where("nombre","mlp")->delete();
        \DB::table('tbl_dashboard')->where("nombre","mlpadm")->delete();
    }
}
