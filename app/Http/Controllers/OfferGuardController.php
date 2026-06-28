<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MonitorItem;
use App\Models\Product;
use App\Models\QuotaLog;
use App\Models\RiskLog;
use App\Models\User;
use App\Services\Check1688Service;
use App\Services\CheckTrademarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * OfferGuard API控制器
 * 处理前端Chrome插件的请求
 */
class OfferGuardController extends Controller
{
    /**
     * 1688检测服务
     *
     * @var Check1688Service
     */
    protected $check1688Service;

    /**
     * 商标检测服务
     *
     * @var CheckTrademarkService
     */
    protected $checkTrademarkService;

    /**
     * 构造函数
     *
     * @param Check1688Service $check1688Service
     * @param CheckTrademarkService $checkTrademarkService
     */
    public function __construct(
        Check1688Service $check1688Service,
        CheckTrademarkService $checkTrademarkService
    ) {
        $this->check1688Service = $check1688Service;
        $this->checkTrademarkService = $checkTrademarkService;
    }

    /**
     * 即时检测接口
     * 接收插件传来的offerId和商品信息，执行检测并返回风险报告
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detect(Request $request)
    {
        // 验证请求参数
        $validated = $request->validate([
            'offer_id' => 'required|string',
            'title' => 'required|string',
            'price' => 'nullable|numeric',
            'main_image' => 'nullable|url',
            'source_url' => 'nullable|url',
            'device_id' => 'nullable|string', // 未登录用户的设备标识
        ]);

        $offerId = $validated['offer_id'];
        $deviceId = $validated['device_id'] ?? $request->header('X-Device-Id') ?? $request->ip();

        // 判断用户是否登录
        $user = Auth::user();

        // 如果未登录，检查免费配额
        if (!$user) {
            if (!$this->hasFreeQuota($deviceId)) {
                return response()->json([
                    'success' => false,
                    'message' => '今日免费检测次数已用完，请登录后继续使用',
                    'quota_remaining' => 0,
                ], 403);
            }
            // 记录配额使用
            $this->recordQuotaUsage($deviceId);
        }

        // 执行检测
        $result = $this->performDetection($validated);

        return response()->json([
            'success' => true,
            'data' => $result,
            'quota_remaining' => $user ? -1 : $this->getRemainingQuota($deviceId),
        ]);
    }

    /**
     * 将商品加入监控库
     * 仅限登录且付费用户
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addMonitor(Request $request)
    {
        // 验证用户登录状态
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '请先登录',
            ], 401);
        }

        // 验证用户付费状态
        if (!$user->is_paid) {
            return response()->json([
                'success' => false,
                'message' => '仅付费用户可使用监控功能',
            ], 403);
        }

        // 验证会员是否过期
        if ($user->expire_at && $user->expire_at < now()) {
            return response()->json([
                'success' => false,
                'message' => '会员已过期，请续费',
            ], 403);
        }

        // 验证请求参数
        $validated = $request->validate([
            'offer_id' => 'required|string',
            'title' => 'required|string',
            'price' => 'nullable|numeric',
            'main_image' => 'nullable|url',
            'source_url' => 'nullable|url',
        ]);

        // 获取或创建商品记录
        $product = Product::firstOrCreate(
            ['offer_id' => $validated['offer_id']],
            [
                'title' => $validated['title'],
                'price' => $validated['price'] ?? 0,
                'main_image' => $validated['main_image'],
                'source_url' => $validated['source_url'],
                'is_active_1688' => true,
            ]
        );

        // 检查监控上限
        $monitorCount = MonitorItem::where('user_id', $user->id)->count();
        if ($monitorCount >= $user->plan_limit) {
            return response()->json([
                'success' => false,
                'message' => "已达到监控上限（{$user->plan_limit}件）",
            ], 403);
        }

        // 创建监控记录（避免重复）
        $monitorItem = MonitorItem::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => '已加入监控库',
            'data' => $monitorItem->load('product'),
        ]);
    }

    /**
     * 获取当前用户的监控列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonitorList(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '请先登录',
            ], 401);
        }

        $monitorItems = MonitorItem::where('user_id', $user->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $monitorItems,
        ]);
    }

    /**
     * 获取当前设备/用户的配额状态
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuotaStatus(Request $request)
    {
        $user = Auth::user();
        $deviceId = $request->input('device_id') ?? $request->header('X-Device-Id') ?? $request->ip();

        if ($user) {
            return response()->json([
                'success' => true,
                'is_logged_in' => true,
                'is_paid' => $user->is_paid,
                'expire_at' => $user->expire_at,
                'quota_remaining' => -1, // -1表示无限次
                'plan_limit' => $user->plan_limit,
            ]);
        }

        return response()->json([
            'success' => true,
            'is_logged_in' => false,
            'is_paid' => false,
            'quota_remaining' => $this->getRemainingQuota($deviceId),
            'plan_limit' => 0,
        ]);
    }

    /**
     * 执行检测（1688状态 + 商标风险）
     *
     * @param array $productData
     * @return array
     */
    protected function performDetection(array $productData): array
    {
        // 1688状态检测
        $check1688Result = $this->check1688Service->check($productData['offer_id']);

        // 商标风险检测
        $checkTrademarkResult = $this->checkTrademarkService->check($productData['title']);

        // 获取或创建商品记录
        $product = Product::firstOrCreate(
            ['offer_id' => $productData['offer_id']],
            [
                'title' => $productData['title'],
                'price' => $productData['price'] ?? 0,
                'main_image' => $productData['main_image'],
                'source_url' => $productData['source_url'],
                'is_active_1688' => $check1688Result['status'] === 'online',
            ]
        );

        // 如果状态为offline，更新商品状态
        if ($check1688Result['status'] === 'offline') {
            $product->is_active_1688 = false;
            $product->save();
        }

        return [
            'product' => $product,
            'check_1688' => $check1688Result,
            'check_trademark' => $checkTrademarkResult,
        ];
    }

    /**
     * 检查是否还有免费配额
     *
     * @param string $deviceId 设备标识
     * @return bool
     */
    protected function hasFreeQuota(string $deviceId): bool
    {
        $todayUsage = QuotaLog::where('device_id', $deviceId)
            ->where('quota_type', QuotaLog::QUOTA_TYPE_FREE_DETECT)
            ->whereDate('created_at', today())
            ->count();

        return $todayUsage < QuotaLog::DAILY_FREE_QUOTA;
    }

    /**
     * 获取剩余免费配额
     *
     * @param string $deviceId 设备标识
     * @return int
     */
    protected function getRemainingQuota(string $deviceId): int
    {
        $todayUsage = QuotaLog::where('device_id', $deviceId)
            ->where('quota_type', QuotaLog::QUOTA_TYPE_FREE_DETECT)
            ->whereDate('created_at', today())
            ->count();

        return max(0, QuotaLog::DAILY_FREE_QUOTA - $todayUsage);
    }

    /**
     * 记录配额使用
     *
     * @param string $deviceId 设备标识
     */
    protected function recordQuotaUsage(string $deviceId): void
    {
        QuotaLog::create([
            'device_id' => $deviceId,
            'quota_type' => QuotaLog::QUOTA_TYPE_FREE_DETECT,
        ]);
    }
}