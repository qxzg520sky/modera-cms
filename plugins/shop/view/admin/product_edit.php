<?php $pageTitle = $pageTitle ?? '编辑商品'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/shop/asset/admin.css?v=1.0.0">
<?php if ($flash): ?><div class="sp-alert ok"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="sp-alert err"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-head"><span><?= $row ? '编辑商品' : '新增商品' ?></span><a class="btn btn-sm" href="/admin/plugin/shop/products">← 返回列表</a></div>
  <form method="post" action="/admin/plugin/shop/product/save" style="padding:16px 18px">
    <input type="hidden" name="id" value="<?= (int) ($row['id'] ?? 0) ?>">
    <div class="sp-form-row">
      <div class="sp-field"><label>商品标题 *</label><input name="title" required value="<?= e($row['title'] ?? '') ?>"></div>
      <div class="sp-field"><label>副标题</label><input name="subtitle" value="<?= e($row['subtitle'] ?? '') ?>"></div>
      <div class="sp-field"><label>分类</label>
        <select name="category_id">
          <option value="0">未分类</option>
          <?php foreach ($cats as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) ($row['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="sp-field"><label>销售价 *</label><input name="price" type="number" step="0.01" min="0.01" required value="<?= e($row['price'] ?? '0.00') ?>"></div>
      <div class="sp-field"><label>市场价</label><input name="market_price" type="number" step="0.01" min="0" value="<?= e($row['market_price'] ?? '0.00') ?>"></div>
      <div class="sp-field"><label>商品总库存</label><input name="stock" type="number" min="0" value="<?= (int) ($row['stock'] ?? 0) ?>"><span class="sp-muted">实际下单扣 SKU 库存，这里用于列表展示</span></div>
      <div class="sp-field"><label>封面图 URL</label><input name="cover" value="<?= e($row['cover'] ?? '') ?>" placeholder="可留空，前台用渐变色占位"></div>
      <div class="sp-field"><label>状态</label>
        <select name="status"><option value="1" <?= (int) ($row['status'] ?? 1) === 1 ? 'selected' : '' ?>>在售</option><option value="0" <?= (int) ($row['status'] ?? 1) === 0 ? 'selected' : '' ?>>下架</option></select>
      </div>
      <div class="sp-field"><label>推荐</label>
        <select name="is_hot"><option value="0" <?= (int) ($row['is_hot'] ?? 0) === 0 ? 'selected' : '' ?>>普通</option><option value="1" <?= (int) ($row['is_hot'] ?? 0) === 1 ? 'selected' : '' ?>>热门</option></select>
      </div>
      <div class="sp-field"><label>排序（越大越靠前）</label><input name="sort" type="number" value="<?= (int) ($row['sort'] ?? 0) ?>"></div>
    </div>

    <div class="sp-field" style="margin-top:14px"><label>轮播图 URL（一行一个）</label><textarea name="images"><?php
      $imgs = json_decode((string) ($row['images'] ?? '[]'), true) ?: [];
      echo e(implode("\n", $imgs));
    ?></textarea></div>
    <div class="sp-field" style="margin-top:14px"><label>商品详情（支持 HTML）</label><textarea name="detail" style="min-height:180px"><?= e($row['detail'] ?? '') ?></textarea></div>

    <h3 style="margin:22px 0 10px;font-size:15px">多规格 SKU / 库存</h3>
    <div class="sp-muted" style="margin-bottom:10px">规格填写示例：<code>颜色:中国红,尺寸:M</code>。SKU 编码必须唯一，库存扣减以 SKU 为准。</div>
    <div id="skuRows">
      <?php if ($skus): foreach ($skus as $s): ?>
        <div class="sp-sku-row">
          <input type="number" name="sku_id[]" value="<?= (int) $s['id'] ?>" placeholder="ID" readonly>
          <input name="sku_code[]" value="<?= e($s['sku_code']) ?>" placeholder="SKU 编码 *">
          <input name="sku_spec[]" value="<?= e(\plugins\shop\service\Shop::specText((string) $s['spec_json'])) ?>" placeholder="颜色:红,尺寸:M">
          <input name="sku_price[]" type="number" step="0.01" value="<?= e($s['price']) ?>" placeholder="价格">
          <input name="sku_stock[]" type="number" value="<?= (int) $s['stock'] ?>" placeholder="库存">
          <input name="sku_warn[]" type="number" value="<?= (int) $s['warn_stock'] ?>" placeholder="预警">
          <span></span>
        </div>
      <?php endforeach; endif; ?>
    </div>
    <div class="sp-actions"><button type="button" class="btn btn-sm" onclick="addSkuRow()">+ 添加 SKU 行</button></div>

    <div class="sp-actions" style="margin-top:22px">
      <button class="btn" type="submit">保存商品</button>
      <a class="btn" href="/admin/plugin/shop/products">取消</a>
    </div>
  </form>
</div>

<script>
function addSkuRow(){
  var html = '<div class="sp-sku-row">'
    + '<input type="number" name="sku_id[]" value="0" placeholder="ID" readonly>'
    + '<input name="sku_code[]" placeholder="SKU 编码 *">'
    + '<input name="sku_spec[]" placeholder="颜色:红,尺寸:M">'
    + '<input name="sku_price[]" type="number" step="0.01" placeholder="价格">'
    + '<input name="sku_stock[]" type="number" placeholder="库存">'
    + '<input name="sku_warn[]" type="number" placeholder="预警">'
    + '<span></span></div>';
  document.getElementById('skuRows').insertAdjacentHTML('beforeend', html);
}
</script>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>
