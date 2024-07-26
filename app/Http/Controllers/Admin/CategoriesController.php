<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permissions\Category as Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Categories\CreateRequest;
use App\Http\Requests\Admin\Categories\EditRequest;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{

    public function index()
    {
        $categories = Category::with(['parent'])
            ->paginate(10);

        return view('admin/categories/index', compact('categories'));
    }

    public function create()
    {
        return view('admin/categories/create', [
            'categories' => Category::select(['id', 'name'])->get()
        ]);
    }

    public function store(CreateRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        Category::create($data);

        notify()->success("Category [$data[name]] was created.");

        return redirect()->route('admin.categories.index');
    }

    public function edit(Category $category)
    {
        return view('admin/categories/edit', [
            'categories' => Category::select(['id', 'name'])
                ->where('id', '!=', $category->id)
                ->get(),
            'category' => $category
        ]);
    }

    public function update(EditRequest $request, Category $category)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        $category->update($data);

        notify()->success("Category '{$data['name']}' was updated!");

        return redirect()->route('admin.categories.index', $category);
    }

    public function destroy(Category $category)
    {
        $this->middleware('permission:' . Permission::DELETE->value);

        $category->delete();

        notify()->success("Category '{$category->name}' was removed!");

        return redirect()->route('admin.categories.index');
    }
}
