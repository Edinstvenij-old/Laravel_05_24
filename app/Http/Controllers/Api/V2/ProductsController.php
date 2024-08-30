<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Products\CreateRequest;
use App\Http\Requests\Api\ProductUpdateRequest;
use App\Http\Resources\Products\ProductResourceV2;
use App\Http\Resources\Products\ProductsCollectionV2;
use App\Models\Product;
use App\Repositories\Contract\ProductsRepositoryContract;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request, ProductsRepositoryContract $repository)
    {
        return new ProductsCollectionV2($repository->paginate($request));
    }

    public function store(CreateRequest $request, ProductsRepositoryContract $repository)
    {
        if ($product = $repository->create($request)) {
            return response()->json([
                'status' => 'success',
                'data' => new ProductResourceV2($product)
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid input data'
        ], 422);
    }

    public function show(Product $product)
    {
        $product->loadMissing(['categories', 'images']);

        return new ProductResourceV2($product);
    }

    public function update(ProductUpdateRequest $request, Product $product, ProductsRepositoryContract $repository)
    {
        if ($repository->updateApi($product, $request)) {
            return response()->json([
                'status' => 'success',
                'data' => new ProductResourceV2($product)
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid input data'
        ], 422);
    }

    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            $product->categories()->detach();
            $product->images()->delete();
            $product->deleteOrFail();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => new ProductResourceV2($product)
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            logs()->error($exception);

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }
    }
}
