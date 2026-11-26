<?php
$pluginMenus = [];
try {
    foreach (\app\model\Plugin::where('enabled', 1)->select()->toArray() as $p) {
        $jf = root_path() . 'plugins/' . $p['name'] . '/plugin.json';
        if (is_file($jf)) {
            $meta = json_decode((string) file_get_contents($jf), true) ?: [];
            foreach ((array) ($meta['admin_menu'] ?? []) as $item) {
                if (!empty($item['title']) && !empty($item['url'])) {
                    $pluginMenus[] = $item;
                }
            }
        }
    }
} catch (\Throwable $e) {
}

$siteName = setting('site_name', '企业管理系统');
$userName = $admin['nickname'] ?? '';
if ($userName === '') {
    $userName = $admin['username'] ?? '管理员';
}
$active = $active ?? '';

$groups = [
    '概览' => [
        ['key' => 'dashboard', 'title' => '仪表盘', 'url' => '/admin', 'ico' => '◎'],
    ],
    '内容' => [
        ['key' => 'article', 'title' => '文章管理', 'url' => '/admin/article', 'ico' => '📝'],
        ['key' => 'category', 'title' => '栏目管理', 'url' => '/admin/category', 'ico' => '🗂'],
        ['key' => 'page', 'title' => '单页管理', 'url' => '/admin/page', 'ico' => '📄'],
    ],
    '外观' => [
        ['key' => 'theme', 'title' => '模板主题', 'url' => '/admin/theme', 'ico' => '🎨'],
    ],
    '扩展' => [
        ['key' => 'module', 'title' => '模块中心', 'url' => '/admin/module', 'ico' => '📦'],
        ['key' => 'plugin', 'title' => '插件扩展', 'url' => '/admin/plugin', 'ico' => '🧩'],
    ],
    '系统' => [
        ['key' => 'setting', 'title' => '系统设置', 'url' => '/admin/setting', 'ico' => '⚙️'],
        ['key' => 'user', 'title' => '管理员', 'url' => '/admin/user', 'ico' => '👤'],
    ],
];
if ($pluginMenus) {
    $groups['插件菜单'] = [];
    foreach ($pluginMenus as $item) {
        $badge = function_exists('plugin_badge') ? plugin_badge((string) $item['url']) : 0;
        $groups['插件菜单'][] = [
            'key'   => 'plugin-' . md5($item['url']),
            'title' => $item['title'],
            'url'   => $item['url'],
            'ico'   => $item['ico'] ?? '›',
            'badge' => $badge,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle ?? '后台') ?> - <?= e($siteName) ?></title>
<link rel="stylesheet" href="/static/admin/admin.css">
</head>
<body>
<aside class="sidebar">
  <div class="brand">
    <h1><?= e($siteName) ?></h1>
    <span>企业管理系统 · 后台</span>
  </div>
  <nav class="nav">
    <?php foreach ($groups as $gName => $items): ?>
      <div class="nav-group"><?= e($gName) ?></div>
      <?php foreach ($items as $item): ?>
        <a class="nav-item <?= $active === $item['key'] ? 'active' : '' ?>" href="<?= e($item['url']) ?>">
          <span class="ico"><?= $item['ico'] ?></span><?= e($item['title']) ?>
          <?php $__b = (int) ($item['badge'] ?? 0); ?>
          <span class="nav-badge" data-badge="<?= e($item['url']) ?>" style="margin-left:auto;min-width:18px;height:18px;padding:0 5px;border-radius:99px;background:#ef4444;color:#fff;font-size:11px;line-height:18px;text-align:center;font-weight:600<?= $__b > 0 ? '' : ';display:none' ?>"><?= $__b > 99 ? '99+' : $__b ?></span>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-foot"><a href="https://modera.webfixer.cn/" target="_blank" rel="noopener">modera-cms</a></div>
</aside>

<div class="main">
  <div class="topbar">
    <h2><?= e($pageTitle ?? '后台') ?></h2>
    <div class="right">
      <a href="/" target="_blank">前台预览 ↗</a>
      <span><?= e($userName) ?></span>
      <a href="/admin/logout" onclick="return confirm('确定退出登录？')">退出</a>
    </div>
  </div>
  <div class="wrap">
    <?php if (!empty($flash)): ?>
      <div class="alert alert-ok"><?= e($flash) ?></div>
    <?php endif; ?>
