<x-admin-layout :tenant="$tenant" :title="'Rincian Honor - ' . ($calculation->tutor?->name ?? 'Tutor')">
    <x-slot:breadcrumbSub>
        <a href="{{ route('admin.honors.index') }}" class="hover:text-terracotta-600 transition">Honor Tutor</a>
        <span class="mx-1 text-gray-300">/</span>
        <span class="text-gray-900 font-bold">Rincian Honor {{ $calculation->tutor?->name }}</span>
    </x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto">
        
        <!-- Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-base shadow-2xs border border-emerald-200/60">
                    <x-cressco.icon-helper name="credit-card" class="w-6 h-6" />
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Rincian Honor: {{ $calculation->tutor?->name }}</h1>
                        <x-cressco.badge :variant="$calculation->status === 'paid' ? 'success' : ($calculation->status === 'final' ? 'blue' : 'gray')" dot>
                            @if ($calculation->status === 'paid')
                                Lunas (Sudah Dibayar)
                            @elseif ($calculation->status === 'final')
                                Final (Siap Bayar)
                            @else
                                Draft
                            @endif
                        </x-cressco.badge>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Periode: <span class="font-semibold text-gray-800">{{ $calculation->period_start ? $calculation->period_start->translatedFormat('d F Y') : '' }} s/d {{ $calculation->period_end ? $calculation->period_end->translatedFormat('d F Y') : '' }}</span>
                        • Cabang: <span class="font-semibold text-gray-800">{{ $calculation->branch?->name ?? 'Semua Cabang Anda' }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.honors.index') }}" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                    ← Kembali
                </a>
            </div>
        </div>

        <!-- 2 Columns: Calculation Breakdown & Sesi Mengajar Aktual -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Column 1: Financial & Scheme Summary -->
            <div class="space-y-6">
                
                <!-- Nominal Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">Ringkasan Pembayaran</h2>

                    <div class="p-4 rounded-xl bg-gray-50/80 border border-gray-100 space-y-3 text-xs">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Honor Pokok (Base)</span>
                            <span class="font-bold text-gray-900">Rp {{ number_format($calculation->base_amount, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex items-center justify-between text-gray-600">
                            <span>Penyesuaian (Adjustment)</span>
                            <span class="{{ $calculation->adjustment_amount > 0 ? 'text-emerald-600 font-bold' : ($calculation->adjustment_amount < 0 ? 'text-red-600 font-bold' : 'text-gray-400') }}">
                                {{ $calculation->adjustment_amount > 0 ? '+' : '' }}Rp {{ number_format($calculation->adjustment_amount, 0, ',', '.') }}
                            </span>
                        </div>

                        @if ($calculation->adjustment_reason)
                            <div class="text-[11px] text-gray-500 bg-white p-2 rounded-lg border border-gray-200/60">
                                <strong>Catatan Penyesuaian:</strong> {{ $calculation->adjustment_reason }}
                            </div>
                        @endif

                        <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
                            <span class="font-bold text-gray-900 text-sm">Total Diterima</span>
                            <span class="font-bold text-emerald-600 text-base">Rp {{ number_format($calculation->final_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="space-y-2.5 text-xs text-gray-600 pt-2">
                        <div class="flex items-center justify-between">
                            <span>Skema Kompensasi</span>
                            <strong class="text-gray-900">{{ $calculation->honorScheme?->name ?? '-' }}</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Metode Penggajian</span>
                            <strong class="text-gray-900 uppercase text-[11px]">{{ str_replace('_', ' ', $calculation->method) }}</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Dihitung Oleh</span>
                            <span class="text-gray-900 font-medium">{{ $calculation->calculatedBy?->name ?? 'Sistem' }}</span>
                        </div>
                        @if ($calculation->finalizedBy)
                            <div class="flex items-center justify-between">
                                <span>Difinalisasi Oleh</span>
                                <span class="text-gray-900 font-medium">{{ $calculation->finalizedBy->name }}</span>
                            </div>
                        @endif
                        @if ($calculation->paid_at)
                            <div class="flex items-center justify-between text-emerald-700">
                                <span>Waktu Pembayaran</span>
                                <span class="font-bold">{{ $calculation->paid_at->translatedFormat('d M Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tutor Profile Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-3 text-xs">
                    <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">Profil Tutor</h2>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr($calculation->tutor?->name ?? 'T', 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">{{ $calculation->tutor?->name }}</h3>
                            <p class="text-gray-500 text-[11px]">{{ $calculation->tutor?->email }}</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Column 2: Actual Teaching Sessions (Basis Perhitungan Honor) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Sesi Mengajar Aktual</h2>
                            <p class="text-xs text-gray-500">Daftar sesi terselesaikan yang menjadi basis perhitungan honor periode ini.</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                            {{ $sessions->count() }} Sesi Mengajar
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50/80 border-b border-gray-200/80 text-gray-500 font-semibold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Tanggal & Sesi</th>
                                    <th class="py-2.5 px-3">Kelas & Cabang</th>
                                    <th class="py-2.5 px-3">Materi yang Diajarkan</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($sessions as $session)
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="py-3 px-3">
                                            <span class="font-bold text-gray-900 block">{{ $session->session_date ? $session->session_date->format('d/m/Y') : '-' }}</span>
                                            <span class="text-[11px] text-gray-500">{{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }}</span>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="font-semibold text-gray-900 block">{{ $session->class->name ?? '-' }}</span>
                                            <span class="text-[11px] text-gray-400">{{ $session->class->branch->name ?? '-' }}</span>
                                        </td>
                                        <td class="py-3 px-3 text-gray-700 max-w-xs">
                                            <span class="font-medium text-gray-900 block">{{ $session->material ?? '-' }}</span>
                                            @if ($session->notes)
                                                <span class="text-[11px] text-gray-500 italic block mt-0.5">{{ Str::limit($session->notes, 60) }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Selesai
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-gray-400">
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

        </div>

    </div>
</x-admin-layout>
