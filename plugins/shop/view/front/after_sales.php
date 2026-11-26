<?php include __DIR__ . '/_head.php'; ?>
<div class="sp-section">
  <h2 style="margin:0 0 16px;font-size:20px">我的售后</h2>
  <?php if (!$list): ?>
    <div class="sp-card sp-empty">还没有售后记录</div>
  <?php else: ?>
    <div style="display:grid;gap:12px">
      <?php foreach ($list as $a): ?>
        <div class="sp-card" style="padding:14px 18px">
          <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;font-size:12.5px;color:#64748b">
            <span><?= e($a['after_sale_no']) ?> · 订单 <a href="/shop/order/<?= e($a['order_no']) ?>"><?= e($a['order_no']) ?></a></span>
            <span class="sp-status <?= $a['status'] === 'refunded' ? 'ok' : ($a['status'] === 'pending' ? 'warn' : '') ?>"><?= e(\plugins\shop\service\Shop::afterStatusText((string) $a['status'])) ?></span>
          </div>
          <div style="margin-top:8px;font-size:13px"><?= e($a['reason']) ?></div>
          <div style="margin-top:6px;font-size:12px;color:#94a3b8">金额 ￥<?= number_format((float) $a['amount'], 2) ?> · <?= date('Y-m-d H:i', (int) $a['created_at']) ?><?php if ($a['admin_remark']): ?> · 审核备注：<?= e($a['admin_remark']) ?><?php endif; ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/_foot.php'; ?>
