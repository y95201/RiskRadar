<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-29 00:37:54
 * @LastEditors: Y95201
 * @LastEditTime: 2026-07-02 03:06:50
 */

namespace App\Enums;

enum VideoTaskStatus: string
{
    case Pending = 'pending'; // 待处理
    case Processing = 'processing'; // 处理中
    case Completed = 'completed'; // 已完成
    case Failed = 'failed'; // 失败

    public function isFinished(): bool
    {
        return $this === self::Completed || $this === self::Failed;
    }

    public function isProcessing(): bool
    {
        return $this === self::Pending || $this === self::Processing;
    }
}
