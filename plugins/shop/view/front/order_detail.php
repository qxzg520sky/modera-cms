<?php include __DIR__ . '/_head.php'; $st = \plugins\shop\service\Shop::orderStatusText((string) $order['status']); ?>
<div class="sp-section">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px">
    <h2 style="margin:0;font-size:20px">订单 <span style="font-size:13px;color:#64748b"><?= e($order['order_no']) ?></span></h2>
    <div><span class="sp-status <?= in_array($order['status'], ['paid', 'shipped', 'completed'], true) ? 'ok' : ($order['status'] === 'pending_payment' ? 'warn' : '') ?>"><?= e($st) ?></span> <a class="sp-btn ghost" href="/shop/orders">返回订单列表</a></div>
  </div>

  <?php if ($order['status'] === 'pending_payment'): ?>
    <div class="sp-card" style="padding:16px 18px;margin-bottom:14px">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
        <div>
          <b>待付款 ￥<?= number_format((float) $order['payable_amount'], 2) ?></b>
          <div style="font-size:12px;color:#94a3b8">将使用会员余额支付<?= (int) $order['points_used'] > 0 ? '，同时扣除已抵扣的 ' . (int) $order['points_used'] . ' 积分' : '' ?>。</div>
        </div>
        <form method="post" action="/shop/order/pay" onsubmit="return confirm('确认使用会员余额支付？')">
          <input type="hidden" name="order_no" value="<?= e($order['order_no']) ?>">
          <button class="sp-btn">余额支付</button>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <div class="sp-row">
    <div style="flex:1;min-width:0">
      <div class="sp-card" style="padding:16px 18px">
        <h3 style="margin:0 0 10px;font-size:14px">收货信息</h3>
        <div><?= e($order['receiver']) ?> <?= e($order['mobile']) ?></div>
        <div style="font-size:12.5px;color:#64748b"><?= e($order['province'] . $order['city'] . $order['district'] . $order['address']) ?></div>
        <?php if ($order['express_no']): ?><div style="margin-top:8px;font-size:12.5px">物流：<?= e($order['express_company']) ?> <?= e($order['express_no']) ?></div><?php endif; ?>
      </div>

      <div class="sp-card" style="padding:16px 18px;margin-top:14px">
        <h3 style="margin:0 0 10px;font-size:14px">商品</h3>
        <table class="sp-table">
          <thead><tr><th>商品</th><th>单价</th><th>数量</th><th>小计</th></tr></thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr><td><b><?= e($it['product_title']) ?></b><div style="font-size:12px;color:#94a3b8"><?= e(\plugins\shop\service\Shop::specText((string) $it['sku_spec'])) ?: '单规格' ?></div></td><td>￥<?= number_format((float) $it['price'], 2) ?></td><td><?= (int) $it['qty'] ?></td><td>￥<?= number_format((float) $it['subtotal'], 2) ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="sp-card" style="padding:16px 18px;margin-top:14px">
        <h3 style="margin:0 0 10px;font-size:14px">订单进度</h3>
        <div class="sp-timeline">
          <?php foreach ($logs as $l): ?>
            <div class="item"><b><?= date('m-d H:i', (int) $l['created_at']) ?></b><span><?= e($l['content']) ?></span></div>
          <?php endforeach; ?>
          <?php if (!$logs): ?><div style="color:#94a3b8;font-size:12.5px">暂无记录</div><?php endif; ?>
        </div>
      </div>
    </div>

    <aside style="width:330px;flex:none">
      <div class="sp-card">
        <div class="sp-summary">
          <div class="line"><span>商品金额</span><b>￥<?= number_format((float) $order['goods_amount'], 2) ?></b></div>
          <div class="line"><span>会员折扣（<?= e($order['discount_rate']) ?>）</span><b>-￥<?= number_format((float) $order['member_discount'], 2) ?></b></div>
          <div class="line"><span>优惠券</span><b>-￥<?= number_format((float) $order['coupon_amount'], 2) ?></b></div>
          <div class="line"><span>积分抵扣（<?= (int) $order['points_used'] ?>）</span><b>-￥<?= number_format((float) $order['points_amount'], 2) ?></b></div>
          <div class="total"><span>应付</span><span>￥<?= number_format((float) $order['payable_amount'], 2) ?></span></div>
          <div class="line"><span>余额支付</span><b>￥<?= number_format((float) $order['balance_amount'], 2) ?></b></div>
          <div style="font-size:11.5px;color:#94a3b8">下单时间 <?= date('Y-m-d H:i', (int) $order['created_at']) ?></div>
        </div>
      </div>

      <?php if (in_array($order['status'], ['paid', 'shipped', 'completed'], true) && (!$after || $after['status'] === 'rejected')): ?>
        <div class="sp-card" style="padding:16px 18px;margin-top:14px">
          <h3 style="margin:0 0 8px;font-size:14px">售后</h3>
          <div style="font-size:12.5px;color:#64748b;margin-bottom:10px">收到商品有问题可申请退款，管理员审核通过后退回余额/积分并回滚库存。</div>
          <a class="sp-btn ghost" href="/shop/after-sale/<?= e($order['order_no']) ?>">申请售后</a>
        </div>
      <?php endif; ?>

      <?php if ($after): ?>
        <div class="sp-card" style="padding:16px 18px;margin-top:14px">
          <h3 style="margin:0 0 8px;font-size:14px">售后单</h3>
          <div style="font-size:12.5px"><?= e($after['after_sale_no']) ?> · <span class="sp-status <?= $after['status'] === 'refunded' ? 'ok' : ($after['status'] === 'pending' ? 'warn' : '') ?>"><?= e(\plugins\shop\service\Shop::afterStatusText((string) $after['status'])) ?></span></div>
          <div style="font-size:12.5px;color:#64748b;margin-top:6px"><?= e($after['reason']) ?><?php if ($after['admin_remark']): ?><br>审核备注：<?= e($after['admin_remark']) ?><?php endif; ?></div>
        </div>
      <?php endif; ?>
    </aside>
  </div>
</div>
<?php include __DIR__ . '/_foot.php'; ?>
