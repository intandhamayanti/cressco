<x-admin-layout :tenant="$tenant" title="Admin Dashboard">
    <x-slot:breadcrumbSub>Dashboard</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{ loading: true }"
         x-init="setTimeout(() => loading = false, 2500)">
        
        <!-- Skeleton Loading State (Visible for 2.5s) -->
        <div x-show="loading" class="transition-opacity duration-300">
            <x-cressco.skeleton-dashboard type="admin" />
        </div>

        <!-- Real Dashboard Content -->
        <div x-show="!loading"
             x-cloak
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6">

            <!-- Header & Branch Filter Selector -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Operasional Cabang</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Ringkasan jadwal mengajar, status tutor, dan penanganan siswa di {{ $tenant->name ?? 'Prime Academy' }}.
                </p>
            </div>

            <!-- Branch Scope Filter Selector -->
            @if ($accessibleBranches->count() > 1)
                <div class="flex items-center gap-2">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2" id="branchFilterForm">
                        <label for="branch_id_select" class="text-xs font-semibold text-gray-600 shrink-0">Cabang:</label>
                        <select name="branch_id"
                                id="branch_id_select"
                                onchange="document.getElementById('branchFilterForm').submit()"
                                class="text-xs font-medium text-gray-800 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-2xs focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer">
                            <option value="all" {{ ! $selectedBranchId ? 'selected' : '' }}>Semua Cabang Saya ({{ $accessibleBranches->count() }})</option>
                            @foreach ($accessibleBranches as $branch)
                                <option value="{{ $branch->id }}" {{ $selectedBranchId === $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @elseif ($accessibleBranches->count() === 1)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-800">
                    <x-cressco.icon-helper name="building" class="w-4 h-4 text-terracotta-500" />
                    <span>Cabang: {{ $accessibleBranches->first()->name }}</span>
                </div>
            @endif
        </div>

        <!-- 4 Operational Stat Cards (Balanced 4-Column Grid) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Siswa Aktif"
                icon="academic"
                iconColor="text-emerald-500"
                value="{{ $activeStudentsCount }}"
                trend="{{ $totalStudentsCount }} Total"
                trendType="positive"
                subtitle="Siswa aktif di cabang akses Anda"
            />

            <x-cressco.project-card
                title="Kelas Aktif"
                icon="folder"
                iconColor="text-blue-500"
                value="{{ $activeClassesCount }}"
                trend="Berjalan"
                trendType="neutral"
                subtitle="Total rombel & kelas aktif"
            />

            <x-cressco.project-card
                title="Kehadiran Siswa"
                icon="check-circle"
                iconColor="text-teal-500"
                value="{{ $attendanceRate }}%"
                trend="Bulan Ini"
                trendType="positive"
                subtitle="Rasio presensi siswa hadir"
            />

            <x-cressco.project-card
                title="Perlu Tindak Lanjut"
                icon="credit-card"
                iconColor="text-rose-500"
                value="{{ $pendingPaymentsCount }}"
                trend="Rp {{ number_format($pendingPaymentsAmount, 0, ',', '.') }}"
                trendType="negative"
                subtitle="Tagihan pending / terlambat"
            />
        </div>

        <!-- Upper Section: Today's Schedule & Teaching Sessions -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-terracotta-50 text-terracotta-600 flex items-center justify-center font-bold shrink-0">
                        <x-cressco.icon-helper name="calendar" class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Jadwal & Sesi Mengajar Hari Ini</h2>
                        <p class="text-[11px] text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-cressco.badge variant="neutral">
                        {{ $todaySessions->count() }} Sesi Terjadwal
                    </x-cressco.badge>
                </div>
            </div>

            @if ($todaySessions->isNotEmpty())
                <div class="divide-y divide-gray-100">
                    @foreach ($todaySessions as $session)
                        <div class="p-4 sm:p-5 hover:bg-gray-50/60 transition flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0">
                                <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-200/70 flex flex-col items-center justify-center shrink-0 text-center shadow-2xs">
                                    <span class="text-[11px] font-bold text-gray-900 leading-tight">
                                        {{ $session->start_time ? substr($session->start_time, 0, 5) : '-' }}
                                    </span>
                                    <span class="text-[9px] text-gray-400 font-medium leading-tight">
                                        {{ $session->end_time ? substr($session->end_time, 0, 5) : 'WIB' }}
                                    </span>
                                </div>
                                <div class="min-w-0 space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-bold text-gray-900 truncate">
                                            {{ $session->classModel?->name ?? 'Kelas' }}
                                        </h3>
                                        <span class="text-xs text-gray-400 font-medium">({{ $session->branch?->name ?? 'Cabang' }})</span>
                                        @if ($session->classModel?->subject)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-700">
                                                {{ $session->classModel->subject }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                                        <div class="flex items-center gap-1.5">
                                            <x-cressco.icon-helper name="user" class="w-3.5 h-3.5 text-gray-400" />
                                            <span>Tutor: <strong class="text-gray-800 font-semibold">{{ $session->actualTutor?->name ?? ($session->scheduledTutor?->name ?? 'Belum ditentukan') }}</strong></span>
                                        </div>
                                        @if ($session->room)
                                            <span class="text-gray-300">•</span>
                                            <div class="flex items-center gap-1.5">
                                                <x-cressco.icon-helper name="building" class="w-3.5 h-3.5 text-gray-400" />
                                                <span>Ruang: {{ $session->room }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 self-start md:self-center shrink-0">
                                @if ($session->status === 'completed')
                                    <x-cressco.badge variant="success" dot>Selesai</x-cressco.badge>
                                @elseif ($session->status === 'in_progress')
                                    <x-cressco.badge variant="warning" dot>Berlangsung</x-cressco.badge>
                                @elseif ($session->status === 'cancelled')
                                    <x-cressco.badge variant="danger" dot>Dibatalkan</x-cressco.badge>
                                @else
                                    <x-cressco.badge variant="info" dot>Terjadwal</x-cressco.badge>
                                @endif

                                <a href="{{ route('admin.attendances.sessions.show', $session) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold border border-gray-200/90 shadow-2xs transition">
                                    <span>Detail Sesi</span>
                                    <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5 text-gray-400" />
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-10 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-200/60 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="calendar" class="w-6 h-6 text-gray-400" />
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Tidak ada sesi mengajar hari ini</h3>
                    <p class="text-xs text-gray-500 mt-1">Semua jadwal kelas hari ini kosong atau belum memiliki sesi aktif.</p>
                </div>
            @endif
        </div>

        <!-- Lower Section: 2 Balanced Symmetrical Columns (Seamless, Minimalist & Aligned) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            
            <!-- Left Column: Sesi Membutuhkan Perhatian (Tutor Pengganti) -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs flex flex-col h-full overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold shrink-0">
                            <x-cressco.icon-helper name="alert-circle" class="w-4 h-4" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Sesi Membutuhkan Perhatian</h2>
                            <p class="text-[11px] text-gray-500">Tutor berhalangan / butuh tutor pengganti</p>
                        </div>
                    </div>
                    
                    @if ($sessionsNeedingAttention->isNotEmpty())
                        <x-cressco.badge variant="warning">
                            {{ $sessionsNeedingAttention->count() }} Perlu Pengganti
                        </x-cressco.badge>
                    @else
                        <x-cressco.badge variant="neutral">
                            Terkendali
                        </x-cressco.badge>
                    @endif
                </div>

                <div class="flex-1 flex flex-col justify-between">
                    @if ($sessionsNeedingAttention->isNotEmpty())
                        <div class="divide-y divide-gray-100">
                            @foreach ($sessionsNeedingAttention->take(5) as $session)
                                <div class="p-4 hover:bg-gray-50/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 border border-gray-200/60 font-bold text-xs text-gray-700">
                                            {{ strtoupper(substr($session->classModel?->name ?? 'K', 0, 2)) }}
                                        </div>
                                        <div class="min-w-0 space-y-0.5">
                                            <h3 class="text-xs font-bold text-gray-900 truncate">
                                                {{ $session->classModel?->name ?? 'Kelas' }}
                                            </h3>
                                            <div class="text-[11px] text-gray-500">
                                                Tutor: <strong class="text-gray-900 font-semibold">{{ $session->scheduledTutor?->name ?? 'Belum ditentukan' }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 self-start sm:self-center shrink-0">
                                        <x-cressco.badge variant="warning">
                                            Tutor Berhalangan
                                        </x-cressco.badge>

                                        <a href="{{ route('admin.attendances.sessions.show', $session) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs border border-gray-200/90 transition shadow-2xs">
                                            <span>Atur Pengganti</span>
                                            <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5 text-gray-400" />
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center flex-1 flex flex-col items-center justify-center">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 border border-gray-100 text-gray-400 flex items-center justify-center mb-2">
                                <x-cressco.icon-helper name="check-circle" class="w-5 h-5 text-emerald-500" />
                            </div>
                            <h3 class="text-xs font-bold text-gray-800">Semua Sesi Memiliki Pengajar</h3>
                            <p class="text-[11px] text-gray-500 mt-0.5">Tidak ada laporan ketidakhadiran tutor yang membutuhkan penggantian.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Perlu Tindak Lanjut (Tagihan & Verifikasi Pembayaran) -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs flex flex-col h-full overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold shrink-0">
                            <x-cressco.icon-helper name="credit-card" class="w-4 h-4" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Perlu Tindak Lanjut</h2>
                            <p class="text-[11px] text-gray-500">Tagihan jatuh tempo & verifikasi pembayaran</p>
                        </div>
                    </div>

                    @if ($actionRequiredPayments->isNotEmpty())
                        <x-cressco.badge variant="danger">
                            {{ $actionRequiredPayments->count() }} Tagihan
                        </x-cressco.badge>
                    @else
                        <x-cressco.badge variant="neutral">
                            Terkendali
                        </x-cressco.badge>
                    @endif
                </div>

                <div class="flex-1 flex flex-col justify-between">
                    @if ($actionRequiredPayments->isNotEmpty())
                        <div class="divide-y divide-gray-100">
                            @foreach ($actionRequiredPayments->take(5) as $pay)
                                <div class="p-4 hover:bg-gray-50/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 border border-gray-200/60 font-bold text-xs text-gray-700">
                                            {{ strtoupper(substr($pay->student?->name ?? 'S', 0, 2)) }}
                                        </div>
                                        <div class="min-w-0 space-y-0.5">
                                            <h3 class="text-xs font-bold text-gray-900 truncate">
                                                {{ $pay->student?->name ?? 'Siswa' }}
                                            </h3>
                                            <div class="text-[11px] text-gray-500">
                                                Nominal: <strong class="text-gray-900 font-semibold">Rp {{ number_format($pay->amount, 0, ',', '.') }}</strong>
                                                @if($pay->due_date)
                                                    &bull; Tempo: <span class="text-gray-600">{{ \Carbon\Carbon::parse($pay->due_date)->translatedFormat('d M Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 self-start sm:self-center shrink-0">
                                        <x-cressco.badge :variant="$pay->status === 'menunggu_verifikasi' ? 'warning' : 'danger'">
                                            {{ $pay->status === 'menunggu_verifikasi' ? 'Verifikasi' : 'Terlambat' }}
                                        </x-cressco.badge>
                                        
                                        <a href="{{ route('admin.payments.show', $pay) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs border border-gray-200/90 transition shadow-2xs">
                                            <span>Detail</span>
                                            <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5 text-gray-400" />
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center flex-1 flex flex-col items-center justify-center">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 border border-gray-100 text-gray-400 flex items-center justify-center mb-2">
                                <x-cressco.icon-helper name="check-circle" class="w-5 h-5 text-emerald-500" />
                            </div>
                            <h3 class="text-xs font-bold text-gray-800">Semua Tagihan Tertangani</h3>
                            <p class="text-[11px] text-gray-500 mt-0.5">Tidak ada tagihan tertunggak atau pembayaran yang menunggu verifikasi saat ini.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>
</x-admin-layout>
