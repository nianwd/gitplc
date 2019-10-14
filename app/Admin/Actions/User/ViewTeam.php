<?php

namespace App\Admin\Actions\User;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class ViewTeam extends RowAction
{
    public $name = '查看团队';

    /**
     * @return string
     */
    public function href()
    {
        $key = $this->getKey();

        return route('user.viewTeam',$key);
    }

    public function handle(Model $model)
    {
        // $model ...

        return $this->response()->success('Success message.')->refresh();
    }

}
