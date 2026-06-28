<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-26 02:02:22
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 02:07:19
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
        Schema::create('quota_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 128)->comment('设备指纹或IP地址');
            $table->string('quota_type', 32)->default('free_detect')->comment('配额类型');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('device_id');
            $table->index('quota_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quota_logs');
    }
};
