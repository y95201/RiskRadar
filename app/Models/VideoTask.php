<?php

namespace App\Models;

use App\Enums\VideoTaskStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'idempotency_key', 'prompt', 'params', 'task_id', 'status', 'video_url', 'error_message'])]
class VideoTask extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => VideoTaskStatus::class,
            'params' => 'json',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isFinished(): bool
    {
        return $this->status->isFinished();
    }

    public function isProcessing(): bool
    {
        return $this->status->isProcessing();
    }

    public function markAsProcessing(string $taskId): void
    {
        $this->update([
            'status' => VideoTaskStatus::Processing,
            'task_id' => $taskId,
        ]);
    }

    public function markAsCompleted(string $videoUrl): void
    {
        $this->update([
            'status' => VideoTaskStatus::Completed,
            'video_url' => $videoUrl,
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => VideoTaskStatus::Failed,
            'error_message' => $errorMessage,
        ]);
    }
}
