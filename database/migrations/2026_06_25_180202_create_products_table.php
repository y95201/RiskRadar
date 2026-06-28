<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-26 02:02:02
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 02:04:57
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('offer_id', 64)->unique()->comment('1688商品ID');
            $table->string('title', 512)->comment('商品标题');
            $table->decimal('price', 10, 2)->default(0)->comment('商品价格');
            $table->string('main_image', 512)->nullable()->comment('商品主图URL');
            $table->string('source_url', 512)->comment('1688商品源URL');
            $table->boolean('is_active_1688')->default(true)->comment('1688是否在架');
            $table->timestamps();
            
            $table->index('offer_id');
            $table->index('is_active_1688');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
