<?php
// 多语言插件路由：启用后自动加载；停用即整段消失
use think\facade\Route;

/* ---------- 前台：语言前缀 ---------- */
foreach (\plugins\multilang\service\Lang::codes() as $code) {
    $ctl = 'plugins\\multilang\\controller\\Front@';

    Route::get($code, $ctl . 'index')->completeMatch();
    Route::get($code . '/', $ctl . 'index')->completeMatch();
    Route::get($code . '/c/:slug', $ctl . 'lists')->completeMatch();
    Route::get($code . '/a/:id', $ctl . 'show')->pattern(['id' => '\d+'])->completeMatch();
    Route::get($code . '/p/:slug', $ctl . 'page')->completeMatch();
    Route::get($code . '/search', $ctl . 'search')->completeMatch();
}

/* ---------- 后台 ---------- */
Route::group('admin/plugin/multilang', function () {
    Route::get('', 'plugins\multilang\controller\Admin@index');
    Route::get('edit/:type/:id', 'plugins\multilang\controller\Admin@edit');
    Route::post('save', 'plugins\multilang\controller\Admin@save');
    Route::post('install', 'plugins\multilang\controller\Admin@install');
    Route::post('drop', 'plugins\multilang\controller\Admin@drop');
    Route::post('save-content', 'plugins\multilang\controller\Admin@saveContent');
})
->middleware(\app\middleware\AdminAuth::class)
->completeMatch();
