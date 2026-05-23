@extends('layouts.admin')

@section('title', 'Detail Riwayat - ' . $patient->nama)

@section('content')
    @php
        $canEditRecord = auth()->check() && auth()->user()->hasAnyRole('admin', 'doctor');
        $canAccessAttachments = auth()->check() && auth()->user()->hasAnyRole('admin', 'doctor', 'koas');
        $attachmentName = $medicalRecord->attachment_path ? basename($medicalRecord->attachment_path) : null;
    @endphp

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="rounded-3xl border border-emerald-100 bg-white/95 p-8 shadow-2xl shadow-emerald-100">
            <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                <div>
                    <a href="{{ route('patients.show', $patient) }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 hover:text-emerald-700">
                        <span>&larr;</span> Kembali ke detail pasien
                    </a>
                    <p class="mt-4 text-sm font-semibold uppercase tracking-[0.25em] text-emerald-500">Detail Riwayat Medis</p>
                    <h1 class="mt-2 text-3xl font-semibold text-emerald-800">{{ $patient->nama }}</h1>
                    <p class="mt-2 text-sm text-slate-500">
                        No. Rekam Medis:
                        <span class="font-semibold text-slate-700">{{ $patient->no_rekam_medis }}</span>
                    </p>
                    <p class="mt-2 text-sm text-slate-500">
                        Tanggal kunjungan:
                        <span class="font-semibold text-slate-700">{{ $medicalRecord->tanggal_kunjungan->format('d/m/Y') }}</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if ($canEditRecord)
                        <a href="{{ route('patients.records.edit', [$patient, $medicalRecord]) }}"
                           class="inline-flex items-center rounded-full border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-500 hover:text-white transition">
                            Edit Riwayat
                        </a>
                    @endif
                    <a href="{{ route('patients.show', $patient) }}"
                       class="inline-flex items-center rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-200 hover:bg-emerald-600 transition">
                        Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-emerald-100 bg-white/95 p-8 shadow-2xl shadow-emerald-100">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-emerald-600">Keluhan</p>
                    <p class="mt-2 whitespace-pre-line text-slate-700">{{ $medicalRecord->keluhan ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-emerald-600">Diagnosa</p>
                    <p class="mt-2 text-slate-700">{{ $medicalRecord->diagnosa }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-emerald-600">Dokter</p>
                    <p class="mt-2 text-slate-700">{{ $medicalRecord->dokter }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-emerald-600">Tanggal Kunjungan</p>
                    <p class="mt-2 text-slate-700">{{ $medicalRecord->tanggal_kunjungan->format('d/m/Y') }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-2xl bg-slate-50 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Catatan</p>
                <div class="mt-2 whitespace-pre-line text-slate-700">
                    {{ $medicalRecord->catatan ?: 'Belum ada catatan.' }}
                </div>
            </div>

            <div class="mt-6 rounded-2xl bg-slate-50 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Lampiran</p>
                @if ($medicalRecord->attachment_path)
                    @if ($canAccessAttachments)
                        <p class="mt-2 text-sm text-slate-600">
                            File:
                            <span class="font-semibold text-slate-700">{{ $attachmentName }}</span>
                        </p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ route('patients.records.download', [$patient, $medicalRecord, 'inline' => 1]) }}"
                               target="_blank"
                               rel="noopener"
                               class="inline-flex items-center rounded-full border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-500 hover:text-white transition">
                                Lihat Lampiran
                            </a>
                            <a href="{{ route('patients.records.download', [$patient, $medicalRecord]) }}"
                               class="inline-flex items-center rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-200 hover:bg-emerald-600 transition">
                                Unduh Lampiran
                            </a>
                        </div>
                    @else
                        <p class="mt-2 text-sm text-slate-500">Lampiran tersedia, akses dibatasi.</p>
                    @endif
                @else
                    <p class="mt-2 text-sm text-slate-500">Tidak ada lampiran pada riwayat ini.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
