<?php
/**
 * 缓存优化插件：卸载钩子
 * 清空页面缓存、删缓存目录、摘掉 .htaccess 里的过期头区块、清空插件配置。
 */

return \plugins\cache\service\Cache::uninstall();
