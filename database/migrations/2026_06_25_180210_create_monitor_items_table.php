<?php
/*
 * @Description: 监控商品表
 * @Author: Y95201
 * @Date: 2026-06-26 02:02:10
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 02:05:37
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
        Schema::create('monitor_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            
            // 一个用户对同一个商品只能监控一次
            $table->unique(['user_id', 'product_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitor_items');
    }
};
