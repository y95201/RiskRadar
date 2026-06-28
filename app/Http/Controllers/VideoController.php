<?php

namespace App\Http\Controllers;

use App\Enums\VideoTaskStatus;
use App\Jobs\GenerateVideoJob;
use App\Models\VideoTask;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'idempotency_key' => 'required|uuid',
            'mode' => 'string|in:t2v,i2v,multi,keyframes',
            'width' => 'integer|min:256|max:1920',
            'height' => 'integer|min:256|max:1920',
            'num_frames' => 'integer|min:9|max:441',
            'frame_rate' => 'integer|min:1|max:60',
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

        try {
            $task = VideoTask::firstOrCreate(
                [
                    'user_id' => Auth::user()->id,
                    'idempotency_key' => $request->idempotency_key,
                ],
                [
                    'prompt' => $request->prompt,
                    'params' => $params,
                    'status' => VideoTaskStatus::Pending,
                ]
            );
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                $task = VideoTask::where([
                    'user_id' => Auth::user()->id,
                    'idempotency_key' => $request->idempotency_key,
                ])->first();

                if (! $task) {
                    return response()->json(['error' => '创建任务失败'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                throw $e;
            }
        }

        if ($task->status === VideoTaskStatus::Completed || $task->status === VideoTaskStatus::Failed) {
            return response()->json([
                'id' => $task->id,
                'status' => $task->status->value,
                'video_url' => $task->video_url,
                'error_message' => $task->error_message,
                'created_at' => $task->created_at,
            ], Response::HTTP_OK);
        }

        if ($task->status === VideoTaskStatus::Pending || $task->status === VideoTaskStatus::Processing) {
            return response()->json([
                'error' => '任务正在处理中，请勿重复提交',
                'task_id' => $task->id,
            ], Response::HTTP_CONFLICT);
        }

        GenerateVideoJob::dispatch($task->id);

        return response()->json([
            'task_id' => $task->id,
            'status' => $task->status->value,
            'message' => '任务已提交',
        ], Response::HTTP_ACCEPTED);
    }

    public function show(VideoTask $task)
    {
        if ($task->user_id !== Auth::user()->id) {
            return response()->json(['error' => '无权访问此任务'], Response::HTTP_FORBIDDEN);
        }

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
        
        $diskName = config('filesystems.default');
        if (env('AWS_BUCKET') && env('AWS_ACCESS_KEY_ID')) {
            $diskName = 's3';
        }
        
        $path = $file->store('videos/images', $diskName);
        $url = Storage::disk($diskName)->url($path);

        return response()->json([
            'url' => $url,
        ], Response::HTTP_OK);
    }

    protected function isUniqueConstraintViolation(QueryException $e): bool
    {
        $errorCode = $e->getCode();

        return in_array($errorCode, ['23000', '23505', '1062'], true);
    }
}
