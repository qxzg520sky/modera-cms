<?php
declare(strict_types=1);

namespace app\middleware;

use app\service\Installer;
use Closure;
use think\Request;

/**
 * 后台登录校验 + 首次安装引导
 */
class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $path = trim(str_replace('/index.php', '', $request->pathinfo()), '/');

        // 安装向导（统一走前台 /install）
        if (!Installer::isInstalled()) {
            if (str_starts_with($path, 'admin/install')) {
                return $next($request);
            }
            return redirect('/install');
        }

        // 免登录白名单
        $allow = ['admin/login', 'admin/logout'];
        if (in_array($path, $allow, true)) {
            return $next($request);
        }

        if (!$request->session('admin_id')) {
            if ($request->isPost() || $request->isAjax()) {
                return json(['code' => 401, 'msg' => '登录状态已失效，请重新登录']);
            }
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
