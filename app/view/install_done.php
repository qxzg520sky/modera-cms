<?php
/**
 * 安装完成 / 已安装提示页
 * @var bool $installed
 * @var string $site
 */
$ok = session('install_ok');
if ($ok) {
    session('install_ok', null);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $ok ? '安装完成' : '系统已安装' ?> - 企业管理系统</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Microsoft YaHei",sans-serif;background:#0b1020;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px 20px}
.bg{position:fixed;inset:0;background:
  radial-gradient(900px 500px at 12% -10%,rgba(56,189,248,.25),transparent 60%),
  radial-gradient(800px 500px at 88% 10%,rgba(129,140,248,.24),transparent 60%),
  linear-gradient(160deg,#0b1020 0%,#111a33 55%,#0b1020 100%);z-index:-1}
.card{width:100%;max-width:640px;background:#fff;border-radius:20px;box-shadow:0 30px 80px rgba(2,6,23,.55);padding:40px 36px;text-align:center}
.ic{width:66px;height:66px;border-radius:50%;margin:0 auto 18px;display:flex;align-items:center;justify-content:center;font-size:30px;color:#fff;background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 14px 30px rgba(16,185,129,.35)}
h1{font-size:24px;color:#0f172a;margin-bottom:10px}
p{color:#64748b;font-size:13.5px;line-height:1.9}
.creds{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:12px;padding:16px 18px;margin:22px 0;text-align:left;font-size:13px;color:#334155;line-height:2}
.creds b{color:#0f172a}
.btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:6px}
.btn{display:inline-flex;align-items:center;justify-content:center;height:44px;padding:0 24px;border-radius:10px;font-size:14px;font-weight:700;text-decoration:none;color:#fff;background:linear-gradient(120deg,#2563eb,#6366f1);box-shadow:0 10px 24px rgba(37,99,235,.3)}
.btn.ghost{background:#fff;color:#2563eb;border:1px solid #bfdbfe;box-shadow:none}
.tip{margin-top:20px;font-size:12px;color:#94a3b8}
@media(max-width:560px){.card{padding:28px 20px}}
</style>
</head>
<body>
<div class="bg"></div>
<div class="card">
  <div class="ic"><?= $ok ? '✓' : 'i' ?></div>
  <?php if ($ok): ?>
    <h1>安装完成</h1>
    <p>站点「<?= e((string) ($ok['site'] ?? $site)) ?>」已经准备好，默认模板与演示插件已启用。</p>
    <div class="creds">
      <div><b>后台地址</b>：<a href="/admin">/admin</a></div>
      <div><b>管理员账号</b>：<?= e((string) ($ok['username'] ?? '')) ?></div>
      <div><b>下一步</b>：登录后台 → 模块中心，安装需要的主题与插件</div>
    </div>
    <div class="btns">
      <a class="btn" href="/admin">进入后台</a>
      <a class="btn ghost" href="/" target="_blank">预览前台</a>
    </div>
    <div class="tip">为安全起见，安装完成后请保留 runtime/install.lock，/install 将不再允许重复安装。</div>
  <?php else: ?>
    <h1>系统已安装</h1>
    <p>检测到站点已经安装完成，安装向导已锁定，无法重复执行安装。<br>如需重装，请先备份数据，再删除 <code>runtime/install.lock</code> 后访问本页。</p>
    <div class="btns" style="margin-top:26px">
      <a class="btn" href="/admin">进入后台</a>
      <a class="btn ghost" href="/" target="_blank">打开前台</a>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
