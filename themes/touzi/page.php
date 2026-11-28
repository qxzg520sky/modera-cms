<?php include __DIR__ . '/_top.php'; ?>
<?php
$slug      = (string) ($row['slug'] ?? '');
$pageName  = trim((string) ml($row, 'title'));
$phone     = setting('site_phone', '');
$phone2    = setting('site_phone2', '010-1111111');
$email     = setting('site_email', '');
$address   = setting('site_address', '');
$bannerMap = [
    'about'   => '/uploads/factory.jpg',
    'contact' => '/uploads/showroom.jpg',
    'culture' => '/uploads/team.jpg',
];
$heroImg = $bannerMap[$slug] ?? '/uploads/team.jpg';
$isContact = ($slug === 'contact');
$headingMap = ['about' => 'ABOUT US', 'contact' => 'CONTACT US', 'culture' => 'CULTURE & INSTITUTE'];
$headingEn = $headingMap[$slug] ?? 'PAGE';
$contactUrl = lang_path('/p/contact');
$sideLabel = ['about' => '公司简介', 'culture' => '企业文化'][$slug] ?? $pageName;
$contentHtml = (string) ml($row, 'content');
if ($heroImg && $contentHtml !== '') {
    $heroBase = preg_quote(basename((string) parse_url($heroImg, PHP_URL_PATH)), '#');
    $contentHtml = preg_replace('#<img[^>]+src="[^"]*' . $heroBase . '[^"]*"[^>]*>#i', '', $contentHtml, 1);
}
$cnLen = mb_strlen($pageName);
$cnPrefix = $cnLen > 2 ? mb_substr($pageName, 0, $cnLen - 2) : $pageName;
$cnTail = $cnLen > 2 ? mb_substr($pageName, -2) : '';
?>

<section class="tz-inner-banner" style="background-image:url('<?= e($heroImg) ?>')">
  <div class="tz-inner-banner-shade"></div>
  <div class="tz-container tz-banner-crumb">
    <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a>
    <span>/</span>
    <em><?= e($pageName) ?></em>
  </div>
  <div class="tz-side-nav">
    <a class="tz-side-toggle" href="javascript:;">MENU <i>☰</i></a>
    <div class="tz-side-menu">
      <ul>
        <?php foreach ($pages as $pp): ?>
          <?php if ((string) ($pp['template'] ?? '') === 'page' || in_array((string) $pp['slug'], ['about', 'contact', 'culture'], true)): ?>
            <li class="<?= (string) $pp['slug'] === $slug ? 'on' : '' ?>"><a href="<?= e(lang_path('/p/' . $pp['slug'])) ?>"><?= e(ml($pp, 'title')) ?></a></li>
          <?php endif; ?>
        <?php endforeach; ?>
        <li><a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a></li>
        <li><a href="javascript:;" class="tz-gotop">返回顶部 ↑</a></li>
      </ul>
    </div>
  </div>
</section>

<section class="tz-inner-head">
  <div class="tz-container">
    <div class="tz-heading tz-heading-simple">
      <span class="tz-en"><?= e($headingEn) ?></span>
      <h2><?= e($cnPrefix) ?><?php if ($cnTail): ?><b><?= e($cnTail) ?></b><?php endif; ?></h2>
    </div>
  </div>
</section>

<section class="tz-page-view">
  <div class="tz-container">
    <?php if ($isContact): ?>
      <div class="tz-contact-grid">
        <div class="tz-contact-card">
          <span>01</span>
          <h3><?= e(lang('公司地址')) ?></h3>
          <p><?= e($address) ?></p>
          <em>ADDRESS</em>
        </div>
        <div class="tz-contact-card">
          <span>02</span>
          <h3><?= e(lang('联系电话')) ?></h3>
          <p><a href="tel:<?= e(preg_replace('/[^0-9+\-]/', '', (string) $phone)) ?>"><?= e($phone) ?></a><br><?= e($phone2) ?></p>
          <em>HOTLINE</em>
        </div>
        <div class="tz-contact-card">
          <span>03</span>
          <h3><?= e(lang('电子邮箱')) ?></h3>
          <p><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><br><?= e(lang('工作日 8:30—18:00 回复')) ?></p>
          <em>E-MAIL</em>
        </div>
      </div>
      <div class="tz-page-card">
        <div class="tz-page-rich"><?= ml($row, 'content') ?></div>
      </div>
    <?php else: ?>
      <div class="tz-page-card">
        <div class="tz-page-side">
          <h3><?= e($sideLabel) ?></h3>
          <p><?= e(setting('site_slogan', '以匠心制造 · 为工业赋能')) ?></p>
          <a href="<?= e($contactUrl) ?>"><?= e(lang('联系我们')) ?> →</a>
        </div>
        <div class="tz-page-rich"><?= $contentHtml ?></div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
