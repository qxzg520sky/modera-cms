<?php
// 缓存优化插件路由：启用后才加载，停用/卸载后这些地址一律 404
use think\facade\Route;

Route::group('admin/plugin/cache', function () {
    Route::get('', 'plugins\cache\controller\Admin@index');
    Route::post('save', 'plugins\cache\controller\Admin@save');
    Route::post('clear', 'plugins\cache\controller\Admin@clear');
})
->middleware(\app\middleware\AdminAuth::class)
->completeMatch();
