<?php $pageTitle = $pageTitle ?? '业务员'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/member/asset/admin.css?v=1.0.0">

<div class="card">
  <div class="card-head">
    <span>业务员 <span class="muted">独立登录入口：/member/staff/login</span></span>
    <a class="btn btn-primary btn-sm" href="/admin/plugin/member/staff/edit">+ 新增业务员</a>
  </div>
  <table class="table">
    <thead><tr><th>ID</th><th>账号</th><th>姓名</th><th>手机号</th><th>名下会员</th><th>状态</th><th>备注</th><th>操作</th></tr></thead>
    <tbody>
    <?php if (!$staffs): ?>
      <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:32px">还没有业务员，新增后即可分配会员</td></tr>
    <?php else: foreach ($staffs as $s): ?>
      <tr>
        <td><?= (int) $s['id'] ?></td>
        <td><strong><?= e($s['username']) ?></strong></td>
        <td><?= e($s['real_name']) ?></td>
        <td><?= e($s['mobile'] ?: '-') ?></td>
        <td><?= (int) $s['member_count'] ?></td>
        <td><?= (int) $s['status'] === 1 ? '<span class="mb-admin-tag ok">启用</span>' : '<span class="mb-admin-tag gray">停用</span>' ?></td>
        <td style="font-size:12px;color:#64748b"><?= e($s['remark'] ?: '-') ?></td>
        <td>
          <a class="btn btn-sm" href="/admin/plugin/member/staff/edit/<?= (int) $s['id'] ?>">编辑</a>
          <form method="post" action="/admin/plugin/member/staff/delete" style="display:inline" data-confirm="删除业务员后，其名下会员会变为未分配，确定？">
            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <button class="btn btn-sm" type="submit">删除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<div class="mb-admin-note" style="margin-top:16px">
  业务员只能查看自己名下会员，不能跨业务员查询；如需调整归属，可在会员编辑页修改“归属业务员”。
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>

