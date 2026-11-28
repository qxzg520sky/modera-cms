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
<?php if (!empty(multilang_config()['langs'])): ?>
<style>
#cmsMobileLang{display:none;position:fixed;left:50%;bottom:calc(14px + env(safe-area-inset-bottom));transform:translateX(-50%);z-index:9996}
#cmsMobileLang .cms-ml-pill{display:flex;align-items:center;gap:2px;padding:3px;border-radius:99px;background:rgba(255,255,255,.96);border:1px solid rgba(15,40,80,.16);box-shadow:0 8px 26px rgba(10,30,60,.18);backdrop-filter:blur(8px)}
#cmsMobileLang a{display:block;padding:7px 12px;border-radius:99px;font-size:12px;line-height:1;color:#53627a;text-decoration:none!important;white-space:nowrap}
#cmsMobileLang a.active{background:#1558b0;color:#fff;font-weight:700}
@media(min-width:981px){#cmsMobileLang{display:none!important}}
</style>
<div id="cmsMobileLang"><div class="cms-ml-pill"><?= lang_switcher('cms-ml-links') ?></div></div>
<script>
(function(){
  var box=document.getElementById('cmsMobileLang');
  if(!box) return;
  function visible(el){var s=getComputedStyle(el);var r=el.getBoundingClientRect();return s.display!=='none'&&s.visibility!=='hidden'&&r.width>0&&r.height>0;}
  function sync(){
    if(window.innerWidth>980){box.style.display='none';return;}
    var others=document.querySelectorAll('a[hreflang]');
    for(var i=0;i<others.length;i++){
      if(box.contains(others[i])) continue;
      if(visible(others[i])){box.style.display='none';return;}
    }
    box.style.display='flex';
  }
  sync();
  window.addEventListener('resize',sync);
  setTimeout(sync,800);
})();
</script>
<?php endif; ?>
</body>
</html>
<?php
if (!empty($GLOBALS['__themeOb']) && ob_get_level() >= $GLOBALS['__themeOb']) {
    ob_end_flush();
    $GLOBALS['__themeOb'] = null;
}
?>
<?php if (current_lang() === 'en'): ?>
<script>
(function(){
  var M={"商城":"Shop","在线客服":"Live chat","登录":"Sign in","注册":"Sign up","阅读全文":"Read more","查看更多":"View more","查看全部":"View all","立即咨询":"Get in touch","首页":"Home","产品中心":"Products","解决方案":"Solutions","新闻中心":"News","服务支持":"Support","关于我们":"About Us","联系我们":"Contact Us","中文":"Chinese"};
  function fix(){
    document.querySelectorAll('a,span,em,button,div,p,li').forEach(function(el){
      if(el.children.length) return;
      var t=(el.textContent||'').trim();
      if(t && M[t]) el.textContent=M[t];
    });
  }
  setTimeout(fix,200);setTimeout(fix,800);
})();
</script>
<?php endif; ?>

