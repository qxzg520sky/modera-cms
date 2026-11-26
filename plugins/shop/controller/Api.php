<?php
declare(strict_types=1);

namespace plugins\shop\controller;

use app\BaseController;
use plugins\shop\service\MemberBridge;
use plugins\shop\logic\OrderLogic;
use plugins\shop\service\Shop;
use think\facade\Cache;
use think\facade\Db;

/**
 * 商城开放 API，供小程序 / H5 / ERP 服务端调用。
 *
 * 签名与会员插件完全一致：
 * X-Member-Appid + "\n" + Timestamp + "\n" + Nonce + "\n" + rawBody
 * HMAC-SHA256(secret, above)。Secret 只放服务端，绝不下发前端。
 */
class Api extends BaseController
{
    public function health()
    {
        if ($r = $this->guard()) return $r;
        return $this->ok(['plugin' => 'shop', 'version' => '1.0.0', 'member' => MemberBridge::enabled(), 'time' => date('c')]);
    }

    public function products()
    {
        if ($r = $this->guard()) return $r;
        $kw = trim((string) $this->request->get('keyword', ''));
        $cat = (int) $this->request->get('category_id', 0);
        $page = max(1, (int) $this->request->get('page', 1));
        $size = min(50, max(1, (int) $this->request->get('size', 10)));
        $q = Db::name(Shop::T_PRODUCT)->where('status', 1);
        if ($kw !== '') $q->whereLike('title|subtitle', '%' . $kw . '%');
        if ($cat > 0) $q->where('category_id', $cat);
        $total = (int) (clone $q)->count();
        $list = $q->field('id,title,subtitle,cover,price,market_price,stock,sales,is_hot')->order('sort desc,id desc')->page($page, $size)->select()->toArray();
        foreach ($list as &$p) {
            $p['skus'] = Db::name(Shop::T_SKU)->where('product_id', $p['id'])->where('status', 1)
                ->field('id,sku_code,spec_json,price,stock')->select()->toArray();
        }
        unset($p);
        return $this->ok(['list' => $list, 'total' => $total, 'page' => $page, 'size' => $size]);
    }

    public function product()
    {
        if ($r = $this->guard()) return $r;
        $id = (int) $this->request->param('id', 0);
        $p = Db::name(Shop::T_PRODUCT)->where('id', $id)->where('status', 1)->find();
        if (!$p) return $this->fail('商品不存在', 404);
        $p['skus'] = Db::name(Shop::T_SKU)->where('product_id', $id)->where('status', 1)->select()->toArray();
        return $this->ok(['product' => $p]);
    }

    public function orderCreate()
    {
        if ($r = $this->guard()) return $r;
        try {
            $data = $this->body();
            $memberId = (int) ($data['member_id'] ?? 0);
            $res = OrderLogic::create($memberId, $data);
            return $this->ok(['order_no' => $res['order']['order_no'], 'payable_amount' => $res['order']['payable_amount'], 'reused' => $res['reused']]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function orderPay()
    {
        if ($r = $this->guard()) return $r;
        try {
            $data = $this->body();
            $memberId = (int) ($data['member_id'] ?? 0);
            $res = OrderLogic::pay((string) ($data['order_no'] ?? ''), $memberId, (string) ($data['pay_type'] ?? 'balance'));
            return $this->ok([
                'order_no' => $res['order']['order_no'],
                'status' => $res['order']['status'],
                'spend_state' => $res['spend_state'] ?? 0,
                'reused' => $res['reused'] ?? false,
            ], ($res['spend_state'] ?? 0) === 3 ? '支付成功，累计消费待对账' : '支付成功');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function order()
    {
        if ($r = $this->guard()) return $r;
        $no = (string) $this->request->param('no', '');
        $memberId = (int) $this->request->get('member_id', 0);
        $o = Db::name(Shop::T_ORDER)->where('order_no', $no)->where('member_id', $memberId)->find();
        if (!$o) return $this->fail('订单不存在', 404);
        $o['items'] = Db::name(Shop::T_ITEM)->where('order_id', $o['id'])->select()->toArray();
        $o['logs'] = Db::name(Shop::T_LOG)->where('order_id', $o['id'])->order('id asc')->select()->toArray();
        return $this->ok(['order' => $o]);
    }

    /* ==================== 签名 ==================== */

    private function guard()
    {
        try {
            $this->verifySign();
            return null;
        } catch (\Throwable $e) {
            return json(['code' => 401, 'msg' => '签名校验失败：' . $e->getMessage(), 'data' => null], 401);
        }
    }

    private function verifySign(): void
    {
        $cfg = Shop::cfg();
        $secret = (string) $cfg['api_secret'];
        $appid  = (string) $cfg['api_appid'];
        if ($secret === '' || $appid === '') throw new \RuntimeException('商城 API 密钥未配置');
        $ip = (string) $this->request->ip();
        $white = array_filter(array_map('trim', explode(',', (string) $cfg['api_ip_whitelist'])));
        if ($white && !in_array($ip, $white, true)) throw new \RuntimeException('IP 不在白名单');

        $reqAppid = (string) $this->request->header('x-member-appid', '');
        $ts       = (int) $this->request->header('x-member-timestamp', '0');
        $nonce    = (string) $this->request->header('x-member-nonce', '');
        $sign     = strtolower((string) $this->request->header('x-member-sign', ''));
        if ($reqAppid === '' || $reqAppid !== $appid) throw new \RuntimeException('appid 不匹配');
        if ($ts <= 0 || abs(time() - $ts) > 300) throw new \RuntimeException('时间戳已过期');
        if (!preg_match('/^[A-Za-z0-9]{8,64}$/', $nonce)) throw new \RuntimeException('nonce 不合法');
        if ($sign === '') throw new \RuntimeException('缺少签名');

        $raw = (string) $this->request->getContent();
        $expected = hash_hmac('sha256', $appid . "\n" . $ts . "\n" . $nonce . "\n" . $raw, $secret);
        if (!hash_equals($expected, $sign)) throw new \RuntimeException('签名不正确');

        $nonceKey = 'shop_api_nonce_' . substr(md5($appid . '|' . $nonce), 0, 32);
        if (Cache::has($nonceKey)) throw new \RuntimeException('nonce 已使用，疑似重放');
        Cache::set($nonceKey, 1, 300);

        $rateKey = 'shop_api_rate_' . date('YmdHi') . '_' . substr(md5($appid), 0, 12);
        $count = (int) Cache::get($rateKey, 0);
        if ($count >= 300) throw new \RuntimeException('请求过于频繁');
        Cache::set($rateKey, $count + 1, 70);
    }

    private function body(): array
    {
        $raw = (string) $this->request->getContent();
        if ($raw !== '') {
            $json = json_decode($raw, true);
            if (is_array($json)) return $json;
        }
        return (array) $this->request->post();
    }

    private function ok($data = [], string $msg = 'ok')
    {
        return json(['code' => 0, 'msg' => $msg, 'data' => $data]);
    }

    private function fail(string $msg, int $code = 400)
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => null], $code >= 400 && $code < 600 ? $code : 400);
    }
}

