<?php
/**
 * 安装向导（独立页面，无后台样式依赖）
 * @var array $checks
 * @var bool  $canInstall
 * @var array $db
 * @var string $error
 * @var array $old
 */
$failed = 0;
foreach ($checks as $c) {
    if (empty($c['ok'])) {
        $failed++;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>安装向导 - Modera企业管理系统</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Microsoft YaHei",sans-serif;background:#0b1020;color:#111827;min-height:100vh}
.bg{position:fixed;inset:0;background:
  radial-gradient(900px 500px at 12% -10%,rgba(56,189,248,.28),transparent 60%),
  radial-gradient(800px 500px at 88% 8%,rgba(129,140,248,.26),transparent 60%),
  linear-gradient(160deg,#0b1020 0%,#111a33 55%,#0b1020 100%);z-index:-1}
.bg:after{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.045) 1px,transparent 1px);background-size:44px 44px;mask-image:radial-gradient(circle at 50% 20%,#000 30%,transparent 78%)}
.wrap{max-width:1040px;margin:0 auto;padding:56px 24px 72px}
.hero{text-align:center;color:#e5e7eb;margin-bottom:34px}
.badge{display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:999px;background:rgba(59,130,246,.16);border:1px solid rgba(96,165,250,.35);color:#93c5fd;font-size:12px;letter-spacing:.08em}
h1{font-size:34px;line-height:1.25;margin:18px 0 12px;color:#f8fafc;font-weight:700;letter-spacing:.5px}
h1 span{background:linear-gradient(90deg,#60a5fa,#a78bfa);-webkit-background-clip:text;background-clip:text;color:transparent}
.sub{color:#94a3b8;font-size:14px;line-height:1.9}
.steps{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:24px}
.step{display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:#cbd5e1;font-size:13px}
.step b{display:inline-flex;width:20px;height:20px;border-radius:50%;align-items:center;justify-content:center;background:#3b82f6;color:#fff;font-size:12px}
.card{background:#fff;border-radius:18px;box-shadow:0 30px 80px rgba(2,6,23,.55);overflow:hidden}
.card-head{padding:22px 28px;background:linear-gradient(120deg,#f8fafc,#eff6ff);border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.card-head h2{font-size:17px;color:#0f172a}
.card-head .hint{font-size:12px;color:#64748b}
.card-body{padding:26px 28px 30px}
.err{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 14px;border-radius:10px;font-size:13px;line-height:1.7;margin-bottom:18px}
.sect{margin-bottom:26px}
.sect-title{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:#0f172a;margin-bottom:12px}
.sect-title i{width:4px;height:16px;border-radius:3px;background:linear-gradient(180deg,#60a5fa,#6366f1);display:inline-block}
.checks{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:10px}
.check{display:flex;align-items:center;gap:10px;border:1px solid #e5e7eb;border-radius:10px;padding:10px 12px;font-size:12.5px;background:#fbfdff}
.check .ic{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;color:#fff;flex:0 0 22px}
.ok .ic{background:#10b981}.bad .ic{background:#ef4444}
.check .txt{flex:1;min-width:0}
.check .txt b{display:block;color:#111827;font-weight:600;font-size:12.5px}
.check .val{color:#64748b;font-size:11.5px;word-break:break-all}
.warn-tip{font-size:12.5px;color:#b45309;background:#fffbeb;border:1px solid #fde68a;padding:10px 12px;border-radius:10px;margin-top:12px}
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 18px}
.grid3{grid-template-columns:repeat(3,minmax(0,1fr))}
.field label{display:block;font-size:12.5px;color:#475569;margin-bottom:6px;font-weight:600}
.field input{width:100%;height:42px;border:1px solid #dbe2ea;border-radius:9px;padding:0 12px;font-size:13.5px;color:#0f172a;background:#fff;outline:none;transition:.18s}
.field input:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,.12)}
.field .tip{font-size:11.5px;color:#94a3b8;margin-top:5px}
.field.full{grid-column:1/-1}
.auto{display:flex;align-items:center;gap:8px;font-size:12.5px;color:#475569;margin-top:4px}
.auto input{width:16px;height:16px}
.actions{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;border-top:1px dashed #e5e7eb;padding-top:20px;margin-top:4px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:44px;padding:0 26px;border:0;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;color:#fff;background:linear-gradient(120deg,#2563eb,#6366f1);box-shadow:0 10px 24px rgba(37,99,235,.32);transition:.18s}
.btn:hover{transform:translateY(-1px);box-shadow:0 14px 28px rgba(37,99,235,.4)}
.btn:disabled{background:#cbd5e1;box-shadow:none;cursor:not-allowed;transform:none}
.actions .note{font-size:12px;color:#64748b;line-height:1.8}
.foot{color:#64748b;font-size:12px;text-align:center;margin-top:22px}
@media (max-width:760px){
  .wrap{padding:32px 14px 48px}
  h1{font-size:25px}
  .grid,.grid3{grid-template-columns:1fr}
  .card-body{padding:20px}
  .actions .btn{width:100%}
  .steps{gap:8px}
  .step{font-size:12px;padding:7px 10px}
}
</style>
</head>
<body>
<div class="bg"></div>
<div class="wrap">
  <div class="hero">
    <div class="badge">INSTALL · 首次部署</div>
    <h1>Modera企业网站管理系统 <span>安装向导</span></h1>
    <p class="sub">三步完成部署：环境检测 → 数据库与管理员 → 执行安装。<br>首次安装只包含「默认模板 + 演示插件」，其它主题与插件到后台「模块中心」下载安装。</p>
    <div class="steps">
      <div class="step"><b>1</b>环境检测</div>
      <div class="step"><b>2</b>数据库 / 管理员</div>
      <div class="step"><b>3</b>执行安装</div>
    </div>
  </div>

  <div class="card">
    <div class="card-head">
      <h2>安装配置</h2>
      <span class="hint">检测项 <?= count($checks) - $failed ?>/<?= count($checks) ?> 通过</span>
    </div>
    <div class="card-body">
      <?php if (!empty($error)): ?>
        <div class="err"><?= e($error) ?></div>
      <?php endif; ?>

      <div class="sect">
        <div class="sect-title"><i></i>环境检测</div>
        <div class="checks">
          <?php foreach ($checks as $c): ?>
            <div class="check <?= !empty($c['ok']) ? 'ok' : 'bad' ?>">
              <span class="ic"><?= !empty($c['ok']) ? '✓' : '!' ?></span>
              <span class="txt">
                <b><?= e($c['label']) ?></b>
                <span class="val"><?= e((string) $c['value']) ?></span>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if ($failed > 0): ?>
          <div class="warn-tip">有 <?= $failed ?> 项未通过。请先开启对应 PHP 扩展、给目录写权限后刷新本页。</div>
        <?php endif; ?>
      </div>

      <form method="post" action="/install" autocomplete="off">
        <div class="sect">
          <div class="sect-title"><i></i>数据库</div>
          <div class="grid grid3">
            <div class="field">
              <label>数据库地址</label>
              <input type="text" name="db_host" value="<?= e($db['host']) ?>" placeholder="127.0.0.1">
            </div>
            <div class="field">
              <label>端口</label>
              <input type="text" name="db_port" value="<?= e($db['port']) ?>" placeholder="3306">
            </div>
            <div class="field">
              <label>表前缀</label>
              <input type="text" name="db_prefix" value="<?= e($db['prefix']) ?>" placeholder="tp_">
              <div class="tip">建议保留 tp_，避免与同库其它程序冲突</div>
            </div>
            <div class="field">
              <label>数据库名</label>
              <input type="text" name="db_name" value="<?= e($db['name']) ?>" placeholder="例如 tempqiyecms" required>
            </div>
            <div class="field">
              <label>数据库用户名</label>
              <input type="text" name="db_user" value="<?= e($db['user']) ?>" placeholder="root" required>
            </div>
            <div class="field">
              <label>数据库密码</label>
              <input type="password" name="db_pass" value="<?= e($db['pass']) ?>" placeholder="留空表示空密码">
            </div>
          </div>
          <label class="auto"><input type="checkbox" name="auto_create" value="1" checked>数据库不存在时自动创建（需要账号有 CREATE 权限）</label>
        </div>

        <div class="sect">
          <div class="sect-title"><i></i>站点信息</div>
          <div class="grid">
            <div class="field">
              <label>站点名称</label>
              <input type="text" name="site_name" value="<?= e($old['site_name'] ?? '') ?>" placeholder="例如 某某钢铁有限公司" required>
            </div>
            <div class="field">
              <label>站点口号</label>
              <input type="text" name="site_slogan" value="<?= e($old['site_slogan'] ?? '专业 · 高效 · 值得信赖') ?>" placeholder="专业 · 高效 · 值得信赖">
            </div>
          </div>
        </div>

        <div class="sect">
          <div class="sect-title"><i></i>管理员账号</div>
          <div class="grid">
            <div class="field">
              <label>管理员用户名</label>
              <input type="text" name="username" value="<?= e($old['username'] ?? '') ?>" placeholder="3-20 位字母/数字/下划线" required>
            </div>
            <div class="field">
              <label>登录密码</label>
              <input type="password" name="password" placeholder="至少 6 位" required>
            </div>
            <div class="field">
              <label>确认密码</label>
              <input type="password" name="password2" placeholder="再输入一次" required>
            </div>
            <div class="field">
              <label>安装内容</label>
              <input type="text" value="默认模板 default + 演示插件 example" disabled>
              <div class="tip">安装完成后可在后台「模块中心」安装其它主题/插件</div>
            </div>
          </div>
        </div>

        <div class="actions">
          <div class="note">点击安装会创建数据表并写入初始数据。<br>如果库里已有同名表，不会覆盖已有数据。</div>
          <button class="btn" type="submit" <?= $canInstall ? '' : 'disabled' ?>>开始安装 →</button>
        </div>
      </form>
    </div>
  </div>
  <div class="foot"><a href="https://modera.webfixer.cn/" target="_blank" rel="noopener">modera-cms</a> · 安装完成后可用管理员账号进入 /admin</div>
</div>
</body>
</html>
