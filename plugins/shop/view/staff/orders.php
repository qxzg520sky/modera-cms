<?php
$pageTitle = $pageTitle ?? '我的客户订单';
$staffName = $staff['real_name'] ?? ($staff['username'] ?? '业务员');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> - <?= e($cfg['shop_name'] ?? '企业商城') ?></title>
<link rel="stylesheet" href="/shop/asset/shop.css?v=1.0.0">
</head>
<body>
<header class="sp-nav"><div class="in">
  <a class="sp-logo" href="/shop"><?= e($cfg['shop_name'] ?? '企业商城') ?><span> · STAFF</span></a>
  <div class="links">
    <span class="user">业务员：<?= e($staffName) ?> · 已付款合计 ￥<?= number_format((float) $sum, 2) ?></span>
    <a href="/member/staff">会员工作台</a>
    <a href="/member/staff/logout">退出</a>
  </div>
</div></header>
<main class="sp-wrap">
<div class="sp-section">
  <h2 style="margin:0 0 6px;font-size:20px">我的客户订单</h2>
  <div style="font-size:12.5px;color:#94a3b8;margin-bottom:14px">只显示绑定在你名下的会员下单记录（staff_id = <?= (int) $staff['id'] ?>）。别人的客户订单在数据层就查不到。</div>
  <div class="sp-tabs">
    <a class="<?= $f['status'] === '' ? 'on' : '' ?>" href="/shop/staff/orders">全部</a>
    <?php foreach (['pending_payment' => '待付款', 'paid' => '已付款', 'shipped' => '已发货', 'completed' => '已完成', 'refunded' => '已退款'] as $k => $v): ?>
      <a class="<?= $f['status'] === $k ? 'on' : '' ?>" href="/shop/staff/orders?status=<?= $k ?>"><?= $v ?></a>
    <?php endforeach; ?>
  </div>

  <?php if (!$list): ?>
    <div class="sp-card sp-empty">还没有订单</div>
  <?php else: ?>
    <div class="sp-card" style="padding:8px 16px 14px;overflow:auto">
      <table class="sp-table">
        <thead><tr><th>订单号</th><th>会员</th><th>金额</th><th>状态</th><th>收货</th><th>时间</th></tr></thead>
        <tbody>
        <?php foreach ($list as $o): ?>
          <tr>
            <td><span style="font-family:Consolas,monospace;font-size:12px"><?= e($o['order_no']) ?></span></td>
            <td><?= e($o['member_name']) ?><div style="font-size:11.5px;color:#94a3b8">#<?= (int) $o['member_id'] ?></div></td>
            <td><b>￥<?= number_format((float) $o['payable_amount'], 2) ?></b></td>
            <td><span class="sp-status <?= in_array($o['status'], ['paid', 'shipped', 'completed'], true) ? 'ok' : ($o['status'] === 'pending_payment' ? 'warn' : '') ?>"><?= e(\plugins\shop\service\Shop::orderStatusText((string) $o['status'])) ?></span></td>
            <td style="font-size:12px;color:#64748b"><?= e($o['receiver']) ?> <?= e($o['mobile']) ?><div><?= e($o['province'] . $o['city']) ?></div></td>
            <td style="font-size:12px"><?= date('Y-m-d H:i', (int) $o['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <?php if ($pager['pages'] > 1): ?>
  <div class="sp-pager">
    <?php for ($i = 1; $i <= $pager['pages']; $i++): ?><a class="<?= $i === $pager['page'] ? 'on' : '' ?>" href="/shop/staff/orders?page=<?= $i ?>&status=<?= urlencode($f['status']) ?>"><?= $i ?></a><?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
</main>
<footer class="sp-foot">业务员只能查看，不可改价/发货；需要操作请联系管理员。</footer>
</body>
</html>
