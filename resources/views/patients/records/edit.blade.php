@extends('layouts.admin')

@section('title', 'Edit Riwayat - ' . $patient->nama)

@section('content')
    <div class="max-w-2xl mx-auto rounded-3xl border border-emerald-100 bg-white/95 p-8 shadow-2xl shadow-emerald-100">
        <a href="{{ route('patients.show', $patient) }}" class="inline-flex items-center gap-2 text-emerald-600 text-sm font-semibold">
            <span>&larr;</span> Kembali ke detail pasien
        </a>
        <h1 class="mt-4 text-3xl font-semibold text-emerald-800">Edit Riwayat Medis</h1>
        <p class="text-sm text-slate-500 mt-1">
            Pasien: <span class="font-semibold text-slate-700">{{ $patient->nama }}</span>
        </p>

        <form method="POST" action="{{ route('patients.records.update', [$patient, $medicalRecord]) }}" class="mt-6 space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <label class="block text-sm font-semibold text-emerald-600">
                Tanggal Kunjungan
                <input type="date" name="tanggal_kunjungan" value="{{ old('tanggal_kunjungan', $medicalRecord->tanggal_kunjungan->format('Y-m-d')) }}"
                       class="mt-1 w-full rounded-2xl border border-emerald-200 bg-white px-4 py-2 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                @error('tanggal_kunjungan')
                    <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
                @enderror
            </label>

            <label class="block text-sm font-semibold text-emerald-600">
                Keluhan Pasien
                <textarea name="keluhan" rows="4"
                          placeholder="Contoh: demam, batuk, atau nyeri pada bagian tertentu"
                          class="mt-1 w-full rounded-2xl border border-emerald-200 bg-white px-4 py-2 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">{{ old('keluhan', $medicalRecord->keluhan) }}</textarea>
                @error('keluhan')
                    <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
                @enderror
            </label>

            <label class="block text-sm font-semibold text-emerald-600">
                Diagnosa
                <input type="text" name="diagnosa" value="{{ old('diagnosa', $medicalRecord->diagnosa) }}"
                       class="mt-1 w-full rounded-2xl border border-emerald-200 bg-white px-4 py-2 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                @error('diagnosa')
                    <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
                @enderror
            </label>

            <label class="block text-sm font-semibold text-emerald-600">
                Dokter
                <input type="text" name="dokter" value="{{ old('dokter', $medicalRecord->dokter) }}"
                       class="mt-1 w-full rounded-2xl border border-emerald-200 bg-white px-4 py-2 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                @error('dokter')
                    <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
                @enderror
            </label>

            <label class="block text-sm font-semibold text-emerald-600">
                Lampiran (opsional)
                <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png"
                       class="mt-1 w-full rounded-2xl border border-emerald-200 bg-white px-4 py-2 file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                <p class="mt-1 text-xs text-slate-500">Unggah file baru untuk mengganti lampiran saat ini (maks. 5 MB).</p>
                @error('attachment')
                    <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
                @enderror

                @if ($medicalRecord->attachment_path)
                    <a href="{{ route('patients.records.download', [$patient, $medicalRecord]) }}"
                       class="mt-2 inline-flex items-center text-sm text-emerald-600 underline"
                       target="_blank" rel="noopener">
                        Lihat lampiran saat ini
                    </a>
                @endif
            </label>

            <label class="block text-sm font-semibold text-emerald-600">
                Catatan
                <textarea name="catatan" rows="4"
                          class="mt-1 w-full rounded-2xl border border-emerald-200 bg-white px-4 py-2 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">{{ old('catatan', $medicalRecord->catatan) }}</textarea>
                @error('catatan')
                    <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
                @enderror
            </label>

            <button type="submit"
                    class="w-full rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-semibold uppercase tracking-wider text-white shadow-lg shadow-emerald-200 hover:bg-emerald-600 transition">
                Perbarui Riwayat
            </button>
        </form>
    </div>
@endsection
