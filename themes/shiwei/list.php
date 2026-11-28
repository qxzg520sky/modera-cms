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
    'product' => '/uploads/showroom.jpg',
    'equipment' => '/uploads/workshop.jpg',
    'machining' => '/uploads/machining.jpg',
    'solution' => '/uploads/line.jpg',
    'service' => '/uploads/qc.jpg',
    'news' => '/uploads/team.jpg',
    'jobs' => '/uploads/factory.jpg',
];
$heroImg = $bannerMap[$rootSlug] ?? (!empty($articles[0]['cover']) ? $articles[0]['cover'] : '/uploads/workshop.jpg');
$sideCats = [$rootCat];
foreach (($rootCat['children'] ?? []) as $ch) {
    $sideCats[] = $ch;
}
if (count($sideCats) < 2 && !empty($subNav)) {
    $sideCats = $subNav;
}
$subTabs = !empty($subNav) ? $subNav : $sideCats;
$catIntro = trim(strip_tags((string) ($cat['seo_description'] ?? '')));
$contactUrl = lang_path('/p/contact');
?>

<section class="sw-inner-hero" style="background-image:url('<?= e($heroImg) ?>')">
  <div class="sw-container">
    <span class="eyebrow"><?= e(lang('产品与服务')) ?></span>
    <h1><?= e($catName) ?></h1>
    <div class="sw-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a>
      <i>/</i>
      <?php if ($rootSlug !== $slug): ?>
        <a href="<?= e(lang_path('/c/' . $rootSlug)) ?>"><?= e($rootCat['name'] ?? '') ?></a>
        <i>/</i>
      <?php endif; ?>
      <span><?= e($catName) ?></span>
    </div>
  </div>
</section>

<section class="sw-inner">
  <div class="sw-container sw-inner-grid">
    <aside class="sw-side">
      <div class="sw-side-block">
        <h3><?= e($rootCat['name'] ?? $catName) ?></h3>
        <div class="sw-side-list">
          <?php foreach ($sideCats as $ch): ?>
            <a class="<?= ((string) $ch['slug'] === $slug) ? 'on' : '' ?>" href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a>
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
      <div class="sw-content-head">
        <h2><?= e($catName) ?></h2>
        <em><?= e(strtoupper($rootSlug ?: 'CONTENT')) ?></em>
      </div>

      <?php if (count($subTabs) > 1): ?>
        <div class="sw-tabs">
          <?php foreach ($subTabs as $ch): ?>
            <a class="<?= ((string) $ch['slug'] === $slug) ? 'on' : '' ?>" href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($catIntro): ?><p style="margin:0 0 24px;color:#7b8794;font-size:13px;line-height:1.9"><?= e($catIntro) ?></p><?php endif; ?>

      <?php if ($articles): ?>

        <?php if ($rootSlug === 'product'): ?>
          <div class="sw-product-grid">
            <?php foreach ($articles as $a): ?>
              <a class="sw-product-item" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
                <span class="pic">
                  <img src="<?= e(trim((string) ml($a, 'cover')) ?: '/uploads/workshop.jpg') ?>" alt="<?= e(ml($a, 'title')) ?>" loading="lazy">
                </span>
                <h3><?= e(ml($a, 'title')) ?></h3>
                <p><?= e(mb_substr(trim(strip_tags((string) ml($a, 'summary')), '　 '), 0, 44)) ?></p>
              </a>
            <?php endforeach; ?>
          </div>

        <?php elseif ($rootSlug === 'solution'): ?>
          <div class="sw-solution-list">
            <?php foreach ($articles as $a): ?>
              <a class="sw-sol-row" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
                <span class="sw-sol-thumb"><img src="<?= e(trim((string) ml($a, 'cover')) ?: '/uploads/line.jpg') ?>" alt="<?= e(ml($a, 'title')) ?>" loading="lazy"></span>
                <span class="sw-sol-body">
                  <h3><?= e(ml($a, 'title')) ?></h3>
                  <p><?= e(mb_substr(trim(strip_tags((string) ml($a, 'summary')), '　 '), 0, 90)) ?></p>
                  <span class="more"><?= e(lang('查看详情')) ?> →</span>
                </span>
              </a>
            <?php endforeach; ?>
          </div>

        <?php elseif ($rootSlug === 'news'): ?>
          <div class="sw-news-grid">
            <?php foreach ($articles as $a): ?>
              <a class="sw-news-card" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
                <span class="sw-news-pic"><img src="<?= e(trim((string) ml($a, 'cover')) ?: '/uploads/factory.jpg') ?>" alt="<?= e(ml($a, 'title')) ?>" loading="lazy"></span>
                <span class="sw-news-body">
                  <h3><?= e(ml($a, 'title')) ?></h3>
                  <p><?= e(mb_substr(trim(strip_tags((string) ml($a, 'summary')), '　 '), 0, 56)) ?></p>
                  <span class="sw-news-meta"><?= e(date('Y-m-d', strtotime((string) $a['create_time']))) ?></span>
                </span>
              </a>
            <?php endforeach; ?>
          </div>

        <?php else: ?>
          <div class="sw-service-list">
            <?php foreach ($articles as $i => $a): ?>
              <div class="sw-service-row">
                <div class="sw-service-pic">
                  <img src="<?= e(trim((string) ml($a, 'cover')) ?: ($i % 2 ? '/uploads/team.jpg' : '/uploads/qc.jpg')) ?>" alt="<?= e(ml($a, 'title')) ?>" loading="lazy">
                </div>
                <div class="sw-service-body">
                  <h3><?= e(ml($a, 'title')) ?></h3>
                  <p><?= e(mb_substr(trim(strip_tags((string) ml($a, 'summary') ?: ml($a, 'content')), '　 '), 0, 120)) ?></p>
                  <a class="more" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= e(lang('查看详情')) ?> →</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="sw-empty"><?= e(lang('暂无内容')) ?></div>
      <?php endif; ?>

      <?php if (!empty($pager)): ?>
        <div class="pager"><?= $pager ?></div>
      <?php endif; ?>

      <?php if ($rootSlug === 'service' && count($sideCats) > 1): ?>
        <div class="sw-service-mini">
          <?php foreach (array_slice($sideCats, 1, 6) as $ch): ?>
            <a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>">
              <b><?= e($ch['name']) ?></b>
              <p><?= e(mb_substr(strip_tags((string) ($ch['seo_description'] ?? '')), 0, 34) ?: lang('按现场条件提供适配的服务组合')) ?></p>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
