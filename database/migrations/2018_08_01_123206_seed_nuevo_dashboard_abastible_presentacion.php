<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\TblDashboard;

class SeedNuevoDashboardAbastiblePresentacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        $lobjDashboard = new TblDashboard;
        $lobjDashboard->nombre = "PresentacionAbastible";
        $lobjDashboard->descripcion = "Dashboard diseñado para una presentacion de abastible";
        $lobjDashboard->vista = "abastible";
        $lobjDashboard->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        TblDashboard::Vista('abastible')->delete();
    }
}
