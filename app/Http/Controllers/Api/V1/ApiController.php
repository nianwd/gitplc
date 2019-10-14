<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/9
 * Time: 10:28
 */

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Http\Response\ApiResponse;
use App\Traits\FileTools;
use App\Traits\RedisTool;
use App\Traits\Tools;

class ApiController extends Controller
{
    use FileTools,Tools,RedisTool,ApiResponse;

//    public function __construct()
//    {
//        $this->middleware(function ($request, $next) {
//            $this->request = $request;
//
//            if (auth('api')->check()) {
//                $this->user = $this->current_user();
//            }
//
//            return $next($request);
//        });
//    }

    public function current_user()
    {
        $user = auth('api')->user();

        $status = $user->status;

        if ($status == 'lock') {
            throw new ApiException('用户锁定',4004);
        } elseif ($status == 'deleted') {
            throw new ApiException('用户删除',4004);
        } elseif ($status == 'enable') {
            return $user;
        } else {
            return $user;
//            throw new ApiException('用户异常',4004);
        }
    }

}
