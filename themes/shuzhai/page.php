<?php
include_once __DIR__ . '/_helpers.php';
$row = $row ?? [];
$title = (string) ml($row, 'title');
$body = (string) ml($row, 'content');
$seoTitle = $title . ' - ' . setting('site_name', '书斋笔记');
$seoDesc = sz_excerpt(['summary' => $body], 100);
include __DIR__ . '/_top.php';
?>

<section class="sz-page-head sz-page-head-simple">
  <div class="sz-wrap sz-page-head-in">
    <div class="sz-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(sz_t('首页', 'Home')) ?></a><span>/</span><span><?= e($title) ?></span>
    </div>
    <div class="sz-page-title">
      <span class="sz-page-kicker">PAGE</span>
      <h1><?= e($title) ?></h1>
      <p><?= e(setting('site_slogan', '慢慢写，认真读')) ?></p>
    </div>
  </div>
</section>

<div class="sz-wrap sz-layout">
  <div class="sz-main-col">
    <div class="sz-prose sz-page-prose" data-prose><?= $body ?></div>
  </div>
  <?php include __DIR__ . '/_sidebar.php'; ?>
</div>

<?php include __DIR__ . '/_bottom.php'; ?>
