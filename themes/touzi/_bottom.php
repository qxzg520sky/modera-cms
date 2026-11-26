<?php
$siteName  = setting('site_name', '企业网站');
$phone     = setting('site_phone', '');
$email     = setting('site_email', '');
$address   = setting('site_address', '');
$icp       = setting('site_icp', '');
$pages     = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$nav       = $nav ?? [];
$aboutPage = $contactPage = $culturePage = null;
foreach ($pages as $pp) {
    if ($pp['slug'] === 'about')   { $aboutPage = $pp; }
    if ($pp['slug'] === 'contact') { $contactPage = $pp; }
    if ($pp['slug'] === 'culture') { $culturePage = $pp; }
}
$catBySlug = [];
foreach ($nav as $c) { $catBySlug[$c['slug']] = $c; }
?>
</main>

<footer class="tz-footer">
  <div class="tz-foot-links">
    <div class="tz-container">
      <ul>
        <li><a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a></li>
        <?php if (!empty($catBySlug['product'])): ?><li><a href="<?= e(lang_path('/c/product')) ?>"><?= e(lang('业务介绍')) ?></a></li><?php endif; ?>
        <?php if (!empty($catBySlug['news'])): ?><li><a href="<?= e(lang_path('/c/news')) ?>"><?= e(lang('媒体中心')) ?></a></li><?php endif; ?>
        <?php if ($culturePage): ?><li><a href="<?= e(lang_path('/p/culture')) ?>"><?= e(lang('集团学院')) ?></a></li><?php endif; ?>
        <?php if (!empty($catBySlug['jobs'])): ?><li><a href="<?= e(lang_path('/c/jobs')) ?>"><?= e(lang('招贤纳士')) ?></a></li><?php endif; ?>
        <?php if ($aboutPage): ?><li><a href="<?= e(lang_path('/p/about')) ?>"><?= e(lang('关于我们')) ?></a></li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="tz-foot-copy">
    <div class="tz-container tz-copy-in">
      <div class="tz-copy-left">
        <span>COPYRIGHT © <?= date('Y') ?></span>
        <span><?= e($siteName) ?></span>
        <?php if ($icp): ?><a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener nofollow"><?= e($icp) ?></a><?php endif; ?>
      </div>
      <div class="tz-copy-right">
        <?php if ($phone): ?><a href="tel:<?= e(preg_replace('/[^0-9+\-]/', '', (string) $phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
        <?php if ($email): ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
  <?= plugin_hook('front_footer') ?>
</footer>

<script>
(function () {
  /* 移动端菜单 */
  var burger = document.getElementById('tzBurger');
  var mobile = document.getElementById('tzMobile');
  if (burger && mobile) {
    burger.addEventListener('click', function () {
      var open = mobile.classList.toggle('open');
      burger.classList.toggle('open', open);
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.classList.toggle('tz-nav-open', open);
    });
    document.addEventListener('click', function (ev) {
      if (!mobile.classList.contains('open')) return;
      if (mobile.contains(ev.target) || burger.contains(ev.target)) return;
      mobile.classList.remove('open');
      burger.classList.remove('open');
      burger.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
      document.body.classList.remove('tz-nav-open');
    });
  }

  /* 首屏轮播 */
  var hero = document.querySelector('.tz-hero');
  var heroSlides = Array.prototype.slice.call(document.querySelectorAll('.tz-hero-slide'));
  var heroDots = Array.prototype.slice.call(document.querySelectorAll('.tz-hero-dots button'));
  if (hero && heroSlides.length) {
    var hi = 0, htimer = null;
    var hshow = function (n) {
      hi = (n + heroSlides.length) % heroSlides.length;
      heroSlides.forEach(function (s, i) { s.classList.toggle('on', i === hi); });
      heroDots.forEach(function (d, i) { d.classList.toggle('on', i === hi); });
    };
    var hplay = function () {
      clearInterval(htimer);
      if (heroSlides.length < 2) return;
      htimer = setInterval(function () { hshow(hi + 1); }, 6000);
    };
    heroDots.forEach(function (d, i) {
      d.addEventListener('click', function () { hshow(i); hplay(); });
    });
    hero.addEventListener('mouseenter', function () { clearInterval(htimer); });
    hero.addEventListener('mouseleave', hplay);
    hshow(0); hplay();
  }

  /* 业务两页切换 */
  var bizPages = Array.prototype.slice.call(document.querySelectorAll('.tz-biz-page'));
  var bizDots = Array.prototype.slice.call(document.querySelectorAll('.tz-biz-dots button'));
  if (bizPages.length > 1) {
    var bshow = function (n) {
      bizPages.forEach(function (p, i) { p.classList.toggle('on', i === n); });
      bizDots.forEach(function (d, i) { d.classList.toggle('on', i === n); });
    };
    bizDots.forEach(function (d, i) { d.addEventListener('click', function () { bshow(i); }); });
  }

  /* 集团风采 */
  var presItems = Array.prototype.slice.call(document.querySelectorAll('.tz-pres-circle'));
  var presDesc = Array.prototype.slice.call(document.querySelectorAll('.tz-pres-desc'));
  if (presItems.length) {
    var pshow = function (n) {
      presItems.forEach(function (el, i) { el.classList.toggle('on', i === n); });
      presDesc.forEach(function (el, i) { el.classList.toggle('on', i === n); });
    };
    presItems.forEach(function (el, i) {
      el.addEventListener('click', function () { pshow(i); });
      el.addEventListener('mouseenter', function () { pshow(i); });
    });
  }

  /* 集团学院轮播 */
  var instItems = Array.prototype.slice.call(document.querySelectorAll('.tz-inst-item'));
  var instIdx = 0;
  var instShow = function (n) {
    if (!instItems.length) return;
    instIdx = (n + instItems.length) % instItems.length;
    instItems.forEach(function (el, i) { el.classList.toggle('on', i === instIdx); });
  };
  var instPrev = document.querySelector('.tz-inst-prev');
  var instNext = document.querySelector('.tz-inst-next');
  if (instPrev) { instPrev.addEventListener('click', function () { instShow(instIdx - 1); }); }
  if (instNext) { instNext.addEventListener('click', function () { instShow(instIdx + 1); }); }

  /* 内页 MENU 下拉 */
  var sideToggle = document.querySelector('.tz-side-toggle');
  var sideMenu = document.querySelector('.tz-side-menu');
  if (sideToggle && sideMenu) {
    sideToggle.addEventListener('click', function (ev) {
      ev.preventDefault();
      var open = sideMenu.classList.toggle('open');
      sideToggle.classList.toggle('open', open);
    });
    document.addEventListener('click', function (ev) {
      if (!sideMenu.classList.contains('open')) return;
      if (sideMenu.contains(ev.target) || sideToggle.contains(ev.target)) return;
      sideMenu.classList.remove('open');
      sideToggle.classList.remove('open');
    });
  }

  /* 回到顶部 */
  Array.prototype.slice.call(document.querySelectorAll('.tz-gotop')).forEach(function (el) {
    el.addEventListener('click', function (ev) {
      ev.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  /* 移动端导航打开时锁滚动 */
  var bodyLock = function () {
    if (document.body.classList.contains('tz-nav-open')) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  };
  if (burger) { burger.addEventListener('click', bodyLock); }
})();
</script>
</body>
</html>
