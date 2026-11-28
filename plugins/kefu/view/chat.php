<?php $pageTitle = $pageTitle ?? '客服会话'; include __DIR__ . '/../../../app/view/admin/_header.php'; ?>
<style>
.kf-tabs{display:flex;gap:8px;margin-bottom:14px}
.kf-tab{padding:7px 14px;border:1px solid #e5e7eb;background:#fff;border-radius:99px;font-size:13px;color:#374151;text-decoration:none}
.kf-tab.on{background:#2563eb;border-color:#2563eb;color:#fff}
.kf-search{display:flex;gap:8px;margin-bottom:14px}
.kf-search input{flex:1;max-width:320px}
.kf-wap{display:flex;gap:10px;align-items:center}
.kf-ava{width:32px;height:32px;border-radius:50%;background:#eef2ff;color:#3730a3;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;flex:none}
</style>

<div class="kf-stats" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px">
  <div style="background:#fff;border-radius:8px;padding:16px 18px;box-shadow:0 1px 2px rgba(16,24,40,.06)"><div style="font-size:24px;font-weight:700"><?= (int) $stats['sessions'] ?></div><div style="font-size:12px;color:#6b7280;margin-top:4px">累计会话</div></div>
  <div style="background:#fff;border-radius:8px;padding:16px 18px;box-shadow:0 1px 2px rgba(16,24,40,.06)"><div style="font-size:24px;font-weight:700;color:#dc2626"><?= (int) $stats['unread'] ?></div><div style="font-size:12px;color:#6b7280;margin-top:4px">待回复消息</div></div>
  <div style="background:#fff;border-radius:8px;padding:16px 18px;box-shadow:0 1px 2px rgba(16,24,40,.06)"><div style="font-size:24px;font-weight:700"><?= (int) $stats['today'] ?></div><div style="font-size:12px;color:#6b7280;margin-top:4px">今日新增</div></div>
  <div style="background:#fff;border-radius:8px;padding:16px 18px;box-shadow:0 1px 2px rgba(16,24,40,.06)"><div style="font-size:24px;font-weight:700"><?= (int) $stats['messages'] ?></div><div style="font-size:12px;color:#6b7280;margin-top:4px">消息总数</div></div>
</div>

<div class="kf-tabs">
  <?php
  $tabs = ['all' => '全部', 'unread' => '待回复', 'open' => '进行中', 'closed' => '已处理'];
  foreach ($tabs as $k => $label):
      $q = array_filter(['tab' => $k, 'kw' => $kw]);
  ?>
    <a class="kf-tab <?= $tab === $k ? 'on' : '' ?>" href="/admin/plugin/kefu/chat?<?= http_build_query($q) ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</div>

<form class="kf-search" method="get" action="/admin/plugin/kefu/chat">
  <input type="hidden" name="tab" value="<?= e($tab) ?>">
  <input type="text" name="kw" value="<?= e($kw) ?>" placeholder="搜访客名称 / 联系方式 / 来源页面">
  <button class="btn" type="submit">搜索</button>
  <?php if ($kw !== ''): ?><a class="btn btn-ghost" href="/admin/plugin/kefu/chat?tab=<?= e($tab) ?>">清空</a><?php endif; ?>
</form>

<div class="card">
  <div class="card-head"><span>会话列表</span><span class="muted" style="font-weight:400;font-size:12px">按最后活动时间倒序，最多 60 条</span></div>
  <?php if (!$list): ?>
    <div class="empty">还没有会话。访客在前台提交留言后会出现在这里</div>
  <?php else: ?>
  <table class="table">
    <thead>
      <tr><th>访客</th><th>联系方式</th><th>最后一条</th><th>来源页面</th><th>时间</th><th>状态</th><th>操作</th></tr>
    </thead>
    <tbody>
    <?php foreach ($list as $s): $last = $lasts[(int) $s['id']] ?? null; ?>
      <tr>
        <td>
          <div class="kf-wap">
            <span class="kf-ava"><?= e(mb_substr($s['name'] ?: '访', 0, 1)) ?></span>
            <span>
              <div style="font-weight:600"><?= e($s['name']) ?>
                <?php if ((int) $s['admin_unread'] > 0): ?><span class="badge" style="background:#fee2e2;color:#b91c1c;margin-left:6px"><?= (int) $s['admin_unread'] ?> 条未读</span><?php endif; ?>
              </div>
              <div class="muted">#<?= (int) $s['id'] ?> · <?= e($s['ip']) ?></div>
            </span>
          </div>
        </td>
        <td><?= $s['contact'] !== '' ? e($s['contact']) : '<span class="muted">未留</span>' ?></td>
        <td style="max-width:320px">
          <?php if ($last): ?>
            <span class="badge badge-gray" style="margin-right:6px"><?= $last['role'] === 'visitor' ? '访客' : ($last['role'] === 'admin' ? '客服' : '系统') ?></span>
            <span style="color:#4b5563"><?= e(mb_substr(trim(strip_tags((string) $last['content'])), 0, 40)) ?></span>
          <?php else: ?><span class="muted">无消息</span><?php endif; ?>
        </td>
        <td class="muted" style="max-width:220px;word-break:break-all"><?= e($s['page']) ?></td>
        <td class="muted"><?= date('m-d H:i', (int) $s['updated_at']) ?></td>
        <td>
          <?php if ((int) $s['status'] === 2): ?><span class="badge badge-gray">已处理</span>
          <?php elseif ((int) $s['admin_unread'] > 0): ?><span class="badge" style="background:#fef3c7;color:#b45309">待回复</span>
          <?php else: ?><span class="badge badge-ok">已回复</span><?php endif; ?>
        </td>
        <td class="actions">
          <a class="btn btn-sm btn-primary" href="/admin/plugin/kefu/chat/<?= (int) $s['id'] ?>">查看 / 回复</a>
          <form method="post" action="/admin/plugin/kefu/chat/delete" style="display:inline" onsubmit="return confirm('删除这条会话和里面的全部消息？')">
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

<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>

<script>
(function () {
  var on = true;
  try { on = localStorage.getItem('kefu_sound') !== '0'; } catch (e) {}

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

  function tip(unread) {
    if (document.getElementById('kfNewTip')) return;
    var bar = document.createElement('div');
    bar.id = 'kfNewTip';
    bar.style.cssText = 'position:fixed;left:50%;top:18px;transform:translateX(-50%);z-index:99999;background:#ef4444;color:#fff;padding:10px 18px;border-radius:8px;font-size:13px;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,.2)';
    bar.textContent = '🔔 新留言 +' + unread + ' 条，点此刷新列表';
    bar.addEventListener('click', function () { window.location.reload(); });
    document.body.appendChild(bar);
  }

  var lastUnread = <?= (int) ($stats['unread'] ?? 0) ?>;

  function syncBadge(n) {
    var b = document.querySelector('.nav-badge[data-badge="/admin/plugin/kefu/chat"]');
    if (!b) return;
    if (n > 0) { b.textContent = n > 99 ? '99+' : n; b.style.display = ''; }
    else { b.style.display = 'none'; }
  }
  document.title = (lastUnread > 0 ? '(' + lastUnread + ') ' : '') + '客服会话 - <?= e(addslashes((string) \setting('site_name', '后台'))) ?>';

  setInterval(function () {
    fetch('/admin/plugin/kefu/api/unread', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d || !d.ok) return;
        if (d.unread > lastUnread) {
          if (on) beep();
          tip(d.unread);
        }
        lastUnread = d.unread;
        syncBadge(d.unread);
        document.title = (d.unread > 0 ? '(' + d.unread + ') ' : '') + '客服会话 - <?= e(addslashes((string) \setting('site_name', '后台'))) ?>';
      })
      .catch(function () {});
  }, 15000);
})();
</script>
