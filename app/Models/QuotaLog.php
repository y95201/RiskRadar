<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-26 02:02:39
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 02:11:17
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotaLog extends Model
{
    use HasFactory;

    /**
     * 是否维护时间戳（只维护 created_at）
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可批量赋值的属性
     *
     * @var array<string>
     */
    // #[Fillable(['device_id', 'quota_type'])]
    protected $fillable = [
        'device_id',
        'quota_type',
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
     * 配额类型常量
     */
    public const QUOTA_TYPE_FREE_DETECT = 'free_detect';

    /**
     * 每日免费检测次数
     */
    public const DAILY_FREE_QUOTA = 3;
}
