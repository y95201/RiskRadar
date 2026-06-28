<?php

namespace App\Services;

use App\Models\MonitorItem;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * 监控库管理
 *
 * @method MonitorItem getModel()
 * @method MonitorItem|\Illuminate\Database\Query\Builder query()
 */
class MonitorItemService extends AdminService
{
	protected string $modelName = MonitorItem::class;
}