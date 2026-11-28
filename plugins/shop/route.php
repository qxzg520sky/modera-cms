<?php
/**
 * 企业商城插件路由
 * 前台：/shop 商城入口（复用会员 session）
 * 业务员：/shop/staff/orders（复用会员插件业务员 session）
 * 后台：/admin/plugin/shop/*
 * 对外 API：/shop/api/*（与会员插件同一套 HMAC 签名）
 */
use think\facade\Route;

/* ---------- 插件静态资源 ---------- */
Route::get('shop/asset/:file', 'plugins\shop\controller\Front@asset')
    ->pattern(['file' => '[A-Za-z0-9_\-\.]+'])
    ->completeMatch();

/* ---------- 对外开放 API（签名校验在控制器内） ---------- */
Route::get('shop/api/health', 'plugins\shop\controller\Api@health')->completeMatch();
Route::get('shop/api/products', 'plugins\shop\controller\Api@products')->completeMatch();
Route::get('shop/api/product/:id', 'plugins\shop\controller\Api@product')->pattern(['id' => '\d+'])->completeMatch();
Route::post('shop/api/order/create', 'plugins\shop\controller\Api@orderCreate')->completeMatch();
Route::post('shop/api/order/pay', 'plugins\shop\controller\Api@orderPay')->completeMatch();
Route::get('shop/api/order/:no', 'plugins\shop\controller\Api@order')->pattern(['no' => '[A-Za-z0-9]+'])->completeMatch();

/* ---------- 会员前台商城 ---------- */
Route::get('shop', 'plugins\shop\controller\Front@index')->completeMatch();
Route::get('shop/product/:id', 'plugins\shop\controller\Front@product')->pattern(['id' => '\d+'])->completeMatch();
Route::get('shop/cart', 'plugins\shop\controller\Front@cart')->completeMatch();
Route::post('shop/cart/add', 'plugins\shop\controller\Front@cartAdd')->completeMatch();
Route::post('shop/cart/update', 'plugins\shop\controller\Front@cartUpdate')->completeMatch();
Route::post('shop/cart/remove', 'plugins\shop\controller\Front@cartRemove')->completeMatch();

Route::get('shop/checkout', 'plugins\shop\controller\Front@checkout')->completeMatch();
Route::post('shop/order/create', 'plugins\shop\controller\Front@orderCreate')->completeMatch();
Route::get('shop/order/:no', 'plugins\shop\controller\Front@orderDetail')->pattern(['no' => '[A-Za-z0-9]+'])->completeMatch();
Route::post('shop/order/pay', 'plugins\shop\controller\Front@orderPay')->completeMatch();
Route::get('shop/orders', 'plugins\shop\controller\Front@orders')->completeMatch();

Route::get('shop/after-sale/:no', 'plugins\shop\controller\Front@afterSaleForm')->pattern(['no' => '[A-Za-z0-9]+'])->completeMatch();
Route::post('shop/after-sale/apply', 'plugins\shop\controller\Front@afterSaleApply')->completeMatch();
Route::get('shop/after-sales', 'plugins\shop\controller\Front@afterSales')->completeMatch();
Route::post('shop/coupon/receive', 'plugins\shop\controller\Front@couponReceive')->completeMatch();

/* ---------- 业务员：只能看自己绑定会员的订单 ---------- */
Route::get('shop/staff/orders', 'plugins\shop\controller\Staff@orders')->completeMatch();

/* ---------- 后台管理 ---------- */
Route::group('admin/plugin/shop', function () {
    Route::get('', 'plugins\shop\controller\Admin@dashboard');

    Route::get('products', 'plugins\shop\controller\Admin@products');
    Route::get('product/edit', 'plugins\shop\controller\Admin@productEdit');
    Route::get('product/edit/:id', 'plugins\shop\controller\Admin@productEdit');
    Route::post('product/save', 'plugins\shop\controller\Admin@productSave');
    Route::post('product/status', 'plugins\shop\controller\Admin@productStatus');
    Route::post('product/delete', 'plugins\shop\controller\Admin@productDelete');

    Route::get('categories', 'plugins\shop\controller\Admin@categories');
    Route::post('category/save', 'plugins\shop\controller\Admin@categorySave');
    Route::post('category/delete', 'plugins\shop\controller\Admin@categoryDelete');

    Route::get('skus', 'plugins\shop\controller\Admin@skus');
    Route::post('sku/save', 'plugins\shop\controller\Admin@skuSave');
    Route::post('sku/stock', 'plugins\shop\controller\Admin@skuStock');

    Route::get('orders', 'plugins\shop\controller\Admin@orders');
    Route::get('order/export', 'plugins\shop\controller\Admin@orderExport');
    Route::get('order/:no', 'plugins\shop\controller\Admin@orderDetail')->pattern(['no' => '[A-Za-z0-9]+']);
    Route::post('order/price', 'plugins\shop\controller\Admin@orderPrice');
    Route::post('order/ship', 'plugins\shop\controller\Admin@orderShip');
    Route::post('order/close', 'plugins\shop\controller\Admin@orderClose');
    Route::post('order/refund', 'plugins\shop\controller\Admin@orderRefund');
    Route::post('order/spend-retry', 'plugins\shop\controller\Admin@orderSpendRetry');

    Route::get('aftersales', 'plugins\shop\controller\Admin@afterSales');
    Route::post('aftersale/audit', 'plugins\shop\controller\Admin@afterSaleAudit');

    Route::get('coupons', 'plugins\shop\controller\Admin@coupons');
    Route::post('coupon/save', 'plugins\shop\controller\Admin@couponSave');
    Route::post('coupon/grant', 'plugins\shop\controller\Admin@couponGrant');

    Route::get('settings', 'plugins\shop\controller\Admin@settings');
    Route::post('settings/save', 'plugins\shop\controller\Admin@settingsSave');
})
->pattern(['id' => '\d+'])
->middleware(\app\middleware\AdminAuth::class)
->completeMatch();
