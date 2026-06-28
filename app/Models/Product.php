<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-26 02:02:38
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 02:08:10
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     *
     * @var array<string>
     */
    // #[Fillable(['offer_id', 'title', 'price', 'main_image', 'source_url', 'is_active_1688'])]
    protected $fillable = [
        'offer_id',
        'title',
        'price',
        'main_image',
        'source_url',
        'is_active_1688',
    ];

    /**
     * 需要类型转换的属性
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active_1688' => 'boolean',
        ];
    }

    /**
     * 获取该商品的所有监控项
     */
    public function monitorItems(): HasMany
    {
        return $this->hasMany(MonitorItem::class);
    }
}
