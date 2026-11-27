<?php
include __DIR__ . '/_helpers.php';
include __DIR__ . '/_top.php';

$articles = $articles ?? [];
$cat = $cat ?? ['name' => '笔记', 'slug' => '', 'seo_description' => ''];
$total = (int) ($total ?? count($articles));
$subNav = $subNav ?? [];
$pager = (string) ($pager ?? '');
$pageNo = max(1, (int) ($_GET['page'] ?? 1));
$catDesc = trim((string) ($cat['seo_description'] ?? ''));
if ($catDesc === '') {
    $catDesc = lang('这个栏目下的全部笔记，按时间倒序排列。');
}
?>
<section class="page-hero wrap">
  <div class="crumb">
    <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a>
    <span>/</span>
    <span><?= e($cat['name'] ?? lang('笔记')) ?></span>
  </div>
  <p class="eyebrow"><span>CATEGORY</span><i></i><?= $total ?> <?= e(lang('篇')) ?></p>
  <h1><?= e($cat['name'] ?? lang('笔记')) ?></h1>
  <p class="page-lede"><?= e($catDesc) ?></p>
  <?php if ($pageNo > 1): ?><p class="page-no"><?= e(lang('第')) ?> <?= $pageNo ?> <?= e(lang('页')) ?></p><?php endif; ?>
  <?php if ($subNav): ?>
  <div class="subnav">
    <?php foreach ($subNav as $s): ?>
      <a href="<?= e(lang_path('/c/' . $s['slug'])) ?>"<?= (int) $s['id'] === (int) ($cat['id'] ?? 0) ? ' class="now"' : '' ?>><?= e($s['name']) ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<section class="list-body wrap">
  <?php if ($articles): ?>
    <div class="post-grid">
      <?php foreach ($articles as $i => $a): ?>
        <?= ink_card($a, (($pageNo - 1) * 10) + $i + 1, $i === 0) ?>
      <?php endforeach; ?>
    </div>
    <?= ink_pager($pager) ?>
  <?php else: ?>
    <div class="empty-state">
      <p class="eyebrow"><span>EMPTY</span><i></i>NO NOTES</p>
      <h2><?= e(lang('这个栏目还没有文章。')) ?></h2>
      <p><?= e(lang('换一个栏目看看，或者回到首页浏览最新笔记。')) ?></p>
      <a class="btn btn-ink" href="<?= e(lang_path('/')) ?>"><?= e(lang('回到首页')) ?><span aria-hidden="true">↗</span></a>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/_bottom.php'; ?>
