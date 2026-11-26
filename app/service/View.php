<?php
declare(strict_types=1);

namespace app\service;

/**
 * 极简模板渲染：原生 PHP 模板，零外部依赖，复制即用
 * 后台模板：app/view/admin/xxx.php
 * 前台模板：themes/{主题}/xxx.php（PC） / themes/{主题}/mobile/xxx.php（手机）
 */
class View
{
    public static function admin(string $tpl, array $data = []): string
    {
        return self::fetch(root_path() . 'app/view/admin/' . $tpl . '.php', $data);
    }

    /**
     * 前台主题渲染：自动按设备选模板
     * 手机端先找 themes/{主题}/mobile/{tpl}.php，没写就回落 PC 模板
     */
    public static function theme(string $tpl, array $data = []): string
    {
        $theme = (string) ThemeManager::current();
        $dev   = device_type();
        $data['__device'] = $dev;

        $files = [];
        if ($dev === 'mobile' && setting('mobile_enable', '1') === '1') {
            $files[] = root_path() . 'themes/' . $theme . '/mobile/' . $tpl . '.php';
        }
        $files[] = root_path() . 'themes/' . $theme . '/' . $tpl . '.php';
        $files[] = root_path() . 'themes/default/' . $tpl . '.php';

        foreach ($files as $file) {
            if (is_file($file)) {
                return self::fetch($file, $data);
            }
        }
        throw new \RuntimeException('模板文件不存在：' . $tpl);
    }

    public static function fetch(string $file, array $data = []): string
    {
        if (!is_file($file)) {
            throw new \RuntimeException('模板文件不存在：' . $file);
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }
}
