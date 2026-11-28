<?php include __DIR__ . '/_top.php'; ?>
<?php
$catRow = \think\facade\Db::name('category')->where('id', (int) ($row['category_id'] ?? 0))->find();
$rootCat = $catRow ?: [];
if (!empty($catRow['pid'])) {
    $rootCat = \think\facade\Db::name('category')->where('id', (int) $catRow['pid'])->find() ?: $catRow;
}
$rootSlug = (string) ($rootCat['slug'] ?? '');
$catName = trim((string) ($catRow['name'] ?? lang('内容详情')));
$isProduct = in_array($rootSlug, ['product', 'equipment', 'machining'], true);
$cover = trim((string) ml($row, 'cover'));
$summary = trim(strip_tags((string) ml($row, 'summary')));
$bannerMap = [
    'product' => '/uploads/showroom.jpg', 'equipment' => '/uploads/workshop.jpg',
    'machining' => '/uploads/machining.jpg', 'solution' => '/uploads/line.jpg',
    'service' => '/uploads/qc.jpg', 'news' => '/uploads/team.jpg', 'jobs' => '/uploads/factory.jpg',
];
$heroImg = $cover ?: ($bannerMap[$rootSlug] ?? '/uploads/workshop.jpg');
$headingMap = [
    'product' => 'PRODUCT DETAILS', 'equipment' => 'INTELLIGENT EQUIPMENT',
    'machining' => 'PRECISION MACHINING', 'solution' => 'SOLUTION DETAILS',
    'service' => 'SERVICE SUPPORT', 'news' => 'NEWS DETAIL', 'jobs' => 'CAREERS',
];
$headingEn = $headingMap[$rootSlug] ?? 'DETAILS';
$sideCats = $rootCat ? [$rootCat] : [$catRow];
foreach (($rootCat['children'] ?? []) as $ch) { $sideCats[] = $ch; }
if (count($sideCats) < 2) {
    foreach ($nav as $c) {
        if ((string) $c['slug'] === $rootSlug && !empty($c['children'])) {
            $sideCats = [$c];
            foreach ($c['children'] as $ch) { $sideCats[] = $ch; }
        }
    }
}
$contactUrl = lang_path('/p/contact');
$contentHtml = (string) ml($row, 'content');
if ($cover && $contentHtml !== '') {
    $coverBase = preg_quote(basename((string) parse_url($cover, PHP_URL_PATH)), '#');
    $contentHtml = preg_replace('#<img[^>]+src="[^"]*' . $coverBase . '[^"]*"[^>]*>#i', '', $contentHtml, 1);
}
$cnLen = mb_strlen($catName);
$cnPrefix = $cnLen > 2 ? mb_substr($catName, 0, $cnLen - 2) : $catName;
$cnTail = $cnLen > 2 ? mb_substr($catName, -2) : '';
?>

<section class="tz-inner-banner" style="background-image:url('<?= e($heroImg) ?>')">
  <div class="tz-inner-banner-shade"></div>
  <div class="tz-container tz-banner-crumb">
    <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a>
    <span>/</span>
    <?php if ($rootCat): ?>
      <a href="<?= e(lang_path('/c/' . ($rootCat['slug'] ?? ''))) ?>"><?= e($rootCat['name']) ?></a>
      <span>/</span>
    <?php endif; ?>
    <em><?= e(mb_substr((string) ml($row, 'title'), 0, 26)) ?></em>
  </div>
  <div class="tz-side-nav">
    <a class="tz-side-toggle" href="javascript:;">MENU <i>☰</i></a>
    <div class="tz-side-menu">
      <ul>
        <?php if ($rootCat): ?><li><a href="<?= e(lang_path('/c/' . ($rootCat['slug'] ?? ''))) ?>"><?= e($rootCat['name']) ?></a></li><?php endif; ?>
        <?php foreach ($sideCats as $ch): ?>
          <?php if ((string) ($ch['slug'] ?? '') === $rootSlug) continue; ?>
          <li class="<?= ((string) ($ch['slug'] ?? '') === (string) ($catRow['slug'] ?? '')) ? 'on' : '' ?>"><a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a></li>
        <li><a href="javascript:;" class="tz-gotop">返回顶部 ↑</a></li>
      </ul>
    </div>
  </div>
</section>

<section class="tz-inner-head">
  <div class="tz-container">
    <div class="tz-heading tz-heading-simple">
      <span class="tz-en"><?= e($headingEn) ?></span>
      <h2><?= e($cnPrefix) ?><?php if ($cnTail): ?><b><?= e($cnTail) ?></b><?php endif; ?></h2>
    </div>
  </div>
</section>

<section class="tz-article-page">
  <div class="tz-container">

    <?php if ($isProduct): ?>
      <div class="tz-pro-detail">
        <div class="tz-pro-gallery">
          <img src="<?= e($heroImg) ?>" alt="<?= e(ml($row, 'title')) ?>">
          <span class="tz-pro-badge"><?= e($catName) ?></span>
        </div>
        <div class="tz-pro-info">
          <span class="tz-en">PRODUCT / <?= e($catName) ?></span>
          <h1><?= e(ml($row, 'title')) ?></h1>
          <?php if ($summary): ?><p class="tz-pro-lead"><?= e($summary) ?></p><?php endif; ?>
          <div class="tz-pro-points">
            <span>按工艺节拍设计</span>
            <span>关键数据验收</span>
            <span>交付即培训</span>
          </div>
          <div class="tz-pro-actions">
            <a class="tz-btn tz-btn-blue" href="<?= e($contactUrl) ?>"><?= e(lang('咨询该产品')) ?></a>
            <a class="tz-text-more" href="<?= e(lang_path('/c/product')) ?>">查看同类产品 →</a>
          </div>
        </div>
      </div>
      <div class="tz-article-wrap">
        <div class="tz-article-rich"><?= $contentHtml ?></div>
      </div>

    <?php else: ?>
      <article class="tz-article-wrap">
        <header class="tz-article-head">
          <span class="tz-en"><?= e($headingEn) ?></span>
          <h1><?= e(ml($row, 'title')) ?></h1>
          <div class="tz-article-meta">
            <span><?= e($catName) ?></span>
            <span><?= e(date('Y-m-d', strtotime((string) $row['create_time']))) ?></span>
            <?php if ((int) ($row['views'] ?? 0) > 0): ?><span><?= e(lang('阅读')) ?> <?= (int) $row['views'] ?></span><?php endif; ?>
          </div>
        </header>
        <?php if ($summary): ?><p class="tz-article-lead"><?= e($summary) ?></p><?php endif; ?>
        <?php if ($cover): ?><div class="tz-article-cover"><img src="<?= e($cover) ?>" alt="<?= e(ml($row, 'title')) ?>"></div><?php endif; ?>
        <div class="tz-article-rich"><?= $contentHtml ?></div>
      </article>
    <?php endif; ?>

    <nav class="tz-art-nav" aria-label="<?= e(lang('上一篇下一篇')) ?>">
      <?php if (!empty($prev)): ?>
        <a href="<?= e(lang_path('/a/' . (int) $prev['id'])) ?>"><i>← <?= e(lang('上一篇')) ?></i><b><?= e(ml($prev, 'title')) ?></b></a>
      <?php else: ?><span></span><?php endif; ?>
      <?php if (!empty($next)): ?>
        <a href="<?= e(lang_path('/a/' . (int) $next['id'])) ?>"><i><?= e(lang('下一篇')) ?> →</i><b><?= e(ml($next, 'title')) ?></b></a>
      <?php endif; ?>
    </nav>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>

