<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    //文章分类表

    protected $table = 'article_category';

    protected $primaryKey = 'id';

    protected $guarded = [];

    //获取传入的分类的无限子类ids
    public static function getSubIds($category_id){
        $categorys = self::all();

        if(blank($categorys)){
            return [];
        }else{
            $categorys = $categorys->toArray();
        }

        $subIds = get_tree_child($categorys,$category_id);

        return $subIds;
    }

}
