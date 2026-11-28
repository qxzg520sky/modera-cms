<?php
declare(strict_types=1);

namespace plugins\shop\model;

use think\Model;

/**
 * 订单明细模型，保存商品快照，退款不依赖商品表当前数据。
 */
class OrderItem extends Model
{
    protected $name = 'shop_order_item';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
