<?php $pageTitle = $pageTitle ?? '售后审核'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/shop/asset/admin.css?v=1.0.0">
<?php if ($flash): ?><div class="sp-alert ok"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="sp-alert err"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-head"><span>售后申请</span></div>
  <form method="get" style="padding:12px 16px;display:flex;gap:10px">
    <select name="status" style="padding:8px 10px;border:1px solid #d8e1ee;border-radius:8px">
      <option value="">全部状态</option>
      <?php foreach (['pending' => '待审核', 'approved' => '已同意', 'rejected' => '已拒绝', 'refunded' => '已退款'] as $k => $v): ?>
        <option value="<?= $k ?>" <?= $f['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn">筛选</button>
  </form>
  <div class="sp-table-wrap">
    <table class="table">
      <thead><tr><th>售后单</th><th>订单</th><th>会员</th><th>原因</th><th>金额</th><th>状态</th><th>操作</th></tr></thead>
      <tbody>
      <?php if (!$list): ?><tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:28px">暂无售后</td></tr><?php endif; ?>
      <?php foreach ($list as $a): ?>
        <tr>
          <td><span class="sp-code"><?= e($a['after_sale_no']) ?></span><div class="sp-muted"><?= date('m-d H:i', (int) $a['created_at']) ?></div></td>
          <td><a href="/admin/plugin/shop/order/<?= e($a['order_no']) ?>"><?= e($a['order_no']) ?></a><div class="sp-muted"><?= e(\plugins\shop\service\Shop::orderStatusText((string) $a['ostatus'])) ?></div></td>
          <td><?= e($a['member_name']) ?> <span class="sp-muted">#<?= (int) $a['member_id'] ?></span></td>
          <td style="max-width:260px"><?= e($a['reason']) ?><?php if ($a['admin_remark']): ?><div class="sp-muted">审核备注：<?= e($a['admin_remark']) ?></div><?php endif; ?></td>
          <td>￥<?= number_format((float) $a['amount'], 2) ?></td>
          <td><span class="sp-badge <?= $a['status'] === 'refunded' ? 'ok' : ($a['status'] === 'pending' ? 'warn' : '') ?>"><?= e(\plugins\shop\service\Shop::afterStatusText((string) $a['status'])) ?></span></td>
          <td>
            <?php if ($a['status'] === 'pending'): ?>
              <form method="post" action="/admin/plugin/shop/aftersale/audit" style="display:flex;gap:6px;flex-wrap:wrap" onsubmit="return confirm('同意会立即退款并回滚库存，确认？')">
                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                <input name="admin_remark" placeholder="审核备注" style="width:130px;border:1px solid #d8e1ee;border-radius:8px;padding:6px">
                <button class="btn btn-sm" name="action" value="approve">同意并退款</button>
                <button class="btn btn-sm sp-danger" name="action" value="reject">拒绝</button>
              </form>
            <?php else: ?>
              <span class="sp-muted">已处理</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>
