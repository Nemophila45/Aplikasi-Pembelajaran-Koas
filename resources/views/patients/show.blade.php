@extends('layouts.admin')

@section('title', 'Detail Pasien - ' . $patient->nama)

@section('content')
    @php
        $canCreateRecord = auth()->check() && auth()->user()->hasAnyRole('admin', 'doctor', 'koas');
        $canEditRecord = auth()->check() && auth()->user()->hasAnyRole('admin', 'doctor');
        $canDeleteRecord = $canEditRecord;
        $canPreviewAttachment = auth()->check() && auth()->user()->hasAnyRole('admin', 'doctor', 'koas');
    @endphp
    <div class="space-y-8" id="riwayat">
        <div class="rounded-3xl border border-emerald-100 bg-white/90 p-6 shadow-xl shadow-emerald-100">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-500">Detail Pasien</p>
                    <h1 class="mt-2 text-4xl font-semibold text-emerald-800">{{ $patient->nama }}</h1>
                    <p class="mt-2 text-sm text-slate-500">No. Rekam Medis:
                        <span class="font-semibold text-slate-700">{{ $patient->no_rekam_medis }}</span>
                    </p>
                </div>
                @if ($canCreateRecord)
                    <a href="{{ route('patients.records.create', $patient) }}"
                       class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-200 hover:bg-emerald-500/90 transition">
                        + Tambah Riwayat
                    </a>
                @endif
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-3">
                    <p class="text-xs uppercase text-emerald-600">NIK</p>
                    <p class="text-lg font-semibold text-slate-800 mt-1">{{ $patient->nik }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-3">
                    <p class="text-xs uppercase text-emerald-600">Tanggal Lahir</p>
                    <p class="text-lg font-semibold text-slate-800 mt-1">{{ optional($patient->tanggal_lahir)?->format('d/m/Y') ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-3">
                    <p class="text-xs uppercase text-emerald-600">Umur</p>
                    <p class="text-lg font-semibold text-slate-800 mt-1">{{ $patient->umur ? $patient->umur . ' tahun' : '-' }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-3">
                    <p class="text-xs uppercase text-emerald-600">Jenis Kelamin</p>
                    <p class="mt-1 inline-flex items-center rounded-full bg-white px-3 py-1 text-sm font-semibold text-emerald-700">
                        {{ $patient->jenis_kelamin ?? '-' }}
                    </p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-3">
                    <p class="text-xs uppercase text-emerald-600">No. HP</p>
                    <p class="text-lg font-semibold text-slate-800 mt-1">{{ $patient->no_hp ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-3">
                    <p class="text-xs uppercase text-emerald-600">Provinsi</p>
                    <p class="text-lg font-semibold text-slate-800 mt-1">{{ $patient->provinsi ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-3">
                    <p class="text-xs uppercase text-emerald-600">Kota/Kabupaten</p>
                    <p class="text-lg font-semibold text-slate-800 mt-1">{{ $patient->kabupaten_kota ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-3">
                    <p class="text-xs uppercase text-emerald-600">Kecamatan</p>
                    <p class="text-lg font-semibold text-slate-800 mt-1">{{ $patient->kecamatan ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-3">
                    <p class="text-xs uppercase text-emerald-600">Kel/Desa</p>
                    <p class="text-lg font-semibold text-slate-800 mt-1">{{ $patient->kelurahan_desa ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 px-4 py-3 md:col-span-2 lg:col-span-3">
                    <p class="text-xs uppercase text-emerald-600">Alamat</p>
                    <p class="mt-1 text-slate-700">{{ $patient->alamat ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-emerald-100 bg-white/95 shadow-2xl shadow-emerald-100">
            <div class="p-6 border-b border-emerald-100 space-y-4">
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-500">Riwayat Penyakit</p>
                    <h2 class="text-2xl font-semibold text-emerald-800">Catatan Kunjungan</h2>
                </div>

                @if (session('status'))
                    <div class="rounded-xl bg-emerald-100/80 px-4 py-2 text-sm font-medium text-emerald-900">
                        {{ session('status') }}
                    </div>
                @endif

            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100">
                    <thead class="bg-emerald-50/70 text-xs font-semibold uppercase tracking-widest text-emerald-600">
                        <tr>
                            <th class="px-5 py-3 text-left">Tanggal</th>
                            <th class="px-5 py-3 text-left">Keluhan</th>
                            <th class="px-5 py-3 text-left">Diagnosa</th>
                            <th class="px-5 py-3 text-left">Dokter</th>
                            <th class="px-5 py-3 text-left">Catatan</th>
                            <th class="px-5 py-3 text-left">Lampiran</th>
                            <th class="px-5 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-50 text-sm">
                        @forelse ($medicalRecords as $record)
                            <tr class="hover:bg-emerald-50/60 transition">
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ $record->tanggal_kunjungan->format('d/m/Y') }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $record->keluhan ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $record->diagnosa }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $record->dokter }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $record->catatan ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    @if ($record->attachment_path && $canPreviewAttachment)
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('patients.records.download', [$patient, $record, 'inline' => 1]) }}"
                                               target="_blank"
                                               rel="noopener"
                                               class="inline-flex items-center justify-center rounded-full border border-emerald-200 p-2 text-emerald-600 hover:bg-emerald-500 hover:text-white transition"
                                               title="Preview">
                                                <!-- eye icon -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('patients.records.download', [$patient, $record]) }}"
                                               class="inline-flex items-center justify-center rounded-full border border-emerald-200 p-2 text-emerald-600 hover:bg-emerald-500 hover:text-white transition"
                                               title="Download">
                                                <!-- download icon -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                                </svg>
                                            </a>
                                        </div>
                                    @elseif ($record->attachment_path)
                                        <span class="text-xs text-slate-400">Terlampir</span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        @if ($canEditRecord)
                                            <a href="{{ route('patients.records.edit', [$patient, $record]) }}"
                                               class="inline-flex items-center rounded-full border border-emerald-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-600 hover:bg-emerald-500 hover:text-white transition">
                                                Edit
                                            </a>
                                        @endif
                                        @if ($canDeleteRecord)
                                            <form method="POST" action="{{ route('patients.records.destroy', [$patient, $record]) }}"
                                                  onsubmit="return confirm('Hapus riwayat medis ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center rounded-full border border-rose-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-rose-600 hover:bg-rose-100 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-slate-500">
                                    Belum ada riwayat medis.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-emerald-50/70 border-t border-emerald-100 rounded-b-3xl">
                {{ $medicalRecords->links('vendor.pagination.patients') }}
            </div>
        </div>
    </div>
@endsection
