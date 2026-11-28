<?php include __DIR__ . '/_top.php'; ?>
<?php
$row = $row ?? [];
$pages = $pages ?? [];
$isContact = (($row['slug'] ?? '') === 'contact');
$isAbout = (($row['slug'] ?? '') === 'about');
$titleEn = $isAbout ? 'ABOUT US' : ($isContact ? 'CONTACT US' : strtoupper((string) ($row['slug'] ?? 'PAGE')));
$title = (string) ($row['title'] ?? '');
?>
<section class="wx-page-hero wx-page-hero-sm">
  <img src="/theme/heroindustry.jpg" alt="<?= e($title) ?>">
  <div class="wx-inner">
    <small><?= e($titleEn) ?></small>
    <h1><?= e($title) ?></h1>
  </div>
</section>

<div class="wx-page-body wx-inner">
  <aside class="wx-side">
    <h3><?= e(lang('关于唯星')) ?></h3>
    <ul>
      <?php foreach ($pages as $p): ?>
        <li class="<?= (($row['slug'] ?? '') === $p['slug']) ? 'now' : '' ?>">
          <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
        </li>
      <?php endforeach; ?>
      <li class="wx-side-contact"><a href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('需要选型建议？')) ?> →</a></li>
    </ul>
  </aside>
  <div class="wx-list-main">
    <div class="wx-crumb"><a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a><span>/</span><?= e($title) ?></div>
    <div class="wx-page-content <?= $isContact ? 'wx-page-contact' : '' ?>">
      <?= $row['content'] ?? '' ?>
      <?php if ($isContact): ?>
      <div class="wx-contact-cards">
        <a href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><span><?= e(lang('销售热线')) ?></span><b><?= e($phone) ?></b></a>
        <a href="mailto:<?= e($email) ?>"><span><?= e(lang('邮箱')) ?></span><b><?= e($email) ?></b></a>
        <p><span><?= e(lang('厂区地址')) ?></span><b><?= e($address) ?></b></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/_bottom.php'; ?>
