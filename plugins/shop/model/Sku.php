<?php
declare(strict_types=1);

namespace plugins\shop\model;

use think\Model;

/**
 * SKU 模型
 * 库存扣减必须用 Db::name('shop_sku')->lock(true) + 条件更新，不能依赖模型 save()。
 */
class Sku extends Model
{
    protected $name = 'shop_sku';

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
