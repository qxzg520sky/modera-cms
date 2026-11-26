<?php
/**
 * 数据库备份插件：卸载钩子
 * 删掉 runtime/dbbackup 下的所有备份文件与状态文件，清空插件配置。
 * 注意：备份文件属于本插件产物，卸载即删除；如需保留请先下载到本地。
 */

return \plugins\dbbackup\service\Backup::uninstall();
