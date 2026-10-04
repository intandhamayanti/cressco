<x-admin-layout :tenant="$tenant" title="Honor Tutor">
    <x-slot:breadcrumbSub>Honor Tutor</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto" x-data="{ activeTab: 'calculations' }">

        <!-- Page Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Honor Tutor & Kompensasi</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Pengawasan rekapitulasi honor pengajar berbasis sesi aktual pada cabang yang menjadi akses Anda.
                </p>
            </div>
        </div>

        <!-- Summary Metric Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Honor Terbayar"
                icon="check"
                iconColor="text-emerald-500"
                value="Rp {{ number_format($totalPaidAmount, 0, ',', '.') }}"
                trend="Lunas"
                trendType="positive"
                subtitle="Honor tutor yang telah dicairkan"
            />

            <x-cressco.project-card
                title="Menunggu Pembayaran"
                icon="credit-card"
                iconColor="text-amber-500"
                value="Rp {{ number_format($pendingFinalAmount, 0, ',', '.') }}"
                trend="Pending"
                trendType="warning"
                subtitle="Honor siap transfer / cair"
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
                title="Skema Honor Aktif"
                icon="settings"
                iconColor="text-terracotta-500"
                value="{{ $activeSchemesCount }}"
                trend="Skema"
                trendType="terracotta"
                subtitle="Konfigurasi tarif aktif bimbel"
            />
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-gray-200/80 pb-px">
            <button type="button"
                    @click="activeTab = 'calculations'"
                    :class="activeTab === 'calculations' ? 'border-terracotta-600 text-terracotta-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'"
                    class="px-4 py-2.5 text-xs border-b-2 transition flex items-center gap-2 cursor-pointer">
                <x-cressco.icon-helper name="billing" class="w-4 h-4" />
                <span>Rekapitulasi & Pembayaran Honor ({{ $calculations->count() }})</span>
            </button>
            <button type="button"
                    @click="activeTab = 'schemes'"
                    :class="activeTab === 'schemes' ? 'border-terracotta-600 text-terracotta-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'"
                    class="px-4 py-2.5 text-xs border-b-2 transition flex items-center gap-2 cursor-pointer">
                <x-cressco.icon-helper name="settings" class="w-4 h-4" />
                <span>Daftar Skema Kompensasi ({{ $schemes->count() }})</span>
            </button>
        </div>

        <!-- TAB 1: REKAPITULASI HONOR -->
        <div x-show="activeTab === 'calculations'" class="space-y-4">
            
            <!-- Filter Bar -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
                <form method="GET" action="{{ route('admin.honors.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <input
                            type="text"
                            name="search"
                            placeholder="Cari nama tutor atau email..."
                            value="{{ $search ?? '' }}"
                            class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition"
                        />
                    </div>

                    <div>
                        <select name="branch_id" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition">
                            <option value="all">Semua Cabang Anda</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}" {{ ($branchId ?? '') === $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="status" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition">
                            <option value="all">Semua Status</option>
                            <option value="draft" {{ ($status ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="final" {{ ($status ?? '') === 'final' ? 'selected' : '' }}>Final (Siap Bayar)</option>
                            <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>Lunas (Sudah Dibayar)</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="w-full h-10 px-4 bg-terracotta-600 hover:bg-terracotta-700 text-white rounded-xl text-xs font-semibold transition">
                            Filter
                        </button>
                        @if ($search || ($branchId && $branchId !== 'all') || ($status && $status !== 'all'))
                            <a href="{{ route('admin.honors.index') }}" class="h-10 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold flex items-center justify-center shrink-0 transition" title="Reset Filter">
                                <x-cressco.icon-helper name="refresh-cw" class="w-3.5 h-3.5" />
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table Honor Calculations -->
            <div class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-200/80 text-gray-500 font-semibold uppercase tracking-wider">
                                <th class="py-3 px-4">Tutor</th>
                                <th class="py-3 px-4">Cabang</th>
                                <th class="py-3 px-4">Periode</th>
                                <th class="py-3 px-4">Skema & Metode</th>
                                <th class="py-3 px-4 text-right">Total Honor</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($calculations as $calc)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-gray-900">{{ $calc->tutor->name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $calc->tutor->email ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-700">
                                            {{ $calc->branch?->name ?? 'Semua Cabang' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-700">
                                        {{ $calc->period_start ? $calc->period_start->format('d/m/Y') : '' }} - {{ $calc->period_end ? $calc->period_end->format('d/m/Y') : '' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-900">{{ $calc->honorScheme->name ?? '-' }}</div>
                                        <span class="text-[10px] uppercase tracking-wider font-semibold text-gray-500">{{ str_replace('_', ' ', $calc->method) }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-gray-900">
                                        Rp {{ number_format($calc->final_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($calc->status === 'paid')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                Lunas
                                            </span>
                                        @elseif ($calc->status === 'final')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800">
                                                Final (Siap Bayar)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700">
                                                Draft
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('admin.honors.show', $calc) }}" class="p-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-600 transition" title="Lihat Rincian Sesi & Honor">
                                                <x-cressco.icon-helper name="eye" class="w-4 h-4" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <x-cressco.icon-helper name="billing" class="w-10 h-10 text-gray-300 mb-2" />
                                            <p class="font-medium text-gray-500">Belum ada data perhitungan honor di cabang Anda.</p>
                                            <p class="text-[11px] text-gray-400 mt-1">Perhitungan honor disusun berbasis sesi pengajaran aktual oleh Owner.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- TAB 2: DAFTAR SKEMA HONOR (REFERENCE VIEW) -->
        <div x-show="activeTab === 'schemes'" class="space-y-4" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($schemes as $scheme)
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-3 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-bold text-gray-900 text-sm">{{ $scheme->name }}</h3>
                                @if ($scheme->id === $defaultSchemeId)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-terracotta-100 text-terracotta-800">
                                        Default
                                    </span>
                                @endif
                            </div>

                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs space-y-1">
                                <div class="flex justify-between text-gray-600">
                                    <span>Metode:</span>
                                    <span class="font-bold text-gray-900 uppercase text-[11px]">{{ str_replace('_', ' ', $scheme->method) }}</span>
                                </div>
                                @if ($scheme->rate)
                                    <div class="flex justify-between text-gray-600">
                                        <span>Tarif per Sesi:</span>
                                        <span class="font-bold text-emerald-700">Rp {{ number_format($scheme->rate, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if ($scheme->fixed_amount)
                                    <div class="flex justify-between text-gray-600">
                                        <span>Nominal Tetap:</span>
                                        <span class="font-bold text-emerald-700">Rp {{ number_format($scheme->fixed_amount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if ($scheme->percentage)
                                    <div class="flex justify-between text-gray-600">
                                        <span>Persentase Bagi Hasil:</span>
                                        <span class="font-bold text-blue-700">{{ $scheme->percentage }}%</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100 text-[11px] text-gray-400 flex items-center justify-between">
                            <span>Status: <strong class="{{ $scheme->status === 'active' ? 'text-emerald-600' : 'text-gray-400' }}">{{ ucfirst($scheme->status) }}</strong></span>
                            <span>Mulai: {{ $scheme->effective_from ? $scheme->effective_from->format('d/m/Y') : '-' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-gray-400 bg-white rounded-2xl border border-gray-200/80">
                        <p class="font-medium text-gray-500">Belum ada skema kompensasi yang dikonfigurasi oleh Owner.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-admin-layout>
