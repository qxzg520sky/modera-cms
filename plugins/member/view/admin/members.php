<?php $pageTitle = $pageTitle ?? '会员列表'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/member/asset/admin.css?v=1.0.0">

<div class="card">
  <div class="card-head">
    <span>会员列表 <span class="muted">共 <?= (int) $res['total'] ?> 人</span></span>
    <span style="display:flex;gap:8px">
      <a class="btn btn-sm" href="/admin/plugin/member/import">导入 / 导出</a>
      <a class="btn btn-primary btn-sm" href="/admin/plugin/member/member/edit">+ 新增会员</a>
    </span>
  </div>
  <form class="mb-admin-toolbar" method="get" action="/admin/plugin/member/members">
    <input name="keyword" value="<?= e($f['keyword']) ?>" placeholder="姓名 / 手机号 / 会员编号 / 公司" style="width:240px">
    <select name="level_id">
      <option value="0">全部等级</option>
      <?php foreach ($levels as $lv): ?><option value="<?= (int) $lv['id'] ?>" <?= (int) $f['level_id'] === (int) $lv['id'] ? 'selected' : '' ?>><?= e($lv['name']) ?></option><?php endforeach; ?>
    </select>
    <select name="staff_id">
      <option value="">全部业务员</option>
      <?php foreach ($staffs as $s): ?><option value="<?= (int) $s['id'] ?>" <?= (string) $f['staff_id'] === (string) $s['id'] ? 'selected' : '' ?>><?= e($s['real_name'] ?: $s['username']) ?></option><?php endforeach; ?>
    </select>
    <select name="status">
      <option value="">全部状态</option>
      <option value="1" <?= (string) $f['status'] === '1' ? 'selected' : '' ?>>正常</option>
      <option value="0" <?= (string) $f['status'] === '0' ? 'selected' : '' ?>>停用</option>
    </select>
    <select name="assigned">
      <option value="">全部分配状态</option>
      <option value="1" <?= !empty($f['assigned']) ? 'selected' : '' ?>>已分配业务员</option>
      <option value="0" <?= !empty($f['unassigned']) ? 'selected' : '' ?>>未分配业务员</option>
    </select>
    <button class="btn btn-sm btn-primary" type="submit">筛选</button>
    <a class="btn btn-sm" href="/admin/plugin/member/members">重置</a>
    <div class="sp"></div>
    <a class="btn btn-sm" href="/admin/plugin/member/export">导出全部 Excel</a>
  </form>

  <table class="table">
    <thead><tr><th>ID</th><th>会员</th><th>等级</th><th>业务员</th><th>余额</th><th>积分</th><th>累计消费</th><th>状态</th><th>注册时间</th><th>操作</th></tr></thead>
    <tbody>
    <?php if (!$res['list']): ?>
      <tr><td colspan="10" style="text-align:center;color:#94a3b8;padding:34px">没有符合条件的会员</td></tr>
    <?php else: foreach ($res['list'] as $m): ?>
      <tr>
        <td><?= (int) $m['id'] ?></td>
        <td><strong><?= e($m['real_name'] ?: $m['username']) ?></strong><div class="muted"><?= e($m['member_no']) ?> · <?= e($m['mobile']) ?></div></td>
        <td><span class="mb-admin-tag"><?= e($m['level_name']) ?></span></td>
        <td><?= $m['staff_name'] !== '' ? e($m['staff_name']) : '<span style="color:#b45309">未分配</span>' ?></td>
        <td>￥<?= number_format((float) $m['balance'], 2) ?></td>
        <td><?= (int) $m['points'] ?></td>
        <td>￥<?= number_format((float) $m['total_spent'], 2) ?></td>
        <td><?= (int) $m['status'] === 1 ? '<span class="mb-admin-tag ok">正常</span>' : '<span class="mb-admin-tag gray">停用</span>' ?></td>
        <td style="font-size:12px;color:#64748b"><?= $m['created_at'] ? date('Y-m-d', (int) $m['created_at']) : '-' ?></td>
        <td><a class="btn btn-sm" href="/admin/plugin/member/member/view/<?= (int) $m['id'] ?>">详情</a> <a class="btn btn-sm" href="/admin/plugin/member/member/edit/<?= (int) $m['id'] ?>">编辑</a></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
  <div class="mb-admin-pager">
    <?php echo \plugins\member\service\Member::pager($res['page'], $res['pages'], '/admin/plugin/member/members?' . http_build_query(array_filter([
      'keyword' => $f['keyword'], 'level_id' => $f['level_id'] ?: '', 'staff_id' => $f['staff_id'],
      'status' => $f['status'], 'assigned' => !empty($f['assigned']) ? '1' : (!empty($f['unassigned']) ? '0' : ''),
    ], function ($v) { return $v !== '' && $v !== null; })) . '&'); ?>
  </div>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>

