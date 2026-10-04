<x-owner-layout :tenant="$tenant" :title="$tutor->name">
    <x-slot:breadcrumbSub>
        <a href="{{ route('owner.tutors.index') }}" class="hover:text-terracotta-600 transition">Management Tutor</a>
        <span class="mx-1 text-gray-300">/</span>
        <span class="text-gray-900 font-bold">{{ $tutor->name }}</span>
    </x-slot:breadcrumbSub>

    @php
        $activeAssignments = $tutor->tutorAssignments->where('status', 'active');
        $completedSessions = $tutor->actualTeachingSessions->where('status', 'completed');
        $totalHonorPaid = $tutor->tutorHonorCalculations->where('status', 'paid')->sum('final_amount');
        $daysMap = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
    @endphp

    <div class="space-y-8 max-w-7xl mx-auto"
         x-data="{
             editModalOpen: false,
             toggleModalOpen: false,
             assignClassModalOpen: false,
             honorSchemeModalOpen: false,
             closeAll() {
                 this.editModalOpen = false;
                 this.toggleModalOpen = false;
                 this.assignClassModalOpen = false;
                 this.honorSchemeModalOpen = false;
             }
         }">
        
        <!-- Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-base shadow-2xs border border-terracotta-200/60">
                    {{ strtoupper(substr($tutor->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $tutor->name }}</h1>
                        <x-cressco.badge :variant="$tutor->status === 'active' ? 'success' : 'gray'" dot>
                            {{ $tutor->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </x-cressco.badge>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Email: <span class="font-semibold text-gray-800">{{ $tutor->email }}</span>
                        @if ($tutor->phone)
                            • WhatsApp: <span class="font-semibold text-gray-800">{{ $tutor->phone }}</span>
                        @endif
                        • Bergabung sejak {{ $tutor->created_at ? $tutor->created_at->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('owner.tutors.index') }}" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition shadow-2xs">
                    ← Kembali
                </a>

                <x-cressco.button variant="secondary" size="md" @click="editModalOpen = true">
                    <x-cressco.icon-helper name="edit" class="w-4 h-4 mr-1.5" />
                    <span>Edit Profil</span>
                </x-cressco.button>

                <x-cressco.button variant="{{ $tutor->status === 'active' ? 'secondary' : 'primary' }}" size="md" @click="toggleModalOpen = true">
                    @if ($tutor->status === 'active')
                        <span class="text-amber-700">Nonaktifkan Tutor</span>
                    @else
                        <span>Aktifkan Tutor</span>
                    @endif
                </x-cressco.button>
            </div>
        </div>

        <!-- 1. STATISTIK KPI CARDS (Balanced 4-Card Overview Row) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Kelas Diampu"
                icon="book-open"
                iconColor="text-terracotta-500"
                value="{{ $activeAssignments->count() }}"
                trend="Kelas"
                trendType="neutral"
                subtitle="Alokasi kelas aktif semester ini"
            />

            <x-cressco.project-card
                title="Sesi Terlaksana"
                icon="calendar"
                iconColor="text-blue-500"
                value="{{ $completedSessions->count() }}"
                trend="Sesi"
                trendType="positive"
                subtitle="Total sesi aktual yang telah diajar"
            />

            <x-cressco.project-card
                title="Skema Kompensasi"
                icon="billing"
                iconColor="text-amber-500"
                value="{{ $activeScheme ? (strlen($activeScheme->name) > 16 ? substr($activeScheme->name, 0, 14).'...' : $activeScheme->name) : 'Belum diatur' }}"
                trend="{{ $tutorOverrideAssignment ? 'Override' : 'Default' }}"
                :trendType="$tutorOverrideAssignment ? 'warning' : 'neutral'"
                subtitle="{{ $activeScheme ? ucfirst(str_replace('_', ' ', $activeScheme->method)) : 'Standar' }}"
            />

            <x-cressco.project-card
                title="Total Honor Dibayar"
                icon="trend-up"
                iconColor="text-emerald-500"
                value="Rp {{ number_format($totalHonorPaid, 0, ',', '.') }}"
                trend="Lunas"
                trendType="positive"
                subtitle="Akumulasi pembayaran honor"
            />
        </div>

        <!-- 2. ROW 1: PROFIL PENGAJAR (KIRI) vs SKEMA HONOR (KANAN) - BALANCED EQUAL-HEIGHT GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            
            <!-- Card Kiri: Profil & Kontak Pengajar -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-6 h-full">
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Profil Pengajar</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Informasi identitas dan data kontak pengajar</p>
                        </div>
                        <x-cressco.badge :variant="$tutor->status === 'active' ? 'success' : 'gray'" dot>
                            {{ $tutor->status === 'active' ? 'Akun Aktif' : 'Nonaktif' }}
                        </x-cressco.badge>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-3.5 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-1">
                            <span class="text-gray-400 text-[11px] block font-medium">Nama Lengkap & Gelar</span>
                            <p class="font-bold text-gray-900 text-sm">{{ $tutor->name }}</p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-1">
                            <span class="text-gray-400 text-[11px] block font-medium">Alamat Email</span>
                            <p class="font-semibold text-gray-800 truncate" title="{{ $tutor->email }}">{{ $tutor->email }}</p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-1">
                            <span class="text-gray-400 text-[11px] block font-medium">No Telepon / WhatsApp</span>
                            <p class="font-semibold text-gray-800">{{ $tutor->phone ?: 'Belum diatur' }}</p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-gray-50/80 border border-gray-100 space-y-1">
                            <span class="text-gray-400 text-[11px] block font-medium">Tanggal Bergabung</span>
                            <p class="font-semibold text-gray-800">{{ $tutor->created_at ? $tutor->created_at->translatedFormat('d M Y') : '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[11px] text-gray-400">ID Pengajar: #{{ $tutor->id }}</span>
                    <button type="button" @click="editModalOpen = true" class="inline-flex items-center gap-1.5 text-xs font-bold text-terracotta-600 hover:text-terracotta-700 transition">
                        <x-cressco.icon-helper name="edit" class="w-3.5 h-3.5" />
                        <span>Edit Data Profil</span>
                    </button>
                </div>
            </div>

            <!-- Card Kanan: Skema Kompensasi & Honor -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-6 h-full">
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Skema Honor Aktif</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Basis kompensasi dan perhitungan gaji tutor</p>
                        </div>
                        @if ($tutorOverrideAssignment)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Override Khusus
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                Default Tenant
                            </span>
                        @endif
                    </div>

                    @if ($activeScheme)
                        <div class="space-y-3.5 text-xs">
                            <!-- Main Rate Box -->
                            <div class="p-4 rounded-2xl bg-terracotta-50/70 border border-terracotta-200/70 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] font-semibold text-terracotta-800 uppercase tracking-wider block">
                                        {{ $activeScheme->name }}
                                    </span>
                                    <div class="text-xl sm:text-2xl font-black text-terracotta-700 mt-0.5">
                                        @if ($activeScheme->method === 'per_session')
                                            Rp {{ number_format($activeScheme->rate, 0, ',', '.') }} <span class="text-xs font-normal text-terracotta-900">/ sesi</span>
                                        @elseif ($activeScheme->method === 'per_student')
                                            Rp {{ number_format($activeScheme->rate, 0, ',', '.') }} <span class="text-xs font-normal text-terracotta-900">/ siswa</span>
                                        @elseif ($activeScheme->method === 'revenue_share')
                                            {{ $activeScheme->percentage }}% <span class="text-xs font-normal text-terracotta-900">bagi hasil</span>
                                        @elseif ($activeScheme->method === 'fixed_monthly')
                                            Rp {{ number_format($activeScheme->fixed_amount, 0, ',', '.') }} <span class="text-xs font-normal text-terracotta-900">/ bulan</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-terracotta-500 text-white flex items-center justify-center shrink-0 shadow-2xs">
                                    <x-cressco.icon-helper name="billing" class="w-5 h-5" />
                                </div>
                            </div>

                            <!-- Parameter Rows -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 rounded-xl bg-gray-50/80 border border-gray-100">
                                    <span class="text-gray-400 text-[11px] block">Metode Perhitungan</span>
                                    <strong class="text-gray-900 text-xs uppercase">{{ str_replace('_', ' ', $activeScheme->method) }}</strong>
                                </div>

                                <div class="p-3 rounded-xl bg-gray-50/80 border border-gray-100">
                                    <span class="text-gray-400 text-[11px] block">Masa Berlaku</span>
                                    <strong class="text-gray-900 text-xs">
                                        {{ $activeScheme->effective_from ? $activeScheme->effective_from->translatedFormat('d M Y') : 'Aktif Permanen' }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="py-8 text-center text-gray-400">
                            <p class="text-xs">Belum ada skema honor yang aktif untuk tutor ini.</p>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[11px] text-gray-400">Perubahan berlaku pada periode kalkulasi berjalan</span>
                    <button type="button" @click="honorSchemeModalOpen = true" class="inline-flex items-center gap-1.5 text-xs font-bold text-terracotta-600 hover:text-terracotta-700 transition">
                        <x-cressco.icon-helper name="billing" class="w-3.5 h-3.5" />
                        <span>Sesuaikan Skema Honor</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- 3. ROW 2: PENUGASAN KELAS (KIRI) vs RIWAYAT SESI MENGAJAR AKTUAL (KANAN) - BALANCED EQUAL-HEIGHT GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            
            <!-- Card Kiri: Penugasan Kelas -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-5 h-full">
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Penugasan Kelas</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Daftar kelas yang resmi diampu oleh {{ $tutor->name }}</p>
                        </div>
                        <x-cressco.button variant="primary" size="sm" @click="assignClassModalOpen = true">
                            <x-cressco.icon-helper name="plus" class="w-3.5 h-3.5 mr-1" />
                            <span>Tugaskan ke Kelas</span>
                        </x-cressco.button>
                    </div>

                    <div class="divide-y divide-gray-100 text-xs">
                        @forelse ($tutor->tutorAssignments as $assignment)
                            <div class="py-3.5 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-terracotta-50 text-terracotta-700 flex items-center justify-center font-bold text-xs shrink-0 border border-terracotta-200/60 shadow-2xs">
                                        {{ strtoupper(substr($assignment->class?->name ?? 'K', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            @if ($assignment->class)
                                                <a href="{{ route('owner.classes.show', $assignment->class) }}" class="font-bold text-gray-900 hover:text-terracotta-600 transition text-sm">
                                                    {{ $assignment->class->name }}
                                                </a>
                                            @else
                                                <span class="font-bold text-gray-900 text-sm">Kelas</span>
                                            @endif
                                            <x-cressco.badge :variant="$assignment->status === 'active' ? 'success' : 'gray'" size="xs" dot>
                                                {{ $assignment->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                            </x-cressco.badge>
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">
                                            Cabang: <span class="font-semibold text-gray-700">{{ $assignment->class?->branch?->name ?? '-' }}</span>
                                            @if ($assignment->class?->subject)
                                                • Mapel: {{ $assignment->class->subject }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <form method="POST" action="{{ route('owner.tutors.classes.toggle-status', [$tutor, $assignment]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-[11px] font-semibold text-gray-700 transition shadow-2xs">
                                            {{ $assignment->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400">
                                <x-cressco.icon-helper name="book-open" class="w-6 h-6 mx-auto mb-2 text-gray-300" />
                                <p class="text-xs font-semibold text-gray-600">Belum ada penugasan kelas</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Klik tombol "+ Tugaskan ke Kelas" untuk menambahkan kelas.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 text-[11px] text-gray-400 flex items-center justify-between">
                    <span>Total {{ $activeAssignments->count() }} kelas aktif</span>
                    <span>Semester Aktif</span>
                </div>
            </div>

            <!-- Card Kanan: Riwayat Sesi Mengajar Aktual -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-5 h-full">
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Riwayat Sesi Mengajar Aktual</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Sesi mengajar yang benar-benar diajar oleh tutor ini</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $completedSessions->count() }} Selesai
                        </span>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        @forelse ($tutor->actualTeachingSessions->take(3) as $session)
                            @php
                                $isReplacement = $session->scheduled_tutor_id !== $session->actual_tutor_id;
                            @endphp
                            <div class="p-3.5 rounded-2xl border border-gray-200/70 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold shrink-0 border border-blue-100 shadow-2xs">
                                        <x-cressco.icon-helper name="calendar" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900">{{ $session->class?->name ?? 'Sesi Belajar' }}</span>
                                            <x-cressco.badge :variant="$session->status === 'completed' ? 'success' : ($session->status === 'cancelled' ? 'danger' : 'gray')" size="xs">
                                                {{ ucfirst($session->status) }}
                                            </x-cressco.badge>
                                            @if ($isReplacement)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Pengganti</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">
                                            {{ $session->session_date ? $session->session_date->translatedFormat('D, d M Y') : '-' }} • {{ substr($session->start_time, 0, 5) }}-{{ substr($session->end_time, 0, 5) }} WIB • {{ $session->branch?->name ?? '-' }}
                                        </div>
                                        @if ($session->material)
                                            <div class="text-[11px] text-gray-700 mt-1 font-medium bg-white px-2 py-0.5 rounded-md border border-gray-200 inline-block">
                                                Materi: {{ $session->material }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-[10px] text-gray-400 block">Ruangan</span>
                                    <span class="font-bold text-gray-800 text-xs">{{ $session->room ?: '-' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400">
                                <p class="text-xs">Belum ada riwayat sesi mengajar aktual yang tercatat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 text-[11px] text-gray-400 flex items-center justify-between">
                    <span>Sesi aktual menjadi basis rekapitulasi honor tutor</span>
                    <span>Tervalidasi Sistem</span>
                </div>
            </div>

        </div>

        <!-- 4. ROW 3: RIWAYAT PERHITUNGAN & PEMBAYARAN HONOR (FULL-WIDTH BALANCED CARD) -->
        <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Riwayat Perhitungan & Pembayaran Honor</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Rekapitulasi slip honor per periode untuk {{ $tutor->name }}</p>
                </div>
            </div>

            <div class="divide-y divide-gray-100 text-xs">
                @forelse ($tutor->tutorHonorCalculations as $calc)
                    <div class="py-3.5 flex items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900 text-sm">
                                    Periode {{ $calc->period_start ? $calc->period_start->translatedFormat('F Y') : '-' }}
                                </span>
                                <x-cressco.badge :variant="$calc->status === 'paid' ? 'success' : ($calc->status === 'final' ? 'blue' : 'gray')" size="xs" dot>
                                    {{ ucfirst($calc->status) }}
                                </x-cressco.badge>
                            </div>
                            <div class="text-[11px] text-gray-400">
                                Skema: <span class="text-gray-600 font-semibold">{{ $calc->honorScheme?->name ?? '-' }}</span> ({{ str_replace('_', ' ', $calc->method) }}) • Cabang: {{ $calc->branch?->name ?? 'Semua Cabang' }}
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <span class="text-base font-extrabold text-gray-900">Rp {{ number_format($calc->final_amount, 0, ',', '.') }}</span>
                                @if ($calc->adjustment_amount != 0)
                                    <span class="text-[11px] text-gray-400 block">Adj: Rp {{ number_format($calc->adjustment_amount, 0, ',', '.') }}</span>
                                @endif
                            </div>

                            <a href="{{ route('owner.honors.show', $calc) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold text-gray-700 transition shadow-2xs">
                                <span>Detail Slip</span>
                                <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5 text-gray-400" />
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-gray-400">
                        <p class="text-xs">Belum ada riwayat perhitungan honor untuk tutor ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: EDIT PROFIL TUTOR                   -->
        <!-- ========================================== -->
        <div x-show="editModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="editModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="editModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-3xl shadow-xl max-w-xl w-full p-6 border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Edit Profil Tutor</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $tutor->name }}</p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.tutors.update', $tutor) }}" class="space-y-4 mt-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nama Lengkap & Gelar <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               value="{{ $tutor->name }}"
                               required
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email"
                                   name="email"
                                   value="{{ $tutor->email }}"
                                   required
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">No WhatsApp / Telepon</label>
                            <input type="text"
                                   name="phone"
                                   value="{{ $tutor->phone }}"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                            <input type="password"
                                   name="password"
                                   placeholder="Minimal 8 karakter"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Status Akun</label>
                            <select name="status" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                                <option value="active" {{ $tutor->status === 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ $tutor->status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Skema Honor Khusus</label>
                        <select name="honor_scheme_id" class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="">Gunakan Skema Default Tenant ({{ $defaultScheme?->name ?? 'Reguler' }})</option>
                            @foreach ($honorSchemes as $scheme)
                                <option value="{{ $scheme->id }}" {{ ($tutorOverrideAssignment?->honor_scheme_id === $scheme->id) ? 'selected' : '' }}>
                                    {{ $scheme->name }} ({{ ucfirst(str_replace('_', ' ', $scheme->method)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Simpan Perubahan
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: TUGASKAN KE KELAS BARU              -->
        <!-- ========================================== -->
        <div x-show="assignClassModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="assignClassModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="assignClassModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-3xl shadow-xl max-w-md w-full p-6 border border-gray-100 z-10">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Tugaskan ke Kelas</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $tutor->name }}</p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.tutors.assign-class', $tutor) }}" class="space-y-4 mt-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Pilih Kelas Bimbel <span class="text-red-500">*</span></label>
                        <select name="class_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="">Pilih Kelas...</option>
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->name }} (Cabang {{ $cls->branch?->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date"
                                   name="started_at"
                                   value="{{ now()->toDateString() }}"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Selesai (Opsional)</label>
                            <input type="date"
                                   name="ended_at"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Status Penugasan</label>
                        <select name="status" class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="active" selected>Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Tugaskan Tutor
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: SESUAIKAN SKEMA HONOR               -->
        <!-- ========================================== -->
        <div x-show="honorSchemeModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="honorSchemeModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="honorSchemeModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-3xl shadow-xl max-w-md w-full p-6 border border-gray-100 z-10">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Sesuaikan Skema Honor Tutor</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $tutor->name }}</p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.tutors.assign-honor-scheme', $tutor) }}" class="space-y-4 mt-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Pilih Skema Honor Khusus</label>
                        <select name="honor_scheme_id" class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="">Gunakan Skema Default Tenant (Hapus Override Khusus)</option>
                            @foreach ($honorSchemes as $scheme)
                                <option value="{{ $scheme->id }}" {{ ($tutorOverrideAssignment?->honor_scheme_id === $scheme->id) ? 'selected' : '' }}>
                                    {{ $scheme->name }} ({{ ucfirst(str_replace('_', ' ', $scheme->method)) }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Pilih "Gunakan Skema Default" jika tutor ini akan mengikuti skema kompensasi standar bimbel.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Mulai Berlaku <span class="text-red-500">*</span></label>
                            <input type="date"
                                   name="effective_from"
                                   required
                                   value="{{ $tutorOverrideAssignment?->effective_from ? $tutorOverrideAssignment->effective_from->format('Y-m-d') : now()->startOfMonth()->toDateString() }}"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Berakhir (Opsional)</label>
                            <input type="date"
                                   name="effective_until"
                                   value="{{ $tutorOverrideAssignment?->effective_until ? $tutorOverrideAssignment->effective_until->format('Y-m-d') : '' }}"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Terapkan Skema Honor
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: TOGGLE STATUS TUTOR                 -->
        <!-- ========================================== -->
        <div x-show="toggleModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="toggleModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="toggleModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-3xl shadow-xl max-w-md w-full p-6 border border-gray-100 z-10 text-center">
                
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 border border-amber-100">
                    <x-cressco.icon-helper name="help" class="w-6 h-6" />
                </div>

                <h3 class="text-base font-bold text-gray-900">
                    {{ $tutor->status === 'active' ? 'Nonaktifkan Tutor?' : 'Aktifkan Tutor?' }}
                </h3>

                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    Apakah Anda yakin ingin mengubah status pengajar <strong class="text-gray-800">{{ $tutor->name }}</strong> menjadi <span class="font-bold">{{ $tutor->status === 'active' ? 'Nonaktif' : 'Aktif' }}</span>?
                </p>

                <form method="POST" action="{{ route('owner.tutors.toggle-status', $tutor) }}" class="mt-6 flex items-center justify-center gap-3">
                    @csrf
                    @method('PATCH')

                    <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                        Batal
                    </button>

                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-white text-xs font-bold transition shadow-xs {{ $tutor->status === 'active' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                        {{ $tutor->status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-owner-layout>
