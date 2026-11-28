<?php
/**
 * 轮播广告插件：卸载钩子
 * 删广告表（广告数据属于本插件，卸载即删除）、清空插件配置。
 */

return \plugins\slider\service\Slider::uninstall();
