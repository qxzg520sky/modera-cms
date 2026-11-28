<?php $pageTitle = $pageTitle ?? '商品管理'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/shop/asset/admin.css?v=1.0.0">
<?php if ($flash): ?><div class="sp-alert ok"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="sp-alert err"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-head">
    <span>商品列表 <span class="sp-muted">共 <?= (int) $pager['total'] ?> 个</span></span>
    <a class="btn" href="/admin/plugin/shop/product/edit">+ 新增商品</a>
  </div>
  <form method="get" action="/admin/plugin/shop/products" style="padding:12px 16px;display:flex;gap:10px;flex-wrap:wrap">
    <input name="keyword" value="<?= e($f['keyword']) ?>" placeholder="商品标题" style="padding:8px 10px;border:1px solid #d8e1ee;border-radius:8px">
    <select name="category_id" style="padding:8px 10px;border:1px solid #d8e1ee;border-radius:8px">
      <option value="0">全部分类</option>
      <?php foreach ($cats as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $f['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
    </select>
    <select name="status" style="padding:8px 10px;border:1px solid #d8e1ee;border-radius:8px">
      <option value="">全部状态</option>
      <option value="1" <?= $f['status'] === '1' ? 'selected' : '' ?>>在售</option>
      <option value="0" <?= $f['status'] === '0' ? 'selected' : '' ?>>下架</option>
    </select>
    <button class="btn" type="submit">筛选</button>
  </form>
  <div class="sp-table-wrap">
    <table class="table">
      <thead><tr><th>商品</th><th>分类</th><th>价格</th><th>库存</th><th>销量</th><th>状态</th><th>操作</th></tr></thead>
      <tbody>
      <?php if (!$list): ?>
        <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:28px">暂无商品</td></tr>
      <?php else: foreach ($list as $p): ?>
        <tr>
          <td style="display:flex;gap:10px;align-items:center">
            <div style="width:46px;height:46px;border-radius:8px;background:linear-gradient(135deg,#1558b0,#16a6c9);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800"><?= mb_substr($p['title'], 0, 1) ?></div>
            <div><b><?= e($p['title']) ?></b><div class="sp-muted"><?= e($p['subtitle']) ?></div></div>
          </td>
          <td><?= e($p['category_name'] ?: '未分类') ?></td>
          <td>￥<?= number_format((float) $p['price'], 2) ?><div class="sp-muted">市场价 ￥<?= number_format((float) $p['market_price'], 2) ?></div></td>
          <td><?= (int) $p['stock'] ?></td>
          <td><?= (int) $p['sales'] ?></td>
          <td><span class="sp-badge <?= (int) $p['status'] === 1 ? 'ok' : '' ?>"><?= (int) $p['status'] === 1 ? '在售' : '下架' ?></span></td>
          <td style="white-space:nowrap">
            <a class="btn btn-sm" href="/admin/plugin/shop/product/edit/<?= (int) $p['id'] ?>">编辑</a>
            <form method="post" action="/admin/plugin/shop/product/status" style="display:inline">
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <input type="hidden" name="status" value="<?= (int) $p['status'] === 1 ? 0 : 1 ?>">
              <button class="btn btn-sm" type="submit"><?= (int) $p['status'] === 1 ? '下架' : '上架' ?></button>
            </form>
            <form method="post" action="/admin/plugin/shop/product/delete" style="display:inline" onsubmit="return confirm('删除商品？历史订单明细不受影响')">
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <button class="btn btn-sm sp-danger" type="submit">删除</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pager['pages'] > 1): ?>
  <div class="sp-pager">
    <?php for ($i = 1; $i <= $pager['pages']; $i++): ?>
      <a class="<?= $i === $pager['page'] ? 'on' : '' ?>" href="/admin/plugin/shop/products?page=<?= $i ?>&keyword=<?= urlencode($f['keyword']) ?>&category_id=<?= (int) $f['category_id'] ?>&status=<?= urlencode($f['status']) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>
