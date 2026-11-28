<?php
// 数据库备份插件路由：启用后才加载，停用/卸载后这些地址一律 404
use think\facade\Route;

Route::group('admin/plugin/dbbackup', function () {
    Route::get('', 'plugins\dbbackup\controller\Admin@index');

    Route::post('run', 'plugins\dbbackup\controller\Admin@backupNow');
    Route::post('restore', 'plugins\dbbackup\controller\Admin@restore');
    Route::post('upload', 'plugins\dbbackup\controller\Admin@upload');
    Route::post('delete', 'plugins\dbbackup\controller\Admin@remove');
    Route::get('download', 'plugins\dbbackup\controller\Admin@download');
    Route::post('setting/save', 'plugins\dbbackup\controller\Admin@settingSave');
})
->middleware(\app\middleware\AdminAuth::class)
->completeMatch();
