<?php include __DIR__ . '/_top.php'; ?>
<?php
$row = $row ?? [];
$cat = $cat ?? null;
$prev = $prev ?? null;
$next = $next ?? null;
$t = strtotime((string) ($row['create_time'] ?? ''));
$content = (string) ($row['content'] ?? '');
$cover = trim((string) ($row['cover'] ?? ''));
$showCover = $cover !== '' && ($content === '' || strpos($content, $cover) === false);
$title = (string) ($row['title'] ?? '');
?>
<section class="wx-page-hero wx-page-hero-sm">
  <img src="<?= e($cover !== '' ? $cover : '/theme/heroindustry.jpg') ?>" alt="<?= e($title) ?>">
  <div class="wx-inner">
    <small><?= e(strtoupper((string) ($cat['slug'] ?? 'ARTICLE'))) ?></small>
    <h1><?= e($title) ?></h1>
    <?php if ($t): ?><p><?= e(date('Y.m.d', $t)) ?><?php if (!empty($row['author'])): ?> · <?= e((string) $row['author']) ?><?php endif; ?></p><?php endif; ?>
  </div>
</section>

<div class="wx-page-body wx-inner wx-article-body">
  <div class="wx-crumb">
    <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a><span>/</span>
    <?php if ($cat): ?><a href="<?= e(lang_path('/c/' . $cat['slug'])) ?>"><?= e($cat['name']) ?></a><span>/</span><?php endif; ?>
    <?= e(lang('详情')) ?>
  </div>
  <article class="wx-article">
    <?php if ($showCover): ?><img class="wx-cover" src="<?= e($cover) ?>" alt="<?= e($title) ?>"><?php endif; ?>
    <div class="wx-content"><?= $content ?></div>
    <div class="wx-prevnext">
      <a href="<?= e($prev ? lang_path('/a/' . (int) $prev['id']) : 'javascript:;') ?>"<?= $prev ? '' : ' aria-disabled="true"' ?>>
        <span><?= e(lang('上一篇')) ?></span><?= e($prev['title'] ?? lang('没有了')) ?>
      </a>
      <a href="<?= e($next ? lang_path('/a/' . (int) $next['id']) : 'javascript:;') ?>"<?= $next ? '' : ' aria-disabled="true"' ?>>
        <span><?= e(lang('下一篇')) ?></span><?= e($next['title'] ?? lang('没有了')) ?>
      </a>
    </div>
  </article>
</div>
<?php include __DIR__ . '/_bottom.php'; ?>
