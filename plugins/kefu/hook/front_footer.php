<?php
/**
 * 前台钩子：在线客服浮窗
 * 由主题模板 _bottom.php 里的 plugin_hook('front_footer') 调用。
 * 插件停用或卸载后这里不再被 include，前台连一行 output 都没有。
 *
 * 样式和脚本直接内联输出（不额外请求 /kefu/asset/*）：
 * 这样不管站点是根目录、子目录、还是换了域名/端口访问，浮窗都能正常排版，不会出现“裸 HTML”。
 */

use plugins\kefu\service\Kefu;

$kf = Kefu::cfg();
if ((string) ($kf['enabled'] ?? '1') !== '1') {
    return;
}

$online = Kefu::onlineNow($kf);
$staff  = (string) ($kf['show_staff'] ?? '1') === '1' ? Kefu::staff(true) : [];

// 站点可能挂在子目录（例如 /tempqiyecms/public），按当前入口脚本算出前缀，
// 让前端拼出来的 /kefu/api 地址在任何部署路径下都能对上。
$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$base       = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($base === '.' || $base === '/') {
    $base = '';
}

$payload = [
    'api'      => $base . '/kefu/api',
    'asset'    => $base . '/kefu/asset',
    'online'   => $online,
    'theme'    => (string) $kf['theme'],
    'position' => (string) $kf['position'],
    'gap'      => (int) $kf['float_gap'],
    'title'    => (string) $kf['title'],
    'subtitle' => (string) $kf['subtitle'],
    'welcome'  => (string) $kf['welcome'],
    'offline'  => (string) $kf['offline_tip'],
    'auto'     => '',
    'allowChat'   => (string) $kf['allow_chat'] === '1',
    'needContact' => (string) $kf['require_contact'] === '1',
    'poll'     => max(3, (int) $kf['poll']),
    'maxLen'   => max(80, (int) $kf['max_len']),
    'guest'    => (string) $kf['guest_default'],
    'showQr'   => (string) $kf['show_qr'] === '1',
    'btnText'  => (string) $kf['btn_text'],
    'btnIcon'  => (string) $kf['btn_icon'],
    'page'     => (string) ($_SERVER['REQUEST_URI'] ?? '/'),
    'staff'    => array_map(static function ($s) {
        return [
            'id'      => (int) $s['id'],
            'name'    => (string) $s['name'],
            'title'   => (string) $s['title'],
            'type'    => (string) $s['type'],
            'account' => (string) $s['account'],
            'qr'      => (string) $s['qr'],
            'avatar'  => (string) $s['avatar'],
            'intro'   => (string) $s['intro'],
        ];
    }, $staff),
];

$cssFile = __DIR__ . '/../asset/kefu.css';
$jsFile  = __DIR__ . '/../asset/kefu.js';
$css     = is_file($cssFile) ? (string) @file_get_contents($cssFile) : '';
$js      = is_file($jsFile) ? (string) @file_get_contents($jsFile) : '';
?>
<?php if ($css !== ''): ?>
<style id="kefuStyle"><?= $css ?></style>
<?php endif; ?>
<div id="kefuRoot" class="kefu-root kefu-<?= e($payload['position']) ?>" aria-live="polite"></div>
<script id="kefuData" type="application/json"><?= json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?></script>
<?php if ($js !== ''): ?>
<script id="kefuScript"><?= $js ?></script>
<?php endif; ?>
