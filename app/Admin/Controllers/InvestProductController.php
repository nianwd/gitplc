<?php

namespace App\Admin\Controllers;

use App\Models\InvestProduct;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class InvestProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '理财产品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new InvestProduct);

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('min_amount', __('Min amount'));
        $grid->column('max_amount', __('Max amount'));
        $grid->column('day', __('Day'));
        $grid->column('income', __('Income'));
        $grid->column('order', __('Order'));
        $grid->column('status', __('Status'));
        $grid->column('can_buy', __('Can buy'));
        $grid->column('is_return', __('Is return'));
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
        $show = new Show(InvestProduct::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('min_amount', __('Min amount'));
        $show->field('max_amount', __('Max amount'));
        $show->field('day', __('Day'));
        $show->field('income', __('Income'));
        $show->field('order', __('Order'));
        $show->field('status', __('Status'));
        $show->field('can_buy', __('Can buy'));
        $show->field('is_return', __('Is return'));
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
        $form = new Form(new InvestProduct);

        $form->text('name', __('Name'));
        $form->decimal('min_amount', __('Min amount'))->default(0.00);
        $form->decimal('max_amount', __('Max amount'));
        $form->number('day', __('Day'));
        $form->decimal('income', __('Income'))->default(0.00);
        $form->number('order', __('Order'));
        $form->switch('status', __('Status'))->default(1);
        $form->switch('can_buy', __('Can buy'))->default(1);
        $form->switch('is_return', __('Is return'))->default(1);

        return $form;
    }
}
