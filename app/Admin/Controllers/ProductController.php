<?php

namespace App\Admin\Controllers;

use App\Services\ProductService;
use Slowlyo\OwlAdmin\Controllers\AdminController;

/**
 * 商品库管
 *
 * @property ProductService $service
 */
class ProductController extends AdminController
{
	protected string $serviceName = ProductService::class;

	public function list()
	{
		$crud = $this->baseCRUD()
			->filterTogglable(false)
			->columns([
				amis()->TableColumn('id', 'ID')->sortable(),
				amis()->TableColumn('offer_id', '1688商品ID'),
				amis()->TableColumn('title', '商品标题'),
				amis()->TableColumn('price', '商品价格'),
				amis()->TableColumn('main_image', '商品主图URL'),
				amis()->TableColumn('source_url', '1688商品源URL'),
				amis()->TableColumn('is_active_1688', '1688是否在架'),
				amis()->TableColumn('created_at', admin_trans('admin.created_at'))->type('datetime')->sortable(),
				amis()->TableColumn('updated_at', admin_trans('admin.updated_at'))->type('datetime')->sortable(),
				$this->rowActions('page')
			]);

		return $this->baseList($crud);
	}

	public function form($isEdit = false)
	{
		return $this->baseForm(true)->body([
			amis()->TextControl('offer_id', '1688商品ID'),
			amis()->TextControl('title', '商品标题'),
			amis()->TextControl('price', '商品价格'),
			amis()->TextControl('main_image', '商品主图URL'),
			amis()->TextControl('source_url', '1688商品源URL'),
			amis()->TextControl('is_active_1688', '1688是否在架'),
		]);
	}

	public function detail()
	{
		return $this->baseDetail()->body([
			amis()->TextControl('id', 'ID')->static(),
			amis()->TextControl('offer_id', '1688商品ID')->static(),
			amis()->TextControl('title', '商品标题')->static(),
			amis()->TextControl('price', '商品价格')->static(),
			amis()->TextControl('main_image', '商品主图URL')->static(),
			amis()->TextControl('source_url', '1688商品源URL')->static(),
			amis()->TextControl('is_active_1688', '1688是否在架')->static(),
			amis()->TextControl('created_at', admin_trans('admin.created_at'))->static(),
			amis()->TextControl('updated_at', admin_trans('admin.updated_at'))->static(),
		]);
	}
}