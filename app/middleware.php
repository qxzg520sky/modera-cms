<?php
// 全局中间件定义文件
return [
    // 首次安装引导：未安装时全站跳到 /install
    \app\middleware\NeedInstall::class,
    // Session初始化
    \think\middleware\SessionInit::class,
    // 页面静态缓存（由「缓存优化」插件控制，插件停用时不生效）
    \app\middleware\PageCache::class,
];
