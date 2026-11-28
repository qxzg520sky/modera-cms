<?php
declare(strict_types=1);

namespace plugins\shop\model;

use think\Model;

/**
 * 订单模型
 * 订单状态流转必须走 OrderLogic（内部使用行锁与事务）。
 */
class Order extends Model
{
    protected $name = 'shop_order';

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function logs()
    {
        return $this->hasMany(OrderLog::class, 'order_id')->order('id', 'desc');
    }

    public function afterSales()
    {
        return $this->hasMany(AfterSale::class, 'order_id');
    }
}
