<?php
declare(strict_types=1);

namespace plugins\shop\model;

use think\Model;

/**
 * 售后申请模型
 */
class AfterSale extends Model
{
    protected $name = 'shop_after_sale';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
