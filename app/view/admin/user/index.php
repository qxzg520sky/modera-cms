<?php $pageTitle = '管理员'; include __DIR__ . '/../_header.php'; ?>

<div class="toolbar">
  <span class="muted">建议至少保留两个管理员账号</span>
  <a class="btn btn-primary" href="/admin/user/edit">＋ 新增管理员</a>
</div>

<div class="card">
  <table class="table">
    <thead><tr><th style="width:60px">ID</th><th>账号</th><th>昵称</th><th style="width:90px">状态</th><th style="width:170px">最后登录</th><th style="width:140px">操作</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td class="muted"><?= (int) $row['id'] ?></td>
        <td><?= e($row['username']) ?><?= (int) $row['id'] === (int) ($admin['id'] ?? 0) ? ' <span class="badge">当前登录</span>' : '' ?></td>
        <td class="muted"><?= e($row['nickname']) ?></td>
        <td><?= ((int) $row['status'] === 1) ? '<span class="badge badge-ok">正常</span>' : '<span class="badge badge-gray">禁用</span>' ?></td>
        <td class="muted"><?= e($row['last_login_time'] ?? '—') ?></td>
        <td class="actions">
          <a class="btn-ghost" href="/admin/user/edit/<?= (int) $row['id'] ?>">编辑</a>
          <?php if ((int) $row['id'] !== (int) ($admin['id'] ?? 0)): ?>
          <form method="post" action="/admin/user/delete" data-confirm="确定删除该管理员？" style="display:inline">
            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            <button class="btn-ghost" type="submit" style="color:#dc2626">删除</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>
