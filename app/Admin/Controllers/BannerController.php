<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BannerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '轮播图';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Banner);

        // 设置初始排序条件
        $grid->model()->orderBy('banner_id', 'desc');

        $grid->column('banner_id', __('Banner id'));
        $grid->column('position', __('Position'))->using(Banner::banner_position());
//        $grid->column('img', __('Img'));
        $grid->img()->lightbox(['class' => 'rounded']);
//        $grid->column('tourl', __('Tourl'));
        $grid->column('status', __('Status'))->using([0=>'不显示',1=>'显示']);
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
        $show = new Show(Banner::findOrFail($id));

        $show->field('banner_id', __('Banner id'));
        $show->field('position', __('Position'))->using(Banner::banner_position());
//        $show->field('img', __('Img'));
        $show->field('img', __('Img'))->image();
//        $show->field('tourl', __('Tourl'));
        $show->field('status', __('Status'));
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
        $form = new Form(new Banner);

        $form->select('position', __('轮播图位置'))->options(Banner::banner_position());
        $form->image('img', __('Img'));
//        $form->text('tourl', __('Tourl'));
        $form->switch('status', __('Status'))->default(1);

        return $form;
    }
}
