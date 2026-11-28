<?php
/**
 * 缓存优化插件：停用钩子
 * 停用即清空页面缓存、摘掉 .htaccess 里的静态资源过期头，前台立刻回到原生 TP8 行为；
 * 配置保留，下次启用可直接接上。
 */

return \plugins\cache\service\Cache::disableCleanup();
