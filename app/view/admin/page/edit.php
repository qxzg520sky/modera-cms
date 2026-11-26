<?php $pageTitle = $pageTitle ?? '单页'; include __DIR__ . '/../_header.php'; ?>
<?php $r = $row ?? []; ?>

<form method="post" action="/admin/page/save">
<input type="hidden" name="id" value="<?= (int) ($r['id'] ?? 0) ?>">
<div class="card">
  <div class="card-head"><span>单页内容</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item" style="flex:2"><label>标题 <span class="req">*</span></label><input type="text" name="title" value="<?= e($r['title'] ?? '') ?>" required></div>
      <div class="form-item"><label>别名 slug <span class="req">*</span></label><input type="text" name="slug" value="<?= e($r['slug'] ?? '') ?>" required placeholder="about"></div>
    </div>
    <div class="form-row">
      <div class="form-item"><label>正文内容</label><?= rich_editor('content', (string) ($r['content'] ?? ''), '520px') ?></div>
    </div>
    <div class="form-row">
      <div class="form-item"><label>状态</label>
        <select name="status">
          <option value="1" <?= (int) ($r['status'] ?? 1) === 1 ? 'selected' : '' ?>>显示</option>
          <option value="0" <?= (int) ($r['status'] ?? 1) === 0 ? 'selected' : '' ?>>隐藏</option>
        </select>
      </div>
      <div class="form-item"><label>模板文件（留空用 page）</label><input type="text" name="template" value="<?= e($r['template'] ?? '') ?>"></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>SEO</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item"><label>SEO 标题</label><input type="text" name="seo_title" value="<?= e($r['seo_title'] ?? '') ?>"></div>
      <div class="form-item"><label>SEO 关键词</label><input type="text" name="seo_keywords" value="<?= e($r['seo_keywords'] ?? '') ?>"></div>
    </div>
    <div class="form-row"><div class="form-item"><label>SEO 描述</label><input type="text" name="seo_description" value="<?= e($r['seo_description'] ?? '') ?>"></div></div>
  </div>
</div>

<div style="display:flex;gap:10px">
  <button class="btn btn-primary" type="submit">保存</button>
  <a class="btn" href="/admin/page">返回列表</a>
</div>
</form>

<?php include __DIR__ . '/../_footer.php'; ?>
