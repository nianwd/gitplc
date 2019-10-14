<?php

namespace App\Admin\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ArticleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '文章列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Article);

        // 设置初始排序条件
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
//        $grid->column('body', __('Body'));
        $grid->column('category_id', __('Category id'))->using(ArticleCategory::query()->pluck('name','id')->toArray());
//        $grid->column('view_count', __('View count'));
        $grid->column('cover', __('Cover'))->gallery(['zooming'=>true,'width'=>80,'height'=>80]);
        $grid->column('excerpt', __('Excerpt'));
        $grid->column('status', __('Status'))->using([0=>'不显示',1=>'显示']);
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
//        $grid->column('deleted_at', __('Deleted at'));

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
        $show = new Show(Article::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('body', __('Body'));
        $show->field('category_id', __('Category id'));
//        $show->field('view_count', __('View count'));
        $show->field('cover', __('Cover'));
        $show->field('excerpt', __('Excerpt'));
        $show->field('status', __('Status'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
//        $show->field('deleted_at', __('Deleted at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Article);

        $form->select('category_id', '文章分类')->options(ArticleCategory::query()->pluck('name','id')->toArray());
        $form->text('title', __('Title'));
//        $form->textarea('body', __('Body'));
        $form->UEditor('body','内容')->options(['initialFrameHeight' => 300]);
//        $form->number('view_count', __('浏览量'))->default(0);
        $form->image('cover', __('Cover'));
        $form->textarea('excerpt', __('Excerpt'));
        $form->switch('status', __('Status'))->default(1);

        $form->saving(function (Form $form) {

            if(blank($form->excerpt)) $form->excerpt = mb_substr(strip_tags($form->body),0,30);

        });

        return $form;
    }
}
