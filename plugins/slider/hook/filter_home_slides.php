<?php
/**
 * 前台过滤钩子：主题首页轮播数据源
 * $value 是主题自带的轮播数据；插件启用且配置了广告时返回广告，否则原样返回。
 */

$items = \plugins\slider\service\Slider::front();

return $items ? $items : $value;
