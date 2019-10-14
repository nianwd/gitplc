<?php

namespace App\Http\Middleware;

use Closure;

class CheckPaypwd
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth('api')->user();

        if (!$user || blank($user->paypwd)){
            return response()->json(['status_code'=>1034,'message'=>'请设置支付密码']);
        }

        return $next($request);
    }
}
