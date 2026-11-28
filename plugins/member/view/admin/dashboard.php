<?php $pageTitle = $pageTitle ?? '会员总览'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/member/asset/admin.css?v=1.0.0">

<div class="mb-admin-stats">
  <div class="mb-admin-stat"><div class="l">会员总数</div><div class="n blue"><?= (int) $stats['members'] ?></div></div>
  <div class="mb-admin-stat"><div class="l">今日新增</div><div class="n green"><?= (int) $stats['today'] ?></div></div>
  <div class="mb-admin-stat"><div class="l">余额合计</div><div class="n">￥<?= number_format((float) $stats['balance'], 2) ?></div></div>
  <div class="mb-admin-stat"><div class="l">积分合计</div><div class="n"><?= (int) $stats['points'] ?></div></div>
  <div class="mb-admin-stat"><div class="l">未分配业务员</div><div class="n warn"><?= (int) $stats['unassigned'] ?></div></div>
</div>

<div style="display:grid;grid-template-columns:1.3fr .7fr;gap:16px">
  <div class="card">
    <div class="card-head"><span>累计消费 TOP 会员</span><a class="btn btn-sm" href="/admin/plugin/member/members">全部会员 →</a></div>
    <table class="table">
      <thead><tr><th>会员</th><th>等级</th><th>累计消费</th><th>余额</th><th>业务员</th><th></th></tr></thead>
      <tbody>
      <?php if (!$top): ?>
        <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:28px">暂无会员，先新增或导入</td></tr>
      <?php else: foreach ($top as $m): ?>
        <tr>
          <td><strong><?= e($m['real_name'] ?: $m['username']) ?></strong><div class="muted"><?= e($m['member_no']) ?> · <?= e($m['mobile']) ?></div></td>
          <td><span class="mb-admin-tag"><?= e($m['level_name'] ?? '普通会员') ?></span></td>
          <td>￥<?= number_format((float) $m['total_spent'], 2) ?></td>
          <td>￥<?= number_format((float) $m['balance'], 2) ?></td>
          <td><?= (int) $m['staff_id'] > 0 ? '已分配' : '<span style="color:#b45309">未分配</span>' ?></td>
          <td><a class="btn btn-sm" href="/admin/plugin/member/member/view/<?= (int) $m['id'] ?>">详情</a></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card-head"><span>等级分布</span></div>
    <div style="padding:16px 18px;display:grid;gap:12px">
      <?php foreach ($stats['levels'] as $d): $pct = $stats['members'] > 0 ? (int) round($d['count'] * 100 / $stats['members']) : 0; ?>
        <div>
          <div style="display:flex;justify-content:space-between;font-size:12.5px;color:#475569"><span><?= e($d['name']) ?></span><b><?= (int) $d['count'] ?> 人</b></div>
          <div style="height:8px;border-radius:99px;background:#eef2f7;margin-top:6px;overflow:hidden"><div style="height:8px;width:<?= $pct ?>%;background:linear-gradient(90deg,#1558b0,#16a6c9)"></div></div>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="padding:0 18px 18px">
      <a class="btn" style="width:100%;text-align:center" href="/admin/plugin/member/levels">管理会员等级</a>
    </div>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <div class="card-head"><span>最近资产变动</span><a class="btn btn-sm" href="/admin/plugin/member/logs">全部日志 →</a></div>
  <table class="table">
    <thead><tr><th>时间</th><th>会员</th><th>类型</th><th>变动</th><th>变动后</th><th>业务单号</th><th>操作人</th></tr></thead>
    <tbody>
    <?php if (!$logs): ?>
      <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:28px">暂无余额变动</td></tr>
    <?php else: foreach ($logs as $l): $n = (float) $l['change_amount']; ?>
      <tr>
        <td><?= date('m-d H:i', (int) $l['created_at']) ?></td>
        <td><?= e($l['member']['real_name'] ?? $l['member']['username'] ?? ('#' . $l['member_id'])) ?></td>
        <td><?= e($l['type_text']) ?></td>
        <td style="font-weight:800;color:<?= $n >= 0 ? '#0f7d4e' : '#b42318' ?>"><?= $n >= 0 ? '+' : '' ?><?= number_format($n, 2) ?></td>
        <td>￥<?= number_format((float) $l['after_balance'], 2) ?></td>
        <td style="font-size:12px;color:#64748b"><?= e($l['biz_no'] ?: '-') ?></td>
        <td><?= e($l['operator_text']) ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<div class="mb-admin-note" style="margin-top:16px">
  <strong>接入提示：</strong>商城插件请通过 <code>/member/api/*</code> 或服务类 <code>plugins\member\service\Member</code> 调用会员资产接口，不要直接写会员表和日志表。API 密钥在 <a href="/admin/plugin/member/settings">会员设置</a> 中查看。当前默认密码：<code><?= e($cfg['default_password'] ?? '') ?></code>。
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>

