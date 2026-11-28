<?php
include_once __DIR__ . '/_helpers.php';
$row = $row ?? [];
$cat = $cat ?? [];
$prev = $prev ?? null;
$next = $next ?? null;
$title = (string) ml($row, 'title');
$body = (string) ml($row, 'content');
$summary = trim((string) ml($row, 'summary'));
$showHero = !sz_cover_is_inline($row);
$heroSrc = sz_cover_src($row);

$related = [];
if (!empty($row['id']) && !empty($row['category_id'])) {
    $related = \think\facade\Db::name('article')
        ->where('status', 1)
        ->where('id', '<>', (int) $row['id'])
        ->where('category_id', (int) $row['category_id'])
        ->order('id desc')->limit(3)->select()->toArray();
}

$seoTitle = $title . ' - ' . setting('site_name', '书斋笔记');
$seoDesc = $summary !== '' ? $summary : sz_excerpt($row, 120);
include __DIR__ . '/_top.php';
?>

<article class="sz-article">
  <div class="sz-wrap sz-article-head">
    <div class="sz-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(sz_t('首页', 'Home')) ?></a><span>/</span>
      <?php if ($cat): ?><a href="<?= e(lang_path('/c/' . $cat['slug'])) ?>"><?= e(sz_cat_name($cat)) ?></a><span>/</span><?php endif; ?>
      <span><?= e($title) ?></span>
    </div>
    <div class="sz-article-title-row">
      <div class="sz-article-title-main">
        <div class="sz-meta">
          <span class="sz-cat"><?= e($cat ? sz_cat_name($cat) : sz_t('随笔', 'Notes')) ?></span>
          <span><?= e(sz_date((string) ($row['create_time'] ?? ''))) ?></span>
          <span><?= e(sz_reading_time($row)) ?> <?= e(sz_t('分钟阅读', 'min read')) ?></span>
          <span>NO.<?= e(sz_issue($row)) ?></span>
        </div>
        <h1><?= e($title) ?></h1>
        <?php if ($summary !== ''): ?><p class="sz-article-summary"><?= e($summary) ?></p><?php endif; ?>
        <div class="sz-byline">
          <span class="sz-byline-avatar"><?= current_lang() === 'en' ? 'S' : '书' ?></span>
          <div><b><?= e((string) ($row['author'] ?? setting('site_name', '书斋笔记'))) ?></b><small><?= e(sz_t('作者', 'Author')) ?></small></div>
        </div>
      </div>
      <div class="sz-article-side-note">
        <span>ISSUE</span>
        <b><?= e(sz_issue($row)) ?></b>
        <small><?= e(date('Y', strtotime((string) ($row['create_time'] ?? 'now')))) ?></small>
      </div>
    </div>
  </div>

  <?php if ($showHero && $heroSrc !== ''): ?>
  <figure class="sz-wrap sz-article-cover">
    <?= sz_cover($row, 'sz-article-cover-img', true) ?>
    <figcaption><span><?= e(sz_t('题图', 'Cover')) ?></span><span><?= e(setting('site_name', '书斋笔记')) ?> · <?= e(sz_date((string) ($row['create_time'] ?? ''), false)) ?></span></figcaption>
  </figure>
  <?php endif; ?>

  <div class="sz-wrap sz-layout sz-article-layout">
    <div class="sz-main-col">
      <div class="sz-prose" data-prose><?= $body ?></div>

      <div class="sz-article-end"><i></i><span><?= e(sz_t('全文完', 'END')) ?></span><i></i></div>

      <nav class="sz-pn">
        <?php if ($prev): ?>
        <a class="sz-pn-item" href="<?= e(lang_path('/a/' . (int) $prev['id'])) ?>">
          <small><?= e(sz_t('上一篇', 'Previous')) ?></small>
          <b><?= e(ml($prev, 'title')) ?></b>
        </a>
        <?php endif; ?>
        <?php if ($next): ?>
        <a class="sz-pn-item sz-pn-next" href="<?= e(lang_path('/a/' . (int) $next['id'])) ?>">
          <small><?= e(sz_t('下一篇', 'Next')) ?></small>
          <b><?= e(ml($next, 'title')) ?></b>
        </a>
        <?php endif; ?>
      </nav>

      <?php if ($related): ?>
      <section class="sz-related">
        <div class="sz-block-head"><h2><?= e(sz_t('相关阅读', 'Related reading')) ?></h2><span>MORE</span></div>
        <div class="sz-related-grid">
          <?php foreach ($related as $r): ?>
          <a class="sz-related-card" href="<?= e(lang_path('/a/' . (int) $r['id'])) ?>">
            <div class="sz-related-media"><?= sz_cover($r, 'sz-related-img') ?></div>
            <div class="sz-related-body">
              <span><?= e(sz_date((string) $r['create_time'], false)) ?></span>
              <h3><?= e(ml($r, 'title')) ?></h3>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>
    </div>

    <?php include __DIR__ . '/_sidebar.php'; ?>
  </div>
</article>

<?php include __DIR__ . '/_bottom.php'; ?>
