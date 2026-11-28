<?php
// 安装：建表 + 写入默认配置
use plugins\stats\service\Stats;

Stats::install();
Stats::saveCfg(Stats::cfg());
return ['ok' => true, 'msg' => '访问统计已安装：三张聚合表已创建，前台 beacon 开始采集'];
