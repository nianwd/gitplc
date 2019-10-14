<?php

namespace App\Admin\Controllers;

use App\Models\UserGroup;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserGroupController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户星级';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserGroup);

        $grid->column('group_id', __('ID'));
        $grid->column('group_name', __('Group name'));
        $grid->column('up_invite_headcount', __('Up invite headcount'));
        $grid->column('up_invite_num', __('Up invite num'));
        $grid->column('up_invite_group', __('Up invite group'));
        $grid->column('up_plc', __('Up plc'));
        $grid->column('superior_award', __('Superior award'));
        $grid->column('ultimate_award', __('Ultimate award'));
        $grid->column('group_award', __('Group award'));
        $grid->column('team_invest_award', __('Team invest award'));
        $grid->column('status', __('Status'));
//        $grid->column('can_upgrade', __('Can upgrade'));
        $grid->column('title', __('Title'));
        $grid->column('content', __('Content'));
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
        $show = new Show(UserGroup::findOrFail($id));

        $show->field('group_id', __('ID'));
        $show->field('group_name', __('Group name'));
        $show->field('up_invite_headcount', __('Up invite headcount'));
        $show->field('up_invite_num', __('Up invite num'));
        $show->field('up_invite_group', __('Up invite group'));
        $show->field('up_plc', __('Up plc'));
        $show->field('superior_award', __('Superior award'));
        $show->field('ultimate_award', __('Ultimate award'));
        $show->field('group_award', __('Group award'));
        $show->field('team_invest_award', __('Team invest award'));
        $show->field('status', __('Status'));
//        $show->field('can_upgrade', __('Can upgrade'));
        $show->field('title', __('Title'));
        $show->field('content', __('Content'));
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
        $form = new Form(new UserGroup);

        $form->text('group_name', __('Group name'));
        $form->number('up_invite_headcount', __('Up invite headcount'));
        $form->number('up_invite_num', __('Up invite num'));
        $form->number('up_invite_group', __('Up invite group'));
        $form->decimal('up_plc', __('Up plc'))->default(0.00);
        $form->decimal('superior_award', __('Superior award'))->default(0.00);
        $form->decimal('ultimate_award', __('Ultimate award'))->default(0.00);
        $form->decimal('group_award', __('Group award'))->default(0.00);
        $form->decimal('team_invest_award', __('Team invest award'))->default(0.00);
        $form->switch('status', __('Status'))->default(1);
//        $form->switch('can_upgrade', __('Can upgrade'))->default(1);
        $form->text('title', __('Title'));
        $form->textarea('content', __('Content'));

        return $form;
    }
}
