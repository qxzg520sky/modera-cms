<?php
$siteName = setting('site_name', '墨白笔记');
$siteSlogan = setting('site_slogan', '写代码，也写方法');
$siteDesc = setting('site_description', '关于建站、代码与长期主义的独立笔记。');
$email = setting('site_email', '');
$icp = setting('site_icp', '');
$copyright = setting('site_copyright', '© ' . date('Y') . ' ' . $siteName . ' 版权所有');
$nav = $nav ?? [];
$pages = $pages ?? [];
$footCats = [];
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'jobs') {
        continue;
    }
    $footCats[] = $c;
    if (count($footCats) >= 5) {
        break;
    }
}
?>
</main>

<footer class="site-foot">
  <div class="wrap foot-top">
    <div class="foot-brand">
      <a href="<?= e(lang_path('/')) ?>" class="foot-logo">
        <span class="brand-mark">M</span>
        <span><?= e($siteName) ?></span>
      </a>
      <p><?= e($siteDesc) ?></p>
      <small>JOURNAL / NOTES · <?= e(date('Y')) ?></small>
    </div>
    <div class="foot-col">
      <h3><?= e(lang('栏目')) ?></h3>
      <?php foreach ($footCats as $c): ?>
        <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="foot-col">
      <h3><?= e(lang('页面')) ?></h3>
      <?php foreach ($pages as $p): ?>
        <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="foot-col foot-contact">
      <h3><?= e(lang('联系')) ?></h3>
      <?php if ($email !== ''): ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
      <a href="<?= e(lang_path('/search')) ?>"><?= e(lang('搜索')) ?></a>
      <p><?= e($siteSlogan) ?></p>
    </div>
  </div>
  <div class="wrap foot-meta">
    <span><?= e($copyright) ?></span>
    <span class="foot-meta-right">
      <?php if ($icp !== ''): ?><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow noopener"><?= e($icp) ?></a><?php endif; ?>
      <span class="foot-theme">INK THEME</span>
    </span>
  </div>
</footer>

<button class="to-top" type="button" data-to-top aria-label="<?= e(lang('回到顶部')) ?>">
  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M5.5 11.5L12 5l6.5 6.5"></path></svg>
</button>

<?= plugin_hook('front_footer') ?>
<script src="/theme/theme.js?v=1.0.0" defer></script>
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

