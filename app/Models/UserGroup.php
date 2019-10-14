<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    //用户级别

    protected $primaryKey = 'group_id';

    /*表名称*/
    protected $table = 'user_group';

    protected $guarded = [];

    protected $appends = ['upgrade_status'];

    public function getUpgradeStatusAttribute(){
        //升星计划 判断前端按钮显示 1按钮高亮可点击状态 2按钮灰暗字体显示已升级 3按钮灰暗字体显示升级 4表示用户处于当前星级不可点击

        $user = currenctUser();
//        dd($user);
        if(!$user){
            return 1;
        }

        if($this->group_id < $user['user_group']){
            return 2;
        }elseif ($this->group_id == $user['user_group']){
            return 4;
        }elseif ($this->group_id == ($user['user_group'] + 1)){
            return 1;
        }else{
            return 3;
        }
    }

}
