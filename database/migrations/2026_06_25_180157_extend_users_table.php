<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-26 02:01:57
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 02:04:02
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->comment('是否付费用户');
            $table->integer('plan_limit')->default(0)->comment('监控商品上限数量');
            $table->dateTime('expire_at')->nullable()->comment('会员过期时间');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'plan_limit', 'expire_at']);
        });
    }
};
