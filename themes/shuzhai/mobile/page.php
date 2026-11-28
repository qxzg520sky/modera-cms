<?php
include_once __DIR__ . '/../_helpers.php';
$row = $row ?? [];
$title = (string) ml($row, 'title');
$body = (string) ml($row, 'content');
$seoTitle = $title . ' - ' . setting('site_name', '书斋笔记');
$seoDesc = sz_excerpt(['summary' => $body], 100);
include __DIR__ . '/_top.php';
?>

<section class="mz-page-head mz-page-head-simple">
  <span class="mz-label">PAGE</span>
  <h1><?= e($title) ?></h1>
  <p><?= e(setting('site_slogan', '慢慢写，认真读')) ?></p>
</section>
<div class="mz-prose mz-page-prose"><?= $body ?></div>

<?php include __DIR__ . '/_bottom.php'; ?>
