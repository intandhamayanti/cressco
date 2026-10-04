<x-owner-layout :tenant="$tenant" title="Laporan Keuangan & Kinerja">
    <x-slot:breadcrumbSub>Laporan</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Laporan Keuangan & Kinerja</h1>
                <p class="text-xs text-gray-500 mt-1">Ringkasan penerimaan kas, beban operasional honor tutor, margin keuntungan, dan performa antar cabang di {{ $tenant->name ?? 'Prime Academy' }}.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold transition flex items-center gap-2 shadow-2xs">
                    <x-cressco.icon-helper name="download" class="w-4 h-4" />
                    <span>Cetak / Simpan PDF</span>
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('owner.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-3 items-center">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Filter Cabang</label>
                    <select name="branch_id" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                        <option value="">Seluruh Cabang (Konsolidasi)</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" {{ $selectedBranchId === $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->city }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Filter Periode</label>
                    <select name="period" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                        <option value="">Semua Periode (All-Time)</option>
                        @foreach ($periods as $p)
                            <option value="{{ $p }}" {{ $selectedPeriod === $p ? 'selected' : '' }}>
                                Periode {{ $p }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-1 md:col-span-2 flex items-end gap-2 pt-5">
                    <button type="submit" class="h-10 px-5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold transition flex items-center gap-2">
                        <x-cressco.icon-helper name="filter" class="w-3.5 h-3.5" />
                        <span>Terapkan Filter</span>
                    </button>
                    @if ($selectedBranchId || $selectedPeriod)
                        <a href="{{ route('owner.reports.index') }}" class="h-10 px-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold transition flex items-center justify-center" title="Reset Filter">
                            <x-cressco.icon-helper name="close" class="w-3.5 h-3.5" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Executive Financial KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Total Pendapatan"
                icon="billing"
                iconColor="text-emerald-500"
                value="Rp {{ number_format($metrics['totalRevenue'], 0, ',', '.') }}"
                trend="Revenue"
                trendType="positive"
                subtitle="Dari tagihan berstatus lunas"
            />

            <x-cressco.project-card
                title="Beban Honor Tutor"
                icon="credit-card"
                iconColor="text-rose-500"
                value="Rp {{ number_format($metrics['totalExpenses'], 0, ',', '.') }}"
                trend="Expenses"
                trendType="error"
                subtitle="Honor final & terbayar"
            />

            <x-cressco.project-card
                title="Laba Bersih"
                icon="trend-up"
                iconColor="text-blue-500"
                value="Rp {{ number_format($metrics['netProfit'], 0, ',', '.') }}"
                trend="Net Profit"
                trendType="{{ $metrics['netProfit'] >= 0 ? 'positive' : 'error' }}"
                subtitle="Revenue dikurangi beban honor"
            />

            <x-cressco.project-card
                title="Collection Rate"
                icon="clock"
                iconColor="text-amber-500"
                value="{{ $metrics['collectionRate'] }}%"
                trend="Terkumpul"
                trendType="warning"
                subtitle="Piutang: Rp {{ number_format($metrics['totalOutstanding'], 0, ',', '.') }}"
            />
        </div>

        <!-- Monthly Trends & Status Distribution Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Monthly Trends Bar List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4 h-full">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Tren Finansial 6 Bulan Terakhir</h3>
                            <p class="text-xs text-gray-500">Perbandingan penerimaan revenue dan beban honor tutor.</p>
                        </div>
                    </div>

                    <div class="space-y-4 pt-2">
                        @php
                            $maxVal = max(1, ...array_map(fn($t) => max($t['revenue'], $t['expenses']), $monthlyTrends));
                        @endphp

                        @foreach ($monthlyTrends as $trend)
                            <div class="space-y-1.5 p-3 rounded-xl bg-gray-50/60 border border-gray-100">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-gray-900">{{ $trend['label'] }} ({{ $trend['period'] }})</span>
                                    <span class="font-bold {{ $trend['net_profit'] >= 0 ? 'text-blue-600' : 'text-rose-600' }}">
                                        Profit: Rp {{ number_format($trend['net_profit'], 0, ',', '.') }}
                                    </span>
                                </div>

                                <!-- Visual Bars -->
                                <div class="space-y-1">
                                    <!-- Revenue Bar -->
                                    <div class="flex items-center gap-2 text-[11px]">
                                        <span class="w-16 text-gray-400 shrink-0">Revenue:</span>
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width: {{ $maxVal > 0 ? round(($trend['revenue'] / $maxVal) * 100) : 0 }}%"></div>
                                        </div>
                                        <span class="w-28 text-right font-medium text-emerald-700 shrink-0">
                                            Rp {{ number_format($trend['revenue'], 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <!-- Expenses Bar -->
                                    <div class="flex items-center gap-2 text-[11px]">
                                        <span class="w-16 text-gray-400 shrink-0">Honor:</span>
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-rose-400 h-2 rounded-full transition-all" style="width: {{ $maxVal > 0 ? round(($trend['expenses'] / $maxVal) * 100) : 0 }}%"></div>
                                        </div>
                                        <span class="w-28 text-right font-medium text-rose-700 shrink-0">
                                            Rp {{ number_format($trend['expenses'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Payment Status Distribution -->
            <div>
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4 h-full">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Distribusi Status Tagihan</h3>
                        <p class="text-xs text-gray-500">Komposisi status invoice berdasarkan filter aktif.</p>
                    </div>

                    @php
                        $totalCount = array_sum($statusCounts);
                    @endphp

                    <div class="space-y-3 pt-2">
                        <!-- Lunas -->
                        <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-100 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Lunas</p>
                                    <p class="text-[10px] text-gray-500">{{ $totalCount > 0 ? round(($statusCounts['lunas'] / $totalCount) * 100, 1) : 0 }}% dari total tagihan</p>
                                </div>
                            </div>
                            <span class="text-sm font-extrabold text-emerald-700">{{ $statusCounts['lunas'] }}</span>
                        </div>

                        <!-- Menunggu Verifikasi -->
                        <div class="p-3 rounded-xl bg-amber-50/50 border border-amber-100 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Menunggu Verifikasi</p>
                                    <p class="text-[10px] text-gray-500">{{ $totalCount > 0 ? round(($statusCounts['menunggu_verifikasi'] / $totalCount) * 100, 1) : 0 }}% dari total tagihan</p>
                                </div>
                            </div>
                            <span class="text-sm font-extrabold text-amber-700">{{ $statusCounts['menunggu_verifikasi'] }}</span>
                        </div>

                        <!-- Belum Bayar -->
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-slate-400"></span>
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Belum Bayar</p>
                                    <p class="text-[10px] text-gray-500">{{ $totalCount > 0 ? round(($statusCounts['belum_bayar'] / $totalCount) * 100, 1) : 0 }}% dari total tagihan</p>
                                </div>
                            </div>
                            <span class="text-sm font-extrabold text-slate-700">{{ $statusCounts['belum_bayar'] }}</span>
                        </div>

                        <!-- Terlambat -->
                        <div class="p-3 rounded-xl bg-rose-50/50 border border-rose-100 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Terlambat (Overdue)</p>
                                    <p class="text-[10px] text-gray-500">{{ $totalCount > 0 ? round(($statusCounts['terlambat'] / $totalCount) * 100, 1) : 0 }}% dari total tagihan</p>
                                </div>
                            </div>
                            <span class="text-sm font-extrabold text-rose-700">{{ $statusCounts['terlambat'] }}</span>
                        </div>
                    </div>

                    <div class="pt-2 text-[11px] text-gray-400 text-center">
                        Total Tagihan: <span class="font-bold text-gray-700">{{ $totalCount }} Invoice</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Comparison Breakdown Table -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden space-y-4">
            <div class="p-6 pb-2">
                <h3 class="text-sm font-bold text-gray-900">Rincian Performa & Keuangan Per Cabang</h3>
                <p class="text-xs text-gray-500">Perbandingan pendapatan, piutang, beban honor tutor, dan margin laba bersih tiap cabang.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/75 border-y border-gray-200/80 text-gray-500 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">Nama Cabang</th>
                            <th class="px-6 py-3.5 text-center">Siswa Aktif</th>
                            <th class="px-6 py-3.5 text-right">Ditagihkan</th>
                            <th class="px-6 py-3.5 text-right">Revenue (Lunas)</th>
                            <th class="px-6 py-3.5 text-right">Beban Honor</th>
                            <th class="px-6 py-3.5 text-right">Laba Bersih</th>
                            <th class="px-6 py-3.5 text-center">Collection Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse ($branchBreakdown as $b)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $b['name'] }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $b['city'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-gray-900">
                                    {{ $b['active_students'] }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap font-medium text-gray-900">
                                    Rp {{ number_format($b['invoiced'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap font-bold text-emerald-600">
                                    Rp {{ number_format($b['revenue'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap font-medium text-rose-600">
                                    Rp {{ number_format($b['expenses'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap font-bold {{ $b['net_profit'] >= 0 ? 'text-blue-600' : 'text-rose-600' }}">
                                    Rp {{ number_format($b['net_profit'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $b['collection_rate'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($b['collection_rate'] >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $b['collection_rate'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada cabang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-owner-layout>
