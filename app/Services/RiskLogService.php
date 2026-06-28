<?php

namespace App\Services;

use App\Models\RiskLog;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * 巡检日志服务
 *
 * @method RiskLog getModel()
 * @method RiskLog|\Illuminate\Database\Query\Builder query()
 */
class RiskLogService extends AdminService
{
	protected string $modelName = RiskLog::class;
}
