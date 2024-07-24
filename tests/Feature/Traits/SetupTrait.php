<?php

namespace Tests\Feature\Traits;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\PermissionsAndRolesSeeder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

trait SetupTrait
{
    use RefreshDatabase;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function afterRefreshingDatabase(): void
    {
        $this->seed(PermissionsAndRolesSeeder::class);
    }

    protected function user(Role $role = Role::ADMIN): User
    {
        $user = User::role($role->value)->first();

        if (!$user) {
            $user = User::factory()->create([
                'name' => $role->value . ' User',
                'email' => strtolower($role->value) . '@example.com',
                'password' => Hash::make('password'),
            ]);
            $user->assignRole($role->value);
        }

        return $user;
    }
}
