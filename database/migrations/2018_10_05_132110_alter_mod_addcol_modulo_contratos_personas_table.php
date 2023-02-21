<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterModAddcolModuloContratosPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $lobjModule = array("module_config"=>'eyJ0YWJsZV9kY4oIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJwcp3tYXJmXit3eSoIok3kQi9udHJhdG9zUGVyci9uYXM4LCJzcWxfciVsZWN0oj24U0VMRUNUoENBU0U5V0hFT4B0YpxfYi9udHJhdG8uSWRDbim0cpF06XN0YSA9oHR4bF9jbim0cpF06XN0YXMuSWRDbim0cpF06XN0YSBUSEVOoDA5RUxTRSAxoEVORCBBUyBU6XBvLCA5dGJsXiNvbnRyYXR1cgRhcymSYX1vb3NvYi3hbCw5dGJsXiNvbnRyYXRvcl9wZXJzbimhcyaqLCA5dGJsXgB3cnNvbpFzL3JVVCw5dGJsXgB3cnNvbpFzLkmvbWJyZXMsoHR4bF9wZXJzbimhcymBcGVsbG3kbgMsoHR4XgVzZXJzLpZ1cnN0XimhbWUsoHR4XgVzZXJzLpxhcgRfbpFtZSw5dGJsXgJvbGVzLkR3ciNy6XBj6VxlMDBpMia5YXM5Up9sXHJcb3x0XHQ5oCA5oCA5oCA5R3JPTSB0YpxfYi9udHJhdG9zXgB3cnNvbpFzXHJcb3x0XHQ5oCA5oCA5oCA5SUmORVo5Sk9JT4B0YpxfYi9udHJhdG85T0a5dGJsXiNvbnRyYXRvLpNvbnRyYXRvXi3koD05dGJsXiNvbnRyYXRvcl9wZXJzbimhcymjbim0cpF0bl91ZFxyXGmcdFx0oCA5oCA5oCA5oE3OTkVSoE1PSUa5dGJsXiNvbnRyYXR1cgRhcyBPT4B0YpxfYi9udHJhdG3zdGFzLk3kQi9udHJhdG3zdGE5PSB0YpxfYi9udHJhdG9zXgB3cnNvbpFzLk3kQi9udHJhdG3zdGFcc3xuXHRcdCA5oCA5oCA5oCBJTkmFU4BKT03OoHR4bF9wZXJzbimhcyBPT4B0YpxfcGVyci9uYXMuSWRQZXJzbimhoD05dGJsXiNvbnRyYXRvcl9wZXJzbimhcymJZFB3cnNvbpE5XHJcb3x0XHQ5oCA5oCA5oCA5SUmORVo5Sk9JT4B0Y39lciVycyBPT4B0YpxfcGVyci9uYXMuZWm0cn3fYn3fYWNjZXNzoD05dGJfdXN3cnMu6WRcc3xuXHRcdCA5oCA5oCA5oCBJTkmFU4BKT03OoHR4bF9ybix3cyBPT4B0YpxfYi9udHJhdG9zXgB3cnNvbpFzLk3kUp9soD05dGJsXgJvbGVzLk3kUp9so4w4cgFsXgd2ZXJ3oj24oFdoRVJFoHR4bF9jbim0cpF0bgNfcGVyci9uYXMuSWRDbim0cpF0bgNQZXJzbimhcyBJUyBOTlQ5T3VMTCosonNxbF9ncp9lcCoIo4osopdy6WQ4O3t7opZ1ZWxkoj24VG3wbyosopFs6WFzoj24o4w4bGF4ZWw4O4JU6XBvo4w4bGFuZgVhZiU4O3tdLCJzZWFyYi54O4oxo4w4ZG9gbpxvYWQ4O4oxo4w4YWx1Zia4O4JsZWZ0o4w4dp33dyoIojE4LCJkZXRh6Ww4O4oxo4w4ci9ydGF4bGU4O4oxo4w4ZnJvepVuoj24MCosoph1ZGR3b4oIojA4LCJzbgJ0bG3zdCoIMCw4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24UpFIbimTbiN1YWw4LCJhbG3hcyoIonR4bF9jbim0cpF06XN0YXM4LCJsYWmndWFnZSoIeyJ3cyoIokNvbnRyYXR1cgRho4w4cHQ4O4o4fSw4bGF4ZWw4O4JDbim0cpF06XN0YSosonZ1ZXc4OjEsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4oyo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24o4w4dH3wZSoIonR3eHQ4fSx7opZ1ZWxkoj24SWRDbim0cpF0bgNQZXJzbimhcyosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9wZXJzbimhcyosopxhbpdlYWd3oj17opVzoj24o4w4cHQ4O4o4fSw4bGF4ZWw4O4JJZENvbnRyYXRvclB3cnNvbpFzo4w4dp33dyoIMCw4ZGV0YW3soj2wLCJzbgJ0YWJsZSoIMCw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMCw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojE4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4LCJ0eXB3oj24dGVadCJ9LHs4Zp33bGQ4O4JJZFB3cnNvbpE4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIo3B3cnNvbpE4LCJwdCoIo4J9LCJsYWJ3bCoIo3B3cnNvbpE4LCJi6WVgoj2xLCJkZXRh6Ww4OjEsonNvcnRhYpx3oj2xLCJzZWFyYi54OjEsopRvdimsbiFkoj2xLCJpcp9IZWa4OjEsopx1bW30ZWQ4O4o4LCJg6WR06CoIojEwMCosopFs6Wduoj24bGVpdCosonNvcnRs6XN0oj24MyosopNvbpa4Ons4dpFs6WQ4O4oxo4w4ZGo4O4J0YpxfcGVyci9uYXM4LCJrZXk4O4JJZFB3cnNvbpE4LCJk6XNwbGFmoj24U3VUfEmvbWJyZXN8QXB3bGx1ZG9zon0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4osonRmcGU4O4J0ZXh0on0seyJp6WVsZCoIok3kQi9udHJhdG3zdGE4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIokNvbnRyYXR1cgRho4w4cHQ4O4o4fSw4bGF4ZWw4O4JDbim0cpF06XN0YSosonZ1ZXc4OjAsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4o0o4w4Yi9ub4oIeyJiYWx1ZCoIojE4LCJkY4oIonR4bF9jbim0cpF06XN0YXM4LCJrZXk4O4JJZENvbnRyYXR1cgRho4w4ZG3zcGxheSoIo3JVVHxSYX1vb3NvYi3hbCJ9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4LCJ0eXB3oj24dGVadCJ9LHs4Zp33bGQ4O4Jjbim0cpF0bl91ZCosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9wZXJzbimhcyosopxhbpdlYWd3oj17opVzoj24Qi9udHJhdG84LCJwdCoIo4J9LCJsYWJ3bCoIokNvbnRyYXRvo4w4dp33dyoIMCw4ZGV0YW3soj2xLCJzbgJ0YWJsZSoIMSw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMSw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojU4LCJjbimuoj17onZhbG3koj24MSosopR4oj24dGJsXiNvbnRyYXRvo4w46iVmoj24Yi9udHJhdG9f6WQ4LCJk6XNwbGFmoj24Yi9udF9udWl3cp98Yi9udF9ubil4cpU4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24o4w4dH3wZSoIonR3eHQ4fSx7opZ1ZWxkoj24SWRSbiw4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIo3JvbCosonB0oj24on0sopxhYpVsoj24Up9so4w4dp33dyoIMSw4ZGV0YW3soj2xLCJzbgJ0YWJsZSoIMSw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMSw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojY4LCJjbimuoj17onZhbG3koj24MSosopR4oj24dGJsXgJvbGVzo4w46iVmoj24SWRSbiw4LCJk6XNwbGFmoj24RGVzYgJ1cGN1XHUwMGYzb4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4LCJ0eXB3oj24dGVadCJ9LHs4Zp33bGQ4O4JJZEVzdGF0dXM4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIokVzdGF0dXM4LCJwdCoIo4J9LCJsYWJ3bCoIokVzdGF0dXM4LCJi6WVgoj2wLCJkZXRh6Ww4OjAsonNvcnRhYpx3oj2wLCJzZWFyYi54OjEsopRvdimsbiFkoj2wLCJpcp9IZWa4OjEsopx1bW30ZWQ4O4o4LCJg6WR06CoIojEwMCosopFs6Wduoj24bGVpdCosonNvcnRs6XN0oj24OCosopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4osonRmcGU4O4J0ZXh0on0seyJp6WVsZCoIo3J3bnRho4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4JDbgN0byBFbXByZXNho4w4cHQ4O4o4fSw4bGF4ZWw4O4JDbgN0byBFbXByZXNho4w4dp33dyoIMCw4ZGV0YW3soj2wLCJzbgJ0YWJsZSoIMCw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMCw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojk4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4LCJ0eXB3oj24dGVadCJ9LHs4Zp33bGQ4O4J3bnRyeV94eSosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9wZXJzbimhcyosopxhbpdlYWd3oj17opVzoj24o4w4cHQ4O4o4fSw4bGF4ZWw4O4JFbnRyeSBCeSosonZ1ZXc4OjAsopR3dGF1bCoIMCw4ci9ydGF4bGU4OjAsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjAsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4oxMSosopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4osonRmcGU4O4J0ZXh0on0seyJp6WVsZCoIopVudHJmXiJmXiFjYiVzcyosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9wZXJzbimhcyosopxhbpdlYWd3oj17opVzoj24o4w4cHQ4O4o4fSw4bGF4ZWw4O4JFbnRyeSBCeSBBYiN3cgM4LCJi6WVgoj2wLCJkZXRh6Ww4OjAsonNvcnRhYpx3oj2wLCJzZWFyYi54OjEsopRvdimsbiFkoj2wLCJpcp9IZWa4OjEsopx1bW30ZWQ4O4o4LCJg6WR06CoIojEwMCosopFs6Wduoj24bGVpdCosonNvcnRs6XN0oj24MTA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4LCJ0eXB3oj24dGVadCJ9LHs4Zp33bGQ4O4JJZEN3bnRybyosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9wZXJzbimhcyosopxhbpdlYWd3oj17opVzoj24QiVudHJvo4w4cHQ4O4o4fSw4bGF4ZWw4O4JDZWm0cp84LCJi6WVgoj2wLCJkZXRh6Ww4OjAsonNvcnRhYpx3oj2wLCJzZWFyYi54OjEsopRvdimsbiFkoj2wLCJpcp9IZWa4OjEsopx1bW30ZWQ4O4o4LCJg6WR06CoIojEwMCosopFs6Wduoj24bGVpdCosonNvcnRs6XN0oj24NyosopNvbpa4Ons4dpFs6WQ4O4oxo4w4ZGo4O4J0YpxfYiVudHJvo4w46iVmoj24SWRDZWm0cp84LCJk6XNwbGFmoj24RGVzYgJ1cGN1bia4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24o4w4dH3wZSoIonR3eHQ4fSx7opZ1ZWxkoj24YgJ3YXR3ZE9uo4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4o4LCJwdCoIo4J9LCJsYWJ3bCoIokNyZWF0ZWRPb4osonZ1ZXc4OjAsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4oxM4osopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4osonRmcGU4O4J0ZXh0on0seyJp6WVsZCoIo3JVVCosopFs6WFzoj24dGJsXgB3cnNvbpFzo4w4bGF4ZWw4O4JSVVQ4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2xMyw4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24Tp9tYnJ3cyosopFs6WFzoj24dGJsXgB3cnNvbpFzo4w4bGF4ZWw4O4JObil4cpVzo4w4bGFuZgVhZiU4O3tdLCJzZWFyYi54O4oxo4w4ZG9gbpxvYWQ4O4oxo4w4YWx1Zia4O4JsZWZ0o4w4dp33dyoIojE4LCJkZXRh6Ww4O4oxo4w4ci9ydGF4bGU4O4oxo4w4ZnJvepVuoj24MCosoph1ZGR3b4oIojA4LCJzbgJ0bG3zdCoIMTQsond1ZHR2oj24MTAwo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24on0seyJp6WVsZCoIokFwZWxs6WRvcyosopFs6WFzoj24dGJsXgB3cnNvbpFzo4w4bGF4ZWw4O4JBcGVsbG3kbgM4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2xNSw4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24Zp3ycgRfbpFtZSosopFs6WFzoj24dGJfdXN3cnM4LCJsYWJ3bCoIokZ1cnN0oEmhbWU4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2xN4w4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24bGFzdF9uYWl3o4w4YWx1YXM4O4J0Y39lciVycyosopxhYpVsoj24TGFzdCBOYWl3o4w4bGFuZgVhZiU4O3tdLCJzZWFyYi54O4oxo4w4ZG9gbpxvYWQ4O4oxo4w4YWx1Zia4O4JsZWZ0o4w4dp33dyoIojE4LCJkZXRh6Ww4O4oxo4w4ci9ydGF4bGU4O4oxo4w4ZnJvepVuoj24MCosoph1ZGR3b4oIojA4LCJzbgJ0bG3zdCoIMTcsond1ZHR2oj24MTAwo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24on0seyJp6WVsZCoIo3JvbCosopFs6WFzoj24dGJsXgJvbGVzo4w4bGF4ZWw4O4JSbiw4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2xOCw4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fV0sopZvcplzoj1beyJp6WVsZCoIok3kQi9udHJhdG9zUGVyci9uYXM4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIo4osonB0oj24on0sopxhYpVsoj24SWRDbim0cpF0bgNQZXJzbimhcyosopZvcplfZgJvdXA4O4o4LCJyZXFl6XJ3ZCoIojA4LCJi6WVgoj2xLCJ0eXB3oj246G3kZGVuo4w4YWRkoj2xLCJz6X13oj24MCosopVk6XQ4OjEsonN3YXJj6CoIojE4LCJzbgJ0bG3zdCoIojA4LCJs6Wl1dGVkoj24o4w4bgB06W9uoj17op9wdF90eXB3oj24o4w4bG9v6gVwXgFlZXJmoj24o4w4bG9v6gVwXgRhYpx3oj24o4w4bG9v6gVwXit3eSoIo4osopxvbitlcF9iYWxlZSoIo4osop3zXiR3cGVuZGVuYgk4O4o4LCJzZWx3YgRfbXVsdG3wbGU4O4owo4w46WlhZiVfbXVsdG3wbGU4O4owo4w4bG9v6gVwXiR3cGVuZGVuYg3f6iVmoj24o4w4cGF06F90bl9lcGxvYWQ4O4o4LCJyZXN1epVfdi3kdG54O4o4LCJyZXN1epVf6GV1Zih0oj24o4w4dXBsbiFkXgRmcGU4O4o4LCJ0bi9sdG3woj24o4w4YXR0cp34dXR3oj24o4w4ZXh0ZWmkXiNsYXNzoj24onl9LHs4Zp33bGQ4O4JJZFB3cnNvbpE4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIo3B3cnNvbpE4LCJwdCoIo4J9LCJsYWJ3bCoIo3B3cnNvbpE4LCJpbgJtXidybgVwoj24o4w4cpVxdW3yZWQ4O4owo4w4dp33dyoIMSw4dH3wZSoIonN3bGVjdCosopFkZCoIMSw4ci3IZSoIojA4LCJ3ZG30oj2xLCJzZWFyYi54O4oxo4w4ci9ydGx1cgQ4O4oxo4w4bG3t6XR3ZCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIopVadGVybpFso4w4bG9v6gVw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bgN0byBFbXByZXNho4w4cHQ4O4o4fSw4bGF4ZWw4O4JDbgN0byBFbXByZXNho4w4Zp9ybV9ncp9lcCoIo4osonJ3cXV1cpVkoj24MCosonZ1ZXc4OjAsonRmcGU4O4J0ZXh0o4w4YWRkoj2xLCJz6X13oj24MCosopVk6XQ4OjEsonN3YXJj6CoIMCw4ci9ydGx1cgQ4O4oio4w4bG3t6XR3ZCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIo4osopxvbitlcF9xdWVyeSoIo4osopxvbitlcF90YWJsZSoIo4osopxvbitlcF9rZXk4O4o4LCJsbi9rdXBfdpFsdWU4O4o4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4cpVz6X13Xgd1ZHR2oj24o4w4cpVz6X13Xih36Wd2dCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fSx7opZ1ZWxkoj24ZWm0cn3fYnk4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIo4osonB0oj24on0sopxhYpVsoj24RWm0cnk5Qnk4LCJpbgJtXidybgVwoj24o4w4cpVxdW3yZWQ4O4owo4w4dp33dyoIMSw4dH3wZSoIoph1ZGR3b4osopFkZCoIMSw4ci3IZSoIojA4LCJ3ZG30oj2xLCJzZWFyYi54O4oxo4w4ci9ydGx1cgQ4O4oao4w4bG3t6XR3ZCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIo4osopxvbitlcF9xdWVyeSoIo4osopxvbitlcF90YWJsZSoIo4osopxvbitlcF9rZXk4O4o4LCJsbi9rdXBfdpFsdWU4O4o4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4cpVz6X13Xgd1ZHR2oj24o4w4cpVz6X13Xih36Wd2dCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fSx7opZ1ZWxkoj24ZWm0cn3fYn3fYWNjZXNzo4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4o4LCJwdCoIo4J9LCJsYWJ3bCoIokVudHJmoEJmoEFjYiVzcyosopZvcplfZgJvdXA4O4o4LCJyZXFl6XJ3ZCoIojA4LCJi6WVgoj2wLCJ0eXB3oj24dGVadGFyZWE4LCJhZGQ4OjEsonN1epU4O4owo4w4ZWR1dCoIMSw4ciVhcpN2oj24MSosonNvcnRs6XN0oj24OCosopx1bW30ZWQ4O4o4LCJvcHR1bia4Ons4bgB0XgRmcGU4O4o4LCJsbi9rdXBfcXV3cnk4O4o4LCJsbi9rdXBfdGF4bGU4O4o4LCJsbi9rdXBf6iVmoj24o4w4bG9v6gVwXgZhbHV3oj24o4w46XNfZGVwZWmkZWmjeSoIo4osonN3bGVjdF9tdWx06XBsZSoIojA4LCJ1bWFnZV9tdWx06XBsZSoIojA4LCJsbi9rdXBfZGVwZWmkZWmjeV9rZXk4O4o4LCJwYXR2XgRvXgVwbG9hZCoIo4osonJ3ci3IZV9g6WR06CoIo4osonJ3ci3IZV92ZW3n6HQ4O4o4LCJlcGxvYWRfdH3wZSoIo4osonRvbix06XA4O4o4LCJhdHRy6WJldGU4O4o4LCJ3eHR3bpRfYixhcgM4O4o4fX0seyJp6WVsZCoIok3kQiVudHJvo4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4JDZWm0cp84LCJwdCoIo4J9LCJsYWJ3bCoIokN3bnRybyosopZvcplfZgJvdXA4O4o4LCJyZXFl6XJ3ZCoIojA4LCJi6WVgoj2xLCJ0eXB3oj24ciVsZWN0o4w4YWRkoj2xLCJz6X13oj24MCosopVk6XQ4OjEsonN3YXJj6CoIojE4LCJzbgJ0bG3zdCoIojU4LCJs6Wl1dGVkoj24o4w4bgB06W9uoj17op9wdF90eXB3oj24ZXh0ZXJuYWw4LCJsbi9rdXBfcXV3cnk4O4o4LCJsbi9rdXBfdGF4bGU4O4J0YpxfYiVudHJvo4w4bG9v6gVwXit3eSoIok3kQiVudHJvo4w4bG9v6gVwXgZhbHV3oj24RGVzYgJ1cGN1bia4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4cpVz6X13Xgd1ZHR2oj24o4w4cpVz6X13Xih36Wd2dCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fSx7opZ1ZWxkoj24YgJ3YXR3ZE9uo4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGF4ZWw4O4JDcpVhdGVkTia4LCJsYWmndWFnZSoIWl0sonJ3cXV1cpVkoj24MCosonZ1ZXc4O4oxo4w4dH3wZSoIonR3eHRhcpVho4w4YWRkoj24MSosopVk6XQ4O4oxo4w4ciVhcpN2oj24MSosonN1epU4O4JzcGFuMTo4LCJzbgJ0bG3zdCoIMTAsopZvcplfZgJvdXA4O4o4LCJvcHR1bia4Ons4bgB0XgRmcGU4O4o4LCJsbi9rdXBfcXV3cnk4O4o4LCJsbi9rdXBfdGF4bGU4O4o4LCJsbi9rdXBf6iVmoj24o4w4bG9v6gVwXgZhbHV3oj24o4w46XNfZGVwZWmkZWmjeSoIo4osonN3bGVjdF9tdWx06XBsZSoIojA4LCJ1bWFnZV9tdWx06XBsZSoIojA4LCJsbi9rdXBfZGVwZWmkZWmjeV9rZXk4O4o4LCJwYXR2XgRvXgVwbG9hZCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fV0sonN3dHR1bpc4Ons4ZgJ1ZHRmcGU4O4o4LCJvcpR3cpJmoj24VG3wbyosop9yZGVydH3wZSoIopFzYyosonB3cnBhZiU4O4oxMCosopZybg13b4oIopZhbHN3o4w4Zp9ybSltZXR2biQ4O4JuYXR1dpU4LCJi6WVgLWl3dGhvZCoIopmhdG3iZSosop3ubG3uZSoIopZhbHN3onl9');
        \DB::table('tb_module')
        ->where("tb_module.module_name","=", "contratospersonas")
        ->where("tb_module.module_type","!=", "checklaboral")
        ->update($lobjModule);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $lobjModule = array("module_config"=>'eyJ0YWJsZV9kY4oIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJwcp3tYXJmXit3eSoIok3kQi9udHJhdG9zUGVyci9uYXM4LCJzcWxfciVsZWN0oj24U0VMRUNUoENBU0U5V0hFT4B0YpxfYi9udHJhdG8uSWRDbim0cpF06XN0YSA9oHR4bF9jbim0cpF06XN0YXMuSWRDbim0cpF06XN0YSBUSEVOoDA5RUxTRSAxoEVORCBBUyBU6XBvLCA5dGJsXiNvbnRyYXR1cgRhcymSYX1vb3NvYi3hbCw5dGJsXiNvbnRyYXRvcl9wZXJzbimhcyaqLCA5dGJsXgB3cnNvbpFzL3JVVCw5dGJsXgB3cnNvbpFzLkmvbWJyZXMsoHR4bF9wZXJzbimhcymBcGVsbG3kbgMsoHR4XgVzZXJzLpZ1cnN0XimhbWUsoHR4XgVzZXJzLpxhcgRfbpFtZSw5dGJsXgJvbGVzLkR3ciNy6XBj6VxlMDBpMia5YXM5Up9sXHJcb3x0XHQ5oCA5oCA5oCA5R3JPTSB0YpxfYi9udHJhdG9zXgB3cnNvbpFzXHJcb3x0XHQ5oCA5oCA5oCA5SUmORVo5Sk9JT4B0YpxfYi9udHJhdG85T0a5dGJsXiNvbnRyYXRvLpNvbnRyYXRvXi3koD05dGJsXiNvbnRyYXRvcl9wZXJzbimhcymjbim0cpF0bl91ZFxyXGmcdFx0oCA5oCA5oCA5oE3OTkVSoE1PSUa5dGJsXiNvbnRyYXR1cgRhcyBPT4B0YpxfYi9udHJhdG3zdGFzLk3kQi9udHJhdG3zdGE5PSB0YpxfYi9udHJhdG9zXgB3cnNvbpFzLk3kQi9udHJhdG3zdGFcc3xuXHRcdCA5oCA5oCA5oCBJTkmFU4BKT03OoHR4bF9wZXJzbimhcyBPT4B0YpxfcGVyci9uYXMuSWRQZXJzbimhoD05dGJsXiNvbnRyYXRvcl9wZXJzbimhcymJZFB3cnNvbpE5XHJcb3x0XHQ5oCA5oCA5oCA5SUmORVo5Sk9JT4B0Y39lciVycyBPT4B0YpxfcGVyci9uYXMuZWm0cn3fYn3fYWNjZXNzoD05dGJfdXN3cnMu6WRcc3xuXHRcdCA5oCA5oCA5oCBJTkmFU4BKT03OoHR4bF9ybix3cyBPT4B0YpxfYi9udHJhdG9zXgB3cnNvbpFzLk3kUp9soD05dGJsXgJvbGVzLk3kUp9so4w4cgFsXgd2ZXJ3oj24oFdoRVJFoHR4bF9jbim0cpF0bgNfcGVyci9uYXMuSWRDbim0cpF0bgNQZXJzbimhcyBJUyBOTlQ5T3VMTCosonNxbF9ncp9lcCoIo4osopdy6WQ4O3t7opZ1ZWxkoj24VG3wbyosopFs6WFzoj24o4w4bGF4ZWw4O4JU6XBvo4w4bGFuZgVhZiU4O3tdLCJzZWFyYi54O4oxo4w4ZG9gbpxvYWQ4O4oxo4w4YWx1Zia4O4JsZWZ0o4w4dp33dyoIojE4LCJkZXRh6Ww4O4oxo4w4ci9ydGF4bGU4O4oxo4w4ZnJvepVuoj24MCosoph1ZGR3b4oIojA4LCJzbgJ0bG3zdCoIMCw4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24UpFIbimTbiN1YWw4LCJhbG3hcyoIonR4bF9jbim0cpF06XN0YXM4LCJsYWmndWFnZSoIeyJ3cyoIokNvbnRyYXR1cgRho4w4cHQ4O4o4fSw4bGF4ZWw4O4JDbim0cpF06XN0YSosonZ1ZXc4OjEsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4oyo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24o4w4dH3wZSoIonR3eHQ4fSx7opZ1ZWxkoj24SWRDbim0cpF0bgNQZXJzbimhcyosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9wZXJzbimhcyosopxhbpdlYWd3oj17opVzoj24o4w4cHQ4O4o4fSw4bGF4ZWw4O4JJZENvbnRyYXRvclB3cnNvbpFzo4w4dp33dyoIMCw4ZGV0YW3soj2wLCJzbgJ0YWJsZSoIMCw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMCw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojE4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4LCJ0eXB3oj24dGVadCJ9LHs4Zp33bGQ4O4JJZFB3cnNvbpE4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIo3B3cnNvbpE4LCJwdCoIo4J9LCJsYWJ3bCoIo3B3cnNvbpE4LCJi6WVgoj2xLCJkZXRh6Ww4OjEsonNvcnRhYpx3oj2xLCJzZWFyYi54OjEsopRvdimsbiFkoj2xLCJpcp9IZWa4OjEsopx1bW30ZWQ4O4o4LCJg6WR06CoIojEwMCosopFs6Wduoj24bGVpdCosonNvcnRs6XN0oj24MyosopNvbpa4Ons4dpFs6WQ4O4oxo4w4ZGo4O4J0YpxfcGVyci9uYXM4LCJrZXk4O4JJZFB3cnNvbpE4LCJk6XNwbGFmoj24U3VUfEmvbWJyZXN8QXB3bGx1ZG9zon0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4osonRmcGU4O4J0ZXh0on0seyJp6WVsZCoIok3kQi9udHJhdG3zdGE4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIokNvbnRyYXR1cgRho4w4cHQ4O4o4fSw4bGF4ZWw4O4JDbim0cpF06XN0YSosonZ1ZXc4OjAsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4o0o4w4Yi9ub4oIeyJiYWx1ZCoIojE4LCJkY4oIonR4bF9jbim0cpF06XN0YXM4LCJrZXk4O4JJZENvbnRyYXR1cgRho4w4ZG3zcGxheSoIo3JVVHxSYX1vb3NvYi3hbCJ9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4LCJ0eXB3oj24dGVadCJ9LHs4Zp33bGQ4O4Jjbim0cpF0bl91ZCosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9wZXJzbimhcyosopxhbpdlYWd3oj17opVzoj24Qi9udHJhdG84LCJwdCoIo4J9LCJsYWJ3bCoIokNvbnRyYXRvo4w4dp33dyoIMCw4ZGV0YW3soj2xLCJzbgJ0YWJsZSoIMSw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMSw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojU4LCJjbimuoj17onZhbG3koj24MSosopR4oj24dGJsXiNvbnRyYXRvo4w46iVmoj24Yi9udHJhdG9f6WQ4LCJk6XNwbGFmoj24Yi9udF9udWl3cp98Yi9udF9ubil4cpU4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24o4w4dH3wZSoIonR3eHQ4fSx7opZ1ZWxkoj24SWRSbiw4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIo3JvbCosonB0oj24on0sopxhYpVsoj24Up9so4w4dp33dyoIMSw4ZGV0YW3soj2xLCJzbgJ0YWJsZSoIMSw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMSw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojY4LCJjbimuoj17onZhbG3koj24MSosopR4oj24dGJsXgJvbGVzo4w46iVmoj24SWRSbiw4LCJk6XNwbGFmoj24RGVzYgJ1cGN1XHUwMGYzb4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4LCJ0eXB3oj24dGVadCJ9LHs4Zp33bGQ4O4JJZEVzdGF0dXM4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIokVzdGF0dXM4LCJwdCoIo4J9LCJsYWJ3bCoIokVzdGF0dXM4LCJi6WVgoj2wLCJkZXRh6Ww4OjAsonNvcnRhYpx3oj2wLCJzZWFyYi54OjEsopRvdimsbiFkoj2wLCJpcp9IZWa4OjEsopx1bW30ZWQ4O4o4LCJg6WR06CoIojEwMCosopFs6Wduoj24bGVpdCosonNvcnRs6XN0oj24OCosopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4osonRmcGU4O4J0ZXh0on0seyJp6WVsZCoIo3J3bnRho4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4JDbgN0byBFbXByZXNho4w4cHQ4O4o4fSw4bGF4ZWw4O4JDbgN0byBFbXByZXNho4w4dp33dyoIMCw4ZGV0YW3soj2wLCJzbgJ0YWJsZSoIMCw4ciVhcpN2oj2xLCJkbgdubG9hZCoIMCw4ZnJvepVuoj2xLCJs6Wl1dGVkoj24o4w4di3kdG54O4oxMDA4LCJhbG3nb4oIopx3ZnQ4LCJzbgJ0bG3zdCoIojk4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4LCJ0eXB3oj24dGVadCJ9LHs4Zp33bGQ4O4J3bnRyeV94eSosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9wZXJzbimhcyosopxhbpdlYWd3oj17opVzoj24o4w4cHQ4O4o4fSw4bGF4ZWw4O4JFbnRyeSBCeSosonZ1ZXc4OjAsopR3dGF1bCoIMCw4ci9ydGF4bGU4OjAsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjAsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4oxMSosopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4osonRmcGU4O4J0ZXh0on0seyJp6WVsZCoIopVudHJmXiJmXiFjYiVzcyosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9wZXJzbimhcyosopxhbpdlYWd3oj17opVzoj24o4w4cHQ4O4o4fSw4bGF4ZWw4O4JFbnRyeSBCeSBBYiN3cgM4LCJi6WVgoj2wLCJkZXRh6Ww4OjAsonNvcnRhYpx3oj2wLCJzZWFyYi54OjEsopRvdimsbiFkoj2wLCJpcp9IZWa4OjEsopx1bW30ZWQ4O4o4LCJg6WR06CoIojEwMCosopFs6Wduoj24bGVpdCosonNvcnRs6XN0oj24MTA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4LCJ0eXB3oj24dGVadCJ9LHs4Zp33bGQ4O4JJZEN3bnRybyosopFs6WFzoj24dGJsXiNvbnRyYXRvcl9wZXJzbimhcyosopxhbpdlYWd3oj17opVzoj24QiVudHJvo4w4cHQ4O4o4fSw4bGF4ZWw4O4JDZWm0cp84LCJi6WVgoj2wLCJkZXRh6Ww4OjAsonNvcnRhYpx3oj2wLCJzZWFyYi54OjEsopRvdimsbiFkoj2wLCJpcp9IZWa4OjEsopx1bW30ZWQ4O4o4LCJg6WR06CoIojEwMCosopFs6Wduoj24bGVpdCosonNvcnRs6XN0oj24NyosopNvbpa4Ons4dpFs6WQ4O4oxo4w4ZGo4O4J0YpxfYiVudHJvo4w46iVmoj24SWRDZWm0cp84LCJk6XNwbGFmoj24RGVzYgJ1cGN1bia4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24o4w4dH3wZSoIonR3eHQ4fSx7opZ1ZWxkoj24YgJ3YXR3ZE9uo4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4o4LCJwdCoIo4J9LCJsYWJ3bCoIokNyZWF0ZWRPb4osonZ1ZXc4OjAsopR3dGF1bCoIMSw4ci9ydGF4bGU4OjEsonN3YXJj6CoIMSw4ZG9gbpxvYWQ4OjEsopZybg13b4oIMSw4bG3t6XR3ZCoIo4osond1ZHR2oj24MTAwo4w4YWx1Zia4O4JsZWZ0o4w4ci9ydGx1cgQ4O4oxM4osopNvbpa4Ons4dpFs6WQ4O4owo4w4ZGo4O4o4LCJrZXk4O4o4LCJk6XNwbGFmoj24on0sopZvcplhdF9hcyoIo4osopZvcplhdF9iYWxlZSoIo4osonRmcGU4O4J0ZXh0on0seyJp6WVsZCoIo3JVVCosopFs6WFzoj24dGJsXgB3cnNvbpFzo4w4bGF4ZWw4O4JSVVQ4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2xMyw4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24Tp9tYnJ3cyosopFs6WFzoj24dGJsXgB3cnNvbpFzo4w4bGF4ZWw4O4JObil4cpVzo4w4bGFuZgVhZiU4O3tdLCJzZWFyYi54O4oxo4w4ZG9gbpxvYWQ4O4oxo4w4YWx1Zia4O4JsZWZ0o4w4dp33dyoIojE4LCJkZXRh6Ww4O4oxo4w4ci9ydGF4bGU4O4oxo4w4ZnJvepVuoj24MCosoph1ZGR3b4oIojA4LCJzbgJ0bG3zdCoIMTQsond1ZHR2oj24MTAwo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24on0seyJp6WVsZCoIokFwZWxs6WRvcyosopFs6WFzoj24dGJsXgB3cnNvbpFzo4w4bGF4ZWw4O4JBcGVsbG3kbgM4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2xNSw4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24Zp3ycgRfbpFtZSosopFs6WFzoj24dGJfdXN3cnM4LCJsYWJ3bCoIokZ1cnN0oEmhbWU4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2xN4w4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fSx7opZ1ZWxkoj24bGFzdF9uYWl3o4w4YWx1YXM4O4J0Y39lciVycyosopxhYpVsoj24TGFzdCBOYWl3o4w4bGFuZgVhZiU4O3tdLCJzZWFyYi54O4oxo4w4ZG9gbpxvYWQ4O4oxo4w4YWx1Zia4O4JsZWZ0o4w4dp33dyoIojE4LCJkZXRh6Ww4O4oxo4w4ci9ydGF4bGU4O4oxo4w4ZnJvepVuoj24MCosoph1ZGR3b4oIojA4LCJzbgJ0bG3zdCoIMTcsond1ZHR2oj24MTAwo4w4Yi9ub4oIeyJiYWx1ZCoIojA4LCJkY4oIo4osopt3eSoIo4osopR1cgBsYXk4O4o4fSw4Zp9ybWF0XiFzoj24o4w4Zp9ybWF0XgZhbHV3oj24on0seyJp6WVsZCoIo3JvbCosopFs6WFzoj24dGJsXgJvbGVzo4w4bGF4ZWw4O4JSbiw4LCJsYWmndWFnZSoIWl0sonN3YXJj6CoIojE4LCJkbgdubG9hZCoIojE4LCJhbG3nb4oIopx3ZnQ4LCJi6WVgoj24MSosopR3dGF1bCoIojE4LCJzbgJ0YWJsZSoIojE4LCJpcp9IZWa4O4owo4w46G3kZGVuoj24MCosonNvcnRs6XN0oj2xOCw4di3kdG54O4oxMDA4LCJjbimuoj17onZhbG3koj24MCosopR4oj24o4w46iVmoj24o4w4ZG3zcGxheSoIo4J9LCJpbgJtYXRfYXM4O4o4LCJpbgJtYXRfdpFsdWU4O4o4fV0sopZvcplzoj1beyJp6WVsZCoIok3kQi9udHJhdG9zUGVyci9uYXM4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIo4osonB0oj24on0sopxhYpVsoj24SWRDbim0cpF0bgNQZXJzbimhcyosopZvcplfZgJvdXA4O4o4LCJyZXFl6XJ3ZCoIojA4LCJi6WVgoj2xLCJ0eXB3oj246G3kZGVuo4w4YWRkoj2xLCJz6X13oj24MCosopVk6XQ4OjEsonN3YXJj6CoIojE4LCJzbgJ0bG3zdCoIojA4LCJs6Wl1dGVkoj24o4w4bgB06W9uoj17op9wdF90eXB3oj24o4w4bG9v6gVwXgFlZXJmoj24o4w4bG9v6gVwXgRhYpx3oj24o4w4bG9v6gVwXit3eSoIo4osopxvbitlcF9iYWxlZSoIo4osop3zXiR3cGVuZGVuYgk4O4o4LCJzZWx3YgRfbXVsdG3wbGU4O4owo4w46WlhZiVfbXVsdG3wbGU4O4owo4w4bG9v6gVwXiR3cGVuZGVuYg3f6iVmoj24o4w4cGF06F90bl9lcGxvYWQ4O4o4LCJyZXN1epVfdi3kdG54O4o4LCJyZXN1epVf6GV1Zih0oj24o4w4dXBsbiFkXgRmcGU4O4o4LCJ0bi9sdG3woj24o4w4YXR0cp34dXR3oj24o4w4ZXh0ZWmkXiNsYXNzoj24onl9LHs4Zp33bGQ4O4JJZFB3cnNvbpE4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIo3B3cnNvbpE4LCJwdCoIo4J9LCJsYWJ3bCoIo3B3cnNvbpE4LCJpbgJtXidybgVwoj24o4w4cpVxdW3yZWQ4O4owo4w4dp33dyoIMSw4dH3wZSoIonN3bGVjdCosopFkZCoIMSw4ci3IZSoIojA4LCJ3ZG30oj2xLCJzZWFyYi54O4oxo4w4ci9ydGx1cgQ4O4oxo4w4bG3t6XR3ZCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIopVadGVybpFso4w4bG9v6gVwXgFlZXJmoj24o4w4bG9v6gVwXgRhYpx3oj24dGJsXgB3cnNvbpFzo4w4bG9v6gVwXit3eSoIok3kUGVyci9uYSosopxvbitlcF9iYWxlZSoIo3JVVHxObil4cpVzfEFwZWxs6WRvcyosop3zXiR3cGVuZGVuYgk4O4o4LCJzZWx3YgRfbXVsdG3wbGU4O4owo4w46WlhZiVfbXVsdG3wbGU4O4owo4w4bG9v6gVwXiR3cGVuZGVuYg3f6iVmoj24o4w4cGF06F90bl9lcGxvYWQ4O4o4LCJyZXN1epVfdi3kdG54O4o4LCJyZXN1epVf6GV1Zih0oj24o4w4dXBsbiFkXgRmcGU4O4o4LCJ0bi9sdG3woj24o4w4YXR0cp34dXR3oj24o4w4ZXh0ZWmkXiNsYXNzoj24onl9LHs4Zp33bGQ4O4JJZENvbnRyYXR1cgRho4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4JDbim0cpF06XN0YSosonB0oj24on0sopxhYpVsoj24Qi9udHJhdG3zdGE4LCJpbgJtXidybgVwoj24o4w4cpVxdW3yZWQ4O4owo4w4dp33dyoIMSw4dH3wZSoIonN3bGVjdCosopFkZCoIMSw4ci3IZSoIojA4LCJ3ZG30oj2xLCJzZWFyYi54O4oxo4w4ci9ydGx1cgQ4O4oyo4w4bG3t6XR3ZCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIopVadGVybpFso4w4bG9v6gVwXgFlZXJmoj24o4w4bG9v6gVwXgRhYpx3oj24dGJsXiNvbnRyYXR1cgRhcyosopxvbitlcF9rZXk4O4JJZENvbnRyYXR1cgRho4w4bG9v6gVwXgZhbHV3oj24U3VUfFJhep9uUi9j6WFso4w46XNfZGVwZWmkZWmjeSoIo4osonN3bGVjdF9tdWx06XBsZSoIojA4LCJ1bWFnZV9tdWx06XBsZSoIojA4LCJsbi9rdXBfZGVwZWmkZWmjeV9rZXk4O4o4LCJwYXR2XgRvXgVwbG9hZCoIo4osonJ3ci3IZV9g6WR06CoIo4osonJ3ci3IZV92ZW3n6HQ4O4o4LCJlcGxvYWRfdH3wZSoIo4osonRvbix06XA4O4o4LCJhdHRy6WJldGU4O4o4LCJ3eHR3bpRfYixhcgM4O4o4fX0seyJp6WVsZCoIopNvbnRyYXRvXi3ko4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4JDbim0cpF0byosonB0oj24on0sopxhYpVsoj24Qi9udHJhdG84LCJpbgJtXidybgVwoj24o4w4cpVxdW3yZWQ4O4owo4w4dp33dyoIMSw4dH3wZSoIonN3bGVjdCosopFkZCoIMSw4ci3IZSoIojA4LCJ3ZG30oj2xLCJzZWFyYi54O4oxo4w4ci9ydGx1cgQ4O4ozo4w4bG3t6XR3ZCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIopVadGVybpFso4w4bG9v6gVwXgFlZXJmoj24o4w4bG9v6gVwXgRhYpx3oj24dGJsXiNvbnRyYXRvo4w4bG9v6gVwXit3eSoIopNvbnRyYXRvXi3ko4w4bG9v6gVwXgZhbHV3oj24Yi9udF9udWl3cp84LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4cpVz6X13Xgd1ZHR2oj24o4w4cpVz6X13Xih36Wd2dCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fSx7opZ1ZWxkoj24SWRSbiw4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIo3JvbCosonB0oj24on0sopxhYpVsoj24Up9so4w4Zp9ybV9ncp9lcCoIo4osonJ3cXV1cpVkoj24MCosonZ1ZXc4OjEsonRmcGU4O4JzZWx3YgQ4LCJhZGQ4OjEsonN1epU4O4owo4w4ZWR1dCoIMSw4ciVhcpN2oj24MSosonNvcnRs6XN0oj24NCosopx1bW30ZWQ4O4o4LCJvcHR1bia4Ons4bgB0XgRmcGU4O4J3eHR3cpmhbCosopxvbitlcF9xdWVyeSoIo4osopxvbitlcF90YWJsZSoIonR4bF9ybix3cyosopxvbitlcF9rZXk4O4JJZFJvbCosopxvbitlcF9iYWxlZSoIokR3ciNy6XBj6VxlMDBpMia4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4cpVz6X13Xgd1ZHR2oj24o4w4cpVz6X13Xih36Wd2dCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fSx7opZ1ZWxkoj24SWRFcgRhdHVzo4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4JFcgRhdHVzo4w4cHQ4O4o4fSw4bGF4ZWw4O4JFcgRhdHVzo4w4Zp9ybV9ncp9lcCoIo4osonJ3cXV1cpVkoj24MCosonZ1ZXc4OjAsonRmcGU4O4JzZWx3YgQ4LCJhZGQ4OjEsonN1epU4O4owo4w4ZWR1dCoIMSw4ciVhcpN2oj2wLCJzbgJ0bG3zdCoIojU4LCJs6Wl1dGVkoj24o4w4bgB06W9uoj17op9wdF90eXB3oj24ZGF0YWx1cgQ4LCJsbi9rdXBfcXV3cnk4O4oxOkFjdG3ibgwyO3NlcgB3bpR1ZG84LCJsbi9rdXBfdGF4bGU4O4o4LCJsbi9rdXBf6iVmoj24o4w4bG9v6gVwXgZhbHV3oj24o4w46XNfZGVwZWmkZWmjeSoIo4osonN3bGVjdF9tdWx06XBsZSoIojA4LCJ1bWFnZV9tdWx06XBsZSoIojA4LCJsbi9rdXBfZGVwZWmkZWmjeV9rZXk4O4o4LCJwYXR2XgRvXgVwbG9hZCoIo4osonJ3ci3IZV9g6WR06CoIo4osonJ3ci3IZV92ZW3n6HQ4O4o4LCJlcGxvYWRfdH3wZSoIo4osonRvbix06XA4O4o4LCJhdHRy6WJldGU4O4o4LCJ3eHR3bpRfYixhcgM4O4o4fX0seyJp6WVsZCoIo3J3bnRho4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4JDbgN0byBFbXByZXNho4w4cHQ4O4o4fSw4bGF4ZWw4O4JDbgN0byBFbXByZXNho4w4Zp9ybV9ncp9lcCoIo4osonJ3cXV1cpVkoj24MCosonZ1ZXc4OjAsonRmcGU4O4J0ZXh0o4w4YWRkoj2xLCJz6X13oj24MCosopVk6XQ4OjEsonN3YXJj6CoIMCw4ci9ydGx1cgQ4O4oio4w4bG3t6XR3ZCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIo4osopxvbitlcF9xdWVyeSoIo4osopxvbitlcF90YWJsZSoIo4osopxvbitlcF9rZXk4O4o4LCJsbi9rdXBfdpFsdWU4O4o4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4cpVz6X13Xgd1ZHR2oj24o4w4cpVz6X13Xih36Wd2dCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fSx7opZ1ZWxkoj24ZWm0cn3fYnk4LCJhbG3hcyoIonR4bF9jbim0cpF0bgNfcGVyci9uYXM4LCJsYWmndWFnZSoIeyJ3cyoIo4osonB0oj24on0sopxhYpVsoj24RWm0cnk5Qnk4LCJpbgJtXidybgVwoj24o4w4cpVxdW3yZWQ4O4owo4w4dp33dyoIMSw4dH3wZSoIoph1ZGR3b4osopFkZCoIMSw4ci3IZSoIojA4LCJ3ZG30oj2xLCJzZWFyYi54O4oxo4w4ci9ydGx1cgQ4O4oao4w4bG3t6XR3ZCoIo4osop9wdG3vb4oIeyJvcHRfdH3wZSoIo4osopxvbitlcF9xdWVyeSoIo4osopxvbitlcF90YWJsZSoIo4osopxvbitlcF9rZXk4O4o4LCJsbi9rdXBfdpFsdWU4O4o4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4cpVz6X13Xgd1ZHR2oj24o4w4cpVz6X13Xih36Wd2dCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fSx7opZ1ZWxkoj24ZWm0cn3fYn3fYWNjZXNzo4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4o4LCJwdCoIo4J9LCJsYWJ3bCoIokVudHJmoEJmoEFjYiVzcyosopZvcplfZgJvdXA4O4o4LCJyZXFl6XJ3ZCoIojA4LCJi6WVgoj2wLCJ0eXB3oj24dGVadGFyZWE4LCJhZGQ4OjEsonN1epU4O4owo4w4ZWR1dCoIMSw4ciVhcpN2oj24MSosonNvcnRs6XN0oj24OCosopx1bW30ZWQ4O4o4LCJvcHR1bia4Ons4bgB0XgRmcGU4O4o4LCJsbi9rdXBfcXV3cnk4O4o4LCJsbi9rdXBfdGF4bGU4O4o4LCJsbi9rdXBf6iVmoj24o4w4bG9v6gVwXgZhbHV3oj24o4w46XNfZGVwZWmkZWmjeSoIo4osonN3bGVjdF9tdWx06XBsZSoIojA4LCJ1bWFnZV9tdWx06XBsZSoIojA4LCJsbi9rdXBfZGVwZWmkZWmjeV9rZXk4O4o4LCJwYXR2XgRvXgVwbG9hZCoIo4osonJ3ci3IZV9g6WR06CoIo4osonJ3ci3IZV92ZW3n6HQ4O4o4LCJlcGxvYWRfdH3wZSoIo4osonRvbix06XA4O4o4LCJhdHRy6WJldGU4O4o4LCJ3eHR3bpRfYixhcgM4O4o4fX0seyJp6WVsZCoIok3kQiVudHJvo4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGFuZgVhZiU4Ons4ZXM4O4JDZWm0cp84LCJwdCoIo4J9LCJsYWJ3bCoIokN3bnRybyosopZvcplfZgJvdXA4O4o4LCJyZXFl6XJ3ZCoIojA4LCJi6WVgoj2xLCJ0eXB3oj24ciVsZWN0o4w4YWRkoj2xLCJz6X13oj24MCosopVk6XQ4OjEsonN3YXJj6CoIojE4LCJzbgJ0bG3zdCoIojU4LCJs6Wl1dGVkoj24o4w4bgB06W9uoj17op9wdF90eXB3oj24ZXh0ZXJuYWw4LCJsbi9rdXBfcXV3cnk4O4o4LCJsbi9rdXBfdGF4bGU4O4J0YpxfYiVudHJvo4w4bG9v6gVwXit3eSoIok3kQiVudHJvo4w4bG9v6gVwXgZhbHV3oj24RGVzYgJ1cGN1bia4LCJ1cl9kZXB3bpR3bpNmoj24o4w4ciVsZWN0XillbHR1cGx3oj24MCosop3tYWd3XillbHR1cGx3oj24MCosopxvbitlcF9kZXB3bpR3bpNmXit3eSoIo4osonBhdGhfdG9fdXBsbiFkoj24o4w4cpVz6X13Xgd1ZHR2oj24o4w4cpVz6X13Xih36Wd2dCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fSx7opZ1ZWxkoj24YgJ3YXR3ZE9uo4w4YWx1YXM4O4J0YpxfYi9udHJhdG9zXgB3cnNvbpFzo4w4bGF4ZWw4O4JDcpVhdGVkTia4LCJsYWmndWFnZSoIWl0sonJ3cXV1cpVkoj24MCosonZ1ZXc4O4oxo4w4dH3wZSoIonR3eHRhcpVho4w4YWRkoj24MSosopVk6XQ4O4oxo4w4ciVhcpN2oj24MSosonN1epU4O4JzcGFuMTo4LCJzbgJ0bG3zdCoIMTAsopZvcplfZgJvdXA4O4o4LCJvcHR1bia4Ons4bgB0XgRmcGU4O4o4LCJsbi9rdXBfcXV3cnk4O4o4LCJsbi9rdXBfdGF4bGU4O4o4LCJsbi9rdXBf6iVmoj24o4w4bG9v6gVwXgZhbHV3oj24o4w46XNfZGVwZWmkZWmjeSoIo4osonN3bGVjdF9tdWx06XBsZSoIojA4LCJ1bWFnZV9tdWx06XBsZSoIojA4LCJsbi9rdXBfZGVwZWmkZWmjeV9rZXk4O4o4LCJwYXR2XgRvXgVwbG9hZCoIo4osonVwbG9hZF90eXB3oj24o4w4dG9vbHR1cCoIo4osopF0dHJ1YnV0ZSoIo4osopVadGVuZF9jbGFzcyoIo4J9fV0sonN3dHR1bpc4Ons4ZgJ1ZHRmcGU4O4o4LCJvcpR3cpJmoj24VG3wbyosop9yZGVydH3wZSoIopFzYyosonB3cnBhZiU4O4oxMCosopZybg13b4oIopZhbHN3o4w4Zp9ybSltZXR2biQ4O4JuYXR1dpU4LCJi6WVgLWl3dGhvZCoIopmhdG3iZSosop3ubG3uZSoIopZhbHN3onl9');
        \DB::table('tb_module')
        ->where("tb_module.module_name","=", "contratospersonas")
        ->where("tb_module.module_type","!=", "checklaboral")
        ->update($lobjModule);
    }
}
