<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MedicalRecordKeluhanTest extends TestCase
{
    public function test_keluhan_field_is_available_and_saved_from_create_form(): void
    {
        Artisan::call('migrate', ['--force' => true]);
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $doctor = User::factory()->create([
            'role' => UserRole::DOCTOR,
        ]);
        Auth::setUser($doctor);

        $patient = Patient::factory()->create();
        $keluhan = 'Demam tinggi dan batuk sejak dua hari';

        $createFormResponse = $this->app->handle(
            Request::create("/patients/{$patient->id}/records/create", 'GET')
        );

        $this->assertSame(200, $createFormResponse->getStatusCode());
        $this->assertStringContainsString('Keluhan Pasien', $createFormResponse->getContent());

        $storeResponse = $this->app->handle(
            Request::create("/patients/{$patient->id}/records", 'POST', [
                'tanggal_kunjungan' => now()->toDateString(),
                'keluhan' => $keluhan,
                'diagnosa' => 'Infeksi Saluran Pernapasan Akut',
                'dokter' => 'dr. Test',
                'catatan' => 'Catatan internal',
            ])
        );

        $this->assertSame(302, $storeResponse->getStatusCode());

        $savedRecord = $patient->medicalRecords()->latest('id')->first();
        $this->assertNotNull($savedRecord);
        $this->assertSame($keluhan, $savedRecord?->keluhan);

        $detailResponse = $this->app->handle(
            Request::create("/patients/{$patient->id}", 'GET')
        );

        $this->assertSame(200, $detailResponse->getStatusCode());
        $this->assertStringContainsString($keluhan, $detailResponse->getContent());
    }

    public function test_keluhan_can_be_edited_on_medical_record_form(): void
    {
        Artisan::call('migrate', ['--force' => true]);
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $doctor = User::factory()->create([
            'role' => UserRole::DOCTOR,
        ]);
        Auth::setUser($doctor);

        $patient = Patient::factory()->create();
        $medicalRecord = MedicalRecord::create([
            'patient_id' => $patient->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'keluhan' => 'Batuk dan pilek',
            'diagnosa' => 'Common Cold',
            'dokter' => 'dr. Lama',
            'catatan' => 'Catatan awal',
        ]);

        $editFormResponse = $this->app->handle(
            Request::create("/patients/{$patient->id}/records/{$medicalRecord->id}/edit", 'GET')
        );

        $this->assertSame(200, $editFormResponse->getStatusCode());
        $this->assertStringContainsString('Keluhan Pasien', $editFormResponse->getContent());
        $this->assertStringContainsString('Batuk dan pilek', $editFormResponse->getContent());

        $updatedKeluhan = 'Nyeri tenggorokan dan demam ringan';

        $updateResponse = $this->app->handle(
            Request::create("/patients/{$patient->id}/records/{$medicalRecord->id}", 'PUT', [
                'tanggal_kunjungan' => now()->toDateString(),
                'keluhan' => $updatedKeluhan,
                'diagnosa' => 'Common Cold',
                'dokter' => 'dr. Baru',
                'catatan' => 'Catatan diperbarui',
            ])
        );

        $this->assertSame(302, $updateResponse->getStatusCode());
        $this->assertSame($updatedKeluhan, $medicalRecord->fresh()?->keluhan);

        $detailResponse = $this->app->handle(
            Request::create("/patients/{$patient->id}", 'GET')
        );

        $this->assertSame(200, $detailResponse->getStatusCode());
        $this->assertStringContainsString($updatedKeluhan, $detailResponse->getContent());
    }
}
