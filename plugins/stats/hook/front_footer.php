<?php
/**
 * 前台页脚钩子：注入统计 beacon。
 * 只有几行 JS，不依赖 jQuery；页面被 cache 插件缓存后依然会在浏览器执行。
 */
use plugins\stats\service\Stats;

$statsCfg = Stats::cfg();
$respectDnt = (string) ($statsCfg['dnt'] ?? '1') === '1';
?>
<script>
(function () {
  try {
    var dnt = navigator.doNotTrack || window.doNotTrack || navigator.msDoNotTrack;
    if (<?= $respectDnt ? 'true' : 'false' ?> && (dnt === '1' || dnt === 'yes')) return;
    var path = location.pathname + location.search;
    if (path.length > 160) return;
    var body = new URLSearchParams();
    body.append('p', path);
    body.append('t', (document.title || '').slice(0, 150));
    body.append('r', document.referrer ? document.referrer.slice(0, 255) : '');
    var url = '/stats/collect';
    if (navigator.sendBeacon && navigator.sendBeacon(url, body)) return;
    if (window.fetch) {
      fetch(url, {
        method: 'POST',
        body: body,
        credentials: 'same-origin',
        keepalive: true
      }).catch(function () {});
    }
  } catch (e) {}
})();
</script>
