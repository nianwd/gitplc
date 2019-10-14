<?php

namespace App\Admin\Controllers;

use App\Models\ArticleCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ArticleCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '文章分类';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ArticleCategory);

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
//        $grid->column('description', __('Description'));
//        $grid->column('post_count', __('Post count'));
        $grid->column('pid', __('Pid'));
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
        $show = new Show(ArticleCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
//        $show->field('description', __('Description'));
//        $show->field('post_count', __('Post count'));
        $show->field('pid', __('Pid'));
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
        $form = new Form(new ArticleCategory);

        $form->text('name', __('Name'));
//        $form->text('description', __('Description'));
//        $form->number('post_count', __('Post count'));
        $form->number('pid', __('Pid'));

        return $form;
    }
}
