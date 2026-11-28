<?php $pageTitle = $pageTitle ?? '商城总览'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/shop/asset/admin.css?v=1.0.0">

<?php if ($flash): ?><div class="sp-alert ok"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="sp-alert err"><?= e($error) ?></div><?php endif; ?>

<div class="sp-admin-stats">
  <div class="sp-stat"><div class="l">商品总数</div><div class="n blue"><?= (int) $stats['products'] ?></div><div class="sp-muted">在售 <?= (int) $stats['on_sale'] ?></div></div>
  <div class="sp-stat"><div class="l">订单总数</div><div class="n"><?= (int) $stats['orders'] ?></div><div class="sp-muted">今日 <?= (int) $stats['today_orders'] ?></div></div>
  <div class="sp-stat"><div class="l">已付款金额</div><div class="n green">￥<?= number_format((float) $stats['paid_amount'], 2) ?></div></div>
  <div class="sp-stat"><div class="l">待付款</div><div class="n warn"><?= (int) $stats['pending'] ?></div></div>
  <div class="sp-stat"><div class="l">待审售后</div><div class="n warn"><?= (int) $stats['after_sales'] ?></div></div>
  <div class="sp-stat"><div class="l">库存预警 SKU</div><div class="n warn"><?= (int) $stats['low_stock'] ?></div></div>
</div>

<?php if (!$memberOk): ?>
<div class="sp-alert err">会员插件未启用或未安装，商城无法读取会员折扣、余额、积分和收货地址。请先到 <a href="/admin/plugin/member">会员插件</a> 启用。</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1.4fr .8fr;gap:16px">
  <div class="card">
    <div class="card-head"><span>最近订单</span><a class="btn btn-sm" href="/admin/plugin/shop/orders">全部订单 →</a></div>
    <div class="sp-table-wrap">
      <table class="table">
        <thead><tr><th>订单号</th><th>会员</th><th>应付</th><th>状态</th><th>累计消费</th><th></th></tr></thead>
        <tbody>
        <?php if (!$orders): ?>
          <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:26px">还没有订单，前台 <a href="/shop" target="_blank">/shop</a> 下单试试</td></tr>
        <?php else: foreach ($orders as $o): ?>
          <tr>
            <td><span class="sp-code"><?= e($o['order_no']) ?></span><div class="sp-muted"><?= date('m-d H:i', (int) $o['created_at']) ?></div></td>
            <td><?= e($o['member_name']) ?><div class="sp-muted">#<?= (int) $o['member_id'] ?> · 业务员 <?= (int) $o['staff_id'] ?></div></td>
            <td><b>￥<?= number_format((float) $o['payable_amount'], 2) ?></b><div class="sp-muted"><?= e($o['pay_type'] ?: '未支付') ?></div></td>
            <td><span class="sp-badge <?= in_array($o['status'], ['paid', 'shipped', 'completed'], true) ? 'ok' : ($o['status'] === 'pending_payment' ? 'warn' : '') ?>"><?= e(\plugins\shop\service\Shop::orderStatusText((string) $o['status'])) ?></span></td>
            <td><?= [0 => '未同步', 1 => '调用中', 2 => '已同步', 3 => '不确定'][(int) $o['spend_state']] ?? '-' ?></td>
            <td><a class="btn btn-sm" href="/admin/plugin/shop/order/<?= e($o['order_no']) ?>">详情</a></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><span>库存预警</span><a class="btn btn-sm" href="/admin/plugin/shop/skus?warn=1">全部 →</a></div>
    <div style="padding:12px 16px;display:grid;gap:10px">
      <?php if (!$low): ?><div class="sp-muted">暂无预警，库存健康</div><?php endif; ?>
      <?php foreach ($low as $s): ?>
        <div style="display:flex;justify-content:space-between;gap:8px;font-size:12.5px">
          <span><?= e($s['title']) ?><div class="sp-muted"><?= e($s['sku_code']) ?></div></span>
          <b class="sp-danger">剩 <?= (int) $s['stock'] ?></b>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="padding:0 16px 16px"><a class="btn" style="width:100%;text-align:center" href="/admin/plugin/shop/settings">商城设置</a></div>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <div class="card-head"><span>解耦提醒（写给自己看）</span></div>
  <div style="padding:14px 18px;font-size:13px;color:#475569;line-height:1.8">
    商城所有会员能力都走 <code>MemberBridge</code>：默认调用 <code>plugins\member\service\Member</code>，也可以在设置里切到 <code>/member/api/*</code> 签名模式。商城表只有 <code>shop_*</code>，从来不碰 <code>tp_member*</code>。<br>
    累计消费状态 <b>3=不确定</b> 的订单不要直接重试，先去订单详情点「对账」，确认会员 <code>total_spent</code> 是否已包含本单，再决定补标记还是重试。
  </div>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>
