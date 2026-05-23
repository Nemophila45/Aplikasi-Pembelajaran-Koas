<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class MedicalRecordAttachmentValidationTest extends TestCase
{
    public function test_allowed_attachment_types_are_accepted_when_creating_medical_record(): void
    {
        $this->prepareAttachmentValidationContext();

        foreach ($this->allowedAttachmentFixtures() as [$filename, $mime, $factory]) {
            $patient = Patient::factory()->create();

            $response = $this->submitStoreRequest($patient, $factory($filename, $mime));

            $this->assertSame(302, $response->getStatusCode());

            $record = $patient->medicalRecords()->latest('id')->first();

            $this->assertNotNull($record);
            $this->assertNotNull($record?->attachment_path);
            $this->assertTrue(Storage::disk('medical-records')->exists($record->attachment_path));
        }
    }

    public function test_allowed_attachment_types_are_accepted_when_updating_medical_record(): void
    {
        $this->prepareAttachmentValidationContext();

        foreach ($this->allowedAttachmentFixtures() as [$filename, $mime, $factory]) {
            $patient = Patient::factory()->create();
            $medicalRecord = $this->createRecordWithAttachment($patient);
            $oldPath = $medicalRecord->attachment_path;

            $response = $this->submitUpdateRequest($patient, $medicalRecord, $factory($filename, $mime));

            $this->assertSame(302, $response->getStatusCode());

            $freshRecord = $medicalRecord->fresh();

            $this->assertNotNull($freshRecord);
            $this->assertNotNull($freshRecord->attachment_path);
            $this->assertTrue(Storage::disk('medical-records')->exists($freshRecord->attachment_path));
            $this->assertNotSame($oldPath, $freshRecord->attachment_path);
            $this->assertFalse(Storage::disk('medical-records')->exists($oldPath));
        }
    }

    public function test_disallowed_attachment_types_are_rejected_when_creating_medical_record(): void
    {
        $this->prepareAttachmentValidationContext();

        foreach ($this->disallowedAttachmentFixtures() as [$filename, $mime, $factory]) {
            $patient = Patient::factory()->create();

            $response = $this->submitStoreRequest($patient, $factory($filename, $mime));

            $this->assertSame(302, $response->getStatusCode());
            $this->assertSame(0, $patient->medicalRecords()->count());
        }
    }

    public function test_disallowed_attachment_types_are_rejected_when_updating_medical_record(): void
    {
        $this->prepareAttachmentValidationContext();

        foreach ($this->disallowedAttachmentFixtures() as [$filename, $mime, $factory]) {
            $patient = Patient::factory()->create();
            $medicalRecord = $this->createRecordWithAttachment($patient);
            $oldPath = $medicalRecord->attachment_path;

            $response = $this->submitUpdateRequest(
                $patient,
                $medicalRecord,
                $factory($filename, $mime),
                [
                    'keluhan' => 'Keluhan baru',
                    'diagnosa' => 'Update Test',
                    'dokter' => 'dr. Baru',
                    'catatan' => 'Catatan berubah',
                ]
            );

            $this->assertSame(302, $response->getStatusCode());

            $freshRecord = $medicalRecord->fresh();

            $this->assertNotNull($freshRecord);
            $this->assertSame($oldPath, $freshRecord->attachment_path);
            $this->assertSame('dr. Lama', $freshRecord->dokter);
            $this->assertTrue(Storage::disk('medical-records')->exists($oldPath));
        }
    }

    private function prepareAttachmentValidationContext(): void
    {
        Artisan::call('migrate', ['--force' => true]);
        $this->withoutMiddleware(VerifyCsrfToken::class);
        Storage::fake('medical-records');

        $doctor = User::factory()->create([
            'role' => UserRole::DOCTOR,
        ]);

        Auth::setUser($doctor);
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: callable(string, string): UploadedFile}>
     */
    private function allowedAttachmentFixtures(): array
    {
        return [
            ['lampiran.pdf', 'application/pdf', fn (string $filename, string $mime): UploadedFile => UploadedFile::fake()->create($filename, 120, $mime)],
            ['foto.jpg', 'image/jpeg', fn (string $filename, string $mime): UploadedFile => UploadedFile::fake()->create($filename, 120, $mime)],
            ['foto.jpeg', 'image/jpeg', fn (string $filename, string $mime): UploadedFile => UploadedFile::fake()->create($filename, 120, $mime)],
            ['foto.png', 'image/png', fn (string $filename, string $mime): UploadedFile => UploadedFile::fake()->create($filename, 120, $mime)],
        ];
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: callable(string, string): UploadedFile}>
     */
    private function disallowedAttachmentFixtures(): array
    {
        return [
            ['shell.php', 'application/x-php', fn (string $filename, string $mime): UploadedFile => UploadedFile::fake()->create($filename, 12, $mime)],
            ['installer.exe', 'application/x-msdownload', fn (string $filename, string $mime): UploadedFile => UploadedFile::fake()->create($filename, 12, $mime)],
        ];
    }

    private function submitStoreRequest(Patient $patient, UploadedFile $attachment): Response
    {
        return $this->app->handle(
            Request::create(
                "/patients/{$patient->id}/records",
                'POST',
                [
                    'tanggal_kunjungan' => now()->toDateString(),
                    'keluhan' => 'Keluhan valid',
                    'diagnosa' => 'Hipertensi',
                    'dokter' => 'dr. Test',
                    'catatan' => 'Catatan internal',
                ],
                [],
                [
                    'attachment' => $attachment,
                ]
            )
        );
    }

    private function submitUpdateRequest(
        Patient $patient,
        MedicalRecord $medicalRecord,
        UploadedFile $attachment,
        array $overrides = []
    ): Response {
        return $this->app->handle(
            Request::create(
                "/patients/{$patient->id}/records/{$medicalRecord->id}",
                'PUT',
                array_merge([
                    'tanggal_kunjungan' => now()->toDateString(),
                    'keluhan' => 'Keluhan valid',
                    'diagnosa' => 'Hipertensi',
                    'dokter' => 'dr. Test',
                    'catatan' => 'Catatan internal',
                ], $overrides),
                [],
                [
                    'attachment' => $attachment,
                ]
            )
        );
    }

    private function createRecordWithAttachment(Patient $patient): MedicalRecord
    {
        $attachmentPath = UploadedFile::fake()
            ->create('lampiran-awal.pdf', 120, 'application/pdf')
            ->store('medical-records', 'medical-records');

        return MedicalRecord::create([
            'patient_id' => $patient->id,
            'tanggal_kunjungan' => now()->toDateString(),
            'keluhan' => 'Keluhan awal',
            'diagnosa' => 'Hipertensi',
            'dokter' => 'dr. Lama',
            'catatan' => 'Catatan awal',
            'attachment_path' => $attachmentPath,
        ]);
    }
}
