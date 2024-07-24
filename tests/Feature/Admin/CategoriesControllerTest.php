<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\Category;
use Tests\Feature\Traits\SetupTrait;
use Tests\TestCase;

class CategoriesControllerTest extends TestCase
{
    use SetupTrait;

    public function test_allow_see_categories_for_admin_role()
    {
        $categories = Category::factory(5)->create();

        $response = $this->actingAs($this->user())
            ->get(route('admin.categories.index'));

        $response->assertSuccessful();
        $response->assertViewIs('admin.categories.index');
        $response->assertSeeInOrder($categories->pluck('name')->toArray());
    }

    public function test_allow_see_categories_for_moderator_role()
    {
        $categories = Category::factory(5)->create();

        $response = $this->actingAs($this->user(Role::MODERATOR))
            ->get(route('admin.categories.index'));

        $response->assertSuccessful();
        $response->assertViewIs('admin.categories.index');
        $response->assertSeeInOrder($categories->pluck('name')->toArray());
    }

    public function test_does_not_allow_see_categories_for_customer_role()
    {
        $response = $this->actingAs($this->user(Role::CUSTOMER))
            ->get(route('admin.categories.index'));

        $response->assertForbidden();
    }

    public function test_it_creates_category_with_valid_data()
    {
        $data = Category::factory()->makeOne()->toArray();

        $this->assertDatabaseMissing('categories', [
            'name' => $data['name']
        ]);

        $response = $this->actingAs($this->user())
            ->post(route('admin.categories.store'), $data);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.categories.index'));

        $response->assertSessionHas('toasts');
        $response->assertSessionHas(
            'toasts',
            fn($collection) => $collection->first()['message'] === "Category [$data[name]] was created."
        );

        $this->assertDatabaseHas('categories', [
            'name' => $data['name']
        ]);
    }

    public function test_it_creates_category_with_parent_from_valid_data()
    {
        $parent = Category::factory()->createOne();
        $data = Category::factory()->makeOne(['parent_id' => $parent->id])->toArray();

        $this->assertDatabaseMissing('categories', [
            'name' => $data['name']
        ]);

        $this->actingAs($this->user())->post(route('admin.categories.store'), $data);

        $this->assertDatabaseHas('categories', [
            'name' => $data['name'],
            'parent_id' => $parent->id
        ]);
    }

    public function test_does_not_create_category_with_invalid_name()
    {
        $data = ['name' => 'a'];

        $this->assertDatabaseMissing('categories', [
            'name' => $data['name']
        ]);

        $response = $this->actingAs($this->user())
            ->post(route('admin.categories.store'), $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
        $response->assertRedirect(route('admin.categories.create'));
        $this->assertDatabaseMissing('categories', [
            'name' => $data['name']
        ]);
    }

    public function test_does_not_create_category_with_invalid_parent_id()
    {
        $data = Category::factory()->makeOne(['parent_id' => 99999999])->toArray();

        $this->assertDatabaseMissing('categories', [
            'name' => $data['name']
        ]);

        $response = $this->actingAs($this->user())
            ->post(route('admin.categories.store'), $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['parent_id']);
        $response->assertRedirect(route('admin.categories.create'));
        $this->assertDatabaseMissing('categories', [
            'name' => $data['name']
        ]);
    }

    public function test_it_updates_category_with_valid_data()
    {
        $newName = 'updated';
        $category = Category::factory()->createOne();
        $data = array_merge($category->toArray(), ['name' => $newName]);

        $this->assertDatabaseHas('categories', [
            'name' => $category->name,
            'slug' => $category->slug
        ]);
        $this->assertDatabaseMissing('categories', [
            'name' => $newName,
            'slug' => $newName
        ]);

        $this->actingAs($this->user())
            ->put(route('admin.categories.update', $category), $data);

        $this->assertDatabaseHas('categories', [
            'name' => $newName,
            'slug' => $newName
        ]);
        $this->assertDatabaseMissing('categories', [
            'name' => $category->name,
            'slug' => $category->slug
        ]);
    }

    public function test_it_updates_category_with_invalid_data()
    {
        $category = Category::factory()->create();
        $data = ['name' => ''];

        $response = $this->actingAs($this->user())
            ->put(route('admin.categories.update', $category), $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $category->name
        ]);
    }

    public function test_it_removes_category_for_admin_role()
    {
        $category = Category::factory()->create();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id
        ]);

        $this->actingAs($this->user())
            ->delete(route('admin.categories.destroy', $category));

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    public function test_it_removes_category_and_set_null_to_child()
    {
        $category = Category::factory()->createOne();
        $child = Category::factory()->createOne(['parent_id' => $category->id]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id
        ]);
        $this->assertEquals($category->id, $child->parent_id);

        $this->actingAs($this->user())
            ->delete(route('admin.categories.destroy', $category));

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);

        $child->refresh();

        $this->assertNull($child->parent_id);
    }

    public function test_delete_category_denied_for_customer()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user(Role::CUSTOMER))
            ->delete(route('admin.categories.destroy', $category));

        $response->assertStatus(403);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id
        ]);
    }

    public function testCreateCategoryViewAccessForAdmin()
    {
        $response = $this->actingAs($this->user())
            ->get(route('admin.categories.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.create');
    }

    public function testCreateCategoryViewAccessDeniedForCustomer()
    {
        $response = $this->actingAs($this->user(Role::CUSTOMER))
            ->get(route('admin.categories.create'));

        $response->assertStatus(403);
    }

    public function testEditCategoryViewAccessForAdmin()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user())
            ->get(route('admin.categories.edit', $category));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.edit');
    }

    public function testEditCategoryViewAccessDeniedForCustomer()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user(Role::CUSTOMER))
            ->get(route('admin.categories.edit', $category));

        $response->assertStatus(403);
    }
}
