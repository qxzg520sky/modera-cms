<?php $pageTitle = $pageTitle ?? '订单管理'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/shop/asset/admin.css?v=1.0.0">
<?php if ($flash): ?><div class="sp-alert ok"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="sp-alert err"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-head"><span>订单列表 <span class="sp-muted">共 <?= (int) $pager['total'] ?> 单</span></span>
    <a class="btn" href="/admin/plugin/shop/order/export?keyword=<?= urlencode($f['keyword']) ?>&status=<?= urlencode($f['status']) ?>&staff_id=<?= (int) $f['staff_id'] ?>&spend_state=<?= urlencode($f['spend_state']) ?>">导出 Excel</a>
  </div>
  <form method="get" style="padding:12px 16px;display:flex;gap:10px;flex-wrap:wrap">
    <input name="keyword" value="<?= e($f['keyword']) ?>" placeholder="订单号 / 会员 / 手机" style="padding:8px 10px;border:1px solid #d8e1ee;border-radius:8px">
    <select name="status" style="padding:8px 10px;border:1px solid #d8e1ee;border-radius:8px">
      <option value="">全部状态</option>
      <?php foreach (['pending_payment' => '待付款', 'paying' => '支付中', 'paid' => '已付款', 'shipped' => '已发货', 'completed' => '已完成', 'closed' => '已关闭', 'refunded' => '已退款'] as $k => $v): ?>
        <option value="<?= $k ?>" <?= $f['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
      <?php endforeach; ?>
    </select>
    <select name="spend_state" style="padding:8px 10px;border:1px solid #d8e1ee;border-radius:8px">
      <option value="">累计消费状态</option>
      <?php foreach ([0 => '未同步', 1 => '调用中', 2 => '已同步', 3 => '不确定'] as $k => $v): ?>
        <option value="<?= $k ?>" <?= $f['spend_state'] === (string) $k ? 'selected' : '' ?>><?= $v ?></option>
      <?php endforeach; ?>
    </select>
    <input name="staff_id" type="number" value="<?= (int) $f['staff_id'] ?: '' ?>" placeholder="业务员ID" style="width:110px;padding:8px 10px;border:1px solid #d8e1ee;border-radius:8px">
    <button class="btn">筛选</button>
  </form>
  <div class="sp-table-wrap">
    <table class="table">
      <thead><tr><th>订单号</th><th>会员 / 业务员</th><th>金额</th><th>状态</th><th>支付</th><th>累计消费</th><th>时间</th><th></th></tr></thead>
      <tbody>
      <?php if (!$list): ?><tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:28px">暂无订单</td></tr><?php endif; ?>
      <?php foreach ($list as $o): ?>
        <tr>
          <td><span class="sp-code"><?= e($o['order_no']) ?></span><div class="sp-muted">biz:<?= e($o['biz_no']) ?></div></td>
          <td><?= e($o['member_name']) ?><div class="sp-muted">#<?= (int) $o['member_id'] ?> · 业务员 <?= (int) $o['staff_id'] ?></div></td>
          <td><b>￥<?= number_format((float) $o['payable_amount'], 2) ?></b><div class="sp-muted">原价 ￥<?= number_format((float) $o['goods_amount'], 2) ?> / 余额 ￥<?= number_format((float) $o['balance_amount'], 2) ?></div></td>
          <td><span class="sp-badge <?= in_array($o['status'], ['paid', 'shipped', 'completed'], true) ? 'ok' : ($o['status'] === 'pending_payment' ? 'warn' : '') ?>"><?= e(\plugins\shop\service\Shop::orderStatusText((string) $o['status'])) ?></span></td>
          <td><?= e($o['pay_type'] ?: '-') ?></td>
          <td><?php $ss = (int) $o['spend_state']; ?>
            <span class="sp-badge <?= $ss === 2 ? 'ok' : ($ss === 3 ? 'danger' : 'warn') ?>"><?= [0 => '未同步', 1 => '调用中', 2 => '已同步', 3 => '不确定'][$ss] ?? $ss ?></span>
          </td>
          <td><?= date('Y-m-d H:i', (int) $o['created_at']) ?></td>
          <td><a class="btn btn-sm" href="/admin/plugin/shop/order/<?= e($o['order_no']) ?>">详情</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pager['pages'] > 1): ?>
  <div class="sp-pager">
    <?php for ($i = 1; $i <= $pager['pages']; $i++): ?>
      <a class="<?= $i === $pager['page'] ? 'on' : '' ?>" href="/admin/plugin/shop/orders?page=<?= $i ?>&keyword=<?= urlencode($f['keyword']) ?>&status=<?= urlencode($f['status']) ?>&staff_id=<?= (int) $f['staff_id'] ?>&spend_state=<?= urlencode($f['spend_state']) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>
