<?php include __DIR__ . '/_head.php'; ?>
<section class="mb-hero" style="padding-bottom:10px">
  <div class="mb-kicker">MY ORDERS</div>
  <h1 class="mb-title">我的订单</h1>
  <p class="mb-sub">会员订单由商城系统提供，会员插件只做身份与资产校验，不直接读写订单表。</p>
</section>

<section class="mb-section">
  <?php if (empty($orders['connected'])): ?>
    <div class="mb-card"><div class="mb-empty">商城尚未接入订单桥接，暂时没有订单数据。<br><span style="font-size:12px;color:#94a3b8">接入后可通过订单号、资产流水完成售后与对账。</span></div></div>
  <?php elseif (!$orders['list']): ?>
    <div class="mb-card"><div class="mb-empty">还没有订单记录，去商城逛逛吧。</div></div>
  <?php else: ?>
    <div class="mb-grid" style="gap:12px">
      <?php foreach ($orders['list'] as $o): ?>
        <div class="mb-card mb-order">
          <div class="top"><span><?= e($o['title'] ?? '订单') ?></span><span><?= e($o['status_text'] ?? $o['status'] ?? '') ?></span></div>
          <div class="no">订单号：<?= e($o['order_no'] ?? '-') ?>　下单时间：<?= e($o['created_at'] ?? '-') ?></div>
          <div class="mid"><?= e($o['summary'] ?? '') ?></div>
          <div class="bot">
            <span class="mb-badge gray"><?= (int) ($o['item_count'] ?? 0) ?> 件商品</span>
            <span style="font-weight:800;font-size:17px">￥<?= number_format((float) ($o['amount'] ?? 0), 2) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mb-pager">
      <?php echo \plugins\member\service\Member::pager($orders['page'], $orders['pages'], '/member/orders?'); ?>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/_foot.php'; ?>
