<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-26 02:02:39
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 02:10:30
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskLog extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     *
     * @var array<string>
     */
    // #[Fillable(['target_type', 'target_id', 'status', 'reason', 'checked_at'])]
    protected $fillable = [
        'target_type',
        'target_id',
        'status',
        'reason',
        'checked_at',
    ];

    /**
     * 类型转换
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    /**
     * 风险类型常量
     */
    public const TYPE_1688 = '1688';
    public const TYPE_TRADEMARK = 'trademark';

    /**
     * 状态常量
     */
    public const STATUS_ONLINE = 'online';
    public const STATUS_OFFLINE = 'offline';
    public const STATUS_HIGH = 'high';
    public const STATUS_LOW = 'low';
}
