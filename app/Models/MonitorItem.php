<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-26 02:02:39
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 02:09:37
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitorItem extends Model
{
    use HasFactory;

    /**
     * 设置表名为 monitor_items（Laravel默认复数化）
     *
     * @var string
     */
    protected $table = 'monitor_items';

    /**
     * 是否维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可批量赋值的属性
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
    ];

    /**
     * 类型转换
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
    /**
     * 获取关联的用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取关联的商品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
