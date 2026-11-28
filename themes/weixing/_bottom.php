<?php
$siteName = setting('site_name', '企业网站');
$logo = setting('site_logo', '');
$phone = setting('site_phone', '');
$email = setting('site_email', '');
$address = setting('site_address', '');
$copyright = setting('site_copyright', '© ' . date('Y') . ' ' . $siteName . ' 版权所有');
$icp = setting('site_icp', '');
$nav = $nav ?? [];
$pages = $pages ?? [];
$productCat = null;
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'product') { $productCat = $c; }
}
$newsCat = null;
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'news') { $newsCat = $c; }
}
$serviceCat = null;
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'service') { $serviceCat = $c; }
}
?>
</main>

<footer class="wx-footer">
  <div class="wx-inner wx-footer-top">
    <div class="wx-fbrand">
      <a href="<?= e(lang_path('/')) ?>">
        <?php if ($logo): ?><img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>"><?php else: ?><img src="/theme/logo.png" alt="<?= e($siteName) ?>"><?php endif; ?>
      </a>
      <p><?= e(setting('site_slogan', '材料，让制造更稳定')) ?></p>
      <span class="wx-fphone"><?= e($phone) ?></span>
    </div>
    <div class="wx-fcol">
      <h4><?= e(lang('产品中心')) ?></h4>
      <?php if ($productCat): ?>
        <a href="<?= e(lang_path('/c/' . $productCat['slug'])) ?>"><?= e($productCat['name']) ?></a>
        <?php foreach (($productCat['children'] ?? []) as $ch): ?>
          <a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="wx-fcol">
      <h4><?= e(lang('新闻动态')) ?></h4>
      <?php if ($newsCat): ?>
        <a href="<?= e(lang_path('/c/' . $newsCat['slug'])) ?>"><?= e($newsCat['name']) ?></a>
      <?php endif; ?>
      <?php foreach ($pages as $p): ?>
        <?php if (in_array($p['slug'], ['about', 'culture'], true)): ?>
          <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php if ($serviceCat): ?><a href="<?= e(lang_path('/c/' . $serviceCat['slug'])) ?>"><?= e($serviceCat['name']) ?></a><?php endif; ?>
    </div>
    <div class="wx-fcol">
      <h4><?= e(lang('关于我们')) ?></h4>
      <?php foreach ($pages as $p): ?>
        <?php if (!in_array($p['slug'], ['contact'], true)): ?>
          <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <div class="wx-fcol wx-fcontact">
      <h4><?= e(lang('联系我们')) ?></h4>
      <?php if ($phone): ?><a href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
      <?php if ($email): ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
      <?php if ($address): ?><p><?= e($address) ?></p><?php endif; ?>
    </div>
  </div>
  <div class="wx-footer-bottom">
    <div class="wx-inner">
      <span><?= e($copyright) ?></span>
      <?php if ($icp): ?><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow"><?= e($icp) ?></a><?php endif; ?>
    </div>
  </div>
</footer>

<?= plugin_hook('front_footer') ?>

<script>
(function () {
  var btn = document.querySelector('.wx-menu-btn');
  var nav = document.querySelector('.wx-nav');
  if (btn && nav) {
    btn.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }
  var form = document.querySelector('.wx-contact-form');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var email = form.getAttribute('data-email') || '';
      var name = (form.querySelector('[name=name]') || {}).value || '';
      var phone = (form.querySelector('[name=phone]') || {}).value || '';
      var message = (form.querySelector('[name=message]') || {}).value || '';
      var body = '姓名：' + name + '\n电话：' + phone + '\n需求：\n' + message;
      window.location.href = 'mailto:' + email + '?subject=' + encodeURIComponent('网站询价 - ' + name) + '&body=' + encodeURIComponent(body);
    });
  }
})();
</script>
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

