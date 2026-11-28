<?php
declare(strict_types=1);

namespace plugins\cache\controller;

use plugins\cache\service\Cache;

/**
 * 缓存优化后台：开关设置 / 清理缓存
 */
class Admin extends \app\controller\admin\Base
{
    protected function render(string $tpl, array $data = []): string
    {
        $flash = session('flash_msg');
        if ($flash !== null && $flash !== '') {
            session('flash_msg', null);
        }
        $data['admin']  = $this->admin;
        $data['flash']  = (string) $flash;
        $data['active'] = $data['active'] ?? 'plugin-' . md5('/admin/plugin/cache');
        return \app\service\View::fetch(__DIR__ . '/../view/' . $tpl . '.php', $data);
    }

    public function index()
    {
        $cfg   = Cache::cfg();
        $stats = Cache::stats();
        $ht    = Cache::htaccessPath();
        $hasBlock = false;
        if (is_file($ht)) {
            $hasBlock = strpos((string) file_get_contents($ht), '# BEGIN cache-plugin') !== false;
        }

        $theme = '';
        try {
            $theme = (string) setting('theme', 'default');
        } catch (\Throwable $e) {
        }

        return $this->render('index', [
            'pageTitle' => '缓存优化',
            'cfg'       => $cfg,
            'stats'     => $stats,
            'entries'   => Cache::entries(20),
            'dir'       => Cache::dir(),
            'hasBlock'  => $hasBlock,
            'htWritable' => is_file($ht) && is_writable($ht),
            'theme'     => $theme,
        ]);
    }

    public function save()
    {
        $ttl  = max(1, min(1440, (int) $this->request->post('ttl', 30)));
        $days = max(1, min(3650, (int) $this->request->post('asset_days', 365)));

        $cfg = [
            'home'       => (string) $this->request->post('home', '') === '1' ? '1' : '0',
            'article'    => (string) $this->request->post('article', '') === '1' ? '1' : '0',
            'ttl'        => (string) $ttl,
            'assets'     => (string) $this->request->post('assets', '') === '1' ? '1' : '0',
            'asset_days' => (string) $days,
        ];

        Cache::saveCfg($cfg);
        $log = Cache::syncHtaccess();
        $ret = Cache::clear();

        $msg = '设置已保存；已清空 ' . $ret[0] . ' 个旧缓存文件。' . $log;
        return $this->back('/admin/plugin/cache', $msg);
    }

    public function clear()
    {
        $ret = Cache::clear();
        return $this->back('/admin/plugin/cache', '缓存已清理：删除 ' . $ret[0] . ' 个文件，释放 '
            . self::humanSize($ret[1]));
    }

    public static function humanSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1048576, 2) . ' MB';
    }
}
