<?php

namespace App\Http\Controllers;

use App\Enums\VideoTaskStatus;
use App\Models\VideoTask;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    private const MAX_IMAGE_SIZE = 10 * 1024 * 1024;
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    private const AGNES_ENDPOINT = '/v1/videos';

    private Client $httpClient;
    private string $apiKey;
    private string $apiBase;
    private string $videoModel;
    private int $agnesTimeout;

    public function __construct()
    {
        $this->apiKey = env('AGNES_API_KEY', '');
        $this->apiBase = env('AGNES_API_BASE', '');
        $this->videoModel = env('AGNES_VIDEO_MODEL', 'agnes-video-v2.0');
        $this->agnesTimeout = (int) env('AGNES_TIMEOUT', 300);

        $this->httpClient = new Client([
            'timeout' => $this->agnesTimeout,
            'connect_timeout' => 30,
            'verify' => false,
            'http_errors' => false,
        ]);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,webp|max:' . (self::MAX_IMAGE_SIZE / 1024),
        ]);

        $file = $request->file('image');

        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            return $this->error('仅支持 JPG/PNG/WebP 图片', 422);
        }

        $ext = $file->getClientOriginalExtension();
        $storageDir = 'uploads/videos/' . date('Y/m/d');
        $fileName = uniqid('vid_', true) . '.' . ($ext ?: 'png');
        $fullPath = $file->storeAs($storageDir, $fileName, 'public');

        if ($fullPath === false) {
            return $this->error('图片保存失败', 500);
        }

        return response()->json([
            'success' => true,
            'url' => $request->getSchemeAndHttpHost() . '/storage/' . ltrim($fullPath, '/'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        set_time_limit($this->agnesTimeout + 10);

        $request->validate([
            'model' => 'nullable|string',
            'prompt' => 'required|string',
            'image' => 'nullable|string',
            'image_urls' => 'nullable|array',
            'mode' => 'nullable|string|in:t2v,i2v,multi,keyframes',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'num_frames' => 'nullable|integer',
            'frame_rate' => 'nullable|integer',
            'num_inference_steps' => 'nullable|integer',
            'seed' => 'nullable|integer',
            'negative_prompt' => 'nullable|string',
            'idempotency_key' => 'nullable|string',
        ]);

        $userId = $request->user()?->id ?? 1;

        $pending = VideoTask::where('user_id', $userId)
            ->whereIn('status', [VideoTaskStatus::Pending, VideoTaskStatus::Processing])
            ->latest()
            ->first();

        if ($pending) {
            return response()->json([
                'success' => false,
                'error' => '有任务正在进行中，请等待完成后再生成新视频',
                'task_id' => $pending->id,
                'status' => $pending->status->value,
            ], 429);
        }

        $params = array_filter([
            'mode' => $request->input('mode', 'i2v'),
            'width' => $request->input('width'),
            'height' => $request->input('height'),
            'num_frames' => $request->input('num_frames', 121),
            'frame_rate' => $request->input('frame_rate', 24),
            'num_inference_steps' => $request->input('num_inference_steps'),
            'negative_prompt' => $request->input('negative_prompt'),
            'seed' => $request->input('seed'),
        ], fn($v) => $v !== null);

        $task = VideoTask::create([
            'user_id' => $userId,
            'idempotency_key' => $request->input('idempotency_key', Str::uuid()->toString()),
            'prompt' => $request->input('prompt'),
            'params' => $params,
            'status' => VideoTaskStatus::Pending,
        ]);

        try {
            $payload = $this->buildAgnesPayload($request);

            Log::info('[VideoAPI] 请求参数', [
                'url' => $this->apiBase . self::AGNES_ENDPOINT,
                'request_body' => $payload,
            ]);

            $response = $this->httpClient->post($this->apiBase . self::AGNES_ENDPOINT, [
                'headers' => $this->agnesHeaders(),
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            Log::info('[VideoAPI] 创建视频响应', ['response' => $data]);

            if ($response->getStatusCode() >= 400 || $this->isAgnesError($data)) {
                $errorMsg = $this->extractAgnesError($data) ?? "Agnes API 返回 HTTP {$response->getStatusCode()}";
                $task->markAsFailed($errorMsg);
                return $this->error($errorMsg, 502, ['original_response' => $data]);
            }

            $agnesTaskId = $data['task_id'] ?? $data['id'] ?? $data['taskId'] ?? '';
            $task->markAsProcessing($agnesTaskId);

            return response()->json([
                'success' => true,
                'task_id' => $task->id,
                'status' => $task->status->value,
                'data' => $data,
            ]);
        } catch (GuzzleException $e) {
            Log::error('[VideoAPI] 请求异常', ['error' => $e->getMessage()]);
            $task->markAsFailed($e->getMessage());
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(VideoTask $task): JsonResponse
    {
        if ($task->isProcessing() && $task->task_id) {
            $this->syncTaskStatus($task);
        }

        return response()->json([
            'success' => true,
            'task_id' => $task->id,
            'status' => $task->status->value,
            'video_url' => $task->video_url,
            'error_message' => $task->error_message,
            'prompt' => $task->prompt,
        ]);
    }

    private function buildAgnesPayload(Request $request): array
    {
        $mode = $request->input('mode', 'i2v');

        $payload = [
            'model' => $request->input('model', $this->videoModel),
            'prompt' => $request->input('prompt'),
        ];

        if ($mode === 't2v') {
            $payload['width'] = (int) $request->input('width', 1152);
            $payload['height'] = (int) $request->input('height', 768);
        } elseif ($mode === 'i2v' && $request->has('image')) {
            $image = $request->input('image');
            if (!str_starts_with($image, 'http')) {
                $image = $request->getSchemeAndHttpHost() . $image;
            }
            $payload['image'] = $image;
        } elseif (in_array($mode, ['multi', 'keyframes'], true) && $request->has('image_urls')) {
            $imageUrls = array_map(function ($url) use ($request) {
                if (!str_starts_with($url, 'http')) {
                    return $request->getSchemeAndHttpHost() . $url;
                }
                return $url;
            }, $request->input('image_urls'));

            $payload['extra_body'] = ['image' => $imageUrls];
            if ($mode === 'keyframes') {
                $payload['extra_body']['mode'] = 'keyframes';
            }
        }

        if ($request->has('num_frames')) {
            $payload['num_frames'] = (int) $request->input('num_frames');
        }

        if ($request->has('frame_rate')) {
            $payload['frame_rate'] = (int) $request->input('frame_rate');
        }

        return $payload;
    }

    private function syncTaskStatus(VideoTask $task): void
    {
        $statusData = $this->queryAgnesVideoStatus($task->task_id);
        if (!$statusData) {
            return;
        }

        $status = $statusData['status'] ?? null;
        $videoUrl = $statusData['video_url']
            ?? $statusData['result_url']
            ?? $statusData['remixed_from_video_id']
            ?? null;

        if (in_array($status, ['completed', 'succeeded'], true) && $videoUrl) {
            $task->markAsCompleted($videoUrl);
            return;
        }

        if (in_array($status, ['failed', 'error'], true)) {
            $task->markAsFailed($this->extractAgnesError($statusData) ?? '生成失败');
            return;
        }

        if (in_array($status, ['queued', 'processing', 'pending', 'running'], true)
            && $task->status !== VideoTaskStatus::Processing
        ) {
            $task->status = VideoTaskStatus::Processing;
            $task->save();
        }
    }

    private function queryAgnesVideoStatus(string $taskId): ?array
    {
        try {
            $response = $this->httpClient->get($this->apiBase . self::AGNES_ENDPOINT, [
                'query' => ['task_id' => $taskId],
                'headers' => $this->agnesHeaders(acceptOnly: true),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            Log::info('[VideoAPI] 查询视频状态', ['task_id' => $taskId, 'response' => $data]);
            return $data;
        } catch (GuzzleException $e) {
            Log::error('[VideoAPI] 查询视频状态失败', ['task_id' => $taskId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function agnesHeaders(bool $acceptOnly = false): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ];
        if (!$acceptOnly) {
            $headers['Content-Type'] = 'application/json';
        }
        return $headers;
    }

    private function isAgnesError(?array $data): bool
    {
        return isset($data['error']) || ($data['status'] ?? null) === 'error';
    }

    private function extractAgnesError(?array $data): ?string
    {
        if (!$data) {
            return null;
        }
        $error = $data['error'] ?? null;
        if (is_array($error)) {
            return $error['message'] ?? $error['detail'] ?? null;
        }
        if (is_string($error)) {
            return $error;
        }
        return $data['message'] ?? $data['detail'] ?? null;
    }

    private function error(string $message, int $status = 400, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => false,
            'error' => $message,
        ], $extra), $status);
    }
}
