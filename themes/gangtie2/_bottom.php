<?php
$siteName = setting('site_name', '企业网站');
$slogan   = setting('site_slogan', '');
$phone    = setting('site_phone', '');
$email    = setting('site_email', '');
$address  = setting('site_address', '');
$logo     = setting('site_logo', '');
$pages    = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$nav      = $nav ?? [];
?>

</main>

<!-- 橙色呼叫条 -->
<section class="g2-callbar">
  <div class="g2-wrap g2-callbar-in">
    <div class="g2-callbar-tel">
      <span class="g2-callbar-ico">
        <svg viewBox="0 0 24 24" width="26" height="26"><path d="M6.6 10.8c1.4 2.8 3.6 5 6.4 6.4l2.2-2.2c.3-.3.7-.4 1-.2 2.3.7 4.6 1 6.9 1 .6 0 1 .4 1 1V21c0 .6-.4 1-1 1C10.4 22 2 13.6 2 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 2.3.3 4.6 1 6.9l1 .3.2.7.1 1-.2z" fill="#fff"/></svg>
      </span>
      <div>
        <i><?= e(lang('服务热线')) ?></i>
        <b><?= e($phone ?: '400-000-0000') ?></b>
      </div>
    </div>
    <div class="g2-callbar-act">
      <a class="g2-btn g2-btn-dark" href="tel:<?= e(preg_replace('/[^0-9\-]/', '', (string) $phone)) ?>"><?= e(lang('立即拨打')) ?></a>
      <a class="g2-btn g2-btn-white" href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('在线咨询')) ?></a>
    </div>
  </div>
</section>

<footer class="g2-ftr">
  <div class="g2-wrap g2-ftr-in">
    <div class="g2-ftr-brand">
      <a class="g2-ftr-logo" href="<?= e(lang_path('/')) ?>">
        <?php if ($logo): ?><img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>"><?php else: ?><b><?= e($siteName) ?></b><?php endif; ?>
      </a>
      <p><?= e($slogan ?: lang('以工艺、装备与服务的组合，为客户交付稳定可用的自动化产线。')) ?></p>
      <div class="g2-social">
        <a href="<?= e(lang_path('/p/contact')) ?>" aria-label="contact"><svg viewBox="0 0 24 24" width="14" height="14"><circle cx="12" cy="12" r="9" fill="none" stroke="#9aa3ad" stroke-width="1.6"/><path d="M12 7v5l3 2" fill="none" stroke="#9aa3ad" stroke-width="1.6"/></svg></a>
        <a href="mailto:<?= e($email) ?>" aria-label="mail"><svg viewBox="0 0 24 24" width="14" height="14"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="#9aa3ad" stroke-width="1.6"/><path d="M4 7l8 6 8-6" fill="none" stroke="#9aa3ad" stroke-width="1.6"/></svg></a>
        <a href="tel:<?= e(preg_replace('/[^0-9\-]/', '', (string) $phone)) ?>" aria-label="phone"><svg viewBox="0 0 24 24" width="14" height="14"><path d="M6.6 10.8c1.4 2.8 3.6 5 6.4 6.4l2.2-2.2c.3-.3.7-.4 1-.2 2.3.7 4.6 1 6.9 1 .6 0 1 .4 1 1V21c0 .6-.4 1-1 1C10.4 22 2 13.6 2 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 2.3.3 4.6 1 6.9l1 .3.2.7.1 1-.2z" fill="none" stroke="#9aa3ad" stroke-width="1.6"/></svg></a>
      </div>
    </div>

    <div class="g2-ftr-col">
      <h4><?= e(lang('公司')) ?></h4>
      <ul>
        <?php foreach (array_slice($pages, 0, 3) as $p): ?>
          <li><a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= e(lang_path('/c/product')) ?>"><?= e(lang('产品中心')) ?></a></li>
        <li><a href="<?= e(lang_path('/c/solution')) ?>"><?= e(lang('解决方案')) ?></a></li>
        <li><a href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('联系我们')) ?></a></li>
      </ul>
    </div>

    <div class="g2-ftr-col">
      <h4><?= e(lang('产品与方案')) ?></h4>
      <ul>
        <?php foreach ($nav as $c): ?>
          <?php if (in_array($c['slug'], ['news', 'jobs'], true)) { continue; } ?>
          <li><a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a></li>
          <?php foreach (array_slice($c['children'], 0, 2) as $ch): ?>
            <li><a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a></li>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="g2-ftr-col g2-ftr-contact">
      <h4><?= e(lang('联系方式')) ?></h4>
      <p><em><?= e(lang('电话')) ?> :</em> <a href="tel:<?= e(preg_replace('/[^0-9\-]/', '', (string) $phone)) ?>"><?= e($phone) ?></a></p>
      <p><em><?= e(lang('邮箱')) ?> :</em> <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
      <p><em><?= e(lang('地址')) ?> :</em> <?= e($address) ?></p>
    </div>
  </div>

  <div class="g2-copy">
    <div class="g2-wrap g2-copy-in">
      <span><?= e(setting('site_copyright', '© ' . date('Y') . ' ' . $siteName . ' 版权所有')) ?></span>
      <?php if (setting('site_icp', '')): ?>
        <span><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow"><?= e(setting('site_icp', '')) ?></a></span>
      <?php endif; ?>
    </div>
  </div>
</footer>

<?= plugin_hook('front_footer') ?>

<script>
(function () {
  /* ---------- 轮播 ---------- */
  var hero = document.getElementById('g2Hero');
  if (hero) {
    var slides = hero.querySelectorAll('.g2-slide');
    var dots = hero.querySelectorAll('.g2-hero-dots button');
    var cur = 0, timer = null;
    function go(i) {
      if (!slides.length) return;
      for (var k = 0; k < slides.length; k++) {
        slides[k].classList.toggle('on', k === i);
        if (dots[k]) dots[k].classList.toggle('on', k === i);
      }
      cur = i;
    }
    for (var d = 0; d < dots.length; d++) {
      dots[d].addEventListener('click', function () {
        go(parseInt(this.getAttribute('data-i'), 10) || 0);
        restart();
      });
    }
    function restart() {
      if (timer) clearInterval(timer);
      timer = setInterval(function () { go((cur + 1) % slides.length); }, 5200);
    }
    if (slides.length > 1) restart();
  }

  /* ---------- 手机菜单 ---------- */
  var burger = document.getElementById('g2Burger');
  var menu = document.getElementById('g2Menu');
  if (burger && menu) {
    burger.addEventListener('click', function () {
      var open = menu.classList.toggle('open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    menu.addEventListener('click', function (e) {
      var a = e.target.closest('a');
      if (!a) return;
      var li = a.parentNode;
      if (li && li.classList.contains('has-sub') && window.innerWidth <= 991 && !li.classList.contains('open')) {
        e.preventDefault();
        li.classList.add('open');
      }
    });
  }

  /* ---------- 询价表单 → 在线客服 ---------- */
  var qf = document.getElementById('g2Quote');
  if (qf) {
    qf.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = (qf.querySelector('[name=name]') || {}).value || '';
      var tel = (qf.querySelector('[name=phone]') || {}).value || '';
      var need = (qf.querySelector('[name=need]') || {}).value || '';
      var msg = '【询价】' + (name ? '称呼：' + name + '；' : '') + (tel ? '电话：' + tel + '；' : '') + (need ? '需求：' + need : '');
      var launcher = document.querySelector('.kf-launcher');
      if (launcher) {
        launcher.click();
        setTimeout(function () {
          var ta = document.querySelector('.kf-send textarea');
          var nameInput = document.querySelector('.kf-contact input');
          if (nameInput && name) nameInput.value = name;
          if (ta) {
            ta.value = msg;
            ta.focus();
          }
        }, 350);
      } else {
        window.location.href = '<?= e(lang_path('/p/contact')) ?>';
      }
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

