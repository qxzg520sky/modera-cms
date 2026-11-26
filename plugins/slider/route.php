<?php
// 轮播广告插件路由：启用后才加载，停用/卸载后这些地址一律 404
use think\facade\Route;

Route::group('admin/plugin/slider', function () {
    Route::get('', 'plugins\slider\controller\Admin@index');
    Route::get('form', 'plugins\slider\controller\Admin@form');
    Route::get('form/:id', 'plugins\slider\controller\Admin@form');
    Route::post('save', 'plugins\slider\controller\Admin@save');
    Route::post('toggle', 'plugins\slider\controller\Admin@toggle');
    Route::post('delete', 'plugins\slider\controller\Admin@remove');
    Route::post('move', 'plugins\slider\controller\Admin@move');
})
->pattern(['id' => '\d+'])
->middleware(\app\middleware\AdminAuth::class)
->completeMatch();
