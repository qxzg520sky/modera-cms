<?php $pageTitle = $pageTitle ?? '栏目'; include __DIR__ . '/../_header.php'; ?>
<?php $r = $row ?? []; ?>

<form method="post" action="/admin/category/save">
<input type="hidden" name="id" value="<?= (int) ($r['id'] ?? 0) ?>">
<div class="card">
  <div class="card-head"><span>栏目信息</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item">
        <label>名称 <span class="req">*</span></label>
        <input type="text" name="name" value="<?= e($r['name'] ?? '') ?>" required>
      </div>
      <div class="form-item">
        <label>别名 slug <span class="req">*</span></label>
        <input type="text" name="slug" value="<?= e($r['slug'] ?? '') ?>" required placeholder="如 news，前台地址 /c/news">
      </div>
    </div>
    <div class="form-row">
      <div class="form-item">
        <label>上级栏目</label>
        <select name="pid">
          <option value="0">顶级栏目</option>
          <?php foreach ($all as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) ($r['pid'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-item">
        <label>栏目类型</label>
        <select name="type">
          <option value="list" <?= ($r['type'] ?? 'list') === 'list' ? 'selected' : '' ?>>文章列表</option>
          <option value="page" <?= ($r['type'] ?? '') === 'page' ? 'selected' : '' ?>>单页</option>
          <option value="link" <?= ($r['type'] ?? '') === 'link' ? 'selected' : '' ?>>外部链接</option>
        </select>
      </div>
      <div class="form-item">
        <label>外部链接（type=link 时生效）</label>
        <input type="text" name="link" value="<?= e($r['link'] ?? '') ?>" placeholder="https://">
      </div>
    </div>
    <div class="form-row">
      <div class="form-item">
        <label>排序</label>
        <input type="number" name="sort" value="<?= (int) ($r['sort'] ?? 0) ?>">
      </div>
      <div class="form-item">
        <label>状态</label>
        <select name="status">
          <option value="1" <?= (int) ($r['status'] ?? 1) === 1 ? 'selected' : '' ?>>显示</option>
          <option value="0" <?= (int) ($r['status'] ?? 1) === 0 ? 'selected' : '' ?>>隐藏</option>
        </select>
      </div>
      <div class="form-item">
        <label>模板文件（留空用默认）</label>
        <input type="text" name="template" value="<?= e($r['template'] ?? '') ?>" placeholder="list">
      </div>
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
  <a class="btn" href="/admin/category">返回列表</a>
</div>
</form>

<?php include __DIR__ . '/../_footer.php'; ?>
