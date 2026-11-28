<?php include __DIR__ . '/_head.php'; ?>
<div class="sp-section">
  <h2 style="margin:0 0 16px;font-size:20px">我的商城订单</h2>
  <div class="sp-tabs">
    <a class="<?= $f['status'] === '' ? 'on' : '' ?>" href="/shop/orders">全部</a>
    <?php foreach (['pending_payment' => '待付款', 'paid' => '已付款', 'shipped' => '已发货', 'completed' => '已完成', 'refunded' => '已退款'] as $k => $v): ?>
      <a class="<?= $f['status'] === $k ? 'on' : '' ?>" href="/shop/orders?status=<?= $k ?>"><?= $v ?></a>
    <?php endforeach; ?>
  </div>

  <?php if (!$list): ?>
    <div class="sp-card sp-empty">还没有订单，<a href="/shop">去商城看看</a></div>
  <?php else: ?>
    <div style="display:grid;gap:12px">
      <?php foreach ($list as $o): ?>
        <div class="sp-card" style="padding:14px 18px">
          <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;font-size:12.5px;color:#64748b">
            <span><?= date('Y-m-d H:i', (int) $o['created_at']) ?> · <span class="sp-status <?= in_array($o['status'], ['paid', 'shipped', 'completed'], true) ? 'ok' : ($o['status'] === 'pending_payment' ? 'warn' : '') ?>"><?= e(\plugins\shop\service\Shop::orderStatusText((string) $o['status'])) ?></span></span>
            <span>订单号 <?= e($o['order_no']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;margin-top:8px;flex-wrap:wrap">
            <div>
              <b style="font-size:15px">￥<?= number_format((float) $o['payable_amount'], 2) ?></b>
              <span style="font-size:12.5px;color:#64748b;margin-left:10px"><?= e($o['receiver']) ?> · 余额支付 ￥<?= number_format((float) $o['balance_amount'], 2) ?> · <?= (int) $o['points_used'] ?> 积分</span>
            </div>
            <div>
              <a class="sp-btn ghost" href="/shop/order/<?= e($o['order_no']) ?>">订单详情</a>
              <?php if ($o['status'] === 'pending_payment'): ?><a class="sp-btn" href="/shop/order/<?= e($o['order_no']) ?>">去支付</a><?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($pager['pages'] > 1): ?>
  <div class="sp-pager">
    <?php for ($i = 1; $i <= $pager['pages']; $i++): ?><a class="<?= $i === $pager['page'] ? 'on' : '' ?>" href="/shop/orders?page=<?= $i ?>&status=<?= urlencode($f['status']) ?>"><?= $i ?></a><?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/_foot.php'; ?>
