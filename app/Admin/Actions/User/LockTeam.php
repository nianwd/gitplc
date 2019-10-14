<?php

namespace App\Admin\Actions\User;

use App\Models\User;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class LockTeam extends RowAction
{
    public $name = '锁定团队';

    public function form()
    {
    }

    public function handle(Model $model)
    {
        // $model ...
        $user_ids = User::getSubChildren($model['user_id']);
        $user_ids[] = $model['user_id'];

        $res = User::query()->whereIn('user_id',$user_ids)->update(['status'=>'lock']);

        if($res){
            return $this->response()->success('锁定成功.')->refresh();
        }
        return $this->response()->error('锁定失败.')->refresh();
    }

}
