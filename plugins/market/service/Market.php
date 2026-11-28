<?php
declare(strict_types=1);

namespace plugins\market\service;

use app\service\ModuleMarket;

/**
 * 应用市场：读取远程 catalog.json，按需下载 zip 并交给 ModuleMarket 安装。
 * 主程序不再随仓库附带全部模块包，模块包放在独立的 market 分支。
 */
class Market
{
    public const DEFAULT_CATALOG = 'https://gitee.com/51iqx/modera-cms/raw/market/catalog.json';
    public const DEFAULT_FALLBACK = 'https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/catalog.json';

    /**
     * 读取远程目录
     * @return array{items:array,error:string,source:string,updated_at:string}
     */
    public static function catalog(array $config = []): array
    {
        $urls = [];
        foreach ([
            (string) ($config['catalog_url'] ?? ''),
            (string) ($config['fallback_url'] ?? ''),
        ] as $url) {
            $url = trim($url);
            if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $url)) {
                $urls[] = $url;
            }
        }
        if (!$urls) {
            $urls[] = self::DEFAULT_CATALOG;
        }

        $lastError = '';
        foreach ($urls as $url) {
            [$ok, $body] = self::httpGet($url);
            if (!$ok || $body === '') {
                $lastError = '无法连接：' . $url;
                continue;
            }
            $data = json_decode($body, true);
            if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
                $lastError = '目录格式不正确：' . $url;
                continue;
            }

            $base = (string) ($data['base'] ?? '');
            if ($base === '' || !preg_match('#^https?://#i', $base)) {
                $base = rtrim(dirname($url), '/') . '/';
            }

            $items = [];
            foreach ($data['items'] as $raw) {
                $item = self::normalizeItem((array) $raw, $base);
                if ($item) {
                    $items[] = $item;
                }
            }
            if (!$items) {
                $lastError = '目录里没有可用的模块：' . $url;
                continue;
            }

            return [
                'items'      => $items,
                'error'      => '',
                'source'     => $url,
                'updated_at' => (string) ($data['updated_at'] ?? ''),
            ];
        }

        return ['items' => [], 'error' => $lastError ?: '应用市场暂时不可用', 'source' => $urls[0], 'updated_at' => ''];
    }

    /** 下载并安装一个目录条目 */
    public static function install(array $item, bool $enable = false): array
    {
        $type = (string) ($item['type'] ?? '');
        $name = (string) ($item['name'] ?? '');
        $url  = (string) ($item['url'] ?? '');
        if (!in_array($type, ['theme', 'plugin'], true) || $name === '' || $url === '') {
            return [false, '模块信息不完整'];
        }
        if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url)) {
            return [false, '模块下载地址不合法'];
        }

        $dir = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'market';
        if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
            return [false, '无法创建下载目录，请检查 runtime 写权限'];
        }
        $tmp = $dir . DIRECTORY_SEPARATOR . $type . '-' . $name . '-' . substr(md5($url . microtime(true)), 0, 8) . '.zip';

        [$ok, $body] = self::httpGet($url, 60);
        if (!$ok || $body === '') {
            return [false, '模块包下载失败，请检查服务器网络或下载地址'];
        }
        if (@file_put_contents($tmp, $body) === false) {
            return [false, '模块包保存失败，请检查 runtime 写权限'];
        }

        $expected = strtolower(trim((string) ($item['sha256'] ?? '')));
        if ($expected !== '' && !hash_equals($expected, hash_file('sha256', $tmp))) {
            @unlink($tmp);
            return [false, '模块包校验失败，可能下载不完整'];
        }

        $meta = ModuleMarket::read($tmp);
        if (!$meta || $meta['type'] !== $type || $meta['name'] !== $name) {
            @unlink($tmp);
            return [false, '模块包内容与目录信息不一致'];
        }

        [$installed, $message] = ModuleMarket::install($tmp, $enable);
        @unlink($tmp);
        return [$installed, $message];
    }

    private static function normalizeItem(array $item, string $base): ?array
    {
        $type = (string) ($item['type'] ?? '');
        if (!in_array($type, ['theme', 'plugin'], true)) {
            return null;
        }
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) ($item['name'] ?? ''));
        if ($name === '') {
            return null;
        }
        $file = str_replace('\\', '/', (string) ($item['file'] ?? ''));
        $prefix = $type === 'theme' ? 'themes/' : 'plugins/';
        if ($file === '' || !preg_match('#^' . $prefix . '[A-Za-z0-9_\-]+\.zip$#', $file)) {
            return null;
        }
        $url = trim((string) ($item['url'] ?? ''));
        if ($url === '') {
            $url = rtrim($base, '/') . '/' . $file;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url)) {
            return null;
        }

        return [
            'type'        => $type,
            'name'        => $name,
            'title'       => (string) ($item['title'] ?? $name),
            'version'     => (string) ($item['version'] ?? ''),
            'author'      => (string) ($item['author'] ?? ''),
            'description' => (string) ($item['description'] ?? ''),
            'file'        => $file,
            'url'         => $url,
            'sha256'      => (string) ($item['sha256'] ?? ''),
            'size'        => (int) ($item['size'] ?? 0),
            'requires'    => array_values((array) ($item['requires'] ?? [])),
        ];
    }

    /** @return array{0:bool,1:string} */
    private static function httpGet(string $url, int $timeout = 20): array
    {
        $caFile = self::caFile();
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            $options = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT        => $timeout,
                CURLOPT_USERAGENT      => 'Modera-Market/1.0',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ];
            if ($caFile !== null) {
                $options[CURLOPT_CAINFO] = $caFile;
            }
            curl_setopt_array($ch, $options);
            $body = curl_exec($ch);
            $ok   = $body !== false && (int) curl_getinfo($ch, CURLINFO_HTTP_CODE) >= 200 && (int) curl_getinfo($ch, CURLINFO_HTTP_CODE) < 300;
            curl_close($ch);
            return [$ok, $ok ? (string) $body : ''];
        }

        $context = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'timeout'       => $timeout,
                'user_agent'    => 'Modera-Market/1.0',
                'follow_location' => 1,
                'max_redirects' => 5,
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
                'cafile'           => $caFile,
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        return [$body !== false, $body !== false ? (string) $body : ''];
    }

    /** 优先使用插件自带的 CA 包，其次使用系统常见证书路径 */
    private static function caFile(): ?string
    {
        $candidates = [
            __DIR__ . '/../certs/ca-bundle.crt',
            (string) ini_get('curl.cainfo'),
            (string) ini_get('openssl.cafile'),
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
            (string) (openssl_get_cert_locations()['default_cert_file'] ?? ''),
        ];
        foreach ($candidates as $candidate) {
            if ($candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }
        return null;
    }
}
