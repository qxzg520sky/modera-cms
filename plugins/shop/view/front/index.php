<?php include __DIR__ . '/_head.php'; ?>
<div class="sp-hero">
  <div class="sp-wrap">
    <h1><?= e($shopCfg['shop_name'] ?? '企业商城') ?></h1>
    <p><?= e($shopCfg['shop_notice'] ?? '会员专属价，余额与积分可直接抵扣。') ?></p>
    <?php if ($me): ?><p style="margin-top:10px;font-size:12.5px;color:#a9cdf0">当前：<?= e($me['real_name'] ?: $me['username']) ?> · <?= e($me['level_name']) ?> · 等级折扣 <?= number_format((float) $me['discount'], 2) ?> · 余额 ￥<?= number_format((float) $me['balance'], 2) ?> · 积分 <?= (int) $me['points'] ?></p><?php endif; ?>
  </div>
</div>

<div class="sp-section">
  <div class="sp-filter">
    <form method="get" action="/shop" style="display:flex;gap:9px;flex-wrap:wrap;align-items:center">
      <input name="keyword" value="<?= e($f['keyword']) ?>" placeholder="搜索商品">
      <select name="category_id">
        <option value="0">全部分类</option>
        <?php foreach ($cats as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $f['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
      </select>
      <select name="sort">
        <option value="default" <?= $f['sort'] === 'default' ? 'selected' : '' ?>>默认排序</option>
        <option value="sales" <?= $f['sort'] === 'sales' ? 'selected' : '' ?>>销量优先</option>
        <option value="price_asc" <?= $f['sort'] === 'price_asc' ? 'selected' : '' ?>>价格从低到高</option>
        <option value="price_desc" <?= $f['sort'] === 'price_desc' ? 'selected' : '' ?>>价格从高到低</option>
      </select>
      <button class="sp-btn">筛选</button>
    </form>
  </div>

  <?php if (!$list): ?>
    <div class="sp-card sp-empty">没有找到商品<?php if ($f['keyword']): ?>，换个关键词试试<?php endif; ?></div>
  <?php else: ?>
    <div class="sp-grid">
      <?php foreach ($list as $p): ?>
        <a class="sp-card sp-product" href="/shop/product/<?= (int) $p['id'] ?>">
          <div class="sp-cover"><?php if ($p['cover']): ?><img src="<?= e($p['cover']) ?>" alt=""><?php else: ?><?= e(mb_substr($p['title'], 0, 1)) ?><?php endif; ?></div>
          <div class="sp-pbody">
            <div class="sp-ptitle"><?= e($p['title']) ?></div>
            <div class="sp-psub"><?= e($p['subtitle']) ?></div>
            <div class="sp-meta">
              <?php if ((int) $p['is_hot'] === 1): ?><span class="sp-tag hot">热销</span><?php endif; ?>
              <span class="sp-tag">库存 <?= (int) $p['stock'] ?></span>
              <span>已售 <?= (int) $p['sales'] ?></span>
            </div>
            <div class="sp-price">￥<?= number_format((float) $p['price'], 2) ?><small>市场价 ￥<?= number_format((float) $p['market_price'], 2) ?></small></div>
            <?php if ($me && (float) $me['discount'] < 1): ?>
              <div class="sp-meta"><span class="sp-tag">你的等级价约 ￥<?= number_format((float) $p['price'] * (float) $me['discount'], 2) ?></span></div>
            <?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($pager['pages'] > 1): ?>
  <div class="sp-pager">
    <?php for ($i = 1; $i <= $pager['pages']; $i++): ?>
      <a class="<?= $i === $pager['page'] ? 'on' : '' ?>" href="/shop?page=<?= $i ?>&keyword=<?= urlencode($f['keyword']) ?>&category_id=<?= (int) $f['category_id'] ?>&sort=<?= urlencode($f['sort']) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <div style="margin-top:18px;font-size:12px;color:#94a3b8">共 <?= (int) $pager['total'] ?> 件商品。下单结算时会自动读取会员等级折扣、收货地址、余额和积分。</div>
</div>
<?php include __DIR__ . '/_foot.php'; ?>
