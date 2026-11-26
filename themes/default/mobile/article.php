<?php include __DIR__ . '/_top.php'; ?>

<section class="msec">
  <div class="mcrumb">
    <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a> /
    <?php if ($cat): ?><a href="<?= e(lang_path('/c/' . $cat['slug'])) ?>"><?= e($cat['name']) ?></a> /<?php endif; ?>
    <?= e(mb_substr((string) $row['title'], 0, 12)) ?>
  </div>

  <article class="mart">
    <h1><?= e($row['title']) ?></h1>
    <div class="mmate"><?= e(date('Y-m-d', strtotime((string) $row['create_time']))) ?>　<?= e(lang('阅读')) ?> <?= (int) $row['views'] ?></div>
    <div class="mart-body"><?= $row['content'] ?></div>
  </article>

  <div class="mart-nav">
    <?php if ($prev): ?><a class="mbtn" href="<?= e(lang_path('/a/' . (int) $prev['id'])) ?>">← <?= e(lang('上一篇')) ?></a><?php else: ?><span></span><?php endif; ?>
    <?php if ($next): ?><a class="mbtn" href="<?= e(lang_path('/a/' . (int) $next['id'])) ?>"><?= e(lang('下一篇')) ?> →</a><?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
