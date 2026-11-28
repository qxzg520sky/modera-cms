<?php include __DIR__ . '/_top.php'; ?>

<section class="gt-pagehero">
  <div class="gt-wrap">
    <h1><?= e($row['title']) ?></h1>
    <div class="gt-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a>
      <span>/</span>
      <b><?= e($row['title']) ?></b>
    </div>
  </div>
</section>

<div class="gt-wrap gt-sec gt-cols">
  <aside class="gt-aside">
    <div class="gt-anav">
      <h3><?= e(lang('关于我们')) ?></h3>
      <ul>
        <?php foreach ($pages as $p): ?>
          <li class="<?= $p['slug'] === $row['slug'] ? 'on' : '' ?>">
            <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?><span>›</span></a>
          </li>
        <?php endforeach; ?>
        <?php foreach ($nav as $c): ?>
          <li><a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?><span>›</span></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="gt-acontact">
      <h3><?= e(lang('联系方式')) ?></h3>
      <p class="gt-ac-tel"><?= e(setting('site_phone', '')) ?></p>
      <p><?= e(setting('site_address', '')) ?></p>
      <p><?= e(setting('site_email', '')) ?></p>
      <a class="gt-btn gt-btn-sm" href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('在线咨询')) ?></a>
    </div>
  </aside>

  <section class="gt-detail">
    <article class="gt-art">
      <h1><?= e($row['title']) ?></h1>
      <div class="gt-art-body gt-page-body"><?= $row['content'] ?></div>
    </article>
  </section>
</div>

<?php include __DIR__ . '/_bottom.php'; ?>
