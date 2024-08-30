<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'moderator']);
        Role::create(['name' => 'customer']);
        Role::create(['name' => 'user']);
    }

    public function test_registration_with_valid_data()
    {
        $data = [
            'name' => 'Denys',
            'lastname' => 'Test',
            'phone' => '1234567890',
            'birthdate' => '1990-01-01',
            'email' => 'admin@admin.com',
            'password' => 'test1234',
            'password_confirmation' => 'test1234'
        ];

        $response = $this->post(route('register'), $data);

        $response->assertRedirect('/home');
        $this->assertDatabaseHas('users', [
            'email' => 'admin@admin.com'
        ]);
    }

    public function test_registration_with_invalid_data()
    {
        $data = [
            'name' => '',
            'lastname' => '',
            'phone' => '12345', // Неверный телефон
            'birthdate' => '1990-01-01',
            'email' => 'not-an-email', // Неверный email
            'password' => 'test1234',
            'password_confirmation' => 'different-password' // Пароли не совпадают
        ];

        $response = $this->post(route('register'), $data);

        $response->assertSessionHasErrors([
            'name',
            'lastname',
            'email',
            'phone',
            'password'
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'not-an-email'
        ]);
    }

    public function test_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => Hash::make('test1234')
        ])->assignRole('admin');

        $response = $this->post(route('login'), [
            'email' => 'admin@admin.com',
            'password' => 'test1234'
        ]);

        $response->assertRedirect('/account');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => Hash::make('test1234')
        ]);

        $response = $this->post(route('login'), [
            'email' => 'admin@admin.com',
            'password' => 'wrong-password'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_access_for_admin_role()
    {
        $adminRole = Role::findByName('admin');
        $admin = User::factory()->create()->assignRole($adminRole);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_admin_access_denied_for_non_admin_role()
    {
        $userRole = Role::findByName('user');
        $user = User::factory()->create()->assignRole($userRole);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }
}
