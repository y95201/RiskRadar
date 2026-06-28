<?php

namespace App\Console\Commands;

use App\Models\MonitorItem;
use App\Models\Product;
use App\Models\RiskLog;
use App\Services\Check1688Service;
use App\Services\CheckTrademarkService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('monitor:daily-inspect')]
#[Description('每日巡检监控库中的1688商品，检测下架状态和商标侵权风险')]
class DailyInspect extends Command
{
    protected $check1688Service;
    protected $checkTrademarkService;

    public function __construct(
        Check1688Service $check1688Service,
        CheckTrademarkService $checkTrademarkService
    ) {
        parent::__construct();
        $this->check1688Service = $check1688Service;
        $this->checkTrademarkService = $checkTrademarkService;
    }

    public function handle(): int
    {
        $this->info('开始执行每日巡检任务...');

        $monitorItems = MonitorItem::with('product')->get();
        $totalCount = $monitorItems->count();
        $successCount = 0;
        $failCount = 0;

        $this->info("共检测 {$totalCount} 个监控项");

        foreach ($monitorItems as $index => $monitorItem) {
            $product = $monitorItem->product;
            
            $this->info("正在检测 [{$index + 1}/{$totalCount}]: {$product->offer_id} - {$product->title}");

            try {
                $this->processMonitorItem($monitorItem, $product);
                $successCount++;

                $delay = rand(2, 5);
                $this->info("等待 {$delay} 秒...");
                sleep($delay);

            } catch (\Exception $e) {
                $failCount++;
                $this->error("检测失败: {$product->offer_id} - {$e->getMessage()}");
            }
        }

        $this->info("巡检完成！成功: {$successCount}，失败: {$failCount}");

        return Command::SUCCESS;
    }

    protected function processMonitorItem(MonitorItem $monitorItem, Product $product): void
    {
        $check1688Result = $this->check1688Service->check($product->offer_id);

        if ($check1688Result['status'] === 'offline' && $product->is_active_1688) {
            $product->is_active_1688 = false;
            $product->save();

            $this->createRiskLog(
                RiskLog::TYPE_1688,
                $product->offer_id,
                'offline',
                $check1688Result['reason']
            );

            $this->warn("检测到商品下架: {$product->offer_id}");
        } elseif ($check1688Result['status'] === 'online' && !$product->is_active_1688) {
            $product->is_active_1688 = true;
            $product->save();

            $this->createRiskLog(
                RiskLog::TYPE_1688,
                $product->offer_id,
                'online',
                '商品重新上架'
            );

            $this->info("商品重新上架: {$product->offer_id}");
        }

        $checkTrademarkResult = $this->checkTrademarkService->check($product->title);

        $lastTrademarkLog = RiskLog::where('target_type', RiskLog::TYPE_TRADEMARK)
            ->where('target_id', $product->offer_id)
            ->orderBy('checked_at', 'desc')
            ->first();

        $lastStatus = $lastTrademarkLog ? $lastTrademarkLog->status : null;

        if ($checkTrademarkResult['status'] !== $lastStatus) {
            $this->createRiskLog(
                RiskLog::TYPE_TRADEMARK,
                $product->offer_id,
                $checkTrademarkResult['status'],
                $checkTrademarkResult['reason']
            );

            if ($checkTrademarkResult['status'] === 'high') {
                $this->warn("检测到商标风险: {$product->offer_id} - {$checkTrademarkResult['reason']}");
            } else {
                $this->info("商标风险已解除: {$product->offer_id}");
            }
        }
    }

    protected function createRiskLog(string $targetType, string $targetId, string $status, string $reason): void
    {
        RiskLog::create([
            'target_type' => $targetType,
            'target_id' => $targetId,
            'status' => $status,
            'reason' => $reason,
            'checked_at' => now(),
        ]);
    }
}
