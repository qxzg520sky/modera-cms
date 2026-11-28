<?php
declare(strict_types=1);

namespace plugins\shop\controller;

use plugins\shop\service\MemberBridge;
use plugins\shop\logic\OrderLogic;
use plugins\shop\service\Shop;
use think\facade\Db;

/**
 * 商城会员前台。登录态复用会员插件 session('member_id')，不另建账号体系。
 */
class Front
{
    protected function render(string $tpl, array $data = []): string
    {
        $data['shopCfg'] = Shop::cfg();
        $data['me'] = MemberBridge::current();
        return \app\service\View::fetch(dirname(__DIR__) . '/view/front/' . $tpl . '.php', $data);
    }

    private function loginUrl(): string
    {
        $back = (string) request()->url(true);
        return '/member/login?redirect=' . urlencode($back);
    }

    private function requireMember()
    {
        $me = MemberBridge::current();
        if (!$me) {
            return redirect($this->loginUrl());
        }
        return null;
    }

    public function asset()
    {
        $file = (string) request()->param('file', '');
        $file = basename($file);
        $path = dirname(__DIR__) . '/asset/' . $file;
        if (!is_file($path) || !preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|webp)$/i', $file)) {
            return response('Not Found', 404);
        }
        $mime = [
            'css' => 'text/css', 'js' => 'application/javascript', 'png' => 'image/png',
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
            'svg' => 'image/svg+xml', 'webp' => 'image/webp',
        ][strtolower(pathinfo($file, PATHINFO_EXTENSION))] ?? 'application/octet-stream';
        return response((string) file_get_contents($path))->header([
            'Content-Type' => $mime . '; charset=utf-8',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    /* ==================== 商品 ==================== */

    public function index()
    {
        $cfg = Shop::cfg();
        $kw = trim((string) request()->get('keyword', ''));
        $cat = (int) request()->get('category_id', 0);
        $sort = (string) request()->get('sort', 'default');
        $page = max(1, (int) request()->get('page', 1));
        $size = max(6, (int) $cfg['page_size']);

        $q = Db::name(Shop::T_PRODUCT)->alias('p')->where('p.status', 1);
        if ($kw !== '') $q->whereLike('p.title|p.subtitle', '%' . $kw . '%');
        if ($cat > 0) $q->where('p.category_id', $cat);
        if ($sort === 'price_asc') $q->order('p.price asc,p.id desc');
        elseif ($sort === 'price_desc') $q->order('p.price desc,p.id desc');
        elseif ($sort === 'sales') $q->order('p.sales desc,p.id desc');
        else $q->order('p.sort desc,p.id desc');

        $total = (int) (clone $q)->count();
        $pages = max(1, (int) ceil($total / $size));
        $page = min($page, $pages);
        $list = $q->field('p.*')->page($page, $size)->select()->toArray();

        return $this->render('index', [
            'pageTitle' => $cfg['shop_name'],
            'list' => $list,
            'cats' => Db::name(Shop::T_CATEGORY)->where('status', 1)->order('sort desc,id asc')->select()->toArray(),
            'f' => ['keyword' => $kw, 'category_id' => $cat, 'sort' => $sort],
            'pager' => ['total' => $total, 'page' => $page, 'pages' => $pages],
        ]);
    }

    public function product()
    {
        $id = (int) request()->param('id', 0);
        $p = Db::name(Shop::T_PRODUCT)->where('id', $id)->where('status', 1)->find();
        if (!$p) return response('商品不存在或已下架', 404);
        $skus = Db::name(Shop::T_SKU)->where('product_id', $id)->where('status', 1)->order('id asc')->select()->toArray();
        $images = json_decode((string) $p['images'], true) ?: [];
        return $this->render('product', [
            'pageTitle' => $p['title'],
            'p' => $p,
            'skus' => $skus,
            'images' => $images,
            'catName' => Db::name(Shop::T_CATEGORY)->where('id', $p['category_id'])->value('name') ?: '未分类',
            'related' => Db::name(Shop::T_PRODUCT)->where('status', 1)->where('category_id', $p['category_id'])->where('id', '<>', $id)->order('sort desc,id desc')->limit(4)->select()->toArray(),
        ]);
    }

    /* ==================== 购物车 ==================== */

    public function cart()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        $items = OrderLogic::cartItems((int) $me['id'], false);
        $goods = 0.0;
        $selected = 0.0;
        foreach ($items as $it) {
            $goods += (float) $it['price'] * (int) $it['qty'];
            if ((int) $it['selected'] === 1) $selected += (float) $it['price'] * (int) $it['qty'];
        }
        return $this->render('cart', [
            'pageTitle' => '购物车',
            'items' => $items,
            'goods' => round($goods, 2),
            'selected' => round($selected, 2),
        ]);
    }

    public function cartAdd()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        $skuId = (int) request()->post('sku_id', 0);
        $qty = max(1, min(99, (int) request()->post('qty', 1)));
        $sku = Db::name(Shop::T_SKU)->where('id', $skuId)->where('status', 1)->find();
        if (!$sku) return $this->back('SKU 不存在或已下架');
        $p = Db::name(Shop::T_PRODUCT)->where('id', $sku['product_id'])->where('status', 1)->find();
        if (!$p) return $this->back('商品已下架');
        if ((int) $sku['stock'] < $qty) return $this->back('库存不足，当前库存 ' . (int) $sku['stock']);
        $cart = Db::name(Shop::T_CART)->where('member_id', $me['id'])->where('sku_id', $skuId)->find();
        if ($cart) {
            $new = min((int) $sku['stock'], (int) $cart['qty'] + $qty);
            Db::name(Shop::T_CART)->where('id', $cart['id'])->update(['qty' => $new, 'selected' => 1, 'updated_at' => time()]);
        } else {
            Db::name(Shop::T_CART)->insert([
                'member_id' => $me['id'], 'product_id' => $sku['product_id'], 'sku_id' => $skuId,
                'qty' => $qty, 'selected' => 1, 'created_at' => time(), 'updated_at' => time(),
            ]);
        }
        return redirect('/shop/cart');
    }

    private function back(string $msg): \think\response\Redirect
    {
        session('shop_flash', $msg);
        return redirect((string) (request()->header('referer') ?: '/shop'));
    }

    public function cartUpdate()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        $id = (int) request()->post('id', 0);
        if (request()->post('remove', '') === '1') {
            Db::name(Shop::T_CART)->where('id', $id)->where('member_id', $me['id'])->delete();
            return redirect('/shop/cart');
        }
        $qty = (int) request()->post('qty', 1);
        $sel = (int) request()->post('selected', 1) === 1 ? 1 : 0;
        $cart = Db::name(Shop::T_CART)->where('id', $id)->where('member_id', $me['id'])->find();
        if (!$cart) return redirect('/shop/cart');
        if ($qty <= 0) {
            Db::name(Shop::T_CART)->where('id', $id)->delete();
            return redirect('/shop/cart');
        }
        $stock = (int) Db::name(Shop::T_SKU)->where('id', $cart['sku_id'])->value('stock');
        Db::name(Shop::T_CART)->where('id', $id)->update([
            'qty' => min($qty, max(1, $stock)), 'selected' => $sel, 'updated_at' => time(),
        ]);
        return redirect('/shop/cart');
    }

    public function cartRemove()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        $id = (int) request()->post('id', 0);
        Db::name(Shop::T_CART)->where('id', $id)->where('member_id', $me['id'])->delete();
        return redirect('/shop/cart');
    }

    /* ==================== 结算 / 订单 ==================== */

    public function checkout()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        try {
            $preview = OrderLogic::preview((int) $me['id'], [
                'address_id' => (int) request()->get('address_id', 0),
                'coupon_code' => trim((string) request()->get('coupon_code', '')),
                'use_points' => (int) request()->get('use_points', 0) === 1,
            ]);
            $err = '';
        } catch (\Throwable $e) {
            $preview = [];
            $err = $e->getMessage();
        }
        return $this->render('checkout', [
            'pageTitle' => '确认订单',
            'preview' => $preview,
            'err' => $err,
            'addressId' => (int) request()->get('address_id', 0),
            'couponCode' => trim((string) request()->get('coupon_code', '')),
            'usePoints' => (int) request()->get('use_points', 0) === 1,
            'bizNo' => 'B' . date('YmdHis') . mt_rand(1000, 9999),
        ]);
    }

    public function orderCreate()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        try {
            $res = OrderLogic::create((int) $me['id'], [
                'biz_no' => trim((string) request()->post('biz_no', '')),
                'cart_ids' => array_map('intval', (array) request()->post('cart_ids', [])),
                'address_id' => (int) request()->post('address_id', 0),
                'coupon_code' => trim((string) request()->post('coupon_code', '')),
                'use_points' => (int) request()->post('use_points', 0) === 1,
                'buyer_remark' => (string) request()->post('buyer_remark', ''),
            ]);
            $no = (string) $res['order']['order_no'];
            if (!empty($res['reused'])) session('shop_flash', '该提交已生成过订单，已为你跳转到原订单');
            return redirect('/shop/order/' . $no);
        } catch (\Throwable $e) {
            session('shop_flash', '下单失败：' . $e->getMessage());
            return redirect('/shop/checkout');
        }
    }

    public function orderDetail()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        $no = (string) request()->param('no', '');
        $order = Db::name(Shop::T_ORDER)->where('order_no', $no)->where('member_id', $me['id'])->find();
        if (!$order) return response('订单不存在', 404);
        return $this->render('order_detail', [
            'pageTitle' => '订单详情',
            'order' => $order,
            'items' => Db::name(Shop::T_ITEM)->where('order_id', $order['id'])->select()->toArray(),
            'after' => Db::name(Shop::T_AFTER)->where('order_id', $order['id'])->order('id desc')->find(),
            'logs' => Db::name(Shop::T_LOG)->where('order_id', $order['id'])->order('id asc')->select()->toArray(),
        ]);
    }

    public function orderPay()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        $no = (string) request()->post('order_no', '');
        try {
            $res = OrderLogic::pay($no, (int) $me['id'], 'balance');
            if (($res['spend_state'] ?? 0) === 3) {
                session('shop_flash', '支付成功。累计消费同步结果不确定，后台已记录待对账，不会自动重复调用。');
            } elseif (($res['spend_state'] ?? 0) === 2) {
                session('shop_flash', '支付成功，累计消费已同步，会员等级已自动重算。');
            } else {
                session('shop_flash', '订单已是已付款状态');
            }
        } catch (\Throwable $e) {
            session('shop_flash', '支付失败：' . $e->getMessage());
        }
        return redirect('/shop/order/' . $no);
    }

    public function orders()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        $status = (string) request()->get('status', '');
        $q = Db::name(Shop::T_ORDER)->where('member_id', $me['id']);
        if ($status !== '') $q->where('status', $status);
        $total = (int) (clone $q)->count();
        $page = max(1, (int) request()->get('page', 1));
        $pages = max(1, (int) ceil($total / 10));
        $list = $q->order('id desc')->page(min($page, $pages), 10)->select()->toArray();
        return $this->render('orders', [
            'pageTitle' => '我的商城订单',
            'list' => $list,
            'f' => ['status' => $status],
            'pager' => ['total' => $total, 'page' => min($page, $pages), 'pages' => $pages],
        ]);
    }

    /* ==================== 售后 ==================== */

    public function afterSaleForm()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        $no = (string) request()->param('no', '');
        $order = Db::name(Shop::T_ORDER)->where('order_no', $no)->where('member_id', $me['id'])->find();
        if (!$order) return response('订单不存在', 404);
        $existing = Db::name(Shop::T_AFTER)->where('order_id', $order['id'])->whereIn('status', ['pending', 'approved'])->find();
        return $this->render('after_sale', [
            'pageTitle' => '申请售后',
            'order' => $order,
            'existing' => $existing,
        ]);
    }

    public function afterSaleApply()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        $no = (string) request()->post('order_no', '');
        $reason = mb_substr(trim((string) request()->post('reason', '')), 0, 255);
        $type = (string) request()->post('type', 'refund');
        $order = Db::name(Shop::T_ORDER)->where('order_no', $no)->where('member_id', $me['id'])->find();
        if (!$order) return $this->back('订单不存在');
        if (!in_array($order['status'], [OrderLogic::STATUS_PAID, OrderLogic::STATUS_SHIPPED, OrderLogic::STATUS_COMPLETED], true)) {
            return $this->back('当前订单状态不能申请售后');
        }
        if ($reason === '') return $this->back('请填写售后原因');
        if (Db::name(Shop::T_AFTER)->where('order_id', $order['id'])->whereIn('status', ['pending', 'approved'])->find()) {
            return $this->back('该订单已有处理中的售后申请');
        }
        Db::name(Shop::T_AFTER)->insert([
            'after_sale_no' => Shop::afterSaleNo(),
            'order_id' => $order['id'],
            'order_no' => $no,
            'member_id' => $me['id'],
            'type' => in_array($type, ['refund', 'return'], true) ? $type : 'refund',
            'reason' => $reason,
            'amount' => round((float) $order['payable_amount'], 2),
            'status' => 'pending',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        Shop::log((int) $order['id'], $no, 'after_sale_apply', '会员申请售后：' . $reason, 'member', (int) $me['id']);
        session('shop_flash', '售后申请已提交，等待管理员审核');
        return redirect('/shop/after-sales');
    }

    public function afterSales()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        $list = Db::name(Shop::T_AFTER)->where('member_id', $me['id'])->order('id desc')->limit(50)->select()->toArray();
        return $this->render('after_sales', [
            'pageTitle' => '我的售后',
            'list' => $list,
        ]);
    }

    /* ==================== 领券 ==================== */

    public function couponReceive()
    {
        if ($r = $this->requireMember()) return $r;
        $me = MemberBridge::current();
        $code = strtoupper(trim((string) request()->post('code', '')));
        $coupon = Db::name(Shop::T_COUPON)->where('code', $code)->where('status', 1)->find();
        $now = time();
        if (!$coupon) return $this->back('优惠券不存在');
        if ((int) $coupon['start_at'] > 0 && $now < (int) $coupon['start_at']) return $this->back('优惠券未到领取时间');
        if ((int) $coupon['end_at'] > 0 && $now > (int) $coupon['end_at']) return $this->back('优惠券已过期');
        if ((int) $coupon['total'] > 0 && (int) $coupon['received'] >= (int) $coupon['total']) return $this->back('优惠券已领完');
        try {
            Db::name(Shop::T_COUPON_M)->insert([
                'coupon_id' => $coupon['id'], 'member_id' => $me['id'], 'status' => 0, 'received_at' => time(),
            ]);
            Db::name(Shop::T_COUPON)->where('id', $coupon['id'])->update(['received' => Db::raw('`received`+1')]);
            session('shop_flash', '优惠券已领取');
        } catch (\Throwable $e) {
            session('shop_flash', '你已经领取过这张券');
        }
        return redirect((string) (request()->header('referer') ?: '/shop'));
    }
}

