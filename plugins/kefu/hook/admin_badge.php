<?php
/**
 * 后台菜单角标：只有「客服会话」这个菜单显示待回复条数。
 * 由 plugin_badge('/admin/plugin/kefu/chat') 调用，echo 一个数字即可。
 */
if (($menuUrl ?? '') === '/admin/plugin/kefu/chat') {
    $kfStats = \plugins\kefu\service\Kefu::stats();
    echo (string) (int) $kfStats['unread'];
}
