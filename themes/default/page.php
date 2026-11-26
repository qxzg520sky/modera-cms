<?php include __DIR__ . '/_top.php'; ?>

<section class="wrap sec">
  <div class="crumb"><a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a> / <?= e($row['title']) ?></div>
  <article class="art">
    <h1><?= e($row['title']) ?></h1>
    <div class="art-body"><?= $row['content'] ?></div>
  </article>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
