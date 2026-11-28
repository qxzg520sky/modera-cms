<?php include __DIR__ . '/_top.php'; ?>
<?php
$catRow = \think\facade\Db::name('category')->where('id', (int) ($row['category_id'] ?? 0))->find();
$rootCat = $catRow ?: [];
if (!empty($catRow['pid'])) {
    $rootCat = \think\facade\Db::name('category')->where('id', (int) $catRow['pid'])->find() ?: $catRow;
}
$rootSlug = (string) ($rootCat['slug'] ?? '');
$isProduct = ($rootSlug === 'product');
$catName = trim((string) ($catRow['name'] ?? lang('内容详情')));
$cover = trim((string) ml($row, 'cover'));
$summary = trim(strip_tags((string) ml($row, 'summary')));
$bannerMap = [
    'product' => '/uploads/showroom.jpg',
    'equipment' => '/uploads/workshop.jpg',
    'machining' => '/uploads/machining.jpg',
    'solution' => '/uploads/line.jpg',
    'service' => '/uploads/qc.jpg',
    'news' => '/uploads/team.jpg',
];
$heroImg = $cover ?: ($bannerMap[$rootSlug] ?? '/uploads/workshop.jpg');
$sideCats = $rootCat ? [$rootCat] : [$catRow];
foreach (($rootCat['children'] ?? []) as $ch) {
    $sideCats[] = $ch;
}
if (count($sideCats) < 2) {
    foreach ($nav as $c) {
        if ((string) $c['slug'] === $rootSlug && !empty($c['children'])) {
            $sideCats = [$c];
            foreach ($c['children'] as $ch) { $sideCats[] = $ch; }
        }
    }
}
$contactUrl = lang_path('/p/contact');
?>

<section class="sw-inner-hero" style="background-image:url('<?= e($heroImg) ?>')">
  <div class="sw-container">
    <span class="eyebrow"><?= e($rootCat['name'] ?? $catName) ?></span>
    <h1><?= e($isProduct ? ml($row, 'title') : $catName) ?></h1>
    <div class="sw-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a>
      <i>/</i>
      <?php if ($rootCat): ?>
        <a href="<?= e(lang_path('/c/' . ($rootCat['slug'] ?? ''))) ?>"><?= e($rootCat['name']) ?></a>
        <i>/</i>
      <?php endif; ?>
      <span><?= e(mb_substr((string) ml($row, 'title'), 0, 22)) ?></span>
    </div>
  </div>
</section>

<section class="sw-inner sw-article-page">
  <div class="sw-container sw-inner-grid">
    <aside class="sw-side">
      <div class="sw-side-block">
        <h3><?= e($rootCat['name'] ?? $catName) ?></h3>
        <div class="sw-side-list">
          <?php foreach ($sideCats as $ch): ?>
            <a class="<?= ((string) ($ch['slug'] ?? '') === (string) ($catRow['slug'] ?? '')) ? 'on' : '' ?>" href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="sw-side-cta">
        <b><?= e(lang('项目咨询')) ?></b>
        <a class="tel" href="tel:<?= e(preg_replace('/[^0-9+\-]/', '', (string) setting('site_phone', ''))) ?>"><?= e(setting('site_phone', '')) ?></a>
        <a href="<?= e($contactUrl) ?>"><?= e(lang('获取方案与报价')) ?> →</a>
      </div>
    </aside>

    <div class="sw-content">
      <?php if ($isProduct): ?>
        <div class="sw-pro-detail-top">
          <div class="sw-pro-info">
            <h1><?= e(ml($row, 'title')) ?></h1>
            <span class="sw-pro-spec"><?= e($catName) ?></span>
            <?php if ($summary): ?><div class="sw-pro-summary"><?= e($summary) ?></div><?php endif; ?>
            <a class="sw-btn sw-btn-primary" href="<?= e($contactUrl) ?>"><?= e(lang('咨询该产品')) ?></a>
          </div>
          <div class="sw-pro-pic">
            <img src="<?= e($heroImg) ?>" alt="<?= e(ml($row, 'title')) ?>">
          </div>
        </div>
        <div class="sw-pro-body">
          <h2><?= e(lang('产品说明')) ?></h2>
          <div class="sw-rich"><?= ml($row, 'content') ?></div>
        </div>
      <?php else: ?>
        <article class="sw-article">
          <header class="sw-article-head">
            <h1><?= e(ml($row, 'title')) ?></h1>
            <div class="sw-article-meta">
              <span><?= e($catName) ?></span>
              <span><?= e(date('Y-m-d', strtotime((string) $row['create_time']))) ?></span>
              <?php if ((int) ($row['views'] ?? 0) > 0): ?><span><?= e(lang('阅读')) ?> <?= (int) $row['views'] ?></span><?php endif; ?>
            </div>
          </header>
          <?php if ($summary): ?><div class="sw-article-lead"><?= e($summary) ?></div><?php endif; ?>
          <?php if ($cover): ?>
            <div class="sw-article-cover"><img src="<?= e($cover) ?>" alt="<?= e(ml($row, 'title')) ?>"></div>
          <?php endif; ?>
          <div class="sw-rich"><?= ml($row, 'content') ?></div>
        </article>
      <?php endif; ?>

      <nav class="sw-art-nav" aria-label="<?= e(lang('上一篇下一篇')) ?>">
        <?php if (!empty($prev)): ?>
          <a href="<?= e(lang_path('/a/' . (int) $prev['id'])) ?>"><i>← <?= e(lang('上一篇')) ?></i><b><?= e(ml($prev, 'title')) ?></b></a>
        <?php else: ?><span></span><?php endif; ?>
        <?php if (!empty($next)): ?>
          <a href="<?= e(lang_path('/a/' . (int) $next['id'])) ?>"><i><?= e(lang('下一篇')) ?> →</i><b><?= e(ml($next, 'title')) ?></b></a>
        <?php endif; ?>
      </nav>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>

