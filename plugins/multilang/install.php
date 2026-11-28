<?php
/**
 * 多语言插件：安装钩子（启用插件时由 PluginManager::install 执行）
 * 给配置里已有的每种外语创建字段。
 */

$codes = \plugins\multilang\service\Lang::codes();

if (!$codes) {
    return ['未配置外语，默认跳过'];
}

return \plugins\multilang\service\Lang::install($codes);
