<?php
// 卸载：删除三张统计表和配置（数据不可恢复）
use plugins\stats\service\Stats;

Stats::uninstall();
return ['ok' => true, 'msg' => '访问统计已卸载，统计表与配置已删除'];
