<?php
$nav   = $nav ?? [];
$pages = $pages ?? [];
// 找出产品中心和新闻中心，用于页脚分栏
$ftProduct = null;
$ftNews    = null;
foreach ($nav as $c) {
    if ($c['slug'] === 'product') {
        $ftProduct = $c;
    }
    if ($c['slug'] === 'news') {
        $ftNews = $c;
    }
}
?>
</main>

<footer class="gt-ftr">
  <div class="gt-wrap gt-ftr-in">
    <div class="gt-ftr-col">
      <h4><?= e(lang('联系我们')) ?></h4>
      <ul>
        <li><a href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('联系方式')) ?></a></li>
        <li><a href="<?= e(lang_path('/search')) ?>"><?= e(lang('站内搜索')) ?></a></li>
        <li><a href="<?= e(lang_path('/c/' . ($ftProduct['slug'] ?? 'product'))) ?>"><?= e(lang('产品询价')) ?></a></li>
      </ul>
    </div>
    <div class="gt-ftr-col">
      <h4><?= e($ftNews['name'] ?? lang('新闻中心')) ?></h4>
      <ul>
        <?php if ($ftNews && !empty($ftNews['children'])): ?>
          <?php foreach ($ftNews['children'] as $ch): ?>
            <li><a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a></li>
          <?php endforeach; ?>
        <?php else: ?>
          <li><a href="<?= e(lang_path('/c/news')) ?>"><?= e(lang('公司新闻')) ?></a></li>
        <?php endif; ?>
        <li><a href="<?= e(lang_path('/c/news')) ?>"><?= e(lang('全部资讯')) ?></a></li>
      </ul>
    </div>
    <div class="gt-ftr-col">
      <h4><?= e(lang('关于我们')) ?></h4>
      <ul>
        <?php foreach ($pages as $p): ?>
          <li><a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></li>
        <?php endforeach; ?>
        <?php foreach (array_slice($nav, 0, 2) as $c): ?>
          <li><a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="gt-ftr-col">
      <h4><?= e($ftProduct['name'] ?? lang('产品中心')) ?></h4>
      <ul>
        <?php if ($ftProduct && !empty($ftProduct['children'])): ?>
          <?php foreach ($ftProduct['children'] as $ch): ?>
            <li><a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a></li>
          <?php endforeach; ?>
        <?php else: ?>
          <?php foreach (array_slice($nav, 0, 4) as $c): ?>
            <li><a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>
    <div class="gt-ftr-qr">
      <?php if (setting('site_logo', '')): ?>
        <img src="<?= e(setting('site_logo', '')) ?>" alt="<?= e(setting('site_name', '')) ?>">
      <?php else: ?>
        <div class="gt-qr-ph">LOGO</div>
      <?php endif; ?>
      <p><?= e(lang('咨询热线')) ?><br><b><?= e(setting('site_phone', '')) ?></b></p>
    </div>
  </div>
</footer>
<div class="gt-copy">
  <div class="gt-wrap gt-copy-in">
    <span><?= e(setting('site_copyright', '© ' . date('Y') . ' ' . setting('site_name', ''))) ?></span>
    <?php if (setting('site_icp', '')): ?>
      <span><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow"><?= e(setting('site_icp', '')) ?></a></span>
    <?php endif; ?>
    <span class="gt-copy-tel"><?= e(setting('site_phone', '')) ?></span>
  </div>
</div>

<?= plugin_hook('front_footer') ?>
<script>
(function () {
  // 轮播
  var slider = document.getElementById('gtSlider');
  if (slider) {
    var slides = slider.querySelectorAll('.gt-slide');
    var dots = slider.querySelectorAll('.gt-slide-dots button');
    var cur = 0, timer = null;
    function go(n) {
      if (!slides.length) return;
      cur = (n + slides.length) % slides.length;
      for (var i = 0; i < slides.length; i++) {
        slides[i].className = 'gt-slide' + (i === cur ? ' on' : '');
      }
      for (var j = 0; j < dots.length; j++) {
        dots[j].className = j === cur ? 'on' : '';
      }
    }
    for (var d = 0; d < dots.length; d++) {
      dots[d].addEventListener('click', function () {
        go(parseInt(this.getAttribute('data-i'), 10));
        restart();
      });
    }
    function restart() {
      if (timer) clearInterval(timer);
      if (slides.length > 1) timer = setInterval(function () { go(cur + 1); }, 5000);
    }
    restart();
  }

  // 栏目 tab
  var tabs = document.querySelectorAll('.gt-tabs li a');
  for (var t = 0; t < tabs.length; t++) {
    tabs[t].addEventListener('click', function (e) {
      e.preventDefault();
      var name = this.getAttribute('data-pane');
      var lis = document.querySelectorAll('.gt-tabs li');
      for (var i = 0; i < lis.length; i++) lis[i].classList.remove('on');
      this.parentNode.classList.add('on');
      var panes = document.querySelectorAll('.gt-pane');
      for (var k = 0; k < panes.length; k++) panes[k].classList.remove('on');
      var pane = document.getElementById(name);
      if (pane) pane.classList.add('on');
    });
  }

  // 手机端栏目展开
  var toggle = document.getElementById('gtNavToggle');
  var menu = document.getElementById('gtMenu');
  if (toggle && menu) {
    toggle.addEventListener('click', function () {
      var open = menu.classList.contains('open');
      menu.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
    });
    var subs = menu.querySelectorAll('.has-sub > a');
    for (var s = 0; s < subs.length; s++) {
      subs[s].addEventListener('click', function (ev) {
        if (window.innerWidth > 980) return;
        var sub = this.parentNode.querySelector('.gt-sub');
        if (!sub) return;
        ev.preventDefault();
        sub.classList.toggle('open');
      });
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

