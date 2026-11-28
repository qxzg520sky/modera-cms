<?php
include_once __DIR__ . '/_helpers.php';
$articles = $articles ?? [];
$cat = $cat ?? [];
$total = (int) ($total ?? count($articles));
$kw = trim((string) ($kw ?? ''));
$pager = $pager ?? '';
$pageTitle = $cat ? sz_cat_name($cat) : ($kw !== '' ? sz_t('搜索：', 'Search: ') . $kw : sz_t('全部文章', 'All posts'));

$allCats = \think\facade\Db::name('category')->where('status', 1)->order('sort asc, id asc')->select()->toArray();
$catCounts = [];
foreach ($allCats as $c) {
    $catCounts[(int) $c['id']] = \think\facade\Db::name('article')->where('status', 1)->where('category_id', (int) $c['id'])->count();
}

$seoTitle = $pageTitle . ' - ' . setting('site_name', '书斋笔记');
$seoDesc = $cat ? (string) ($cat['description'] ?? '') : '';
include __DIR__ . '/_top.php';
?>

<section class="sz-page-head">
  <div class="sz-wrap sz-page-head-in">
    <div class="sz-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(sz_t('首页', 'Home')) ?></a>
      <span>/</span>
      <span><?= e($pageTitle) ?></span>
    </div>
    <div class="sz-page-title">
      <span class="sz-page-kicker"><?= $kw !== '' ? 'SEARCH' : ($cat ? 'SECTION' : 'ARCHIVE') ?></span>
      <h1><?= e($pageTitle) ?></h1>
      <p>
        <?php if ($kw !== ''): ?>
          <?= e(sz_t('共找到 ', 'Found ')) ?><?= $total ?><?= e(sz_t(' 篇文章', ' posts')) ?>
        <?php elseif ($cat && !empty($cat['description'])): ?>
          <?= e((string) $cat['description']) ?>
        <?php else: ?>
          <?= e(sz_t('按时间排列的全部文章，慢慢翻。', 'Everything published so far, in chronological order.')) ?>
        <?php endif; ?>
      </p>
    </div>
    <div class="sz-page-count"><b><?= $total ?></b><span><?= e(sz_t('篇', 'POSTS')) ?></span></div>
  </div>
</section>

<div class="sz-wrap sz-layout">
  <div class="sz-main-col">
    <?php if (!$articles): ?>
      <div class="sz-empty">
        <h2><?= e(sz_t('这里还没有文章', 'Nothing here yet')) ?></h2>
        <p><?= e(sz_t('换个分类，或者回首页看看。', 'Try another section, or go back home.')) ?></p>
        <a class="sz-read" href="<?= e(lang_path('/')) ?>"><?= e(sz_t('返回首页', 'Back home')) ?><span>→</span></a>
      </div>
    <?php else: ?>
      <div class="sz-archive-list">
        <?php foreach ($articles as $i => $a): ?>
        <article class="sz-arc">
          <a class="sz-arc-media" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
            <?= sz_cover($a, 'sz-arc-img') ?>
          </a>
          <div class="sz-arc-body">
            <div class="sz-meta">
              <span class="sz-cat"><?= e($cat ? sz_cat_name($cat) : (function ($cid) use ($allCats) { foreach ($allCats as $cc) { if ((int) $cc['id'] === (int) $cid) { return sz_cat_name($cc); } } return sz_t('随笔', 'Notes'); })($a['category_id'])) ?></span>
              <span><?= e(sz_date((string) $a['create_time'])) ?></span>
              <span><?= e(sz_reading_time($a)) ?> <?= e(sz_t('分钟', 'min')) ?></span>
            </div>
            <h2><a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= e(ml($a, 'title')) ?></a></h2>
            <p><?= e(sz_excerpt($a, 100)) ?></p>
            <a class="sz-text-link" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= e(sz_t('阅读全文', 'Read article')) ?> <span>→</span></a>
          </div>
          <span class="sz-arc-no"><?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
        </article>
        <?php endforeach; ?>
      </div>
      <?php if ($pager): ?><div class="sz-pager"><?= $pager ?></div><?php endif; ?>
    <?php endif; ?>
  </div>

  <?php $sideLean = true; include __DIR__ . '/_sidebar.php'; ?>
</div>

<?php include __DIR__ . '/_bottom.php'; ?>
