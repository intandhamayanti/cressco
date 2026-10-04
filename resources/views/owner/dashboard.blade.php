<x-owner-layout :tenant="$tenant" title="Dashboard">
    <x-slot:breadcrumbSub>Dashboard</x-slot:breadcrumbSub>

    @php
        $branchOptions = [
            ['value' => 'all', 'label' => 'Semua Cabang (Consolidated)'],
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
            title="Dashboard"
            subtitle="Overview of your tutoring center's performance and operations."
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
                subtitle="Siswa terdaftar aktif di seluruh kelas"
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

            <!-- KPI 4: Estimasi Laba Operasional -->
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

            <!-- Right Column: Payment Overview Semi-Circular Gauge -->
            <div class="lg:col-span-5 xl:col-span-4 flex flex-col">
                <x-cressco.chart-task-distribution
                    title="Payment Overview"
                    subtitle="Status penerimaan tagihan & transaksi siswa"
                    centerLabel="TOTAL TRANSAKSI"
                    :total="$totalTransactionsCount"
                    unit="Tagihan"
                    :segments="[
                        ['label' => 'Lunas', 'value' => $paidCount, 'color' => '#C84B31', 'barColor' => 'bg-[#C84B31]'],
                        ['label' => 'Pending', 'value' => $pendingCount, 'color' => '#2B3440', 'barColor' => 'bg-[#2B3440]'],
                        ['label' => 'Overdue', 'value' => $overdueCount, 'color' => '#717B8C', 'barColor' => 'bg-[#717B8C]'],
                    ]"
                    class="h-full"
                />
            </div>

        </div>

        <!-- 4. DETAILED OPERATIONAL & ACTIVITY GRID (2x2 Balanced Grid) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            
            <!-- Row 1 Left: Payment Breakdown & Collection Progress -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-5 h-full">
                <div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Rincian Nominal Pembayaran</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Ringkasan perputaran dana periode {{ $periodLabel }}</p>
                        </div>
                        <x-cressco.badge :variant="$paymentCollectionRate >= 80 ? 'success' : 'warning'" size="sm">
                            {{ $paymentCollectionRate }}% Terkumpul
                        </x-cressco.badge>
                    </div>

                    <!-- Collection Progress Bar -->
                    <div class="space-y-1.5 mt-5">
                        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden flex">
                            <div class="bg-emerald-500 h-full transition-all duration-300" style="width: {{ $paymentCollectionRate }}%"></div>
                            <div class="bg-amber-400 h-full transition-all duration-300" style="width: {{ 100 - $paymentCollectionRate }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Payment Breakdown List -->
                <div class="divide-y divide-gray-100 text-xs mt-2">
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="font-medium text-gray-700">Lunas / Terverifikasi</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-gray-900">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                            <span class="text-[11px] text-gray-400 ml-1">({{ $paidCount }})</span>
                        </div>
                    </div>

                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="font-medium text-gray-700">Pending / Menunggu Verifikasi</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-gray-900">Rp {{ number_format($pendingAmount, 0, ',', '.') }}</span>
                            <span class="text-[11px] text-gray-400 ml-1">({{ $pendingCount }})</span>
                        </div>
                    </div>

                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                            <span class="font-medium text-gray-700">Terlambat (Overdue)</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-gray-900">Rp {{ number_format($overdueAmount, 0, ',', '.') }}</span>
                            <span class="text-[11px] text-gray-400 ml-1">({{ $overdueCount }})</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 1 Right: Attendance Overview Card -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-5 h-full">
                <div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Attendance Overview</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Tingkat kehadiran siswa pada sesi pembelajaran</p>
                        </div>
                        <x-cressco.badge :variant="$attendanceRate >= 85 ? 'success' : ($attendanceRate >= 75 ? 'warning' : 'error')" size="sm">
                            {{ $attendanceRate }}% Rata-rata
                        </x-cressco.badge>
                    </div>

                    <!-- Big Metric & Context -->
                    <div class="flex items-center gap-5 p-4 rounded-2xl bg-gray-50/80 border border-gray-100 mt-5">
                        <div class="text-4xl font-bold text-gray-900 tracking-tight">{{ $attendanceRate }}%</div>
                        <div class="text-xs text-gray-600 space-y-1">
                            <div class="font-semibold text-gray-800">
                                {{ $lowAttendanceCount > 0 ? "{$lowAttendanceCount} siswa membutuhkan perhatian khusus" : "Tingkat kehadiran optimal" }}
                            </div>
                            <p class="text-[11px] text-gray-500 leading-tight">
                                Siswa dengan persentase kehadiran di bawah ambang batas minimal 70%.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Attendance Quick Highlights -->
                <div class="grid grid-cols-2 gap-3 text-xs mt-2">
                    <div class="p-3 rounded-2xl border border-gray-100 bg-white space-y-1">
                        <span class="text-gray-500 text-[11px]">Ambang Kehadiran Standar</span>
                        <div class="font-bold text-gray-900">≥ 80% Min Target</div>
                    </div>
                    <div class="p-3 rounded-2xl border border-gray-100 bg-white space-y-1">
                        <span class="text-gray-500 text-[11px]">Status Evaluasi</span>
                        <div class="font-bold {{ $attendanceRate >= 80 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $attendanceRate >= 80 ? 'Sesuai Target Bimbel' : 'Perlu Pendampingan' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2 Left: Student & Operational Overview -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-4 h-full">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Student & Operational Overview</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Ringkasan kapasitas kelas dan aktivitas belajar mengajar</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <div class="p-3.5 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-1">
                        <div class="text-[11px] font-semibold text-gray-500">Siswa Aktif</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $totalStudents }}</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-1">
                        <div class="text-[11px] font-semibold text-gray-500">Kelas Aktif</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $activeClassesCount }}</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-1">
                        <div class="text-[11px] font-semibold text-gray-500">Sesi Hari Ini</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $todaySessionsCount }}</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-1">
                        <div class="text-[11px] font-semibold text-gray-500">Tutor Aktif</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $activeTutorsCount }}</div>
                    </div>
                </div>
            </div>

            <!-- Row 2 Right: Recent Activity Card -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-4 h-full">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Recent Activity</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Aktivitas pembayaran dan pendaftaran siswa terbaru</p>
                    </div>
                    <span class="text-[11px] text-gray-400 font-medium">Terbaru</span>
                </div>

                <!-- Activity List with Icon + Relative Time -->
                <div class="divide-y divide-gray-100 text-xs">
                    @forelse ($recentActivities as $act)
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-xl {{ $act['type'] === 'payment_success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : ($act['type'] === 'enrollment' ? 'bg-purple-50 text-purple-600 border border-purple-100' : 'bg-amber-50 text-amber-600 border border-amber-100') }} flex items-center justify-center shrink-0">
                                    @if ($act['type'] === 'enrollment')
                                        <x-cressco.icon-helper name="academic" class="w-4 h-4" />
                                    @else
                                        <x-cressco.icon-helper name="credit-card" class="w-4 h-4" />
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-900 truncate">{{ $act['user'] }}</span>
                                        <span class="text-[10px] text-gray-400 font-normal shrink-0">{{ $act['time'] }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 truncate">{{ $act['action'] }}</div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-semibold text-gray-900">{{ $act['detail'] }}</span>
                                <div class="text-[10px] text-gray-400">{{ $act['badge'] }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-gray-400 text-xs">
                            Belum ada aktivitas tercatat pada periode ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</x-owner-layout>
