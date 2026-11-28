<?php
declare(strict_types=1);

namespace plugins\shop\model;

use think\Model;

/**
 * 商品模型
 * 说明：核心读写仍使用 Db 门面（需要 lock/事务/条件更新）；
 * 模型主要用于扩展关联查询与业务层的可读封装。
 */
class Product extends Model
{
    protected $name = 'shop_product';

    public function skus()
    {
        return $this->hasMany(Sku::class, 'product_id')->order('id', 'asc');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
