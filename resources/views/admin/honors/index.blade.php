<x-admin-layout :tenant="$tenant" title="Rekap Honor Tutor">
    <x-slot:breadcrumbSub>Rekap Honor Tutor</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Rekap Honor Tutor</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Pemeriksaan data operasional dan rekapitulasi sesi mengajar yang menjadi dasar perhitungan honor tutor di cabang Anda.
                </p>
            </div>
        </div>

        <!-- Summary Metric Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-cressco.project-card
                title="Tutor Aktif Mengajar"
                icon="people"
                iconColor="text-gray-500"
                value="{{ $totalActiveTutors }}"
                trend="Tutor"
                trendType="neutral"
                subtitle="Tutor dengan rekap mengajar"
            />

            <x-cressco.project-card
                title="Sesi Selesai"
                icon="calendar"
                iconColor="text-blue-500"
                value="{{ $totalCompletedSessions }}"
                trend="Sesi"
                trendType="neutral"
                subtitle="Total sesi mengajar terselesaikan"
            />

            <x-cressco.project-card
                title="Estimasi Total Honor"
                icon="billing"
                iconColor="text-emerald-500"
                value="Rp {{ number_format($totalEstimatedHonor, 0, ',', '.') }}"
                trend="Terhitung"
                trendType="positive"
                subtitle="Honor terhitung berbasis sesi aktual"
            />
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.honors.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <!-- Search Input -->
                <div class="lg:col-span-5">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <x-cressco.icon-helper name="search" class="w-4 h-4" />
                        </div>
                        <input
                            type="text"
                            name="search"
                            placeholder="Cari nama tutor atau email..."
                            value="{{ $search ?? '' }}"
                            class="w-full h-10 pl-9 pr-3.5 rounded-xl border border-gray-200 bg-white text-xs text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition shadow-2xs"
                        />
                    </div>
                </div>

                <!-- Tutor Filter -->
                <div class="lg:col-span-4">
                    <select name="tutor_id" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs text-gray-700 focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition shadow-2xs">
                        <option value="all">Semua Tutor</option>
                        @foreach ($tutors as $t)
                            <option value="{{ $t->id }}" {{ ($tutorId ?? '') === $t->id ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Period Filter & Action Buttons -->
                <div class="lg:col-span-3 flex items-center gap-2">
                    <select name="period" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs text-gray-700 focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition shadow-2xs">
                        <option value="">Semua Periode</option>
                        @foreach ($periods as $p)
                            <option value="{{ $p }}" {{ ($period ?? '') === $p ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($p . '-01')->translatedFormat('F Y') }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="h-10 px-4 bg-terracotta-600 hover:bg-terracotta-700 text-white rounded-xl text-xs font-semibold shrink-0 transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                        <x-cressco.icon-helper name="filter" class="w-3.5 h-3.5" />
                        <span>Filter</span>
                    </button>

                    @if ($search || ($tutorId && $tutorId !== 'all') || $period)
                        <a href="{{ route('admin.honors.index') }}" class="h-10 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold flex items-center justify-center shrink-0 transition" title="Reset Filter">
                            <x-cressco.icon-helper name="refresh" class="w-3.5 h-3.5" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Honor Recap -->
        <div class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200/80 text-gray-500 font-bold text-[11px] uppercase tracking-wider">
                            <th class="py-3.5 px-4 sm:px-6">Tutor</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Periode</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Jumlah Sesi Selesai</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Tarif Honor</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Total Honor</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($calculations as $calc)
                            @php
                                $rate = $calc->honorScheme?->rate ?? ($calc->total_sessions > 0 ? (float) ($calc->base_amount / $calc->total_sessions) : 0);
                            @endphp
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <!-- Tutor Name & Avatar -->
                                <td class="py-3.5 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gray-50 text-gray-700 flex items-center justify-center font-bold text-xs shrink-0 border border-gray-200/60">
                                            {{ strtoupper(substr($calc->tutor?->name ?? 'T', 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.honors.show', $calc) }}" class="font-bold text-gray-900 hover:text-terracotta-600 transition block leading-snug">
                                                {{ $calc->tutor?->name ?? '-' }}
                                            </a>
                                            <span class="text-[11px] text-gray-400 block mt-0.5">{{ $calc->tutor?->email ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Periode -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-gray-700 font-medium">
                                    {{ $calc->period_start ? $calc->period_start->translatedFormat('d M Y') : '' }} - {{ $calc->period_end ? $calc->period_end->translatedFormat('d M Y') : '' }}
                                </td>

                                <!-- Jumlah Sesi Selesai -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                                        {{ $calc->total_sessions ?? 0 }} Sesi
                                    </span>
                                </td>

                                <!-- Tarif Honor -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap text-gray-600 font-medium">
                                    @if ($rate > 0)
                                        Rp {{ number_format($rate, 0, ',', '.') }} <span class="text-[10px] text-gray-400">/ sesi</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <!-- Total Honor -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap font-bold text-gray-900 text-sm">
                                    Rp {{ number_format($calc->final_amount, 0, ',', '.') }}
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center">
                                        <a href="{{ route('admin.honors.show', $calc) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200/90 bg-white hover:bg-gray-50 text-gray-700 hover:text-gray-900 text-xs font-semibold transition shadow-2xs"
                                           title="Lihat Rincian Sesi Mengajar">
                                            <x-cressco.icon-helper name="document" class="w-3.5 h-3.5 text-gray-400" />
                                            <span>Detail Sesi</span>
                                            <x-cressco.icon-helper name="chevron-right" class="w-3 h-3 text-gray-400" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                                            <x-cressco.icon-helper name="billing" class="w-6 h-6 text-gray-400" />
                                        </div>
                                        <p class="font-semibold text-gray-700 text-sm">Belum ada data rekap honor tutor pada filter ini.</p>
                                        <p class="text-xs text-gray-400 mt-1">Data rekap honor terhitung otomatis berdasarkan sesi mengajar yang telah diselesaikan.</p>
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
