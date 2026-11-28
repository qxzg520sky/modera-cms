<?php
// 访问统计插件路由：启用后才加载
use think\facade\Route;

// 前台采集端点（不需要登录，只接受 POST）
Route::post('stats/collect', 'plugins\stats\controller\Collect@collect');

Route::group('admin/plugin/stats', function () {
    Route::get('', 'plugins\stats\controller\Admin@index');
    Route::post('save', 'plugins\stats\controller\Admin@save');
    Route::post('clear', 'plugins\stats\controller\Admin@clear');
    Route::post('purge', 'plugins\stats\controller\Admin@purge');
})->middleware(\app\middleware\AdminAuth::class)->completeMatch();
