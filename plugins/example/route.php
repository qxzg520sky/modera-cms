<?php
// 示例插件路由：启用插件后自动加载
use think\facade\Route;

// 前台：/plugin-example
Route::get('plugin-example', 'plugins\example\controller\Front@index');

// 后台：/admin/plugin/example（需登录）
Route::get('admin/plugin/example', 'plugins\example\controller\Admin@index')
    ->middleware(\app\middleware\AdminAuth::class);
