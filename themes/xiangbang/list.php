<?php include __DIR__ . '/_top.php'; ?>
<?php
$cat      = $cat ?? ['name' => lang('栏目', '栏目'), 'slug' => '', 'seo_description' => ''];
$articles = $articles ?? [];
$subNav   = $subNav ?? [];
$total    = $total ?? count($articles);
$pager    = $pager ?? '';

$catName = (string) ml($cat, 'name');
$catSlug = (string) ($cat['slug'] ?? '');
$rootSlug = (string) ($subNav[0]['slug'] ?? $catSlug);
$isProduct = in_array($rootSlug, ['product', 'products', 'solution'], true);
$isNews = in_array($rootSlug, ['news', 'article', 'blog'], true) || !$isProduct;
$catDesc = trim((string) ml($cat, 'seo_description'));
$catDesc = $catDesc !== '' ? $catDesc : '从产品、方案到服务，把每一个可交付的细节，整理成清晰的选择。';
$crumbs = [['name' => lang('首页', '首页'), 'url' => lang_path('/')], ['name' => $catName]];
xb_page_hero($catName, $catDesc, $crumbs, lang('PRODUCT & SOLUTIONS', 'PRODUCT & SOLUTIONS'));
?>

<nav class="xb-subnav" aria-label="<?= e($catName) ?>">
  <div class="xb-container">
    <ul class="xb-subnav-list">
      <?php if ($subNav): ?>
        <?php foreach ($subNav as $i => $s): ?>
          <li><a class="<?= (int) $s['id'] === (int) ($cat['id'] ?? 0) ? 'on' : '' ?>" href="<?= e(xb_cat_url((string) $s['slug'])) ?>"><i><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></i><?= e(ml($s, 'name')) ?></a></li>
        <?php endforeach; ?>
      <?php else: ?>
        <li><a class="on" href="<?= e(xb_cat_url($catSlug)) ?>"><i>01</i><?= e($catName) ?></a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<section class="xb-section xb-list-section">
  <div class="xb-container">
    <div class="xb-list-head" data-reveal>
      <div>
        <span class="xb-eyebrow"><?= e($catName) ?></span>
        <h2 class="xb-section-title"><?= $isProduct ? lang('把每一种能力，<br>做成可验证的产品', '把每一种能力，做成可验证的产品') : lang('正在发生，<br>与值得分享的', '正在发生，与值得分享的') ?></h2>
      </div>
      <span class="xb-list-count"><?= e(lang('共', '共')) ?> <?= (int) $total ?> <?= e(lang('条', '条')) ?></span>
    </div>

    <?php if ($articles): ?>
      <?php if ($isProduct): ?>
      <div class="xb-product-grid xb-list-grid">
        <?php foreach ($articles as $i => $a): ?>
          <article class="xb-product-card" data-reveal data-delay="<?= $i % 3 ?>">
            <a href="<?= e(xb_article_url($a)) ?>">
              <div class="xb-product-thumb"><img src="<?= e(xb_cover($a)) ?>" alt="<?= e(xb_title($a)) ?>" loading="lazy"></div>
              <div class="xb-product-body">
                <span class="xb-product-tag"><?= e($catName) ?></span>
                <h3><?= e(xb_title($a)) ?></h3>
                <p><?= e(xb_summary($a, 58)) ?></p>
                <span class="xb-card-arrow">↗</span>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="xb-news-list">
        <?php foreach ($articles as $i => $a): ?>
          <article class="xb-news-row" data-reveal data-delay="<?= $i % 2 ?>">
            <a class="xb-news-row-thumb" href="<?= e(xb_article_url($a)) ?>"><img src="<?= e(xb_cover($a)) ?>" alt="<?= e(xb_title($a)) ?>" loading="lazy"></a>
            <div class="xb-news-row-body">
              <div class="xb-news-meta"><em><?= e($catName) ?></em><span><?= e(xb_date($a)) ?></span></div>
              <h3><a href="<?= e(xb_article_url($a)) ?>"><?= e(xb_title($a)) ?></a></h3>
              <p><?= e(xb_summary($a, 96)) ?></p>
              <a class="xb-text-link" href="<?= e(xb_article_url($a)) ?>"><?= e(lang('阅读全文', '阅读全文')) ?> <span>→</span></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="xb-empty"><?= e(lang('该栏目暂无内容，请在后台添加。', '该栏目暂无内容，请在后台添加。')) ?></div>
    <?php endif; ?>

    <?php if ($pager): ?><div class="xb-pager"><?= $pager ?></div><?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
