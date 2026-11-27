<?php
declare(strict_types=1);

$src = __DIR__ . '/assets';
// 兼容两种部署：站点根目录直接对外，或标准 ThinkPHP 的 public 作为文档根
$targets = [root_path() . 'static/editor'];
if (is_dir(root_path() . 'public')) {
    $targets[] = root_path() . 'public/static/editor';
}

$copyDir = static function (string $from, string $to) use (&$copyDir): bool {
    if (!is_dir($to) && !@mkdir($to, 0755, true) && !is_dir($to)) {
        return false;
    }
    foreach (scandir($from) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $s = $from . DIRECTORY_SEPARATOR . $item;
        $d = $to . DIRECTORY_SEPARATOR . $item;
        if (is_dir($s)) {
            if (!$copyDir($s, $d)) {
                return false;
            }
            continue;
        }
        if (!@copy($s, $d)) {
            return false;
        }
    }
    return true;
};

$logs = [];
if (!is_dir($src)) {
    return ['编辑器静态资源目录不存在：plugins/editor/assets'];
}

foreach ($targets as $dst) {
    if (!$copyDir($src, $dst)) {
        return ['编辑器静态资源复制失败，请检查 static 目录权限：' . $dst];
    }
    @file_put_contents($dst . '/.editor-plugin', 'modera-editor');
}

$logs[] = '编辑器静态资源已部署：static/editor';
if (count($targets) > 1) {
    $logs[] = '同时写入 public/static/editor，兼容 public 作为文档根的部署';
}
$logs[] = '可在插件设置中切换 WangEditor（默认）或 Vditor';
return $logs;

