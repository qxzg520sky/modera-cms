<?php include __DIR__ . '/_top.php'; ?>
<?php
$catName = trim((string) ($cat['name'] ?? ($pageTitle ?? lang('产品中心'))));
$slug    = (string) ($cat['slug'] ?? '');
$rootCat = $cat;
if (!empty($cat['pid'])) {
    $rootCat = \think\facade\Db::name('category')->where('id', (int) $cat['pid'])->find() ?: $cat;
}
$rootSlug = (string) ($rootCat['slug'] ?? $slug);
$bannerMap = [
    'product'   => '/uploads/showroom.jpg',
    'equipment' => '/uploads/workshop.jpg',
    'machining' => '/uploads/machining.jpg',
    'solution'  => '/uploads/line.jpg',
    'service'   => '/uploads/qc.jpg',
    'news'      => '/uploads/team.jpg',
    'jobs'      => '/uploads/factory.jpg',
];
$heroImg = $bannerMap[$rootSlug] ?? (!empty($articles[0]['cover']) ? $articles[0]['cover'] : '/uploads/workshop.jpg');
$headingMap = [
    'product' => 'PRODUCTS & SOLUTIONS', 'equipment' => 'INTELLIGENT EQUIPMENT',
    'machining' => 'PRECISION MACHINING', 'solution' => 'TURNKEY SOLUTIONS',
    'service' => 'SERVICE SUPPORT', 'news' => 'COMPANY NEWS',
    'jobs' => 'CAREERS', 'default' => 'CONTENT',
];
$headingEn = $headingMap[$rootSlug] ?? 'CONTENT';
$sideCats = [$rootCat];
foreach (($rootCat['children'] ?? []) as $ch) { $sideCats[] = $ch; }
if (count($sideCats) < 2 && !empty($subNav)) { $sideCats = $subNav; }
$subTabs = !empty($subNav) ? $subNav : $sideCats;
$catIntro = trim(strip_tags((string) ($cat['seo_description'] ?? '')));
$contactUrl = lang_path('/p/contact');
$cnLen = mb_strlen($catName);
$cnPrefix = $cnLen > 2 ? mb_substr($catName, 0, $cnLen - 2) : $catName;
$cnTail = $cnLen > 2 ? mb_substr($catName, -2) : '';
$total = isset($total) ? (int) $total : count($articles);
?>

<section class="tz-inner-banner" style="background-image:url('<?= e($heroImg) ?>')">
  <div class="tz-inner-banner-shade"></div>
  <div class="tz-container tz-banner-crumb">
    <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a>
    <span>/</span>
    <?php if ($rootSlug !== $slug): ?>
      <a href="<?= e(lang_path('/c/' . $rootSlug)) ?>"><?= e($rootCat['name'] ?? '') ?></a>
      <span>/</span>
    <?php endif; ?>
    <em><?= e($catName) ?></em>
  </div>
  <div class="tz-side-nav">
    <a class="tz-side-toggle" href="javascript:;">MENU <i>☰</i></a>
    <div class="tz-side-menu">
      <ul>
        <?php if (!empty($rootCat['slug'])): ?>
          <li class="<?= $slug === $rootSlug ? 'on' : '' ?>"><a href="<?= e(lang_path('/c/' . $rootSlug)) ?>"><?= e($rootCat['name']) ?></a></li>
        <?php endif; ?>
        <?php foreach ($sideCats as $ch): ?>
          <?php if ((string) ($ch['slug'] ?? '') === $rootSlug) continue; ?>
          <li class="<?= ((string) ($ch['slug'] ?? '') === $slug) ? 'on' : '' ?>"><a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a></li>
        <?php endforeach; ?>
        <?php if ($rootSlug === 'news'): ?><li><a href="<?= e(lang_path('/#perspective')) ?>"><?= e(lang('集团视角')) ?></a></li><?php endif; ?>
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
    <?php if (count($subTabs) > 1): ?>
      <div class="tz-subtabs">
        <?php foreach ($subTabs as $ch): ?>
          <a class="<?= ((string) ($ch['slug'] ?? '') === $slug) ? 'on' : '' ?>" href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="tz-list">
  <div class="tz-container">
    <?php if ($catIntro): ?><p class="tz-list-intro"><?= e($catIntro) ?></p><?php endif; ?>
    <?php if (!empty($articles)): ?>

      <?php if ($rootSlug === 'news'): ?>
        <div class="tz-news-grid">
          <?php foreach ($articles as $a): ?>
            <?php
              $sum = trim(strip_tags((string) ml($a, 'summary')));
              if ($sum === '') { $sum = mb_substr(trim(strip_tags((string) ml($a, 'content'))), 0, 90) . '…'; }
            ?>
            <a class="tz-news-card" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
              <span class="tz-news-pic"><img src="<?= e(trim((string) ml($a, 'cover')) ?: '/uploads/team.jpg') ?>" alt="<?= e(ml($a, 'title')) ?>" loading="lazy"></span>
              <span class="tz-news-body">
                <em>NEWS / <?= e(date('Y.m.d', strtotime((string) $a['create_time']))) ?></em>
                <h3><?= e(ml($a, 'title')) ?></h3>
                <p><?= e($sum) ?></p>
                <span class="tz-news-more">MORE <i>→</i></span>
              </span>
            </a>
          <?php endforeach; ?>
        </div>

      <?php elseif ($rootSlug === 'product' || $rootSlug === 'equipment' || $rootSlug === 'machining'): ?>
        <div class="tz-product-grid">
          <?php foreach ($articles as $a): ?>
            <a class="tz-product-card" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
              <span class="tz-product-pic"><img src="<?= e(trim((string) ml($a, 'cover')) ?: '/uploads/workshop.jpg') ?>" alt="<?= e(ml($a, 'title')) ?>" loading="lazy"></span>
              <span class="tz-product-body">
                <h3><?= e(ml($a, 'title')) ?></h3>
                <p><?= e(mb_substr(trim(strip_tags((string) ml($a, 'summary'))), 0, 52)) ?></p>
                <span>MORE <i>→</i></span>
              </span>
            </a>
          <?php endforeach; ?>
        </div>

      <?php elseif ($rootSlug === 'solution'): ?>
        <div class="tz-solution-grid">
          <?php foreach ($articles as $i => $a): ?>
            <a class="tz-solution-card" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
              <span class="tz-solution-pic"><img src="<?= e(trim((string) ml($a, 'cover')) ?: '/uploads/line.jpg') ?>" alt="<?= e(ml($a, 'title')) ?>" loading="lazy"><i><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></i></span>
              <span class="tz-solution-body">
                <em>SOLUTION / <?= e(date('Y.m', strtotime((string) $a['create_time']))) ?></em>
                <h3><?= e(ml($a, 'title')) ?></h3>
                <p><?= e(mb_substr(trim(strip_tags((string) ml($a, 'summary'))), 0, 86)) ?></p>
                <span>查看方案 →</span>
              </span>
            </a>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <div class="tz-row-list">
          <?php foreach ($articles as $i => $a): ?>
            <a class="tz-row-item" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
              <span class="tz-row-no"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <span class="tz-row-main">
                <h3><?= e(ml($a, 'title')) ?></h3>
                <p><?= e(mb_substr(trim(strip_tags((string) ml($a, 'summary'))), 0, 100)) ?></p>
              </span>
              <span class="tz-row-date"><?= e(date('Y-m-d', strtotime((string) $a['create_time']))) ?></span>
              <span class="tz-row-arrow">→</span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <div class="tz-empty"><?= e(lang('该栏目暂无内容')) ?></div>
    <?php endif; ?>

    <?php if (!empty($pager)): ?><div class="tz-pager"><?= $pager ?></div><?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
