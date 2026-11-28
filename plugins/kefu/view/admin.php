<?php $pageTitle = $pageTitle ?? '客服坐席'; include __DIR__ . '/../../../app/view/admin/_header.php'; ?>
<style>
.kf-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
.kf-stat{background:#fff;border-radius:8px;padding:16px 18px;box-shadow:0 1px 2px rgba(16,24,40,.06)}
.kf-stat .n{font-size:24px;font-weight:700;color:#111827}
.kf-stat .l{font-size:12px;color:#6b7280;margin-top:4px}
.kf-state{display:flex;align-items:center;gap:10px;font-size:13px;color:#374151}
.kf-dot{width:8px;height:8px;border-radius:99px;display:inline-block}
.kf-dot.on{background:#16a34a;box-shadow:0 0 0 3px #dcfce7}
.kf-dot.off{background:#9ca3af;box-shadow:0 0 0 3px #f3f4f6}
.kf-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#38bdf8);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;overflow:hidden}
.kf-avatar img{width:100%;height:100%;object-fit:cover}
</style>

<div class="kf-stats">
  <div class="kf-stat"><div class="n"><?= (int) $stats['staff'] ?></div><div class="l">坐席总数</div></div>
  <div class="kf-stat"><div class="n"><?= (int) $stats['sessions'] ?></div><div class="l">累计会话</div></div>
  <div class="kf-stat"><div class="n" style="color:#dc2626"><?= (int) $stats['unread'] ?></div><div class="l">待回复消息</div></div>
  <div class="kf-stat"><div class="n"><?= (int) $stats['today'] ?></div><div class="l">今日新增会话</div></div>
</div>

<?php if ($pending): ?>
<div class="card" style="border-left:4px solid #ef4444">
  <div class="card-head">
    <span>🔔 有 <?= count($pending) ?> 个访客在等您回复</span>
    <span style="display:flex;gap:8px">
      <a class="btn btn-primary btn-sm" href="/admin/plugin/kefu/chat?tab=unread">全部去回复 →</a>
      <button type="button" class="btn btn-sm" id="kfSoundBtn">🔔 提示音：开</button>
    </span>
  </div>
  <table class="table">
    <thead>
      <tr><th>访客</th><th>联系方式</th><th>留言内容</th><th>时间</th><th>操作</th></tr>
    </thead>
    <tbody>
    <?php foreach ($pending as $p): $lm = $pendingLast[(int) $p['id']] ?? []; ?>
      <tr>
        <td>
          <div style="font-weight:600"><?= e($p['name']) ?></div>
          <div class="muted">#<?= (int) $p['id'] ?> · <?= e($p['ip']) ?></div>
        </td>
        <td><?= $p['contact'] !== '' ? e($p['contact']) : '<span class="muted">未留</span>' ?></td>
        <td style="max-width:420px"><?= e(mb_substr(trim(strip_tags((string) ($lm['content'] ?? ''))), 0, 60)) ?></td>
        <td class="muted"><?= date('m-d H:i', (int) $p['updated_at']) ?></td>
        <td class="actions">
          <a class="btn btn-sm btn-primary" href="/admin/plugin/kefu/chat/<?= (int) $p['id'] ?>">回复</a>
          <form method="post" action="/admin/plugin/kefu/chat/close" style="display:inline">
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button class="btn btn-sm" type="submit">已处理</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-head">
    <span>坐席列表</span>
    <span style="font-weight:400;font-size:12px;display:flex;gap:12px;align-items:center">
      <span class="kf-state"><i class="kf-dot <?= $online ? 'on' : 'off' ?>"></i><?= $online ? '当前在线' : '当前离线' ?></span>
      <a class="btn btn-primary btn-sm" href="/admin/plugin/kefu/staff/edit">＋ 新增坐席</a>
    </span>
  </div>
  <?php if (!$staff): ?>
    <div class="empty">还没有坐席，点右上角「新增坐席」添加第一个</div>
  <?php else: ?>
  <table class="table">
    <thead>
      <tr><th>坐席</th><th>岗位</th><th>联系方式</th><th>类型</th><th>排序</th><th>状态</th><th>操作</th></tr>
    </thead>
    <tbody>
    <?php foreach ($staff as $s): ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            <span class="kf-avatar"><?php if (!empty($s['avatar'])): ?><img src="<?= e($s['avatar']) ?>" alt=""><?php else: ?><?= e(mb_substr($s['name'], 0, 1)) ?><?php endif; ?></span>
            <span>
              <div style="font-weight:600"><?= e($s['name']) ?></div>
              <?php if (!empty($s['intro'])): ?><div class="muted"><?= e($s['intro']) ?></div><?php endif; ?>
            </span>
          </div>
        </td>
        <td><?= e($s['title']) ?></td>
        <td>
          <?php if (!empty($s['account'])): ?>
            <span style="font-family:Consolas,monospace"><?= e($s['account']) ?></span>
          <?php else: ?>
            <span class="muted">在线对话</span>
          <?php endif; ?>
          <?php if (!empty($s['qr'])): ?><span class="badge" style="margin-left:6px">二维码</span><?php endif; ?>
        </td>
        <td><span class="badge badge-gray"><?= e($types[$s['type']] ?? $s['type']) ?></span></td>
        <td><?= (int) $s['sort'] ?></td>
        <td>
          <?php if ((int) $s['status'] === 1): ?><span class="badge badge-ok">显示</span>
          <?php else: ?><span class="badge badge-gray">隐藏</span><?php endif; ?>
        </td>
        <td class="actions">
          <a class="btn btn-sm" href="/admin/plugin/kefu/staff/edit/<?= (int) $s['id'] ?>">编辑</a>
          <form method="post" action="/admin/plugin/kefu/staff/delete" style="display:inline" onsubmit="return confirm('删除这个坐席？')">
            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <button class="btn btn-sm btn-danger" type="submit">删除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-head"><span>前台效果说明</span></div>
  <div class="card-body" style="font-size:13px;color:#4b5563;line-height:2">
    坐席会按「排序值」从小到大显示在前台浮窗里，访客点「在线对话」直接进入聊天，点电话 / 邮箱 / 链接会跳转对应应用，微信 / 企业微信可以配二维码图片。
    前台是否出现浮窗、什么时间段算在线、欢迎语和自动回复，都在
    <a href="/admin/plugin/kefu/setting" style="color:#2563eb">客服设置</a> 里改。
  </div>
</div>

<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>

<script>
(function () {
  var KEY = 'kefu_sound';
  var on = true;
  try { on = localStorage.getItem(KEY) !== '0'; } catch (e) {}
  var btn = document.getElementById('kfSoundBtn');
  function paint() { if (btn) btn.textContent = '🔔 提示音：' + (on ? '开' : '关'); }
  paint();
  if (btn) {
    btn.addEventListener('click', function () {
      on = !on;
      try { localStorage.setItem(KEY, on ? '1' : '0'); } catch (e) {}
      paint();
      if (on) beep();
    });
  }

  function beep() {
    try {
      var C = window.AudioContext || window.webkitAudioContext;
      if (!C) return;
      var ctx = new C();
      var o = ctx.createOscillator(), g = ctx.createGain();
      o.type = 'sine'; o.frequency.value = 900;
      o.connect(g); g.connect(ctx.destination);
      g.gain.setValueAtTime(0.0001, ctx.currentTime);
      g.gain.exponentialRampToValueAtTime(0.28, ctx.currentTime + 0.02);
      g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.4);
      o.start(); o.stop(ctx.currentTime + 0.42);
    } catch (e) {}
  }

  var lastUnread = <?= (int) ($stats['unread'] ?? 0) ?>;

  function syncBadge(n) {
    var b = document.querySelector('.nav-badge[data-badge="/admin/plugin/kefu/chat"]');
    if (!b) return;
    if (n > 0) { b.textContent = n > 99 ? '99+' : n; b.style.display = ''; }
    else { b.style.display = 'none'; }
  }

  function toast(msg, href) {
    var d = document.createElement('div');
    d.textContent = msg;
    d.style.cssText = 'position:fixed;right:24px;bottom:24px;z-index:99999;background:#111827;color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;cursor:pointer;box-shadow:0 10px 30px rgba(0,0,0,.25)';
    d.addEventListener('click', function () { window.location.href = href; });
    document.body.appendChild(d);
    setTimeout(function () { if (d.parentNode) d.parentNode.removeChild(d); }, 12000);
  }

  setInterval(function () {
    fetch('/admin/plugin/kefu/api/unread', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d || !d.ok) return;
        if (d.unread > lastUnread) {
          if (on) beep();
          toast('🔔 有新的访客留言（' + d.unread + ' 条待回复），点这里去回复', '/admin/plugin/kefu/chat?tab=unread');
        }
        lastUnread = d.unread;
        syncBadge(d.unread);
        document.title = (d.unread > 0 ? '(' + d.unread + ') ' : '') + '客服坐席 - <?= e(addslashes((string) \setting('site_name', '后台'))) ?>';
      })
      .catch(function () {});
  }, 20000);
})();
</script>
