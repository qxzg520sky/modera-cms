<?php
/**
 * 在线客服插件：卸载钩子
 * 删掉自己建的三张表（会话、消息、坐席）并清空插件配置，
 * 前台浮窗随之消失，站点回到没装插件的样子。
 */

return \plugins\kefu\service\Kefu::uninstall();
