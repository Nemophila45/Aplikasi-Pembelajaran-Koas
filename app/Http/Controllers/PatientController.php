<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PatientController extends Controller
{
    private const ATTACHMENT_DISK = 'medical-records';

    /**
     * Tampilkan daftar pasien dengan pencarian.
     */



    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $selectedDoctor = trim((string) $request->input('dokter', ''));
        $selectedDiagnosis = trim((string) $request->input('diagnosa', ''));
        $selectedAgeRange = trim((string) $request->input('umur', ''));

        $patients = Patient::query()
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $like = '%' . $search . '%';
                    $inner->where('nama', 'like', $like)
                        ->orWhere('nik', 'like', $like)
                        ->orWhere('no_rekam_medis', 'like', $like);
                });
            })
            ->when($selectedDoctor !== '', function ($query) use ($selectedDoctor): void {
                $query->whereHas('medicalRecords', function ($records) use ($selectedDoctor): void {
                    $records->where('dokter', $selectedDoctor);
                });
            })
            ->when($selectedDiagnosis !== '', function ($query) use ($selectedDiagnosis): void {
                $query->whereHas('medicalRecords', function ($records) use ($selectedDiagnosis): void {
                    $records->where('diagnosa', $selectedDiagnosis);
                });
            })
            ->when($selectedAgeRange !== '', function ($query) use ($selectedAgeRange): void {
                [$minAge, $maxAge] = array_pad(array_map('intval', explode('-', $selectedAgeRange)), 2, null);
                if ($minAge !== null && $maxAge !== null) {
                    $today = now();
                    $upperBound = $today->copy()->subYears($minAge); // tanggal lahir maks (lebih muda)
                    $lowerBound = $today->copy()->subYears($maxAge + 1)->addDay(); // tanggal lahir min (lebih tua)

                    $query->whereBetween('tanggal_lahir', [$lowerBound->startOfDay(), $upperBound->endOfDay()]);
                }
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $doctorOptions = MedicalRecord::query()
            ->select('dokter')
            ->whereNotNull('dokter')
            ->distinct()
            ->orderBy('dokter')
            ->pluck('dokter');

        $diagnosisOptions = MedicalRecord::query()
            ->select('diagnosa')
            ->whereNotNull('diagnosa')
            ->distinct()
            ->orderBy('diagnosa')
            ->pluck('diagnosa');

        $ageRanges = collect(range(1, 96, 5))->map(function (int $start): array {
            $end = min($start + 4, 100);

            return [
                'value' => "{$start} - {$end}",
                'label' => "{$start} - {$end} tahun",
            ];
        });

        $ageRanges->push([
            'value' => '101 - 150',
            'label' => '101+ tahun',
        ]);

        return view('patients.index', [
            'patients' => $patients,
            'search' => $search,
            'selectedDoctor' => $selectedDoctor,
            'selectedDiagnosis' => $selectedDiagnosis,
             'selectedAgeRange' => $selectedAgeRange,
            'doctorOptions' => $doctorOptions,
            'diagnosisOptions' => $diagnosisOptions,
            'ageOptions' => $ageRanges,
        ]);
    }

    /**
     * Detail pasien + riwayat dengan filter.
     */
    public function show(Patient $patient): View
    {
        $medicalRecords = $patient->medicalRecords()
            ->orderByDesc('tanggal_kunjungan')
            ->paginate(10);

        return view('patients.show', [
            'patient' => $patient,
            'medicalRecords' => $medicalRecords,
        ]);
    }

    /**
     * Detail riwayat medis.
     */
    public function showRecord(Patient $patient, MedicalRecord $medicalRecord): View
    {
        $this->ensureRecordBelongsToPatient($medicalRecord, $patient);

        return view('patients.records.show', [
            'patient' => $patient,
            'medicalRecord' => $medicalRecord,
        ]);
    }

    /**
     * Form tambah pasien.
     */
    public function create(): View
    {
        return view('patients.create');
    }

    /**
     * Simpan pasien baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'no_rekam_medis' => ['required', 'string', 'max:50', 'unique:patients,no_rekam_medis'],
            'nik' => ['required', 'string', 'max:20', 'unique:patients,nik'],
            'nama' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'string', 'max:20'],
            'no_hp' => ['nullable', 'string', 'max:25'],
            'alamat' => ['nullable', 'string'],
            'provinsi' => ['nullable', 'string', 'max:255'],
            'kabupaten_kota' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kelurahan_desa' => ['nullable', 'string', 'max:255'],
        ]);

        $patient = Patient::create($validated);

        return redirect()
            ->route('patients.show', $patient)
            ->with('status', 'Pasien berhasil ditambahkan.');
    }

    /**
     * Form tambah riwayat.
     */
    public function createRecord(Patient $patient): View
    {
        return view('patients.records.create', [
            'patient' => $patient,
        ]);
    }

    /**
     * Simpan riwayat baru.
     */
    public function storeRecord(Request $request, Patient $patient): RedirectResponse
    {
        $data = $this->validatedRecord($request);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('medical-records', self::ATTACHMENT_DISK);
        }

        $medicalRecord = $patient->medicalRecords()->create($data);

        $this->logActivity(
            'medical_record_created',
            "Menambahkan riwayat untuk {$patient->nama} ({$patient->no_rekam_medis})",
            [
                'patient_id' => $patient->id,
                'patient_name' => $patient->nama,
                'record_id' => $medicalRecord->id,
                'diagnosa' => $medicalRecord->diagnosa,
                'dokter' => $medicalRecord->dokter,
            ],
            MedicalRecord::class,
            $medicalRecord->id
        );

        return redirect()
            ->route('patients.show', $patient)
            ->with('status', 'Riwayat medis berhasil ditambahkan.');
    }

    /**
     * Form edit riwayat.
     */
    public function editRecord(Patient $patient, MedicalRecord $medicalRecord): View
    {
        $this->ensureRecordBelongsToPatient($medicalRecord, $patient);

        return view('patients.records.edit', [
            'patient' => $patient,
            'medicalRecord' => $medicalRecord,
        ]);
    }

    /**
     * Update riwayat penyakit.
     */
    public function updateRecord(
        Request $request,
        Patient $patient,
        MedicalRecord $medicalRecord
    ): RedirectResponse {
        $this->ensureRecordBelongsToPatient($medicalRecord, $patient);

        $data = $this->validatedRecord($request);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('medical-records', self::ATTACHMENT_DISK);
            $this->deleteAttachmentFile($medicalRecord->attachment_path);
        }

        $medicalRecord->update($data);

        $this->logActivity(
            'medical_record_updated',
            "Memperbarui riwayat untuk {$patient->nama} ({$patient->no_rekam_medis})",
            [
                'patient_id' => $patient->id,
                'patient_name' => $patient->nama,
                'record_id' => $medicalRecord->id,
                'diagnosa' => $medicalRecord->diagnosa,
                'dokter' => $medicalRecord->dokter,
            ],
            MedicalRecord::class,
            $medicalRecord->id
        );

        return redirect()
            ->route('patients.show', $patient)
            ->with('status', 'Riwayat medis berhasil diperbarui.');
    }

    /**
     * Hapus riwayat medis (dokter/admin).
     */
    public function destroyRecord(Patient $patient, MedicalRecord $medicalRecord): RedirectResponse
    {
        $this->ensureRecordBelongsToPatient($medicalRecord, $patient);

        $metadata = [
            'patient_id' => $patient->id,
            'patient_name' => $patient->nama,
            'record_id' => $medicalRecord->id,
            'diagnosa' => $medicalRecord->diagnosa,
            'dokter' => $medicalRecord->dokter,
        ];

        $this->deleteAttachmentFile($medicalRecord->attachment_path);
        $medicalRecord->delete();

        $this->logActivity(
            'medical_record_deleted',
            "Menghapus riwayat untuk {$patient->nama} ({$patient->no_rekam_medis})",
            $metadata,
            MedicalRecord::class,
            $metadata['record_id']
        );

        return redirect()
            ->route('patients.show', $patient)
            ->with('status', 'Riwayat medis berhasil dihapus.');
    }

    /**
     * Unduh lampiran riwayat medis dan catat history.
     */
    public function downloadRecord(Request $request, Patient $patient, MedicalRecord $medicalRecord)
    {
        $this->ensureRecordBelongsToPatient($medicalRecord, $patient);

        $disk = $this->attachmentDisk();

        abort_unless(
            $medicalRecord->attachment_path && $disk->exists($medicalRecord->attachment_path),
            404
        );

        $filename = basename($medicalRecord->attachment_path);

        $inline = $request->boolean('inline', false);
        $mime = $disk->mimeType($medicalRecord->attachment_path) ?? 'application/pdf';

        // Hindari logging berkali kali
        $userId = Auth::id() ?? 0;
        $dedupeKey = "record_download:{$userId}:{$medicalRecord->id}";
        $shouldLog = !Cache::has($dedupeKey);
        Cache::put($dedupeKey, true, now()->addSeconds(10));

        if ($inline) {
            // log kalau mau
            if ($shouldLog) {
                $this->logActivity(
                    'medical_record_previewed',
                    "Preview lampiran riwayat {$patient->nama} ({$patient->no_rekam_medis})",
                    [
                        'patient_id' => $patient->id,
                        'patient_name' => $patient->nama,
                        'record_id' => $medicalRecord->id,
                        'diagnosa' => $medicalRecord->diagnosa,
                        'dokter' => $medicalRecord->dokter,
                    ],
                    MedicalRecord::class,
                    $medicalRecord->id
                );
            }

            // INI YANG BIKIN BISA PREVIEW
            return $disk->response($medicalRecord->attachment_path, $filename, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        }

        // ini baru untuk tombol download beneran
        if ($shouldLog) {
            $this->logActivity(
                'medical_record_downloaded',
                "Mengunduh lampiran riwayat {$patient->nama} ({$patient->no_rekam_medis})",
                [
                    'patient_id' => $patient->id,
                    'patient_name' => $patient->nama,
                    'record_id' => $medicalRecord->id,
                    'diagnosa' => $medicalRecord->diagnosa,
                    'dokter' => $medicalRecord->dokter,
                ],
                MedicalRecord::class,
                $medicalRecord->id
            );
        }

        return $disk->download($medicalRecord->attachment_path, $filename, [
            'Content-Type' => $mime,
        ]);
    }


    /**
     * Validasi data riwayat.
     */
    private function validatedRecord(Request $request): array
    {
        $validated = $request->validate([
            'tanggal_kunjungan' => ['required', 'date'],
            'keluhan' => ['nullable', 'string', 'max:1000'],
            'diagnosa' => ['required', 'string', 'max:255'],
            'dokter' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        unset($validated['attachment']);

        return $validated;
    }

    /**
     * Pastikan riwayat milik pasien terkait.
     */
    private function ensureRecordBelongsToPatient(MedicalRecord $medicalRecord, Patient $patient): void
    {
        abort_unless($medicalRecord->patient_id === $patient->id, 404);
    }

    /**
     * Storage khusus lampiran rekam medis.
     */
    private function attachmentDisk(): Filesystem
    {
        return Storage::disk(self::ATTACHMENT_DISK);
    }

    /**
     * Hapus lampiran dari storage private dan cleanup file legacy di public disk.
     */
    private function deleteAttachmentFile(?string $attachmentPath): void
    {
        if (!$attachmentPath) {
            return;
        }

        foreach ([self::ATTACHMENT_DISK, 'public'] as $diskName) {
            $disk = Storage::disk($diskName);

            if ($disk->exists($attachmentPath)) {
                $disk->delete($attachmentPath);
            }
        }
    }

    private function logActivity(
        string $action,
        string $description,
        array $metadata = [],
        ?string $subjectType = null,
        ?int $subjectId = null
    ): void {
       $user = Auth::user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_role' => $user?->role?->label(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'metadata' => empty($metadata) ? null : $metadata,
        ]);
    }
}
