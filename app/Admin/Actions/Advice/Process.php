<?php

namespace App\Admin\Actions\Advice;

use App\Models\User;
use App\Notifications\AdviceProcessed;
use Carbon\Carbon;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Process extends RowAction
{
    public $name = '处理反馈';

    public function form()
    {
        $this->textarea('process_note', '处理备注')->rules('required');
    }

    public function handle(Model $model,Request $request)
    {
        // $model ...

        $process_note = $request->get('process_note');

        $model->is_process = 1;
        $model->process_note = $process_note;
        $model->process_time = Carbon::now()->toDateTimeString();
        $res = $model->save();

        if($res){
            $user = User::query()->find($model['user_id']);
            if(!blank($user)) $user->notify(new AdviceProcessed($model));

            return $this->response()->success('处理成功.')->refresh();
        }
        return $this->response()->error('处理失败.')->refresh();
    }

}
