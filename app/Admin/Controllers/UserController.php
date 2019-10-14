<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\User\LockWithdraw;
use App\Admin\Actions\User\Replicate;
use App\Admin\Actions\User\UnlockTeam;
use App\Admin\Actions\User\UnlockWithdraw;
use App\Models\User;
use App\Models\UserGroup;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Admin\Actions\User\ViewTeam;
use App\Admin\Actions\User\LockTeam;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User);

        // 设置初始排序条件
        $grid->model()->orderBy('user_id', 'desc');

        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            // 在这里添加字段过滤器
            $filter->equal('phone', '手机号');
            $filter->in('user_group','用户星级')->multipleSelect(UserGroup::query()->pluck('group_name','group_id')->toArray());

            $filter->where(function ($query) {

                $user = User::query()->where('phone', $this->input)->first();
                $user_ids = User::getSubChildren($user['user_id']);
                $user_ids[] = $user['user_id'];

                $query->whereIn('user_id',$user_ids);

            }, '根据手机号搜索团队');

        });

        $grid->disableCreateButton();
        $grid->disableRowSelector();

        $grid->actions(function ($actions) {

            $actions->disableDelete();

            $actions->row;

            $actions->add(new ViewTeam);
            if($actions->row->status == 'enable'){
                $actions->add(new LockTeam);
                $actions->add(new LockWithdraw);
            }else{
                $actions->add(new UnlockTeam);
                $actions->add(new UnlockWithdraw);
            }
        });

        $grid->column('user_id', __('User id'));
        $grid->column('pid', __('上级用户手机号'))->display(function ($pid){
            $phone = User::query()->where('user_id',$pid)->value('phone');
            return "<a href='".route('users.index',['phone'=>$phone])."' style='color:green'>". $phone ."</a>";
        });
        $grid->column('invite_code', __('Invite code'));
//        $grid->column('real_name', __('Real name'));
//        $grid->column('birthday', __('Birthday'));
        $grid->column('phone', __('Phone'));
        $grid->column('username', __('Username'));
        $grid->column('email', __('Email'));
//        $grid->column('sex', __('Sex'));
        $grid->column('reg_ip', __('Reg ip'));
        $grid->column('last_login_ip', __('Last login ip'));
        $grid->column('last_login_time', __('Last login time'));
        $grid->column('status', __('Status'))->select(User::user_status());
//        $grid->column('deep', __('Deep'));
        $grid->column('user_group', __('User group'))->using(UserGroup::query()->pluck('group_name','group_id')->toArray());
        $grid->column('avatar', __('Avatar'))->gallery(['zooming'=>true,'width'=>50,'height'=>50]);

        $grid->column('teamInfo', '团队信息')->display(function (){
            $user_ids = User::getSubChildren($this->user_id);
            $team_headcount = User::query()->whereIn('user_id',$user_ids)->count();//团队总人数
            $team_star_count = User::query()->whereIn('user_id',$user_ids)->where('user_group','>',1)->count();//团队星级人数
            $today_count = User::query()->whereIn('user_id',$user_ids)->whereDate('created_at',date('Y-m-d'))->count();//今天注册的人数
            $today_star_count = User::query()->whereIn('user_id',$user_ids)->whereHas('user_upgrade_log',function ($query){
                $query->where('target_user_group',2)->whereDate('created_at',date('Y-m-d'));
            })->count();//今天升星人数

            $html = "<span style='color:green'>团队总人数：$team_headcount</span>&nbsp;&nbsp;&nbsp;&nbsp;<span style='color:green'>团队星级人数：$team_star_count</span>";
            $html .= "<br><span style='color:green'>今日注册人数：$today_count</span>&nbsp;&nbsp;&nbsp;&nbsp;<span style='color:green'>今日升星人数：$today_star_count</span>";
            return $html;
        });

        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));

        $show->field('user_id', __('User id'));
        $show->field('pid', __('Pid'));
        $show->field('invite_code', __('Invite code'));
        $show->field('real_name', __('Real name'));
        $show->field('birthday', __('Birthday'));
        $show->field('phone', __('Phone'));
        $show->field('username', __('Username'));
        $show->field('email', __('Email'));
        $show->field('password', __('Password'));
        $show->field('paypwd', __('Paypwd'));
        $show->field('sex', __('Sex'));
        $show->field('reg_ip', __('Reg ip'));
        $show->field('last_login_ip', __('Last login ip'));
        $show->field('last_login_time', __('Last login time'));
        $show->field('status', __('Status'));
        $show->field('deep', __('Deep'));
        $show->field('path', __('Path'));
        $show->field('user_group', __('User group'));
        $show->field('avatar', __('Avatar'));
        $show->field('login_code', __('Login code'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User);

        $form->number('pid', __('Pid'));
        $form->text('invite_code', __('Invite code'));
        $form->text('real_name', __('Real name'));
        $form->text('birthday', __('Birthday'));
        $form->mobile('phone', __('Phone'));
        $form->text('username', __('Username'));
        $form->email('email', __('Email'));
        $form->password('password', __('Password'));
        $form->text('paypwd', __('Paypwd'));
        $form->switch('sex', __('Sex'));
        $form->text('reg_ip', __('Reg ip'));
        $form->text('last_login_ip', __('Last login ip'));
        $form->datetime('last_login_time', __('Last login time'))->default(date('Y-m-d H:i:s'));
        $form->text('status', __('Status'))->default('enable');
        $form->number('deep', __('Deep'));
        $form->textarea('path', __('Path'));
        $form->number('user_group', __('User group'))->default(1);
        $form->image('avatar', __('Avatar'));
        $form->text('login_code', __('Login code'));

        return $form;
    }

    public function viewTeam($user_id,Content $content)
    {
        $content->header('查看团队');

        $deeps = [
            ['deep'=>1,'deep_name'=>'一代'],
            ['deep'=>2,'deep_name'=>'二代'],
            ['deep'=>3,'deep_name'=>'三代'],
            ['deep'=>4,'deep_name'=>'四代'],
            ['deep'=>5,'deep_name'=>'五代'],
            ['deep'=>6,'deep_name'=>'六代'],
            ['deep'=>7,'deep_name'=>'七代'],
            ['deep'=>8,'deep_name'=>'八代'],
            ['deep'=>9,'deep_name'=>'九代'],
        ];

        $user = User::query()->find($user_id);
        $user_ids = User::getSubChildren($user['user_id']);

        $headers = ['用户ID', '上级ID','邀请码', '手机号码','用户名','邮箱','状态','注册时间','用户星级'];
        $fileds = ['user_id', 'pid','invite_code', 'phone','username','email','status','user_group','created_at'];
//        $rows = User::query()->select($headers)->whereIn('user_id',$user_ids)->where('deep',$user['deep']+1)->get()->makeHidden(['user_group_name','set_paypwd'])->toArray();

        $tab = new Tab();
        foreach ($deeps as $deep){
            $rows = User::query()->select($fileds)
                ->whereIn('user_id',$user_ids)
                ->where('deep',$user['deep']+$deep['deep'])
                ->get()
                ->makeHidden(['user_group','set_paypwd'])
                ->map(function ($item,$key) {
                    $phone = $item['phone'];
                    $item['phone'] = "<a href='".route('users.index',['phone'=>$phone])."' style='color:green'>". $phone ."</a>";
                    return $item;
                })
                ->toArray();
//dd($rows);
            $table = new Table($headers, $rows);

            $tab->add($deep['deep_name'], $table);
        }

        $content->body($tab);

        return $content;
    }

}
