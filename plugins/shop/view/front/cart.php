<?php include __DIR__ . '/_head.php'; ?>
<div class="sp-section">
  <h2 style="margin:0 0 16px;font-size:20px">购物车</h2>
  <?php if (!$items): ?>
    <div class="sp-card sp-empty">购物车是空的，<a href="/shop">去逛逛</a></div>
  <?php else: ?>
  <div class="sp-card" style="padding:8px 16px 16px">
    <table class="sp-table">
      <thead><tr><th style="width:40px">选</th><th>商品</th><th>单价</th><th>数量</th><th>小计</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): $sub = (float) $it['price'] * (int) $it['qty']; ?>
        <tr>
          <td>
            <form method="post" action="/shop/cart/update">
              <input type="hidden" name="id" value="<?= (int) $it['cart_id'] ?>">
              <input type="hidden" name="qty" value="<?= (int) $it['qty'] ?>">
              <input type="hidden" name="selected" value="<?= (int) $it['selected'] === 1 ? 0 : 1 ?>">
              <input type="checkbox" <?= (int) $it['selected'] === 1 ? 'checked' : '' ?> onchange="this.form.submit()">
            </form>
          </td>
          <td>
            <div class="sp-cartline">
              <div class="sp-thumb"><?php if ($it['cover']): ?><img src="<?= e($it['cover']) ?>" alt=""><?php else: ?><?= e(mb_substr($it['title'], 0, 1)) ?><?php endif; ?></div>
              <div><a href="/shop/product/<?= (int) $it['product_id'] ?>"><b><?= e($it['title']) ?></b></a>
                <div style="font-size:12px;color:#94a3b8"><?= e(\plugins\shop\service\Shop::specText((string) $it['spec_json'])) ?: '单规格' ?> · 库存 <?= (int) $it['stock'] ?></div>
              </div>
            </div>
          </td>
          <td>￥<?= number_format((float) $it['price'], 2) ?></td>
          <td>
            <form method="post" action="/shop/cart/update" class="sp-qty">
              <input type="hidden" name="id" value="<?= (int) $it['cart_id'] ?>">
              <input type="hidden" name="selected" value="<?= (int) $it['selected'] ?>">
              <button type="submit" name="qty" value="<?= max(1, (int) $it['qty'] - 1) ?>">−</button>
              <input value="<?= (int) $it['qty'] ?>" readonly>
              <button type="submit" name="qty" value="<?= (int) $it['qty'] + 1 ?>">+</button>
            </form>
          </td>
          <td><b>￥<?= number_format($sub, 2) ?></b></td>
          <td>
            <form method="post" action="/shop/cart/remove">
              <input type="hidden" name="id" value="<?= (int) $it['cart_id'] ?>">
              <button class="sp-btn ghost" style="padding:5px 10px">删除</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="sp-card" style="margin-top:14px;padding:16px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
    <div style="font-size:13px;color:#64748b">已选合计：<b style="color:#c2410c;font-size:20px">￥<?= number_format((float) $selected, 2) ?></b><span style="margin-left:10px">购物车合计 ￥<?= number_format((float) $goods, 2) ?></span></div>
    <div>
      <a class="sp-btn ghost" href="/shop">继续购物</a>
      <a class="sp-btn" href="/shop/checkout" <?= $selected <= 0 ? 'style="opacity:.5;pointer-events:none"' : '' ?>>去结算</a>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/_foot.php'; ?>
