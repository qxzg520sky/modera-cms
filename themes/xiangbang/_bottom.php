<?php
$siteName  = setting('site_name', '企业网站');
$slogan    = (string) setting('site_slogan', '');
$phone     = (string) setting('site_phone', '');
$email     = (string) setting('site_email', '');
$address   = (string) setting('site_address', '');
$copyright = (string) setting('site_copyright', '© ' . date('Y') . ' ' . $siteName);
$icp       = (string) setting('site_icp', '');
$nav       = $nav ?? [];
$pages     = $pages ?? [];
$contactPage = null;
foreach ($pages as $p) {
    if (($p['slug'] ?? '') === 'contact') { $contactPage = $p; }
}
$contactUrl = $contactPage ? xb_page_url((string) $contactPage['slug']) : lang_path('/');
$shopEnabled = false;
try {
    $shopEnabled = (bool) \think\facade\Db::name('plugin')->where('name', 'shop')->where('enabled', 1)->find();
} catch (\Throwable $e) {
}
?>
</main>

<footer class="xb-footer">
  <div class="xb-container">
    <div class="xb-foot-top">
      <div class="xb-foot-brand">
        <div class="xb-foot-wordmark"><strong><?= e($siteName) ?></strong><span>NATURAL · BUILT TO LAST</span></div>
        <?php if ($slogan !== ''): ?><p class="xb-foot-slogan"><?= e($slogan) ?></p><?php endif; ?>
        <p class="xb-foot-copy">把自然质感、稳定品质与长期服务，做成可以落地的空间与产品方案。</p>
        <?php if ($phone !== ''): ?>
        <a class="xb-foot-phone" href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><span><?= e(lang('全国服务热线', '全国服务热线')) ?></span><strong><?= e($phone) ?></strong></a>
        <?php endif; ?>
      </div>

      <div class="xb-foot-links">
        <div class="xb-foot-col">
          <h4><?= e(lang('产品与方案', '产品与方案')) ?></h4>
          <?php foreach ($nav as $c): if (($c['slug'] ?? '') === 'jobs') continue; ?>
            <a href="<?= e(xb_cat_url((string) $c['slug'])) ?>"><?= e(ml($c, 'name')) ?></a>
          <?php endforeach; ?>
          <?php if ($shopEnabled): ?><a href="/shop"><?= e(lang('商城', '商城')) ?></a><?php endif; ?>
        </div>
        <div class="xb-foot-col">
          <h4><?= e(lang('了解我们', '了解我们')) ?></h4>
          <?php foreach ($pages as $p): ?>
            <a href="<?= e(xb_page_url((string) $p['slug'])) ?>"><?= e(ml($p, 'title')) ?></a>
          <?php endforeach; ?>
          <a href="<?= e(lang_path('/search')) ?>"><?= e(lang('站内搜索', '站内搜索')) ?></a>
        </div>
        <div class="xb-foot-col xb-foot-contact">
          <h4><?= e(lang('联系', '联系')) ?></h4>
          <?php if ($email !== ''): ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
          <?php if ($address !== ''): ?><p><?= e($address) ?></p><?php endif; ?>
          <a class="xb-foot-more" href="<?= e($contactUrl) ?>"><?= e(lang('查看联系方式', '查看联系方式')) ?> →</a>
        </div>
        <div class="xb-foot-col xb-foot-qr">
          <h4><?= e(lang('扫码联系', '扫码联系')) ?></h4>
          <div class="xb-qr-row">
            <div><img src="/theme/ewm1.jpg" alt="<?= e(lang('加盟微信', '加盟微信')) ?>" loading="lazy"><span><?= e(lang('加盟微信', '加盟微信')) ?></span></div>
            <div><img src="/theme/ewm2.jpg" alt="<?= e(lang('官方微信', '官方微信')) ?>" loading="lazy"><span><?= e(lang('官方微信', '官方微信')) ?></span></div>
            <div><img src="/theme/ewm3.jpg" alt="<?= e(lang('官方微博', '官方微博')) ?>" loading="lazy"><span><?= e(lang('官方微博', '官方微博')) ?></span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="xb-foot-bottom">
      <span><?= e($copyright) ?></span>
      <span><?php if ($icp): ?><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow"><?= e($icp) ?></a><?php endif; ?><?= $icp ? ' · ' : '' ?><?= e(lang('自然质感 · 长期主义', '自然质感 · 长期主义')) ?></span>
    </div>
  </div>
</footer>

<?= plugin_hook('front_footer') ?>
<script src="/theme/site.js?v=1.0.0"></script>
</body>
</html>
