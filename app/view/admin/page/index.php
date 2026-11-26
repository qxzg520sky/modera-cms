<?php $pageTitle = '单页管理'; include __DIR__ . '/../_header.php'; ?>

<div class="toolbar">
  <span class="muted">单页适合“关于我们 / 联系我们 / 服务条款”等固定页面，前台地址 /p/{slug}</span>
  <a class="btn btn-primary" href="/admin/page/edit">＋ 新建单页</a>
</div>

<div class="card">
  <?php if (empty($rows)): ?>
    <div class="empty">还没有单页</div>
  <?php else: ?>
  <table class="table">
    <thead><tr><th style="width:60px">ID</th><th>标题</th><th style="width:160px">别名</th><th style="width:90px">状态</th><th style="width:150px">更新时间</th><th style="width:140px">操作</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td class="muted"><?= (int) $row['id'] ?></td>
        <td><?= e($row['title']) ?></td>
        <td class="muted"><?= e($row['slug']) ?></td>
        <td><?= ((int) $row['status'] === 1) ? '<span class="badge badge-ok">显示</span>' : '<span class="badge badge-gray">隐藏</span>' ?></td>
        <td class="muted"><?= e($row['update_time'] ?? $row['create_time'] ?? '') ?></td>
        <td class="actions">
          <a class="btn-ghost" href="/page/<?= e($row['slug']) ?>" target="_blank">预览</a>
          <a class="btn-ghost" href="/admin/page/edit/<?= (int) $row['id'] ?>">编辑</a>
          <form method="post" action="/admin/page/delete" data-confirm="确定删除这个单页？" style="display:inline">
            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            <button class="btn-ghost" type="submit" style="color:#dc2626">删除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>
