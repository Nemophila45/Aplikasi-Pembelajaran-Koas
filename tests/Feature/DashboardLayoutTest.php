<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DashboardLayoutTest extends TestCase
{
    public function test_dashboard_topbar_shows_user_identity_for_all_dashboard_roles(): void
    {
        Artisan::call('migrate', ['--force' => true]);

        foreach ([
            UserRole::ADMIN,
            UserRole::DOCTOR,
            UserRole::KOAS,
            UserRole::MANAGEMENT,
        ] as $role) {
            $user = User::factory()->create([
                'name' => ucfirst($role->value) . ' Dashboard',
                'role' => $role,
            ]);

            Auth::setUser($user);

            $response = $this->app->handle(Request::create('/patients', 'GET'));

            $this->assertSame(200, $response->getStatusCode());
            $this->assertStringContainsString('Menu', $response->getContent());
            $this->assertStringContainsString($user->name, $response->getContent());
            $this->assertStringContainsString($role->label(), $response->getContent());
            $this->assertStringContainsString('Logout', $response->getContent());
        }
    }
}
