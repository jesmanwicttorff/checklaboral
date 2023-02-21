<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use JWTAuth;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $flag=false;
      if ($this->auth->guest()) {
        try {
          $user = JWTAuth::parseToken()->authenticate();
          $flag=true;
        } catch (\Exception $e) {
          if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
            return response()->json(['status' => 'Token no valido','code' => '401.1'],401);
          }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
            return response()->json(['status' => 'Token expirado','code' => '401.1'],401);
          }else{
            //return response()->json(['status' => 'Se necesita un token de autorización','code' => '401.1'],401);
          }
        }
        if ($request->ajax()) {
          return response('Unauthorized.', 401);
        } else {
          if(!$flag){
            return redirect()->guest('user/login');
          }
        }
      }

      return $next($request);
    }
}
