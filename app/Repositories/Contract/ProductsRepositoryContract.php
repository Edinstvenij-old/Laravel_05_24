<?php

namespace App\Repositories\Contract;

use App\Http\Requests\Admin\Products\CreateRequest;
use App\Http\Requests\Admin\Products\EditRequest;
use App\Http\Requests\Api\ProductUpdateRequest;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ProductsRepositoryContract
{
    /**
     * Create a new product.
     *
     * @param CreateRequest $request
     * @return Product|false
     */
    public function create(CreateRequest $request): Product|false;
    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param EditRequest $request
     * @return bool
     */
    public function update(Product $product, EditRequest $request): bool;
    /**
     * Update a product via API.
     *
     * @param Product $product
     * @param ProductUpdateRequest $request
     * @return bool
     */
    public function updateApi(Product $product, ProductUpdateRequest $request): bool;
    /**
     * Paginate the list of products based on request parameters.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function paginate(Request $request): LengthAwarePaginator;
}
