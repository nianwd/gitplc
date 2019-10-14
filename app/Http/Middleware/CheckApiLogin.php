<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class CheckApiLogin extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        try {
            // 检查此次请求中是否带有 token，如果没有则抛出异常。
            $this->checkForToken($request);
        } catch(\Exception $e) {
            return response()->json(['status_code'=>1002,'message'=>'缺少token']);
        }


        try {
            $login_code = auth('api')->payload()->get('login_code');
//            dd($login_code);
            $user = $this->auth->parseToken()->authenticate();

            //暂时屏蔽单点登陆
//            if ($user && $login_code !== $user->login_code) {
//                throw new ApiException('你的账号在其他地方登录，你被迫下线','1003');
//            }

        } catch (TokenExpiredException $e) {
            try {
                // 刷新用户的 token
                $token = auth('api')->refresh();
                // 使用一次性登录以保证此次请求的成功
                $userId = auth('api')->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub'];
                auth('api')->onceUsingId($userId);
//                $request->merge(['user_id'=>$userId]);
                $GLOBALS['refreshToken'] = $token;

                // 在响应头中返回新的 token
                return $this->setAuthenticationHeader($next($request), $token);

//              echo($token);
            } catch (JWTException $exception) {
                // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                throw new ApiException('验证Token已过期，请重新登录',1003);
            }

        } catch (JWTException $e) {
            throw new ApiException('验证Token已过期，请重新登录',1003);
        } catch (TokenBlacklistedException $e){//黑名单异常
            throw new ApiException('验证Token已过期，请重新登录',1003);
        }catch (TokenInvalidException $e){
            throw new ApiException('token不正确',1003);
        }


        return $next($request);
    }


    //10分钟有效
    public function timeVerify($timestamp,$verify = 10)
    {
        $now = time();
        if (($now - $timestamp) >= ($verify*60) || ($now < $timestamp)) throw new ApiException('非法的API请求',403);
    }






}
