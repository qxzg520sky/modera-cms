<?php
// 应用公共文件

if (!function_exists('e')) {
    /** HTML 转义输出 */
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('setting')) {
    /** 读取系统设置 */
    function setting(string $name, $default = null)
    {
        return \app\model\Setting::getValue($name, $default);
    }
}

if (!function_exists('admin_url')) {
    function admin_url(string $path = ''): string
    {
        return '/admin' . ($path === '' ? '' : '/' . ltrim($path, '/'));
    }
}

if (!function_exists('plugin_url')) {
    function plugin_url(string $name, string $path = ''): string
    {
        return '/admin/plugin/' . $name . '/' . ltrim($path, '/');
    }
}

if (!function_exists('arr_get')) {
    function arr_get(array $arr, string $key, $default = null)
    {
        return array_key_exists($key, $arr) ? $arr[$key] : $default;
    }
}

if (!function_exists('json_ok')) {
    function json_ok($data = [], string $msg = 'ok')
    {
        return json(['code' => 0, 'msg' => $msg, 'data' => $data]);
    }
}

if (!function_exists('json_err')) {
    function json_err(string $msg, int $code = 1)
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => null]);
    }
}

if (!function_exists('pager_html')) {
    /** 简易分页条 */
    function pager_html(int $page, int $total, int $size, string $baseUrl): string
    {
        $pages = (int) ceil($total / max(1, $size));
        if ($pages <= 1) {
            return '';
        }
        $sep  = strpos($baseUrl, '?') === false ? '?' : '&';
        $html = '<div class="pager">';
        for ($i = 1; $i <= $pages; $i++) {
            if ($pages > 9 && $i > 2 && $i < $pages - 1 && abs($i - $page) > 1) {
                if ($i === 3) {
                    $html .= '<span>…</span>';
                }
                continue;
            }
            if ($i === $page) {
                $html .= '<span class="cur">' . $i . '</span>';
            } else {
                $html .= '<a href="' . e($baseUrl . $sep . 'page=' . $i) . '">' . $i . '</a>';
            }
        }
        return $html . '</div>';
    }
}

if (!function_exists('is_mobile')) {
    /**
     * 是否手机端访问
     * 优先级：URL 参数 ?mobile=1|0（会记住 30 天） > cookie > User-Agent
     * 说明：手机模板放在 themes/{主题}/mobile/ 下，没有则自动回落 PC 模板
     */
    function is_mobile(): bool
    {
        static $value = null;
        if ($value !== null) {
            return $value;
        }

        $q = $_GET['mobile'] ?? null;
        if ($q === '1' || $q === '0') {
            if (!headers_sent()) {
                setcookie('site_mobile', (string) $q, time() + 86400 * 30, '/');
            }
            return $value = ($q === '1');
        }

        if (!empty($_COOKIE['site_mobile'])) {
            return $value = ($_COOKIE['site_mobile'] === '1');
        }

        $ua = strtolower((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        if ($ua === '') {
            return $value = false;
        }
        // 平板也走手机模板（竖屏居多），可按需去掉 ipad
        return $value = (bool) preg_match('/(iphone|ipod|ipad|android|mobile|windows phone|blackberry|opera mini|iemobile|micromessenger)/', $ua);
    }
}

if (!function_exists('device_type')) {
    /** 当前设备：mobile / pc */
    function device_type(): string
    {
        return is_mobile() ? 'mobile' : 'pc';
    }
}

/* ===================== 多语言（由插件 plugins/multilang 提供） =====================
 * 说明：这几个函数属于“内置能力”，插件卸载后依然存在但会退化为纯中文，不会报错。
 * 语言通过 URL 前缀区分，例如 /en/ 、/en/c/product ；默认语言（zh-cn）不带前缀。
 */

if (!function_exists('multilang_config')) {
    function multilang_config(): array
    {
        static $cfg = null;
        if ($cfg !== null) {
            return $cfg;
        }

        $cfg = ['enabled' => false, 'default' => 'zh-cn', 'langs' => [], 'names' => []];

        try {
            $row = \think\facade\Db::name('plugin')->where('name', 'multilang')->find();
            if ($row && (int) $row['enabled'] === 1) {
                $c = json_decode((string) $row['config'], true) ?: [];
                $cfg['enabled'] = true;
                $cfg['default'] = strtolower((string) ($c['default_lang'] ?? 'zh-cn'));

                $names = (array) ($c['names'] ?? []);
                $rawLangs = trim((string) ($c['langs'] ?? ''));
                foreach (preg_split('/[,\s]+/', $rawLangs) as $code) {
                    $code = strtolower(trim($code));
                    if ($code === '' || $code === $cfg['default']) {
                        continue;
                    }
                    if (!preg_match('/^[a-z]{2,3}(_[a-z]{2,4})?$/', $code)) {
                        continue;
                    }
                    $cfg['langs'][] = $code;
                    $cfg['names'][$code] = $names[$code] ?? strtoupper($code);
                }
            }
        } catch (\Throwable $e) {
            $cfg = ['enabled' => false, 'default' => 'zh-cn', 'langs' => [], 'names' => []];
        }

        return $cfg;
    }
}

if (!function_exists('current_lang')) {
    /** 当前语言代码，按 URL 前缀判断 */
    function current_lang(): string
    {
        static $lang = null;
        if ($lang !== null) {
            return $lang;
        }

        $cfg = multilang_config();
        if (!$cfg['enabled'] || !$cfg['langs']) {
            return $lang = $cfg['default'] ?: 'zh-cn';
        }

        $path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = '/' . ltrim($path, '/');

        foreach ($cfg['langs'] as $code) {
            if ($path === '/' . $code || strpos($path, '/' . $code . '/') === 0) {
                return $lang = $code;
            }
        }
        return $lang = $cfg['default'];
    }
}

if (!function_exists('lang')) {
    /** 界面文案翻译：中文原文作为 key，找不到词条就原样返回 */
    function lang(string $key, ?string $fallback = null): string
    {
        $cfg  = multilang_config();
        $code = current_lang();

        if (!$cfg['enabled'] || $code === $cfg['default']) {
            return $key;
        }

        static $dict = [];
        if (!isset($dict[$code])) {
            $file        = root_path() . 'plugins/multilang/lang/' . $code . '.php';
            $dict[$code] = is_file($file) ? (array) include $file : [];
        }

        return (string) ($dict[$code][$key] ?? ($fallback ?? $key));
    }
}

if (!function_exists('ml')) {
    /** 取多语言字段：当前语言该字段有值就用，没有回落中文 */
    function ml(array $row, string $field, $default = '')
    {
        $cfg  = multilang_config();
        $code = current_lang();

        if ($cfg['enabled'] && $code !== $cfg['default']) {
            $key = $field . '_' . str_replace('-', '_', $code);
            if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
                return $row[$key];
            }
        }

        return $row[$field] ?? $default;
    }
}

if (!function_exists('lang_path')) {
    /** 给内部链接加语言前缀（默认语言不加） */
    function lang_path(string $path = '/'): string
    {
        $cfg  = multilang_config();
        $code = current_lang();

        if (!$cfg['enabled'] || !$cfg['langs'] || $code === $cfg['default']) {
            return $path;
        }

        if ($path === '/' || $path === '') {
            return '/' . $code . '/';
        }

        if (strpos($path, 'http') === 0) {
            return $path;
        }

        return '/' . $code . '/' . ltrim($path, '/');
    }
}

if (!function_exists('lang_url')) {
    /** 当前页面在指定语言下的地址（用于语言切换） */
    function lang_url(string $code): string
    {
        $cfg     = multilang_config();
        $default = $cfg['default'] ?: 'zh-cn';

        $uri   = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $parts = explode('?', $uri, 2);
        $path  = $parts[0] === '' ? '/' : $parts[0];
        $qs    = $parts[1] ?? '';

        foreach ($cfg['langs'] as $c) {
            if ($path === '/' . $c || strpos($path, '/' . $c . '/') === 0) {
                $path = substr($path, strlen('/' . $c));
                if ($path === '' || $path === false) {
                    $path = '/';
                }
                break;
            }
        }

        if ($code !== $default) {
            $path = '/' . $code . ($path === '/' ? '/' : $path);
        }

        return $path . ($qs !== '' ? '?' . $qs : '');
    }
}

if (!function_exists('lang_switcher')) {
    /** 语言切换器 HTML，未启用多语言时返回空串 */
    function lang_switcher(string $class = 'lang-switch'): string
    {
        $cfg = multilang_config();
        if (!$cfg['enabled'] || !$cfg['langs']) {
            return '';
        }

        $cur   = current_lang();
        $items = [['code' => $cfg['default'], 'name' => '中文']];
        foreach ($cfg['langs'] as $c) {
            $items[] = ['code' => $c, 'name' => $cfg['names'][$c] ?? strtoupper($c)];
        }

        $html = '<div class="' . $class . '">';
        foreach ($items as $item) {
            $html .= '<a class="' . ($cur === $item['code'] ? 'active' : '') . '" href="'
                . e(lang_url($item['code'])) . '" hreflang="' . e($item['code']) . '">'
                . e($item['name']) . '</a>';
        }
        return $html . '</div>';
    }
}

if (!function_exists('rich_editor')) {
    /**
     * 富文本编辑器（wangEditor 5，本地文件，不依赖外网）
     * $name 表单字段名，$value 初始 HTML。页面里可多次调用，库只加载一次。
     */
    function rich_editor(string $name, string $value = '', string $height = '460px'): string
    {
        static $loaded = false;
        static $seq    = 0;
        $seq++;
        $id = 're' . $seq . '_' . preg_replace('/\W/', '_', $name);

        $out = '';
        if (!$loaded) {
            $loaded = true;
            $out .= '<link rel="stylesheet" href="/static/wangeditor/style.css">';
            $out .= '<script src="/static/wangeditor/index.js"></script>';
            $out .= '<style>.re-box{border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;background:#fff}'
                . '.re-toolbar{border-bottom:1px solid #e5e7eb;background:#fafbfc}'
                . '.re-body{overflow-y:auto}.re-body .w-e-text-container{background:#fff}'
                . '.re-hint{font-size:12px;color:#9aa3af;margin:6px 0 0}</style>';
        }

        $out .= '<div class="re-box"><div id="' . $id . '_tb" class="re-toolbar"></div>'
            . '<div id="' . $id . '_ed" class="re-body" style="height:' . e($height) . '"></div></div>'
            . '<div class="re-hint">支持加粗、标题、列表、表格、链接，图片可直接上传或粘贴网络地址</div>'
            . '<textarea name="' . e($name) . '" id="' . $id . '_ta" style="display:none">' . e($value) . '</textarea>';

        $js = 'var E=window.wangEditor,ed=document.getElementById("' . $id . '_ed"),ta=document.getElementById("' . $id . '_ta");'
            . 'if(!E||!ed||!ta)return;'
            . 'var editor=E.createEditor({selector:"#' . $id . '_ed",html:ta.value||"<p><br></p>",config:{placeholder:"在这里写正文…",MENU_CONF:{uploadImage:{customUpload:function(file,insertFn){var f=new FormData();f.append("file",file);fetch("/admin/upload",{method:"POST",body:f,credentials:"same-origin"}).then(function(r){return r.json()}).then(function(j){if(j&&j.errno===0&&j.data&&j.data.url){insertFn(j.data.url,j.data.alt||"",j.data.href||"")}else{alert((j&&j.message)||"图片上传失败")}}).catch(function(){alert("图片上传失败")})}}}}});'
            . 'E.createToolbar({editor:editor,selector:"#' . $id . '_tb",config:{excludeKeys:["fullScreen"]}});'
            . 'if(ta.form){ta.form.addEventListener("submit",function(){ta.value=editor.getHtml()})}';

        return $out . '<script>(function(){' . $js . '})();</script>';
    }
}

if (!function_exists('plugin_hook')) {
    /**
     * 前台插件钩子：主题模板里写 <?= plugin_hook('front_footer') ?>
     * 遍历已启用的插件，执行 plugins/{插件名}/hook/{钩子}.php 的输出。
     * 插件停用或卸载后这里返回空字符串，前台不留任何痕迹。
     */
    function plugin_hook(string $point): string
    {
        $out = '';
        try {
            foreach (\app\service\PluginManager::enabled() as $plugin) {
                $file = root_path() . 'plugins/' . $plugin . '/hook/' . $point . '.php';
                if (!is_file($file)) {
                    continue;
                }
                ob_start();
                include $file;
                $out .= (string) ob_get_clean();
            }
        } catch (\Throwable $e) {
            $out = '';
        }
        return $out;
    }
}

if (!function_exists('plugin_filter')) {
    /**
     * 前台插件过滤钩子：主题里写 plugin_filter('home_slides', $默认数据)
     * 遍历已启用的插件，执行 plugins/{插件名}/hook/filter_{钩子}.php，
     * 钩子文件里可以直接用 $value，并 return 修改后的值；return 空数组/空值则保留原值。
     * 没有插件接管时原样返回，主题不受影响。
     */
    function plugin_filter(string $point, $value)
    {
        try {
            foreach (\app\service\PluginManager::enabled() as $plugin) {
                $file = root_path() . 'plugins/' . $plugin . '/hook/filter_' . $point . '.php';
                if (!is_file($file)) {
                    continue;
                }
                $ret = include $file;
                if (is_array($ret) && $ret) {
                    $value = $ret;
                }
            }
        } catch (\Throwable $e) {
        }
        return $value;
    }
}

if (!function_exists('plugin_badge')) {
    /**
     * 后台插件菜单角标：插件在 hook/admin_badge.php 里 echo 一个数字即可。
     * 传入当前菜单网址，插件据此决定要不要出数字。
     */
    function plugin_badge(string $menuUrl): int
    {
        $n = 0;
        try {
            foreach (\app\service\PluginManager::enabled() as $plugin) {
                $file = root_path() . 'plugins/' . $plugin . '/hook/admin_badge.php';
                if (!is_file($file)) {
                    continue;
                }
                ob_start();
                include $file;
                $out = trim((string) ob_get_clean());
                if ($out !== '' && ctype_digit($out)) {
                    $n += (int) $out;
                }
            }
        } catch (\Throwable $e) {
        }
        return $n;
    }
}
