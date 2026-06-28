<?php

namespace App\Jobs;

use App\Enums\VideoTaskStatus;
use App\Models\VideoTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 2000;

    public function __construct(protected int $videoTaskId)
    {
    }

    public function handle(): void
    {
        $task = VideoTask::findOrFail($this->videoTaskId);

        try {
            $params = $task->params ?? [];
            $requestBody = [
                'model' => 'agnes-video-v2.0',
                'prompt' => $task->prompt,
                'height' => $params['height'] ?? 768,
                'width' => $params['width'] ?? 1152,
                'num_frames' => $params['num_frames'] ?? 121,
                'frame_rate' => $params['frame_rate'] ?? 24,
            ];

            if (!empty($params['negative_prompt'])) {
                $requestBody['negative_prompt'] = $params['negative_prompt'];
            }
            if (isset($params['seed'])) {
                $requestBody['seed'] = $params['seed'];
            }
            if (isset($params['num_inference_steps'])) {
                $requestBody['num_inference_steps'] = $params['num_inference_steps'];
            }

            $mode = $params['mode'] ?? 't2v';
            $imageUrls = $params['image_urls'] ?? [];

            switch ($mode) {
                case 'i2v':
                    if (!empty($imageUrls)) {
                        if (count($imageUrls) === 1) {
                            $requestBody['image'] = $imageUrls[0];
                        } else {
                            $requestBody['image'] = $imageUrls;
                        }
                    }
                    break;

                case 'multi':
                    if (!empty($imageUrls)) {
                        $requestBody['extra_body'] = [
                            'image' => $imageUrls,
                        ];
                    }
                    break;

                case 'keyframes':
                    if (!empty($imageUrls)) {
                        $requestBody['extra_body'] = [
                            'image' => $imageUrls,
                            'mode' => 'keyframes',
                        ];
                    }
                    break;

                case 't2v':
                default:
                    break;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.agnes.api_key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.agnes.api_url') . '/videos', $requestBody);

            if (! $response->successful()) {
                throw new \Exception('Agnes API 请求失败: ' . $response->status());
            }

            $responseData = $response->json();
            $videoId = $responseData['video_id'] ?? null;

            if (! $videoId) {
                throw new \Exception('未获取到 Agnes 视频 ID');
            }

            $task->markAsProcessing($videoId);

            $this->pollAgnesStatus($task, $videoId);
        } catch (\Exception $e) {
            Log::error('视频生成任务失败', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $task->markAsFailed($e->getMessage());
        }
    }

    protected function pollAgnesStatus(VideoTask $task, string $videoId): void
    {
        $maxAttempts = 36;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.agnes.api_key'),
                ])->get('https://apihub.agnes-ai.com/agnesapi', [
                    'video_id' => $videoId,
                ]);

                if (! $response->successful()) {
                    Log::warning('查询 Agnes 任务状态失败', [
                        'task_id' => $task->id,
                        'video_id' => $videoId,
                        'status' => $response->status(),
                    ]);

                    sleep(5);
                    continue;
                }

                $responseData = $response->json();
                $status = $responseData['status'] ?? 'unknown';

                switch ($status) {
                    case 'completed':
                        $videoUrl = $responseData['remixed_from_video_id'] ?? null;

                        if ($videoUrl) {
                            $task->markAsCompleted($videoUrl);
                        } else {
                            $task->markAsFailed('视频生成完成但未返回视频 URL');
                        }

                        return;

                    case 'failed':
                        $errorData = $responseData['error'] ?? null;
                        $errorMessage = is_array($errorData) ? json_encode($errorData) : ($errorData ?? '未知错误');
                        $task->markAsFailed($errorMessage);

                        return;

                    default:
                        sleep(5);
                }
            } catch (\Exception $e) {
                Log::error('轮询 Agnes 状态时发生异常', [
                    'task_id' => $task->id,
                    'video_id' => $videoId,
                    'error' => $e->getMessage(),
                ]);

                sleep(5);
            }
        }

        $task->markAsFailed('轮询超时，视频生成任务未在规定时间内完成');
    }
}
