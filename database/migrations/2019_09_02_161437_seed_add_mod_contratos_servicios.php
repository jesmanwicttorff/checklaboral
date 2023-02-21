<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedAddModContratosServicios extends Migration
{

  static private $gstrModule = "contratosservicios";
  static private $gstrModuleTitle = "Servicios";
  static private $gstrModuleNote = "Servicios";
  static private $gstrAutor = "Diego Diaz";
  static private $gstrDescription = "";
  static private $gstrTabla = "tbl_contratos_servicios";
  static private $gstrPrimarykey = "id";
  static private $gstrType = "ajax";
  static private $gstrConfigBase = "eyJ0YWJsZV9kY4oIopFucgd3cnM4LCJwcp3tYXJmXit3eSoIop3ko4w4cgFsXgN3bGVjdCoIo3NFTEVDVCB0YpxfYi9udHJhdG9zXgN3cnZ1Yi3vcyaqLCB0YpxfYi9udHJhdG9zXidydXBvcl93cgB3Yi3p6WNvcymuYWl3oGFzoG3kZgJlcG93cgB3Yi3p6WNvXiR3ciNcc3xuR3JPTSB0YpxfYi9udHJhdG9zXgN3cnZ1Yi3vclxyXGmMRUZUoE1PSUa5dGJsXiNvbnRyYXRvcl9ncnVwbgNfZXNwZWN1Zp3jbgM5T0a5dGJsXiNvbnRyYXRvcl9ncnVwbgNfZXNwZWN1Zp3jbgMu6WQ5PSB0YpxfYi9udHJhdG9zXgN3cnZ1Yi3vcym1ZGdydXBvZXNwZWN1Zp3jbzs4LCJzcWxfdih3cpU4O4o5V0hFUkU5dGJsXiNvbnRyYXRvcl9zZXJi6WN1bgMu6WQ5SVM5Tk9UoEmVTEw4LCJzcWxfZgJvdXA4O4o4LCJpbgJtcyoIWgs4Zp33bGQ4O4J1ZCosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9zZXJi6WN1bgM4LCJsYWmndWFnZSoIeyJ3cyoIo4osonB0oj24on0sopxhYpVsoj24SWQ4LCJpbgJtXidybgVwoj24o4w4cpVxdW3yZWQ4O4owo4w4dp33dyoIMSw4dH3wZSoIonR3eHRhcpVho4w4YWRkoj2xLCJz6X13oj24MCosopVk6XQ4OjEsonN3YXJj6CoIojE4LCJzbgJ0bG3zdCoIojA4LCJs6Wl1dGVkoj24o4w4bgB06W9uoj17op9wdF90eXB3oj24o4w4bG9v6gVwXgFlZXJmoj24o4w4bG9v6gVwXgRhYpx3oj24o4w4bG9v6gVwXit3eSoIo4osopxvbitlcF9iYWxlZSoIo4osop3zXiR3cGVuZGVuYgk4O4o4LCJzZWx3YgRfbXVsdG3wbGU4O4owo4w46WlhZiVfbXVsdG3wbGU4O4owo4w4bG9v6gVwXiR3cGVuZGVuYg3f6iVmoj24o4w4cGF06F90bl9lcGxvYWQ4O4o4LCJyZXN1epVfdi3kdG54O4o4LCJyZXN1epVf6GV1Zih0oj24o4w4dXBsbiFkXgRmcGU4O4o4LCJ0bi9sdG3woj24o4w4YXR0cp34dXR3oj24o4w4ZXh0ZWmkXiNsYXNzoj24onl9LHs4Zp33bGQ4O4J1ZGdydXBvZXNwZWN1Zp3jbyosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9zZXJi6WN1bgM4LCJsYWmndWFnZSoIeyJ3cyoIo4osonB0oj24on0sopxhYpVsoj24SWRncnVwbiVzcGVj6WZ1Yi84LCJpbgJtXidybgVwoj24o4w4cpVxdW3yZWQ4O4JyZXFl6XJ3ZCosonZ1ZXc4OjEsonRmcGU4O4J0ZXh0YXJ3YSosopFkZCoIMSw4ci3IZSoIojA4LCJ3ZG30oj2xLCJzZWFyYi54O4oxo4w4ci9ydGx1cgQ4O4oxo4w4bG3t6XR3ZCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIo4osopxvbitlcF9xdWVyeSoIo4osopxvbitlcF90YWJsZSoIo4osopxvbitlcF9rZXk4O4o4LCJsbi9rdXBfdpFsdWU4O4o4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4cpVz6X13Xgd1ZHR2oj24o4w4cpVz6X13Xih36Wd2dCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fSx7opZ1ZWxkoj24bpFtZSosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9zZXJi6WN1bgM4LCJsYWmndWFnZSoIeyJ3cyoIo4osonB0oj24on0sopxhYpVsoj24TpFtZSosopZvcplfZgJvdXA4O4o4LCJyZXFl6XJ3ZCoIonJ3cXV1cpVko4w4dp33dyoIMSw4dH3wZSoIonR3eHRhcpVho4w4YWRkoj2xLCJz6X13oj24MCosopVk6XQ4OjEsonN3YXJj6CoIojE4LCJzbgJ0bG3zdCoIojo4LCJs6Wl1dGVkoj24o4w4bgB06W9uoj17op9wdF90eXB3oj24o4w4bG9v6gVwXgFlZXJmoj24o4w4bG9v6gVwXgRhYpx3oj24o4w4bG9v6gVwXit3eSoIo4osopxvbitlcF9iYWxlZSoIo4osop3zXiR3cGVuZGVuYgk4O4o4LCJzZWx3YgRfbXVsdG3wbGU4O4owo4w46WlhZiVfbXVsdG3wbGU4O4owo4w4bG9v6gVwXiR3cGVuZGVuYg3f6iVmoj24o4w4cGF06F90bl9lcGxvYWQ4O4o4LCJyZXN1epVfdi3kdG54O4o4LCJyZXN1epVf6GV1Zih0oj24o4w4dXBsbiFkXgRmcGU4O4o4LCJ0bi9sdG3woj24o4w4YXR0cp34dXR3oj24o4w4ZXh0ZWmkXiNsYXNzoj24onl9LHs4Zp33bGQ4O4J1ZGVzdGF0dXM4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfciVydp3j6W9zo4w4bGFuZgVhZiU4Ons4ZXM4O4o4LCJwdCoIo4J9LCJsYWJ3bCoIok3kZXN0YXRlcyosopZvcplfZgJvdXA4O4o4LCJyZXFl6XJ3ZCoIojA4LCJi6WVgoj2xLCJ0eXB3oj24dGVadGFyZWE4LCJhZGQ4OjEsonN1epU4O4owo4w4ZWR1dCoIMSw4ciVhcpN2oj24MSosonNvcnRs6XN0oj24Myosopx1bW30ZWQ4O4o4LCJvcHR1bia4Ons4bgB0XgRmcGU4O4o4LCJsbi9rdXBfcXV3cnk4O4o4LCJsbi9rdXBfdGF4bGU4O4o4LCJsbi9rdXBf6iVmoj24o4w4bG9v6gVwXgZhbHV3oj24o4w46XNfZGVwZWmkZWmjeSoIo4osonN3bGVjdF9tdWx06XBsZSoIojA4LCJ1bWFnZV9tdWx06XBsZSoIojA4LCJsbi9rdXBfZGVwZWmkZWmjeV9rZXk4O4o4LCJwYXR2XgRvXgVwbG9hZCoIo4osonJ3ci3IZV9g6WR06CoIo4osonJ3ci3IZV92ZW3n6HQ4O4o4LCJlcGxvYWRfdH3wZSoIo4osonRvbix06XA4O4o4LCJhdHRy6WJldGU4O4o4LCJ3eHR3bpRfYixhcgM4O4o4fX0seyJp6WVsZCoIopNyZWF0ZWRfYXQ4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfciVydp3j6W9zo4w4bGFuZgVhZiU4Ons4ZXM4O4o4LCJwdCoIo4J9LCJsYWJ3bCoIokNyZWF0ZWQ5QXQ4LCJpbgJtXidybgVwoj24o4w4cpVxdW3yZWQ4O4owo4w4dp33dyoIMSw4dH3wZSoIonR3eHRhcpVho4w4YWRkoj2xLCJz6X13oj24MCosopVk6XQ4OjEsonN3YXJj6CoIojE4LCJzbgJ0bG3zdCoIojQ4LCJs6Wl1dGVkoj24o4w4bgB06W9uoj17op9wdF90eXB3oj24o4w4bG9v6gVwXgFlZXJmoj24o4w4bG9v6gVwXgRhYpx3oj24o4w4bG9v6gVwXit3eSoIo4osopxvbitlcF9iYWxlZSoIo4osop3zXiR3cGVuZGVuYgk4O4o4LCJzZWx3YgRfbXVsdG3wbGU4O4owo4w46WlhZiVfbXVsdG3wbGU4O4owo4w4bG9v6gVwXiR3cGVuZGVuYg3f6iVmoj24o4w4cGF06F90bl9lcGxvYWQ4O4o4LCJyZXN1epVfdi3kdG54O4o4LCJyZXN1epVf6GV1Zih0oj24o4w4dXBsbiFkXgRmcGU4O4o4LCJ0bi9sdG3woj24o4w4YXR0cp34dXR3oj24o4w4ZXh0ZWmkXiNsYXNzoj24onl9LHs4Zp33bGQ4O4JlcGRhdGVkXiF0o4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgN3cnZ1Yi3vcyosopxhbpdlYWd3oj17opVzoj24o4w4cHQ4O4o4fSw4bGF4ZWw4O4JVcGRhdGVkoEF0o4w4Zp9ybV9ncp9lcCoIo4osonJ3cXV1cpVkoj24MCosonZ1ZXc4OjEsonRmcGU4O4J0ZXh0YXJ3YSosopFkZCoIMSw4ci3IZSoIojA4LCJ3ZG30oj2xLCJzZWFyYi54O4oxo4w4ci9ydGx1cgQ4O4olo4w4bG3t6XR3ZCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIo4osopxvbitlcF9xdWVyeSoIo4osopxvbitlcF90YWJsZSoIo4osopxvbitlcF9rZXk4O4o4LCJsbi9rdXBfdpFsdWU4O4o4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4cpVz6X13Xgd1ZHR2oj24o4w4cpVz6X13Xih36Wd2dCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fV0sopdy6WQ4O3t7opZ1ZWxkoj246WQ4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfciVydp3j6W9zo4w4bGFuZgVhZiU4Ons4ZXM4O4o4LCJwdCoIo4J9LCJsYWJ3bCoIok3ko4w4dp33dyoIMCw4ZGV0YW3soj2xLCJzbgJ0YWJsZSoIMSw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMSw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojE4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj246WRncnVwbiVzcGVj6WZ1Yi84LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfciVydp3j6W9zo4w4bGFuZgVhZiU4Ons4ZXM4O4o4LCJwdCoIo4J9LCJsYWJ3bCoIok3kZgJlcG93cgB3Yi3p6WNvo4w4dp33dyoIMCw4ZGV0YW3soj2xLCJzbgJ0YWJsZSoIMSw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMSw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojo4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj246WRncnVwbiVzcGVj6WZ1Yi9fZGVzYyosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9ncnVwbgNfZXNwZWN1Zp3jbgM4LCJsYWmndWFnZSoIeyJ3cyoIokdydXBvoEVzcGVjXHUwMGVkZp3jbyosonB0oj24on0sopxhYpVsoj24RgJlcG85RXNwZWNcdTAwZWRp6WNvo4w4dp33dyoIMSw4ZGV0YW3soj2xLCJzbgJ0YWJsZSoIMSw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMSw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojM4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24bpFtZSosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9zZXJi6WN1bgM4LCJsYWmndWFnZSoIeyJ3cyoIokmvbWJyZSosonB0oj24on0sopxhYpVsoj24Tp9tYnJ3o4w4dp33dyoIMSw4ZGV0YW3soj2xLCJzbgJ0YWJsZSoIMSw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMSw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojQ4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj246WR3cgRhdHVzo4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgN3cnZ1Yi3vcyosopxhbpdlYWd3oj17opVzoj24o4w4cHQ4O4o4fSw4bGF4ZWw4O4JJZGVzdGF0dXM4LCJi6WVgoj2wLCJkZXRh6Ww4OjEsonNvcnRhYpx3oj2xLCJzZWFyYi54OjEsopRvdimsbiFkoj2xLCJpcp9IZWa4OjEsopx1bW30ZWQ4O4o4LCJg6WR06CoIojEwMCosopFs6Wduoj24bGVpdCosonNvcnRs6XN0oj24NSosopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4J9LHs4Zp33bGQ4O4JjcpVhdGVkXiF0o4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgN3cnZ1Yi3vcyosopxhbpdlYWd3oj17opVzoj24RpVj6GE5QgJ3YWN1XHUwMGYzb4osonB0oj24on0sopxhYpVsoj24RpVj6GE5QgJ3YWN1XHUwMGYzb4osonZ1ZXc4OjEsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4oio4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24ZGF0ZSosopZvcplhdF9iYWxlZSoIopRcLilcLlk4fSx7opZ1ZWxkoj24dXBkYXR3ZF9hdCosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9zZXJi6WN1bgM4LCJsYWmndWFnZSoIeyJ3cyoIokZ3YihhoElvZG3p6WNhYi3cdTAwZjNuo4w4cHQ4O4o4fSw4bGF4ZWw4O4JGZWN2YSBNbiR1Zp3jYWN1XHUwMGYzb4osonZ1ZXc4OjEsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4ogo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24ZGF0ZSosopZvcplhdF9iYWxlZSoIopRcLilcLlk4fVl9";
  static private $gstrMenu = 1;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

      $lobjModule = array("module_name"=>self::$gstrModule,
          "module_title"=>self::$gstrModuleTitle,
          "module_note"=>self::$gstrModuleNote,
          "module_author"=>self::$gstrAutor,
          "module_created"=>date("Y-m-d H:i:s"),
          "module_desc"=>self::$gstrDescription,
          "module_db"=>self::$gstrTabla,
          "module_db_key"=>self::$gstrPrimarykey,
          "module_type"=>self::$gstrType,
          "module_config"=>self::$gstrConfigBase,
          "module_lang"=>'{\"title\":{\"es\":\"'.self::$gstrModuleTitle.'\",\"pt\":\"\"},\"note\":{\"es\":\"'.self::$gstrModuleTitle.'\",\"pt\":\"'.self::$gstrModuleTitle.'\"}}',
      );
      $lintId = \DB::table('tb_module')->insertGetId($lobjModule);

      $lobjModule = array("group_id"=>1,
                          "module_id"=> $lintId,
                          "access_data"=>'{"is_global":"1","is_view":"1","is_detail":"1","is_add":"1","is_edit":"1","is_remove":"1","is_excel":"1"}');
      $lintId = \DB::table('tb_groups_access')->insertGetId($lobjModule);

      if (self::$gstrMenu){
        if (self::$gstrType=='checklaboral'){
          $lstrType = 'checklaboral/';
        }else{
          $lstrType = '';
        }
        $lobjModule = array("parent_id"=>0,
                            "module"=> $lstrType.self::$gstrModule,
                            "menu_name"=> self::$gstrModuleTitle,
                            "menu_type"=> 'internal',
                            "role_id"=> null,
                            "deep"=> null,
                            "ordering"=> 0,
                            "position"=> 'sidebar',
                            "menu_icons" => '',
                            "active" => 1,
                            "access_data"=>'{"1":"1"}',
                            "allow_guest" => NULL,
                            "menu_lang" => '{\"title\":{\"es\":\"'.self::$gstrModuleTitle.'\",\"pt\":\"\"},\"note\":{\"es\":\"'.self::$gstrModuleTitle.'\",\"pt\":\"'.self::$gstrModuleTitle.'\"}}');
        $lintId = \DB::table('tb_menu')->insertGetId($lobjModule);
      }

    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {

      if (self::$gstrType=='checklaboral'){
        $lstrType = 'checklaboral/';
      }else{
        $lstrType = '';
      }
      \DB::table('tb_menu')->where('module',$lstrType.self::$gstrModule)->delete();
      $lintIdModule = \DB::table('tb_module')->select('module_id')->where('module_name',self::$gstrModule)->where('module_type',self::$gstrType)->first();
      if ($lintIdModule){
        \DB::table('tb_groups_access')->where("module_id",$lintIdModule->module_id)->delete();
      }
      \DB::table('tb_module')->where('module_name',self::$gstrModule)->where('module_type',self::$gstrType)->delete();
    }
}

