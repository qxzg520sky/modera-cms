<?php $pageTitle = $pageTitle ?? '坐席'; include __DIR__ . '/../../../app/view/admin/_header.php'; ?>
<?php $r = $row ?? []; ?>

<form method="post" action="/admin/plugin/kefu/staff/save">
<input type="hidden" name="id" value="<?= (int) ($r['id'] ?? 0) ?>">

<div class="card">
  <div class="card-head"><span>坐席信息</span><span class="muted" style="font-weight:400;font-size:12px">显示在前台浮窗里</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item">
        <label>名称 <span class="req">*</span></label>
        <input type="text" name="name" value="<?= e($r['name'] ?? '') ?>" required placeholder="例如：客服小鼎">
      </div>
      <div class="form-item">
        <label>岗位 / 一句话介绍</label>
        <input type="text" name="title" value="<?= e($r['title'] ?? '') ?>" placeholder="例如：售前咨询 · 产品选型">
      </div>
    </div>

    <div class="form-row">
      <div class="form-item">
        <label>联系方式类型</label>
        <select name="type" id="kfType">
          <?php foreach ($types as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= ($r['type'] ?? 'online') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-item" style="flex:2">
        <label>联系方式内容</label>
        <input type="text" name="account" id="kfAccount" value="<?= e($r['account'] ?? '') ?>" placeholder="电话写 0937-2866888；邮箱写邮箱地址；链接写完整地址；微信写微信号">
        <div class="muted" style="font-size:12px;margin-top:6px">类型选「在线客服」时可以不填，访客直接进对话</div>
      </div>
    </div>

    <div class="form-row">
      <div class="form-item">
        <label>补充说明（小字）</label>
        <input type="text" name="intro" value="<?= e($r['intro'] ?? '') ?>" placeholder="例如：工作日 08:30-18:00 在线">
      </div>
      <div class="form-item">
        <label>排序值</label>
        <input type="number" name="sort" value="<?= (int) ($r['sort'] ?? 0) ?>">
      </div>
      <div class="form-item">
        <label>状态</label>
        <select name="status">
          <option value="1" <?= (int) ($r['status'] ?? 1) === 1 ? 'selected' : '' ?>>显示</option>
          <option value="0" <?= (int) ($r['status'] ?? 1) === 0 ? 'selected' : '' ?>>隐藏</option>
        </select>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>头像与二维码（可选）</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item">
        <label>头像地址</label>
        <div style="display:flex;gap:8px">
          <input type="text" name="avatar" id="kfAvatar" value="<?= e($r['avatar'] ?? '') ?>" placeholder="/uploads/xxx.jpg，不填就用首字头像">
          <button type="button" class="btn btn-sm" data-upload="kfAvatar">上传</button>
        </div>
      </div>
      <div class="form-item">
        <label>二维码图片地址</label>
        <div style="display:flex;gap:8px">
          <input type="text" name="qr" id="kfQr" value="<?= e($r['qr'] ?? '') ?>" placeholder="/uploads/xxx.jpg，微信 / 企微建议放二维码">
          <button type="button" class="btn btn-sm" data-upload="kfQr">上传</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div style="display:flex;gap:10px">
  <button type="submit" class="btn btn-primary">保存</button>
  <a class="btn" href="/admin/plugin/kefu">返回坐席列表</a>
</div>
</form>

<input type="file" id="kfFile" accept="image/*" style="display:none">
<script>
(function () {
  var file = document.getElementById('kfFile');
  var target = null;
  document.querySelectorAll('[data-upload]').forEach(function (btn) {
    btn.addEventListener('click', function () { target = document.getElementById(btn.dataset.upload); file.click(); });
  });
  file.addEventListener('change', function () {
    if (!file.files || !file.files[0] || !target) return;
    var fd = new FormData(); fd.append('file', file.files[0]);
    fetch('/admin/upload', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (j && j.errno === 0 && j.data && j.data.url) { target.value = j.data.url; }
        else { alert((j && j.message) || '上传失败'); }
      })
      .catch(function () { alert('上传失败'); })
      .finally(function () { file.value = ''; });
  });
})();
</script>

<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>
