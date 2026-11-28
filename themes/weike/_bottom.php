<?php
$nav   = $nav ?? [];
$pages = $pages ?? [];
$siteName  = setting('site_name', '企业网站');
$phone     = setting('site_phone', '');
$email     = setting('site_email', '');
$address   = setting('site_address', '');
$copyright = setting('site_copyright', '© ' . date('Y') . ' ' . $siteName);
$icp       = setting('site_icp', '');
?>
</div><!-- /.main -->

<div class="foot">
  <div class="wk-foot-nav">
    <?php foreach ($nav as $c): ?>
      <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a>
    <?php endforeach; ?>
    <?php foreach ($pages as $p): ?>
      <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
    <?php endforeach; ?>
    <a href="<?= e(lang_path('/search')) ?>"><?= e(lang('站内搜索')) ?></a>
  </div>
  <div><?= e($copyright) ?></div>
  <?php if ($address): ?><div><?= e(lang('地址')) ?>：<?= e($address) ?></div><?php endif; ?>
  <?php if ($phone || $email): ?>
    <div>
      <?php if ($phone): ?><?= e(lang('电话')) ?>：<a href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
      <?php if ($phone && $email): ?>　<?php endif; ?>
      <?php if ($email): ?><?= e(lang('邮箱')) ?>：<a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
    </div>
  <?php endif; ?>
  <?php if ($icp): ?>
    <div><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow"><?= e($icp) ?></a></div>
  <?php endif; ?>
</div>

<?= plugin_hook('front_footer') ?>

<script>
$(function () {
  var $header = $('.header');
  var isHome = $('body').hasClass('wk-home');
  function syncHeader() {
    if (!isHome || $(window).scrollTop() > 10) {
      $header.addClass('h_fix');
    } else {
      $header.removeClass('h_fix');
    }
  }
  syncHeader();
  $(window).on('scroll', syncHeader);

  $('.nav_m .n_icon').on('click', function (e) {
    e.stopPropagation();
    $('.head .nav').slideToggle(180);
  });
  $('.wk-menu-btn').on('click', function (e) {
    e.stopPropagation();
    var open = $('.head .nav').is(':visible');
    $('.head .nav').slideToggle(180);
    $(this).attr('aria-expanded', open ? 'false' : 'true');
  });
  $(window).on('resize', function () {
    if ($(window).width() > 990) {
      $('.head .nav').removeAttr('style');
    }
  });

  // 首屏轮播：不依赖第三方插件，图片作为背景直接渲染
  (function () {
    var $hero = $('#wkHero');
    if (!$hero.length) return;
    var $slides = $hero.find('.wk-hero-slide');
    var $dots = $hero.find('.wk-hero-dots button');
    if ($slides.length < 2) return;
    var cur = 0, timer = null;
    function go(n) {
      n = (n + $slides.length) % $slides.length;
      $slides.removeClass('on').eq(n).addClass('on');
      $dots.removeClass('on').eq(n).addClass('on');
      cur = n;
    }
    function play() {
      timer = setInterval(function () { go(cur + 1); }, 6500);
    }
    function stop() {
      if (timer) { clearInterval(timer); timer = null; }
    }
    $dots.on('click', function () { stop(); go(parseInt($(this).data('i'), 10) || 0); play(); });
    $hero.on('mouseenter', stop).on('mouseleave', play);
    play();
  })();

  // 无后端留言接口时，用本机邮件客户端发送，避免表单提交到空地址
  $('.wk-contact-form').on('submit', function (e) {
    e.preventDefault();
    var email = $(this).data('email') || '';
    var name = $.trim($(this).find('input[name="name"]').val() || '');
    var phone = $.trim($(this).find('input[name="phone"]').val() || '');
    var body = '姓名：' + name + '\n电话：' + phone + '\n留言：\n';
    location.href = 'mailto:' + email + '?subject=' + encodeURIComponent('网站留言 - ' + name) + '&body=' + encodeURIComponent(body);
  });
});
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

