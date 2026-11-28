<?php
// 后台路由：/admin
use think\facade\Route;
use app\middleware\AdminAuth;

Route::group('admin', function () {

    Route::get('', 'admin.Dashboard/index');

    // 安装 / 登录
    Route::get('install', 'admin.Install/index');
    Route::post('install', 'admin.Install/doInstall');
    Route::get('login', 'admin.Login/index');
    Route::post('login', 'admin.Login/doLogin');
    Route::get('logout', 'admin.Login/logout');

    // 仪表盘
    Route::get('dashboard', 'admin.Dashboard/index');

    // 文章
    Route::get('article', 'admin.Article/index');
    Route::get('article/edit', 'admin.Article/edit');
    Route::get('article/edit/:id', 'admin.Article/edit');
    Route::post('article/save', 'admin.Article/save');
    Route::post('article/delete', 'admin.Article/delete');

    // 栏目
    Route::get('category', 'admin.Category/index');
    Route::get('category/edit', 'admin.Category/edit');
    Route::get('category/edit/:id', 'admin.Category/edit');
    Route::post('category/save', 'admin.Category/save');
    Route::post('category/delete', 'admin.Category/delete');

    // 单页
    Route::get('page', 'admin.Page/index');
    Route::get('page/edit', 'admin.Page/edit');
    Route::get('page/edit/:id', 'admin.Page/edit');
    Route::post('page/save', 'admin.Page/save');
    Route::post('page/delete', 'admin.Page/delete');

    // 模板
    Route::get('theme', 'admin.Theme/index');
    Route::post('theme/enable', 'admin.Theme/enable');
    Route::post('theme/uninstall', 'admin.Theme/uninstall');

    // 模块中心（主题 / 插件按需安装）
    Route::get('module', 'admin.Module/index');
    Route::post('module/install', 'admin.Module/install');
    Route::post('module/upload', 'admin.Module/upload');
    Route::post('module/remove', 'admin.Module/remove');
    Route::post('module/uninstall', 'admin.Module/uninstall');

    // 插件
    Route::get('plugin', 'admin.Plugin/index');
    Route::post('plugin/toggle', 'admin.Plugin/toggle');
    Route::post('plugin/uninstall', 'admin.Plugin/uninstall');
    Route::get('plugin/config', 'admin.Plugin/config');
    Route::get('plugin/config/:name', 'admin.Plugin/config');
    Route::post('plugin/save', 'admin.Plugin/save');

    // 编辑器图片上传
    Route::post('upload', 'admin.Upload/image');

    // 系统设置
    Route::get('setting', 'admin.Setting/index');
    Route::post('setting/save', 'admin.Setting/save');

    // 在线更新（程序核心，支持 GitHub / Gitee 与回滚）
    Route::get('update', 'admin.Update/index');
    Route::post('update/check', 'admin.Update/check');
    Route::post('update/run', 'admin.Update/run');
    Route::post('update/rollback', 'admin.Update/rollback');

    // 管理员
    Route::get('user', 'admin.User/index');
    Route::get('user/edit', 'admin.User/edit');
    Route::get('user/edit/:id', 'admin.User/edit');
    Route::post('user/save', 'admin.User/save');
    Route::post('user/delete', 'admin.User/delete');
})
->middleware(AdminAuth::class)
->pattern(['id' => '\d+', 'name' => '[A-Za-z0-9_\-]+'])
->completeMatch();


