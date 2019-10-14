<?php

namespace App\Admin\Controllers;

use App\Models\UserWallet;
use App\Models\UserWalletLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserWalletLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '资产流水';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserWalletLog);

        $grid->disableActions();

        // 设置初始排序条件
        $grid->model()->orderBy('id', 'desc');

        $grid->filter(function($filter) {

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->where(function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->where('phone', '=', $this->input);
                });
            }, '手机号');

            $filter->in('rich_type','资产类型')->multipleSelect(UserWallet::rich_types());
            $filter->in('log_type','流水类型')->multipleSelect(UserWalletLog::log_types());
        });

        $grid->disableCreateButton();
        $grid->disableRowSelector();

        $grid->column('id', __('Id'));
        $grid->column('user_id', __('User id'));
        $grid->column('rich_type', __('Rich type'))->using(UserWallet::rich_types());
        $grid->column('amount', __('Amount'));
        $grid->column('log_type', __('Log type'))->using(UserWalletLog::log_types());
        $grid->column('log_note', __('Log note'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(UserWalletLog::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('rich_type', __('Rich type'));
        $show->field('amount', __('Amount'));
        $show->field('log_type', __('Log type'));
        $show->field('log_note', __('Log note'));
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
        $form = new Form(new UserWalletLog);

        $form->number('user_id', __('User id'));
        $form->text('rich_type', __('Rich type'));
        $form->decimal('amount', __('Amount'))->default(0.00);
        $form->switch('log_type', __('Log type'))->default(1);
        $form->text('log_note', __('Log note'));

        return $form;
    }
}
