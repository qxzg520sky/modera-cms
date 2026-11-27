<?php
include __DIR__ . '/_helpers.php';

$row = $row ?? [];
$cat = $cat ?? null;
$prev = $prev ?? null;
$next = $next ?? null;
$title = (string) ml($row, 'title');
$summary = trim((string) ml($row, 'summary'));
if ($summary === '') {
    $summary = ink_summary($row, 150);
}
$body = (string) ($row['content'] ?? '');
$cover = trim((string) ($row['cover'] ?? ''));
$showCover = $cover === '' || strpos($body, $cover) === false;
$catName = $cat['name'] ?? ink_cat_name($row);
$views = (int) ($row['views'] ?? 0);
$author = trim((string) ($row['author'] ?? '')) ?: setting('site_name', '墨白笔记');
$chars = mb_strlen(trim(strip_tags($body)), 'UTF-8');
$minutes = max(2, (int) ceil($chars / 520));
$date = ink_date($row);
$dateIso = strtotime((string) ($row['create_time'] ?? ''));

$related = [];
if (!empty($row['category_id'])) {
    $related = \think\facade\Db::name('article')->where('status', 1)
        ->where('category_id', (int) $row['category_id'])->where('id', '<>', (int) $row['id'])
        ->order('is_top desc, id desc')->limit(3)->select()->toArray();
}

include __DIR__ . '/_top.php';

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $title,
    'description' => $summary,
    'datePublished' => $dateIso ? date('c', $dateIso) : null,
    'dateModified' => $dateIso ? date('c', max($dateIso, (int) (strtotime((string) ($row['update_time'] ?? '')) ?: 0))) : null,
    'author' => ['@type' => 'Person', 'name' => $author],
    'publisher' => ['@type' => 'Organization', 'name' => setting('site_name', '墨白笔记')],
    'mainEntityOfPage' => $canonical ?: null,
    'wordCount' => $chars,
];
if ($cover !== '') {
    $schema['image'] = ink_abs_url($cover);
}
$schema = array_filter($schema, static fn ($v) => $v !== null && $v !== '');
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

<article class="post-page">
  <header class="article-head wrap">
    <div class="crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a><span>/</span>
      <a href="<?= e(lang_path('/c/' . ($cat['slug'] ?? ink_cat_slug($row)))) ?>"><?= e($catName) ?></a><span>/</span>
      <span><?= e(lang('正文')) ?></span>
    </div>
    <div class="article-meta">
      <a class="tag" href="<?= e(lang_path('/c/' . ($cat['slug'] ?? ink_cat_slug($row)))) ?>"><?= e($catName) ?></a>
      <time datetime="<?= $dateIso ? e(date('c', $dateIso)) : '' ?>"><?= e($date) ?></time>
      <span><?= $views ?> <?= e(lang('阅读')) ?></span>
      <span><?= e(lang('约')) ?> <?= $minutes ?> <?= e(lang('分钟')) ?></span>
    </div>
    <h1><?= e($title) ?></h1>
    <p class="article-lede"><?= e($summary) ?></p>
    <div class="byline">
      <span class="byline-avatar" aria-hidden="true"><?= e(mb_substr($author, 0, 1, 'UTF-8')) ?></span>
      <div><b><?= e($author) ?></b><small>WORDS BY <?= e(strtoupper(mb_substr($author, 0, 1, 'UTF-8'))) ?> · <?= e($date) ?></small></div>
    </div>
  </header>

  <?php if ($showCover): ?>
  <div class="article-cover wrap">
    <?= ink_cover($row, 'hero', true) ?>
  </div>
  <?php endif; ?>

  <div class="article-body wrap">
    <div class="prose" data-prose><?= $body ?></div>

    <div class="article-end">
      <span>END OF NOTE</span>
      <i aria-hidden="true"></i>
    </div>

    <div class="article-foot">
      <div class="foot-tags">
        <span><?= e(lang('栏目')) ?></span>
        <a class="tag" href="<?= e(lang_path('/c/' . ($cat['slug'] ?? ink_cat_slug($row)))) ?>"><?= e($catName) ?></a>
      </div>
      <div class="foot-sign"><?= e(lang('写于')) ?> <?= e($date) ?> · <?= e(setting('site_name', '墨白笔记')) ?></div>
    </div>
  </div>

  <nav class="prev-next wrap" aria-label="<?= e(lang('上一篇下一篇')) ?>">
    <?php if ($prev): ?>
      <a class="pn pn-prev" href="<?= e(lang_path('/a/' . (int) $prev['id'])) ?>">
        <span><?= e(lang('上一篇')) ?></span>
        <b><?= e((string) ml($prev, 'title')) ?></b>
      </a>
    <?php else: ?>
      <div class="pn pn-empty"><span><?= e(lang('上一篇')) ?></span><b><?= e(lang('已经是最新一篇')) ?></b></div>
    <?php endif; ?>
    <?php if ($next): ?>
      <a class="pn pn-next" href="<?= e(lang_path('/a/' . (int) $next['id'])) ?>">
        <span><?= e(lang('下一篇')) ?></span>
        <b><?= e((string) ml($next, 'title')) ?></b>
      </a>
    <?php else: ?>
      <div class="pn pn-empty"><span><?= e(lang('下一篇')) ?></span><b><?= e(lang('已经是最后一篇')) ?></b></div>
    <?php endif; ?>
  </nav>
</article>

<?php if ($related): ?>
<section class="related wrap">
  <div class="section-head">
    <div><p class="eyebrow"><span>RELATED</span><i></i><?= e(lang('同栏目')) ?></p><h2><?= e(lang('继续阅读')) ?></h2></div>
    <a class="text-link" href="<?= e(lang_path('/c/' . ($cat['slug'] ?? ink_cat_slug($row)))) ?>"><?= e(lang('查看全部')) ?><span aria-hidden="true">↗</span></a>
  </div>
  <div class="post-grid post-grid-3">
    <?php foreach ($related as $i => $a): ?>
      <?= ink_card($a, $i + 1, false) ?>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<?php include __DIR__ . '/_bottom.php'; ?>
