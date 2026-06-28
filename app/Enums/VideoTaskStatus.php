<?php

namespace App\Enums;

enum VideoTaskStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function isFinished(): bool
    {
        return $this === self::Completed || $this === self::Failed;
    }

    public function isProcessing(): bool
    {
        return $this === self::Pending || $this === self::Processing;
    }
}
