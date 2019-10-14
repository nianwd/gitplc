<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Advice\Process;
use App\Models\Advice;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AdviceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户反馈';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Advice);

        // 设置初始排序条件
        $grid->model()->orderBy('id', 'desc');

        $grid->disableCreateButton();
//        $grid->disableRowSelector();

        $grid->actions(function ($actions) {

            $actions->disableDelete();
            $actions->disableEdit();

            $actions->add(new Process);
        });

        $grid->column('id', __('Id'));
        $grid->column('user_id', __('User id'));
        $grid->column('phone', __('Phone'));
        $grid->column('email', __('Email'));
        $grid->column('realname', __('Realname'));
        $grid->column('contents', __('Contents'));
        $grid->column('is_process', __('Is process'))->using([0=>'未处理',1=>'已处理'])->label([
            0 => 'default',
            1 => 'success',
        ])->filter([0=>'未处理',1=>'已处理']);
        $grid->column('process_note', __('Process note'));
        $grid->column('process_time', __('Process time'));
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
        $show = new Show(Advice::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('phone', __('Phone'));
        $show->field('email', __('Email'));
        $show->field('realname', __('Realname'));
        $show->field('contents', __('Contents'));
        $show->field('is_process', __('Is process'));
        $show->field('process_note', __('Process note'));
        $show->field('process_time', __('Process time'));
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
        $form = new Form(new Advice);

        $form->number('user_id', __('User id'));
        $form->mobile('phone', __('Phone'));
        $form->email('email', __('Email'));
        $form->text('realname', __('Realname'));
        $form->textarea('contents', __('Contents'));
        $form->switch('is_process', __('Is process'));
        $form->text('process_note', __('Process note'));
        $form->datetime('process_time', __('Process time'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
