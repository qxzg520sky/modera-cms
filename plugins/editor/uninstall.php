<?php
declare(strict_types=1);

$targets = [root_path() . 'static/editor'];
if (is_dir(root_path() . 'public')) {
    $targets[] = root_path() . 'public/static/editor';
}
$logs = [];

$remove = static function (string $dir) use (&$remove): bool {
    foreach (scandir($dir) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (!$remove($path)) {
                return false;
            }
        } elseif (!@unlink($path)) {
            return false;
        }
    }
    return @rmdir($dir);
};

foreach ($targets as $dst) {
    if (!is_dir($dst)) {
        continue;
    }
    if (!is_file($dst . '/.editor-plugin')) {
        $logs[] = $dst . ' 不是本插件创建，已跳过删除';
        continue;
    }
    if ($remove($dst)) {
        $logs[] = '编辑器静态资源已清理：' . $dst;
    } else {
        $logs[] = '编辑器静态资源清理失败，请手动删除：' . $dst;
    }
}

return $logs ?: ['未发现 static/editor，无需清理'];

