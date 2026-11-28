<?php
/**
 * 企业商城插件：卸载钩子
 * 删除全部 shop_* 表，不触碰会员插件数据。
 */

return \plugins\shop\service\Shop::uninstall();
