<?php
/**
 * 缓存优化插件：安装钩子
 * 建缓存目录，并把静态资源过期头写进 public/.htaccess（独立区块，可整块移除）。
 */

return \plugins\cache\service\Cache::install();
