<?php
declare(strict_types=1);

namespace app\middleware;

use app\service\PluginManager;

/**
 * 缓存优化插件的接入点：
 * 插件启用时把请求交给 plugins\cache\service\PageCache 处理；
 * 插件没装/停用时这里什么都不做（顺手清掉残留的页面缓存），前台与原生 TP8 完全一致。
 */
class PageCache
{
    public function handle($request, \Closure $next)
    {
        if (!in_array('cache', PluginManager::enabled(), true) || !class_exists(\plugins\cache\service\PageCache::class)) {
            if (class_exists(\plugins\cache\service\Cache::class)) {
                \plugins\cache\service\Cache::clear();
            }
            return $next($request);
        }

        return (new \plugins\cache\service\PageCache())->handle($request, $next);
    }
}
