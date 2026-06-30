<?php
/*
 * @Description: 创建测试用户和修复数据库配置
 * @Author: Y95201
 * @Date: 2026-06-30
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
        // 创建默认测试用户（如果不存在）
        if (!DB::table('users')->where('id', 1)->exists()) {
            DB::table('users')->insert([
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => \Illuminate\Support\Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('id', 1)->delete();
    }
};
