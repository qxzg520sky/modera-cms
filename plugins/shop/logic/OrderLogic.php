<?php
declare(strict_types=1);

namespace plugins\shop\logic;

use plugins\shop\service\MemberBridge;
use plugins\shop\service\Shop;
use think\facade\Db;

/**
 * 商城订单业务层。
 *
 * 核心边界：
 * - 商品、库存、购物车、订单、优惠券全部是 shop_* 表。
 * - 会员资料、等级折扣、余额、积分、地址、累计消费只通过 MemberBridge 调用会员插件。
 * - addSpend 没有会员侧幂等表，所以商城用订单 spend_state + spend_before 做本地闸门，
 *   同一个订单只允许发起一次；超时后标记 3=不确定，必须人工核对后再处理。
 */
class OrderLogic
{
    public const STATUS_PENDING = 'pending_payment';
    public const STATUS_PAYING  = 'paying';
    public const STATUS_PAID    = 'paid';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CLOSED  = 'closed';
    public const STATUS_REFUNDED = 'refunded';

    /* ==================== 购物车 / 结算预览 ==================== */

    public static function cartItems(int $memberId, bool $onlySelected = true, array $cartIds = []): array
    {
        $q = Db::name(Shop::T_CART)->alias('c')
            ->join(Shop::table(Shop::T_SKU) . ' s', 's.id=c.sku_id')
            ->join(Shop::table(Shop::T_PRODUCT) . ' p', 'p.id=c.product_id')
            ->where('c.member_id', $memberId)
            ->where('s.status', 1)
            ->where('p.status', 1);
        if ($cartIds) {
            $q->whereIn('c.id', array_map('intval', $cartIds));
        } elseif ($onlySelected) {
            $q->where('c.selected', 1);
        }
        return $q->field('c.id cart_id,c.qty,c.selected,s.id sku_id,s.product_id,s.sku_code,s.spec_json,s.price,s.stock,s.warn_stock,p.title,p.cover,p.subtitle')
            ->order('c.id desc')->select()->toArray();
    }

    public static function priceItems(array $items): array
    {
        $out = [];
        $total = 0.0;
        foreach ($items as $it) {
            $qty = max(1, (int) $it['qty']);
            $price = round((float) $it['price'], 2);
            $subtotal = round($price * $qty, 2);
            $total += $subtotal;
            $out[] = [
                'cart_id' => (int) ($it['cart_id'] ?? 0),
                'product_id' => (int) $it['product_id'],
                'sku_id' => (int) $it['sku_id'],
                'sku_code' => (string) ($it['sku_code'] ?? ''),
                'product_title' => (string) $it['title'],
                'sku_spec' => (string) ($it['spec_json'] ?? '{}'),
                'spec_text' => Shop::specText((string) ($it['spec_json'] ?? '{}')),
                'cover' => (string) ($it['cover'] ?? ''),
                'price' => $price,
                'qty' => $qty,
                'subtotal' => $subtotal,
                'stock' => (int) ($it['stock'] ?? 0),
            ];
        }
        return ['items' => $out, 'goods_amount' => round($total, 2)];
    }

    /**
     * 结算预览，不锁库存、不写订单。
     */
    public static function preview(int $memberId, array $in = []): array
    {
        $member = MemberBridge::get($memberId);
        if (!$member || (int) ($member['status'] ?? 0) !== 1) {
            throw new \RuntimeException('会员不存在或已停用');
        }
        $items = self::cartItems($memberId, true);
        $priced = self::priceItems($items);
        if (!$priced['items']) {
            throw new \RuntimeException('购物车没有选中的商品');
        }
        $address = self::resolveAddress($memberId, (int) ($in['address_id'] ?? 0), []);
        $calc = self::calc($member, $priced['goods_amount'], (string) ($in['coupon_code'] ?? ''), !empty($in['use_points']));
        return array_merge($calc, [
            'items' => $priced['items'],
            'address' => $address,
            'member' => $member,
            'addresses' => MemberBridge::addresses($memberId),
        ]);
    }

    /* ==================== 下单 ==================== */

    public static function create(int $memberId, array $in): array
    {
        $bizNo = trim((string) ($in['biz_no'] ?? ''));
        if ($bizNo === '') $bizNo = 'B' . Shop::orderNo();
        $exists = Db::name(Shop::T_ORDER)->where('biz_no', $bizNo)->find();
        if ($exists) {
            return ['order' => $exists, 'reused' => true];
        }

        $member = MemberBridge::get($memberId);
        if (!$member || (int) ($member['status'] ?? 0) !== 1) {
            throw new \RuntimeException('会员不存在或已停用');
        }

        $cartIds = array_map('intval', (array) ($in['cart_ids'] ?? []));
        $items = self::cartItems($memberId, true, $cartIds);
        $priced = self::priceItems($items);
        if (!$priced['items']) throw new \RuntimeException('购物车没有可下单商品');

        $address = self::resolveAddress($memberId, (int) ($in['address_id'] ?? 0), [
            'receiver' => (string) ($in['receiver'] ?? ''),
            'mobile' => (string) ($in['mobile'] ?? ''),
            'province' => (string) ($in['province'] ?? ''),
            'city' => (string) ($in['city'] ?? ''),
            'district' => (string) ($in['district'] ?? ''),
            'address' => (string) ($in['address'] ?? ''),
        ]);
        $calc = self::calc($member, $priced['goods_amount'], (string) ($in['coupon_code'] ?? ''), !empty($in['use_points']));

        Db::startTrans();
        try {
            if (Db::name(Shop::T_ORDER)->where('biz_no', $bizNo)->lock(true)->find()) {
                Db::commit();
                return ['order' => Db::name(Shop::T_ORDER)->where('biz_no', $bizNo)->find(), 'reused' => true];
            }

            foreach ($priced['items'] as $it) {
                $sku = Db::name(Shop::T_SKU)->where('id', $it['sku_id'])->lock(true)->find();
                if (!$sku || (int) $sku['status'] !== 1) throw new \RuntimeException('SKU 已下架：' . $it['sku_code']);
                if ((int) $sku['stock'] < $it['qty']) throw new \RuntimeException('库存不足：' . $it['product_title'] . ' / ' . $it['spec_text']);
                Db::name(Shop::T_SKU)->where('id', $it['sku_id'])->where('stock', '>=', $it['qty'])->update([
                    'stock' => Db::raw('stock-' . $it['qty']),
                    'updated_at' => time(),
                ]);
                Db::name(Shop::T_PRODUCT)->where('id', $it['product_id'])->where('stock', '>=', $it['qty'])->update([
                    'stock' => Db::raw('stock-' . $it['qty']),
                    'updated_at' => time(),
                ]);
            }

            $orderNo = 'S' . Shop::orderNo();
            $orderId = (int) Db::name(Shop::T_ORDER)->insertGetId([
                'order_no' => $orderNo,
                'biz_no' => $bizNo,
                'member_id' => $memberId,
                'member_name' => (string) ($member['real_name'] ?: $member['username']),
                'staff_id' => (int) ($member['staff_id'] ?? 0),
                'status' => self::STATUS_PENDING,
                'goods_amount' => $calc['goods_amount'],
                'member_discount' => $calc['member_discount'],
                'coupon_id' => (int) ($calc['coupon']['id'] ?? 0),
                'coupon_code' => (string) ($calc['coupon']['code'] ?? ''),
                'coupon_amount' => $calc['coupon_amount'],
                'points_used' => $calc['points_used'],
                'points_amount' => $calc['points_amount'],
                'payable_amount' => $calc['payable_amount'],
                'discount_rate' => $calc['discount_rate'],
                'receiver' => $address['receiver'],
                'mobile' => $address['mobile'],
                'province' => $address['province'],
                'city' => $address['city'],
                'district' => $address['district'],
                'address' => $address['address'],
                'address_json' => json_encode($address, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'buyer_remark' => mb_substr((string) ($in['buyer_remark'] ?? ''), 0, 255),
                'spend_state' => 0,
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            foreach ($priced['items'] as $it) {
                Db::name(Shop::T_ITEM)->insert([
                    'order_id' => $orderId,
                    'order_no' => $orderNo,
                    'product_id' => $it['product_id'],
                    'sku_id' => $it['sku_id'],
                    'product_title' => $it['product_title'],
                    'sku_spec' => $it['sku_spec'],
                    'price' => $it['price'],
                    'qty' => $it['qty'],
                    'subtotal' => $it['subtotal'],
                    'created_at' => time(),
                ]);
            }

            if (!empty($calc['coupon']['cm_id'])) {
                Db::name(Shop::T_COUPON_M)->where('id', (int) $calc['coupon']['cm_id'])->update([
                    'status' => 1, 'order_no' => $orderNo, 'used_at' => time(),
                ]);
                Db::name(Shop::T_COUPON)->where('id', (int) $calc['coupon']['id'])->update(['used' => Db::raw('`used`+1')]);
            }

            if ($cartIds) {
                Db::name(Shop::T_CART)->where('member_id', $memberId)->whereIn('id', $cartIds)->delete();
            } else {
                Db::name(Shop::T_CART)->where('member_id', $memberId)->where('selected', 1)->delete();
            }

            Shop::log($orderId, $orderNo, 'create', '创建订单，应付 ￥' . $calc['payable_amount'], 'member', $memberId);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
        return ['order' => Db::name(Shop::T_ORDER)->where('id', $orderId)->find(), 'reused' => false];
    }

    /* ==================== 支付 ==================== */

    public static function pay(string $orderNo, int $memberId, string $payType = 'balance'): array
    {
        $order = Db::name(Shop::T_ORDER)->where('order_no', $orderNo)->where('member_id', $memberId)->find();
        if (!$order) throw new \RuntimeException('订单不存在');
        if ($order['status'] === self::STATUS_PAID || $order['status'] === self::STATUS_SHIPPED || $order['status'] === self::STATUS_COMPLETED) {
            return ['order' => $order, 'reused' => true, 'spend_state' => (int) $order['spend_state']];
        }
        if ($order['status'] !== self::STATUS_PENDING) throw new \RuntimeException('当前订单状态不能支付：' . Shop::orderStatusText((string) $order['status']));

        $cfg = Shop::cfg();
        $member = MemberBridge::get($memberId);
        $payable = round((float) $order['payable_amount'], 2);
        $balanceUse = 0.0;
        if ($payable > 0) {
            if ((string) $cfg['balance_pay'] !== '1') throw new \RuntimeException('商城未开启余额支付');
            if ($payType !== 'balance') throw new \RuntimeException('当前仅支持余额支付');
            $balanceUse = $payable;
            if (round((float) $member['balance'], 2) < $balanceUse) {
                throw new \RuntimeException('会员余额不足，当前余额 ￥' . number_format((float) $member['balance'], 2));
            }
        }

        Db::startTrans();
        try {
            $locked = Db::name(Shop::T_ORDER)->where('id', $order['id'])->lock(true)->find();
            if ($locked['status'] !== self::STATUS_PENDING) {
                Db::commit();
                return ['order' => $locked, 'reused' => true, 'spend_state' => (int) $locked['spend_state']];
            }
            Db::name(Shop::T_ORDER)->where('id', $order['id'])->update([
                'status' => self::STATUS_PAYING,
                'pay_type' => $payable > 0 ? 'balance' : 'free',
                'balance_amount' => $balanceUse,
                'updated_at' => time(),
            ]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        $pointsDeducted = false;
        $balanceDeducted = false;
        try {
            if ((int) $order['points_used'] > 0) {
                MemberBridge::pointsChange($memberId, -(int) $order['points_used'], 'consume', $orderNo . '-points', '商城积分抵扣');
                $pointsDeducted = true;
            }
            if ($balanceUse > 0) {
                MemberBridge::balanceChange($memberId, -$balanceUse, 'consume', $orderNo . '-balance', '商城余额支付');
                $balanceDeducted = true;
            }

            $fresh = MemberBridge::get($memberId);
            Db::name(Shop::T_ORDER)->where('id', $order['id'])->where('status', self::STATUS_PAYING)->update([
                'status' => self::STATUS_PAID,
                'pay_status' => 1,
                'paid_at' => time(),
                'spend_before' => round((float) ($fresh['total_spent'] ?? 0), 2),
                'updated_at' => time(),
            ]);

            foreach (Db::name(Shop::T_ITEM)->where('order_id', $order['id'])->select()->toArray() as $it) {
                Db::name(Shop::T_PRODUCT)->where('id', $it['product_id'])->update([
                    'sales' => Db::raw('sales+' . (int) $it['qty']), 'updated_at' => time(),
                ]);
            }
            Shop::log((int) $order['id'], $orderNo, 'pay', '支付成功，余额 ￥' . $balanceUse . '，积分 ' . (int) $order['points_used'], 'member', $memberId);
        } catch (\Throwable $e) {
            // 跨插件无法共享事务：已经扣掉的资产按原业务单号回滚
            try {
                if ($balanceDeducted) MemberBridge::balanceChange($memberId, $balanceUse, 'refund', $orderNo . '-pay-fail-balance', '支付失败退回余额');
                if ($pointsDeducted) MemberBridge::pointsChange($memberId, (int) $order['points_used'], 'refund', $orderNo . '-pay-fail-points', '支付失败退回积分');
            } catch (\Throwable $inner) {
                Shop::log((int) $order['id'], $orderNo, 'pay_rollback_error', $inner->getMessage(), 'system');
            }
            Db::name(Shop::T_ORDER)->where('id', $order['id'])->update([
                'status' => self::STATUS_PENDING, 'balance_amount' => 0, 'updated_at' => time(),
            ]);
            throw $e;
        }

        $spend = self::syncSpend($orderNo);
        return ['order' => Db::name(Shop::T_ORDER)->where('order_no', $orderNo)->find(), 'reused' => false, 'spend_state' => $spend['state']];
    }

    /**
     * 调用会员 addSpend。
     * state：0 未调用 1 调用中 2 成功 3 结果不确定（禁止自动重试）
     */
    public static function syncSpend(string $orderNo, bool $force = false): array
    {
        $order = Db::name(Shop::T_ORDER)->where('order_no', $orderNo)->find();
        if (!$order) throw new \RuntimeException('订单不存在');
        if ((int) $order['spend_state'] === 2) return ['state' => 2, 'msg' => '已同步'];
        if ((int) $order['spend_state'] === 3 && !$force) {
            return ['state' => 3, 'msg' => '上次调用结果不确定，禁止自动重试'];
        }
        if (round((float) $order['payable_amount'], 2) <= 0) {
            Db::name(Shop::T_ORDER)->where('id', $order['id'])->update(['spend_state' => 2, 'spend_synced_at' => time()]);
            Shop::log((int) $order['id'], $orderNo, 'spend_skip', '0 元订单不计累计消费');
            return ['state' => 2, 'msg' => '0 元订单已标记'];
        }

        $where = $force
            ? Db::name(Shop::T_ORDER)->where('id', $order['id'])->whereIn('spend_state', [0, 3])
            : Db::name(Shop::T_ORDER)->where('id', $order['id'])->where('spend_state', 0);
        $affected = $where->update(['spend_state' => 1, 'updated_at' => time()]);
        if (!$affected) {
            $now = Db::name(Shop::T_ORDER)->where('order_no', $orderNo)->find();
            return ['state' => (int) ($now['spend_state'] ?? 1), 'msg' => '未取得累计消费调用权，已跳过'];
        }

        try {
            $ret = MemberBridge::addSpend((int) $order['member_id'], round((float) $order['payable_amount'], 2), $orderNo);
            Db::name(Shop::T_ORDER)->where('id', $order['id'])->update(['spend_state' => 2, 'spend_synced_at' => time()]);
            Shop::log((int) $order['id'], $orderNo, 'spend_ok', '累计消费 +￥' . $order['payable_amount'] . '，等级：' . (string) ($ret['level'] ?? ''), 'system');
            return ['state' => 2, 'data' => $ret, 'msg' => '累计消费已同步'];
        } catch (\Throwable $e) {
            Db::name(Shop::T_ORDER)->where('id', $order['id'])->update(['spend_state' => 3, 'updated_at' => time()]);
            Shop::log((int) $order['id'], $orderNo, 'spend_uncertain', $e->getMessage(), 'system');
            return ['state' => 3, 'msg' => '累计消费结果不确定：' . $e->getMessage()];
        }
    }

    /* ==================== 关闭 / 退款 ==================== */

    public static function close(int $orderId, string $operatorType = 'admin', int $operatorId = 0): void
    {
        Db::startTrans();
        try {
            $order = Db::name(Shop::T_ORDER)->where('id', $orderId)->lock(true)->find();
            if (!$order) throw new \RuntimeException('订单不存在');
            if (!in_array($order['status'], [self::STATUS_PENDING], true)) {
                throw new \RuntimeException('只有待付款订单可以关闭');
            }
            self::restoreStock($order);
            self::restoreCoupon($order);
            Db::name(Shop::T_ORDER)->where('id', $orderId)->update([
                'status' => self::STATUS_CLOSED, 'closed_at' => time(), 'updated_at' => time(),
            ]);
            Shop::log($orderId, (string) $order['order_no'], 'close', '关闭订单并回滚库存', $operatorType, $operatorId);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 全额退款：回滚库存、退回余额/积分。
     * 注意：会员插件没有“减少累计消费”的接口，退款不会降低 total_spent，等级也不会降。
     */
    public static function refund(int $orderId, string $reason = '', int $afterSaleId = 0, string $operatorType = 'admin', int $operatorId = 0): array
    {
        $order = Db::name(Shop::T_ORDER)->where('id', $orderId)->find();
        if (!$order) throw new \RuntimeException('订单不存在');
        if ($order['status'] === self::STATUS_REFUNDED) return ['order' => $order, 'reused' => true];
        if (!in_array($order['status'], [self::STATUS_PAID, self::STATUS_SHIPPED, self::STATUS_COMPLETED], true)) {
            throw new \RuntimeException('当前订单不能退款');
        }

        Db::startTrans();
        try {
            $locked = Db::name(Shop::T_ORDER)->where('id', $orderId)->lock(true)->find();
            if ($locked['status'] === self::STATUS_REFUNDED) {
                Db::commit();
                return ['order' => $locked, 'reused' => true];
            }
            if ((int) $locked['stock_returned'] !== 1) {
                self::restoreStock($locked);
                Db::name(Shop::T_ORDER)->where('id', $orderId)->update(['stock_returned' => 1]);
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        // 会员资产退回放在商城事务外；会员流水 biz_no 唯一，重复调用会被拒绝，这里按已处理容错
        if ((int) $locked['points_used'] > 0) {
            self::safeAsset('points', (int) $locked['member_id'], (int) $locked['points_used'], (string) $locked['order_no'] . '-refund-points');
        }
        if (round((float) $locked['balance_amount'], 2) > 0) {
            self::safeAsset('balance', (int) $locked['member_id'], round((float) $locked['balance_amount'], 2), (string) $locked['order_no'] . '-refund-balance');
        }

        Db::name(Shop::T_ORDER)->where('id', $orderId)->update([
            'status' => self::STATUS_REFUNDED,
            'updated_at' => time(),
            'admin_remark' => mb_substr($reason, 0, 255),
        ]);
        if ($afterSaleId > 0) {
            Db::name(Shop::T_AFTER)->where('id', $afterSaleId)->update([
                'status' => 'refunded', 'refunded_at' => time(), 'updated_at' => time(),
            ]);
        }
        Shop::log($orderId, (string) $locked['order_no'], 'refund', '全额退款：' . $reason . '；累计消费不回退', $operatorType, $operatorId);

        return ['order' => Db::name(Shop::T_ORDER)->where('id', $orderId)->find(), 'reused' => false];
    }

    private static function safeAsset(string $type, int $memberId, $amount, string $bizNo): void
    {
        try {
            if ($type === 'points') {
                MemberBridge::pointsChange($memberId, (int) $amount, 'refund', $bizNo, '商城退款退回积分');
            } else {
                MemberBridge::balanceChange($memberId, (float) $amount, 'refund', $bizNo, '商城退款退回余额');
            }
        } catch (\Throwable $e) {
            // 会员流水 biz_no 唯一，重复退款时会报“业务单号已处理”，视为已处理
            if (str_contains($e->getMessage(), '已处理')) return;
            throw $e;
        }
    }

    private static function restoreStock(array $order): void
    {
        if ((int) $order['stock_returned'] === 1) return;
        $items = Db::name(Shop::T_ITEM)->where('order_id', $order['id'])->select()->toArray();
        foreach ($items as $it) {
            Db::name(Shop::T_SKU)->where('id', $it['sku_id'])->update([
                'stock' => Db::raw('stock+' . (int) $it['qty']), 'updated_at' => time(),
            ]);
            Db::name(Shop::T_PRODUCT)->where('id', $it['product_id'])->update([
                'stock' => Db::raw('stock+' . (int) $it['qty']), 'updated_at' => time(),
            ]);
        }
        Db::name(Shop::T_ORDER)->where('id', $order['id'])->update(['stock_returned' => 1]);
    }

    private static function restoreCoupon(array $order): void
    {
        if ((int) $order['coupon_id'] <= 0) return;
        $cm = Db::name(Shop::T_COUPON_M)->where('order_no', $order['order_no'])->find();
        if ($cm) {
            Db::name(Shop::T_COUPON_M)->where('id', $cm['id'])->update(['status' => 0, 'order_no' => '', 'used_at' => 0]);
            Db::name(Shop::T_COUPON)->where('id', $order['coupon_id'])->where('used', '>', 0)->update(['used' => Db::raw('`used`-1')]);
        }
    }

    /* ==================== 计算 / 地址 / 优惠券 ==================== */

    private static function calc(array $member, float $goodsAmount, string $couponCode, bool $usePoints): array
    {
        $cfg = Shop::cfg();
        $rate = round((float) ($member['discount'] ?? 1), 2);
        if ($rate <= 0 || $rate > 1) $rate = 1.0;
        $base = round($goodsAmount, 2);
        $stack = (string) $cfg['discount_stack'] === '1';
        $memberDiscount = $stack ? round($base * (1 - $rate), 2) : 0.0;
        $afterMember = round($base - $memberDiscount, 2);

        $coupon = [];
        $couponAmount = 0.0;
        if ($couponCode !== '') {
            $coupon = self::couponFor((int) $member['id'], $couponCode, $base);
            $couponBase = $stack ? $afterMember : $base;
            if ($coupon['type'] === 'percent') {
                $couponAmount = round($couponBase * (1 - (float) $coupon['value']), 2);
            } else {
                $couponAmount = round((float) $coupon['value'], 2);
            }
            $couponAmount = min($couponAmount, $couponBase);
        }
        if (!$stack && $coupon) {
            // 不叠加：用了优惠券就不再享受会员折扣
            $memberDiscount = 0.0;
            $afterMember = $base;
            if ($coupon['type'] === 'percent') $couponAmount = round($base * (1 - (float) $coupon['value']), 2);
            else $couponAmount = min((float) $coupon['value'], $base);
        }

        $beforePoints = round(max(0, $base - $memberDiscount - $couponAmount), 2);
        $maxPercent = max(0, min(100, (float) $cfg['points_max_percent']));
        $ratePoints = max(1, (float) $cfg['points_rate']);
        $maxPointsValue = round($beforePoints * $maxPercent / 100, 2);
        $maxPoints = (int) floor($maxPointsValue * $ratePoints);
        $memberPoints = (int) ($member['points'] ?? 0);
        $pointsUsed = 0;
        if ($usePoints && (string) $cfg['points_deduction'] === '1') {
            $pointsUsed = max(0, min($memberPoints, $maxPoints));
        }
        $pointsAmount = round($pointsUsed / $ratePoints, 2);
        $payable = round(max(0, $beforePoints - $pointsAmount), 2);

        return [
            'goods_amount' => $base,
            'discount_rate' => $rate,
            'member_discount' => $memberDiscount,
            'coupon' => $coupon,
            'coupon_amount' => $couponAmount,
            'points_used' => $pointsUsed,
            'points_amount' => $pointsAmount,
            'points_max' => $maxPoints,
            'before_points' => $beforePoints,
            'payable_amount' => $payable,
            'member' => $member,
        ];
    }

    private static function couponFor(int $memberId, string $code, float $base): array
    {
        $now = time();
        $row = Db::name(Shop::T_COUPON)->alias('c')
            ->join(Shop::table(Shop::T_COUPON_M) . ' m', 'm.coupon_id=c.id')
            ->where('c.code', $code)
            ->where('c.status', 1)
            ->where('m.member_id', $memberId)
            ->where('m.status', 0)
            ->where(function ($q) use ($now) { $q->where('c.start_at', '<=', $now)->whereOr('c.start_at', 0); })
            ->where(function ($q) use ($now) { $q->where('c.end_at', '>=', $now)->whereOr('c.end_at', 0); })
            ->field('c.*,m.id cm_id')
            ->find();
        if (!$row) throw new \RuntimeException('优惠券不存在、已使用或未发放给当前会员');
        if ($base < (float) $row['min_amount']) throw new \RuntimeException('订单金额未达到优惠券使用门槛');
        if ($row['type'] === 'percent' && ((float) $row['value'] <= 0 || (float) $row['value'] >= 1)) {
            throw new \RuntimeException('优惠券折扣率配置错误');
        }
        return $row;
    }

    private static function resolveAddress(int $memberId, int $addressId, array $manual): array
    {
        if ($addressId > 0) {
            foreach (MemberBridge::addresses($memberId) as $a) {
                if ((int) $a['id'] === $addressId) return self::normalizeAddress($a);
            }
            throw new \RuntimeException('收货地址不存在');
        }
        if (($manual['receiver'] ?? '') !== '' && ($manual['mobile'] ?? '') !== '' && (($manual['address'] ?? '') !== '' || ($manual['detail'] ?? '') !== '')) {
            return self::normalizeAddress($manual);
        }
        // 未选地址时自动取默认地址
        foreach (MemberBridge::addresses($memberId) as $a) {
            if ((int) ($a['is_default'] ?? 0) === 1) return self::normalizeAddress($a);
        }
        throw new \RuntimeException('请选择收货地址');
    }

    private static function normalizeAddress(array $a): array
    {
        $detail = (string) ($a['detail'] ?? ($a['address'] ?? ''));
        return [
            'id' => (int) ($a['id'] ?? 0),
            'receiver' => (string) ($a['receiver'] ?? ''),
            'mobile' => (string) ($a['mobile'] ?? ''),
            'province' => (string) ($a['province'] ?? ''),
            'city' => (string) ($a['city'] ?? ''),
            'district' => (string) ($a['district'] ?? ''),
            'detail' => $detail,
            'address' => $detail,
            'full_address' => trim((string) ($a['province'] ?? '') . (string) ($a['city'] ?? '') . (string) ($a['district'] ?? '') . $detail),
        ];
    }
}
