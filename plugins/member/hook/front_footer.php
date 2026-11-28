<?php
/**
 * 前台会员入口：全部由本钩子输出。
 * 插件停用后 plugin_hook('front_footer') 不会加载本文件，前台不留任何会员入口。
 *
 * 重要：本文件不直接读取会员 session，登录态由 JS 请求 /member/status 后再渲染。
 * 因为首页等页面存在整页缓存，如果把登录态写进 HTML，会把“游客菜单”缓存给已登录会员。
 */
$cfg = \plugins\member\service\Member::cfg();
if (($cfg['enabled'] ?? '1') !== '1' || ($cfg['footer_link'] ?? '1') !== '1') {
    return;
}
?>
<style>
#mbfNavMount{display:flex;align-items:stretch;flex:0 0 auto}
.mbf-nav-drop{position:relative;display:flex;align-items:stretch;flex:0 0 auto}
.mbf-nav-trigger{display:flex;align-items:center;gap:8px;padding:0 14px;color:#333;font-size:14px;font-weight:600;white-space:nowrap;text-decoration:none!important}
.mbf-nav-trigger:hover{background:var(--tz-blue,#0c65bb);color:#fff!important}
.mbf-ava{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#1558b0,#16a6c9);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex:0 0 auto}
.mbf-name{display:flex;flex-direction:column;line-height:1.2;max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mbf-name small{font-size:9px;font-weight:500;color:#999;margin-top:2px}
.mbf-nav-trigger:hover .mbf-name small{color:rgba(255,255,255,.8)}
.mbf-menu{display:none;position:absolute;right:0;top:100%;min-width:188px;background:#fff;border:1px solid #e5eaf1;border-radius:8px;box-shadow:0 16px 38px rgba(12,34,66,.16);padding:8px;z-index:10001}
.mbf-nav-drop:hover .mbf-menu,.mbf-nav-drop:focus-within .mbf-menu{display:block}
.mbf-menu-head{padding:9px 10px 10px;border-bottom:1px dashed #e8eef7;margin-bottom:5px;color:#64748b;font-size:11.5px;line-height:1.6}
.mbf-menu-head b{display:block;color:#0f172a;font-size:13.5px;margin-bottom:2px}
.mbf-menu a{display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border-radius:6px;color:#334155;font-size:13px;text-decoration:none!important}
.mbf-menu a:hover{background:#eef4fd;color:var(--tz-blue,#1558b0)!important}
.mbf-menu .mbf-exit{margin-top:4px;border-top:1px dashed #eef2f7;color:#b42318;border-radius:0 0 6px 6px}
.mbf-guest{display:flex;align-items:center;gap:6px;padding:0 10px}
.mbf-guest a{display:inline-flex;align-items:center;justify-content:center;height:32px;padding:0 12px;border-radius:5px;font-size:13px;font-weight:700;text-decoration:none!important;white-space:nowrap}
.mbf-guest .mbf-login{color:var(--tz-blue,#1558b0);border:1px solid rgba(21,88,176,.35)}
.mbf-guest .mbf-login:hover{background:var(--tz-blue,#1558b0);color:#fff!important}
.mbf-guest .mbf-reg{background:var(--tz-blue,#1558b0);color:#fff}
.mbf-guest .mbf-reg:hover{filter:brightness(1.06)}
.mbf-dock{position:fixed;right:16px;top:16px;z-index:10000}
.mbf-dock .mbf-nav-trigger{height:40px;border-radius:99px;background:linear-gradient(135deg,#1558b0,#16a6c9);color:#fff;box-shadow:0 10px 26px rgba(15,55,120,.24);padding:0 14px}
.mbf-dock .mbf-nav-trigger .mbf-name small{color:rgba(255,255,255,.75)}
.mbf-dock .mbf-menu{right:0;top:calc(100% + 8px)}
.g2-tb-right{display:flex;align-items:center;gap:16px}
#mbfNavMount.mbf-topbar .mbf-nav-trigger{color:#fff;font-size:13px;padding:0 4px;gap:7px}
#mbfNavMount.mbf-topbar .mbf-nav-trigger:hover{background:transparent;color:var(--org,#ff5e14)!important}
#mbfNavMount.mbf-topbar .mbf-ava{width:24px;height:24px;font-size:11px}
#mbfNavMount.mbf-topbar .mbf-name small{color:#b9bfc6}
#mbfNavMount.mbf-topbar .mbf-nav-trigger:hover .mbf-name small{color:#ffd0b6}
#mbfNavMount.mbf-topbar .mbf-guest{padding:0;gap:8px}
#mbfNavMount.mbf-topbar .mbf-guest a{height:26px;padding:0 9px;font-size:12px}
#mbfNavMount.mbf-topbar .mbf-guest .mbf-login{color:#fff;border-color:rgba(255,255,255,.35)}
#mbfNavMount.mbf-topbar .mbf-guest .mbf-login:hover{background:#fff;color:#111!important}
#mbfNavMount.mbf-topbar .mbf-guest .mbf-reg{background:var(--org,#ff5e14)}
#mbfNavMount.mbf-topbar .mbf-menu{top:calc(100% + 12px)}
.g2-menu > li > a{white-space:nowrap}
.tz-nav > a,.tz-nav-drop > a{white-space:nowrap}
.mbf-fab{display:none;position:fixed;left:14px;bottom:16px;z-index:9998}
.mbf-fab .mbf-nav-drop{display:flex}
.mbf-fab .mbf-nav-trigger{height:38px;border-radius:99px;background:linear-gradient(135deg,#1558b0,#16a6c9);color:#fff;box-shadow:0 10px 26px rgba(15,55,120,.24);padding:0 12px;font-size:12px}
.mbf-fab .mbf-nav-trigger .mbf-name small{color:rgba(255,255,255,.75)}
.mbf-fab .mbf-guest{display:flex;padding:0}
.mbf-fab .mbf-guest .mbf-login{height:38px;padding:0 16px;border:0;border-radius:99px;background:linear-gradient(135deg,#1558b0,#16a6c9);color:#fff!important;box-shadow:0 10px 26px rgba(15,55,120,.24)}
.mbf-fab .mbf-guest .mbf-reg{display:none}
.mbf-fab .mbf-menu{display:none!important}
.mbf-mobile-group{margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.12)}
.mbf-mobile-head{padding:8px 4px 10px;color:#9fb0c1;font-size:12px;line-height:1.6}
.mbf-mobile-head b{display:block;color:#fff;font-size:14px;margin-bottom:2px}
.mbf-mobile-links{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.mbf-mobile-links a{display:flex;align-items:center;justify-content:center;height:39px;border:1px solid rgba(255,255,255,.14);border-radius:6px;color:#e8edf3;font-size:13px;text-decoration:none!important;background:rgba(255,255,255,.03)}
.mbf-mobile-links a:hover{background:var(--tz-blue,#1558b0);border-color:var(--tz-blue,#1558b0);color:#fff!important}
.mbf-mobile-links .mbf-reg{background:var(--tz-blue,#1558b0);border-color:var(--tz-blue,#1558b0);color:#fff}
.mbf-mobile-links .mbf-exit{grid-column:1/-1;color:#ffb4b4;border-color:rgba(255,120,120,.3)}
.mbf-g2-mobile{display:none}
@media(max-width:1180px){.mbf-nav-trigger{padding:0 10px;font-size:13px}}
@media(max-width:980px){
  .mbf-nav-drop{display:none}
  .mbf-guest{display:none}
  .mbf-fab{display:block}
  .mbf-fab .mbf-nav-drop{display:flex!important}
  .mbf-fab .mbf-guest{display:flex!important}
  .mbf-g2-mobile{display:block!important;width:100%;border-bottom:1px solid #f0f2f4}
  .mbf-g2-mobile > a{display:block;height:48px;line-height:48px;padding:0 20px;font-size:14px;color:#152035}
  .mbf-g2-mobile > a:hover{color:#ff5e14}
}
@media(max-width:560px){.mbf-dock{right:10px;top:10px}.mbf-fab{left:10px;bottom:12px}.mbf-dock .mbf-nav-trigger{height:36px;padding:0 11px;font-size:12px}}
</style>

<div id="mbfNavMount"></div>
<div id="mbfFabMount" class="mbf-fab"></div>

<script>
(function () {
  var mount = document.getElementById('mbfNavMount');
  var fabMount = document.getElementById('mbfFabMount');
  if (!mount || !fabMount) return;
  var statusUrl = '/member/status?_=' + Date.now();

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined && text !== null) n.textContent = text;
    return n;
  }

  function link(href, label, cls, confirmText) {
    var a = el('a', cls || '', label);
    a.href = href;
    if (confirmText) {
      a.setAttribute('data-confirm', confirmText);
      a.addEventListener('click', function (e) {
        if (!window.confirm(confirmText)) e.preventDefault();
      });
    }
    return a;
  }

  function buildNav(logged, d) {
    if (!logged) {
      var guest = el('div', 'mbf-guest');
      guest.appendChild(link('/member/login', '登录', 'mbf-login'));
      guest.appendChild(link('/member/register', '注册', 'mbf-reg'));
      return guest;
    }
    var name = d.name || '会员';
    var drop = el('div', 'mbf-nav-drop');
    var trigger = el('a', 'mbf-nav-trigger');
    trigger.href = '/member/center';
    trigger.appendChild(el('span', 'mbf-ava', name.slice(0, 1)));
    var nameBox = el('span', 'mbf-name', name);
    nameBox.appendChild(el('small', '', d.level || '会员中心'));
    trigger.appendChild(nameBox);

    var menu = el('div', 'mbf-menu');
    var head = el('div', 'mbf-menu-head');
    head.appendChild(el('b', '', name));
    head.appendChild(document.createTextNode('余额 ￥' + (d.balance || '0.00') + ' · 积分 ' + (d.points || 0)));
    menu.appendChild(head);
    menu.appendChild(link('/member/center', '会员中心'));
    menu.appendChild(link('/member/orders', '我的订单'));
    menu.appendChild(link('/member/address', '收货地址'));
    menu.appendChild(link('/member/logs', '资产明细'));
    menu.appendChild(link('/member/profile', '会员资料'));
    menu.appendChild(link('/member/logout', '退出登录', 'mbf-exit', '确定退出会员中心？'));

    drop.appendChild(trigger);
    drop.appendChild(menu);
    return drop;
  }

  function mobileLinks(logged) {
    if (!logged) {
      return [
        ['/member/login', '会员登录', ''],
        ['/member/register', '注册会员', 'mbf-reg']
      ];
    }
    return [
      ['/member/center', '会员中心', ''],
      ['/member/orders', '我的订单', ''],
      ['/member/address', '收货地址', ''],
      ['/member/logs', '资产明细', ''],
      ['/member/profile', '会员资料', ''],
      ['/member/logout', '退出登录', 'mbf-exit']
    ];
  }

  function buildTzMobile(logged, d) {
    var box = el('div', 'mbf-mobile-group');
    var head = el('div', 'mbf-mobile-head');
    if (logged) {
      head.appendChild(el('b', '', d.name || '会员'));
      head.appendChild(document.createTextNode((d.level || '会员') + ' · 余额 ￥' + (d.balance || '0.00') + ' · 积分 ' + (d.points || 0)));
    } else {
      head.appendChild(el('b', '', '会员中心'));
      head.appendChild(document.createTextNode('登录后可查看余额、积分、订单和收货地址'));
    }
    box.appendChild(head);
    var grid = el('div', 'mbf-mobile-links');
    mobileLinks(logged).forEach(function (it) {
      var a = link(it[0], it[1], it[2], it[2] === 'mbf-exit' ? '确定退出会员中心？' : '');
      grid.appendChild(a);
    });
    box.appendChild(grid);
    return box;
  }

  function buildG2Mobile(logged) {
    var menu = document.getElementById('g2Menu');
    if (!menu) return;
    menu.querySelectorAll('.mbf-g2-mobile').forEach(function (n) { n.remove(); });
    mobileLinks(logged).forEach(function (it) {
      var li = el('li', 'mbf-g2-mobile');
      li.appendChild(link(it[0], it[1], '', it[2] === 'mbf-exit' ? '确定退出会员中心？' : ''));
      menu.appendChild(li);
    });
  }

  function applyState(logged, d) {
    d = d || {};
    var nav = buildNav(logged, d);

    var desk = document.querySelector('.tz-nav');
    var g2Top = document.querySelector('.g2-tb-right');
    var g2Act = document.querySelector('.g2-nav-act');
    mount.className = '';
    if (desk) {
      desk.appendChild(mount);
    } else if (g2Top) {
      mount.className = 'mbf-topbar';
      g2Top.insertBefore(mount, g2Top.firstChild);
    } else if (g2Act) {
      var search = g2Act.querySelector('.g2-search');
      if (search) g2Act.insertBefore(mount, search);
      else g2Act.insertBefore(mount, g2Act.firstChild);
    } else {
      mount.className = 'mbf-dock';
    }
    mount.innerHTML = '';
    mount.appendChild(nav);

    fabMount.innerHTML = '';
    fabMount.appendChild(buildNav(logged, d));

    var mob = document.querySelector('.tz-mobile-in');
    if (mob) {
      var old = mob.querySelector('.mbf-mobile-group');
      if (old) old.remove();
      var foot = mob.querySelector('.tz-mobile-foot');
      if (foot) mob.insertBefore(buildTzMobile(logged, d), foot);
      else mob.appendChild(buildTzMobile(logged, d));
    }
    buildG2Mobile(logged);
  }

  fetch(statusUrl, { credentials: 'same-origin', cache: 'no-store' })
    .then(function (r) { return r.json(); })
    .then(function (d) { applyState(!!(d && d.logged), d || {}); })
    .catch(function () { applyState(false, {}); });
})();
</script>
