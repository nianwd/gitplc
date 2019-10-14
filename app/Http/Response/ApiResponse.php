<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/26
 * Time: 14:19
 */
namespace App\Http\Response;

trait ApiResponse
{
    //参数错误
    public function parameterError($code = 1004,$message = '参数错误' , $data = null) {
        return $this->responseJson($code,$message,$data);
    }

    public function zidingyiError($message) {
        return $this->responseJson(10000,$message,null);
    }

    //处理成功
    public function success($message = 'success',$data = null) {
        return $this->responseJson(200,$message,$data);
    }

    public function successWithData($data = null,$message = 'success') {
        return $this->responseJson($code = 200,$message,$data);
    }

    public function error($code = 4001,$message = 'fail',$data = null) {
        return $this->responseJson($code,$message,$data);
    }

    public function responseJson($statusCode,$message,$data)
    {
        if (isset($GLOBALS['refreshToken']))
        return response()->json([ 'status_code' => $statusCode, 'message' => $message,'data' => $data, 'refresh_token'=>$GLOBALS['refreshToken']]);
        return response()->json([ 'status_code' => $statusCode, 'message' => $message,'data' => $data]);
    }

}
