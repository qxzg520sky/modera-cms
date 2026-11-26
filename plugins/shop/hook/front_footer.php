<?php
/**
 * 商城前台导航入口。
 *
 * 设计目标：
 * 1. 不修改主题模板，插件停用后入口自动消失；
 * 2. 不把入口做成大头图/悬浮按钮，避免遮挡内容；
 * 3. 插到主导航第二个位置，窄屏自动缩成图标+短文字，不把其它导航挤换行。
 *
 * 说明：这里输出的是静态入口，不含会员登录态，可安全进入整页缓存。
 */
$cfg = \plugins\shop\service\Shop::cfg();
if (($cfg['enabled'] ?? '1') !== '1') {
    return;
}
?>
<style>
.shop-nav-item > a,
.tz-nav > a.shop-nav-link,
.sw-nav > a.shop-nav-link{display:flex!important;align-items:center;gap:6px;line-height:1.2!important}
/* 工业橙固定按钮宽度：白色主导航的「获取报价」不能被 flex 压成竖排 */
.g2-cta{white-space:nowrap!important;flex:0 0 auto!important}
.g2-nav-act{flex:0 0 auto}
@media (min-width:992px){
  .g2-menu{gap:22px!important;margin-left:16px!important}
}
@media (max-width:1199px){
  .g2-search{display:none!important}
}
/* 视维蓝导航 */
.shop-nav-item > a .shop-nav-ico,
.tz-nav > a.shop-nav-link .shop-nav-ico,
.sw-nav > a.shop-nav-link .shop-nav-ico{display:inline-flex;flex:0 0 auto}
.shop-nav-item > a .shop-nav-ico svg,
.tz-nav > a.shop-nav-link .shop-nav-ico svg,
.sw-nav > a.shop-nav-link .shop-nav-ico svg{width:17px;height:17px;display:block}
.shop-nav-item > a .shop-nav-label,
.tz-nav > a.shop-nav-link .shop-nav-label,
.sw-nav > a.shop-nav-link .shop-nav-label{display:inline-block;white-space:nowrap}
.sw-mobile-in > a.shop-nav-link .shop-nav-ico{display:inline-flex;vertical-align:-3px;margin-right:7px}
.sw-mobile-in > a.shop-nav-link .shop-nav-ico svg{width:15px;height:15px;display:block}
.sw-mobile-in > a.shop-nav-link .shop-nav-dot{display:none}
.shop-nav-item > a .shop-nav-dot{width:5px;height:5px;border-radius:50%;background:#ffd34d;flex:0 0 auto;margin-left:1px}
/* 1366 及以下：只压缩商城入口自身和导航左右内边距，不改主题结构 */
@media (min-width:981px) and (max-width:1366px){
  .shop-nav-item > a{padding-left:11px!important;padding-right:11px!important;font-size:14px!important}
  .gt-menu > li > a{padding-left:12px;padding-right:12px;font-size:14px}
  .gt-search input{width:96px}
}
/* 1100 以下：收起文字只留购物袋图标，宽度约 40px，避免挤压搜索框 */
@media (min-width:981px) and (max-width:1100px){
  .shop-nav-item > a{padding-left:12px!important;padding-right:12px!important}
  .shop-nav-item > a .shop-nav-label,
  .shop-nav-item > a .shop-nav-dot{display:none}
  .gt-menu > li > a{padding-left:9px;padding-right:9px;font-size:13px}
  .gt-search input{width:80px}
}
@media (max-width:980px){
  .shop-nav-item > a{height:42px!important;line-height:42px!important;padding:0 22px!important;font-size:15px!important;
    background:rgba(255,255,255,.10)}
  .shop-nav-item > a .shop-nav-ico svg{width:15px;height:15px}
}
</style>
<script>
(function () {
  if (window.__shopNavMounted) return;
  window.__shopNavMounted = true;

  var path = location.pathname || '/';
  var active = path === '/shop' || path.indexOf('/shop/') === 0 ? ' on' : '';

  function makeLink(cls) {
    var a = document.createElement('a');
    a.href = '/shop';
    a.className = cls;
    a.setAttribute('aria-label', '企业商城');
    var ico = document.createElement('span');
    ico.className = 'shop-nav-ico';
    ico.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8h12l-1.2 12H7.2L6 8z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>';
    var label = document.createElement('span');
    label.className = 'shop-nav-label';
    label.textContent = '商城';
    var dot = document.createElement('span');
    dot.className = 'shop-nav-dot';
    a.appendChild(ico);
    a.appendChild(label);
    a.appendChild(dot);
    return a;
  }

  // 工业蓝/白色主导航（gangtie / gangtie2）
  var menu = document.querySelector('.gt-menu') || document.querySelector('.g2-menu');
  if (menu) {
    var old = menu.querySelector('.shop-nav-item');
    if (old) old.remove();
    var li = document.createElement('li');
    li.className = 'shop-nav-item' + active;
    li.appendChild(makeLink(''));
    var second = menu.children.length > 1 ? menu.children[1] : null;
    menu.insertBefore(li, second);
  }

  // 视维蓝导航（shiwei）：桌面导航 + 移动菜单各加一个
  var swNav = document.querySelector('.sw-nav');
  if (swNav) {
    var oldSw = swNav.querySelector('.shop-nav-link');
    if (oldSw) oldSw.remove();
    var swFirst = swNav.querySelector('a');
    var swLink = makeLink('shop-nav-link' + active);
    if (swFirst && swFirst.nextSibling) swNav.insertBefore(swLink, swFirst.nextSibling);
    else swNav.appendChild(swLink);
    var swMob = document.querySelector('.sw-mobile-in');
    if (swMob) {
      var oldSwM = swMob.querySelector('.shop-nav-link');
      if (oldSwM) oldSwM.remove();
      var swMFirst = swMob.querySelector('a');
      var swMLink = makeLink('shop-nav-link' + active);
      if (swMFirst && swMFirst.nextSibling) swMob.insertBefore(swMLink, swMFirst.nextSibling);
      else swMob.insertBefore(swMLink, swMob.firstChild);
    }
  }

  // 投资蓝导航（touzi）
  var tz = document.querySelector('.tz-nav');
  if (tz) {
    var oldA = tz.querySelector('.shop-nav-link');
    if (oldA) oldA.remove();
    var a = makeLink('shop-nav-link' + active);
    var firstA = tz.querySelector('a');
    if (firstA && firstA.nextSibling) tz.insertBefore(a, firstA.nextSibling);
    else tz.appendChild(a);
  }
})();
</script>
