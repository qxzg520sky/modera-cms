<?php
$copyright = setting('site_copyright', '© ' . date('Y') . ' ' . $siteName . ' 版权所有');
$icp = setting('site_icp', '');
$productCat = $newsCat = null;
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'product') { $productCat = $c; }
    if (($c['slug'] ?? '') === 'news') { $newsCat = $c; }
}
$slogan = setting('site_slogan', '让医疗软件更懂临床');
?>
</main>
<section class="cta-band">
  <div class="wrap cta-in">
    <div class="cta-mark">K</div>
    <div><h2>让数据回到临床，<br>让系统真正好用。</h2><p>预约一次产品演示，聊聊你的科室流程、数据接口与交付计划。</p></div>
    <a class="btn btn-red" href="<?= e($contactUrl) ?>">预约演示 <span class="arrow">↗</span></a>
  </div>
</section>
<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="f-brand">
      <a class="brand" href="<?= e(lang_path('/')) ?>"><span class="brand-mark"><i></i><i></i></span><span class="brand-text"><b><?= e($siteName) ?></b><span>KANGSOFT · 医疗科技</span></span></a>
      <p><?= e($slogan) ?>。专注医院信息化、临床数据与医疗运营数字化，为医疗机构提供可持续演进的产品与服务。</p>
      <div class="f-social"><a href="<?= e($contactUrl) ?>">产品咨询</a><a href="<?= e($contactUrl) ?>">解决方案</a><?php if ($email): ?><a href="mailto:<?= e($email) ?>">邮箱</a><?php endif; ?></div>
    </div>
    <div class="f-col"><h4>产品与方案</h4>
      <?php if ($productCat): ?><a href="<?= e(lang_path('/c/' . $productCat['slug'])) ?>"><?= e($productCat['name']) ?></a><?php foreach (($productCat['children'] ?? []) as $ch): ?><a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a><?php endforeach; ?><?php endif; ?>
    </div>
    <div class="f-col"><h4>快速链接</h4>
      <?php foreach ($navItems as $it): if (in_array($it['key'], ['solution', 'news', 'service', 'about', 'contact'], true)): ?><a href="<?= e($it['url']) ?>"><?= e($it['name']) ?></a><?php endif; endforeach; ?>
      <a href="<?= e(lang_path('/search')) ?>">站内搜索</a>
    </div>
    <div class="f-col f-contact"><h4>联系我们</h4>
      <?php if ($phone): ?><b><?= e($phone) ?></b><?php endif; ?>
      <?php if ($email): ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
      <?php if ($address): ?><span><?= e($address) ?></span><?php endif; ?>
      <span>周一至周五 09:00–18:00</span>
    </div>
  </div>
  <div class="wrap footer-bottom"><span><?= e($copyright) ?></span><span><?php if ($icp): ?><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow"><?= e($icp) ?></a><?php endif; ?></span></div>
</footer>
<?= plugin_hook('front_footer') ?>
<script src="/theme/theme.js?v=2.0.0"></script>
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

