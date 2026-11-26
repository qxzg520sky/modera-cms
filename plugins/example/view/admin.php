<?php $m = $meta; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<title><?= e($m['title'] ?? '示例插件') ?> - 后台</title>
<link rel="stylesheet" href="/static/admin/admin.css">
</head>
<body style="background:#f4f6fa;padding:28px">
<div style="max-width:860px;margin:0 auto">
  <div class="card">
    <div class="card-head">
      <span><?= e($m['title'] ?? '示例插件') ?></span>
      <a class="btn" href="/admin/plugin">返回插件列表</a>
    </div>
    <div class="card-body" style="font-size:14px;line-height:1.9;color:#374151">
      <div>版本：<?= e($m['version'] ?? '') ?>　作者：<?= e($m['author'] ?? '') ?></div>
      <div>状态：<?= ((int) ($row['enabled'] ?? 0) === 1) ? '<span class="badge badge-ok">已启用</span>' : '<span class="badge badge-gray">未启用</span>' ?></div>
      <div style="margin-top:12px"><?= e($m['description'] ?? '') ?></div>
      <div style="margin-top:12px;color:#6b7280">插件配置项（在“插件设置”里修改）：</div>
      <div>前台展示文字：<b><?= e($config['welcome_text'] ?? '（未设置）') ?></b></div>
      <div>说明：<?= e($config['intro'] ?? '（未设置）') ?></div>
      <div style="margin-top:16px">
        <a class="btn btn-primary" href="/plugin-example" target="_blank">查看插件前台页面</a>
        <a class="btn" href="/admin/plugin/config/example">插件设置</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>

