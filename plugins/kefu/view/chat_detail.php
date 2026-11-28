<?php $pageTitle = $pageTitle ?? '会话详情'; include __DIR__ . '/../../../app/view/admin/_header.php'; ?>
<?php
$statusText = [0 => '待回复', 1 => '已回复', 2 => '已处理'];
$statusCls  = [0 => 'background:#fef3c7;color:#b45309', 1 => 'background:#f0fdf4;color:#15803d', 2 => 'background:#f3f4f6;color:#6b7280'];
?>
<style>
.kf-wrap{display:grid;grid-template-columns:280px 1fr;gap:16px;align-items:start}
.kf-side{position:sticky;top:16px}
.kf-side .row{display:flex;justify-content:space-between;font-size:13px;padding:8px 0;border-bottom:1px dashed #f1f5f9;gap:10px}
.kf-side .row:last-child{border-bottom:0}
.kf-side .k{color:#6b7280;flex:none}
.kf-side .v{color:#111827;text-align:right;word-break:break-all}
.kf-chat{max-height:calc(100vh - 420px);min-height:280px;overflow-y:auto;padding:18px;background:#f8fafc;border-radius:8px}
.kf-msg{display:flex;gap:8px;margin-bottom:14px}
.kf-msg .b{max-width:78%;padding:9px 13px;border-radius:12px;font-size:13px;line-height:1.7;white-space:pre-wrap;word-break:break-word}
.kf-msg .t{font-size:11px;color:#9ca3af;align-self:flex-end;flex:none}
.kf-msg.visitor .b{background:#fff;border:1px solid #e5e7eb;color:#111827;border-top-left-radius:4px}
.kf-msg.admin{flex-direction:row-reverse}
.kf-msg.admin .b{background:#2563eb;color:#fff;border-top-right-radius:4px}
.kf-msg.system{justify-content:center}
.kf-msg.system .b{background:#eef2ff;color:#3730a3;font-size:12px;max-width:86%}
.kf-reply{margin-top:16px}
.kf-quick{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px}
.kf-quick button{font-size:12px;padding:5px 10px;border:1px solid #e5e7eb;background:#fff;border-radius:99px;cursor:pointer;color:#374151}
.kf-quick button:hover{background:#f9fafb}
</style>

<div class="kf-wrap">
  <div class="card kf-side">
    <div class="card-head"><span>访客信息</span></div>
    <div class="card-body" style="padding:14px 18px">
      <div class="row"><span class="k">会话号</span><span class="v">#<?= (int) $row['id'] ?></span></div>
      <div class="row"><span class="k">访客</span><span class="v"><?= e($row['name']) ?></span></div>
      <div class="row"><span class="k">联系方式</span><span class="v"><?= $row['contact'] !== '' ? e($row['contact']) : '<span class="muted">未留</span>' ?></span></div>
      <div class="row"><span class="k">状态</span><span class="v"><span class="badge" style="<?= $statusCls[(int) $row['status']] ?? '' ?>"><?= e($statusText[(int) $row['status']] ?? '') ?></span></span></div>
      <div class="row"><span class="k">来源页</span><span class="v"><?= e($row['page']) ?: '-' ?></span></div>
      <div class="row"><span class="k">IP</span><span class="v"><?= e($row['ip']) ?></span></div>
      <div class="row"><span class="k">首次</span><span class="v"><?= date('Y-m-d H:i', (int) $row['created_at']) ?></span></div>
      <div class="row"><span class="k">最后</span><span class="v"><?= date('Y-m-d H:i', (int) $row['updated_at']) ?></span></div>
      <div class="row"><span class="k">浏览器</span><span class="v muted" style="font-size:12px"><?= e(mb_substr((string) $row['ua'], 0, 60)) ?></span></div>
    </div>
    <div class="card-body" style="border-top:1px solid #f1f5f9;display:flex;gap:8px;padding:12px 18px">
      <form method="post" action="/admin/plugin/kefu/chat/close" style="flex:1" onsubmit="return confirm('标记为已处理？')">
        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
        <button class="btn btn-sm" type="submit" style="width:100%">标记已处理</button>
      </form>
      <form method="post" action="/admin/plugin/kefu/chat/delete" style="flex:1" onsubmit="return confirm('删除这条会话？')">
        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
        <button class="btn btn-sm btn-danger" type="submit" style="width:100%">删除</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><span>对话记录（<?= count($msgs) ?> 条）</span><a class="btn btn-ghost btn-sm" href="/admin/plugin/kefu/chat">← 返回列表</a></div>
    <div class="card-body">
      <div class="kf-chat" id="kfChat">
        <?php if (!$msgs): ?>
          <div class="empty">这个会话还没有消息</div>
        <?php endif; ?>
        <?php foreach ($msgs as $m): ?>
          <div class="kf-msg <?= e($m['role']) ?>">
            <div class="b"><?= e(strip_tags((string) $m['content'])) ?></div>
            <div class="t"><?= date('m-d H:i', (int) $m['created_at']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="kf-reply">
        <div class="kf-quick">
          <button type="button" data-tpl="您好，收到您的留言。您方便说一下设备型号和现场工况吗？我们好给您一个准确的方案。">问设备型号</button>
          <button type="button" data-tpl="报价需要几个信息：加工对象、精度要求、单班产量。您提供后我们当天出初步报价。">要报价</button>
          <button type="button" data-tpl="这个问题我转给技术支持，稍后会有工程师直接联系您，请保持电话畅通。">转技术支持</button>
          <button type="button" data-tpl="感谢您的咨询，资料已经发到您的联系方式里了，有问题随时回复这条消息。">已发送资料</button>
        </div>
        <form method="post" action="/admin/plugin/kefu/chat/reply">
          <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
          <textarea name="content" id="kfReply" style="min-height:110px" placeholder="在这里回复访客，支持换行。访客打开面板或停留页面时会自动收到新消息" required></textarea>
          <div style="display:flex;gap:10px;margin-top:12px">
            <button class="btn btn-primary" type="submit">发送回复</button>
            <span class="muted" style="font-size:12px;align-self:center">访客端默认 <?= (int) ((\plugins\kefu\service\Kefu::cfg()['poll']) ?: 5) ?> 秒轮询一次新消息</span>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var chat = document.getElementById('kfChat');
  if (chat) chat.scrollTop = chat.scrollHeight;

  document.querySelectorAll('.kf-quick button').forEach(function (b) {
    b.addEventListener('click', function () {
      var ta = document.getElementById('kfReply');
      ta.value = b.dataset.tpl;
      ta.focus();
    });
  });

  /* ---- 客服开着这个页面时，访客新消息自动冒出来 ---- */
  var SID = <?= (int) $row['id'] ?>;
  var lastId = <?= (int) array_reduce($msgs, static function ($c, $m) { return max((int) $c, (int) $m['id']); }, 0) ?>;
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

  function addMsg(m) {
    if (!chat) return;
    var empty = chat.querySelector('.empty');
    if (empty) empty.remove();
    var d = document.createElement('div');
    d.className = 'kf-msg ' + (m.role === 'admin' ? 'admin' : m.role === 'system' ? 'system' : 'visitor');
    var b = document.createElement('div');
    b.className = 'b';
    b.textContent = m.content;
    var t = document.createElement('div');
    t.className = 't';
    t.textContent = m.time;
    d.appendChild(b);
    d.appendChild(t);
    chat.appendChild(d);
  }

  setInterval(function () {
    if (document.hidden) return;
    fetch('/admin/plugin/kefu/api/messages?id=' + SID + '&after=' + lastId, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d || !d.ok || !d.messages || !d.messages.length) return;
        d.messages.forEach(function (m) {
          addMsg(m);
          if (m.id > lastId) lastId = m.id;
        });
        chat.scrollTop = chat.scrollHeight;
        if (on) beep();
      })
      .catch(function () {});
  }, 10000);
})();
</script>

<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>
