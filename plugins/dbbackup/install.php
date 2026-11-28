<?php
/**
 * 数据库备份插件：安装钩子
 * 只准备备份目录，不建表（备份文件存在 runtime/dbbackup 下）。
 */

return \plugins\dbbackup\service\Backup::install();
