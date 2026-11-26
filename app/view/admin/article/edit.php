<?php $pageTitle = $pageTitle ?? '文章'; include __DIR__ . '/../_header.php'; ?>
<?php $r = $row ?? []; ?>

<form method="post" action="/admin/article/save">
<input type="hidden" name="id" value="<?= (int) ($r['id'] ?? 0) ?>">

<div class="card">
  <div class="card-head"><span>基本信息</span><span class="muted" style="font-weight:400;font-size:12px">内容支持 HTML</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item" style="flex:2">
        <label>标题 <span class="req">*</span></label>
        <input type="text" name="title" value="<?= e($r['title'] ?? '') ?>" required placeholder="文章标题">
      </div>
      <div class="form-item">
        <label>所属栏目</label>
        <select name="category_id">
          <option value="0">未分类</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) ($r['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-item">
        <label>别名 slug</label>
        <input type="text" name="slug" value="<?= e($r['slug'] ?? '') ?>" placeholder="可选，英文/数字，用于友好链接">
      </div>
      <div class="form-item">
        <label>作者</label>
        <input type="text" name="author" value="<?= e($r['author'] ?? ($admin['username'] ?? '')) ?>">
      </div>
      <div class="form-item">
        <label>封面图片地址</label>
        <input type="text" name="cover" value="<?= e($r['cover'] ?? '') ?>" placeholder="/uploads/xxx.jpg">
      </div>
    </div>

    <div class="form-row">
      <div class="form-item">
        <label>摘要</label>
        <textarea name="summary" style="min-height:70px" placeholder="列表页显示，可留空自动截取"><?= e($r['summary'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="form-row">
      <div class="form-item">
        <label>正文内容</label>
        <?= rich_editor('content', (string) ($r['content'] ?? ''), '520px') ?>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>发布设置与 SEO</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item">
        <label>状态</label>
        <select name="status">
          <option value="1" <?= (int) ($r['status'] ?? 1) === 1 ? 'selected' : '' ?>>已发布</option>
          <option value="0" <?= (int) ($r['status'] ?? 1) === 0 ? 'selected' : '' ?>>草稿</option>
        </select>
      </div>
      <div class="form-item">
        <label>置顶</label>
        <select name="is_top">
          <option value="0" <?= (int) ($r['is_top'] ?? 0) === 0 ? 'selected' : '' ?>>否</option>
          <option value="1" <?= (int) ($r['is_top'] ?? 0) === 1 ? 'selected' : '' ?>>是</option>
        </select>
      </div>
      <div class="form-item">
        <label>排序值</label>
        <input type="number" name="sort" value="<?= (int) ($r['sort'] ?? 0) ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-item">
        <label>SEO 标题</label>
        <input type="text" name="seo_title" value="<?= e($r['seo_title'] ?? '') ?>">
      </div>
      <div class="form-item">
        <label>SEO 关键词</label>
        <input type="text" name="seo_keywords" value="<?= e($r['seo_keywords'] ?? '') ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-item">
        <label>SEO 描述</label>
        <input type="text" name="seo_description" value="<?= e($r['seo_description'] ?? '') ?>">
      </div>
    </div>
  </div>
</div>

<div style="display:flex;gap:10px">
  <button type="submit" class="btn btn-primary">保存</button>
  <a class="btn" href="/admin/article">返回列表</a>
</div>
</form>

<?php include __DIR__ . '/../_footer.php'; ?>
