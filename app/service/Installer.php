<?php
declare(strict_types=1);

namespace app\service;

use app\model\Admin;
use think\facade\Db;

/**
 * 安装器：环境检测 + 写 .env + 建表 + 最小初始数据（默认模板 + 演示插件）。
 * 首次安装只装「默认模板 default」与「演示插件 example」，其他主题/插件一律通过
 * 后台「模块中心」下载/上传 zip 包后再安装、启用。
 */
class Installer
{
    /** 安装锁文件 */
    public static function lockPath(): string
    {
        return root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'install.lock';
    }

    public static function isInstalled(): bool
    {
        if (is_file(self::lockPath())) {
            return true;
        }
        static $installed = null;
        if ($installed !== null) {
            return $installed;
        }
        try {
            $rows      = Db::query('SHOW TABLES LIKE \'' . self::prefix() . 'admin\'');
            $installed = !empty($rows);
        } catch (\Throwable $e) {
            $installed = false;
        }
        return $installed;
    }

    /** 写入安装锁（可附带安装时间、版本） */
    public static function markInstalled(array $data = []): void
    {
        $dir = dirname(self::lockPath());
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $payload = array_merge([
            'installed_at' => date('Y-m-d H:i:s'),
            'version'      => '1.0.0',
            'php'          => PHP_VERSION,
        ], $data);
        @file_put_contents(self::lockPath(), json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /** 移除安装锁（仅用于本地调试/重装） */
    public static function clearLock(): void
    {
        if (is_file(self::lockPath())) {
            @unlink(self::lockPath());
        }
    }

    public static function prefix(): string
    {
        try {
            return (string) Db::connect('mysql')->getConfig('prefix');
        } catch (\Throwable $e) {
            return 'tp_';
        }
    }

    /** 安装环境检测，返回 [['label'=>..,'ok'=>bool,'need'=>bool,'value'=>..]] */
    public static function checks(): array
    {
        $root = root_path();
        $items = [];
        $items[] = [
            'label' => 'PHP 版本 ≥ 8.0',
            'ok'    => version_compare(PHP_VERSION, '8.0.0', '>='),
            'need'  => true,
            'value' => PHP_VERSION,
        ];
        $items[] = [
            'label' => 'PDO MySQL 扩展',
            'ok'    => extension_loaded('pdo_mysql'),
            'need'  => true,
            'value' => extension_loaded('pdo_mysql') ? '已安装' : '未安装',
        ];
        $items[] = [
            'label' => 'MBstring 扩展',
            'ok'    => extension_loaded('mbstring'),
            'need'  => true,
            'value' => extension_loaded('mbstring') ? '已安装' : '未安装',
        ];
        $items[] = [
            'label' => 'Zip 扩展（模块安装）',
            'ok'    => class_exists('ZipArchive'),
            'need'  => true,
            'value' => class_exists('ZipArchive') ? '已安装' : '未安装',
        ];
        foreach ([
            'runtime 目录可写' => $root . 'runtime',
            'themes 目录可写'  => $root . 'themes',
            'plugins 目录可写' => $root . 'plugins',
            'modules 目录可写' => $root . 'modules',
            '站点根目录可写（写 .env）' => $root,
        ] as $label => $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0777, true);
            }
            $items[] = [
                'label' => $label,
                'ok'    => is_dir($path) && is_writable($path),
                'need'  => true,
                'value' => is_dir($path) && is_writable($path) ? '可写' : '不可写',
            ];
        }
        return $items;
    }

    /** 是否有致命项不通过 */
    public static function checksFailed(array $checks): bool
    {
        foreach ($checks as $c) {
            if (!empty($c['need']) && empty($c['ok'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * 用 PDO 测试数据库连接，可选自动建库。
     * $db: host, port, name, user, pass, prefix
     * @return string 提示信息
     */
    public static function prepareDatabase(array $db, bool $create = true): string
    {
        $host = $db['host'] ?: '127.0.0.1';
        $port = (int) ($db['port'] ?: 3306);
        $name = (string) $db['name'];
        $user = (string) $db['user'];
        $pass = (string) $db['pass'];

        if ($name === '' || !preg_match('/^[A-Za-z0-9_\-]+$/', $name)) {
            throw new \RuntimeException('数据库名只能包含字母、数字、下划线和短横线');
        }

        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';charset=utf8mb4';
        try {
            $pdo = new \PDO($dsn, $user, $pass, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 5]);
        } catch (\Throwable $e) {
            throw new \RuntimeException('数据库连接失败：' . $e->getMessage());
        }

        $exists = false;
        foreach ($pdo->query('SHOW DATABASES')->fetchAll(\PDO::FETCH_COLUMN) as $row) {
            if (strcasecmp((string) $row, $name) === 0) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            if (!$create) {
                throw new \RuntimeException('数据库 ' . $name . ' 不存在，且未勾选自动创建');
            }
            $pdo->exec('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
            $exists = true;
        }

        // 真正连接该库，确认可用
        $pdo2 = new \PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4', $user, $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 5,
        ]);
        $pdo2->query('SELECT 1');

        return $exists ? '数据库连接成功' : '';
    }

    /** 写 .env（保留其它键，只覆盖数据库相关与基础项） */
    public static function writeEnv(array $db): void
    {
        $path = root_path() . '.env';
        $lines = [];
        if (is_file($path)) {
            $lines = preg_split('/\r\n|\r|\n/', (string) file_get_contents($path)) ?: [];
        }

        $values = [
            'APP_DEBUG'        => 'false',
            'DB_TYPE'          => 'mysql',
            'DB_HOST'          => (string) ($db['host'] ?: '127.0.0.1'),
            'DB_NAME'          => (string) $db['name'],
            'DB_USER'          => (string) $db['user'],
            'DB_PASS'          => (string) $db['pass'],
            'DB_PORT'          => (string) ($db['port'] ?: '3306'),
            'DB_CHARSET'       => 'utf8mb4',
            'DB_PREFIX'        => (string) ($db['prefix'] ?? 'tp_'),
            'DEFAULT_LANG'     => 'zh-cn',
            'DEFAULT_TIMEZONE' => 'Asia/Shanghai',
        ];

        $seen = [];
        foreach ($lines as $i => $line) {
            if (preg_match('/^\s*([A-Za-z0-9_]+)\s*=/', $line, $m) && array_key_exists($m[1], $values)) {
                $seen[$m[1]] = true;
                $lines[$i] = $m[1] . ' = ' . $values[$m[1]];
            }
        }
        $append = [];
        foreach ($values as $k => $v) {
            if (!isset($seen[$k])) {
                $append[] = $k . ' = ' . $v;
            }
        }
        if ($append) {
            $lines[] = '';
            foreach ($append as $line) {
                $lines[] = $line;
            }
        }

        $content = trim(implode("\n", $lines)) . "\n";
        if (@file_put_contents($path, $content) === false) {
            throw new \RuntimeException('.env 写入失败，请检查站点根目录写权限');
        }
    }

    /** 让当前请求的数据库连接指向新装的库（.env 是给后续请求用的） */
    public static function useDatabase(array $db): void
    {
        $conn = [
            'type'            => 'mysql',
            'hostname'        => (string) ($db['host'] ?: '127.0.0.1'),
            'database'        => (string) $db['name'],
            'username'        => (string) $db['user'],
            'password'        => (string) $db['pass'],
            'hostport'        => (string) ($db['port'] ?: '3306'),
            'params'          => [],
            'charset'         => 'utf8mb4',
            'prefix'          => (string) ($db['prefix'] ?? 'tp_'),
            'deploy'          => 0,
            'rw_separate'     => false,
            'master_num'      => 1,
            'slave_no'        => '',
            'fields_strict'   => true,
            'break_reconnect' => false,
            'trigger_sql'     => false,
            'fields_cache'    => false,
        ];
        \think\facade\Config::set([
            'default'         => 'mysql',
            'time_query_rule' => [],
            'auto_timestamp'  => true,
            'datetime_format' => 'Y-m-d H:i:s',
            'connections'     => ['mysql' => $conn],
        ], 'database');
        Db::connect('mysql', true);
    }

    /** 建表语句 */
    public static function tables(): array
    {
        $p = self::prefix();
        return [
"CREATE TABLE IF NOT EXISTS `{$p}admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `nickname` varchar(50) NOT NULL DEFAULT '',
  `status` tinyint(3) NOT NULL DEFAULT '1',
  `last_login_time` datetime DEFAULT NULL,
  `last_login_ip` varchar(50) NOT NULL DEFAULT '',
  `create_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

"CREATE TABLE IF NOT EXISTS `{$p}setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` text,
  `group` varchar(50) NOT NULL DEFAULT 'site',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

"CREATE TABLE IF NOT EXISTS `{$p}category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `slug` varchar(100) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT 'list',
  `link` varchar(255) NOT NULL DEFAULT '',
  `template` varchar(50) NOT NULL DEFAULT '',
  `sort` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT '1',
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '',
  `seo_description` varchar(255) NOT NULL DEFAULT '',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

"CREATE TABLE IF NOT EXISTS `{$p}article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL DEFAULT '',
  `slug` varchar(120) NOT NULL DEFAULT '',
  `cover` varchar(255) NOT NULL DEFAULT '',
  `summary` varchar(500) NOT NULL DEFAULT '',
  `content` longtext,
  `author` varchar(50) NOT NULL DEFAULT '',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `sort` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT '1',
  `is_top` tinyint(3) NOT NULL DEFAULT '0',
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '',
  `seo_description` varchar(255) NOT NULL DEFAULT '',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

"CREATE TABLE IF NOT EXISTS `{$p}page` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(200) NOT NULL DEFAULT '',
  `content` longtext,
  `template` varchar(50) NOT NULL DEFAULT '',
  `status` tinyint(3) NOT NULL DEFAULT '1',
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '',
  `seo_description` varchar(255) NOT NULL DEFAULT '',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

"CREATE TABLE IF NOT EXISTS `{$p}plugin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `version` varchar(30) NOT NULL DEFAULT '',
  `author` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(500) NOT NULL DEFAULT '',
  `enabled` tinyint(3) NOT NULL DEFAULT '0',
  `config` text,
  `sort` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

"CREATE TABLE IF NOT EXISTS `{$p}theme` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `version` varchar(30) NOT NULL DEFAULT '',
  `author` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(500) NOT NULL DEFAULT '',
  `screenshot` varchar(255) NOT NULL DEFAULT '',
  `enabled` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
        ];
    }

    /** 执行安装（最小化：默认模板 + 演示插件） */
    public static function install(array $data): array
    {
        foreach (self::tables() as $sql) {
            Db::execute($sql);
        }

        $username = trim((string) $data['username']);
        $password = (string) $data['password'];
        $siteName = trim((string) ($data['site_name'] ?? '我的企业网站'));

        // 管理员
        if (!Db::name('admin')->where('username', $username)->find()) {
            Db::name('admin')->insert([
                'username'    => $username,
                'password'    => Admin::hashPassword($password),
                'nickname'    => $data['nickname'] ?? $username,
                'status'      => 1,
                'create_time' => date('Y-m-d H:i:s'),
            ]);
        }

        // 默认配置
        $defaults = [
            'site_name'        => $siteName,
            'site_slogan'      => (string) ($data['site_slogan'] ?? '专业 · 高效 · 值得信赖'),
            'site_keywords'    => '企业网站,企业官网,企业管理系统',
            'site_description' => $siteName . '官方网站',
            'site_logo'        => '',
            'site_icp'         => '',
            'site_copyright'   => '© ' . date('Y') . ' ' . $siteName,
            'site_phone'       => '400-000-0000',
            'site_email'       => 'admin@example.com',
            'site_address'     => '',
            'theme'            => 'default',
            'article_page_size' => '10',
        ];
        foreach ($defaults as $k => $v) {
            if (!Db::name('setting')->where('name', $k)->find()) {
                Db::name('setting')->insert(['name' => $k, 'value' => (string) $v, 'group' => 'site']);
            }
        }

        // 栏目
        $cats = [
            ['name' => '新闻中心', 'slug' => 'news',    'sort' => 1],
            ['name' => '产品中心', 'slug' => 'product', 'sort' => 2],
            ['name' => '人才招聘', 'slug' => 'jobs',    'sort' => 3],
        ];
        foreach ($cats as $cat) {
            if (!Db::name('category')->where('slug', $cat['slug'])->find()) {
                Db::name('category')->insert($cat + [
                    'pid' => 0, 'type' => 'list', 'link' => '', 'template' => '',
                    'status' => 1, 'create_time' => date('Y-m-d H:i:s'), 'update_time' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // 单页
        $pages = [
            ['slug' => 'about',   'title' => '关于我们', 'content' => '<p>这里是关于我们页面，请在后台“单页管理”中修改内容。</p>'],
            ['slug' => 'contact', 'title' => '联系我们', 'content' => '<p>地址：请在后台填写<br>电话：400-000-0000<br>邮箱：admin@example.com</p>'],
        ];
        foreach ($pages as $page) {
            if (!Db::name('page')->where('slug', $page['slug'])->find()) {
                Db::name('page')->insert($page + [
                    'template' => '', 'status' => 1, 'seo_title' => $page['title'],
                    'seo_keywords' => '', 'seo_description' => '',
                    'create_time' => date('Y-m-d H:i:s'), 'update_time' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // 示例文章
        if (!Db::name('article')->count()) {
            $catId = (int) Db::name('category')->where('slug', 'news')->value('id');
            Db::name('article')->insert([
                'category_id' => $catId,
                'title'       => '欢迎使用企业管理系统',
                'slug'        => 'welcome',
                'summary'     => '这是系统自动创建的示例文章，可在后台文章管理中编辑或删除。',
                'content'     => '<p>系统已经准备就绪。你可以：</p><ul><li>在“内容管理”里发布栏目与文章</li><li>在“外观模板”里切换主题</li><li>在“插件扩展”里开启多语言、商城等插件</li><li>在“系统设置”里修改站点信息</li></ul>',
                'author'      => 'admin',
                'status'      => 1,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ]);
        }

        // 只注册磁盘上真实存在的主题与插件（首次安装时磁盘上只有 default 与 example）
        ThemeManager::scan();
        PluginManager::scan();

        // 启用默认模板 + 演示插件，其余模块留待后台「模块中心」安装
        ThemeManager::enable('default');
        if (is_dir(root_path() . 'plugins/example')) {
            PluginManager::toggle('example', 1);
        }

        self::markInstalled(['username' => $username, 'site_name' => $siteName]);

        return ['username' => $username];
    }
}
