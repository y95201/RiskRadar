<?php
/*
 * @Description: 
 * @Author: Y95201
 * @Date: 2026-06-26 16:29:58
 * @LastEditors: Y95201
 * @LastEditTime: 2026-06-26 16:48:18
 */

namespace App\Admin\Controllers;

use App\Services\Service;
use Slowlyo\OwlAdmin\Controllers\AdminController;

/**
 * 巡检日志管理
 *
 * @property Service $service
 */
class Controller extends AdminController
{
	protected string $serviceName = Service::class;

	public function list()
	{
		$crud = $this->baseCRUD()
			->filterTogglable(true)
			->columns([
				amis()->TableColumn('id', 'ID')->sortable(),
				amis()->TableColumn('target_type', '检测类型')
					->type('mapping')
					->map([
						'1688' => '1688状态',
						'trademark' => '商标风险',
					]),
				amis()->TableColumn('target_id', '目标ID(如offerId)'),
				amis()->TableColumn('status', '状态')
					->type('mapping')
					->map([
						'online' => '在线',
						'offline' => '离线',
						'high' => '高风险',
						'low' => '低风险',
					])
					->label([
						'online' => 'success',
						'offline' => 'danger',
						'high' => 'warning',
						'low' => 'info',
					]),
				amis()->TableColumn('reason', '原因描述'),
				amis()->TableColumn('checked_at', '检测时间')
					->type('datetime'),
				amis()->TableColumn('created_at', admin_trans('admin.created_at'))->type('datetime')->sortable(),
				$this->rowActions('page')
			]);

		return $this->baseList($crud);
	}

	public function form($isEdit = false)
	{
		return $this->baseForm($isEdit)->body([
			amis()->SelectControl('target_type', '检测类型')
				->options([
					['label' => '1688状态', 'value' => '1688'],
					['label' => '商标风险', 'value' => 'trademark'],
				])
				->required(),
			amis()->TextControl('target_id', '目标ID(如offerId)')->required(),
			amis()->SelectControl('status', '状态')
				->options([
					['label' => '在线', 'value' => 'online'],
					['label' => '离线', 'value' => 'offline'],
					['label' => '高风险', 'value' => 'high'],
					['label' => '低风险', 'value' => 'low'],
				])
				->required(),
			amis()->TextareaControl('reason', '原因描述'),
			amis()->DateTimeControl('checked_at', '检测时间'),
		]);
	}

	public function detail()
	{
		return $this->baseDetail()->body([
			amis()->TextControl('id', 'ID')->static(),
			amis()->TextControl('target_type', '检测类型')->static(),
			amis()->TextControl('target_id', '目标ID(如offerId)')->static(),
			amis()->TextControl('status', '状态')->static(),
			amis()->TextareaControl('reason', '原因描述')->static(),
			amis()->TextControl('checked_at', '检测时间')->static(),
			amis()->TextControl('created_at', admin_trans('admin.created_at'))->static(),
			amis()->TextControl('updated_at', admin_trans('admin.updated_at'))->static(),
		]);
	}
}
