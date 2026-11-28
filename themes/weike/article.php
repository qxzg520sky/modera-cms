<?php include __DIR__ . '/_top.php'; ?>
<?php
$row  = $row ?? [];
$cat  = $cat ?? null;
$prev = $prev ?? null;
$next = $next ?? null;
$t = strtotime((string) ($row['create_time'] ?? ''));
$content = (string) ($row['content'] ?? '');
$cover = trim((string) ($row['cover'] ?? ''));
// 后台正文里通常已经插入了封面图，避免同一张图在页面里出现两次
$showCover = $cover !== '' && ($content === '' || strpos($content, $cover) === false);
?>
<div class="banner_s"><img src="/theme/banner_b_clean.jpg" alt="<?= e((string) ($row['title'] ?? '')) ?>"></div>
<div class="s_bg">
  <div class="scd clearfix">
    <div class="scd_r wk-r-full">
      <div class="r_top r_top_a">
        <p><?= e(strtoupper((string) ($cat['slug'] ?? 'ARTICLE'))) ?></p>
        <em><?= e((string) ($cat['name'] ?? lang('文章详情'))) ?></em>
      </div>
      <div class="scd_rm">
        <div class="wk-crumb">
          <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a>
          <?php if ($cat): ?> / <a href="<?= e(lang_path('/c/' . $cat['slug'])) ?>"><?= e($cat['name']) ?></a><?php endif; ?>
          / <?= e(lang('详情')) ?>
        </div>
        <div class="wk-article">
          <h1><?= e((string) $row['title']) ?></h1>
          <div class="wk-meta">
            <?php if ($t): ?><span><?= e(date('Y-m-d H:i', $t)) ?></span><?php endif; ?>
            <?php if (!empty($row['author'])): ?><span><?= e((string) $row['author']) ?></span><?php endif; ?>
            <span><?= e(lang('阅读')) ?> <?= (int) ($row['views'] ?? 0) ?></span>
          </div>
          <?php if ($showCover): ?>
            <img class="wk-cover" src="<?= e($cover) ?>" alt="<?= e((string) $row['title']) ?>">
          <?php endif; ?>
          <div class="wk-content"><?= $content ?></div>

          <div class="wk-prevnext">
            <a href="<?= e($prev ? lang_path('/a/' . (int) $prev['id']) : 'javascript:;') ?>"<?= $prev ? '' : ' aria-disabled="true"' ?>>
              <span><?= e(lang('上一篇')) ?></span>
              <?= e($prev['title'] ?? lang('没有了')) ?>
            </a>
            <a href="<?= e($next ? lang_path('/a/' . (int) $next['id']) : 'javascript:;') ?>"<?= $next ? '' : ' aria-disabled="true"' ?>>
              <span><?= e(lang('下一篇')) ?></span>
              <?= e($next['title'] ?? lang('没有了')) ?>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/_bottom.php'; ?>


