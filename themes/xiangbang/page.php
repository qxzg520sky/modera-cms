<?php include __DIR__ . '/_top.php'; ?>
<?php
$row   = $row ?? [];
$pages = $pages ?? [];
$isContact = (($row['slug'] ?? '') === 'contact');
$isAbout   = (($row['slug'] ?? '') === 'about');
$title = (string) ml($row, 'title');
$content = (string) ($row['content'] ?? '');
$desc = trim((string) ($row['seo_description'] ?? ''));
if ($desc === '') {
    $plain = trim(strip_tags($content));
    $desc = mb_strlen($plain, 'UTF-8') > 100 ? mb_substr($plain, 0, 100, 'UTF-8') . '…' : $plain;
}
$crumbs = [['name' => lang('首页', '首页'), 'url' => lang_path('/')], ['name' => $title]];
xb_page_hero($title, $desc, $crumbs, $isContact ? 'CONTACT' : ($isAbout ? 'ABOUT US' : strtoupper((string) ($row['slug'] ?? 'PAGE'))));
?>

<nav class="xb-subnav" aria-label="<?= e($title) ?>">
  <div class="xb-container">
    <ul class="xb-subnav-list">
      <?php foreach ($pages as $i => $p): ?>
        <li><a class="<?= (($row['slug'] ?? '') === $p['slug']) ? 'on' : '' ?>" href="<?= e(xb_page_url((string) $p['slug'])) ?>"><i><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></i><?= e(ml($p, 'title')) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</nav>

<?php if ($isContact): ?>
<section class="xb-section xb-contact-section">
  <div class="xb-container xb-contact-layout">
    <div class="xb-contact-copy" data-reveal>
      <span class="xb-eyebrow">01 / <?= e(lang('联系方式', '联系方式')) ?></span>
      <h2 class="xb-section-title"><?= e($title) ?></h2>
      <p class="xb-section-desc"><?= e($desc) ?></p>
      <div class="xb-contact-list">
        <?php if ($phone !== ''): ?><div><span><?= e(lang('服务热线', '服务热线')) ?></span><strong><a href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><?= e($phone) ?></a></strong></div><?php endif; ?>
        <?php if ($email !== ''): ?><div><span><?= e(lang('邮箱', '邮箱')) ?></span><strong><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></strong></div><?php endif; ?>
        <?php if ($address !== ''): ?><div><span><?= e(lang('地址', '地址')) ?></span><strong><?= e($address) ?></strong></div><?php endif; ?>
        <div><span><?= e(lang('服务时间', '服务时间')) ?></span><strong><?= e(lang('周一至周日 08:00–18:00', '周一至周日 08:00–18:00')) ?></strong></div>
      </div>
      <?php if ($content !== ''): ?><div class="xb-page-content xb-contact-content"><?= $content ?></div><?php endif; ?>
    </div>

    <aside class="xb-contact-card" data-reveal data-delay="1">
      <span class="xb-eyebrow xb-eyebrow-light">02 / <?= e(lang('扫码联系', '扫码联系')) ?></span>
      <h3><?= e(lang('加微信，先聊清楚', '加微信，先聊清楚')) ?></h3>
      <p><?= e(lang('产品咨询、方案配置、项目合作与售后问题，都可以通过官方微信或服务热线联系。', '产品咨询、方案配置、项目合作与售后问题，都可以通过官方微信或服务热线联系。')) ?></p>
      <div class="xb-qr-row xb-qr-row-light">
        <div><img src="/theme/ewm1.jpg" alt="<?= e(lang('加盟微信', '加盟微信')) ?>" loading="lazy"><span><?= e(lang('加盟微信', '加盟微信')) ?></span></div>
        <div><img src="/theme/ewm2.jpg" alt="<?= e(lang('官方微信', '官方微信')) ?>" loading="lazy"><span><?= e(lang('官方微信', '官方微信')) ?></span></div>
        <div><img src="/theme/ewm3.jpg" alt="<?= e(lang('官方微博', '官方微博')) ?>" loading="lazy"><span><?= e(lang('官方微博', '官方微博')) ?></span></div>
      </div>
      <?php if ($phone !== ''): ?><a class="xb-btn xb-btn-lime" href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><?= e(lang('拨打服务热线', '拨打服务热线')) ?> <span>→</span></a><?php endif; ?>
    </aside>
  </div>
</section>
<?php else: ?>
<section class="xb-section xb-page-section">
  <div class="xb-container xb-page-layout">
    <article class="xb-page-article" data-reveal>
      <div class="xb-article-meta"><span class="xb-article-cat"><?= e($title) ?></span></div>
      <div class="xb-article-content xb-page-content"><?= $content ?></div>
    </article>
    <aside class="xb-page-side">
      <div class="xb-side-card">
        <span class="xb-eyebrow"><?= e(lang('快速了解', '快速了解')) ?></span>
        <h3><?= e($siteName) ?></h3>
        <p><?= e(mb_substr(trim((string) $slogan), 0, 40, 'UTF-8')) ?></p>
        <?php foreach ($pages as $p): ?>
          <a class="xb-side-link" href="<?= e(xb_page_url((string) $p['slug'])) ?>"><strong><?= e(ml($p, 'title')) ?></strong><span>→</span></a>
        <?php endforeach; ?>
      </div>
      <div class="xb-side-contact">
        <span class="xb-eyebrow xb-eyebrow-light"><?= e(lang('联系我们', '联系我们')) ?></span>
        <h3><?= e(lang('需要更具体的建议？', '需要更具体的建议？')) ?></h3>
        <p><?= e(lang('告诉我们你的场景与目标，我们会给出更具体的方案。', '告诉我们你的场景与目标，我们会给出更具体的方案。')) ?></p>
        <?php if ($phone !== ''): ?><a class="xb-btn xb-btn-lime" href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><?= e($phone) ?> <span>→</span></a><?php endif; ?>
      </div>
    </aside>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/_bottom.php'; ?>
