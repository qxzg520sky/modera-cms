<?php
declare(strict_types=1);

namespace app\middleware;

use app\service\Installer;
use Closure;
use think\Request;

/**
 * 首次安装引导：文件刚上传、数据库还没装时，把全站请求带到 /install。
 * 安装完成（runtime/install.lock 或数据表存在）后本中间件直接放行。
 */
class NeedInstall
{
    public function handle(Request $request, Closure $next)
    {
        if (Installer::isInstalled()) {
            return $next($request);
        }

        $path = trim(str_replace('/index.php', '', $request->pathinfo()), '/');

        // 安装向导自身与静态资源放行，避免样式/图片也跳转
        if ($path === 'install' || str_starts_with($path, 'install/')) {
            return $next($request);
        }
        if (in_array($path, ['favicon.ico', 'robots.txt'], true)) {
            return $next($request);
        }
        if (preg_match('#^(static|uploads|theme)/#', $path)) {
            return $next($request);
        }

        if ($request->isAjax() || $request->isPost()) {
            return json(['code' => 503, 'msg' => '站点尚未安装，请先访问 /install 完成安装'], 503);
        }
        return redirect('/install');
    }
}
