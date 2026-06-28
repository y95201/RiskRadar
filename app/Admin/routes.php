<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::get('/admin', fn() => \Slowlyo\OwlAdmin\Admin::view());

Route::group([
    'domain'     => config('admin.route.domain'),
    'prefix'     => config('admin.route.prefix'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->resource('dashboard', \App\Admin\Controllers\HomeController::class);
    $router->resource('system/settings', \App\Admin\Controllers\SettingController::class);

    /*
    |--------------------------------------------------------------------------
    | OfferGuard 后台管理路由
    |--------------------------------------------------------------------------
    */

    // 用户管理
    $router->resource('offerguard/users', \App\Admin\Controllers\UserController::class);

    // 商品库管理
    $router->resource('offerguard/products', \App\Admin\Controllers\ProductController::class);

    // 监控库管理
    $router->resource('offerguard/monitors', \App\Admin\Controllers\MonitorItemController::class);

    // 巡检日志管理
    $router->resource('offerguard/risk-logs', \App\Admin\Controllers\RiskLogController::class);
});
