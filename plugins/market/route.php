<?php
// 应用市场插件路由：启用插件后自动加载
use think\facade\Route;

Route::get('admin/plugin/market', 'plugins\market\controller\Admin@index')
    ->middleware(\app\middleware\AdminAuth::class);

Route::post('admin/plugin/market/install', 'plugins\market\controller\Admin@install')
    ->middleware(\app\middleware\AdminAuth::class);

Route::get('admin/plugin/market/refresh', 'plugins\market\controller\Admin@refresh')
    ->middleware(\app\middleware\AdminAuth::class);
