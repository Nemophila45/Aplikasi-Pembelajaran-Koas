<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MedicalRecordAttachmentAccessTest extends TestCase
{
    public function test_guest_is_redirected_when_opening_attachment_download_route(): void
    {
        Artisan::call('migrate', ['--force' => true]);

        [$patient, $medicalRecord] = $this->createPatientWithAttachment();

        $response = $this->app->handle(
            Request::create("/patients/{$patient->id}/records/{$medicalRecord->id}/download", 'GET')
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('login'), $response->headers->get('Location'));
    }

    public function test_admin_doctor_and_koas_can_download_attachment_through_controller(): void
    {
        Artisan::call('migrate', ['--force' => true]);

        foreach ([UserRole::ADMIN, UserRole::DOCTOR, UserRole::KOAS] as $role) {
            [$patient, $medicalRecord] = $this->createPatientWithAttachment();

            $user = User::factory()->create([
                'role' => $role,
            ]);

            Auth::setUser($user);

            $response = $this->app->handle(
                Request::create("/patients/{$patient->id}/records/{$medicalRecord->id}/download", 'GET')
            );

            $this->assertSame(200, $response->getStatusCode());
            $this->assertStringContainsString(
                'attachment',
                (string) $response->headers->get('Content-Disposition')
            );
        }
    }

    public function test_management_is_forbidden_from_attachment_download_route(): void
    {
        Artisan::call('migrate', ['--force' => true]);

        [$patient, $medicalRecord] = $this->createPatientWithAttachment();

        $user = User::factory()->create([
            'role' => UserRole::MANAGEMENT,
        ]);

        Auth::setUser($user);

        $response = $this->app->handle(
            Request::create("/patients/{$patient->id}/records/{$medicalRecord->id}/download", 'GET')
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_public_storage_path_does_not_expose_medical_record_attachment(): void
    {
        Artisan::call('migrate', ['--force' => true]);

        [$patient, $medicalRecord, $attachmentPath] = $this->createPatientWithAttachment();

        $response = $this->app->handle(
            Request::create("/storage/{$attachmentPath}", 'GET')
        );

        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertTrue(Storage::disk('medical-records')->exists($attachmentPath));
        $this->assertFalse(Storage::disk('public')->exists($attachmentPath));
    }

    /**
     * @return array{0: Patient, 1: MedicalRecord, 2: string}
     */
    private function createPatientWithAttachment(): array
    {
        Storage::fake('medical-records');
        Storage::fake('public');

        $patient = Patient::factory()->create();

        $attachmentPath = UploadedFile::fake()
            ->create('rekam-medis.pdf', 128, 'application/pdf')
            ->store('medical-records', 'medical-records');

        $medicalRecord = MedicalRecord::create([
            'patient_id' => $patient->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'keluhan' => 'Demam dan batuk',
            'diagnosa' => 'Hipertensi',
            'dokter' => 'dr. Test',
            'catatan' => 'Lampiran internal',
            'attachment_path' => $attachmentPath,
        ]);

        return [$patient, $medicalRecord, $attachmentPath];
    }
}
