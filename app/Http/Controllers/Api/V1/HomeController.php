<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends ApiController
{
    //个人中心

    public function getUserInfo()
    {
        $user = $this->current_user();
//        $data = User::query()->with('user_wallet')->find($user['user_id']);

        return $this->successWithData($user);
    }

    public function updateUserInfo(Request $request)
    {
        if ($res = $this->verifyField($request->all(),[
            'avatar' => '',
            'username' => 'string',
            'sex' => 'integer|in:0,1,2',
        ])) return $res;

        $params = $request->all();
        if(isset($params['avatar'])){
            $params['avatar'] = $this->uploadSingleImg($params['avatar'],'avatar');
        }

        $user = $this->current_user();

        $res = $user->update($params);
        if(!$res){
            return $this->error();
        }

        return $this->success();
    }

    //获取用户钱包奖金和理财收益统计
    public function getIncomeStatistics(Request $request)
    {
        $user = $this->current_user();

        $data['plc_bonus'] = $user->user_wallet_log()->where('log_type',3)->sum('amount');//PLC奖金

        //收益 = 理财收益 + 团队收益 - 团队溢出
        $data['invest_earnings'] = $user->user_wallet_log()->where(function ($query){
            $query->where('log_type',7)->orWhere('log_type',8);
        })->sum('amount') - abs($user->user_wallet_log()->where(function ($query){
            $query->where('log_type',9);
        })->sum('amount'));

        return $this->successWithData($data);
    }

    public function myTeamInfo(Request $request)
    {
        $user = $this->current_user();

        $user_ids = User::getSubChildren($user['user_id']);

        $builder = User::query()->whereIn('user_id',$user_ids);

        $data['user'] = $user;
        $data['team_headcount'] = $builder->count();//团队总人数
        $data['team_star_count'] = $builder->where('user_group','>',1)->count();//团队星级人数

        return $this->successWithData($data);
    }

    public function getTeamUser(Request $request)
    {
        if($res = $this->verifyField($request->all(),[
            'deep' => 'required|integer',
        ])) return $res;

        $user = $this->current_user();
        $deep = $request->deep;
        $deep = $user['deep'] + $deep;

        $user_ids = User::getSubChildren($user['user_id']);

        $builder = User::query()->withCount('children')->whereIn('user_id',$user_ids);

        if($deep){
            $builder->where('deep',$deep);
        }

        $data = $builder->paginate();

        return $this->successWithData($data);
    }

    //团队订单：从团队产生的收益 以及漏单的流水记录
    public function teamOrder(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'log_type' => 'integer',
            'start_time' => 'date',
            'end_time' => 'date',
        ])) return $vr;

        $user = $this->current_user();

        $log_types = [3,8];

        $builder = $user->user_wallet_log();

        if($log_type = $request->input('log_type')){
            $builder->where('log_type',$log_type);
        }else{
            $builder->whereIn('log_type',$log_types);
        }

        if($start_time = $request->input('start_time') && $end_time = $request->input('end_time')){
            $builder->whereBetween('created_at', [$start_time,$end_time]);
        }

        $data = $builder->latest()->paginate();

        return $this->successWithData($data);
    }

    //邀请有礼
    public function invitation()
    {
        $user = $this->current_user();

        $url = 'http://plc.yunzupu.online';
        $invite_url = $url . "/invite_reg/index.html?invite_code=" . $user['invite_code'];

        $data['invite_code'] = $user['invite_code'];
        $data['invite_url'] = $invite_url;

        return $this->successWithData($data);
    }

}
