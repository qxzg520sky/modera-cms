/* 在线客服插件 · 前台脚本（原生 JS，无依赖） */
(function () {
  'use strict';

  var dataEl = document.getElementById('kefuData');
  var root = document.getElementById('kefuRoot');
  if (!dataEl || !root) return;

  var C = {};
  try { C = JSON.parse(dataEl.textContent || '{}'); } catch (e) { return; }
  if (!C || !C.api) return;

  var STORE_TOKEN = 'kefu_token';
  var STORE_LAST = 'kefu_last';
  var token = '';
  var lastId = 0;
  try {
    token = localStorage.getItem(STORE_TOKEN) || '';
    lastId = parseInt(localStorage.getItem(STORE_LAST) || '0', 10) || 0;
  } catch (e) {}

  /* ---------- 样式变量 ---------- */
  if (C.theme) root.style.setProperty('--kf-c', C.theme);
  if (C.gap) root.style.setProperty('--kf-gap', C.gap + 'px');

  /* ---------- 搭骨架 ---------- */
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined && text !== null) n.textContent = text;
    return n;
  }

  var launcher = el('button', 'kf-launcher');
  launcher.type = 'button';
  launcher.setAttribute('aria-label', '打开在线客服');
  if (C.btnIcon) launcher.appendChild(el('span', 'kf-ico', C.btnIcon));
  if (C.btnText) launcher.appendChild(el('span', 'kf-txt', C.btnText));
  var launcherDot = el('i', 'kf-dot', '');
  launcher.appendChild(launcherDot);

  var panel = el('div', 'kf-panel');

  var head = el('div', 'kf-head');
  head.appendChild(el('h3', '', C.title || '在线客服'));
  if (C.subtitle) head.appendChild(el('p', '', C.subtitle));
  var state = el('span', 'kf-state' + (C.online ? '' : ' off'));
  state.appendChild(el('i', ''));
  state.appendChild(document.createTextNode(C.online ? '在线 · 通常几分钟内回复' : '离线 · 可先留言'));
  head.appendChild(state);
  var closeBtn = el('button', 'kf-close', '×');
  closeBtn.type = 'button';
  head.appendChild(closeBtn);

  var body = el('div', 'kf-body');
  var welcome = el('div', 'kf-welcome');
  welcome.appendChild(document.createTextNode(C.welcome || ''));
  if (!C.online && C.offline) {
    welcome.appendChild(el('span', 'kf-tip', C.offline));
  }
  body.appendChild(welcome);

  var staffBox = el('div', 'kf-staffs');
  body.appendChild(staffBox);
  var chatBox = el('div', 'kf-chat');
  body.appendChild(chatBox);
  var backBar = el('div', 'kf-back kf-hide', '← 返回坐席列表');
  backBar.addEventListener('click', function () { backToStaffs(); });
  body.insertBefore(backBar, chatBox);

  var foot = el('div', 'kf-foot');
  var contactRow = el('div', 'kf-contact');
  var nameInput = el('input');
  nameInput.type = 'text'; nameInput.placeholder = '您的称呼'; nameInput.maxLength = 20;
  var contactInput = el('input');
  contactInput.type = 'text'; contactInput.placeholder = '手机号 / 微信 / 邮箱';
  contactInput.maxLength = 60;
  contactRow.appendChild(nameInput);
  contactRow.appendChild(contactInput);

  var sendRow = el('div', 'kf-send');
  var ta = el('textarea');
  ta.placeholder = '说说您想咨询的问题…';
  ta.rows = 1;
  var sendBtn = el('button', '', '发送');
  sendBtn.type = 'button';
  sendRow.appendChild(ta);
  sendRow.appendChild(sendBtn);

  var hint = el('div', 'kf-hintline', (C.online ? '客服在线，发送后可直接对话' : '当前离线，留言后客服会在下个工作日联系您'));
  foot.appendChild(contactRow);
  foot.appendChild(sendRow);
  foot.appendChild(hint);

  panel.appendChild(head);
  panel.appendChild(body);
  panel.appendChild(foot);

  var qrMask = el('div', 'kf-qr-mask');
  var qrBox = el('div', 'kf-qr-box');
  qrMask.appendChild(qrBox);

  var toast = el('div', 'kf-toast', '');

  root.appendChild(launcher);
  root.appendChild(panel);
  root.appendChild(qrMask);
  root.appendChild(toast);

  if (C.allowChat === false) {
    foot.style.display = 'none';
  }
  if (!C.needContact) {
    // 不需要留联系方式时，只在第一次对话时显示称呼（可选）
    contactRow.classList.remove('on');
  }

  /* ---------- 工具 ---------- */
  var toastTimer = null;
  function showToast(msg) {
    toast.textContent = msg;
    toast.classList.add('on');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toast.classList.remove('on'); }, 2200);
  }

  function saveToken(t) {
    token = t;
    try { localStorage.setItem(STORE_TOKEN, t); } catch (e) {}
  }
  function saveLast(n) {
    lastId = n;
    try { localStorage.setItem(STORE_LAST, String(n)); } catch (e) {}
  }

  function scrollBottom() {
    body.scrollTop = body.scrollHeight;
  }

  function appendMsg(m) {
    var node = el('div', 'kf-msg ' + (m.role === 'admin' ? 'admin' : m.role === 'system' ? 'system' : 'visitor'));
    node.appendChild(document.createTextNode(m.content));
    if (m.time) node.appendChild(el('span', 'kf-t', m.time));
    chatBox.appendChild(node);
    chatBox.classList.add('on');
    scrollBottom();
  }

  function clearChat() {
    chatBox.innerHTML = '';
    chatBox.classList.remove('on');
  }

  /* ---------- 坐席卡片 ---------- */
  var TYPE_LABEL = {
    online: '开始对话', wechat: '加微信', wecom: '加企微', qq: '加 QQ',
    phone: '打电话', email: '发邮件', link: '打开链接'
  };

  function copyText(t) {
    if (!t) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(t).then(function () { showToast('已复制：' + t); }, function () { showToast(t); });
    } else {
      var tmp = document.createElement('textarea');
      tmp.value = t; document.body.appendChild(tmp); tmp.select();
      try { document.execCommand('copy'); showToast('已复制：' + t); } catch (e) { showToast(t); }
      document.body.removeChild(tmp);
    }
  }

  function openQr(s) {
    qrBox.innerHTML = '';
    qrBox.appendChild(el('div', 'kf-qr-name', s.name + (s.title ? ' · ' + s.title : '')));
    var img = el('img');
    img.src = s.qr;
    img.alt = s.name + ' 二维码';
    qrBox.appendChild(img);
    if (s.account) {
      var btn = el('button', 'kf-send-qr', '复制账号：' + s.account);
      btn.type = 'button';
      btn.style.cssText = 'margin-top:10px;padding:8px 14px;border:1px solid #e5e7eb;background:#fff;border-radius:8px;cursor:pointer;font:inherit;font-size:12px';
      btn.addEventListener('click', function () { copyText(s.account); });
      qrBox.appendChild(btn);
    }
    qrBox.appendChild(el('div', 'kf-qr-tip', '长按或扫码识别，也可以直接复制上面的账号'));
    qrMask.classList.add('on');
  }
  qrMask.addEventListener('click', function () { qrMask.classList.remove('on'); });

  function openChat(fromStaff) {
    chatBox.classList.add('on');
    if (fromStaff) {
      welcome.classList.add('kf-hide');
      staffBox.classList.add('kf-hide');
      backBar.classList.remove('kf-hide');
    }
    if (C.needContact && !token) contactRow.classList.add('on');
    setTimeout(function () {
      ta.focus();
      if (fromStaff || chatBox.childElementCount > 0) { scrollBottom(); } else { body.scrollTop = 0; }
    }, 60);
  }

  function backToStaffs() {
    chatBox.classList.remove('on');
    backBar.classList.add('kf-hide');
    welcome.classList.remove('kf-hide');
    staffBox.classList.remove('kf-hide');
    body.scrollTop = 0;
  }

  function staffAction(s) {
    var t = s.type || 'online';
    if ((t === 'wechat' || t === 'wecom' || t === 'qq') && s.qr && C.showQr) { openQr(s); return; }
    if (t === 'phone' && s.account) { window.location.href = 'tel:' + s.account.replace(/[^\d+\-]/g, ''); return; }
    if (t === 'email' && s.account) { window.location.href = 'mailto:' + s.account; return; }
    if (t === 'link' && s.account) { window.open(s.account, '_blank', 'noopener'); return; }
    if ((t === 'wechat' || t === 'wecom' || t === 'qq') && s.account) { copyText(s.account); openChat(true); return; }
    openChat(true);
  }

  (C.staff || []).forEach(function (s) {
    var card = el('div', 'kf-staff');
    var ava = el('span', 'kf-ava');
    if (s.avatar) {
      var img = el('img'); img.src = s.avatar; img.alt = s.name; ava.appendChild(img);
    } else {
      ava.textContent = (s.name || '客').slice(0, 1);
    }
    var info = el('div', 'kf-info');
    info.appendChild(el('div', 'kf-name', s.name || '客服'));
    var sub = s.title || s.account || '';
    if (s.intro) sub = sub ? sub + ' · ' + s.intro : s.intro;
    if (sub) info.appendChild(el('div', 'kf-sub', sub));
    var act = el('span', 'kf-act', TYPE_LABEL[s.type] || '联系');
    card.appendChild(ava);
    card.appendChild(info);
    card.appendChild(act);
    card.addEventListener('click', function () { staffAction(s); });
    staffBox.appendChild(card);
  });
  if (!(C.staff || []).length) staffBox.style.display = 'none';

  /* ---------- 发送 ---------- */
  var sending = false;
  function post(url, body) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body,
      credentials: 'same-origin'
    }).then(function (r) { return r.json(); });
  }

  function send() {
    var text = (ta.value || '').trim();
    if (text === '') { ta.focus(); return; }
    if (sending) return;
    sending = true;
    sendBtn.disabled = true;

    var body = 'content=' + encodeURIComponent(text)
      + '&token=' + encodeURIComponent(token)
      + '&name=' + encodeURIComponent(nameInput.value || '')
      + '&contact=' + encodeURIComponent(contactInput.value || '')
      + '&page=' + encodeURIComponent(C.page || '');

    post(C.api + '/send', body).then(function (res) {
      sending = false;
      sendBtn.disabled = false;
      if (!res || !res.ok) {
        if (res && res.need_contact) {
          contactRow.classList.add('on');
          showToast(res.msg || '请先留下联系方式');
          nameInput.focus();
          return;
        }
        showToast((res && res.msg) || '发送失败，稍后再试');
        return;
      }
      saveToken(res.token);
      if (res.id) saveLast(Math.max(lastId, res.id));
      appendMsg({ role: 'visitor', content: text, time: new Date().toTimeString().slice(0, 5) });
      ta.value = '';
      ta.style.height = '42px';
      contactRow.classList.remove('on');
      hint.textContent = C.online ? '客服在线，发送后可直接对话' : '已收到，客服上线后会联系您';
    }).catch(function () {
      sending = false;
      sendBtn.disabled = false;
      showToast('网络异常，请稍后再试');
    });
  }

  sendBtn.addEventListener('click', send);
  ta.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
      e.preventDefault();
      send();
    }
  });
  ta.addEventListener('input', function () {
    ta.style.height = '42px';
    ta.style.height = Math.min(110, ta.scrollHeight + 4) + 'px';
  });

  /* ---------- 打开面板时恢复历史对话 ---------- */
  function loadHistory() {
    if (!token || chatBox.childElementCount > 0) return;
    fetch(C.api + '/poll?token=' + encodeURIComponent(token) + '&after=0', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.ok || !res.messages || !res.messages.length) return;
        res.messages.forEach(function (m) {
          appendMsg(m);
          if (m.id > lastId) { saveLast(m.id); }
        });
      })
      .catch(function () {});
  }

  /* ---------- 轮询 ---------- */
  function poll(silent) {
    if (!token) return;
    var url = C.api + '/poll?token=' + encodeURIComponent(token) + '&after=' + lastId;
    fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (res) {
      if (!res || !res.ok) return;
      if (res.gone) {
        try { localStorage.removeItem(STORE_TOKEN); localStorage.removeItem(STORE_LAST); } catch (e) {}
        token = ''; lastId = 0;
        return;
      }
      if (res.messages && res.messages.length) {
        res.messages.forEach(function (m) {
          if (m.id > lastId) {
            appendMsg(m);
            saveLast(m.id);
            if (!panel.classList.contains('is-open') && m.role !== 'visitor') {
              launcherDot.textContent = String((parseInt(launcherDot.textContent || '0', 10) || 0) + 1);
              launcher.classList.add('has-unread');
            }
          }
        });
      }
      state.className = 'kf-state' + (res.online ? '' : ' off');
      if (res.closed) {
        hint.textContent = '本次会话已处理完毕，有新问题可以继续发送';
      }
    }).catch(function () {});
  }

  var timer = null;
  function startPoll() {
    if (timer) return;
    timer = setInterval(function () {
      if (document.hidden) return;
      poll(true);
    }, Math.max(3, C.poll || 5) * 1000);
  }
  function stopPoll() {
    if (timer) { clearInterval(timer); timer = null; }
  }

  /* ---------- 开关面板 ---------- */
  function openPanel() {
    panel.classList.add('is-open');
    launcher.classList.remove('has-unread');
    launcherDot.textContent = '';
    openChat();
    loadHistory();
    if (lastId > 0) { poll(true); }
  }
  function closePanel() {
    panel.classList.remove('is-open');
  }

  launcher.addEventListener('click', function () {
    if (panel.classList.contains('is-open')) { closePanel(); } else { openPanel(); }
  });
  closeBtn.addEventListener('click', closePanel);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && panel.classList.contains('is-open')) closePanel();
  });

  // 有历史 token 就先拉一遍，红点提示离线期间的新消息
  if (token) {
    if (lastId === 0) { poll(true); } else { poll(true); }
  }
  startPoll();
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) { poll(true); }
  });
})();
