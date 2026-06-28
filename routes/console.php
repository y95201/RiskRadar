<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| OfferGuard 定时任务调度
|--------------------------------------------------------------------------
|
| 配置 OfferGuard 系统的定时任务，包括每日巡检等
|
*/

// 每天早上9:00执行每日巡检任务
Schedule::command('monitor:daily-inspect')->dailyAt('09:00');
