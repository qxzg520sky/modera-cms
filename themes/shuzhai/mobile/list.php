<?php
include_once __DIR__ . '/../_helpers.php';
$articles = $articles ?? [];
$cat = $cat ?? [];
$total = (int) ($total ?? count($articles));
$kw = trim((string) ($kw ?? ''));
$pager = $pager ?? '';
$pageTitle = $cat ? sz_cat_name($cat) : ($kw !== '' ? sz_t('搜索：', 'Search: ') . $kw : sz_t('全部文章', 'All posts'));
$catId = (int) ($cat['id'] ?? 0);
$allCats = \think\facade\Db::name('category')->where('status', 1)->order('sort asc, id asc')->select()->toArray();
$seoTitle = $pageTitle . ' - ' . setting('site_name', '书斋笔记');
include __DIR__ . '/_top.php';
?>

<section class="mz-page-head">
  <span class="mz-label"><?= $kw !== '' ? 'SEARCH' : ($cat ? 'SECTION' : 'ARCHIVE') ?></span>
  <h1><?= e($pageTitle) ?></h1>
  <p>
    <?php if ($kw !== ''): ?><?= e(sz_t('找到 ', 'Found ')) ?><?= $total ?><?= e(sz_t(' 篇文章', 'posts')) ?>
    <?php elseif ($cat && !empty($cat['description'])): ?><?= e((string) $cat['description']) ?>
    <?php else: ?><?= e(sz_t('按时间排列，慢慢翻。', 'Everything published so far.')) ?><?php endif; ?>
  </p>
  <div class="mz-page-count"><b><?= $total ?></b><?= e(sz_t('篇', 'posts')) ?></div>
</section>

<div class="mz-filters">
  <a href="<?= e(lang_path('/')) ?>"<?= !$cat ? ' class="now"' : '' ?>><?= e(sz_t('全部', 'All')) ?></a>
  <?php foreach ($allCats as $c): if (($c['slug'] ?? '') === 'jobs') { continue; } ?>
  <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"<?= $catId === (int) $c['id'] ? ' class="now"' : '' ?>><?= e(sz_cat_name($c)) ?></a>
  <?php endforeach; ?>
</div>

<section class="mz-section mz-section-tight">
  <?php if (!$articles): ?>
  <div class="mz-empty">
    <h2><?= e(sz_t('这里还没有文章', 'Nothing here yet')) ?></h2>
    <p><?= e(sz_t('换个分类，或者回首页看看。', 'Try another section or go back home.')) ?></p>
    <a class="mz-read" href="<?= e(lang_path('/')) ?>"><?= e(sz_t('返回首页', 'Back home')) ?><span>→</span></a>
  </div>
  <?php else: ?>
  <div class="mz-arc-list">
    <?php foreach ($articles as $a): ?>
    <article class="mz-arc">
      <a class="mz-arc-media" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= sz_cover($a, 'mz-arc-img') ?></a>
      <div class="mz-arc-body">
        <div class="mz-meta"><span><?= e(sz_date((string) $a['create_time'], false)) ?></span><span><?= e(sz_reading_time($a)) ?> <?= e(sz_t('分钟', 'min')) ?></span></div>
        <h2><a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= e(ml($a, 'title')) ?></a></h2>
        <p><?= e(sz_excerpt($a, 50)) ?></p>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php if ($pager): ?><div class="mz-pager"><?= $pager ?></div><?php endif; ?>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
