<x-owner-layout :tenant="$tenant" title="Management Tutor">
    <x-slot:breadcrumbSub>Management Tutor</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             createModalOpen: false,
             editModalOpen: false,
             toggleModalOpen: false,
             editTutor: { id: '', name: '', email: '', phone: '', honor_scheme_id: '', status: 'active' },
             toggleTutor: { id: '', name: '', status: '' },
             openCreate() {
                 this.toggleModalOpen = false;
                 this.editModalOpen = false;
                 this.createModalOpen = true;
             },
             openEdit(tutor) {
                 this.toggleModalOpen = false;
                 this.createModalOpen = false;
                 const overrideAssignment = tutor.honor_assignments ? tutor.honor_assignments.find(a => a.assignment_type === 'tutor_override') : null;
                 const scheme = overrideAssignment ? overrideAssignment.honor_scheme : null;
                 this.editTutor = {
                     id: tutor.id,
                     name: tutor.name,
                     email: tutor.email,
                     phone: tutor.phone || '',
                     status: tutor.status || 'active',
                     honor_mode: overrideAssignment ? 'other' : 'default',
                     method: scheme ? scheme.method : 'per_session',
                     rate: scheme ? (scheme.rate || scheme.fixed_amount || 75000) : 75000,
                     effective_from: overrideAssignment && overrideAssignment.effective_from ? overrideAssignment.effective_from.substring(0, 10) : '{{ now()->startOfMonth()->toDateString() }}',
                     honor_scheme_id: overrideAssignment ? overrideAssignment.honor_scheme_id : ''
                 };
                 this.editModalOpen = true;
             },
             openToggle(tutor) {
                 this.createModalOpen = false;
                 this.editModalOpen = false;
                 this.toggleTutor = {
                     id: tutor.id,
                     name: tutor.name,
                     status: tutor.status
                 };
                 this.toggleModalOpen = true;
             },
             closeAll() {
                 this.createModalOpen = false;
                 this.editModalOpen = false;
                 this.toggleModalOpen = false;
             }
         }">

        <!-- Page Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Management Tutor</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola data pengajar, penugasan kelas, dan skema kompensasi honor tutor di {{ $tenant->name ?? 'Prime Academy' }}.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-cressco.button
                    as="a"
                    :href="route('owner.imports.create', 'tutors')"
                    variant="outline"
                    size="md"
                    leadingIcon="upload"
                >
                    Import Tutor
                </x-cressco.button>

                <x-cressco.button variant="primary" size="md" leadingIcon="plus" @click="openCreate()">
                    Tambah Tutor Baru
                </x-cressco.button>
            </div>
        </div>

        <!-- Summary Metric Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Total Tutor"
                icon="users"
                iconColor="text-gray-500"
                value="{{ $totalTutorsCount }}"
                trend="Pengajar"
                trendType="neutral"
                subtitle="Seluruh tutor & tentor terdaftar"
            />

            <x-cressco.project-card
                title="Tutor Aktif"
                icon="check"
                iconColor="text-emerald-500"
                value="{{ $activeTutorsCount }}"
                trend="Aktif Mengajar"
                trendType="positive"
                subtitle="Tutor dengan status aktif"
            />

            <x-cressco.project-card
                title="Penugasan Kelas"
                icon="book-open"
                iconColor="text-terracotta-500"
                value="{{ $totalClassAssignmentsCount }}"
                trend="Penugasan"
                trendType="terracotta"
                subtitle="Alokasi kelas aktif semester ini"
            />

            <x-cressco.project-card
                title="Sesi Terlaksana"
                icon="calendar"
                iconColor="text-blue-500"
                value="{{ $totalCompletedSessionsCount }}"
                trend="Selesai"
                trendType="neutral"
                subtitle="Total sesi belajar yang telah tuntas"
            />
        </div>

        <!-- Filter & Search Controls -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('owner.tutors.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                
                <!-- Search Input -->
                <div class="sm:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <x-cressco.icon-helper name="search" class="w-4 h-4" />
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Cari nama tutor, email, no whatsapp..."
                           class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-white placeholder-gray-400 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                </div>

                <!-- Branch Filter -->
                <div class="sm:col-span-3">
                    <select name="branch_id"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white text-gray-700 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                        <option value="all" {{ ($branchId ?? 'all') === 'all' ? 'selected' : '' }}>Semua Cabang</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" {{ ($branchId ?? '') === $b->id ? 'selected' : '' }}>
                                Cabang {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="sm:col-span-2">
                    <select name="status"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white text-gray-700 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                        <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <!-- Actions / Reset -->
                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 px-3 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-medium text-xs transition">
                        Cari
                    </button>
                    @if ($search || ($branchId && $branchId !== 'all') || ($status && $status !== 'all'))
                        <a href="{{ route('owner.tutors.index') }}" class="px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium text-xs transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tutors Table -->
        <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200/70 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3.5 px-5">Tutor & Kontak</th>
                            <th class="py-3.5 px-4">Kelas Diampu</th>
                            <th class="py-3.5 px-4">Pengaturan Honor</th>
                            <th class="py-3.5 px-4 text-center">Sesi Selesai</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                        @forelse ($tutors as $tutor)
                            @php
                                $overrideAssignment = $tutor->honorAssignments->where('assignment_type', 'tutor_override')->first();
                                $activeScheme = $overrideAssignment?->honorScheme ?? $defaultScheme;
                                $activeAssignments = $tutor->tutorAssignments->where('status', 'active');
                                $completedSessionsCount = $tutor->actualTeachingSessions->count();
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition">
                                
                                <!-- Tutor & Kontak (Combined into 1 clear, uncluttered block) -->
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-terracotta-50 text-terracotta-700 flex items-center justify-center font-bold text-xs shrink-0 border border-terracotta-200/60 shadow-2xs">
                                            {{ strtoupper(substr($tutor->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('owner.tutors.show', $tutor) }}" class="font-bold text-gray-900 hover:text-terracotta-600 transition block text-xs sm:text-sm">
                                                {{ $tutor->name }}
                                            </a>
                                            <div class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-1.5">
                                                <span>{{ $tutor->email }}</span>
                                                @if ($tutor->phone)
                                                    <span>•</span>
                                                    <span>{{ $tutor->phone }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kelas & Cabang Diampu (High-Level Summary) -->
                                <td class="py-4 px-4">
                                    @php
                                        $assignedBranches = $activeAssignments->map(fn($a) => $a->class?->branch?->name)->filter()->unique();
                                        $classesCount = $activeAssignments->count();
                                        $branchesCount = $assignedBranches->count();
                                    @endphp
                                    @if ($classesCount > 0)
                                        <div>
                                            <div class="font-bold text-gray-900 text-xs">{{ $classesCount }} Kelas Aktif</div>
                                            <div class="text-[11px] text-gray-400 mt-0.5">
                                                @if ($branchesCount === 1)
                                                    Cabang {{ $assignedBranches->first() }}
                                                @elseif ($branchesCount > 1)
                                                    {{ $branchesCount }} Cabang ({{ $assignedBranches->take(2)->join(', ') }})
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic">Belum ada kelas</span>
                                    @endif
                                </td>

                                <!-- Skema Kompensasi Honor -->
                                <td class="py-4 px-4">
                                    @if ($activeScheme)
                                        <div>
                                            <div class="text-xs font-bold text-gray-900">
                                                @if ($activeScheme->method === 'per_session')
                                                    Rp {{ number_format($activeScheme->rate, 0, ',', '.') }} <span class="text-[11px] text-gray-400 font-normal">/ sesi</span>
                                                @elseif ($activeScheme->method === 'per_student')
                                                    Rp {{ number_format($activeScheme->rate, 0, ',', '.') }} <span class="text-[11px] text-gray-400 font-normal">/ siswa</span>
                                                @elseif ($activeScheme->method === 'revenue_share')
                                                    {{ $activeScheme->percentage }}% <span class="text-[11px] text-gray-400 font-normal">bagi hasil</span>
                                                @elseif ($activeScheme->method === 'fixed_monthly')
                                                    Rp {{ number_format($activeScheme->fixed_amount, 0, ',', '.') }} <span class="text-[11px] text-gray-400 font-normal">/ bulan</span>
                                                @endif
                                            </div>
                                            <div class="mt-0.5">
                                                @if ($overrideAssignment)
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Pengaturan Lain</span>
                                                @else
                                                    <span class="text-[11px] text-gray-500 font-medium">Default Bimbel</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic">Belum diatur</span>
                                    @endif
                                </td>

                                <!-- Sesi Mengajar Selesai -->
                                <td class="py-4 px-4 text-center">
                                    <span class="text-sm font-extrabold text-gray-900">{{ $completedSessionsCount }}</span>
                                    <span class="text-[11px] text-gray-400 block font-normal">Sesi</span>
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-4">
                                    <x-cressco.badge :variant="$tutor->status === 'active' ? 'success' : 'gray'" dot>
                                        {{ $tutor->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </x-cressco.badge>
                                </td>

                                <!-- Aksi -->
                                <td class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('owner.tutors.show', $tutor) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-gray-200/90 bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs transition shadow-2xs">
                                            <span>Detail</span>
                                            <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5 text-gray-400" />
                                        </a>

                                        <button type="button"
                                                @click="openEdit({{ $tutor->toJson() }})"
                                                class="p-1.5 rounded-xl border border-transparent hover:border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition"
                                                title="Edit Profil">
                                            <x-cressco.icon-helper name="edit" class="w-4 h-4" />
                                        </button>

                                        <button type="button"
                                                @click="openToggle({{ $tutor->toJson() }})"
                                                class="p-1.5 rounded-xl border border-transparent hover:border-amber-200 text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition"
                                                title="{{ $tutor->status === 'active' ? 'Nonaktifkan Tutor' : 'Aktifkan Tutor' }}">
                                            <x-cressco.icon-helper name="{{ $tutor->status === 'active' ? 'close' : 'check' }}" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-3 text-gray-400">
                                        <x-cressco.icon-helper name="users" class="w-6 h-6" />
                                    </div>
                                    <p class="font-medium text-gray-600 text-sm">Belum ada data tutor yang ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-1">Silakan sesuaikan filter pencarian atau tambahkan tutor baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: TAMBAH TUTOR BARU                   -->
        <!-- ========================================== -->
        <div x-show="createModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="createModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="createModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl max-w-xl w-full p-6 border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Tambah Tutor Baru</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Daftarkan pengajar baru di {{ $tenant->name ?? 'Prime Academy' }}.</p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.tutors.store') }}" class="space-y-4 mt-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nama Lengkap & Gelar <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               required
                               placeholder="Contoh: Sarah Wijaya, S.Pd."
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email"
                                   name="email"
                                   required
                                   placeholder="tutor.sarah@cressco.test"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">No WhatsApp / Telepon</label>
                            <input type="text"
                                   name="phone"
                                   placeholder="08123456789"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Password Awal (Opsional)</label>
                            <input type="password"
                                   name="password"
                                   placeholder="Default: Password123!"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                                <option value="active" selected>Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pengaturan Honor Terintegrasi -->
                    <div class="pt-3 border-t border-gray-100" x-data="{
                        createHonorMode: 'default',
                        createMethod: 'per_session',
                        createRate: 75000,
                        createEffectiveFrom: '{{ now()->startOfMonth()->toDateString() }}'
                    }">
                        <label class="block font-bold text-gray-700 mb-2">Pengaturan Honor Tutor</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <label class="relative flex flex-col p-3.5 rounded-2xl border-2 cursor-pointer transition"
                                   :class="createHonorMode === 'default' ? 'border-terracotta-500 bg-terracotta-50/40 text-terracotta-900' : 'border-gray-200 bg-white hover:bg-gray-50/70 text-gray-700'">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-xs">Default Bimbel</span>
                                    <input type="radio" name="honor_mode" value="default" x-model="createHonorMode" class="text-terracotta-600 focus:ring-terracotta-500">
                                </div>
                                <p class="text-[11px] text-gray-500 leading-relaxed">
                                    Mengikuti honor standar bimbel 
                                    @if ($defaultScheme)
                                        ({{ $defaultScheme->method === 'per_session' ? 'Per Sesi Mengajar' : ($defaultScheme->method === 'per_student' ? 'Per Siswa' : ($defaultScheme->method === 'fixed_monthly' ? 'Bulanan Tetap' : ucfirst($defaultScheme->method))) }} - Rp {{ number_format($defaultScheme->rate ?: $defaultScheme->fixed_amount, 0, ',', '.') }}).
                                    @else
                                        yang ditentukan di Pengaturan Bimbel.
                                    @endif
                                </p>
                            </label>

                            <label class="relative flex flex-col p-3.5 rounded-2xl border-2 cursor-pointer transition"
                                   :class="createHonorMode === 'other' ? 'border-terracotta-500 bg-terracotta-50/40 text-terracotta-900' : 'border-gray-200 bg-white hover:bg-gray-50/70 text-gray-700'">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-xs">Pengaturan Lain</span>
                                    <input type="radio" name="honor_mode" value="other" x-model="createHonorMode" class="text-terracotta-600 focus:ring-terracotta-500">
                                </div>
                                <p class="text-[11px] text-gray-500 leading-relaxed">
                                    Tentukan tarif honor dan tanggal berlaku tersendiri untuk tutor ini.
                                </p>
                            </label>
                        </div>

                        <!-- Form Pengaturan Lain -->
                        <div x-show="createHonorMode === 'other'" x-transition class="space-y-3 bg-gray-50/70 p-3.5 rounded-2xl border border-gray-100 mb-3">
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Metode Honor <span class="text-red-500">*</span></label>
                                <select name="method" x-model="createMethod" class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-white focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                                    <option value="per_session">Per Sesi Mengajar</option>
                                    <option value="per_student" disabled class="text-gray-400 bg-gray-100">Per Siswa (Coming Soon)</option>
                                    <option value="fixed_monthly" disabled class="text-gray-400 bg-gray-100">Bulanan Tetap (Coming Soon)</option>
                                    <option value="revenue_share" disabled class="text-gray-400 bg-gray-100">Bagi Hasil / Revenue Share (Coming Soon)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Tarif Honor Per Sesi <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                                        <input type="number"
                                               name="rate"
                                               x-model="createRate"
                                               min="0"
                                               step="1000"
                                               placeholder="75000"
                                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 bg-white focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Mulai Berlaku <span class="text-red-500">*</span></label>
                                    <input type="date"
                                           name="effective_from"
                                           x-model="createEffectiveFrom"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-white focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Penugasan Kelas Awal (Opsional)</label>
                        <select name="class_id" class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="">Belum Ditugaskan ke Kelas (Bisa diatur nanti)</option>
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->name }} (Cabang {{ $cls->branch?->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Simpan Data Tutor
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: EDIT TUTOR                          -->
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
                 class="relative bg-white rounded-2xl shadow-xl max-w-xl w-full p-6 border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Edit Profil Tutor</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="editTutor.name"></p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" :action="'{{ url('/owner/tutors') }}/' + editTutor.id" class="space-y-4 mt-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nama Lengkap & Gelar <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               x-model="editTutor.name"
                               required
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email"
                                   name="email"
                                   x-model="editTutor.email"
                                   required
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">No WhatsApp / Telepon</label>
                            <input type="text"
                                   name="phone"
                                   x-model="editTutor.phone"
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
                            <label class="block font-bold text-gray-700 mb-1">Status</label>
                            <select name="status" x-model="editTutor.status" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pengaturan Honor Terintegrasi di Edit Modal -->
                    <div class="pt-3 border-t border-gray-100">
                        <label class="block font-bold text-gray-700 mb-2">Pengaturan Honor Tutor</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <label class="relative flex flex-col p-3.5 rounded-2xl border-2 cursor-pointer transition"
                                   :class="editTutor.honor_mode === 'default' ? 'border-terracotta-500 bg-terracotta-50/40 text-terracotta-900' : 'border-gray-200 bg-white hover:bg-gray-50/70 text-gray-700'">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-xs">Default Bimbel</span>
                                    <input type="radio" name="honor_mode" value="default" x-model="editTutor.honor_mode" class="text-terracotta-600 focus:ring-terracotta-500">
                                </div>
                                <p class="text-[11px] text-gray-500 leading-relaxed">
                                    Mengikuti honor standar bimbel 
                                    @if ($defaultScheme)
                                        ({{ $defaultScheme->method === 'per_session' ? 'Per Sesi Mengajar' : ($defaultScheme->method === 'per_student' ? 'Per Siswa' : ($defaultScheme->method === 'fixed_monthly' ? 'Bulanan Tetap' : ucfirst($defaultScheme->method))) }} - Rp {{ number_format($defaultScheme->rate ?: $defaultScheme->fixed_amount, 0, ',', '.') }}).
                                    @else
                                        yang ditentukan di Pengaturan Bimbel.
                                    @endif
                                </p>
                            </label>

                            <label class="relative flex flex-col p-3.5 rounded-2xl border-2 cursor-pointer transition"
                                   :class="editTutor.honor_mode === 'other' ? 'border-terracotta-500 bg-terracotta-50/40 text-terracotta-900' : 'border-gray-200 bg-white hover:bg-gray-50/70 text-gray-700'">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-xs">Pengaturan Lain</span>
                                    <input type="radio" name="honor_mode" value="other" x-model="editTutor.honor_mode" class="text-terracotta-600 focus:ring-terracotta-500">
                                </div>
                                <p class="text-[11px] text-gray-500 leading-relaxed">
                                    Tentukan tarif honor dan tanggal berlaku tersendiri untuk tutor ini.
                                </p>
                            </label>
                        </div>

                        <!-- Form Pengaturan Lain -->
                        <div x-show="editTutor.honor_mode === 'other'" x-transition class="space-y-3 bg-gray-50/70 p-3.5 rounded-2xl border border-gray-100 mb-3">
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Metode Honor <span class="text-red-500">*</span></label>
                                <select name="method" x-model="editTutor.method" class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-white focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                                    <option value="per_session">Per Sesi Mengajar</option>
                                    <option value="per_student" disabled class="text-gray-400 bg-gray-100">Per Siswa (Coming Soon)</option>
                                    <option value="fixed_monthly" disabled class="text-gray-400 bg-gray-100">Bulanan Tetap (Coming Soon)</option>
                                    <option value="revenue_share" disabled class="text-gray-400 bg-gray-100">Bagi Hasil / Revenue Share (Coming Soon)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Tarif Honor Per Sesi <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                                        <input type="number"
                                               name="rate"
                                               x-model="editTutor.rate"
                                               min="0"
                                               step="1000"
                                               placeholder="75000"
                                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 bg-white focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Mulai Berlaku <span class="text-red-500">*</span></label>
                                    <input type="date"
                                           name="effective_from"
                                           x-model="editTutor.effective_from"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-white focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Perbarui Data Tutor
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
                 class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-gray-100 z-10 text-center">
                
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 border border-amber-100">
                    <x-cressco.icon-helper name="help" class="w-6 h-6" />
                </div>

                <h3 class="text-base font-bold text-gray-900">
                    <span x-text="toggleTutor.status === 'active' ? 'Nonaktifkan Tutor?' : 'Aktifkan Tutor?'"></span>
                </h3>

                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    Apakah Anda yakin ingin mengubah status pengajar <strong class="text-gray-800" x-text="toggleTutor.name"></strong>?
                </p>

                <form method="POST" :action="'{{ url('/owner/tutors') }}/' + toggleTutor.id + '/toggle-status'" class="mt-6 flex items-center justify-center gap-3">
                    @csrf
                    @method('PATCH')

                    <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                        Batal
                    </button>

                    <button type="submit"
                            :class="toggleTutor.status === 'active' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                            class="px-4 py-2 rounded-xl text-white text-xs font-bold transition shadow-xs">
                        <span x-text="toggleTutor.status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"></span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-owner-layout>
