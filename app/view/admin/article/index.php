<?php $pageTitle = '文章管理'; include __DIR__ . '/../_header.php'; ?>

<div class="toolbar">
  <form method="get" action="/admin/article" style="display:flex;gap:8px;flex-wrap:wrap">
    <input type="text" name="kw" value="<?= e($kw) ?>" placeholder="搜索标题" style="width:200px">
    <select name="cat" style="width:150px">
      <option value="0">全部栏目</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= (int) $c['id'] ?>" <?= $catId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn" type="submit">搜索</button>
  </form>
  <a class="btn btn-primary" href="/admin/article/edit">＋ 发布文章</a>
</div>

<div class="card">
  <?php if (empty($rows)): ?>
    <div class="empty">暂无文章</div>
  <?php else: ?>
  <table class="table">
    <thead>
      <tr><th style="width:60px">ID</th><th>标题</th><th style="width:120px">栏目</th><th style="width:90px">状态</th><th style="width:140px">更新时间</th><th style="width:140px">操作</th></tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td class="muted"><?= (int) $row['id'] ?></td>
        <td>
          <?php if ((int) $row['is_top'] === 1): ?><span class="badge">置顶</span> <?php endif; ?>
          <?= e($row['title']) ?>
        </td>
        <td class="muted"><?= e($row['category_name']) ?></td>
        <td><?= ((int) $row['status'] === 1) ? '<span class="badge badge-ok">已发布</span>' : '<span class="badge badge-gray">草稿</span>' ?></td>
        <td class="muted"><?= e($row['update_time'] ?? $row['create_time'] ?? '') ?></td>
        <td class="actions">
          <a class="btn-ghost" href="/admin/article/edit/<?= (int) $row['id'] ?>">编辑</a>
          <form method="post" action="/admin/article/delete" data-confirm="确定删除《<?= e($row['title']) ?>》？" style="display:inline">
            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            <button type="submit" class="btn-ghost" style="color:#dc2626">删除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?= $pager ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>
