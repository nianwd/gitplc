<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\UserWallet;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserWalletController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户资产';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserWallet);

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

            $filter->where(function ($query) {

                $user = User::query()->where('phone', $this->input)->first();
                $user_ids = User::getSubChildren($user['user_id']);
                $user_ids[] = $user['user_id'];

                $query->whereIn('user_id',$user_ids);

            }, '根据手机号搜索团队');

        });

        $grid->disableCreateButton();
//        $grid->disableRowSelector();

//        $grid->actions(function ($actions) {
//
//            $actions->disableDelete();
//            $actions->disableEdit();
//
//            // append一个操作
////            $actions->append('<a href=""><i class="fa fa-eye"></i></a>');
//
//            // prepend一个操作
////            $actions->prepend('<a href=""><i class="fa fa-paper-plane"></i></a>');
//        });

        $grid->column('id', __('Id'));
//        $grid->column('user_id', __('User id'));
        $grid->column('user_phone', __('用户手机号'))->display(function (){
            return User::query()->where('user_id',$this->user_id)->value('phone');
        });
        $grid->column('usable_balance', __(UserWallet::rich_types()['usable_balance']))->totalRow();
        $grid->column('withdraw_balance', __(UserWallet::rich_types()['withdraw_balance']))->totalRow();
        $grid->column('money', __(UserWallet::rich_types()['money']))->totalRow();
        $grid->column('lcz_money', __(UserWallet::rich_types()['lcz_money']))->totalRow();
//        $grid->column('wallet_password', __('Wallet password'));
        $grid->column('withdraw_total', __('PLC提现累计'))->totalRow();
        $grid->column('conversion_total', __('理财资产兑换累计'))->totalRow();
        $grid->column('wallet_address', __('Wallet address'));
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
        $show = new Show(UserWallet::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('usable_balance', __('Usable balance'));
        $show->field('withdraw_balance', __('Withdraw balance'));
        $show->field('money', __('Money'));
        $show->field('lcz_money', __('Lcz money'));
        $show->field('wallet_address', __('Wallet address'));
//        $show->field('wallet_password', __('Wallet password'));
        $show->field('withdraw_total', __('PLC提现累计'));
        $show->field('conversion_total', __('理财资产兑换累计'));
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
        $form = new Form(new UserWallet);

        $form->number('user_id', __('User id'));
        $form->decimal('usable_balance', __('Usable balance'))->default(0.00);
        $form->decimal('withdraw_balance', __('Withdraw balance'))->default(0.00);
        $form->decimal('money', __('Money'))->default(0.00);
        $form->decimal('lcz_money', __('Lcz money'))->default(0.00);
        $form->text('wallet_address', __('Wallet address'));
//        $form->text('wallet_password', __('Wallet password'));

        return $form;
    }
}
