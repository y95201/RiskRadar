<?php

namespace App\Services;

use App\Models\Product;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * 商品库管
 *
 * @method Product getModel()
 * @method Product|\Illuminate\Database\Query\Builder query()
 */
class ProductService extends AdminService
{
	protected string $modelName = Product::class;
}