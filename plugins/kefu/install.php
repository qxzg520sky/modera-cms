<?php
/**
 * 在线客服插件：安装钩子（启用插件时执行）
 * 建三张表 + 默认坐席 + 默认设置，重复启用不会重复插入。
 */

return \plugins\kefu\service\Kefu::install();
