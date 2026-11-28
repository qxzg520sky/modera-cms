<?php include __DIR__ . '/_head.php'; ?>
<div class="sp-section">
  <h2 style="margin:0 0 16px;font-size:20px">申请售后</h2>
  <div class="sp-card" style="padding:18px">
    <div style="font-size:13px;color:#64748b">订单 <?= e($order['order_no']) ?> · 应付 ￥<?= number_format((float) $order['payable_amount'], 2) ?> · <?= e(\plugins\shop\service\Shop::orderStatusText((string) $order['status'])) ?></div>
    <?php if ($existing): ?>
      <div class="sp-note warn">该订单已有处理中的售后申请（<?= e(\plugins\shop\service\Shop::afterStatusText((string) $existing['status'])) ?>），请等待管理员处理。</div>
    <?php else: ?>
      <form method="post" action="/shop/after-sale/apply" style="display:grid;gap:12px;margin-top:14px">
        <input type="hidden" name="order_no" value="<?= e($order['order_no']) ?>">
        <div class="sp-form-line" style="margin:0">
          <span style="font-size:12.5px;color:#64748b">类型</span>
          <label><input type="radio" name="type" value="refund" checked> 仅退款</label>
          <label><input type="radio" name="type" value="return"> 退货退款</label>
        </div>
        <div>
          <div style="font-size:12.5px;color:#64748b;margin-bottom:6px">售后原因</div>
          <textarea name="reason" required rows="4" placeholder="写清楚原因，方便管理员审核" style="width:100%;border:1px solid #d8e1ee;border-radius:10px;padding:10px 12px;font-size:13px"></textarea>
        </div>
        <div><button class="sp-btn">提交申请</button> <a class="sp-btn ghost" href="/shop/order/<?= e($order['order_no']) ?>">返回订单</a></div>
        <div style="font-size:11.5px;color:#94a3b8">提交后不会立即退款。管理员同意后才退回余额/积分并回滚库存；累计消费不会回退。</div>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/_foot.php'; ?>
