<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PatientAgeFilterTest extends TestCase
{
    public function test_patient_index_shows_consistent_last_age_bucket_label(): void
    {
        Artisan::call('migrate', ['--force' => true]);

        $user = User::factory()->create([
            'role' => UserRole::DOCTOR,
        ]);

        Auth::setUser($user);

        $response = $this->app->handle(Request::create('/patients', 'GET'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('101+ tahun', $response->getContent());
        $this->assertStringNotContainsString('100 + tahun', $response->getContent());
    }
}
