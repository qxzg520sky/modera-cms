<?php
$copyright = setting('site_copyright', '© ' . date('Y') . ' ' . $siteName . ' 版权所有');
$icp = setting('site_icp', '');
$productCat = $newsCat = null;
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'product') { $productCat = $c; }
    if (($c['slug'] ?? '') === 'news') { $newsCat = $c; }
}
$slogan = setting('site_slogan', '以匠心制造 · 为工业赋能');
?>
</main>

<section class="cta">
  <div class="wrap cta-in">
    <div>
      <h2>让下一次现场，更清晰、更稳定。</h2>
      <p>告诉我们你的工艺与产能目标，我们会在 1 个工作日内给出设备选型、产线配置与交付节奏建议。</p>
    </div>
    <a class="btn btn-dark" href="<?= e($contactUrl) ?>">获取方案 <span class="arrow">↗</span></a>
  </div>
</section>

<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="f-brand">
      <a class="brand" href="<?= e(lang_path('/')) ?>"><span class="brand-mark">Z</span><span class="brand-text"><b><?= e($siteName) ?></b><span>MODERA · INDUSTRIAL</span></span></a>
      <p><?= e($slogan) ?>。从单台设备到整线交付，专注稳定、精密与可扩展的工业自动化解决方案。</p>
      <div class="f-social"><a href="<?= e($contactUrl) ?>">微信</a><a href="<?= e($contactUrl) ?>">视频号</a><?php if ($email): ?><a href="mailto:<?= e($email) ?>">邮箱</a><?php endif; ?></div>
    </div>
    <div class="f-col">
      <h4>产品中心</h4>
      <?php if ($productCat): ?>
        <a href="<?= e(lang_path('/c/' . $productCat['slug'])) ?>"><?= e($productCat['name']) ?></a>
        <?php foreach (($productCat['children'] ?? []) as $ch): ?>
          <a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="f-col">
      <h4>快速链接</h4>
      <?php foreach ($navItems as $it): ?>
        <?php if (in_array($it['key'], ['solution', 'news', 'service', 'about', 'contact'], true)): ?>
          <a href="<?= e($it['url']) ?>"><?= e($it['name']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
      <a href="<?= e(lang_path('/search')) ?>">站内搜索</a>
    </div>
    <div class="f-col f-contact">
      <h4>服务热线</h4>
      <?php if ($phone): ?><b><?= e($phone) ?></b><?php endif; ?>
      <?php if ($email): ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
      <?php if ($address): ?><span><?= e($address) ?></span><?php endif; ?>
      <span>周一至周日 08:30–18:00</span>
    </div>
  </div>
  <div class="wrap footer-bottom">
    <span><?= e($copyright) ?></span>
    <span><?php if ($icp): ?><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow"><?= e($icp) ?></a><?php endif; ?></span>
  </div>
</footer>

<?= plugin_hook('front_footer') ?>
<script src="/theme/theme.js?v=1.1.0"></script>
</body>
</html>
