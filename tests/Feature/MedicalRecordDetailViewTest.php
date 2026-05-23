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

class MedicalRecordDetailViewTest extends TestCase
{
    public function test_guest_is_redirected_from_medical_record_detail_route(): void
    {
        Artisan::call('migrate', ['--force' => true]);

        Auth::logout();
        [$patient, $medicalRecord] = $this->createPatientWithMedicalRecord();

        $response = $this->app->handle(
            Request::create("/patients/{$patient->id}/records/{$medicalRecord->id}", 'GET')
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('login'), $response->headers->get('Location'));
    }

    public function test_internal_roles_can_open_medical_record_detail_and_see_complete_information(): void
    {
        Artisan::call('migrate', ['--force' => true]);

        [$patient, $medicalRecord] = $this->createPatientWithMedicalRecord();

        foreach ([UserRole::ADMIN, UserRole::DOCTOR, UserRole::KOAS, UserRole::MANAGEMENT] as $role) {
            $user = User::factory()->create([
                'role' => $role,
            ]);

            Auth::setUser($user);

            $response = $this->app->handle(
                Request::create("/patients/{$patient->id}/records/{$medicalRecord->id}", 'GET')
            );

            $this->assertSame(200, $response->getStatusCode());
            $this->assertStringContainsString('Detail Riwayat Medis', $response->getContent());
            $this->assertStringContainsString($medicalRecord->keluhan ?? '-', $response->getContent());
            $this->assertStringContainsString($medicalRecord->catatan ?? '-', $response->getContent());
            $this->assertStringContainsString('Lihat Lampiran', $response->getContent());
            $this->assertStringContainsString('Unduh Lampiran', $response->getContent());
            $this->assertStringContainsString(
                route('patients.records.download', [$patient, $medicalRecord, 'inline' => 1]),
                $response->getContent()
            );
            $this->assertStringContainsString(
                route('patients.records.download', [$patient, $medicalRecord]),
                $response->getContent()
            );
        }
    }

    public function test_main_patient_table_shows_detail_button_and_hides_catatan_column(): void
    {
        Artisan::call('migrate', ['--force' => true]);

        $doctor = User::factory()->create([
            'role' => UserRole::DOCTOR,
        ]);

        Auth::setUser($doctor);
        [$patient, $medicalRecord] = $this->createPatientWithMedicalRecord();

        $response = $this->app->handle(
            Request::create("/patients/{$patient->id}", 'GET')
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString(route('patients.records.show', [$patient, $medicalRecord]), $response->getContent());
        $this->assertStringContainsString('Detail', $response->getContent());
        $this->assertStringNotContainsString('<th class="px-5 py-3 text-left">Catatan</th>', $response->getContent());
    }

    /**
     * @return array{0: Patient, 1: MedicalRecord}
     */
    private function createPatientWithMedicalRecord(): array
    {
        Storage::fake('medical-records');

        $patient = Patient::factory()->create();

        $attachmentPath = UploadedFile::fake()
            ->create('lampiran-riwayat.pdf', 128, 'application/pdf')
            ->store('medical-records', 'medical-records');

        $medicalRecord = MedicalRecord::create([
            'patient_id' => $patient->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'keluhan' => 'Demam dan batuk',
            'diagnosa' => 'Influenza',
            'dokter' => 'dr. Detail',
            'catatan' => 'Catatan pemeriksaan lengkap.',
            'attachment_path' => $attachmentPath,
        ]);

        return [$patient, $medicalRecord];
    }
}
