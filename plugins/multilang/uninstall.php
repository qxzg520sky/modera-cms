<?php
/**
 * 多语言插件：卸载钩子（后台「卸载」按钮或 PluginManager::uninstall 执行）
 * 删除所有 {字段}_{语言} 列（连同里面的译文数据），站点回到纯中文。
 * 模板里的 lang() / lang_path() / ml() 是系统内置能力，字段不存在时自动退化为中文，不会报错。
 */

return \plugins\multilang\service\Lang::uninstall([], true);
