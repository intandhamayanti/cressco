<x-owner-layout :tenant="$tenant" :title="'Rincian Honor - ' . ($calculation->tutor?->name ?? 'Tutor')">
    <x-slot:breadcrumbSub>
        <a href="{{ route('owner.honors.index') }}" class="hover:text-terracotta-600 transition">Honor Tutor</a>
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
                        • Cabang: <span class="font-semibold text-gray-800">{{ $calculation->branch?->name ?? 'Semua Cabang (Konsolidasi)' }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('owner.honors.index') }}" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                    ← Kembali
                </a>

                @if ($calculation->status === 'final')
                    <form method="POST" action="{{ route('owner.honors.mark-paid', $calculation) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <x-cressco.button type="submit" variant="primary" size="md">
                            <x-cressco.icon-helper name="check" class="w-4 h-4 mr-1.5" />
                            <span>Tandai Sudah Dibayar</span>
                        </x-cressco.button>
                    </form>
                @endif
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
                        @if ($calculation->finalized_at)
                            <div class="flex items-center justify-between">
                                <span>Difinalisasi Pada</span>
                                <span class="text-gray-900 font-medium">{{ $calculation->finalized_at->translatedFormat('d M Y H:i') }}</span>
                            </div>
                        @endif
                        @if ($calculation->paid_at)
                            <div class="flex items-center justify-between text-emerald-700">
                                <span>Dibayar Pada</span>
                                <span class="font-bold">{{ $calculation->paid_at->translatedFormat('d M Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tutor Profile Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-3 text-xs">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Profil Pengajar</h3>

                    <div class="flex items-center gap-3 pt-1">
                        <div class="w-10 h-10 rounded-full bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr($calculation->tutor?->name ?? 'T', 0, 2)) }}
                        </div>
                        <div>
                            <span class="font-bold text-gray-900 text-sm block">{{ $calculation->tutor?->name }}</span>
                            <span class="text-gray-500 text-[11px]">{{ $calculation->tutor?->email }}</span>
                        </div>
                    </div>

                    @if ($calculation->tutor?->phone)
                        <div class="pt-2 border-t border-gray-100 text-gray-600">
                            <span>No WhatsApp: </span>
                            <span class="font-medium text-gray-900">{{ $calculation->tutor->phone }}</span>
                        </div>
                    @endif

                    <div class="pt-2">
                        <a href="{{ route('owner.tutors.show', $calculation->tutor) }}" class="text-xs font-bold text-terracotta-600 hover:text-terracotta-700 transition">
                            Lihat Profil Lengkap Tutor →
                        </a>
                    </div>
                </div>

            </div>

            <!-- Column 2 & 3: Actual Teaching Sessions Detail Breakdown -->
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Rincian Sesi Mengajar Aktual ({{ $sessions->count() }} Sesi)</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Sesi mengajar yang diselesaikan oleh {{ $calculation->tutor?->name }} selama periode ini.</p>
                        </div>
                    </div>

                    <!-- Business Rules Info Callout -->
                    <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100 flex items-start gap-2.5 text-xs text-blue-800">
                        <x-cressco.icon-helper name="check" class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                        <div class="text-[11px] leading-relaxed">
                            <strong>Basis Data Aktual:</strong> Perhitungan honor per sesi dan per siswa dihitung secara akurat berdasarkan sesi aktual yang berstatus <code>completed</code>. Sesi pengganti (replacement) secara adil dialokasikan kepada pengajar aktual.
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        @forelse ($sessions as $session)
                            @php
                                $isReplacement = $session->scheduled_tutor_id !== $session->actual_tutor_id;
                            @endphp
                            <div class="p-3.5 rounded-xl border border-gray-200/80 bg-gray-50/40 hover:bg-gray-50 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-terracotta-50 text-terracotta-700 flex items-center justify-center font-bold text-xs shrink-0 border border-terracotta-200/60">
                                        {{ strtoupper(substr($session->class?->name ?? 'K', 0, 2)) }}
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900 text-sm">{{ $session->class?->name ?? 'Sesi Pembelajaran' }}</span>
                                            <x-cressco.badge variant="success" size="xs">
                                                Completed
                                            </x-cressco.badge>
                                            @if ($isReplacement)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Menggantikan Tutor</span>
                                            @endif
                                        </div>

                                        <div class="text-[11px] text-gray-500 mt-1">
                                            <span>Tanggal: <strong class="text-gray-700">{{ $session->session_date ? $session->session_date->translatedFormat('l, d F Y') : '-' }}</strong></span>
                                            <span>•</span>
                                            <span>Waktu: {{ substr($session->start_time, 0, 5) }}-{{ substr($session->end_time, 0, 5) }} WIB</span>
                                            <span>•</span>
                                            <span>Cabang: {{ $session->branch?->name ?? '-' }}</span>
                                        </div>

                                        @if ($session->material)
                                            <div class="text-[11px] text-gray-700 mt-1.5 bg-white p-2 rounded-lg border border-gray-100">
                                                <strong>Materi Diajarkan:</strong> {{ $session->material }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-[11px] text-gray-400 block">Ruangan</span>
                                    <span class="font-semibold text-gray-800">{{ $session->room ?: '-' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400 border border-dashed border-gray-200 rounded-xl">
                                <p class="text-xs font-semibold text-gray-600">Tidak ada sesi mengajar tercatat pada periode ini</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Honor ini mungkin menggunakan metode gaji tetap bulanan atau penyesuaian khusus.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-owner-layout>
