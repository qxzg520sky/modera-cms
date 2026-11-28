<?php
/**
 * 企业会员管理插件路由
 * 前台：/member 会员中心；/member/staff 业务员工作台
 * 接口：/member/api/* 商城等外部系统调用（HMAC 签名）
 * 后台：/admin/plugin/member/*（需管理员登录）
 */
use think\facade\Route;

/* ---------- 插件静态资源 ---------- */
Route::get('member/asset/:file', 'plugins\member\controller\Front@asset')
    ->pattern(['file' => '[A-Za-z0-9_\-\.]+'])
    ->completeMatch();

/* ---------- 对外开放 API（签名校验在控制器内） ---------- */
Route::get('member/api/health', 'plugins\member\controller\Api@health')->completeMatch();
Route::post('member/api/member/get', 'plugins\member\controller\Api@memberGet')->completeMatch();
Route::post('member/api/member/register', 'plugins\member\controller\Api@memberRegister')->completeMatch();
Route::post('member/api/member/verify', 'plugins\member\controller\Api@memberVerify')->completeMatch();
Route::post('member/api/member/update', 'plugins\member\controller\Api@memberUpdate')->completeMatch();
Route::post('member/api/assets', 'plugins\member\controller\Api@assets')->completeMatch();
Route::post('member/api/discount', 'plugins\member\controller\Api@discount')->completeMatch();
Route::post('member/api/balance/change', 'plugins\member\controller\Api@balanceChange')->completeMatch();
Route::post('member/api/points/change', 'plugins\member\controller\Api@pointsChange')->completeMatch();
Route::post('member/api/spend/record', 'plugins\member\controller\Api@spendRecord')->completeMatch();
Route::post('member/api/address/list', 'plugins\member\controller\Api@addressList')->completeMatch();
Route::post('member/api/address/save', 'plugins\member\controller\Api@addressSave')->completeMatch();
Route::post('member/api/address/delete', 'plugins\member\controller\Api@addressDelete')->completeMatch();
Route::post('member/api/orders', 'plugins\member\controller\Api@orders')->completeMatch();

/* ---------- 会员中心（前台，独立登录态） ---------- */
Route::get('member/status', 'plugins\member\controller\Front@status')->completeMatch();
Route::get('member', 'plugins\member\controller\Front@index')->completeMatch();
Route::get('member/login', 'plugins\member\controller\Front@login')->completeMatch();
Route::post('member/login', 'plugins\member\controller\Front@doLogin')->completeMatch();
Route::get('member/register', 'plugins\member\controller\Front@register')->completeMatch();
Route::post('member/register', 'plugins\member\controller\Front@doRegister')->completeMatch();
Route::get('member/logout', 'plugins\member\controller\Front@logout')->completeMatch();
Route::get('member/center', 'plugins\member\controller\Front@center')->completeMatch();
Route::get('member/address', 'plugins\member\controller\Front@address')->completeMatch();
Route::post('member/address/save', 'plugins\member\controller\Front@addressSave')->completeMatch();
Route::post('member/address/delete', 'plugins\member\controller\Front@addressDelete')->completeMatch();
Route::get('member/logs', 'plugins\member\controller\Front@logs')->completeMatch();
Route::get('member/profile', 'plugins\member\controller\Front@profile')->completeMatch();
Route::post('member/profile/save', 'plugins\member\controller\Front@profileSave')->completeMatch();
Route::post('member/password', 'plugins\member\controller\Front@password')->completeMatch();
Route::get('member/orders', 'plugins\member\controller\Front@orders')->completeMatch();

/* ---------- 业务员工作台（独立登录态，只能看自己名下会员） ---------- */
Route::get('member/staff/login', 'plugins\member\controller\Staff@login')->completeMatch();
Route::post('member/staff/login', 'plugins\member\controller\Staff@doLogin')->completeMatch();
Route::get('member/staff/logout', 'plugins\member\controller\Staff@logout')->completeMatch();
Route::get('member/staff', 'plugins\member\controller\Staff@index')->completeMatch();
Route::get('member/staff/member/:id', 'plugins\member\controller\Staff@member')->pattern(['id' => '\d+'])->completeMatch();
Route::post('member/staff/remark', 'plugins\member\controller\Staff@remark')->completeMatch();

/* ---------- 后台管理（管理员可见全部会员） ---------- */
Route::group('admin/plugin/member', function () {
    Route::get('', 'plugins\member\controller\Admin@dashboard');
    Route::get('members', 'plugins\member\controller\Admin@members');
    Route::get('member/edit', 'plugins\member\controller\Admin@memberEdit');
    Route::get('member/edit/:id', 'plugins\member\controller\Admin@memberEdit');
    Route::get('member/view/:id', 'plugins\member\controller\Admin@memberView');
    Route::post('member/save', 'plugins\member\controller\Admin@memberSave');
    Route::post('member/asset', 'plugins\member\controller\Admin@memberAsset');
    Route::post('member/upgrade', 'plugins\member\controller\Admin@memberUpgrade');

    Route::get('levels', 'plugins\member\controller\Admin@levels');
    Route::post('level/save', 'plugins\member\controller\Admin@levelSave');
    Route::post('level/delete', 'plugins\member\controller\Admin@levelDelete');

    Route::get('staff', 'plugins\member\controller\Admin@staff');
    Route::get('staff/edit', 'plugins\member\controller\Admin@staffEdit');
    Route::get('staff/edit/:id', 'plugins\member\controller\Admin@staffEdit');
    Route::post('staff/save', 'plugins\member\controller\Admin@staffSave');
    Route::post('staff/delete', 'plugins\member\controller\Admin@staffDelete');

    Route::get('logs', 'plugins\member\controller\Admin@logs');
    Route::get('import', 'plugins\member\controller\Admin@importPage');
    Route::post('import', 'plugins\member\controller\Admin@doImport');
    Route::get('export', 'plugins\member\controller\Admin@export');
    Route::get('template', 'plugins\member\controller\Admin@template');

    Route::get('settings', 'plugins\member\controller\Admin@settings');
    Route::post('settings/save', 'plugins\member\controller\Admin@settingsSave');
})
->pattern(['id' => '\d+'])
->middleware(\app\middleware\AdminAuth::class)
->completeMatch();
