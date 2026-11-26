<?php include __DIR__ . '/_top.php'; ?>

<section class="wrap sec">
  <div class="crumb">
    <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a> /
    <?php if ($cat): ?><a href="<?= e(lang_path('/c/' . $cat['slug'])) ?>"><?= e($cat['name']) ?></a> /<?php endif; ?>
    <?= e($row['title']) ?>
  </div>
  <article class="art">
    <h1><?= e($row['title']) ?></h1>
    <div class="art-meta"><?= e($row['create_time']) ?>　<?= e(lang('阅读')) ?> <?= (int) $row['views'] ?></div>
    <div class="art-body"><?= $row['content'] ?></div>
  </article>
  <div class="art-nav">
    <?php if ($prev): ?><a href="<?= e(lang_path('/a/' . (int) $prev['id'])) ?>">← <?= e($prev['title']) ?></a><?php else: ?><span></span><?php endif; ?>
    <?php if ($next): ?><a href="<?= e(lang_path('/a/' . (int) $next['id'])) ?>"><?= e($next['title']) ?> →</a><?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
