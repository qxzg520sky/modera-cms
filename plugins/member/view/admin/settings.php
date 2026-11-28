<?php $pageTitle = $pageTitle ?? '会员设置'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/member/asset/admin.css?v=1.0.0">

<div class="card">
  <div class="card-head"><span>会员设置</span><span class="muted">保存后立即生效</span></div>
  <form method="post" action="/admin/plugin/member/settings/save" style="padding:18px">
    <div class="mb-admin-form">
      <div><label>会员功能总开关</label><select name="enabled"><option value="1" <?= (string) $cfg['enabled'] === '1' ? 'selected' : '' ?>>开启</option><option value="0" <?= (string) $cfg['enabled'] !== '1' ? 'selected' : '' ?>>关闭</option></select><div class="hint">关闭后会员中心与 API 均不可用</div></div>
      <div><label>开放会员注册</label><select name="allow_register"><option value="1" <?= (string) $cfg['allow_register'] === '1' ? 'selected' : '' ?>>允许</option><option value="0" <?= (string) $cfg['allow_register'] !== '1' ? 'selected' : '' ?>>仅后台新增</option></select></div>
      <div><label>按累计消费自动升级</label><select name="auto_upgrade"><option value="1" <?= (string) $cfg['auto_upgrade'] === '1' ? 'selected' : '' ?>>开启</option><option value="0" <?= (string) $cfg['auto_upgrade'] !== '1' ? 'selected' : '' ?>>关闭</option></select></div>
      <div><label>前台页脚显示会员中心入口</label><select name="footer_link"><option value="1" <?= (string) $cfg['footer_link'] === '1' ? 'selected' : '' ?>>显示</option><option value="0" <?= (string) $cfg['footer_link'] !== '1' ? 'selected' : '' ?>>隐藏</option></select></div>
      <div><label>注册赠送积分</label><input name="register_points" value="<?= e((string) $cfg['register_points']) ?>"><div class="hint">0 表示不赠送；赠送会写积分日志</div></div>
      <div><label>后台会员列表每页条数</label><input name="page_size" value="<?= e((string) $cfg['page_size']) ?>"><div class="hint">10-100</div></div>
      <div><label>积分抵扣汇率（1 积分 = 元）</label><input name="points_money_rate" value="<?= e((string) $cfg['points_money_rate']) ?>"><div class="hint">例如 0.01 表示 100 积分抵 1 元</div></div>
      <div><label>新建会员默认密码</label><input name="default_password" value="<?= e((string) $cfg['default_password']) ?>"><div class="hint">用于后台新增未填密码、Excel 导入留空密码</div></div>
      <div><label>API AppID</label><input name="api_appid" value="<?= e((string) $cfg['api_appid']) ?>" required><div class="hint">商城调用时放在 X-Member-Appid 头</div></div>
      <div><label>API Secret</label><input name="api_secret" value="<?= e((string) $cfg['api_secret']) ?>"><div class="hint">修改后商城侧必须同步更新；留空保持不变</div></div>
      <div class="full"><label>API IP 白名单</label><input name="api_ip_whitelist" value="<?= e((string) $cfg['api_ip_whitelist']) ?>" placeholder="留空不限制；多个 IP 用英文逗号分隔"><div class="hint">生产环境建议限制为商城服务器 IP</div></div>
    </div>
    <div style="margin-top:18px"><button class="btn btn-primary" type="submit">保存设置</button></div>
  </form>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
  <div class="card">
    <div class="card-head"><span>接口签名规则</span></div>
    <div style="padding:18px;font-size:12.5px;color:#475569;line-height:1.9">
      <p style="margin-top:0">请求头：<code>X-Member-Appid</code>、<code>X-Member-Timestamp</code>、<code>X-Member-Nonce</code>、<code>X-Member-Sign</code></p>
      <p>待签名串：<code>appid + "\n" + timestamp + "\n" + nonce + "\n" + rawBody</code></p>
      <p>签名：<code>hash_hmac('sha256', $signStr, $secret)</code>，十六进制小写。</p>
      <p>时间窗 300 秒，nonce 8-64 位字母数字，同一 nonce 不可重放；默认限流 120 次 / 分钟。</p>
      <p class="muted">健康检查：<code>GET /member/api/health</code> 也需要签名。</p>
    </div>
  </div>
  <div class="card">
    <div class="card-head"><span>当前规模</span></div>
    <div style="padding:18px;display:grid;grid-template-columns:1fr 1fr;gap:14px">
      <div><div class="muted">会员总数</div><div style="font-size:24px;font-weight:800"><?= (int) $stats['members'] ?></div></div>
      <div><div class="muted">业务员</div><div style="font-size:24px;font-weight:800"><?= (int) $stats['staff'] ?></div></div>
      <div><div class="muted">未分配会员</div><div style="font-size:24px;font-weight:800"><?= (int) $stats['unassigned'] ?></div></div>
      <div><div class="muted">停用会员</div><div style="font-size:24px;font-weight:800"><?= (int) $stats['disabled'] ?></div></div>
      <div><div class="muted">余额合计</div><div style="font-size:20px;font-weight:800">￥<?= number_format((float) $stats['balance'], 2) ?></div></div>
      <div><div class="muted">积分合计</div><div style="font-size:20px;font-weight:800"><?= (int) $stats['points'] ?></div></div>
    </div>
  </div>
</div>

<div class="mb-admin-note" style="margin-top:16px">
  <strong>安全建议：</strong>上线前修改默认 API Secret 和默认会员密码；API Secret 只放在商城服务端，不能写进前端 JS；如商城与会员系统不在同一台机器，请把商城服务器 IP 加入白名单。
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>

