<?php
// 在线客服插件路由：启用后才加载，停用/卸载后这些地址一律 404
use think\facade\Route;

/* ---------- 前台：浮窗接口 + 插件静态资源 ---------- */
Route::get('kefu/asset/:file', 'plugins\kefu\controller\Api@asset')
    ->pattern(['file' => '[A-Za-z0-9_\-\.]+'])
    ->completeMatch();
Route::post('kefu/api/send', 'plugins\kefu\controller\Api@send')->completeMatch();
Route::get('kefu/api/poll', 'plugins\kefu\controller\Api@poll')->completeMatch();

/* ---------- 后台 ---------- */
Route::group('admin/plugin/kefu', function () {
    Route::get('', 'plugins\kefu\controller\Admin@index');

    Route::get('staff/edit', 'plugins\kefu\controller\Admin@staffEdit');
    Route::get('staff/edit/:id', 'plugins\kefu\controller\Admin@staffEdit');
    Route::post('staff/save', 'plugins\kefu\controller\Admin@staffSave');
    Route::post('staff/delete', 'plugins\kefu\controller\Admin@staffDelete');

    Route::get('chat', 'plugins\kefu\controller\Admin@chat');
    Route::get('chat/:id', 'plugins\kefu\controller\Admin@chatDetail');
    Route::post('chat/reply', 'plugins\kefu\controller\Admin@chatReply');
    Route::post('chat/close', 'plugins\kefu\controller\Admin@chatClose');
    Route::post('chat/delete', 'plugins\kefu\controller\Admin@chatDelete');

    Route::get('setting', 'plugins\kefu\controller\Admin@setting');
    Route::post('setting/save', 'plugins\kefu\controller\Admin@settingSave');

    // 后台页面自动刷新用的小接口
    Route::get('api/unread', 'plugins\kefu\controller\Admin@apiUnread');
    Route::get('api/messages', 'plugins\kefu\controller\Admin@apiMessages');
})
->pattern(['id' => '\d+'])
->middleware(\app\middleware\AdminAuth::class)
->completeMatch();
