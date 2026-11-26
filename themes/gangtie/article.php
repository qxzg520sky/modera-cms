<?php include __DIR__ . '/_top.php'; ?>

<section class="gt-pagehero">
  <div class="gt-wrap">
    <h1><?= e($cat['name'] ?? lang('详情')) ?></h1>
    <div class="gt-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a>
      <span>/</span>
      <?php if ($cat): ?><a href="<?= e(lang_path('/c/' . $cat['slug'])) ?>"><?= e($cat['name']) ?></a><span>/</span><?php endif; ?>
      <b><?= e($row['title']) ?></b>
    </div>
  </div>
</section>

<div class="gt-wrap gt-sec gt-cols">
  <?php include __DIR__ . '/_aside.php'; ?>

  <section class="gt-detail">
    <article class="gt-art">
      <h1><?= e($row['title']) ?></h1>
      <div class="gt-art-meta">
        <span><?= e(date('Y-m-d H:i', strtotime((string) $row['create_time']))) ?></span>
        <span><?= e(lang('阅读')) ?> <?= (int) $row['views'] ?></span>
      </div>
      <div class="gt-art-body"><?= $row['content'] ?></div>
    </article>
    <div class="gt-art-nav">
      <?php if ($prev): ?>
        <a href="<?= e(lang_path('/a/' . (int) $prev['id'])) ?>"><i><?= e(lang('上一篇')) ?></i><b><?= e($prev['title']) ?></b></a>
      <?php else: ?><span class="gt-art-nav-empty"></span><?php endif; ?>
      <?php if ($next): ?>
        <a href="<?= e(lang_path('/a/' . (int) $next['id'])) ?>"><i><?= e(lang('下一篇')) ?></i><b><?= e($next['title']) ?></b></a>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php include __DIR__ . '/_bottom.php'; ?>
