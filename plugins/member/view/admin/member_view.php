<?php $pageTitle = $pageTitle ?? '会员详情'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/member/asset/admin.css?v=1.0.0">

<div class="card">
  <div class="card-head">
    <span><?= e($m['real_name'] ?: $m['username']) ?> <span class="muted"><?= e($m['member_no']) ?> · <?= e($m['mobile']) ?></span></span>
    <span style="display:flex;gap:8px">
      <a class="btn btn-sm" href="/admin/plugin/member/member/edit/<?= (int) $m['id'] ?>">编辑资料</a>
      <a class="btn btn-sm" href="/admin/plugin/member/members">← 返回列表</a>
    </span>
  </div>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;padding:18px">
    <div><div class="muted">账户余额</div><div style="font-size:23px;font-weight:800;color:#1558b0">￥<?= number_format((float) $m['balance'], 2) ?></div></div>
    <div><div class="muted">可用积分</div><div style="font-size:23px;font-weight:800"><?= (int) $m['points'] ?></div></div>
    <div><div class="muted">累计消费</div><div style="font-size:23px;font-weight:800">￥<?= number_format((float) $m['total_spent'], 2) ?></div></div>
    <div><div class="muted">会员折扣</div><div style="font-size:23px;font-weight:800"><?= e(\plugins\member\service\Member::discountText((float) $m['discount'])) ?></div></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
  <div class="card">
    <div class="card-head"><span>基本资料</span></div>
    <div style="padding:18px;display:grid;grid-template-columns:1fr 1fr;gap:14px 18px;font-size:13px">
      <div><div class="muted">姓名</div><strong><?= e($m['real_name'] ?: '-') ?></strong></div>
      <div><div class="muted">用户名</div><strong><?= e($m['username']) ?></strong></div>
      <div><div class="muted">手机号</div><strong><?= e($m['mobile']) ?></strong></div>
      <div><div class="muted">邮箱</div><strong><?= e($m['email'] ?: '-') ?></strong></div>
      <div><div class="muted">公司</div><strong><?= e($m['company'] ?: '-') ?></strong></div>
      <div><div class="muted">会员等级</div><strong><?= e($m['level_name']) ?></strong></div>
      <div><div class="muted">归属业务员</div><strong><?php $sn=''; foreach ($staffs as $s) if ((int)$s['id']===(int)$m['staff_id']) $sn=$s['real_name']?:$s['username']; echo e($sn ?: '未分配'); ?></strong></div>
      <div><div class="muted">状态</div><strong><?= (int) $m['status'] === 1 ? '正常' : '停用' ?></strong></div>
      <div><div class="muted">注册时间</div><strong><?= $m['created_at'] ? date('Y-m-d H:i', (int) $m['created_at']) : '-' ?></strong></div>
      <div><div class="muted">最后登录</div><strong><?= $m['last_login_at'] ? date('Y-m-d H:i', (int) $m['last_login_at']) : '-' ?></strong></div>
      <div style="grid-column:1/-1"><div class="muted">备注</div><div style="margin-top:4px;line-height:1.7;color:#475569"><?= nl2br(e($raw['remark'] ?: '-')) ?></div></div>
    </div>
    <div style="padding:0 18px 18px;display:flex;gap:8px">
      <form method="post" action="/admin/plugin/member/member/upgrade">
        <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
        <button class="btn btn-sm" type="submit">按累计消费重算等级</button>
      </form>
    </div>
  </div>
  <div class="card">
    <div class="card-head"><span>收货地址</span><span class="muted">共 <?= count($addresses) ?> 条</span></div>
    <div style="padding:16px 18px;display:grid;gap:12px">
      <?php if (!$addresses): ?><div style="color:#94a3b8;font-size:13px">暂无收货地址</div><?php endif; ?>
      <?php foreach ($addresses as $a): ?>
        <div style="border:1px solid #e8eef7;border-radius:8px;padding:12px">
          <div style="display:flex;justify-content:space-between;gap:8px"><strong><?= e($a['receiver']) ?></strong><span class="muted"><?= e($a['mobile']) ?><?= $a['is_default'] ? ' · 默认' : '' ?></span></div>
          <div style="color:#475569;margin-top:5px;font-size:12.5px;line-height:1.6"><?= e($a['province'] . $a['city'] . $a['district']) ?> <?= e($a['detail']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <div class="card-head"><span>资产调整</span><span class="muted">所有变动都会记录流水，消费 / 退款 / 提现 / 积分扣减必须填写业务单号</span></div>
  <div class="mb-admin-asset" style="padding:18px">
    <form class="box" method="post" action="/admin/plugin/member/member/asset">
      <h4>余额调整</h4>
      <div class="row" style="margin-bottom:9px">
        <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
        <input type="hidden" name="kind" value="balance">
        <select name="direction"><option value="in">增加余额</option><option value="out">扣减余额</option></select>
        <input class="narrow" name="amount" placeholder="金额" value="">
      </div>
      <div class="row" style="margin-bottom:9px">
        <select name="type">
          <?php foreach ($balTypes as $k => $v): if ($k === 'init') continue; ?><option value="<?= e($k) ?>"><?= e($v) ?></option><?php endforeach; ?>
        </select>
        <input name="biz_no" placeholder="业务单号（部分类型必填）">
      </div>
      <div style="margin-bottom:9px"><input name="remark" placeholder="备注，例如：线下充值 / 售后补偿"></div>
      <button class="btn btn-primary btn-sm" type="submit" data-confirm="确认执行余额调整？">确认调整余额</button>
    </form>

    <form class="box" method="post" action="/admin/plugin/member/member/asset">
      <h4>积分调整</h4>
      <div class="row" style="margin-bottom:9px">
        <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
        <input type="hidden" name="kind" value="points">
        <select name="direction"><option value="in">增加积分</option><option value="out">扣减积分</option></select>
        <input class="narrow" name="amount" placeholder="积分" value="">
      </div>
      <div class="row" style="margin-bottom:9px">
        <select name="type">
          <?php foreach ($ptsTypes as $k => $v): if ($k === 'init') continue; ?><option value="<?= e($k) ?>"><?= e($v) ?></option><?php endforeach; ?>
        </select>
        <input name="biz_no" placeholder="业务单号（部分类型必填）">
      </div>
      <div style="margin-bottom:9px"><input name="remark" placeholder="备注，例如：活动赠送 / 积分抵扣"></div>
      <button class="btn btn-primary btn-sm" type="submit" data-confirm="确认执行积分调整？">确认调整积分</button>
    </form>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <div class="mb-admin-tabs">
    <a class="on" href="#balance">余额流水</a>
    <a href="#points">积分流水</a>
  </div>
  <div id="balance">
    <table class="table">
      <thead><tr><th>时间</th><th>类型</th><th>变动</th><th>变动前</th><th>变动后</th><th>业务单号</th><th>操作人</th><th>备注</th></tr></thead>
      <tbody>
      <?php if (!$balLog['list']): ?><tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:26px">暂无余额流水</td></tr><?php endif; ?>
      <?php foreach ($balLog['list'] as $l): $n = (float) $l['change_amount']; ?>
        <tr><td><?= date('Y-m-d H:i', (int) $l['created_at']) ?></td><td><?= e($l['type_text']) ?></td><td style="font-weight:800;color:<?= $n >= 0 ? '#0f7d4e' : '#b42318' ?>"><?= $n >= 0 ? '+' : '' ?><?= number_format($n, 2) ?></td><td>￥<?= number_format((float) $l['before_balance'], 2) ?></td><td>￥<?= number_format((float) $l['after_balance'], 2) ?></td><td><?= e($l['biz_no'] ?: '-') ?></td><td><?= e($l['operator_text']) ?></td><td><?= e($l['remark'] ?: '-') ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div id="points" style="display:none">
    <table class="table">
      <thead><tr><th>时间</th><th>类型</th><th>变动</th><th>变动前</th><th>变动后</th><th>业务单号</th><th>操作人</th><th>备注</th></tr></thead>
      <tbody>
      <?php if (!$ptsLog['list']): ?><tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:26px">暂无积分流水</td></tr><?php endif; ?>
      <?php foreach ($ptsLog['list'] as $l): $n = (int) $l['change_points']; ?>
        <tr><td><?= date('Y-m-d H:i', (int) $l['created_at']) ?></td><td><?= e($l['type_text']) ?></td><td style="font-weight:800;color:<?= $n >= 0 ? '#0f7d4e' : '#b42318' ?>"><?= $n >= 0 ? '+' : '' ?><?= $n ?></td><td><?= (int) $l['before_points'] ?></td><td><?= (int) $l['after_points'] ?></td><td><?= e($l['biz_no'] ?: '-') ?></td><td><?= e($l['operator_text']) ?></td><td><?= e($l['remark'] ?: '-') ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.querySelectorAll('.mb-admin-tabs a').forEach(function(a){
  a.addEventListener('click', function(e){
    e.preventDefault();
    var id = a.getAttribute('href').replace('#','');
    document.querySelectorAll('.mb-admin-tabs a').forEach(function(x){x.classList.remove('on')});
    a.classList.add('on');
    document.getElementById('balance').style.display = id === 'balance' ? '' : 'none';
    document.getElementById('points').style.display = id === 'points' ? '' : 'none';
  });
});
</script>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>

