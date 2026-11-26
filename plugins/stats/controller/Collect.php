<?php
declare(strict_types=1);

namespace plugins\stats\controller;

use plugins\stats\service\Stats;

/**
 * 前台采集端点：接收一个很小的 beacon，写入聚合表。
 * 只接受 POST，不做任何页面渲染，出错也返回 200，避免影响访客。
 */
class Collect
{
    public function collect()
    {
        $request = request();

        if (!$request->isPost()) {
            return json(['ok' => 0]);
        }

        $cfg = Stats::cfg();
        if ((string) $cfg['dnt'] === '1' && (string) $request->header('dnt', '') === '1') {
            return json(['ok' => 1, 'skip' => 'dnt']);
        }

        $ua = (string) $request->header('user-agent', '');
        if ($ua === '' || preg_match('/(bot|crawler|spider|slurp|bingpreview|headless|phantom|python-requests|curl|wget|facebookexternalhit|whatsapp|telegrambot|semrush|ahrefs|mj12|dotbot|petalbot|bytespider)/i', $ua)) {
            return json(['ok' => 1, 'skip' => 'bot']);
        }

        $rawPath = (string) $request->post('p', '/');
        $path = parse_url($rawPath, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return json(['ok' => 0]);
        }
        $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
        $path = preg_replace('#/+#', '/', $path) ?: '/';
        if (strlen($path) > 1) {
            $path = rtrim($path, '/');
        }
        if (strlen($path) > 160 || preg_match('/[\x00-\x1F\x7F]/', $path)) {
            return json(['ok' => 0]);
        }
        if (preg_match('#^/(admin|install|api|stats|uploads|static|themes|favicon|robots)#i', $path)) {
            return json(['ok' => 1, 'skip' => 'path']);
        }

        $title = (string) $request->post('t', '');
        $title = html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $title = trim((string) preg_replace('/\s+/u', ' ', $title));
        if (function_exists('mb_substr')) {
            $title = mb_substr($title, 0, 150, 'UTF-8');
        } else {
            $title = substr($title, 0, 150);
        }

        $referrer = trim((string) $request->post('r', ''));
        $referrer = mb_substr($referrer, 0, 255, 'UTF-8');
        $refHost = '';
        if ($referrer !== '') {
            $host = parse_url($referrer, PHP_URL_HOST);
            $refHost = is_string($host) ? strtolower($host) : '';
            $selfHost = strtolower((string) parse_url('http://' . $request->host(), PHP_URL_HOST));
            if ($refHost !== '' && $selfHost !== '' && $refHost === $selfHost) {
                $refHost = '内部跳转';
            }
        }
        if ($refHost === '') {
            $refHost = '直接访问';
            $referrer = '';
        }

        $type = 'other';
        if ($path === '/' || $path === '/en') {
            $type = 'home';
        } elseif (preg_match('#^(?:/en)?/a/\d+#', $path)) {
            $type = 'article';
        } elseif (preg_match('#^(?:/en)?/c/#', $path)) {
            $type = 'category';
        } elseif (preg_match('#^(?:/en)?/p/#', $path)) {
            $type = 'page';
        } elseif ($path === '/search' || $path === '/en/search') {
            $type = 'search';
        }

        $day = date('Y-m-d');
        $vid = (string) $request->cookie('modera_vid', '');
        if (!preg_match('/^[a-f0-9]{16}$/', $vid)) {
            $vid = substr(hash('sha256', $request->ip() . '|' . $ua . '|' . $cfg['salt'] . '|' . $day), 0, 16);
        }

        Stats::record([
            'day'           => $day,
            'path'          => $path,
            'title'         => $title,
            'page_type'     => $type,
            'visitor'       => $vid,
            'referrer'      => $referrer,
            'referrer_host' => $refHost,
            'now'           => time(),
        ]);

        return json(['ok' => 1])->cookie('modera_vid', $vid, [
            'expire'   => 31536000,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
