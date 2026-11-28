<?php include __DIR__ . '/_top.php'; ?>
<?php
$slug      = (string) ($row['slug'] ?? '');
$pageName  = trim((string) ml($row, 'title'));
$phone     = setting('site_phone', '');
$email     = setting('site_email', '');
$address   = setting('site_address', '');
$bannerMap = [
    'about'   => '/uploads/team.jpg',
    'contact' => '/uploads/factory.jpg',
    'culture' => '/uploads/workshop.jpg',
];
$heroImg   = $bannerMap[$slug] ?? '/uploads/team.jpg';
$isContact = ($slug === 'contact');
$contactUrl = lang_path('/p/' . ($slug ?: 'contact'));
?>

<section class="sw-inner-hero" style="background-image:url('<?= e($heroImg) ?>')">
  <div class="sw-container">
    <span class="eyebrow"><?= e(lang('关于企业')) ?></span>
    <h1><?= e($pageName) ?></h1>
    <div class="sw-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a>
      <i>/</i>
      <span><?= e($pageName) ?></span>
    </div>
  </div>
</section>

<section class="sw-inner sw-page-view">
  <div class="sw-container sw-inner-grid">
    <aside class="sw-side">
      <div class="sw-side-block">
        <h3><?= e(lang('关于我们')) ?></h3>
        <div class="sw-side-list">
          <?php foreach ($pages as $pp): ?>
            <?php if (in_array((string) $pp['slug'], ['about', 'culture', 'contact'], true) || (string) ($pp['template'] ?? '') === 'page'): ?>
              <a class="<?= ((string) $pp['slug'] === $slug) ? 'on' : '' ?>" href="<?= e(lang_path('/p/' . $pp['slug'])) ?>"><?= e(ml($pp, 'title')) ?></a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="sw-side-cta">
        <b><?= e(lang('项目咨询')) ?></b>
        <a class="tel" href="tel:<?= e(preg_replace('/[^0-9+\-]/', '', (string) $phone)) ?>"><?= e($phone) ?></a>
        <a href="<?= e($contactUrl) ?>"><?= e(lang('获取方案与报价')) ?> →</a>
      </div>
    </aside>

    <div class="sw-content">
      <div class="sw-content-head">
        <h2><?= e($pageName) ?></h2>
        <em><?= e(strtoupper($slug ?: 'PAGE')) ?></em>
      </div>

      <?php if ($isContact): ?>
        <div class="sw-contact-grid">
          <div class="sw-contact-card">
            <h3><?= e(lang('公司地址')) ?></h3>
            <em>ADDRESS</em>
            <p><?= e($address) ?></p>
          </div>
          <div class="sw-contact-card">
            <h3><?= e(lang('联系电话')) ?></h3>
            <em>HOTLINE</em>
            <p><a href="tel:<?= e(preg_replace('/[^0-9+\-]/', '', (string) $phone)) ?>"><?= e($phone) ?></a><br><?= e(setting('site_phone2', '010-1111111')) ?></p>
          </div>
          <div class="sw-contact-card">
            <h3><?= e(lang('电子邮箱')) ?></h3>
            <em>E-MAIL</em>
            <p><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><br><?= e(lang('工作日 8:30—18:00 回复')) ?></p>
          </div>
        </div>
        <div class="sw-rich"><?= ml($row, 'content') ?></div>
        <div class="sw-contact-map">
          <div>
            <b><?= e(lang('来访路线')) ?></b>
            <p><?= e($address) ?><br><?= e(lang('自驾或公共交通到厂前，请提前电话联系，我们安排门岗接待。')) ?></p>
          </div>
        </div>
      <?php else: ?>
        <?php if ($slug === 'about'): ?>
          <div class="sw-about-img"><img src="/uploads/factory.jpg" alt="<?= e($pageName) ?>"></div>
        <?php endif; ?>
        <div class="sw-rich"><?= ml($row, 'content') ?></div>
        <?php if ($slug === 'about'): ?>
          <div style="margin-top:30px;padding-top:22px;border-top:1px dashed #dfe5eb;display:flex;flex-wrap:wrap;gap:10px">
            <a class="sw-btn sw-btn-primary" href="<?= e(lang_path('/c/solution')) ?>"><?= e(lang('查看解决方案')) ?></a>
            <a class="sw-btn" style="border-color:#d7e0ea;color:#004588" href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('联系我们')) ?></a>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>

