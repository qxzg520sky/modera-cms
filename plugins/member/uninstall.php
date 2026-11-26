<?php
/**
 * 企业会员管理插件：卸载钩子
 * 删除会员相关 6 张表和插件配置。商城订单表不归本插件管，不会动。
 */

return \plugins\member\service\Member::uninstall();
