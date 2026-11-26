<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>安装向导</title>
<link rel="stylesheet" href="/static/admin/admin.css">
</head>
<body>
<div class="login-page">
  <div class="login-box" style="width:420px">
    <h1>安装企业管理系统</h1>
    <p class="sub">首次使用请设置管理员账号，系统将自动建表并写入示例数据</p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-err"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="alert" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe">
      当前数据库：<b><?= e($db['name'] ?? '未读取') ?></b>（<?= e($db['host'] ?? '') ?>，前缀 <?= e($db['prefix'] ?? '') ?>）
      <div class="hint" style="color:#3b82f6">如需更换数据库，请先编辑项目根目录 .env 中的 DB_ 配置</div>
    </div>

    <form method="post" action="/admin/install">
      <div class="form-item">
        <label>站点名称</label>
        <input type="text" name="site_name" value="我的企业网站" required>
      </div>
      <div class="form-item">
        <label>管理员账号 <span class="req">*</span></label>
        <input type="text" name="username" value="admin" required>
      </div>
      <div class="form-item">
        <label>管理员密码 <span class="req">*</span></label>
        <input type="password" name="password" required placeholder="至少 6 位">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">开始安装</button>
    </form>
    <div class="login-foot">安装会创建 tp_ 前缀数据表，重复执行不会破坏已有数据</div>
  </div>
</div>
</body>
</html>
