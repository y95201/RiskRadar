<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-26 16:29:58
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 16:47:45
 */

namespace App\Services;

use App\Models\RiskLog;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * 巡检日志管理
 *
 * @method RiskLog getModel()
 * @method RiskLog|\Illuminate\Database\Query\Builder query()
 */
class Service extends AdminService
{
	protected string $modelName = RiskLog::class;
}
