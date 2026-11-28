<?php
// 前台路由
use think\facade\Route;

require __DIR__ . '/admin.php';

Route::get('install', 'Install/index');
Route::post('install', 'Install/doInstall');
Route::get('install/done', 'Install/done');

Route::get('/', 'Index/index');
Route::get('c/:slug', 'Index/lists');
Route::get('a/:key', 'Index/show')->pattern(['key' => '[\w\.\-]+']);
Route::get('p/:slug', 'Index/page');
Route::get('search', 'Index/search');
Route::get('theme/:file', 'Index/asset')->pattern(['file' => '[\w\.\-]+']);
Route::get('static/admin/:file', 'Index/adminAsset');

// 已启用插件的路由（启用即生效）
foreach (\app\service\PluginManager::enabled() as $plugin) {
    $file = root_path() . 'plugins/' . $plugin . '/route.php';
    if (is_file($file)) {
        require $file;
    }
}
