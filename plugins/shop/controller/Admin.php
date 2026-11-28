<?php
declare(strict_types=1);

namespace plugins\shop\controller;

use plugins\shop\service\Excel;
use plugins\shop\service\MemberBridge;
use plugins\shop\logic\OrderLogic;
use plugins\shop\service\Shop;
use think\facade\Db;

/**
 * 商城后台（仅管理员）。
 * 业务员订单在 Staff 控制器；会员订单在 Front 控制器。
 */
class Admin extends \app\controller\admin\Base
{
    protected function render(string $tpl, array $data = []): string
    {
        $flash = session('shop_flash');
        if ($flash !== null && $flash !== '') session('shop_flash', null);
        $err = session('shop_error');
        if ($err !== null && $err !== '') session('shop_error', null);
        $data['admin']  = $this->admin;
        $data['flash']  = (string) $flash;
        $data['error']  = (string) $err;
        $data['active'] = $data['active'] ?? '';
        return \app\service\View::fetch(dirname(__DIR__) . '/view/admin/' . $tpl . '.php', $data);
    }

    private function active(string $url): string
    {
        return 'plugin-' . md5($url);
    }

    private function ok(string $msg, string $to): \think\response\Redirect
    {
        session('shop_flash', $msg);
        return redirect($to);
    }

    private function fail(string $msg, string $to): \think\response\Redirect
    {
        session('shop_error', $msg);
        return redirect($to);
    }

    private function page(int $total, int $page, int $size): array
    {
        $pages = max(1, (int) ceil($total / $size));
        return ['total' => $total, 'page' => min(max(1, $page), $pages), 'pages' => $pages, 'size' => $size];
    }

    /* ==================== 总览 ==================== */

    public function dashboard()
    {
        return $this->render('dashboard', [
            'active' => $this->active('/admin/plugin/shop'),
            'pageTitle' => '商城总览',
            'stats' => Shop::stats(),
            'orders' => Db::name(Shop::T_ORDER)->order('id desc')->limit(8)->select()->toArray(),
            'low' => Shop::lowStock(6),
            'memberOk' => MemberBridge::enabled(),
            'cfg' => Shop::cfg(),
        ]);
    }

    /* ==================== 商品 ==================== */

    public function products()
    {
        $kw = trim((string) $this->request->get('keyword', ''));
        $cat = (int) $this->request->get('category_id', 0);
        $status = (string) $this->request->get('status', '');
        $page = max(1, (int) $this->request->get('page', 1));
        $size = 15;
        $q = Db::name(Shop::T_PRODUCT)->alias('p')->join(Shop::table(Shop::T_CATEGORY) . ' c', 'c.id=p.category_id', 'left');
        if ($kw !== '') $q->whereLike('p.title', '%' . $kw . '%');
        if ($cat > 0) $q->where('p.category_id', $cat);
        if ($status !== '') $q->where('p.status', (int) $status);
        $total = (int) (clone $q)->count();
        $pager = $this->page($total, $page, $size);
        $list = $q->field('p.*,c.name category_name')->order('p.sort desc,p.id desc')
            ->page($pager['page'], $size)->select()->toArray();
        return $this->render('products', [
            'active' => $this->active('/admin/plugin/shop/products'),
            'pageTitle' => '商品管理',
            'list' => $list,
            'pager' => $pager,
            'cats' => Db::name(Shop::T_CATEGORY)->order('sort desc,id asc')->select()->toArray(),
            'f' => ['keyword' => $kw, 'category_id' => $cat, 'status' => $status],
        ]);
    }

    public function productEdit()
    {
        $id = (int) $this->request->param('id', 0);
        $row = $id > 0 ? Db::name(Shop::T_PRODUCT)->where('id', $id)->find() : [];
        $skus = $id > 0 ? Db::name(Shop::T_SKU)->where('product_id', $id)->order('id asc')->select()->toArray() : [];
        return $this->render('product_edit', [
            'active' => $this->active('/admin/plugin/shop/products'),
            'pageTitle' => $id > 0 ? '编辑商品' : '新增商品',
            'row' => $row,
            'skus' => $skus,
            'cats' => Db::name(Shop::T_CATEGORY)->where('status', 1)->order('sort desc,id asc')->select()->toArray(),
        ]);
    }

    public function productSave()
    {
        $id = (int) $this->request->post('id', 0);
        $data = [
            'category_id' => (int) $this->request->post('category_id', 0),
            'title' => trim((string) $this->request->post('title', '')),
            'subtitle' => trim((string) $this->request->post('subtitle', '')),
            'cover' => trim((string) $this->request->post('cover', '')),
            'images' => json_encode(array_values(array_filter(array_map('trim', explode("\n", (string) $this->request->post('images', ''))))), JSON_UNESCAPED_UNICODE),
            'detail' => (string) $this->request->post('detail', ''),
            'price' => round((float) $this->request->post('price', 0), 2),
            'market_price' => round((float) $this->request->post('market_price', 0), 2),
            'stock' => max(0, (int) $this->request->post('stock', 0)),
            'status' => (int) $this->request->post('status', 1) === 1 ? 1 : 0,
            'is_hot' => (int) $this->request->post('is_hot', 0) === 1 ? 1 : 0,
            'sort' => (int) $this->request->post('sort', 0),
            'updated_at' => time(),
        ];
        if ($data['title'] === '') return $this->fail('商品标题不能为空', '/admin/plugin/shop/products');
        if ($data['price'] <= 0) return $this->fail('商品价格必须大于 0', '/admin/plugin/shop/products');

        if ($id > 0) {
            Db::name(Shop::T_PRODUCT)->where('id', $id)->update($data);
        } else {
            $data['created_at'] = time();
            $data['sales'] = 0;
            $id = (int) Db::name(Shop::T_PRODUCT)->insertGetId($data);
        }

        $skus = [];
        $codes = (array) $this->request->post('sku_code', []);
        foreach ($codes as $i => $code) {
            $skus[] = [
                'id' => (int) (($this->request->post('sku_id', [])[$i] ?? 0)),
                'sku_code' => (string) $code,
                'spec' => (string) (($this->request->post('sku_spec', [])[$i] ?? '')),
                'price' => (string) (($this->request->post('sku_price', [])[$i] ?? '')),
                'market_price' => (string) (($this->request->post('sku_market_price', [])[$i] ?? '')),
                'stock' => (string) (($this->request->post('sku_stock', [])[$i] ?? '')),
                'warn_stock' => (string) (($this->request->post('sku_warn', [])[$i] ?? '')),
            ];
        }
        $keep = [];
        foreach ($skus as $s) {
            $sid = (int) ($s['id'] ?? 0);
            $code = trim((string) ($s['sku_code'] ?? ''));
            if ($code === '') continue;
            $spec = [];
            foreach (preg_split('/[,，;；\n]/u', (string) ($s['spec'] ?? '')) as $pair) {
                $pair = trim($pair);
                if ($pair === '') continue;
                $kv = preg_split('/[:：]/u', $pair, 2);
                if (count($kv) === 2 && trim($kv[0]) !== '') $spec[trim($kv[0])] = trim($kv[1]);
            }
            $row = [
                'product_id' => $id,
                'sku_code' => $code,
                'spec_json' => json_encode($spec, JSON_UNESCAPED_UNICODE),
                'price' => round((float) ($s['price'] ?? $data['price']), 2),
                'market_price' => round((float) ($s['market_price'] ?? $data['market_price']), 2),
                'stock' => max(0, (int) ($s['stock'] ?? 0)),
                'warn_stock' => max(0, (int) ($s['warn_stock'] ?? Shop::cfg()['warn_stock'])),
                'status' => 1,
                'updated_at' => time(),
            ];
            if ($sid > 0) {
                Db::name(Shop::T_SKU)->where('id', $sid)->where('product_id', $id)->update($row);
                $keep[] = $sid;
            } else {
                $row['created_at'] = time();
                $keep[] = (int) Db::name(Shop::T_SKU)->insertGetId($row);
            }
        }
        if ($keep) Db::name(Shop::T_SKU)->where('product_id', $id)->whereNotIn('id', $keep)->update(['status' => 0]);
        if (!$keep) {
            // 未填 SKU 时用商品价库存生成默认 SKU，保证商城可下单
            $exists = Db::name(Shop::T_SKU)->where('product_id', $id)->count();
            if ($exists == 0) {
                Db::name(Shop::T_SKU)->insert([
                    'product_id' => $id, 'sku_code' => 'P' . $id . '-DEFAULT',
                    'spec_json' => '{}', 'price' => $data['price'], 'market_price' => $data['market_price'],
                    'stock' => $data['stock'], 'warn_stock' => (int) Shop::cfg()['warn_stock'],
                    'status' => 1, 'created_at' => time(), 'updated_at' => time(),
                ]);
            }
        }
        return $this->ok('商品已保存', '/admin/plugin/shop/products');
    }

    public function productStatus()
    {
        $id = (int) $this->request->post('id', 0);
        $status = (int) $this->request->post('status', 0) === 1 ? 1 : 0;
        Db::name(Shop::T_PRODUCT)->where('id', $id)->update(['status' => $status, 'updated_at' => time()]);
        return $this->ok($status ? '商品已上架' : '商品已下架', '/admin/plugin/shop/products');
    }

    public function productDelete()
    {
        $id = (int) $this->request->post('id', 0);
        Db::name(Shop::T_PRODUCT)->where('id', $id)->delete();
        Db::name(Shop::T_SKU)->where('product_id', $id)->update(['status' => 0]);
        Db::name(Shop::T_CART)->where('product_id', $id)->delete();
        return $this->ok('商品已删除（历史订单明细不受影响）', '/admin/plugin/shop/products');
    }

    /* ==================== 分类 ==================== */

    public function categories()
    {
        return $this->render('categories', [
            'active' => $this->active('/admin/plugin/shop/categories'),
            'pageTitle' => '商品分类',
            'list' => Db::name(Shop::T_CATEGORY)->order('sort desc,id asc')->select()->toArray(),
        ]);
    }

    public function categorySave()
    {
        $id = (int) $this->request->post('id', 0);
        $name = trim((string) $this->request->post('name', ''));
        if ($name === '') return $this->fail('分类名称不能为空', '/admin/plugin/shop/categories');
        $data = [
            'pid' => (int) $this->request->post('pid', 0),
            'name' => $name,
            'slug' => trim((string) $this->request->post('slug', '')),
            'sort' => (int) $this->request->post('sort', 0),
            'status' => (int) $this->request->post('status', 1) === 1 ? 1 : 0,
            'updated_at' => time(),
        ];
        if ($id > 0) Db::name(Shop::T_CATEGORY)->where('id', $id)->update($data);
        else { $data['created_at'] = time(); Db::name(Shop::T_CATEGORY)->insert($data); }
        return $this->ok('分类已保存', '/admin/plugin/shop/categories');
    }

    public function categoryDelete()
    {
        $id = (int) $this->request->post('id', 0);
        $used = Db::name(Shop::T_PRODUCT)->where('category_id', $id)->count();
        if ($used > 0) return $this->fail('该分类下还有商品，不能删除', '/admin/plugin/shop/categories');
        Db::name(Shop::T_CATEGORY)->where('id', $id)->delete();
        return $this->ok('分类已删除', '/admin/plugin/shop/categories');
    }

    /* ==================== SKU 库存 ==================== */

    public function skus()
    {
        $kw = trim((string) $this->request->get('keyword', ''));
        $warn = $this->request->get('warn', '') === '1';
        $q = Db::name(Shop::T_SKU)->alias('s')->join(Shop::table(Shop::T_PRODUCT) . ' p', 'p.id=s.product_id');
        if ($kw !== '') $q->whereLike('p.title|s.sku_code', '%' . $kw . '%');
        if ($warn) $q->where('s.stock', '<=', Db::raw('s.warn_stock'));
        $total = (int) (clone $q)->count();
        $pager = $this->page($total, max(1, (int) $this->request->get('page', 1)), 20);
        $list = $q->field('s.*,p.title,p.status pstatus')->order('s.stock asc,s.id desc')->page($pager['page'], 20)->select()->toArray();
        return $this->render('skus', [
            'active' => $this->active('/admin/plugin/shop/skus'),
            'pageTitle' => '库存管理',
            'list' => $list,
            'pager' => $pager,
            'f' => ['keyword' => $kw, 'warn' => $warn],
        ]);
    }

    public function skuSave()
    {
        $id = (int) $this->request->post('id', 0);
        $data = [
            'price' => round((float) $this->request->post('price', 0), 2),
            'stock' => max(0, (int) $this->request->post('stock', 0)),
            'warn_stock' => max(0, (int) $this->request->post('warn_stock', 0)),
            'status' => (int) $this->request->post('status', 1) === 1 ? 1 : 0,
            'updated_at' => time(),
        ];
        Db::name(Shop::T_SKU)->where('id', $id)->update($data);
        return $this->ok('SKU 已保存', '/admin/plugin/shop/skus');
    }

    public function skuStock()
    {
        $id = (int) $this->request->post('id', 0);
        $op = (string) $this->request->post('op', 'set');
        $num = (int) $this->request->post('num', 0);
        if ($num < 0) return $this->fail('数量不能为负', '/admin/plugin/shop/skus');
        if ($op === 'add') Db::name(Shop::T_SKU)->where('id', $id)->update(['stock' => Db::raw('stock+' . $num), 'updated_at' => time()]);
        else Db::name(Shop::T_SKU)->where('id', $id)->update(['stock' => $num, 'updated_at' => time()]);
        return $this->ok('库存已更新', '/admin/plugin/shop/skus');
    }

    /* ==================== 订单 ==================== */

    public function orders()
    {
        $f = [
            'keyword' => trim((string) $this->request->get('keyword', '')),
            'status' => (string) $this->request->get('status', ''),
            'staff_id' => (int) $this->request->get('staff_id', 0),
            'spend_state' => (string) $this->request->get('spend_state', ''),
        ];
        [$q] = $this->orderQuery($f);
        $total = (int) (clone $q)->count();
        $pager = $this->page($total, max(1, (int) $this->request->get('page', 1)), 15);
        $list = $q->order('o.id desc')->page($pager['page'], 15)->select()->toArray();
        return $this->render('orders', [
            'active' => $this->active('/admin/plugin/shop/orders'),
            'pageTitle' => '订单管理',
            'list' => $list,
            'pager' => $pager,
            'f' => $f,
        ]);
    }

    private function orderQuery(array $f): array
    {
        $q = Db::name(Shop::T_ORDER)->alias('o');
        if ($f['keyword'] !== '') {
            $q->whereLike('o.order_no|o.member_name|o.mobile', '%' . $f['keyword'] . '%');
        }
        if ($f['status'] !== '') $q->where('o.status', $f['status']);
        if ($f['staff_id'] > 0) $q->where('o.staff_id', $f['staff_id']);
        if ($f['spend_state'] !== '') $q->where('o.spend_state', (int) $f['spend_state']);
        return [$q];
    }

    public function orderDetail()
    {
        $no = (string) $this->request->param('no', '');
        $order = Db::name(Shop::T_ORDER)->where('order_no', $no)->find();
        if (!$order) return $this->fail('订单不存在', '/admin/plugin/shop/orders');
        $member = MemberBridge::get((int) $order['member_id']);
        return $this->render('order_detail', [
            'active' => $this->active('/admin/plugin/shop/orders'),
            'pageTitle' => '订单详情',
            'order' => $order,
            'items' => Db::name(Shop::T_ITEM)->where('order_id', $order['id'])->select()->toArray(),
            'orderItems' => Db::name(Shop::T_ITEM)->where('order_id', $order['id'])->select()->toArray(),
            'logs' => Db::name(Shop::T_LOG)->where('order_id', $order['id'])->order('id asc')->select()->toArray(),
            'after' => Db::name(Shop::T_AFTER)->where('order_id', $order['id'])->order('id desc')->find(),
            'member' => $member,
        ]);
    }

    public function orderPrice()
    {
        $no = (string) $this->request->post('order_no', '');
        $amount = round((float) $this->request->post('payable_amount', 0), 2);
        $remark = mb_substr((string) $this->request->post('admin_remark', ''), 0, 255);
        if ($amount < 0) return $this->fail('改价不能为负', '/admin/plugin/shop/order/' . $no);
        $order = Db::name(Shop::T_ORDER)->where('order_no', $no)->find();
        if (!$order) return $this->fail('订单不存在', '/admin/plugin/shop/orders');
        if ($order['status'] !== OrderLogic::STATUS_PENDING) return $this->fail('只有待付款订单可以改价', '/admin/plugin/shop/order/' . $no);
        Db::name(Shop::T_ORDER)->where('id', $order['id'])->update([
            'payable_amount' => $amount, 'admin_remark' => $remark, 'updated_at' => time(),
        ]);
        Shop::log((int) $order['id'], $no, 'price', '后台改价：原 ￥' . $order['payable_amount'] . ' → ￥' . $amount . ' ' . $remark, 'admin', (int) ($this->admin['id'] ?? 0));
        return $this->ok('订单已改价', '/admin/plugin/shop/order/' . $no);
    }

    public function orderShip()
    {
        $no = (string) $this->request->post('order_no', '');
        $company = mb_substr((string) $this->request->post('express_company', ''), 0, 80);
        $expressNo = mb_substr((string) $this->request->post('express_no', ''), 0, 80);
        $order = Db::name(Shop::T_ORDER)->where('order_no', $no)->find();
        if (!$order) return $this->fail('订单不存在', '/admin/plugin/shop/order/' . $no);
        if ($order['status'] !== OrderLogic::STATUS_PAID) return $this->fail('只有已付款订单可以发货', '/admin/plugin/shop/order/' . $no);
        Db::name(Shop::T_ORDER)->where('id', $order['id'])->update([
            'status' => OrderLogic::STATUS_SHIPPED, 'express_company' => $company, 'express_no' => $expressNo,
            'shipped_at' => time(), 'updated_at' => time(),
        ]);
        Shop::log((int) $order['id'], $no, 'ship', '发货：' . $company . ' ' . $expressNo, 'admin', (int) ($this->admin['id'] ?? 0));
        return $this->ok('已发货', '/admin/plugin/shop/order/' . $no);
    }

    public function orderClose()
    {
        $no = (string) $this->request->post('order_no', '');
        $order = Db::name(Shop::T_ORDER)->where('order_no', $no)->find();
        if (!$order) return $this->fail('订单不存在', '/admin/plugin/shop/order/' . $no);
        try {
            OrderLogic::close((int) $order['id'], 'admin', (int) ($this->admin['id'] ?? 0));
            return $this->ok('订单已关闭，库存与优惠券已回滚', '/admin/plugin/shop/order/' . $no);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), '/admin/plugin/shop/order/' . $no);
        }
    }

    public function orderRefund()
    {
        $no = (string) $this->request->post('order_no', '');
        $reason = mb_substr((string) $this->request->post('reason', '后台退款'), 0, 255);
        $order = Db::name(Shop::T_ORDER)->where('order_no', $no)->find();
        if (!$order) return $this->fail('订单不存在', '/admin/plugin/shop/order/' . $no);
        try {
            OrderLogic::refund((int) $order['id'], $reason, 0, 'admin', (int) ($this->admin['id'] ?? 0));
            return $this->ok('退款完成，余额/积分已按流水退回；累计消费不回退', '/admin/plugin/shop/order/' . $no);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), '/admin/plugin/shop/order/' . $no);
        }
    }

    /**
     * 累计消费不确定时的对账入口：
     * 先看会员当前 total_spent 是否已经达到 spend_before + payable，已到账只补标记，绝不盲目重试。
     */
    public function orderSpendRetry()
    {
        $no = (string) $this->request->post('order_no', '');
        $order = Db::name(Shop::T_ORDER)->where('order_no', $no)->find();
        if (!$order) return $this->fail('订单不存在', '/admin/plugin/shop/order/' . $no);
        if (!in_array((int) $order['spend_state'], [0, 3], true)) return $this->fail('当前状态无需对账', '/admin/plugin/shop/order/' . $no);
        $member = MemberBridge::get((int) $order['member_id']);
        $target = round((float) $order['spend_before'] + (float) $order['payable_amount'], 2);
        $now = round((float) ($member['total_spent'] ?? 0), 2);
        if ($now >= $target) {
            Db::name(Shop::T_ORDER)->where('id', $order['id'])->update(['spend_state' => 2, 'spend_synced_at' => time()]);
            Shop::log((int) $order['id'], $no, 'spend_reconcile', '对账发现会员累计消费已到账：当前 ' . $now . ' >= 目标 ' . $target . '，仅补标记', 'admin');
            return $this->ok('对账完成：会员累计消费已包含本单，已补标记为已同步', '/admin/plugin/shop/order/' . $no);
        }
        $r = OrderLogic::syncSpend($no, true);
        return $r['state'] === 2
            ? $this->ok('累计消费已同步', '/admin/plugin/shop/order/' . $no)
            : $this->fail('仍未成功：' . ($r['msg'] ?? ''), '/admin/plugin/shop/order/' . $no);
    }

    public function orderExport()
    {
        $f = [
            'keyword' => trim((string) $this->request->get('keyword', '')),
            'status' => (string) $this->request->get('status', ''),
            'staff_id' => (int) $this->request->get('staff_id', 0),
            'spend_state' => (string) $this->request->get('spend_state', ''),
        ];
        [$q] = $this->orderQuery($f);
        $list = $q->order('o.id desc')->limit(5000)->select()->toArray();
        $rows = [];
        foreach ($list as $o) {
            $rows[] = [
                $o['order_no'], $o['biz_no'], $o['member_name'], $o['mobile'], Shop::orderStatusText((string) $o['status']),
                number_format((float) $o['goods_amount'], 2, '.', ''),
                number_format((float) $o['member_discount'], 2, '.', ''),
                number_format((float) $o['coupon_amount'], 2, '.', ''),
                $o['points_used'],
                number_format((float) $o['payable_amount'], 2, '.', ''),
                number_format((float) $o['balance_amount'], 2, '.', ''),
                $o['receiver'] . ' ' . $o['province'] . $o['city'] . $o['district'] . $o['address'],
                $o['express_company'] . ' ' . $o['express_no'],
                date('Y-m-d H:i:s', (int) $o['created_at']),
                (int) $o['spend_state'],
            ];
        }
        try {
            $path = Excel::export('shop_orders', ['订单号', '业务号', '会员', '手机', '状态', '商品金额', '会员优惠', '优惠券', '抵扣积分', '应付', '余额支付', '收货信息', '物流', '下单时间', '累计消费状态'], $rows);
        } catch (\Throwable $e) {
            return $this->fail('导出失败：' . $e->getMessage(), '/admin/plugin/shop/orders');
        }
        return download($path)->name('商城订单-' . date('YmdHis') . '.xlsx');
    }

    /* ==================== 售后 ==================== */

    public function afterSales()
    {
        $status = (string) $this->request->get('status', '');
        $q = Db::name(Shop::T_AFTER)->alias('a')->join(Shop::table(Shop::T_ORDER) . ' o', 'o.id=a.order_id', 'left');
        if ($status !== '') $q->where('a.status', $status);
        $list = $q->field('a.*,o.status ostatus,o.payable_amount,o.member_name')->order('a.id desc')->limit(200)->select()->toArray();
        return $this->render('aftersales', [
            'active' => $this->active('/admin/plugin/shop/aftersales'),
            'pageTitle' => '售后审核',
            'list' => $list,
            'f' => ['status' => $status],
        ]);
    }

    public function afterSaleAudit()
    {
        $id = (int) $this->request->post('id', 0);
        $action = (string) $this->request->post('action', 'reject');
        $remark = mb_substr((string) $this->request->post('admin_remark', ''), 0, 255);
        $as = Db::name(Shop::T_AFTER)->where('id', $id)->find();
        if (!$as) return $this->fail('售后单不存在', '/admin/plugin/shop/aftersales');
        if ($as['status'] !== 'pending') return $this->fail('该售后单已处理', '/admin/plugin/shop/aftersales');

        if ($action !== 'approve') {
            Db::name(Shop::T_AFTER)->where('id', $id)->update(['status' => 'rejected', 'admin_remark' => $remark, 'updated_at' => time()]);
            return $this->ok('已拒绝售后', '/admin/plugin/shop/aftersales');
        }
        try {
            OrderLogic::refund((int) $as['order_id'], $remark !== '' ? $remark : (string) $as['reason'], $id, 'admin', (int) ($this->admin['id'] ?? 0));
            Db::name(Shop::T_AFTER)->where('id', $id)->update(['admin_remark' => $remark, 'updated_at' => time()]);
            return $this->ok('已同意并完成退款（累计消费不回退）', '/admin/plugin/shop/aftersales');
        } catch (\Throwable $e) {
            return $this->fail('退款失败：' . $e->getMessage(), '/admin/plugin/shop/aftersales');
        }
    }

    /* ==================== 优惠券 ==================== */

    public function coupons()
    {
        $list = Db::name(Shop::T_COUPON)->order('id desc')->select()->toArray();
        return $this->render('coupons', [
            'active' => $this->active('/admin/plugin/shop/coupons'),
            'pageTitle' => '优惠券',
            'list' => $list,
        ]);
    }

    public function couponSave()
    {
        $id = (int) $this->request->post('id', 0);
        $name = trim((string) $this->request->post('name', ''));
        $code = strtoupper(trim((string) $this->request->post('code', '')));
        if ($name === '' || $code === '') return $this->fail('券名称和券码不能为空', '/admin/plugin/shop/coupons');
        $type = (string) $this->request->post('type', 'fixed');
        if (!in_array($type, ['fixed', 'percent'], true)) $type = 'fixed';
        $value = round((float) $this->request->post('value', 0), 2);
        if ($type === 'percent' && ($value <= 0 || $value >= 1)) return $this->fail('折扣券 value 必须填 0~1 之间，如 0.9 表示 9 折', '/admin/plugin/shop/coupons');
        if ($type === 'fixed' && $value <= 0) return $this->fail('满减券金额必须大于 0', '/admin/plugin/shop/coupons');
        $data = [
            'name' => $name,
            'code' => $code,
            'type' => $type,
            'value' => $value,
            'min_amount' => round((float) $this->request->post('min_amount', 0), 2),
            'total' => max(0, (int) $this->request->post('total', 0)),
            'start_at' => ($t = strtotime((string) $this->request->post('start_at', ''))) ? $t : 0,
            'end_at' => ($t = strtotime((string) $this->request->post('end_at', ''))) ? $t : 0,
            'status' => (int) $this->request->post('status', 1) === 1 ? 1 : 0,
            'updated_at' => time(),
        ];
        try {
            if ($id > 0) Db::name(Shop::T_COUPON)->where('id', $id)->update($data);
            else { $data['created_at'] = time(); Db::name(Shop::T_COUPON)->insert($data); }
        } catch (\Throwable $e) {
            return $this->fail('保存失败，券码可能重复', '/admin/plugin/shop/coupons');
        }
        return $this->ok('优惠券已保存', '/admin/plugin/shop/coupons');
    }

    public function couponGrant()
    {
        $id = (int) $this->request->post('id', 0);
        $ids = preg_split('/[,，\s]+/u', trim((string) $this->request->post('member_ids', '')));
        $coupon = Db::name(Shop::T_COUPON)->where('id', $id)->find();
        if (!$coupon) return $this->fail('优惠券不存在', '/admin/plugin/shop/coupons');
        $ok = 0; $skip = [];
        foreach ($ids as $mid) {
            $mid = (int) $mid;
            if ($mid <= 0) continue;
            if (!MemberBridge::get($mid)) { $skip[] = $mid . '(会员不存在)'; continue; }
            if ((int) $coupon['total'] > 0 && (int) $coupon['received'] >= (int) $coupon['total']) { $skip[] = $mid . '(券已发完)'; continue; }
            try {
                Db::name(Shop::T_COUPON_M)->insert([
                    'coupon_id' => $id, 'member_id' => $mid, 'status' => 0, 'received_at' => time(),
                ]);
                Db::name(Shop::T_COUPON)->where('id', $id)->update(['received' => Db::raw('`received`+1')]);
                $ok++;
            } catch (\Throwable $e) {
                $skip[] = $mid . '(已领取过)';
            }
        }
        $msg = '成功发放 ' . $ok . ' 张';
        if ($skip) $msg .= '；跳过：' . implode('，', $skip);
        return $this->ok($msg, '/admin/plugin/shop/coupons');
    }

    /* ==================== 设置 ==================== */

    public function settings()
    {
        return $this->render('settings', [
            'active' => $this->active('/admin/plugin/shop/settings'),
            'pageTitle' => '商城设置',
            'cfg' => Shop::cfg(),
            'memberOk' => MemberBridge::enabled(),
        ]);
    }

    public function settingsSave()
    {
        $keys = ['shop_name', 'shop_notice', 'page_size', 'points_deduction', 'points_rate', 'points_max_percent',
            'balance_pay', 'discount_stack', 'auto_close_minutes', 'warn_stock', 'member_mode', 'member_api_base',
            'member_appid', 'member_secret', 'api_appid', 'api_appid', 'api_secret', 'api_ip_whitelist'];
        $patch = [];
        foreach ($keys as $k) {
            $v = $this->request->post($k, null);
            if ($v !== null) $patch[$k] = trim((string) $v);
        }
        $patch['points_deduction'] = (string) $this->request->post('points_deduction', '0') === '1' ? '1' : '0';
        $patch['balance_pay'] = (string) $this->request->post('balance_pay', '0') === '1' ? '1' : '0';
        $patch['discount_stack'] = (string) $this->request->post('discount_stack', '0') === '1' ? '1' : '0';
        if (!in_array($patch['member_mode'] ?? 'service', ['service', 'api'], true)) $patch['member_mode'] = 'service';
        // 密钥留空表示不修改，避免误保存把已有 Secret 清掉
        if (($patch['api_secret'] ?? '') === '') unset($patch['api_secret']);
        Shop::saveCfg($patch);
        return $this->ok('商城设置已保存', '/admin/plugin/shop/settings');
    }
}

