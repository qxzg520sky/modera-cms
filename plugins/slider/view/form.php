<?php $pageTitle = $pageTitle ?? '编辑广告'; include __DIR__ . '/../../../app/view/admin/_header.php'; ?>
<style>
.sl-back{display:inline-block;margin-bottom:12px;font-size:13px;color:#2563eb;text-decoration:none}
.sl-imgwrap{display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap}
.sl-prev{width:340px;height:191px;border-radius:8px;object-fit:cover;background:#f3f4f6;border:1px solid #e5e7eb}
.sl-previnfo{flex:1;min-width:240px}
.sl-previnfo .hint{font-size:12px;color:#6b7280;line-height:1.8;margin-top:8px}
.sl-upload{display:flex;gap:8px;align-items:center;margin-top:10px;flex-wrap:wrap}
.sl-upload input[type=file]{font-size:12px;max-width:260px}
.sl-sec{font-size:13px;font-weight:600;color:#111827;margin:6px 0 12px;padding-top:14px;border-top:1px solid #f0f1f3}
.sl-en{background:#f9fafb;border:1px dashed #e5e7eb;border-radius:8px;padding:14px 16px 2px;margin-bottom:6px}
.sl-en .t{font-size:12px;color:#6b7280;margin-bottom:10px}
</style>

<a class="sl-back" href="/admin/plugin/slider">← 返回广告列表</a>

<div class="card">
  <div class="card-head"><span><?= e($pageTitle) ?></span></div>
  <div class="card-body">
    <form method="post" action="/admin/plugin/slider/save" id="slForm">
      <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">

      <div class="form-row">
        <div class="form-item">
          <label>广告标题 <span class="req">*</span></label>
          <input type="text" name="title" maxlength="120" required value="<?= e($row['title']) ?>" placeholder="例如：精密零件表面处理 · 阳极氧化与硬质氧化">
        </div>
        <div class="form-item">
          <label>小标题（图片上方橙色小字，可空）</label>
          <input type="text" name="subtitle" maxlength="160" value="<?= e($row['subtitle']) ?>" placeholder="例如：产品中心">
        </div>
      </div>

      <div class="form-row">
        <div class="form-item">
          <label>摘要 / 副文案（可空，手机上最多显示两行）</label>
          <textarea name="summary" maxlength="255" style="min-height:90px" placeholder="一句话说清这张广告要推什么，建议 60 字以内"><?= e($row['summary']) ?></textarea>
        </div>
      </div>

      <div class="sl-sec">广告图片</div>
      <div class="sl-imgwrap">
        <img class="sl-prev" id="imgPrev" src="<?= $row['image'] !== '' ? e($row['image']) : 'data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22340%22 height=%22191%22%3E%3Crect width=%22340%22 height=%22191%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%22170%22 y=%22100%22 font-size=%2213%22 fill=%22%239ca3af%22 text-anchor=%22middle%22%3E还没有图片%3C/text%3E%3C/svg%3E' ?>" alt="">
        <div class="sl-previnfo">
          <label style="display:block;margin-bottom:6px;font-size:13px;color:#374151;font-weight:500">图片地址 <span class="req">*</span></label>
          <input type="text" name="image" id="imgUrl" maxlength="255" required value="<?= e($row['image']) ?>" placeholder="点下面「上传图片」自动填入，也可直接粘贴 /uploads/xxx.jpg">
          <div class="sl-upload">
            <input type="file" id="imgFile" accept="image/jpeg,image/png,image/gif,image/webp">
            <button type="button" class="btn btn-sm" id="imgBtn">上传图片</button>
            <span id="imgMsg" style="font-size:12px;color:#6b7280"></span>
          </div>
          <div class="hint">
            建议横图 <strong>1920×1080</strong>（16:9）左右，主体放在中间，手机上不会被裁掉；单张不超过 5MB，支持 jpg / png / gif / webp。
          </div>
        </div>
      </div>

      <div class="sl-sec" style="margin-top:18px">链接与按钮</div>
      <div class="form-row">
        <div class="form-item">
          <label>主链接（可空，留空则不显示主按钮）</label>
          <input type="text" name="link" maxlength="255" value="<?= e($row['link']) ?>" placeholder="/a/12 或 /c/product 或 https://…">
        </div>
        <div class="form-item">
          <label>主按钮文字（默认「查看详情」）</label>
          <input type="text" name="link_text" maxlength="40" value="<?= e($row['link_text']) ?>" placeholder="查看详情">
        </div>
        <div class="form-item">
          <label>打开方式</label>
          <select name="target">
            <option value="_self" <?= $row['target'] !== '_blank' ? 'selected' : '' ?>>当前窗口</option>
            <option value="_blank" <?= $row['target'] === '_blank' ? 'selected' : '' ?>>新窗口</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-item">
          <label>次链接（可空，留空则不显示第二个按钮）</label>
          <input type="text" name="link2" maxlength="255" value="<?= e($row['link2']) ?>" placeholder="/c/solution">
        </div>
        <div class="form-item">
          <label>次按钮文字（默认「更多栏目」）</label>
          <input type="text" name="link2_text" maxlength="40" value="<?= e($row['link2_text']) ?>" placeholder="更多栏目">
        </div>
      </div>

      <div class="sl-sec" style="margin-top:18px">排序与状态</div>
      <div class="form-row">
        <div class="form-item">
          <label>排序（数字小的先播）</label>
          <input type="number" name="sort" value="<?= (int) $row['sort'] ?>" placeholder="1">
        </div>
        <div class="form-item">
          <label>状态</label>
          <select name="status">
            <option value="1" <?= (int) $row['status'] === 1 ? 'selected' : '' ?>>启用（前台显示）</option>
            <option value="0" <?= (int) $row['status'] === 0 ? 'selected' : '' ?>>停用（只留后台）</option>
          </select>
        </div>
      </div>

      <div class="sl-sec" style="margin-top:18px">英文站文案（可选，留空则英文站沿用中文）</div>
      <div class="sl-en">
        <div class="t">站点启用多语言时，英文站（/en/）会优先用这里的文案；不填就显示上面的中文。</div>
        <div class="form-row">
          <div class="form-item">
            <label>Title</label>
            <input type="text" name="title_en" maxlength="160" value="<?= e($row['title_en']) ?>">
          </div>
          <div class="form-item">
            <label>Eyebrow</label>
            <input type="text" name="subtitle_en" maxlength="160" value="<?= e($row['subtitle_en']) ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="form-item">
            <label>Summary</label>
            <textarea name="summary_en" maxlength="255" style="min-height:80px"><?= e($row['summary_en']) ?></textarea>
          </div>
        </div>
        <div class="form-row">
          <div class="form-item">
            <label>Button 1</label>
            <input type="text" name="link_text_en" maxlength="40" value="<?= e($row['link_text_en']) ?>">
          </div>
          <div class="form-item">
            <label>Button 2</label>
            <input type="text" name="link2_text_en" maxlength="40" value="<?= e($row['link2_text_en']) ?>">
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px;align-items:center;margin-top:8px">
        <button class="btn btn-primary" type="submit">保存广告</button>
        <a class="btn" href="/admin/plugin/slider">取消</a>
        <span style="font-size:12px;color:#6b7280">保存后刷新首页即可看到（前台轮播 5 秒左右自动切换）</span>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  var url = document.getElementById('imgUrl');
  var prev = document.getElementById('imgPrev');
  var file = document.getElementById('imgFile');
  var btn = document.getElementById('imgBtn');
  var msg = document.getElementById('imgMsg');

  function setPrev(v) {
    if (v) { prev.src = v; }
  }
  url.addEventListener('input', function () { setPrev(url.value.trim()); });

  function upload() {
    if (!file.files || !file.files[0]) {
      msg.textContent = '先选择一个图片文件';
      return;
    }
    var fd = new FormData();
    fd.append('file', file.files[0]);
    btn.disabled = true;
    msg.textContent = '正在上传…';
    fetch('/admin/upload', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        btn.disabled = false;
        if (j && j.errno === 0 && j.data && j.data.url) {
          url.value = j.data.url;
          setPrev(j.data.url);
          msg.textContent = '上传成功，已填入图片地址';
        } else {
          msg.textContent = (j && j.message) || '上传失败';
        }
      })
      .catch(function () { btn.disabled = false; msg.textContent = '上传失败，请重试'; });
  }

  btn.addEventListener('click', upload);
  file.addEventListener('change', function () {
    if (file.files && file.files[0]) { upload(); }
  });
})();
</script>

<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>
