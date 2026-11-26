<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>登录 - <?= e($siteName) ?></title>
<link rel="stylesheet" href="/static/admin/admin.css">
</head>
<body>
<div class="login-page">
  <div class="login-box">
    <h1><?= e($siteName) ?></h1>
    <p class="sub">企业管理系统 · 后台登录</p>
    <?php if (!empty($error)): ?>
      <div class="alert alert-err"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/admin/login">
      <div class="form-item">
        <label>管理员账号</label>
        <input type="text" name="username" autofocus required placeholder="请输入账号">
      </div>
      <div class="form-item">
        <label>登录密码</label>
        <input type="password" name="password" required placeholder="请输入密码">
      </div>
      <button type="submit" class="btn btn-primary">登 录</button>
    </form>
    <div class="login-foot"><a href="https://modera.webfixer.cn/" target="_blank" rel="noopener">modera-cms</a> · 后台路径 /admin</div>
  </div>
</div>
</body>
</html>
