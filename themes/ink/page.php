<?php
include __DIR__ . '/_helpers.php';
$row = $row ?? [];
$title = (string) ml($row, 'title');
$body = (string) ($row['content'] ?? '');
include __DIR__ . '/_top.php';
?>
<section class="page-hero page-hero-simple wrap">
  <div class="crumb">
    <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a><span>/</span><span><?= e($title) ?></span>
  </div>
  <p class="eyebrow"><span>PAGE</span><i></i><?= e(setting('site_name', '墨白笔记')) ?></p>
  <h1><?= e($title) ?></h1>
</section>

<article class="page-body wrap">
  <div class="prose" data-prose><?= $body ?></div>
  <div class="article-end"><span>END OF PAGE</span><i aria-hidden="true"></i></div>
</article>
<?php include __DIR__ . '/_bottom.php'; ?>
