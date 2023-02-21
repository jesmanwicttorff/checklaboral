<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedTbModuleVwdocsporfecha extends Migration
{
  static private $gstrModule = "vwdocsporfecha";
  static private $gstrModuleTitle = "Documentos por fecha";
  static private $gstrModuleNote = "Documentos por fecha";
  static private $gstrAutor = "gneira";
  static private $gstrDescription = "";
  static private $gstrTabla = "vw_docs_por_fecha";
  static private $gstrPrimarykey = "";
  static private $gstrType = "report";
  static private $gstrConfigBase = "eyJzcWxfciVsZWN0oj24oFNFTEVDVCBidl9kbiNzXgBvc39pZWN2YSaqoEZST005dndfZG9jcl9wbgJfZpVj6GE5o4w4cgFsXgd2ZXJ3oj24o4w4cgFsXidybgVwoj24o4w4dGF4bGVfZGo4O4Jidl9kbiNzXgBvc39pZWN2YSosonBy6Wlhcn3f6iVmoj24o4w4ZgJ1ZCoIWgs4Zp33bGQ4O4JGZWN2YSBDYXJnYSosopFs6WFzoj24dndfZG9jcl9wbgJfZpVj6GE4LCJsYWJ3bCoIokZ3YihhoENhcpdho4w4bGFuZgVhZiU4O3tdLCJzZWFyYi54O4oxo4w4ZG9gbpxvYWQ4O4oxo4w4YWx1Zia4O4JsZWZ0o4w4dp33dyoIojE4LCJkZXRh6Ww4O4oxo4w4ci9ydGF4bGU4O4oxo4w4ZnJvepVuoj24MCosoph1ZGR3b4oIojA4LCJzbgJ0bG3zdCoIMCw4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24RGVzYgJ1cGN1bia4LCJhbG3hcyoIonZgXiRvYgNfcG9yXiZ3Yihho4w4bGF4ZWw4O4JEZXNjcp3wYi3vb4osopxhbpdlYWd3oj1bXSw4ciVhcpN2oj24MSosopRvdimsbiFkoj24MSosopFs6Wduoj24bGVpdCosonZ1ZXc4O4oxo4w4ZGV0YW3soj24MSosonNvcnRhYpx3oj24MSosopZybg13b4oIojA4LCJ26WRkZWa4O4owo4w4ci9ydGx1cgQ4OjEsond1ZHR2oj24MTAwo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24on0seyJp6WVsZCoIokNhbnRfRG9jcyosopFs6WFzoj24dndfZG9jcl9wbgJfZpVj6GE4LCJsYWJ3bCoIokNhbnQ5RG9jcyosopxhbpdlYWd3oj1bXSw4ciVhcpN2oj24MSosopRvdimsbiFkoj24MSosopFs6Wduoj24bGVpdCosonZ1ZXc4O4oxo4w4ZGV0YW3soj24MSosonNvcnRhYpx3oj24MSosopZybg13b4oIojA4LCJ26WRkZWa4O4owo4w4ci9ydGx1cgQ4Ojosond1ZHR2oj24MTAwo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24onldLCJpbgJtcyoIWgs4Zp33bGQ4O4JGZWN2YSBDYXJnYSosopFs6WFzoj24dndfZG9jcl9wbgJfZpVj6GE4LCJsYWJ3bCoIokZ3YihhoENhcpdho4w4bGFuZgVhZiU4O3tdLCJyZXFl6XJ3ZCoIojA4LCJi6WVgoj24MSosonRmcGU4O4J0ZXh0o4w4YWRkoj24MSosopVk6XQ4O4oxo4w4ciVhcpN2oj24MSosonN1epU4O4JzcGFuMTo4LCJzbgJ0bG3zdCoIMCw4Zp9ybV9ncp9lcCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIo4osopxvbitlcF9xdWVyeSoIo4osopxvbitlcF90YWJsZSoIo4osopxvbitlcF9rZXk4O4o4LCJsbi9rdXBfdpFsdWU4O4o4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4dXBsbiFkXgRmcGU4O4o4LCJ0bi9sdG3woj24o4w4YXR0cp34dXR3oj24o4w4ZXh0ZWmkXiNsYXNzoj24onl9LHs4Zp33bGQ4O4JEZXNjcp3wYi3vb4osopFs6WFzoj24dndfZG9jcl9wbgJfZpVj6GE4LCJsYWJ3bCoIokR3ciNy6XBj6W9uo4w4bGFuZgVhZiU4O3tdLCJyZXFl6XJ3ZCoIojA4LCJi6WVgoj24MSosonRmcGU4O4J0ZXh0o4w4YWRkoj24MSosopVk6XQ4O4oxo4w4ciVhcpN2oj24MSosonN1epU4O4JzcGFuMTo4LCJzbgJ0bG3zdCoIMSw4Zp9ybV9ncp9lcCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIo4osopxvbitlcF9xdWVyeSoIo4osopxvbitlcF90YWJsZSoIo4osopxvbitlcF9rZXk4O4o4LCJsbi9rdXBfdpFsdWU4O4o4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4dXBsbiFkXgRmcGU4O4o4LCJ0bi9sdG3woj24o4w4YXR0cp34dXR3oj24o4w4ZXh0ZWmkXiNsYXNzoj24onl9LHs4Zp33bGQ4O4JDYWm0X0RvYgM4LCJhbG3hcyoIonZgXiRvYgNfcG9yXiZ3Yihho4w4bGF4ZWw4O4JDYWm0oERvYgM4LCJsYWmndWFnZSoIWl0sonJ3cXV1cpVkoj24MCosonZ1ZXc4O4oxo4w4dH3wZSoIonR3eHQ4LCJhZGQ4O4oxo4w4ZWR1dCoIojE4LCJzZWFyYi54O4oxo4w4ci3IZSoIonNwYWaxM4osonNvcnRs6XN0oj2yLCJpbgJtXidybgVwoj24o4w4bgB06W9uoj17op9wdF90eXB3oj24o4w4bG9v6gVwXgFlZXJmoj24o4w4bG9v6gVwXgRhYpx3oj24o4w4bG9v6gVwXit3eSoIo4osopxvbitlcF9iYWxlZSoIo4osop3zXiR3cGVuZGVuYgk4O4o4LCJzZWx3YgRfbXVsdG3wbGU4O4owo4w46WlhZiVfbXVsdG3wbGU4O4owo4w4bG9v6gVwXiR3cGVuZGVuYg3f6iVmoj24o4w4cGF06F90bl9lcGxvYWQ4O4o4LCJlcGxvYWRfdH3wZSoIo4osonRvbix06XA4O4o4LCJhdHRy6WJldGU4O4o4LCJ3eHR3bpRfYixhcgM4O4o4fXldfQ==";
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
