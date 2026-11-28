<?php include __DIR__ . '/_head.php'; ?>
<div class="sp-crumb"><a href="/shop">商城</a> / <?= e($catName) ?> / <?= e($p['title']) ?></div>

<div class="sp-card" style="padding:22px">
  <div class="sp-detail">
    <div class="big"><?php if ($p['cover']): ?><img src="<?= e($p['cover']) ?>" alt=""><?php else: ?><?= e(mb_substr($p['title'], 0, 1)) ?><?php endif; ?></div>
    <div>
      <h1 style="margin:0 0 8px;font-size:23px"><?= e($p['title']) ?></h1>
      <div style="color:#64748b;font-size:13px"><?= e($p['subtitle']) ?></div>
      <div style="margin:16px 0 8px;font-size:12px;color:#94a3b8">市场价 <span style="text-decoration:line-through">￥<?= number_format((float) $p['market_price'], 2) ?></span> · 已售 <?= (int) $p['sales'] ?> · 库存 <?= (int) $p['stock'] ?></div>
      <div class="sp-price" id="priceBox">￥<?= number_format((float) $p['price'], 2) ?></div>
      <?php if ($me && (float) $me['discount'] < 1): ?>
        <div class="sp-note ok">你的等级「<?= e($me['level_name']) ?>」享 <?= number_format((float) $me['discount'], 2) ?> 折，结算时自动计算，无需领券。</div>
      <?php else: ?>
        <div class="sp-note warn">当前未登录或为普通等级，按原价结算。<a href="/member/login?redirect=<?= urlencode('/shop/product/' . (int) $p['id']) ?>">会员登录</a> 可享等级折扣。</div>
      <?php endif; ?>

      <form method="post" action="/shop/cart/add" id="buyForm">
        <?php if ($skus): ?>
          <div style="font-size:12.5px;color:#475569;margin-top:6px">选择规格</div>
          <div class="sp-sku">
            <?php foreach ($skus as $i => $s): ?>
              <input type="radio" id="sku<?= (int) $s['id'] ?>" name="sku_id" value="<?= (int) $s['id'] ?>" data-price="<?= e($s['price']) ?>" data-stock="<?= (int) $s['stock'] ?>" <?= $i === 0 ? 'checked' : '' ?>>
              <label for="sku<?= (int) $s['id'] ?>"><?= e(\plugins\shop\service\Shop::specText((string) $s['spec_json'])) ?: e($s['sku_code']) ?><br><span style="font-size:11px;color:#94a3b8">￥<?= number_format((float) $s['price'], 2) ?> · 库存 <?= (int) $s['stock'] ?></span></label>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="sp-note err">该商品还没有可用 SKU，请先在后台补 SKU。</div>
        <?php endif; ?>

        <div class="sp-form-line">
          <span style="font-size:12.5px;color:#475569">数量</span>
          <div class="sp-qty"><button type="button" onclick="qty(-1)">−</button><input name="qty" id="qty" value="1" inputmode="numeric"><button type="button" onclick="qty(1)">+</button></div>
          <span style="font-size:12px;color:#94a3b8" id="stockTip"></span>
        </div>

        <?php if ($me): ?>
          <button class="sp-btn" type="submit" <?= $skus ? '' : 'disabled' ?>>加入购物车</button>
          <a class="sp-btn ghost" href="/shop/cart">去购物车</a>
        <?php else: ?>
          <a class="sp-btn" href="/member/login?redirect=<?= urlencode('/shop/product/' . (int) $p['id']) ?>">登录后购买</a>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<div class="sp-card" style="padding:22px;margin-top:18px">
  <h3 style="margin:0 0 12px;font-size:15px">商品详情</h3>
  <div style="font-size:13.5px;color:#334155;line-height:1.9"><?= $p['detail'] ?: '<span style="color:#94a3b8">暂无详情</span>' ?></div>
</div>

<?php if ($related): ?>
<div class="sp-section">
  <h3 style="font-size:15px">同类商品</h3>
  <div class="sp-grid">
    <?php foreach ($related as $r): ?>
      <a class="sp-card sp-product" href="/shop/product/<?= (int) $r['id'] ?>">
        <div class="sp-cover"><?php if ($r['cover']): ?><img src="<?= e($r['cover']) ?>" alt=""><?php else: ?><?= e(mb_substr($r['title'], 0, 1)) ?><?php endif; ?></div>
        <div class="sp-pbody"><div class="sp-ptitle"><?= e($r['title']) ?></div><div class="sp-price">￥<?= number_format((float) $r['price'], 2) ?></div></div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<script>
function qty(d){var i=document.getElementById('qty');var v=parseInt(i.value||'1')+d;var max=99;var r=document.querySelector('input[name=sku_id]:checked');if(r)max=parseInt(r.dataset.stock||'99');i.value=Math.max(1,Math.min(max||99,v));updateTip();}
function updateTip(){var r=document.querySelector('input[name=sku_id]:checked');if(!r)return;document.getElementById('stockTip').textContent='库存 '+r.dataset.stock+' 件';document.getElementById('priceBox').textContent='￥'+parseFloat(r.dataset.price).toFixed(2);}
document.querySelectorAll('input[name=sku_id]').forEach(function(x){x.addEventListener('change',updateTip)});
updateTip();
</script>
<?php include __DIR__ . '/_foot.php'; ?>
