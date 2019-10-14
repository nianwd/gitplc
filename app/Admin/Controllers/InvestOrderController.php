<?php

namespace App\Admin\Controllers;

use App\Models\InvestOrder;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class InvestOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '理财订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new InvestOrder);

        $grid->disableCreateButton();
        $grid->disableRowSelector();

        $grid->disableActions();

        // 设置初始排序条件
        $grid->model()->orderBy('order_id', 'desc');

        $grid->filter(function($filter) {

            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->where(function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->where('phone', '=', $this->input);
                });
            }, '手机号');
        });

        $grid->column('order_id', __('Order id'));
        $grid->column('order_sn', __('Order sn'));
        $grid->column('user_id', __('User id'));
        $grid->column('product_id', __('Product id'));
        $grid->column('product_income', __('Product income'));
        $grid->column('product_name', __('Product name'));
        $grid->column('order_money', __('Order money'));
        $grid->column('plc_price', __('Plc price'));
        $grid->column('order_plc', __('Order plc'));
        $grid->column('status', __('Status'));
        $grid->column('return_max_day', __('Return max day'));
        $grid->column('return_day_money', __('Return day money'));
        $grid->column('return_anticipated_money', __('Return anticipated money'));
        $grid->column('return_count_money', __('Return count money'));
        $grid->column('return_count_day', __('Return count day'));
        $grid->column('set_last_time', __('Set last time'));
        $grid->column('is_set', __('Is set'));
        $grid->column('set_time', __('Set time'));
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
        $show = new Show(InvestOrder::findOrFail($id));

        $show->field('order_id', __('Order id'));
        $show->field('order_sn', __('Order sn'));
        $show->field('user_id', __('User id'));
        $show->field('product_id', __('Product id'));
        $show->field('product_income', __('Product income'));
        $show->field('product_name', __('Product name'));
        $show->field('order_money', __('Order money'));
        $show->field('plc_price', __('Plc price'));
        $show->field('order_plc', __('Order plc'));
        $show->field('status', __('Status'));
        $show->field('return_max_day', __('Return max day'));
        $show->field('return_day_money', __('Return day money'));
        $show->field('return_anticipated_money', __('Return anticipated money'));
        $show->field('return_count_money', __('Return count money'));
        $show->field('return_count_day', __('Return count day'));
        $show->field('set_last_time', __('Set last time'));
        $show->field('is_set', __('Is set'));
        $show->field('set_time', __('Set time'));
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
        $form = new Form(new InvestOrder);

        $form->text('order_sn', __('Order sn'));
        $form->number('user_id', __('User id'));
        $form->number('product_id', __('Product id'));
        $form->decimal('product_income', __('Product income'));
        $form->text('product_name', __('Product name'));
        $form->decimal('order_money', __('Order money'))->default(0.00);
        $form->decimal('plc_price', __('Plc price'))->default(0.00);
        $form->decimal('order_plc', __('Order plc'))->default(0.00);
        $form->switch('status', __('Status'))->default(1);
        $form->number('return_max_day', __('Return max day'));
        $form->decimal('return_day_money', __('Return day money'))->default(0.00);
        $form->decimal('return_anticipated_money', __('Return anticipated money'))->default(0.00);
        $form->decimal('return_count_money', __('Return count money'))->default(0.00);
        $form->number('return_count_day', __('Return count day'));
        $form->datetime('set_last_time', __('Set last time'))->default(date('Y-m-d H:i:s'));
        $form->switch('is_set', __('Is set'));
        $form->datetime('set_time', __('Set time'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
