<?php $pageTitle = $pageTitle ?? '订单详情'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/shop/asset/admin.css?v=1.0.0">
<?php if ($flash): ?><div class="sp-alert ok"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="sp-alert err"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-head"><span>订单 <span class="sp-code"><?= e($order['order_no']) ?></span></span><a class="btn btn-sm" href="/admin/plugin/shop/orders">← 返回列表</a></div>
  <div style="padding:16px 18px;display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;font-size:13px">
    <div><div class="sp-muted">状态</div><b><?= e(\plugins\shop\service\Shop::orderStatusText((string) $order['status'])) ?></b></div>
    <div><div class="sp-muted">业务号 biz_no</div><span class="sp-code"><?= e($order['biz_no']) ?></span></div>
    <div><div class="sp-muted">下单时间</div><?= date('Y-m-d H:i:s', (int) $order['created_at']) ?></div>
    <div><div class="sp-muted">会员</div><?= e($order['member_name']) ?> <span class="sp-muted">#<?= (int) $order['member_id'] ?></span></div>
    <div><div class="sp-muted">归属业务员</div><?= (int) $order['staff_id'] > 0 ? (int) $order['staff_id'] : '未分配' ?></div>
    <div><div class="sp-muted">收货人</div><?= e($order['receiver']) ?> <?= e($order['mobile']) ?><div class="sp-muted"><?= e($order['province'] . $order['city'] . $order['district'] . $order['address']) ?></div></div>
    <div><div class="sp-muted">物流</div><?= e($order['express_company'] ?: '-') ?> <?= e($order['express_no']) ?></div>
    <div><div class="sp-muted">买家备注</div><?= e($order['buyer_remark'] ?: '-') ?></div>
    <div><div class="sp-muted">后台备注</div><?= e($order['admin_remark'] ?: '-') ?></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.3fr .8fr;gap:16px;margin-top:16px">
  <div class="card">
    <div class="card-head"><span>商品明细</span></div>
    <table class="table">
      <thead><tr><th>商品</th><th>规格</th><th>单价</th><th>数量</th><th>小计</th></tr></thead>
      <tbody>
      <?php foreach (($orderItems ?? []) as $it): ?>
        <tr>
          <td><b><?= e($it['product_title']) ?></b></td>
          <td><?= e(\plugins\shop\service\Shop::specText((string) $it['sku_spec'])) ?: '-' ?></td>
          <td>￥<?= number_format((float) $it['price'], 2) ?></td>
          <td><?= (int) $it['qty'] ?></td>
          <td>￥<?= number_format((float) $it['subtotal'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <div style="padding:14px 18px;display:grid;gap:6px;font-size:13px;max-width:380px;margin-left:auto">
      <div style="display:flex;justify-content:space-between"><span>商品金额</span><span>￥<?= number_format((float) $order['goods_amount'], 2) ?></span></div>
      <div style="display:flex;justify-content:space-between"><span>会员折扣（<?= e($order['discount_rate']) ?>）</span><span>-￥<?= number_format((float) $order['member_discount'], 2) ?></span></div>
      <div style="display:flex;justify-content:space-between"><span>优惠券 <?= e($order['coupon_code']) ?></span><span>-￥<?= number_format((float) $order['coupon_amount'], 2) ?></span></div>
      <div style="display:flex;justify-content:space-between"><span>积分抵扣 <?= (int) $order['points_used'] ?> 分</span><span>-￥<?= number_format((float) $order['points_amount'], 2) ?></span></div>
      <div style="display:flex;justify-content:space-between;font-weight:800;border-top:1px solid #e6ecf3;padding-top:6px"><span>应付</span><span>￥<?= number_format((float) $order['payable_amount'], 2) ?></span></div>
      <div style="display:flex;justify-content:space-between"><span>余额支付</span><span>￥<?= number_format((float) $order['balance_amount'], 2) ?></span></div>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-head"><span>会员资产（实时读取会员插件）</span></div>
      <div style="padding:14px 18px;display:grid;gap:8px;font-size:13px">
        <?php if (!$member): ?>
          <div class="sp-muted">会员插件不可用或会员不存在，无法读取资产</div>
        <?php else: ?>
          <div style="display:flex;justify-content:space-between"><span>等级</span><b><?= e($member['level_name']) ?>（<?= e($member['discount']) ?> 折）</b></div>
          <div style="display:flex;justify-content:space-between"><span>余额</span><b>￥<?= number_format((float) $member['balance'], 2) ?></b></div>
          <div style="display:flex;justify-content:space-between"><span>积分</span><b><?= (int) $member['points'] ?></b></div>
          <div style="display:flex;justify-content:space-between"><span>累计消费</span><b>￥<?= number_format((float) $member['total_spent'], 2) ?></b></div>
        <?php endif; ?>
        <div style="border-top:1px solid #e6ecf3;padding-top:8px" class="sp-muted">
          累计消费状态：<b><?= [0 => '未同步', 1 => '调用中', 2 => '已同步', 3 => '不确定'][(int) $order['spend_state']] ?></b><br>
          调用前累计：￥<?= number_format((float) $order['spend_before'], 2) ?>；本单应付：￥<?= number_format((float) $order['payable_amount'], 2) ?>
        </div>
      </div>
    </div>

    <div class="card" style="margin-top:16px">
      <div class="card-head"><span>订单操作</span></div>
      <div style="padding:14px 18px;display:grid;gap:12px">
        <?php if ($order['status'] === 'pending_payment'): ?>
          <form method="post" action="/admin/plugin/shop/order/price" style="display:grid;gap:8px">
            <input type="hidden" name="order_no" value="<?= e($order['order_no']) ?>">
            <div class="sp-field"><label>改价（应付金额）</label><input name="payable_amount" type="number" step="0.01" min="0" value="<?= e($order['payable_amount']) ?>"></div>
            <div class="sp-field"><label>备注</label><input name="admin_remark" placeholder="改价原因"></div>
            <button class="btn">保存改价</button>
          </form>
          <form method="post" action="/admin/plugin/shop/order/close" onsubmit="return confirm('关闭订单？会回滚库存和优惠券')">
            <input type="hidden" name="order_no" value="<?= e($order['order_no']) ?>">
            <button class="btn sp-danger" style="width:100%">关闭订单</button>
          </form>
        <?php endif; ?>

        <?php if ($order['status'] === 'paid'): ?>
          <form method="post" action="/admin/plugin/shop/order/ship" style="display:grid;gap:8px">
            <input type="hidden" name="order_no" value="<?= e($order['order_no']) ?>">
            <div class="sp-field"><label>快递公司</label><input name="express_company" placeholder="顺丰/中通"></div>
            <div class="sp-field"><label>快递单号</label><input name="express_no"></div>
            <button class="btn">确认发货</button>
          </form>
        <?php endif; ?>

        <?php if (in_array($order['status'], ['paid', 'shipped', 'completed'], true)): ?>
          <form method="post" action="/admin/plugin/shop/order/refund" onsubmit="return confirm('全额退款？会回退余额/积分并回滚库存，累计消费不回退')" style="display:grid;gap:8px">
            <input type="hidden" name="order_no" value="<?= e($order['order_no']) ?>">
            <div class="sp-field"><label>退款原因</label><input name="reason" value="后台退款"></div>
            <button class="btn sp-danger">全额退款</button>
          </form>
        <?php endif; ?>

        <?php if ((int) $order['spend_state'] === 3 || (int) $order['spend_state'] === 0): ?>
          <form method="post" action="/admin/plugin/shop/order/spend-retry" onsubmit="return confirm('先核对会员累计消费？系统会比对该会员 total_spent 是否已包含本单，已到账只补标记')">
            <input type="hidden" name="order_no" value="<?= e($order['order_no']) ?>">
            <button class="btn">累计消费对账</button>
          </form>
        <?php endif; ?>
        <div class="sp-muted">退款不会减少会员累计消费，这是会员插件当前接口边界，详见 README 风险说明。</div>
      </div>
    </div>

    <?php if ($after): ?>
    <div class="card" style="margin-top:16px">
      <div class="card-head"><span>售后单</span></div>
      <div style="padding:14px 18px;font-size:13px;display:grid;gap:6px">
        <div><?= e($after['after_sale_no']) ?> · <span class="sp-badge"><?= e(\plugins\shop\service\Shop::afterStatusText((string) $after['status'])) ?></span></div>
        <div class="sp-muted"><?= e($after['reason']) ?></div>
        <div class="sp-muted">申请金额 ￥<?= number_format((float) $after['amount'], 2) ?> · <?= date('Y-m-d H:i', (int) $after['created_at']) ?></div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <div class="card-head"><span>订单日志</span></div>
  <div style="padding:14px 18px" class="sp-timeline">
    <?php foreach ($logs as $l): ?>
      <div class="item"><b><?= date('m-d H:i:s', (int) $l['created_at']) ?></b><span>[<?= e($l['action']) ?>]</span><span><?= e($l['content']) ?></span><span class="sp-muted"><?= e($l['operator_type']) ?> #<?= (int) $l['operator_id'] ?></span></div>
    <?php endforeach; ?>
    <?php if (!$logs): ?><div class="sp-muted">暂无日志</div><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>
