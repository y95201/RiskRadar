<?php

namespace App\Admin\Controllers;

use App\Services\MonitorItemService;
use Slowlyo\OwlAdmin\Controllers\AdminController;

/**
 * 监控库管理
 *
 * @property MonitorItemService $service
 */
class MonitorItemController extends AdminController
{
	protected string $serviceName = MonitorItemService::class;

	public function list()
	{
		$crud = $this->baseCRUD()
			->filterTogglable(false)
			->columns([
				amis()->TableColumn('id', 'ID')->sortable(),
				amis()->TableColumn('user_id', 'UserId'),
				amis()->TableColumn('product_id', 'ProductId'),
				amis()->TableColumn('created_at', admin_trans('admin.created_at'))->type('datetime')->sortable(),
				amis()->TableColumn('updated_at', admin_trans('admin.updated_at'))->type('datetime')->sortable(),
				$this->rowActions('page')
			]);

		return $this->baseList($crud);
	}

	public function form($isEdit = false)
	{
		return $this->baseForm(true)->body([
			amis()->TextControl('user_id', 'UserId'),
			amis()->TextControl('product_id', 'ProductId'),
		]);
	}

	public function detail()
	{
		return $this->baseDetail()->body([
			amis()->TextControl('id', 'ID')->static(),
			amis()->TextControl('user_id', 'UserId')->static(),
			amis()->TextControl('product_id', 'ProductId')->static(),
			amis()->TextControl('created_at', admin_trans('admin.created_at'))->static(),
			amis()->TextControl('updated_at', admin_trans('admin.updated_at'))->static(),
		]);
	}
}