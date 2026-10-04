<x-admin-layout :tenant="$tenant" title="Laporan Operasional Cabang">
    <x-slot:breadcrumbSub>Laporan</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Laporan Operasional Cabang</h1>
                <p class="text-xs text-gray-500 mt-1">Ringkasan kinerja siswa, kelas, presensi pengajaran, tagihan kas, dan honor tutor pada cabang akses Anda.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold transition flex items-center gap-2 shadow-2xs cursor-pointer">
                    <x-cressco.icon-helper name="download" class="w-4 h-4" />
                    <span>Cetak / Simpan PDF</span>
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-3 items-center">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Filter Cabang</label>
                    <select name="branch_id" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                        <option value="">Semua Cabang Akses Anda</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" {{ ($filters['branch_id'] ?? '') === $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Filter Periode</label>
                    <select name="period" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                        <option value="">Semua Periode</option>
                        @foreach ($allPeriods as $p)
                            <option value="{{ $p }}" {{ ($filters['period'] ?? '') === $p ? 'selected' : '' }}>
                                Periode {{ $p }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-1 md:col-span-2 flex items-end gap-2 pt-5">
                    <button type="submit" class="h-10 px-5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold transition flex items-center gap-2 cursor-pointer">
                        <x-cressco.icon-helper name="filter" class="w-3.5 h-3.5" />
                        <span>Terapkan Filter</span>
                    </button>
                    @if ($filters['branch_id'] || $filters['period'])
                        <a href="{{ route('admin.reports.index') }}" class="h-10 px-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold transition flex items-center justify-center" title="Reset Filter">
                            <x-cressco.icon-helper name="close" class="w-3.5 h-3.5" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Summary KPIs Top -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Siswa Aktif"
                icon="users"
                iconColor="text-blue-500"
                value="{{ number_format($students['active']) }}"
                trend="Siswa"
                trendType="neutral"
                subtitle="Total siswa bimbingan terdaftar"
            />

            <x-cressco.project-card
                title="Utilisasi Kelas"
                icon="academic"
                iconColor="text-emerald-500"
                value="{{ $classes['utilization'] }}%"
                trend="{{ $classes['activeClasses'] }} Kelas"
                trendType="positive"
                subtitle="Kapasitas terisi: {{ $classes['enrollments'] }}/{{ $classes['capacity'] }}"
            />

            <x-cressco.project-card
                title="Tingkat Presensi"
                icon="check-circle"
                iconColor="text-purple-500"
                value="{{ $attendance['rate'] }}%"
                trend="{{ $attendance['sessions'] }} Sesi"
                trendType="positive"
                subtitle="Persentase kehadiran siswa"
            />

            <x-cressco.project-card
                title="Penerimaan Kas"
                icon="billing"
                iconColor="text-terracotta-500"
                value="Rp {{ number_format($finance['revenue'], 0, ',', '.') }}"
                trend="Collection: {{ $finance['collectionRate'] }}%"
                trendType="terracotta"
                subtitle="Tagihan lunas terverifikasi"
            />
        </div>

        <!-- Grid 2 Columns: Detailed Report Categories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Section 1: Ringkasan Siswa & Akademik -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <x-cressco.icon-helper name="users" class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Ringkasan Siswa & Pendaftaran</h3>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-gray-400 block">Total Siswa Terdata</span>
                        <span class="text-base font-bold text-gray-900 mt-0.5 block">{{ number_format($students['total']) }}</span>
                    </div>
                    <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100">
                        <span class="text-emerald-700 font-medium block">Siswa Aktif Belajar</span>
                        <span class="text-base font-bold text-emerald-900 mt-0.5 block">{{ number_format($students['active']) }}</span>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-gray-400 block">Siswa Nonaktif / Lulus</span>
                        <span class="text-base font-bold text-gray-600 mt-0.5 block">{{ number_format($students['inactive']) }}</span>
                    </div>
                    <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-100">
                        <span class="text-purple-700 font-medium block">Pendaftaran Bulan Ini</span>
                        <span class="text-base font-bold text-purple-900 mt-0.5 block">{{ number_format($students['new']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Ringkasan Kelas & Jadwal -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <x-cressco.icon-helper name="academic" class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Ringkasan Kelas & Jadwal</h3>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-gray-400 block">Kelas Aktif</span>
                        <span class="text-base font-bold text-gray-900 mt-0.5 block">{{ number_format($classes['activeClasses']) }}</span>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-gray-400 block">Total Kapasitas Kursi</span>
                        <span class="text-base font-bold text-gray-900 mt-0.5 block">{{ number_format($classes['capacity']) }} Kursi</span>
                    </div>
                    <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100">
                        <span class="text-blue-700 font-medium block">Siswa Ter-enroll</span>
                        <span class="text-base font-bold text-blue-900 mt-0.5 block">{{ number_format($classes['enrollments']) }}</span>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-gray-400 block">Jadwal Mingguan Aktif</span>
                        <span class="text-base font-bold text-gray-900 mt-0.5 block">{{ number_format($classes['schedules']) }} Jadwal</span>
                    </div>
                </div>
            </div>

            <!-- Section 3: Ringkasan Kehadiran & Sesi -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                            <x-cressco.icon-helper name="check-circle" class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Ringkasan Presensi & Kehadiran</h3>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-100 text-center">
                        <span class="text-emerald-700 font-semibold block text-[11px]">Hadir</span>
                        <span class="text-sm font-extrabold text-emerald-900 block mt-0.5">{{ number_format($attendance['present']) }}</span>
                    </div>
                    <div class="p-2.5 bg-blue-50 rounded-xl border border-blue-100 text-center">
                        <span class="text-blue-700 font-semibold block text-[11px]">Izin</span>
                        <span class="text-sm font-extrabold text-blue-900 block mt-0.5">{{ number_format($attendance['permission']) }}</span>
                    </div>
                    <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-100 text-center">
                        <span class="text-amber-700 font-semibold block text-[11px]">Sakit</span>
                        <span class="text-sm font-extrabold text-amber-900 block mt-0.5">{{ number_format($attendance['sick']) }}</span>
                    </div>
                    <div class="p-2.5 bg-rose-50 rounded-xl border border-rose-100 text-center">
                        <span class="text-rose-700 font-semibold block text-[11px]">Alpa</span>
                        <span class="text-sm font-extrabold text-rose-900 block mt-0.5">{{ number_format($attendance['absent']) }}</span>
                    </div>
                </div>

                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs flex items-center justify-between">
                    <span class="text-gray-500">Sesi Pengajaran Selesai Terverifikasi:</span>
                    <span class="font-bold text-gray-900">{{ number_format($attendance['sessions']) }} Sesi</span>
                </div>
            </div>

            <!-- Section 4: Ringkasan Tagihan & Kas Masuk -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-terracotta-50 text-terracotta-600 flex items-center justify-center">
                            <x-cressco.icon-helper name="billing" class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Ringkasan Tagihan & Kas</h3>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-xl">
                        <span class="text-gray-500">Total Tagihan Diterbitkan:</span>
                        <span class="font-bold text-gray-900">Rp {{ number_format($finance['invoiced'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-2.5 bg-emerald-50 rounded-xl border border-emerald-100">
                        <span class="text-emerald-700 font-semibold">Penerimaan Kas (Lunas):</span>
                        <span class="font-bold text-emerald-900">Rp {{ number_format($finance['revenue'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-2.5 bg-amber-50 rounded-xl border border-amber-100">
                        <span class="text-amber-700 font-semibold">Outstanding / Piutang:</span>
                        <span class="font-bold text-amber-900">Rp {{ number_format($finance['outstanding'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-2.5 bg-rose-50 rounded-xl border border-rose-100">
                        <span class="text-rose-700 font-semibold">Tagihan Melewati Jatuh Tempo:</span>
                        <span class="font-bold text-rose-900">Rp {{ number_format($finance['overdue'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Section 5: Ringkasan Honor Tutor Cabang -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <x-cressco.icon-helper name="credit-card" class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Ringkasan Honor Tutor Cabang</h3>
                            <p class="text-[11px] text-gray-500">Akumulasi kompensasi pengajar berbasis sesi mengajar aktual di cabang Anda.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-gray-400 block">Total Beban Honor</span>
                        <span class="text-base font-bold text-gray-900 mt-0.5 block">Rp {{ number_format($honor['total'], 0, ',', '.') }}</span>
                    </div>
                    <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100">
                        <span class="text-emerald-700 font-medium block">Honor Terbayar (Lunas)</span>
                        <span class="text-base font-bold text-emerald-900 mt-0.5 block">Rp {{ number_format($honor['paid'], 0, ',', '.') }}</span>
                    </div>
                    <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100">
                        <span class="text-blue-700 font-medium block">Honor Siap Bayar (Final)</span>
                        <span class="text-base font-bold text-blue-900 mt-0.5 block">Rp {{ number_format($honor['final'], 0, ',', '.') }}</span>
                    </div>
                    <div class="p-3 bg-amber-50/60 rounded-xl border border-amber-100">
                        <span class="text-amber-700 font-medium block">Honor Draft (Proses)</span>
                        <span class="text-base font-bold text-amber-900 mt-0.5 block">Rp {{ number_format($honor['draft'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Section 6: Branch Breakdown Table (if multiple branches) -->
            @if (count($branchComparison) > 1)
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-xs">
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-gray-900">Perbandingan Antar Cabang Akses Anda</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50/80 border-b border-gray-200/80 text-gray-500 font-semibold uppercase tracking-wider">
                                    <th class="py-3 px-4">Nama Cabang</th>
                                    <th class="py-3 px-4 text-center">Siswa Aktif</th>
                                    <th class="py-3 px-4 text-center">Kelas Aktif</th>
                                    <th class="py-3 px-4 text-center">Sesi Selesai</th>
                                    <th class="py-3 px-4 text-right">Kas Masuk (Lunas)</th>
                                    <th class="py-3 px-4 text-right">Outstanding</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($branchComparison as $bc)
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="py-3 px-4 font-bold text-gray-900">
                                            {{ $bc['name'] }}
                                            <span class="text-[11px] text-gray-400 font-normal block">{{ $bc['code'] }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-center">{{ number_format($bc['students']) }}</td>
                                        <td class="py-3 px-4 text-center">{{ number_format($bc['classes']) }}</td>
                                        <td class="py-3 px-4 text-center">{{ number_format($bc['sessions']) }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-emerald-700">Rp {{ number_format($bc['revenue'], 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-right font-semibold text-amber-700">Rp {{ number_format($bc['outstanding'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>

    </div>
</x-admin-layout>
