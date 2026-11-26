<?php
declare (strict_types = 1);

namespace app;

use think\Service;

/**
 * 应用服务类
 */
class AppService extends Service
{
    public function register()
    {
    }

    public function boot()
    {
        // 插件自动加载：plugins\example\controller\Index => plugins/example/controller/Index.php
        spl_autoload_register(static function (string $class): void {
            if (!str_starts_with($class, 'plugins\\')) {
                return;
            }
            $file = root_path() . 'plugins/' . str_replace('\\', '/', substr($class, 8)) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        });
    }
}
