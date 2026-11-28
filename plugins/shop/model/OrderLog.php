<?php
declare(strict_types=1);

namespace plugins\shop\model;

use think\Model;

/**
 * 订单日志模型
 */
class OrderLog extends Model
{
    protected $name = 'shop_order_log';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
