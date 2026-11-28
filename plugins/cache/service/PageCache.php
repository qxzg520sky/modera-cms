<?php
declare(strict_types=1);

namespace plugins\cache\service;

use think\Request;

/**
 * 前台中间件本体：命中缓存直接返回，未命中先渲染再落盘
 */
class PageCache
{
    public function handle(Request $request, \Closure $next)
    {
        $cfg = Cache::cfg();

        // 后台的写操作（保存文章/栏目/单页/设置/主题/广告等）一律让页面缓存失效
        if (!$request->isGet()) {
            $response = $next($request);
            if (str_starts_with((string) $request->pathinfo(), 'admin/')) {
                Cache::clear();
            }
            return $response;
        }

        $type = Cache::pageType($request, $cfg);
        if ($type === '') {
            return $next($request);
        }

        $path = '/' . trim((string) $request->pathinfo(), '/');

        $hit = Cache::hit($path, $cfg);
        if ($hit instanceof \think\Response) {
            return $hit;
        }

        $response = $next($request);

        if ($response->getCode() === 200) {
            $html = $response->getContent();
            $ctype = (string) $response->getHeader('Content-Type');
            if (is_string($html) && stripos($ctype, 'text/html') !== false) {
                Cache::put($path, $html, $type);
            }
        }

        $response->header(['X-Page-Cache' => 'MISS']);
        return $response;
    }
}
