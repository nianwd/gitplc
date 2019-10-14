<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ArticleController extends ApiController
{
    public function faq(Request $request)
    {
        $category = 1;

        $builder = Article::query()->where('status',1);

        if( blank($ids = ArticleCategory::getSubIds($category)) ){
            $builder->where('category_id',$category);
        }else{
            $ids[] = $category;
            $builder->whereIn('category_id',$ids);
        }

        $data = $builder->orderBy('view_count','desc')->orderBy('created_at','desc')->get();

        return $this->successWithData($data);
    }

    public function about_us(Request $request)
    {
        $category = 2;

        $builder = Article::query()->where('status',1);

        if( blank($ids = ArticleCategory::getSubIds($category)) ){
            $builder->where('category_id',$category);
        }else{
            $ids[] = $category;
            $builder->whereIn('category_id',$ids);
        }

        $data = $builder->orderBy('view_count','desc')->orderBy('created_at','desc')->get();

        return $this->successWithData($data);
    }

    public function information(Request $request)
    {
        $category = 3;

        $builder = Article::query()->where('status',1);

        if( blank($ids = ArticleCategory::getSubIds($category)) ){
            $builder->where('category_id',$category);
        }else{
            $ids[] = $category;
            $builder->whereIn('category_id',$ids);
        }

        $data = $builder->orderBy('view_count','desc')->orderBy('created_at','desc')->get();

        return $this->successWithData($data);
    }

    public function notice(Request $request)
    {
        $category = 4;

        $builder = Article::query()->where('status',1);

        if( blank($ids = ArticleCategory::getSubIds($category)) ){
            $builder->where('category_id',$category);
        }else{
            $ids[] = $category;
            $builder->whereIn('category_id',$ids);
        }

        $data = $builder->orderBy('view_count','desc')->orderBy('created_at','desc')->get();

        return $this->successWithData($data);
    }

    //最新公告--获取最新一条公告通知
    public function newest_notice(Request $request)
    {
        $category = 4;

        $notice = Article::query()
            ->where('status',1)
            ->where('category_id',$category)
            ->orderBy('created_at','desc')
            ->first();

        return $this->successWithData($notice);
    }

    public function detail(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'id' => 'required|integer',
        ])) return $vr;

        $detail = Article::query()->findOrFail($request->id);

        return $this->successWithData($detail);
    }
}
