<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    //文章表
    use SoftDeletes;

    protected $table = 'articles';

    protected $primaryKey = 'id';

    protected $guarded = [];

    //配置软删除属性
    protected $dates = ['deleted_at'];

    protected $appends = ['category_name'];

    public function getCategoryNameAttribute()
    {
        return ArticleCategory::query()->where('id',$this->category_id)->value('name');
    }

    public function getCoverAttribute($value)
    {
        return blank($value) ? '' : env('IMG_URL') . $value;
    }

}
