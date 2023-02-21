<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedTbModuleVwdocsptesresumen extends Migration
{
    static private $gstrModule = "vwdocsptesresumen";
    static private $gstrModuleTitle = "Documentos Pendientes Resumen";
    static private $gstrModuleNote = "Documentos Pendientes Resumen";
    static private $gstrAutor = "gneira";
    static private $gstrDescription = "";
    static private $gstrTabla = "vw_docs_ptes_resumen";
    static private $gstrPrimarykey = "";
    static private $gstrType = "report";
    static private $gstrConfigBase = "eyJzcWxfciVsZWN0oj24oFNFTEVDVCBidl9kbiNzXgB0ZXNfcpVzdWl3b4aqoEZST005dndfZG9jcl9wdGVzXgJ3cgVtZWa5o4w4cgFsXgd2ZXJ3oj24o4w4cgFsXidybgVwoj24o4w4dGF4bGVfZGo4O4Jidl9kbiNzXgB0ZXNfcpVzdWl3b4osonBy6Wlhcn3f6iVmoj24o4w4Zp9ybXM4O3t7opZ1ZWxkoj24RGVzYgJ1cGN1bia4LCJhbG3hcyoIonZgXiRvYgNfcHR3cl9yZXNlbWVuo4w4bGF4ZWw4O4JEZXNjcp3wYi3vb4osopxhbpdlYWd3oj1bXSw4cpVxdW3yZWQ4O4owo4w4dp33dyoIojE4LCJ0eXB3oj24dGVadCosopFkZCoIojE4LCJ3ZG30oj24MSosonN3YXJj6CoIojE4LCJz6X13oj24cgBhbjEyo4w4ci9ydGx1cgQ4OjAsopZvcplfZgJvdXA4O4o4LCJvcHR1bia4Ons4bgB0XgRmcGU4O4o4LCJsbi9rdXBfcXV3cnk4O4o4LCJsbi9rdXBfdGF4bGU4O4o4LCJsbi9rdXBf6iVmoj24o4w4bG9v6gVwXgZhbHV3oj24o4w46XNfZGVwZWmkZWmjeSoIo4osonN3bGVjdF9tdWx06XBsZSoIojA4LCJ1bWFnZV9tdWx06XBsZSoIojA4LCJsbi9rdXBfZGVwZWmkZWmjeV9rZXk4O4o4LCJwYXR2XgRvXgVwbG9hZCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fSx7opZ1ZWxkoj24Q09VT3Q2ZCm1ZHR1cG9kbiNlbWVudG81o4w4YWx1YXM4O4Jidl9kbiNzXgB0ZXNfcpVzdWl3b4osopxhYpVsoj24Q09VT3Q2ZCm1ZHR1cG9kbiNlbWVudG81o4w4bGFuZgVhZiU4O3tdLCJyZXFl6XJ3ZCoIojA4LCJi6WVgoj24MSosonRmcGU4O4J0ZXh0o4w4YWRkoj24MSosopVk6XQ4O4oxo4w4ciVhcpN2oj24MSosonN1epU4O4JzcGFuMTo4LCJzbgJ0bG3zdCoIMSw4Zp9ybV9ncp9lcCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIo4osopxvbitlcF9xdWVyeSoIo4osopxvbitlcF90YWJsZSoIo4osopxvbitlcF9rZXk4O4o4LCJsbi9rdXBfdpFsdWU4O4o4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4dXBsbiFkXgRmcGU4O4o4LCJ0bi9sdG3woj24o4w4YXR0cp34dXR3oj24o4w4ZXh0ZWmkXiNsYXNzoj24onl9XSw4ZgJ1ZCoIWgs4Zp33bGQ4O4JEZXNjcp3wYi3vb4osopFs6WFzoj24dndfZG9jcl9wdGVzXgJ3cgVtZWa4LCJsYWmndWFnZSoIeyJ3cyoIo4osonB0oj24on0sopxhYpVsoj24RGVzYgJ1cGN1bia4LCJi6WVgoj2xLCJkZXRh6Ww4OjEsonNvcnRhYpx3oj2xLCJzZWFyYi54OjEsopRvdimsbiFkoj2xLCJpcp9IZWa4OjEsopx1bW30ZWQ4O4o4LCJg6WR06CoIojEwMCosopFs6Wduoj24bGVpdCosonNvcnRs6XN0oj24MCosopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4J9LHs4Zp33bGQ4O4JDTlVOVChkLp3kdG3wbiRvYgVtZWm0byk4LCJhbG3hcyoIonZgXiRvYgNfcHR3cl9yZXNlbWVuo4w4bGFuZgVhZiU4Ons4ZXM4O4JDdWVudGE4LCJwdCoIo4J9LCJsYWJ3bCoIokNlZWm0YSosonZ1ZXc4OjEsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4oxo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24onldfQ==";
    static private $gstrMenu = 1;
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
      \DB::table('tb_groups_access')->insert(['group_id'=>1,'access_data'=>'{"is_global":"1","is_view":"1","is_detail":"1","is_add":"1","is_edit":"1","is_remove":"1","is_excel":"1"}','module_id'=>$lintId]);
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
