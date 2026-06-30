<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-29 00:38:41
 * @LastEditors: Y95201
 * @LastEditTime: 2026-07-01 05:00:45
 */

namespace App\Http\Controllers;

use App\Enums\VideoTaskStatus;
use App\Jobs\GenerateVideoJob;
use App\Models\VideoTask;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    protected string $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = config('services.agnes.api_url');
    }

    public function store(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'idempotency_key' => 'required|uuid',
            'mode' => 'string|in:t2v,i2v,multi,keyframes',
            'width' => 'integer|min:256|max:1920',
            'height' => 'integer|min:256|max:1920',
            'num_frames' => 'integer|min:9|max:441',
            'frame_rate' => 'numeric|min:1|max:60',
            'num_inference_steps' => 'integer|min:1|max:100',
            'seed' => 'integer',
            'negative_prompt' => 'string',
            'image_urls' => 'array',
            'image_urls.*' => 'url',
        ]);

        $params = $request->only([
            'mode',
            'width',
            'height',
            'num_frames',
            'frame_rate',
            'num_inference_steps',
            'seed',
            'negative_prompt',
            'image_urls',
        ]);

        $userId = $this->getCurrentUserId();

        $task = VideoTask::where([
            'user_id' => $userId,
            'idempotency_key' => $request->idempotency_key,
        ])->first();

        if ($task) {
            if ($task->status === VideoTaskStatus::Processing) {
                return response()->json([
                    'error' => '任务正在处理中，请勿重复提交',
                    'task_id' => $task->id,
                ], Response::HTTP_CONFLICT);
            }

            if ($task->status === VideoTaskStatus::Completed) {
                return response()->json([
                    'id' => $task->id,
                    'status' => $task->status->value,
                    'video_url' => $task->video_url,
                    'created_at' => $task->created_at,
                ], Response::HTTP_OK);
            }

            if ($task->status === VideoTaskStatus::Failed) {
                return response()->json([
                    'id' => $task->id,
                    'status' => $task->status->value,
                    'error_message' => $task->error_message,
                    'created_at' => $task->created_at,
                ], Response::HTTP_OK);
            }
        }

        $task = VideoTask::create([
            'user_id' => $userId,
            'idempotency_key' => $request->idempotency_key,
            'prompt' => $request->prompt,
            'params' => $params,
            'status' => VideoTaskStatus::Pending,
        ]);

        $task->status = VideoTaskStatus::Processing;
        $task->save();

        $result = $this->generateVideo($task);

        return response()->json($result, $result['status'] === 'completed' ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function getCurrentUserId(): int
    {
        // 优先尝试从 Sanctum token 获取用户
        if (Auth::guard('sanctum')->check()) {
            return Auth::guard('sanctum')->id();
        }

        // 个人测试模式：如果没有登录，返回 1（假设 user_id=1 是测试账号）
        // 如果需要切换用户，修改这里或添加 header
        return 1;
    }

    protected function generateVideo(VideoTask $task)
    {
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

            Log::info('Agnes API 请求参数', [
                'url' => $this->apiBaseUrl . '/v1/videos',
                'request_body' => $requestBody,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.agnes.api_key'),
                'Content-Type' => 'application/json',
            ])->timeout(300)->post($this->apiBaseUrl . '/v1/videos', $requestBody);

            if (! $response->successful()) {
                $task->markAsFailed('Agnes API 请求失败: ' . $response->status());
                return [
                    'task_id' => $task->id,
                    'status' => 'failed',
                    'error_message' => 'Agnes API 请求失败: ' . $response->status(),
                ];
            }

            $responseData = $response->json();
            $videoId = $responseData['video_id'] ?? null;

            if (! $videoId) {
                $task->markAsFailed('未获取到 Agnes 视频 ID');
                return [
                    'task_id' => $task->id,
                    'status' => 'failed',
                    'error_message' => '未获取到 Agnes 视频 ID',
                ];
            }

            $task->markAsProcessing($videoId);

            return $this->pollAgnesStatus($task, $videoId);
        } catch (\Exception $e) {
            Log::error('视频生成任务失败', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $task->markAsFailed($e->getMessage());

            return [
                'task_id' => $task->id,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ];
        }
    }

    protected function pollAgnesStatus(VideoTask $task, string $videoId)
    {
        $maxAttempts = 36;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.agnes.api_key'),
                ])->timeout(30)->get('https://apihub.agnes-ai.com/agnesapi?video_id=' . $videoId);

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
                            return [
                                'task_id' => $task->id,
                                'status' => 'completed',
                                'video_url' => $videoUrl,
                                'seconds' => $responseData['seconds'] ?? null,
                                'size' => $responseData['size'] ?? null,
                            ];
                        } else {
                            $task->markAsFailed('视频生成完成但未返回视频 URL');
                            return [
                                'task_id' => $task->id,
                                'status' => 'failed',
                                'error_message' => '视频生成完成但未返回视频 URL',
                            ];
                        }

                    case 'failed':
                        $errorData = $responseData['error'] ?? null;
                        $errorMessage = is_array($errorData) ? json_encode($errorData) : ($errorData ?? '未知错误');
                        $task->markAsFailed($errorMessage);

                        return [
                            'task_id' => $task->id,
                            'status' => 'failed',
                            'error_message' => $errorMessage,
                        ];

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

        return [
            'task_id' => $task->id,
            'status' => 'failed',
            'error_message' => '轮询超时，视频生成任务未在规定时间内完成',
        ];
    }

    public function show(VideoTask $task)
    {
        // 个人测试模式：不校验 user_id
        $response = [
            'id' => $task->id,
            'status' => $task->status->value,
        ];

        if ($task->status === VideoTaskStatus::Completed) {
            $response['video_url'] = $task->video_url;
        } elseif ($task->status === VideoTaskStatus::Failed) {
            $response['error_message'] = $task->error_message;
        } else {
            $response['message'] = '处理中...';
        }

        return response()->json($response);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        $file = $request->file('image');
        
        // 优先使用 S3，否则使用 public disk
        $diskName = 'public';
        if (env('AWS_BUCKET') && env('AWS_ACCESS_KEY_ID')) {
            $diskName = 's3';
        }
        
        $path = $file->store('videos/images', $diskName);
        $url = Storage::disk($diskName)->url($path);

        return response()->json([
            'url' => $url,
        ], Response::HTTP_OK);
    }

}
