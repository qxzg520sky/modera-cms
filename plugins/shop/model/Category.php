<?php
declare(strict_types=1);

namespace plugins\shop\model;

use think\Model;

/**
 * 商品分类模型
 */
class Category extends Model
{
    protected $name = 'shop_category';

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
