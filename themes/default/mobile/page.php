<?php include __DIR__ . '/_top.php'; ?>

<section class="msec">
  <div class="mcrumb"><a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a> / <?= e($row['title']) ?></div>
  <article class="mart">
    <h1><?= e($row['title']) ?></h1>
    <div class="mart-body"><?= $row['content'] ?></div>
  </article>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
