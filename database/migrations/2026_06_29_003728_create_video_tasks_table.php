<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-29 00:37:38
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-29 00:39:32
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('idempotency_key', 36)->index();
            $table->text('prompt');
            $table->string('task_id')->nullable()->index();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('video_url')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_tasks');
    }
};
