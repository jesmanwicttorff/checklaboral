<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedModRemuneracionesCreate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $lobjModule = array("module_name"=>"remuneraciones", 
                            "module_title"=>"Remuneraciones",
                            "module_note"=>"Remuneraciones",
                            "module_author"=>"ddiaz",
                            "module_created"=>date("Y-m-d H:i:s"),
                            "module_desc"=>NULL,
                            "module_db"=>'tbl_f30_1',
                            "module_db_key"=>'IdF301',
                            "module_type"=>'ajax',
                            "module_config"=>'eyJ0YWJsZV9kY4oIonR4bF9pMzBfMSosonBy6Wlhcn3f6iVmoj24SWRGMzAxo4w4cgFsXgN3bGVjdCoIo3NFTEVDVCB0YpxfYi9udHJhdG3zdGFzL3JldCw5dGJsXiNvbnRyYXR1cgRhcymSYX1vb3NvYi3hbCw5dGJsXiNvbnRyYXRvLpNvbnRyYXRvXi3kLCB0YpxfYi9udHJhdG8uYi9udF9udWl3cp8soCBzdW02dGJsXiYzMF8xL3RyYWJh6pFkbgJ3clZ1ZiVudGVzKSBUcpF4YW1hZG9yZXNW6Wd3bnR3cyw5cgVtKHR4bF9pMzBfMSmUbgRhbENvdG3IYWN1bim3cyk5YXM5VG90YWxDbgR1epFj6W9uZXNcc3xuR3JPTSB0YpxfZjMwXzFcc3xuSUmORVo5Sk9JT4B0YpxfYi9udHJhdG85T0a5dGJsXiNvbnRyYXRvLpNvbnRyYXRvXi3koD05dGJsXiYzMF8xLpNvbnRyYXRvXi3kXHJcbk3OTkVSoE1PSUa5dGJsXiNvbnRyYXR1cgRhcyBvb4B0YpxfYi9udHJhdG3zdGFzLk3kQi9udHJhdG3zdGE5PSB0YpxfZjMwXzEuSWRDbim0cpF06XN0YSosonNxbF9g6GVyZSoIo4osonNxbF9ncp9lcCoIopdybgVwoGJmoHR4bF9jbim0cpF06XN0YXMuUnV0LCB0YpxfYi9udHJhdG3zdGFzL3Jhep9uUi9j6WFsLCB0YpxfYi9udHJhdG8uYi9udHJhdG9f6WQsoHR4bF9jbim0cpF0bymjbim0XimlbWVybyosopdy6WQ4O3t7opZ1ZWxkoj24UnV0o4w4YWx1YXM4O4J0YpxfYi9udHJhdG3zdGFzo4w4bGFuZgVhZiU4Ons4ZXM4O4JSdXQ4LCJwdCoIo4J9LCJsYWJ3bCoIo3JldCosonZ1ZXc4OjEsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4owo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24o4w4dH3wZSoIonR3eHQ4fSx7opZ1ZWxkoj24UpFIbimTbiN1YWw4LCJhbG3hcyoIonR4bF9jbim0cpF06XN0YXM4LCJsYWmndWFnZSoIeyJ3cyoIo3Jhep9uoFNvYi3hbCosonB0oj24on0sopxhYpVsoj24UpFIbia5Ui9j6WFso4w4dp33dyoIMSw4ZGV0YW3soj2xLCJzbgJ0YWJsZSoIMSw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMSw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojE4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4LCJ0eXB3oj24dGVadCJ9LHs4Zp33bGQ4O4Jjbim0cpF0bl91ZCosopFs6WFzoj24dGJsXiNvbnRyYXRvo4w4bGFuZgVhZiU4Ons4ZXM4O4JOdWl3cp85Qi9udHJhdG84LCJwdCoIo4J9LCJsYWJ3bCoIokmlbWVybyBDbim0cpF0byosonZ1ZXc4OjEsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4oyo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24o4w4dH3wZSoIonR3eHQ4fSx7opZ1ZWxkoj24Yi9udF9udWl3cp84LCJhbG3hcyoIonR4bF9jbim0cpF0byosopxhYpVsoj24Qi9udCBOdWl3cp84LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2zLCJg6WR06CoIojEwMCosopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4J9LHs4Zp33bGQ4O4JUcpF4YW1hZG9yZXNW6Wd3bnR3cyosopFs6WFzoj24o4w4bGFuZgVhZiU4Ons4ZXM4O4JUcpF4YW1hZG9yZXM4LCJwdCoIo4J9LCJsYWJ3bCoIo3RyYWJh6pFkbgJ3cyosonZ1ZXc4OjEsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4ozo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24o4w4dH3wZSoIonR3eHQ4fSx7opZ1ZWxkoj24VG90YWxDbgR1epFj6W9uZXM4LCJhbG3hcyoIo4osopxhbpdlYWd3oj17opVzoj24VG90YWw5SGF4ZXJ3cyosonB0oj24on0sopxhYpVsoj24VG90YWw5SGF4ZXJ3cyosonZ1ZXc4OjEsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4o0o4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24o4w4dH3wZSoIonR3eHQ4fV0sopZvcplzoj1bXX0=',
                            "module_lang"=>'{\"title\":{\"es\":\"Remuneraciones\",\"pt\":\"\"},\"note\":{\"es\":\"Remuneraciones\",\"pt\":\"\"}}',
                        );
        \DB::table('tb_module')->insert($lobjModule);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
         \DB::table('tb_module')->where('module_name', '=', 'remuneraciones')->delete();
    }
}
