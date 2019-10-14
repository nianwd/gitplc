<?php

namespace App\Admin\Actions\User;

use App\Models\User;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class UnlockWithdraw extends RowAction
{
    public $name = '提现解锁';

    public function form()
    {
    }

    public function handle(Model $model)
    {
        // $model ...
        $user_ids = User::getSubChildren($model['user_id']);
        $user_ids[] = $model['user_id'];

        $res = User::query()->whereIn('user_id',$user_ids)->update(['status'=>'enable']);

        if($res){
            return $this->response()->success('解锁成功.')->refresh();
        }
        return $this->response()->error('解锁失败.')->refresh();
    }

}
