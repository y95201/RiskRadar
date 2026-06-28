<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-05-26 07:11:52
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-29 04:13:00
 */

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/video-generator', function () {
    return view('dome');
});
