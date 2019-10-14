<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Banner;
use App\Models\UserGroup;
use App\Services\UpgradeService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IndexController extends ApiController
{
    //

    public function getBanners(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'position' => 'required|integer',
            'limit' => 'integer',
        ])) return $vr;

        $limit = $request->input('limit',5);

        $banners = Banner::query()->where('position',$request->position)->limit($limit)->latest()->get();

        return $this->successWithData($banners);
    }

    public function upgradePlan(Request $request)
    {
        $data = UserGroup::query()->where('can_upgrade',1)->get();

        return $this->successWithData($data);
    }

    public function getUpgradeData(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'group_id' => 'required|integer',
        ])) return $vr;

        $user = $this->current_user();
        $user_group = $user['user_group'];

        $group_id = $request->group_id;
        if($group_id != ($user_group+1)) return $this->error(4001,'操作错误');
        $data = UserGroup::query()->findOrFail($group_id);

        $plc_price = getCoinCnyPrice();//获取PLC当前币价和人民币的汇率
        $key = 'upgrade-plc_price:'.$user['user_id'];
        Cache::put($key, $plc_price,config('app.plc_price_ttl'));//缓存针对当前用户的PLC价格

        $data['plc_price'] = $plc_price;

        return $this->successWithData($data);
    }

    public function upgrade(Request $request,UpgradeService $upgradeService)
    {
        if ($vr = $this->verifyField($request->all(),[
            'group_id' => 'required|integer',
            'paypwd' => 'required',
        ])) return $vr;

        $user = $this->current_user();
        $group_id = $request->group_id;

        $key = 'upgrade-plc_price:'.$user['user_id'];
        if (!Cache::has($key)){
            return $this->error(4001,'超时，请重新购买');
        }

        $plc_price = Cache::get($key);

        //检测支付密码是否正确
        $check_res = $user->verifyPassword($request->paypwd,$user->paypwd);
        if(!$check_res) {
            return $this->error(4001,'密码错误');
        }

        return $upgradeService->upgrade($user,$group_id,$plc_price);
    }

}
