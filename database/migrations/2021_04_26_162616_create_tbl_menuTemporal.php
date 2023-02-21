<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMenuTemporal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("tbl_menuvue",function(Blueprint $table){
            $table->increments('menu_id');
            $table->string('name');
            $table->string('icon');
            $table->string('vueRouter');
            $table->string('laravelRouter');
            $table->integer('orden');
            $table->integer('parent_id');
            $table->string('group_id');
            $table->integer('active');
        });

        $tbl_menutemporal = array(
        	array(
            //'menu_id'=>0,
        		'name'=>"Estados de Pago",
        		'icon'=>"fas fa-clipboard-list",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>NULL,
        		'orden'=>1,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //menu_id'=>1,
        		'name'=>"Gestión Variables Críticas",
        		'icon'=>"fas fa-chart-line",
        		'vueRouter'=>"/contratos/dashboard/kpi",
        		'laravelRouter'=>NULL,
        		'orden'=>2,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //menu_id'=>2,
        		'name'=>"Evaluación",
        		'icon'=>"fas fa-sort-numeric-up",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>NULL,
        		'orden'=>3,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //menu_id'=>3,
        		'name'=>"Registros",
        		'icon'=>"fas fa-clipboard-check",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>NULL,
        		'orden'=>4,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //menu_id'=>4,
        		'name'=>"Acreditación",
        		'icon'=>"fas fa-id-card-alt",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>"/acreditacion",
        		'orden'=>5,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //menu_id'=>5,
        		'name'=>"Reporte Check Laboral",
        		'icon'=>"fas fa-chalkboard-teacher",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>"/checklaboral/reporte",
        		'orden'=>6,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //menu_id'=>6,
        		'name'=>"Carga Documental",
        		'icon'=>"fas fa-file-upload",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>NULL,
        		'orden'=>7,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>7,
        		'name'=>"Gestión Documental",
        		'icon'=>"fas fa-paste",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>NULL,
        		'orden'=>8,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>8,
        		'name'=>"Pases",
        		'icon'=>"fas fa-key",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>"/accesos",
        		'orden'=>9,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>9,
        		'name'=>"Portería",
        		'icon'=>"fas fa-door-open",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>"/porteria",
        		'orden'=>10,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>10,
        		'name'=>"Colaboración",
        		'icon'=>"fas fa-handshake",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>NULL,
        		'orden'=>11,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>11,
        		'name'=>"Herramientas",
        		'icon'=>"fas fa-tools",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>NULL,
        		'orden'=>12,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>12,
        		'name'=>"Seguridad",
        		'icon'=>"fas fa-shield-alt",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>NULL,
        		'orden'=>13,
        		'parent_id'=>NULL,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
        );

        \DB::table('tbl_menuvue')->insert($tbl_menutemporal);

        $tbl_menutemporal = array(
        	array(
            //'menu_id'=>13,
        		'name'=>"Dashboard",
        		'icon'=>"far fa-chart-bar",
        		'vueRouter'=>'/estadosdepago/dashboard/historico',
        		'laravelRouter'=>NULL,
        		'orden'=>1,
        		'parent_id'=>1,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>14,
        		'name'=>"Listado",
        		'icon'=>"bx bx-list-ul",
        		'vueRouter'=>'/estadosdepago/listado',
        		'laravelRouter'=>NULL,
        		'orden'=>1,
        		'parent_id'=>1,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>15,
        		'name'=>"Crear",
        		'icon'=>"bx bx-plus-circle",
        		'vueRouter'=>'/estadosdepago',
        		'laravelRouter'=>NULL,
        		'orden'=>1,
        		'parent_id'=>1,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>16,
        		'name'=>"Detalle",
        		'icon'=>"fas fa-search",
        		'vueRouter'=>'/evaluacion',
        		'laravelRouter'=>NULL,
        		'orden'=>1,
        		'parent_id'=>3,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>17,
        		'name'=>"Crear",
        		'icon'=>"far fa-clipboard",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/encuestados',
        		'orden'=>1,
        		'parent_id'=>3,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>18,
        		'name'=>"Categorias",
        		'icon'=>"fas fa-stream",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/encuestadoscategorias',
        		'orden'=>1,
        		'parent_id'=>3,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>19,
        		'name'=>"Contratistas",
        		'icon'=>"fas fa-user-tie",
        		'vueRouter'=>'/contratistas',
        		'laravelRouter'=>NULL,
        		'orden'=>1,
        		'parent_id'=>4,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Contratos",
        		'icon'=>"fas fa-file-contract",
        		'vueRouter'=>'/contratos',
        		'laravelRouter'=>NULL,
        		'orden'=>1,
        		'parent_id'=>4,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Personas",
        		'icon'=>"fas fa-users",
        		'vueRouter'=>'/personas',
        		'laravelRouter'=>NULL,
        		'orden'=>1,
        		'parent_id'=>4,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Activos",
        		'icon'=>"far fa-check-circle",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/activos',
        		'orden'=>1,
        		'parent_id'=>4,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Carga Mensual",
        		'icon'=>"far fa-calendar-alt",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/documentosMesAnterior',
        		'orden'=>1,
        		'parent_id'=>7,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Carga Acreditación",
        		'icon'=>"fas fa-id-card-alt",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/cargaacreditacion',
        		'orden'=>1,
        		'parent_id'=>7,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Carga No Conformidades",
        		'icon'=>"fas fa-user-times",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/noconformidades',
        		'orden'=>1,
        		'parent_id'=>7,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Buscador de Documentos",
        		'icon'=>"fas fa-search",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/buscadordocumental',
        		'orden'=>1,
        		'parent_id'=>8,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Gestión de Solicitudes",
        		'icon'=>"fas fa-tasks",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/aprobaciones',
        		'orden'=>1,
        		'parent_id'=>8,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Bitácora",
        		'icon'=>"fas fa-pencil-alt",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/documentoslog',
        		'orden'=>1,
        		'parent_id'=>8,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Wiki",
        		'icon'=>"fas fa-book",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/wiki',
        		'orden'=>1,
        		'parent_id'=>11,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Wiki Admin",
        		'icon'=>"fas fa-book-reader",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/wikiadmin',
        		'orden'=>1,
        		'parent_id'=>11,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Proceso de cierre",
        		'icon'=>"fas fa-times",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/checklaboral/cargamasivadiferencias',
        		'orden'=>1,
        		'parent_id'=>12,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Fechas Dashboard",
        		'icon'=>"far fa-calendar",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/pcontrolado',
        		'orden'=>1,
        		'parent_id'=>12,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Tipo de Documentos",
        		'icon'=>"fas fa-file-alt",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/tipodocumentos',
        		'orden'=>1,
        		'parent_id'=>12,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Usuarios",
        		'icon'=>"fas fa-child",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/coreusers',
        		'orden'=>1,
        		'parent_id'=>13,
        		'group_id'=>"1,2",
        		'active'=>1,
        	),
          array(
            //'menu_id'=>20,
        		'name'=>"Perfiles",
        		'icon'=>"far fa-user-circle",
        		'vueRouter'=>NULL,
        		'laravelRouter'=>'/maetbgrupos',
        		'orden'=>1,
        		'parent_id'=>13,
        		'group_id'=>"1,2",
        		'active'=>1,
        	)
        );

        \DB::table('tbl_menuvue')->insert($tbl_menutemporal);

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
