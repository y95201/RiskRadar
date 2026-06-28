<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-26 02:02:17
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 02:06:16
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
        Schema::create('risk_logs', function (Blueprint $table) {
             $table->id();
            $table->string('target_type', 32)->comment('风险类型：1688(下架风险), trademark(商标侵权)');
            $table->string('target_id', 64)->comment('目标ID(如offerId)');
            $table->string('status', 32)->comment('状态：online(在线), offline(下架), high(高风险), low(低风险)');
            $table->text('reason')->nullable()->comment('风险原因描述');
            $table->timestamp('checked_at')->comment('检测时间');
            $table->timestamps();
            
            $table->index('target_type');
            $table->index('status');
            $table->index('target_id');
            $table->index('checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_logs');
    }
};
