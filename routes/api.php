<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-25 21:25:26
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 17:46:45
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfferGuardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| OfferGuard API Routes
|--------------------------------------------------------------------------
|
| OfferGuard跨境电商货源风险监控系统API接口
| 前端Chrome插件通过这些接口与后端交互
|
*/

// 即时检测接口（支持未登录用户免费检测）
Route::post('/detect', [OfferGuardController::class, 'detect']);

// 监控功能接口（需要登录）
Route::middleware('auth:sanctum')->group(function () {
    // 将商品加入监控库（仅限付费用户）
    Route::post('/monitor/add', [OfferGuardController::class, 'addMonitor']);
    
    // 获取当前用户的监控列表
    Route::get('/monitor/list', [OfferGuardController::class, 'getMonitorList']);
});

// 获取配额状态（支持未登录用户）
Route::get('/quota/status', [OfferGuardController::class, 'getQuotaStatus']);