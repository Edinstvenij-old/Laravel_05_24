<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permissions\Category as Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Products\CreateRequest;
use App\Http\Requests\Admin\Products\EditRequest; // Предполагается, что существует EditRequest для обновлений продуктов
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contract\ProductsRepositoryContract;
use Illuminate\Support\Str;

class ProductsController extends Controller
{
    /**
     * Отображение списка ресурсов.
     */
    public function index()
    {
        $products = Product::with(['categories'])
            ->sortable()
            ->paginate(10);

        return view('admin/products/index', compact('products'));
    }

    /**
     * Показ формы для создания нового ресурса.
     */
    public function create()
    {
        return view('admin/products/create', ['categories' => Category::select(['id', 'name'])->get()]);
    }

    /**
     * Сохранение нового ресурса в хранилище.
     */
    public function store(CreateRequest $request, ProductsRepositoryContract $repository)
    {
        if ($product = $repository->create($request)) {
            return redirect()->route('admin.products.index');
        }

        return redirect()->back()->withInput();
    }

    /**
     * Показ формы для редактирования указанного ресурса.
     */
    public function edit(Product $product)
    {
        return view('admin/products/edit', [
            'categories' => Category::select(['id', 'name'])
                ->whereNot('id', $product->id)
                ->get(),
            'product' => $product
        ]);
    }

    /**
     * Обновление указанного ресурса в хранилище.
     */
    public function update(EditRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        $product->updateOrFail($data);

        return redirect()->route('admin.products.edit', $product);
    }

    /**
     * Удаление указанного ресурса из хранилища.
     */
    public function destroy(Product $product)
    {
        $this->middleware('permission:' . Permission::DELETE->value);

        $product->deleteOrFail();

        return redirect()->route('admin.products.index');
    }
}
