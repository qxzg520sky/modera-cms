<?php
// 停用：停止采集和后台入口，保留历史聚合数据；清一次页面缓存，避免缓存里残留 beacon
use plugins\stats\service\Stats;

Stats::clearPageCache();
return ['ok' => true, 'msg' => '访问统计已停用，历史聚合数据保留'];
