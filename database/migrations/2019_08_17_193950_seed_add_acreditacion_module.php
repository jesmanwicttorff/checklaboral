<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedAddAcreditacionModule extends Migration
{
    
      static private $gstrModule = "acreditacion";
      static private $gstrModuleTitle = "Acreditacion";
      static private $gstrModuleNote = "Acreditacion";
      static private $gstrAutor = "scp";
      static private $gstrDescription = "";
      static private $gstrTabla = "answers";
      static private $gstrPrimarykey = "id";
      static private $gstrType = "ajax";
      static private $gstrConfigBase = "eyJzcWxfciVsZWN0oj24oFNFTEVDVCBhbnNgZXJzL425R3JPTSBhbnNgZXJzoCosonNxbF9g6GVyZSoIo4BXSEVSRSBhbnNgZXJzLp3koE3ToEmPVCBOVUxMo4w4cgFsXidybgVwoj24o4w4dGF4bGVfZGo4O4JhbnNgZXJzo4w4cHJ1bWFyeV9rZXk4O4J1ZCosopdy6WQ4O3t7opZ1ZWxkoj246WQ4LCJhbG3hcyoIopFucgd3cnM4LCJsYWJ3bCoIok3ko4w4bGFuZgVhZiU4O3tdLCJzZWFyYi54O4oxo4w4ZG9gbpxvYWQ4O4oxo4w4YWx1Zia4O4JsZWZ0o4w4dp33dyoIojE4LCJkZXRh6Ww4O4oxo4w4ci9ydGF4bGU4O4oxo4w4ZnJvepVuoj24MCosoph1ZGR3b4oIojA4LCJzbgJ0bG3zdCoIMCw4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24YWmzdiVyo4w4YWx1YXM4O4JhbnNgZXJzo4w4bGF4ZWw4O4JBbnNgZXo4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2xLCJg6WR06CoIojEwMCosopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4J9LHs4Zp33bGQ4O4JxdWVzdG3vb391ZCosopFs6WFzoj24YWmzdiVycyosopxhYpVsoj24UXV3cgR1bia5SWQ4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2yLCJg6WR06CoIojEwMCosopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4J9LHs4Zp33bGQ4O4JxdWVzdG3vb390eXB3o4w4YWx1YXM4O4JhbnNgZXJzo4w4bGF4ZWw4O4JRdWVzdG3vb4BUeXB3o4w4bGFuZgVhZiU4O3tdLCJzZWFyYi54O4oxo4w4ZG9gbpxvYWQ4O4oxo4w4YWx1Zia4O4JsZWZ0o4w4dp33dyoIojE4LCJkZXRh6Ww4O4oxo4w4ci9ydGF4bGU4O4oxo4w4ZnJvepVuoj24MCosoph1ZGR3b4oIojA4LCJzbgJ0bG3zdCoIMyw4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24YgNzo4w4YWx1YXM4O4JhbnNgZXJzo4w4bGF4ZWw4O4JDcgM4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj20LCJg6WR06CoIojEwMCosopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4J9LHs4Zp33bGQ4O4JwbgN1dG3vb4osopFs6WFzoj24YWmzdiVycyosopxhYpVsoj24UG9z6XR1bia4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2lLCJg6WR06CoI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";
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
    
    
