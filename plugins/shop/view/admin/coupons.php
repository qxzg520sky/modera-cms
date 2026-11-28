<?php $pageTitle = $pageTitle ?? '优惠券'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/shop/asset/admin.css?v=1.0.0">
<?php if ($flash): ?><div class="sp-alert ok"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="sp-alert err"><?= e($error) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1.15fr .85fr;gap:16px">
  <div class="card">
    <div class="card-head"><span>优惠券列表</span></div>
    <div class="sp-table-wrap">
      <table class="table">
        <thead><tr><th>名称 / 券码</th><th>优惠</th><th>门槛</th><th>已发/总量</th><th>有效期</th><th>发放</th></tr></thead>
        <tbody>
        <?php if (!$list): ?><tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:26px">暂无优惠券</td></tr><?php endif; ?>
        <?php foreach ($list as $c): ?>
          <tr>
            <td><b><?= e($c['name']) ?></b><div class="sp-code"><?= e($c['code']) ?></div></td>
            <td><?= $c['type'] === 'percent' ? '打 ' . e($c['value']) . ' 折' : '减 ￥' . number_format((float) $c['value'], 2) ?></td>
            <td>满 ￥<?= number_format((float) $c['min_amount'], 2) ?></td>
            <td><?= (int) $c['received'] ?> / <?= (int) $c['total'] > 0 ? (int) $c['total'] : '不限' ?><div class="sp-muted">已用 <?= (int) $c['used'] ?></div></td>
            <td class="sp-muted"><?= (int) $c['start_at'] ? date('m-d', (int) $c['start_at']) : '即日' ?> ~ <?= (int) $c['end_at'] ? date('m-d H:i', (int) $c['end_at']) : '长期' ?></td>
            <td>
              <form method="post" action="/admin/plugin/shop/coupon/grant" style="display:grid;gap:6px">
                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                <input name="member_ids" placeholder="会员ID，逗号分隔" style="width:150px;border:1px solid #d8e1ee;border-radius:8px;padding:6px">
                <button class="btn btn-sm">发放</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><span>新增 / 修改优惠券</span></div>
    <form method="post" action="/admin/plugin/shop/coupon/save" style="padding:16px 18px;display:grid;gap:12px">
      <div class="sp-field"><label>券 ID（新增填 0）</label><input name="id" type="number" value="0"></div>
      <div class="sp-field"><label>名称 *</label><input name="name" required placeholder="新客立减券"></div>
      <div class="sp-field"><label>券码 *</label><input name="code" required placeholder="NEW10（唯一，大写）"></div>
      <div class="sp-field"><label>类型</label><select name="type"><option value="fixed">满减券（固定金额）</option><option value="percent">折扣券（0~1）</option></select></div>
      <div class="sp-field"><label>优惠值 *</label><input name="value" type="number" step="0.01" required placeholder="满减填 10；折扣填 0.9"></div>
      <div class="sp-field"><label>使用门槛（满多少元）</label><input name="min_amount" type="number" step="0.01" value="0"></div>
      <div class="sp-field"><label>发放总量（0=不限）</label><input name="total" type="number" min="0" value="0"></div>
      <div class="sp-field"><label>开始时间</label><input name="start_at" type="datetime-local"></div>
      <div class="sp-field"><label>结束时间</label><input name="end_at" type="datetime-local"></div>
      <div class="sp-field"><label>状态</label><select name="status"><option value="1">启用</option><option value="0">停用</option></select></div>
      <div class="sp-actions"><button class="btn">保存优惠券</button></div>
    </form>
    <div class="sp-muted" style="padding:0 18px 16px">发放按会员 ID 精确发放；领取/使用记录在 shop_coupon_member，会员来源不读会员表。</div>
  </div>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>
