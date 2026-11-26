<?php $pageTitle = '栏目管理'; include __DIR__ . '/../_header.php'; ?>
<?php
$byPid = [];
foreach ($rows as $row) {
    $byPid[(int) $row['pid']][] = $row;
}
$render = function (int $pid, int $level, array $byPid, callable $render): void {
    if (empty($byPid[$pid])) {
        return;
    }
    foreach ($byPid[$pid] as $row) {
        ?>
        <tr>
          <td class="muted"><?= (int) $row['id'] ?></td>
          <td><?= str_repeat('&nbsp;&nbsp;&nbsp;', $level) ?><?= $level > 0 ? '└ ' : '' ?><?= e($row['name']) ?></td>
          <td class="muted"><?= e($row['slug']) ?></td>
          <td><span class="badge badge-gray"><?= e($row['type']) ?></span></td>
          <td class="muted"><?= (int) $row['sort'] ?></td>
          <td><?= ((int) $row['status'] === 1) ? '<span class="badge badge-ok">显示</span>' : '<span class="badge badge-gray">隐藏</span>' ?></td>
          <td class="actions">
            <a class="btn-ghost" href="/admin/category/edit/<?= (int) $row['id'] ?>">编辑</a>
            <form method="post" action="/admin/category/delete" data-confirm="删除栏目会同时解除其文章归属，确定？" style="display:inline">
              <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
              <button class="btn-ghost" type="submit" style="color:#dc2626">删除</button>
            </form>
          </td>
        </tr>
        <?php
        $render((int) $row['id'], $level + 1, $byPid, $render);
    }
};
?>

<div class="toolbar">
  <span class="muted">栏目决定前台导航结构，slug 对应前台 /c/{slug}</span>
  <a class="btn btn-primary" href="/admin/category/edit">＋ 新建栏目</a>
</div>

<div class="card">
  <?php if (empty($rows)): ?>
    <div class="empty">还没有栏目</div>
  <?php else: ?>
  <table class="table">
    <thead><tr><th style="width:60px">ID</th><th>名称</th><th style="width:160px">别名</th><th style="width:90px">类型</th><th style="width:70px">排序</th><th style="width:80px">状态</th><th style="width:140px">操作</th></tr></thead>
    <tbody><?php $render(0, 0, $byPid, $render); ?></tbody>
  </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>
