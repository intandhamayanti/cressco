<x-admin-layout :tenant="$tenant" :title="'Rincian Sesi & Honor - ' . ($calculation->tutor?->name ?? 'Tutor')">
    <x-slot:breadcrumbSub>
        <a href="{{ route('admin.honors.index') }}" class="hover:text-terracotta-600 transition">Rekap Honor Tutor</a>
        <span class="mx-1 text-gray-300">/</span>
        <span class="text-gray-900 font-bold">Rincian Sesi {{ $calculation->tutor?->name }}</span>
    </x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-gray-50 text-gray-700 flex items-center justify-center font-bold text-sm shadow-2xs border border-gray-200/60">
                    {{ strtoupper(substr($calculation->tutor?->name ?? 'T', 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Rincian Sesi: {{ $calculation->tutor?->name }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Periode: <span class="font-semibold text-gray-800">{{ $calculation->period_start ? $calculation->period_start->translatedFormat('d F Y') : '' }} s/d {{ $calculation->period_end ? $calculation->period_end->translatedFormat('d F Y') : '' }}</span>
                        &bull; Cabang: <span class="font-semibold text-gray-800">{{ $calculation->branch?->name ?? 'Semua Cabang Akses' }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.honors.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition shadow-2xs">
                    <x-cressco.icon-helper name="arrow-left" class="w-3.5 h-3.5" />
                    <span>Kembali ke Rekap</span>
                </a>
            </div>
        </div>

        <!-- Operational Summary Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-1">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Sesi Mengajar Selesai</span>
                <div class="text-2xl font-bold text-gray-900 mt-1">
                    {{ $sessions->count() }} <span class="text-xs font-normal text-gray-500">Sesi</span>
                </div>
                <p class="text-[11px] text-gray-400">Sesi aktual yang diajar tutor periode ini</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-1">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tarif Honor</span>
                <div class="text-2xl font-bold text-gray-900 mt-1">
                    @if ($ratePerSession > 0)
                        Rp {{ number_format($ratePerSession, 0, ',', '.') }} <span class="text-xs font-normal text-gray-500">/ sesi</span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </div>
                <p class="text-[11px] text-gray-400">Tarif honor mengajar bimbel</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-1">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Honor Terhitung</span>
                <div class="text-2xl font-extrabold text-emerald-600 mt-1">
                    Rp {{ number_format($calculation->final_amount, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-gray-400">Akumulasi honor berbasis sesi aktual</p>
            </div>
        </div>

        <!-- Actual Teaching Sessions Table -->
        <div class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-xs space-y-4 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Daftar Teaching Session Aktual</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Sesi mengajar yang diselesaikan oleh tutor ini (termasuk sesi sebagai tutor pengganti).</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                    {{ $sessions->count() }} Sesi Selesai
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200/80 text-gray-500 font-bold text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-3">Tanggal & Waktu</th>
                            <th class="py-3 px-3">Kelas & Cabang</th>
                            <th class="py-3 px-3">Tutor Terjadwal</th>
                            <th class="py-3 px-3">Tutor Aktual (Pengajar)</th>
                            <th class="py-3 px-3">Materi Pembelajaran</th>
                            <th class="py-3 px-3 text-right">Estimasi Honor</th>
                            <th class="py-3 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($sessions as $session)
                            @php
                                $isReplacement = $session->actual_tutor_id && $session->actual_tutor_id !== $session->scheduled_tutor_id;
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition">
                                <!-- Tanggal & Waktu -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="font-bold text-gray-900 block">{{ $session->session_date ? $session->session_date->translatedFormat('d M Y') : '-' }}</span>
                                    <span class="text-[11px] text-gray-500">{{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB</span>
                                </td>

                                <!-- Kelas & Cabang -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900 block">{{ $session->classModel?->name ?? '-' }}</span>
                                    <span class="text-[11px] text-gray-400">{{ $session->branch?->name ?? '-' }}</span>
                                </td>

                                <!-- Tutor Terjadwal -->
                                <td class="py-3 px-3 whitespace-nowrap text-gray-600">
                                    {{ $session->scheduledTutor?->name ?? '-' }}
                                </td>

                                <!-- Tutor Aktual (Pengajar) -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-gray-900">{{ $session->actualTutor?->name ?? ($session->scheduledTutor?->name ?? '-') }}</span>
                                        @if ($isReplacement)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/60">
                                                Tutor Pengganti
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Materi Pembelajaran -->
                                <td class="py-3 px-3 text-gray-700 max-w-xs">
                                    <span class="font-medium text-gray-900 block truncate">{{ $session->material ?: 'Materi reguler' }}</span>
                                    @if ($session->notes)
                                        <span class="text-[11px] text-gray-400 italic block mt-0.5 truncate">{{ $session->notes }}</span>
                                    @endif
                                </td>

                                <!-- Estimasi Honor -->
                                <td class="py-3 px-3 text-right whitespace-nowrap font-semibold text-gray-900">
                                    Rp {{ number_format($ratePerSession, 0, ',', '.') }}
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                        Selesai
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-cressco.icon-helper name="calendar" class="w-8 h-8 text-gray-300 mb-2" />
                                        <p class="font-medium text-gray-500">Tidak ada sesi mengajar terselesaikan pada periode ini di cabang Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>
