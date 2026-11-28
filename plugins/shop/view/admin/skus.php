<?php $pageTitle = $pageTitle ?? '库存管理'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/shop/asset/admin.css?v=1.0.0">
<?php if ($flash): ?><div class="sp-alert ok"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="sp-alert err"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-head"><span>SKU 库存 <span class="sp-muted">共 <?= (int) $pager['total'] ?> 条</span></span></div>
  <form method="get" style="padding:12px 16px;display:flex;gap:10px;flex-wrap:wrap">
    <input name="keyword" value="<?= e($f['keyword']) ?>" placeholder="商品标题 / SKU 编码" style="padding:8px 10px;border:1px solid #d8e1ee;border-radius:8px">
    <label style="display:flex;gap:6px;align-items:center;font-size:13px"><input type="checkbox" name="warn" value="1" <?= $f['warn'] ? 'checked' : '' ?>> 只看库存预警</label>
    <button class="btn">筛选</button>
  </form>
  <div class="sp-table-wrap">
    <table class="table">
      <thead><tr><th>商品</th><th>SKU</th><th>规格</th><th>价格</th><th>库存</th><th>预警线</th><th>调整</th></tr></thead>
      <tbody>
      <?php if (!$list): ?><tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:26px">暂无 SKU</td></tr><?php endif; ?>
      <?php foreach ($list as $s): ?>
        <tr>
          <td><b><?= e($s['title']) ?></b><div class="sp-muted">商品 #<?= (int) $s['product_id'] ?> <?= (int) $s['pstatus'] === 1 ? '在售' : '已下架' ?></div></td>
          <td><span class="sp-code"><?= e($s['sku_code']) ?></span></td>
          <td><?= e(\plugins\shop\service\Shop::specText((string) $s['spec_json'])) ?: '<span class="sp-muted">单规格</span>' ?></td>
          <td>￥<?= number_format((float) $s['price'], 2) ?></td>
          <td class="<?= (int) $s['stock'] <= (int) $s['warn_stock'] ? 'sp-danger' : '' ?>"><b><?= (int) $s['stock'] ?></b></td>
          <td><?= (int) $s['warn_stock'] ?></td>
          <td>
            <form method="post" action="/admin/plugin/shop/sku/stock" style="display:flex;gap:6px">
              <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
              <select name="op" style="border:1px solid #d8e1ee;border-radius:8px;padding:6px"><option value="add">+</option><option value="set">设为</option></select>
              <input name="num" type="number" min="0" value="0" style="width:70px;border:1px solid #d8e1ee;border-radius:8px;padding:6px">
              <button class="btn btn-sm">确定</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pager['pages'] > 1): ?>
  <div class="sp-pager">
    <?php for ($i = 1; $i <= $pager['pages']; $i++): ?><a class="<?= $i === $pager['page'] ? 'on' : '' ?>" href="/admin/plugin/shop/skus?page=<?= $i ?>&keyword=<?= urlencode($f['keyword']) ?><?= $f['warn'] ? '&warn=1' : '' ?>"><?= $i ?></a><?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>
