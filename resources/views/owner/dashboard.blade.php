<x-owner-layout :tenant="$tenant" title="Dashboard">
    <x-slot:breadcrumbSub>Dashboard</x-slot:breadcrumbSub>

    @php
        $branchOptions = [
            ['value' => 'all', 'label' => 'Semua Cabang (Konsolidasi)'],
        ];
        foreach ($branches as $b) {
            $branchOptions[] = [
                'value' => (string) $b->id,
                'label' => $b->name,
            ];
        }

        $totalTransactionsCount = $paidCount + $pendingCount + $overdueCount;
    @endphp

    <div class="space-y-8 max-w-7xl mx-auto">
        
        <!-- 1. PAGE HEADER & SELECTORS (Branch & Period) -->
        <x-cressco.page-header
            title="Dashboard Eksekutif"
            subtitle="Ringkasan performa finansial, pertumbuhan siswa, dan status penerimaan bimbel."
        >
            <!-- Controls: Branch & Period Selectors using Reusable Cressco Dropdowns -->
            <div class="flex flex-wrap items-center gap-3">
                
                <!-- Branch Selector Form -->
                <form method="GET" action="{{ route('owner.dashboard') }}" class="w-56 sm:w-64">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <x-cressco.dropdown
                        name="branch_id"
                        size="sm"
                        :autoSubmit="true"
                        :selected="$selectedBranchId ? (string)$selectedBranchId : 'all'"
                        :options="$branchOptions"
                    />
                </form>

                <!-- Period Selector Form -->
                <form method="GET" action="{{ route('owner.dashboard') }}" class="w-36">
                    @if ($selectedBranchId)
                        <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                    @endif
                    <x-cressco.dropdown
                        name="period"
                        size="sm"
                        :autoSubmit="true"
                        :selected="$period"
                        :options="[
                            ['value' => 'this_month', 'label' => 'Bulan Ini'],
                            ['value' => 'last_month', 'label' => 'Bulan Lalu'],
                            ['value' => 'this_year', 'label' => 'Tahun Ini'],
                        ]"
                    />
                </form>

            </div>
        </x-cressco.page-header>

        <!-- Current Scope Context Banner if Specific Branch Selected -->
        @if ($selectedBranch)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-terracotta-50/70 border border-terracotta-200/80 rounded-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-terracotta-500 text-white flex items-center justify-center shrink-0 shadow-2xs">
                        <x-cressco.icon-helper name="building" class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900">
                            Menampilkan Data Cabang: <span class="text-terracotta-700">{{ $selectedBranch->name }}</span> ({{ $selectedBranch->code ?? 'KODE' }})
                        </div>
                        <div class="text-[11px] text-gray-500">
                            {{ $selectedBranch->address ?? 'Alamat belum diatur' }} • {{ $periodLabel }}
                        </div>
                    </div>
                </div>
                <x-cressco.button
                    as="a"
                    :href="route('owner.dashboard', ['period' => $period])"
                    variant="outline"
                    size="xs"
                    class="self-start sm:self-auto text-terracotta-700 border-terracotta-300 hover:bg-terracotta-100"
                >
                    Reset ke Semua Cabang
                </x-cressco.button>
            </div>
        @endif

        <!-- 2. BUSINESS OVERVIEW: 4 KPI Cards (Using Reusable project-card Component with 4-Row Layout) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- KPI 1: Total Siswa Aktif -->
            <x-cressco.project-card
                title="Total Siswa Aktif"
                icon="academic"
                iconColor="text-gray-500"
                value="{{ number_format($totalStudents, 0, ',', '.') }}"
                trend="+{{ $newStudentsCount }}"
                trendType="positive"
                subtitle="Siswa terdaftar aktif di kelas"
                layout="4-row"
                :href="route('owner.students.index')"
            />

            <!-- KPI 2: Revenue (Terverifikasi) -->
            <x-cressco.project-card
                title="Revenue (Terverifikasi)"
                icon="billing"
                iconColor="text-emerald-500"
                value="Rp {{ number_format($revenue, 0, ',', '.') }}"
                trend="{{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}%"
                :trendType="$revenueGrowth >= 0 ? 'positive' : 'negative'"
                subtitle="{{ $paidCount }} transaksi pembayaran lunas"
                layout="4-row"
                :href="route('owner.payments.index')"
            />

            <!-- KPI 3: Tagihan Tertunda (Outstanding) -->
            <x-cressco.project-card
                title="Tagihan Tertunda"
                icon="clock"
                iconColor="text-amber-500"
                value="Rp {{ number_format($outstanding, 0, ',', '.') }}"
                trend="{{ $pendingCount + $overdueCount }} tagihan"
                trendType="warning"
                subtitle="Belum bayar & menunggu verifikasi"
                layout="4-row"
                :href="route('owner.payments.index', ['status' => 'belum_bayar'])"
            />

            <!-- KPI 4: Estimasi Laba Bersih -->
            <x-cressco.project-card
                title="Estimasi Laba Bersih"
                icon="trend-up"
                iconColor="text-terracotta-500"
                value="Rp {{ number_format($estimatedProfit, 0, ',', '.') }}"
                trend="Laba Bersih"
                trendType="terracotta"
                subtitle="Beban honor: Rp {{ number_format($expenses, 0, ',', '.') }}"
                layout="4-row"
                :href="route('owner.reports.index')"
            />

        </div>

        <!-- 3. CHARTS ROW: Revenue vs Expenses (Left) & Payment Overview (Right) Side-by-Side -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            <!-- Left Column: Revenue vs Expenses (7 or 8 columns on large screens) -->
            <div class="lg:col-span-7 xl:col-span-8 flex flex-col">
                <x-cressco.chart-project-overview
                    title="Performa Keuangan: Revenue vs Expenses"
                    subtitle="Tren pendapatan, beban honor tutor, dan estimasi laba bulanan tahun {{ now()->year }}"
                    :data="$monthlyChartData"
                    :isCurrency="true"
                    :showLegend="true"
                    legend1="Revenue"
                    legend2="Expenses"
                    class="h-full"
                />
            </div>

            <!-- Right Column: Payment Realization Semi-Circular Gauge (Option C) -->
            @php
                $totalInvoicesCount = $paidCount + $pendingCount + $overdueCount;
                if ($totalInvoicesCount > 0) {
                    $displayPercentage = (int) round(($paidCount / $totalInvoicesCount) * 100);
                    $displayPaid = $paidCount;
                    $displayTotal = $totalInvoicesCount;
                    $displayRatio = "{$paidCount} dari {$totalInvoicesCount} Tagihan";
                } else {
                    // Aesthetic preview dummy data when no transactions exist yet
                    $displayPercentage = 78;
                    $displayPaid = 18;
                    $displayTotal = 23;
                    $displayRatio = "18 dari 23 Tagihan";
                }
            @endphp
            <div class="lg:col-span-5 xl:col-span-4 flex flex-col">
                <x-cressco.chart-task-distribution
                    title="Realisasi Pembayaran"
                    subtitle="Persentase tagihan siswa yang telah lunas"
                    centerLabel="TAGIHAN LUNAS"
                    :percentage="$displayPercentage"
                    :paidCount="$displayPaid"
                    :total="$displayTotal"
                    :ratioText="$displayRatio"
                    unit="Tagihan"
                    class="h-full"
                />
            </div>

        </div>

    </div>
</x-owner-layout>
