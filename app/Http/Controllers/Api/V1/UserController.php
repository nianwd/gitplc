<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiException;
use App\Models\Advice;
use App\Models\User;
use App\Notifications\AdviceProcessed;
use App\Notifications\DirectUserRegister;
use App\Services\CoinServices\GethService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserController extends ApiController
{
    //

    public function test()
    {
//        $user = User::query()->findOrFail(21);
//        $advice = Advice::query()->where('user_id',$user['user_id'])->first();
//        $user->notify(new AdviceProcessed($advice));
//        throw new ApiException('aaa');
//        dd(numToWord(10));
    }

    //模拟登陆获取token
    public function mockLogin(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'user_id' => 'required|integer'
        ])) return $vr;

        $user = User::query()->find($request->user_id);

        //生成token
        $login_code = User::gen_login_code();
        $user->login_code = $login_code;
        $user->last_login_ip = $request->getClientIp();
        $user->last_login_time = Carbon::now()->toDateTimeString();
        $user->save();

        $token =  auth('api')->claims(['login_code' => $login_code])->fromUser($user);

        return $this->successWithData(['token' => $token,'user_id'=>$user['user_id']]);
    }

    public function add_advice(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
//            'phone' => 'string',
//            'email' => 'string',
//            'realname' => 'string',
            'contents' => 'required|string',
        ])) return $vr;

        $user = $this->current_user();

        $params = $request->only(['phone','email','contents']);
        $params['user_id'] = $user['user_id'];

        $res = Advice::query()->create($params);

        if(!$res){
            return $this->error();
        }
        return $this->success();
    }

    public function advices()
    {
        $user = $this->current_user();

        $advices = Advice::query()->where(['user_id'=>$user['user_id']])->latest()->paginate();

        return $this->successWithData($advices);
    }

    public function advice_detail(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'id' => 'required|integer',
        ])) return $vr;

        $user = $this->current_user();

        $advice = Advice::query()->where(['user_id'=>$user['user_id'],'id'=>$request->id])->firstOrFail();

        return $this->successWithData($advice);
    }

    //用户消息通知
    public function myNotifiablesCount(Request $request)
    {
        $user = $this->current_user();

        $count = $user->unreadNotifications()->count();

        return $this->successWithData(['count'=>$count]);
    }

    public function myNotifiables(Request $request)
    {
        $user = $this->current_user();

        $notifiables = $user->notifications;

        //全部标记已读
        $user->unreadNotifications->markAsRead();

        return $this->successWithData($notifiables);
    }

    public function readNotifiable(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'id' => 'required',
        ])) return $vr;

        $user = $this->current_user();

        $notifiable = $user->notifications()->where('id',$request->id)->firstOrFail();

        //标记消息为已读
        $notifiable->markAsRead();

        return $this->successWithData($notifiable);
    }

    public function register(Request $request,User $user)
    {
        if ($vr = $this->verifyField($request->all(),[
            'phone' => 'required|string',
            'password' => 'required|string|min:8|max:16|confirmed:password_confirmation',
            'password_confirmation' => 'required|string',
            'sms_code' => 'required|string',
            'invite_code' => 'required|string',

//            'email' => 'email'
        ])) return $vr;

        $lockKey = 'user_register_keylock:' . $request->phone;
        if (!$this->setKeyLock($lockKey,5)) return $this->error();

        if ($user->getUserByPhone($request->phone)) throw new ApiException('手机号已被注册');

//        $checkResult = checkSMSCode($request->phone,$request->sms_code);
//        if ($checkResult !== true) return $this->error(4001,$checkResult);

        //邀请注册
        if($invite_code = $request->input('invite_code')){
            $parent_user = User::query()->where('invite_code',$invite_code)->first();
            if(!$parent_user){
                throw new ApiException('不存在该邀请码');
            }
            $user->pid = $parent_user->user_id;
            $user->deep = $parent_user->deep + 1;
        }else{
            $user->pid = 0;
            $user->deep = 0;
        }

        $user->phone = $request->phone;
        $user->password = $user->passwordHash($request->password);
        $user->reg_ip = $request->getClientIp();
        $user->invite_code = User::gen_invite_code();
        $user->username = $request->phone;

        DB::beginTransaction();
        try{
            $user->save();

            $loginCode = User::gen_login_code(6);
            $token = auth('api')->claims(['login_code'=>$loginCode])->fromUser($user);
            $user->update(['login_code'=>$loginCode,'last_login_ip'=>$request->getClientIp(),'last_login_time' => Carbon::now()->toDateTimeString()]);

            $wallet_password = 'eth_pass_'.$user->user_id;
            $wallet_address = (new GethService())->newAccount($wallet_password);

            $user->user_wallet()->create([
                'user_id'=>$user['user_id'],
                'coin_id'=>1,
                'wallet_address'=>$wallet_address,
                'wallet_password'=>$wallet_password
            ]);

            $return_data['token'] = $token;

            DB::commit();

            if($invite_code){
                $parent_user->notify(new DirectUserRegister($user));
            }

            return $this->successWithData($return_data);
        }catch (\Exception $exception){
            DB::rollBack();

            return $this->error(4003,$exception->getMessage());
        }
    }

    //发送用户注册短信验证码
    public function sendRegisterCode(Request $request,User $user)
    {
        if ($vr = $this->verifyField($request->all(),[
            'phone' => 'required|string',
        ])) return $vr;

        if (!$this->isCNPhone($request->phone))
            throw new ApiException('手机号不正确');
        if ($user->getUserByPhone($request->phone))
            throw new ApiException('手机号已被注册');

        $sendResult = sendCodeSMS($request->phone,'verify');
        if ($sendResult === true){
            return $this->success();
        }
        return $this->error(4001,$sendResult);
    }

    //发送短信验证码
    public function sendSmsCode(Request $request,User $user)
    {
        if ($vr = $this->verifyField($request->all(),[
            'phone' => 'required|string',
        ])) return $vr;

        if (!$this->isCNPhone($request->phone))
            throw new ApiException('手机号不正确');
        if (!$user->getUserByPhone($request->phone))
            throw new ApiException('手机号不存在');

        $sendResult = sendCodeSMS($request->phone,'verify');
        if ($sendResult === true){
            return $this->success();
        }
        return $this->error(4001,$sendResult);
    }

    public function login(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'account' => 'required|string',
            'password' => 'required|string'
        ])) return $vr;

        if ($this->isEmail($request->account)){
            $account_credentials = ['email' => $request->account, 'password' => $request->password];
        }else{
            $account_credentials = ['phone' => $request->account, 'password' => $request->password];
        }

        if (!(auth('api')->attempt($account_credentials))) {
            return $this->error(1005,'账号或密码不正确');
        }

        $user = User::query()->where('phone',$request->account)->orWhere('email', $request->account)->first();

        $login_code = User::gen_login_code();
        $token = auth('api')->claims(['login_code' => $login_code])->fromUser($user);

        $user->login_code = $login_code;
        $user->last_login_ip = $request->getClientIp();
        $user->last_login_time = Carbon::now()->toDateTimeString();
        $user->save();

        return $this->successWithData(['token'=>$token,'user_id'=>$user['user_id']],'登录成功');
    }

    public function logout()
    {
        auth('api')->logout();

        return $this->success();
    }

    //更新密码
    public function update_password(Request $request)
    {
        if ($res = $this->verifyField($request->all(),[
            'old_password' => 'required',
            'password' => 'required|min:8|max:16|confirmed:password_confirmation',
            'password_confirmation' => 'required',
        ])) return $res;

        $user = $this->current_user();

        if(!$user->verifyPassword($request->old_password,$user->password)){
            throw new ApiException(trans('frontend.password_error'));
        }

        $user->password = $user->passwordHash($request->password);
        $user->save();

        return $this->success();
    }

    //忘记登录密码
    public function forget_password(Request $request)
    {
        if ($res = $this->verifyField($request->all(),[
            'phone' => 'required',
            'sms_code' => 'required',
            'password' => 'required|min:8|max:16|confirmed:password_confirmation',
            'password_confirmation' => 'required',
        ])) return $res;

        $data = $request->all();

        $user = User::query()->where(['phone'=>$data['phone']])->first();
        if(blank($user)) return $this->error(4001,'用户不存在');

        $checkResult = checkSMSCode($request->phone,$request->sms_code);
        if ($checkResult !== true) return $this->error(4001,$checkResult);

        $user->password = $user->passwordHash($data['password']);
        $user->save();

        return $this->success();
    }

    //设置支付密码
    public function set_paypwd(Request $request)
    {
        if ($res = $this->verifyField($request->all(),[
            'paypwd' => 'required|digits:6|confirmed:paypwd_confirmation',
            'paypwd_confirmation' => 'required|digits:6',
        ])) return $res;

        $user = $this->current_user();

        $user->paypwd = $user->passwordHash($request->paypwd);
        $user->save();

        return $this->success();
    }

    //修改交易密码
    public function update_paypwd(Request $request)
    {
        if ($res = $this->verifyField($request->all(),[
            'phone' => 'required',
            'sms_code' => 'required',
            'paypwd' => 'required|digits:6|confirmed:paypwd_confirmation',
            'paypwd_confirmation' => 'required|digits:6',
        ])) return $res;

        $user = User::query()->where(['phone'=>$request->phone])->first();
        if(blank($user)) return $this->error(4001,'用户不存在');

        $checkResult = checkSMSCode($request->phone,$request->sms_code);
        if ($checkResult !== true) return $this->error(4001,$checkResult);

        $user->paypwd = $user->passwordHash($request->paypwd);
        $user->save();

        return $this->success();
    }

}
