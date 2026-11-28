<?php
include_once __DIR__ . '/../_helpers.php';
$row = $row ?? [];
$cat = $cat ?? [];
$prev = $prev ?? null;
$next = $next ?? null;
$title = (string) ml($row, 'title');
$body = (string) ml($row, 'content');
$summary = trim((string) ml($row, 'summary'));
$showHero = !sz_cover_is_inline($row);
$related = [];
if (!empty($row['id']) && !empty($row['category_id'])) {
    $related = \think\facade\Db::name('article')->where('status', 1)->where('id', '<>', (int) $row['id'])
        ->where('category_id', (int) $row['category_id'])->order('id desc')->limit(2)->select()->toArray();
}
$seoTitle = $title . ' - ' . setting('site_name', '书斋笔记');
$seoDesc = $summary !== '' ? $summary : sz_excerpt($row, 100);
include __DIR__ . '/_top.php';
?>

<article class="mz-article">
  <div class="mz-crumb">
    <a href="<?= e(lang_path('/')) ?>"><?= e(sz_t('首页', 'Home')) ?></a><span>/</span>
    <?php if ($cat): ?><a href="<?= e(lang_path('/c/' . $cat['slug'])) ?>"><?= e(sz_cat_name($cat)) ?></a><?php endif; ?>
  </div>

  <header class="mz-article-head">
    <div class="mz-meta">
      <span class="mz-cat"><?= e($cat ? sz_cat_name($cat) : sz_t('随笔', 'Notes')) ?></span>
      <span><?= e(sz_date((string) ($row['create_time'] ?? ''))) ?></span>
      <span><?= e(sz_reading_time($row)) ?> <?= e(sz_t('分钟', 'min')) ?></span>
    </div>
    <h1><?= e($title) ?></h1>
    <?php if ($summary !== ''): ?><p class="mz-article-sum"><?= e($summary) ?></p><?php endif; ?>
    <div class="mz-author">
      <span><?= current_lang() === 'en' ? 'S' : '书' ?></span>
      <b><?= e((string) ($row['author'] ?? setting('site_name', '书斋笔记'))) ?></b>
      <em>NO.<?= e(sz_issue($row)) ?></em>
    </div>
  </header>

  <?php if ($showHero && sz_cover_src($row) !== ''): ?>
  <figure class="mz-article-cover"><?= sz_cover($row, 'mz-article-img', true) ?></figure>
  <?php endif; ?>

  <div class="mz-prose"><?= $body ?></div>

  <div class="mz-end"><i></i><span><?= e(sz_t('全文完', 'END')) ?></span><i></i></div>

  <nav class="mz-pn">
    <?php if ($prev): ?><a href="<?= e(lang_path('/a/' . (int) $prev['id'])) ?>"><small>← <?= e(sz_t('上一篇', 'Previous')) ?></small><b><?= e(ml($prev, 'title')) ?></b></a><?php endif; ?>
    <?php if ($next): ?><a class="mz-pn-next" href="<?= e(lang_path('/a/' . (int) $next['id'])) ?>"><small><?= e(sz_t('下一篇', 'Next')) ?> →</small><b><?= e(ml($next, 'title')) ?></b></a><?php endif; ?>
  </nav>

  <?php if ($related): ?>
  <section class="mz-section mz-related">
    <div class="mz-section-head"><h2><?= e(sz_t('相关阅读', 'Related')) ?></h2><span>MORE</span></div>
    <?php foreach ($related as $r): ?>
    <a class="mz-related-item" href="<?= e(lang_path('/a/' . (int) $r['id'])) ?>">
      <span class="mz-related-thumb"><?= sz_cover($r, 'mz-related-img') ?></span>
      <span class="mz-related-body"><small><?= e(sz_date((string) $r['create_time'], false)) ?></small><b><?= e(ml($r, 'title')) ?></b></span>
    </a>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>
</article>

<?php include __DIR__ . '/_bottom.php'; ?>
