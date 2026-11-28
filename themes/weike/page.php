<?php include __DIR__ . '/_top.php'; ?>
<?php
$row   = $row ?? [];
$pages = $pages ?? [];
$isContact = (($row['slug'] ?? '') === 'contact');
$isAbout   = (($row['slug'] ?? '') === 'about');
$titleEn = $isAbout ? 'ABOUT US' : ($isContact ? 'CONTACT US' : strtoupper((string) ($row['slug'] ?? 'PAGE')));
?>
<div class="banner_s"><img src="/theme/<?= $isContact ? 'banner_c.jpg?v=2' : 'banner_b.jpg' ?>" alt="<?= e((string) ($row['title'] ?? '')) ?>"></div>
<div class="s_bg">
  <div class="scd clearfix">
    <ul class="scd_l">
      <?php foreach ($pages as $p): ?>
        <li class="<?= (($row['slug'] ?? '') === $p['slug']) ? 'now' : '' ?>">
          <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="scd_r">
      <div class="r_top r_top_a">
        <p><?= e($titleEn) ?></p>
        <em><?= e((string) ($row['title'] ?? '')) ?></em>
      </div>
      <div class="scd_rm">
        <div class="wk-crumb">
          <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a> / <?= e((string) ($row['title'] ?? '')) ?>
        </div>
        <div class="wk-page">
          <div class="about"><?= $row['content'] ?? '' ?></div>
          <?php if ($isContact): ?>
          <div class="wk-page-contact">
            <div class="item"><b><?= e(lang('销售热线')) ?></b><a href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><?= e($phone) ?></a></div>
            <div class="item"><b><?= e(lang('邮箱')) ?></b><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></div>
            <div class="item"><b><?= e(lang('厂区地址')) ?></b><?= e($address) ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/_bottom.php'; ?>


