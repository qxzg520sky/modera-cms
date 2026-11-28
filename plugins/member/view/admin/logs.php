<?php $pageTitle = $pageTitle ?? '资产日志'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/member/asset/admin.css?v=1.0.0">

<div class="card">
  <div class="mb-admin-tabs">
    <a class="<?= $kind === 'balance' ? 'on' : '' ?>" href="/admin/plugin/member/logs?kind=balance">余额日志</a>
    <a class="<?= $kind === 'points' ? 'on' : '' ?>" href="/admin/plugin/member/logs?kind=points">积分日志</a>
  </div>
  <form class="mb-admin-toolbar" method="get" action="/admin/plugin/member/logs">
    <input type="hidden" name="kind" value="<?= e($kind) ?>">
    <input name="keyword" value="<?= e($f['keyword']) ?>" placeholder="会员姓名 / 手机号 / 编号" style="width:230px">
    <select name="type">
      <option value="">全部类型</option>
      <?php foreach ($types as $k => $v): ?><option value="<?= e($k) ?>" <?= $f['type'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn-sm btn-primary" type="submit">筛选</button>
    <a class="btn btn-sm" href="/admin/plugin/member/logs?kind=<?= e($kind) ?>">重置</a>
    <div class="sp"></div>
    <span class="muted" style="font-size:12px">共 <?= (int) $res['total'] ?> 条</span>
  </form>
  <table class="table">
    <thead><tr><th>时间</th><th>会员</th><th>类型</th><th>变动</th><th>变动前</th><th>变动后</th><th>业务单号</th><th>操作人</th><th>备注</th></tr></thead>
    <tbody>
    <?php if (!$res['list']): ?>
      <tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:32px">暂无日志</td></tr>
    <?php else: foreach ($res['list'] as $l): ?>
      <?php
        $n = $kind === 'balance' ? (float) $l['change_amount'] : (int) $l['change_points'];
        $before = $kind === 'balance' ? (float) $l['before_balance'] : (int) $l['before_points'];
        $after = $kind === 'balance' ? (float) $l['after_balance'] : (int) $l['after_points'];
        $mm = $l['member'] ?? [];
      ?>
      <tr>
        <td><?= date('Y-m-d H:i:s', (int) $l['created_at']) ?></td>
        <td><strong><?= e($mm['real_name'] ?: ($mm['username'] ?? ('#' . $l['member_id']))) ?></strong><div class="muted"><?= e($mm['mobile'] ?? '') ?></div></td>
        <td><span class="mb-admin-tag"><?= e($l['type_text']) ?></span></td>
        <td style="font-weight:800;color:<?= $n >= 0 ? '#0f7d4e' : '#b42318' ?>"><?= $n >= 0 ? '+' : '' ?><?= $kind === 'balance' ? number_format($n, 2) : $n ?></td>
        <td><?= $kind === 'balance' ? '￥' . number_format($before, 2) : $before ?></td>
        <td><?= $kind === 'balance' ? '￥' . number_format($after, 2) : $after ?></td>
        <td style="font-size:12px;color:#64748b"><?= e($l['biz_no'] ?: '-') ?></td>
        <td><?= e($l['operator_text']) ?></td>
        <td style="font-size:12px;color:#64748b"><?= e($l['remark'] ?: '-') ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
  <div class="mb-admin-pager">
    <?php echo \plugins\member\service\Member::pager($res['page'], $res['pages'], '/admin/plugin/member/logs?' . http_build_query(array_filter(['kind' => $kind, 'keyword' => $f['keyword'], 'type' => $f['type']], function ($v) { return $v !== '' && $v !== null; })) . '&'); ?>
  </div>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>

