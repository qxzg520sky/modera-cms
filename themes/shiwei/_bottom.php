<?php
$siteName  = setting('site_name', '企业网站');
$slogan    = setting('site_slogan', '');
$phone     = setting('site_phone', '');
$email     = setting('site_email', '');
$address   = setting('site_address', '');
$icp       = setting('site_icp', '');
$pages     = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$nav       = $nav ?? [];
$aboutPage = $contactPage = null;
foreach ($pages as $pp) {
    if ($pp['slug'] === 'about')   { $aboutPage = $pp; }
    if ($pp['slug'] === 'contact') { $contactPage = $pp; }
}
$catBySlug = [];
foreach ($nav as $c) { $catBySlug[$c['slug']] = $c; }
$footProduct = $catBySlug['product'] ?? null;
$footSolution = $catBySlug['solution'] ?? null;
$footService = $catBySlug['service'] ?? null;
$footNews = $catBySlug['news'] ?? null;
?>
</main>

<footer class="sw-footer">
  <div class="sw-foot-main">
    <div class="sw-foot-brand">
      <b><?= e($siteName) ?></b>
      <span><?= e($slogan ?: 'PRECISION INDUSTRY SOLUTIONS') ?></span>
      <p><?= e(setting('site_description', '专注智能装备制造、工业自动化整线交付与精密零部件加工，把每一台设备做扎实，让客户的产线稳定跑起来。')) ?></p>
    </div>
    <div class="sw-foot-contact">
      <span><?= e(lang('全国服务热线')) ?></span>
      <a class="tel" href="tel:<?= e(preg_replace('/[^0-9+\-]/', '', (string) $phone)) ?>"><?= e($phone) ?></a>
      <a class="mail" href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
    </div>
  </div>

  <div class="sw-foot-nav">
    <div class="sw-foot-col">
      <h4><?= e(lang('联系我们')) ?></h4>
      <p><?= e($address) ?></p>
      <p><?= e(lang('电话')) ?>：<?= e($phone) ?></p>
      <p><?= e(lang('邮箱')) ?>：<?= e($email) ?></p>
    </div>
    <div class="sw-foot-col">
      <h4><?= e(lang('快速导航')) ?></h4>
      <ul>
        <li><a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a></li>
        <?php if ($aboutPage): ?><li><a href="<?= e(lang_path('/p/' . $aboutPage['slug'])) ?>"><?= e(lang('关于我们')) ?></a></li><?php endif; ?>
        <?php if ($contactPage): ?><li><a href="<?= e(lang_path('/p/' . $contactPage['slug'])) ?>"><?= e(lang('联系我们')) ?></a></li><?php endif; ?>
      </ul>
    </div>
    <div class="sw-foot-col">
      <h4><?= e(lang('产品与服务')) ?></h4>
      <ul>
        <?php if ($footProduct): ?><li><a href="<?= e(lang_path('/c/' . $footProduct['slug'])) ?>"><?= e($footProduct['name']) ?></a></li><?php endif; ?>
        <?php if ($footSolution): ?><li><a href="<?= e(lang_path('/c/' . $footSolution['slug'])) ?>"><?= e($footSolution['name']) ?></a></li><?php endif; ?>
        <?php if ($footService): ?><li><a href="<?= e(lang_path('/c/' . $footService['slug'])) ?>"><?= e($footService['name']) ?></a></li><?php endif; ?>
        <?php if ($footNews): ?><li><a href="<?= e(lang_path('/c/' . $footNews['slug'])) ?>"><?= e($footNews['name']) ?></a></li><?php endif; ?>
      </ul>
    </div>
    <div class="sw-foot-col">
      <h4><?= e(lang('服务承诺')) ?></h4>
      <p class="sw-foot-note">方案设计 · 机械加工 · 电气成套 · 现场调试<br>7×12 小时响应，重点区域 48 小时到场。</p>
    </div>
  </div>

  <div class="sw-copy">
    <div class="sw-copy-in">
      <span>© <?= date('Y') ?> <?= e($siteName) ?> <?= e(lang('版权所有')) ?></span>
      <span>
        <?php if ($icp): ?><a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener nofollow"><?= e($icp) ?></a><?php endif; ?>
      </span>
    </div>
  </div>
  <?= plugin_hook('front_footer') ?>
</footer>

<script>
(function () {
  var burger = document.getElementById('swBurger');
  var mobile = document.getElementById('swMobile');
  if (burger && mobile) {
    burger.addEventListener('click', function () {
      var open = mobile.classList.toggle('open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', function (ev) {
      if (!mobile.classList.contains('open')) return;
      if (mobile.contains(ev.target) || burger.contains(ev.target)) return;
      mobile.classList.remove('open');
      burger.setAttribute('aria-expanded', 'false');
    });
  }

  // 首屏轮播
  var slides = Array.prototype.slice.call(document.querySelectorAll('.sw-slide'));
  var dots = Array.prototype.slice.call(document.querySelectorAll('.sw-hero-dots button'));
  if (slides.length > 1) {
    var idx = 0, timer = null;
    var show = function (n) {
      idx = (n + slides.length) % slides.length;
      slides.forEach(function (s, i) { s.classList.toggle('on', i === idx); });
      dots.forEach(function (d, i) { d.classList.toggle('on', i === idx); });
    };
    var play = function () {
      clearInterval(timer);
      timer = setInterval(function () { show(idx + 1); }, 6500);
    };
    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () { show(i); play(); });
    });
    var hero = document.querySelector('.sw-hero');
    if (hero) {
      hero.addEventListener('mouseenter', function () { clearInterval(timer); });
      hero.addEventListener('mouseleave', play);
    }
    show(0);
    play();
  }

  // 产品横滑
  var track = document.querySelector('.sw-pro-track');
  if (track) {
    var prev = document.querySelector('.sw-pro-prev');
    var next = document.querySelector('.sw-pro-next');
    var step = function () {
      var card = track.querySelector('.sw-pro-card');
      return card ? card.getBoundingClientRect().width + 24 : track.clientWidth / 2;
    };
    if (prev) { prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); }); }
    if (next) { next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); }); }
    var dotsWrap = document.querySelector('.sw-pro-dots');
    if (dotsWrap) {
      var cards = Array.prototype.slice.call(track.querySelectorAll('.sw-pro-card'));
      var slots = Math.max(1, Math.ceil(cards.length / 3));
      for (var i = 0; i < slots; i++) {
        var s = document.createElement('span');
        if (i === 0) { s.className = 'on'; }
        dotsWrap.appendChild(s);
      }
      var tick = function () {
        var per = step();
        var page = per ? Math.round(track.scrollLeft / per) : 0;
        var all = Array.prototype.slice.call(dotsWrap.children);
        var active = Math.min(all.length - 1, Math.max(0, Math.floor(page / 3)));
        all.forEach(function (el, i) { el.classList.toggle('on', i === active); });
      };
      track.addEventListener('scroll', tick, { passive: true });
    }
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

