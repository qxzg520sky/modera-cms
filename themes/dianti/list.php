<?php include __DIR__ . '/_top.php'; ?>
<?php
$slug = (string) ($cat['slug'] ?? '');
$isNews = in_array($slug, ['news', ''], true);
$isSearch = $slug === '';
$pageTitle = (string) ($cat['name'] ?? '列表');
$pageEn = $isSearch ? 'SEARCH' : strtoupper($slug ?: 'LIST');
$desc = trim((string) ($cat['seo_description'] ?? ''));
if ($desc === '') { $desc = $isSearch ? '按关键词检索站内文章与产品资料。' : '从产品到方案，从交付到服务，查看' . $pageTitle . '的完整内容。'; }
$cover = static function (array $a, string $fallback = '/theme/product.jpg'): string {
    $c = trim((string) ($a['cover'] ?? ''));
    return $c !== '' ? $c : $fallback;
};
$titleOf = static fn (array $a): string => (string) ml($a, 'title');
$summaryOf = static function (array $a, int $len = 80): string {
    $s = trim((string) ml($a, 'summary'));
    if ($s === '') { $s = trim(strip_tags((string) ml($a, 'content'))); }
    return mb_substr($s, 0, $len, 'UTF-8');
};
$dateOf = static function (array $a): string {
    $t = strtotime((string) ($a['create_time'] ?? ''));
    return $t ? date('Y.m.d', $t) : '—';
};
$kw = trim((string) ($_GET['kw'] ?? ''));
$catNameById = static function (int $id) use ($nav): string {
    foreach ($nav as $c) {
        if ((int) $c['id'] === $id) { return (string) $c['name']; }
        foreach (($c['children'] ?? []) as $ch) {
            if ((int) $ch['id'] === $id) { return (string) $ch['name']; }
        }
    }
    return '资讯';
};
?>
<section class="page-hero" data-en="<?= e($pageEn) ?>">
  <div class="wrap">
    <div class="breadcrumb"><a href="<?= e(lang_path('/')) ?>">首页</a> / <?= e($pageTitle) ?></div>
    <div class="hero-line"></div>
    <h1><?= e($pageTitle) ?></h1>
    <p><?= e($desc) ?></p>
  </div>
</section>

<section class="sec-pad-sm">
  <div class="wrap">
    <?php if ($isSearch): ?>
    <form class="theme-search" action="<?= e(lang_path('/search')) ?>" method="get" role="search">
      <input type="text" name="kw" value="<?= e($kw) ?>" placeholder="输入产品、方案或文章关键词">
      <button type="submit">搜索</button>
    </form>
    <?php endif; ?>

    <?php if (!$isSearch): ?>
    <div class="filter-bar">
      <div class="filter-tabs">
        <a class="active" href="<?= e(lang_path('/c/' . $slug)) ?>">全部</a>
        <?php foreach (($subNav ?? []) as $s): ?>
          <a class="<?= (string) $s['slug'] === $slug ? 'active' : '' ?>" href="<?= e(lang_path('/c/' . $s['slug'])) ?>"><?= e($s['name']) ?></a>
        <?php endforeach; ?>
      </div>
      <span class="count">共 <?= (int) ($total ?? count($articles)) ?> 条</span>
    </div>
    <?php else: ?>
    <div class="filter-bar"><span class="count">关键词：<?= e($kw !== '' ? $kw : '—') ?>　共 <?= (int) ($total ?? 0) ?> 条结果</span></div>
    <?php endif; ?>

    <?php if (empty($articles)): ?>
      <div class="empty">没有找到相关内容。<a href="<?= e(lang_path('/')) ?>" style="color:var(--accent)">返回首页 ↗</a></div>
    <?php elseif ($isNews): ?>
      <div class="news-grid" style="grid-template-columns:1fr">
        <?php foreach ($articles as $a): ?>
        <a class="news-item news-row" href="<?= e(lang_path('/a/' . $a['id'])) ?>">
          <div class="n-date"><?= e($dateOf($a)) ?><span><?= e($catNameById((int) $a['category_id'])) ?></span></div>
          <div class="news-inner">
            <div class="news-thumb"><img src="<?= e($cover($a, '/theme/about.jpg')) ?>" alt="<?= e($titleOf($a)) ?>" loading="lazy"></div>
            <div><h3><?= e($titleOf($a)) ?></h3><p><?= e($summaryOf($a, 96)) ?></p><span class="n-arrow">阅读全文 ↗</span></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="catalog-grid">
        <?php foreach ($articles as $a): ?>
        <article class="catalog-card">
          <div class="c-img"><a href="<?= e(lang_path('/a/' . $a['id'])) ?>"><img src="<?= e($cover($a)) ?>" alt="<?= e($titleOf($a)) ?>" loading="lazy"></a></div>
          <div class="c-body">
            <span class="c-tag"><?= e($catNameById((int) $a['category_id'])) ?> / <?= e($dateOf($a)) ?></span>
            <h3><a href="<?= e(lang_path('/a/' . $a['id'])) ?>"><?= e($titleOf($a)) ?></a></h3>
            <p><?= e($summaryOf($a, 90)) ?></p>
            <div class="c-foot"><span>阅读详情</span><a href="<?= e(lang_path('/a/' . $a['id'])) ?>" class="arrow">↗</a></div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($pager)): ?><div class="pager"><?= $pager ?></div><?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/_bottom.php'; ?>



