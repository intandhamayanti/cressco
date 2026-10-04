<x-owner-layout :tenant="$tenant" title="Honor Tutor">
    <x-slot:breadcrumbSub>Honor Tutor</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             activeTab: 'calculations',
             createSchemeModalOpen: false,
             editSchemeModalOpen: false,
             editScheme: { id: '', name: '', method: 'per_session', rate: '', percentage: '', fixed_amount: '', effective_from: '', effective_until: '', status: 'active', is_default: false },
             openCreateScheme() {
                 this.editSchemeModalOpen = false;
                 this.createSchemeModalOpen = true;
             },
             openEditScheme(scheme, isDefault) {
                 this.createSchemeModalOpen = false;
                 this.editScheme = {
                     id: scheme.id,
                     name: scheme.name,
                     method: scheme.method,
                     rate: scheme.rate || '',
                     percentage: scheme.percentage || '',
                     fixed_amount: scheme.fixed_amount || '',
                     effective_from: scheme.effective_from ? scheme.effective_from.substring(0, 10) : '',
                     effective_until: scheme.effective_until ? scheme.effective_until.substring(0, 10) : '',
                     status: scheme.status || 'active',
                     is_default: isDefault
                 };
                 this.editSchemeModalOpen = true;
             },
             closeAll() {
                 this.createSchemeModalOpen = false;
                 this.editSchemeModalOpen = false;
             }
         }">

        <!-- Page Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Honor Tutor & Kompensasi</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Pengawasan rekapitulasi honor pengajar berbasis sesi aktual dan konfigurasi skema kompensasi di {{ $tenant->name ?? 'Prime Academy' }}.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-cressco.button variant="primary" size="md" @click="openCreateScheme()">
                    <x-cressco.icon-helper name="plus" class="w-4 h-4 mr-1.5" />
                    <span>Buat Skema Honor</span>
                </x-cressco.button>
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
                subtitle="Konfigurasi tarif aktif"
            />
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-gray-200/80 pb-px">
            <button type="button"
                    @click="activeTab = 'calculations'"
                    :class="activeTab === 'calculations' ? 'border-terracotta-600 text-terracotta-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'"
                    class="px-4 py-2.5 text-xs border-b-2 transition">
                Rekapitulasi Honor Tutor
            </button>
            <button type="button"
                    @click="activeTab = 'schemes'"
                    :class="activeTab === 'schemes' ? 'border-terracotta-600 text-terracotta-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'"
                    class="px-4 py-2.5 text-xs border-b-2 transition">
                Konfigurasi Skema Honor (Policy)
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: REKAPITULASI HONOR TUTOR            -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'calculations'" class="space-y-4">
            
            <!-- Filters & Search -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
                <form method="GET" action="{{ route('owner.honors.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                    
                    <!-- Search Input -->
                    <div class="sm:col-span-4 relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <x-cressco.icon-helper name="search" class="w-4 h-4" />
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Cari nama tutor..."
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
                    <div class="sm:col-span-3">
                        <select name="status"
                                onchange="this.form.submit()"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white text-gray-700 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="draft" {{ ($status ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="final" {{ ($status ?? '') === 'final' ? 'selected' : '' }}>Final (Menunggu Bayar)</option>
                            <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>Lunas (Sudah Dibayar)</option>
                        </select>
                    </div>

                    <!-- Actions / Reset -->
                    <div class="sm:col-span-2 flex items-center gap-2">
                        <button type="submit" class="flex-1 px-3 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-medium text-xs transition">
                            Cari
                        </button>
                        @if ($search || ($branchId && $branchId !== 'all') || ($status && $status !== 'all') || $period)
                            <a href="{{ route('owner.honors.index') }}" class="px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium text-xs transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Calculations Table -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-200/70 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3 px-4">Tutor Pengajar</th>
                                <th class="py-3 px-4">Cabang & Periode</th>
                                <th class="py-3 px-4">Skema Honor</th>
                                <th class="py-3 px-4">Honor Pokok</th>
                                <th class="py-3 px-4">Penyesuaian</th>
                                <th class="py-3 px-4">Total Akhir</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                            @forelse ($calculations as $calc)
                                <tr class="hover:bg-gray-50/70 transition">
                                    
                                    <!-- Tutor Pengajar -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-xs shrink-0 border border-terracotta-200/60">
                                                {{ strtoupper(substr($calc->tutor?->name ?? 'T', 0, 2)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('owner.honors.show', $calc) }}" class="font-bold text-gray-900 hover:text-terracotta-600 transition block">
                                                    {{ $calc->tutor?->name ?? 'Tutor' }}
                                                </a>
                                                <span class="text-[11px] text-gray-400">{{ $calc->tutor?->email ?? '' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Cabang & Periode -->
                                    <td class="py-3.5 px-4">
                                        <div>
                                            <span class="font-bold text-gray-900">
                                                {{ $calc->period_start ? $calc->period_start->translatedFormat('d M') : '' }} - {{ $calc->period_end ? $calc->period_end->translatedFormat('d M Y') : '' }}
                                            </span>
                                            <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1">
                                                <x-cressco.icon-helper name="building" class="w-3 h-3 text-gray-400" />
                                                <span>{{ $calc->branch?->name ?? 'Semua Cabang (Konsolidasi)' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Skema Honor -->
                                    <td class="py-3.5 px-4">
                                        <div>
                                            <span class="font-semibold text-gray-900">{{ $calc->honorScheme?->name ?? '-' }}</span>
                                            <span class="text-[10px] text-gray-400 block uppercase">{{ str_replace('_', ' ', $calc->method) }}</span>
                                        </div>
                                    </td>

                                    <!-- Honor Pokok -->
                                    <td class="py-3.5 px-4">
                                        <span class="font-medium text-gray-800">Rp {{ number_format($calc->base_amount, 0, ',', '.') }}</span>
                                    </td>

                                    <!-- Penyesuaian -->
                                    <td class="py-3.5 px-4">
                                        @if ($calc->adjustment_amount != 0)
                                            <div>
                                                <span class="{{ $calc->adjustment_amount > 0 ? 'text-emerald-600 font-bold' : 'text-red-600 font-bold' }}">
                                                    {{ $calc->adjustment_amount > 0 ? '+' : '' }}Rp {{ number_format($calc->adjustment_amount, 0, ',', '.') }}
                                                </span>
                                                @if ($calc->adjustment_reason)
                                                    <span class="text-[10px] text-gray-400 block truncate max-w-[120px]" title="{{ $calc->adjustment_reason }}">
                                                        {{ $calc->adjustment_reason }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Total Akhir -->
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-gray-900 text-sm">Rp {{ number_format($calc->final_amount, 0, ',', '.') }}</span>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3.5 px-4">
                                        <x-cressco.badge :variant="$calc->status === 'paid' ? 'success' : ($calc->status === 'final' ? 'blue' : 'gray')" dot>
                                            @if ($calc->status === 'paid')
                                                Lunas
                                            @elseif ($calc->status === 'final')
                                                Final (Siap Bayar)
                                            @else
                                                Draft
                                            @endif
                                        </x-cressco.badge>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('owner.honors.show', $calc) }}"
                                               class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition"
                                               title="Lihat Rincian Sesi">
                                                <x-cressco.icon-helper name="chevron-right" class="w-4 h-4" />
                                            </a>

                                            @if ($calc->status === 'final')
                                                <form method="POST" action="{{ route('owner.honors.mark-paid', $calc) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition shadow-2xs"
                                                            title="Tandai Sudah Dibayar">
                                                        Bayar
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-gray-400">
                                        <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-3 text-gray-400">
                                            <x-cressco.icon-helper name="credit-card" class="w-6 h-6" />
                                        </div>
                                        <p class="font-medium text-gray-600 text-sm">Belum ada data perhitungan honor</p>
                                        <p class="text-xs text-gray-400 mt-1">Perhitungan honor dihitung berdasarkan sesi mengajar aktual yang telah selesai.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- TAB 2: KONFIGURASI SKEMA HONOR (POLICY)    -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'schemes'" class="space-y-4">
            
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Daftar Skema Kompensasi Honor</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Aturan tarif dan metode perhitungan honor pengajar di seluruh cabang.</p>
                    </div>
                    <x-cressco.button variant="secondary" size="sm" @click="openCreateScheme()">
                        <x-cressco.icon-helper name="plus" class="w-3.5 h-3.5 mr-1" />
                        <span>Tambah Skema Baru</span>
                    </x-cressco.button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-200/70 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3 px-4">Nama Skema</th>
                                <th class="py-3 px-4">Metode Penggajian</th>
                                <th class="py-3 px-4">Besaran Tarif</th>
                                <th class="py-3 px-4">Periode Berlaku</th>
                                <th class="py-3 px-4">Tipe Skema</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                            @forelse ($schemes as $scheme)
                                @php
                                    $isDefault = $defaultSchemeId === $scheme->id;
                                    $overrideCount = $scheme->assignments->where('assignment_type', 'tutor_override')->count();
                                @endphp
                                <tr class="hover:bg-gray-50/70 transition">
                                    
                                    <!-- Nama Skema -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-terracotta-50 text-terracotta-700 flex items-center justify-center font-bold text-xs shrink-0 border border-terracotta-100">
                                                {{ strtoupper(substr($scheme->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-900 block">{{ $scheme->name }}</span>
                                                <span class="text-[10px] text-gray-400">Dibuat oleh {{ $scheme->createdBy?->name ?? 'Owner' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Metode Penggajian -->
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-800 uppercase border border-gray-200">
                                            {{ str_replace('_', ' ', $scheme->method) }}
                                        </span>
                                    </td>

                                    <!-- Besaran Tarif -->
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-terracotta-600 text-sm">
                                            @if ($scheme->method === 'per_session')
                                                Rp {{ number_format($scheme->rate, 0, ',', '.') }} / sesi
                                            @elseif ($scheme->method === 'per_student')
                                                Rp {{ number_format($scheme->rate, 0, ',', '.') }} / siswa
                                            @elseif ($scheme->method === 'revenue_share')
                                                {{ $scheme->percentage }}% bagi hasil
                                            @elseif ($scheme->method === 'fixed_monthly')
                                                Rp {{ number_format($scheme->fixed_amount, 0, ',', '.') }} / bulan
                                            @endif
                                        </span>
                                    </td>

                                    <!-- Periode Berlaku -->
                                    <td class="py-3.5 px-4">
                                        <span>{{ $scheme->effective_from ? $scheme->effective_from->translatedFormat('d M Y') : '-' }}</span>
                                        <span class="text-gray-400 block text-[11px]">{{ $scheme->effective_until ? 's/d ' . $scheme->effective_until->translatedFormat('d M Y') : '(Seterusnya)' }}</span>
                                    </td>

                                    <!-- Tipe Skema -->
                                    <td class="py-3.5 px-4">
                                        @if ($isDefault)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Default Tenant
                                            </span>
                                        @elseif ($overrideCount > 0)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                {{ $overrideCount }} Tutor Override
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-[11px]">Opsional</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3.5 px-4">
                                        <x-cressco.badge :variant="$scheme->status === 'active' ? 'success' : 'gray'" dot>
                                            {{ $scheme->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                        </x-cressco.badge>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if (! $isDefault && $scheme->status === 'active')
                                                <form method="POST" action="{{ route('owner.honors.schemes.set-default', $scheme) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="px-2 py-1 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-medium text-[11px] transition"
                                                            title="Jadikan Skema Default Tenant">
                                                        Jadikan Default
                                                    </button>
                                                </form>
                                            @endif

                                            <button type="button"
                                                    @click="openEditScheme({{ $scheme->toJson() }}, {{ $isDefault ? 'true' : 'false' }})"
                                                    class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition"
                                                    title="Edit Skema">
                                                <x-cressco.icon-helper name="edit" class="w-4 h-4" />
                                            </button>

                                            <form method="POST" action="{{ route('owner.honors.schemes.toggle-status', $scheme) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-gray-500 hover:text-amber-600 hover:bg-amber-50 transition"
                                                        title="{{ $scheme->status === 'active' ? 'Nonaktifkan Skema' : 'Aktifkan Skema' }}">
                                                    <x-cressco.icon-helper name="{{ $scheme->status === 'active' ? 'close' : 'check' }}" class="w-4 h-4" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">
                                        Belum ada skema honor yang dibuat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL: CREATE SKEMA HONOR                  -->
        <!-- ========================================== -->
        <div x-show="createSchemeModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="createSchemeModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="createSchemeModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl max-w-xl w-full p-6 border border-gray-100 z-10 max-h-[90vh] overflow-y-auto"
                 x-data="{ selectedMethod: 'per_session' }">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Buat Skema Honor Baru</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Tentukan aturan tarif kompensasi honor pengajar.</p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.honors.schemes.store') }}" class="space-y-4 mt-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nama Skema Honor <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               required
                               placeholder="Contoh: Skema Honor Senior Per Sesi"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Metode Penggajian <span class="text-red-500">*</span></label>
                        <select name="method" x-model="selectedMethod" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="per_session">Per Sesi Mengajar (Fixed Rate / Session)</option>
                            <option value="per_student">Per Siswa Aktif (Headcount / Student)</option>
                            <option value="revenue_share">Bagi Hasil Pendapatan (% Revenue Share)</option>
                            <option value="fixed_monthly">Gaji Tetap Bulanan (Fixed Monthly)</option>
                        </select>
                    </div>

                    <!-- Input Dinamis Berdasarkan Metode -->
                    <div x-show="selectedMethod === 'per_session' || selectedMethod === 'per_student'">
                        <label class="block font-bold text-gray-700 mb-1">
                            <span x-text="selectedMethod === 'per_session' ? 'Tarif per Sesi (Rp)' : 'Tarif per Siswa (Rp)'"></span>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="rate"
                               min="0"
                               placeholder="Contoh: 150000"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div x-show="selectedMethod === 'revenue_share'">
                        <label class="block font-bold text-gray-700 mb-1">Persentase Bagi Hasil (%) <span class="text-red-500">*</span></label>
                        <input type="number"
                               name="percentage"
                               min="0"
                               max="100"
                               step="0.1"
                               placeholder="Contoh: 60 (Artinya 60% dari SPP siswa yang lunas)"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div x-show="selectedMethod === 'fixed_monthly'">
                        <label class="block font-bold text-gray-700 mb-1">Nominal Gaji Tetap Bulanan (Rp) <span class="text-red-500">*</span></label>
                        <input type="number"
                               name="fixed_amount"
                               min="0"
                               placeholder="Contoh: 3500000"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Mulai Berlaku <span class="text-red-500">*</span></label>
                            <input type="date"
                                   name="effective_from"
                                   required
                                   value="{{ now()->startOfMonth()->toDateString() }}"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Berakhir (Opsional)</label>
                            <input type="date"
                                   name="effective_until"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox"
                               name="is_default"
                               value="1"
                               id="is_default_check"
                               class="rounded border-gray-300 text-terracotta-600 focus:ring-terracotta-500" />
                        <label for="is_default_check" class="font-bold text-gray-800 cursor-pointer">
                            Jadikan skema ini sebagai Skema Default untuk seluruh tutor di tenant
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Simpan Skema
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: EDIT SKEMA HONOR                    -->
        <!-- ========================================== -->
        <div x-show="editSchemeModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="editSchemeModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="editSchemeModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl max-w-xl w-full p-6 border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Edit Skema Honor</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="editScheme.name"></p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" :action="'{{ url('/owner/honors/schemes') }}/' + editScheme.id" class="space-y-4 mt-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nama Skema Honor <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               x-model="editScheme.name"
                               required
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Metode Penggajian <span class="text-red-500">*</span></label>
                        <select name="method" x-model="editScheme.method" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="per_session">Per Sesi Mengajar (Fixed Rate / Session)</option>
                            <option value="per_student">Per Siswa Aktif (Headcount / Student)</option>
                            <option value="revenue_share">Bagi Hasil Pendapatan (% Revenue Share)</option>
                            <option value="fixed_monthly">Gaji Tetap Bulanan (Fixed Monthly)</option>
                        </select>
                    </div>

                    <!-- Input Dinamis -->
                    <div x-show="editScheme.method === 'per_session' || editScheme.method === 'per_student'">
                        <label class="block font-bold text-gray-700 mb-1">
                            <span x-text="editScheme.method === 'per_session' ? 'Tarif per Sesi (Rp)' : 'Tarif per Siswa (Rp)'"></span>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="rate"
                               x-model="editScheme.rate"
                               min="0"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div x-show="editScheme.method === 'revenue_share'">
                        <label class="block font-bold text-gray-700 mb-1">Persentase Bagi Hasil (%) <span class="text-red-500">*</span></label>
                        <input type="number"
                               name="percentage"
                               x-model="editScheme.percentage"
                               min="0"
                               max="100"
                               step="0.1"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div x-show="editScheme.method === 'fixed_monthly'">
                        <label class="block font-bold text-gray-700 mb-1">Nominal Gaji Tetap Bulanan (Rp) <span class="text-red-500">*</span></label>
                        <input type="number"
                               name="fixed_amount"
                               x-model="editScheme.fixed_amount"
                               min="0"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Mulai Berlaku <span class="text-red-500">*</span></label>
                            <input type="date"
                                   name="effective_from"
                                   x-model="editScheme.effective_from"
                                   required
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Berakhir (Opsional)</label>
                            <input type="date"
                                   name="effective_until"
                                   x-model="editScheme.effective_until"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox"
                               name="is_default"
                               value="1"
                               x-model="editScheme.is_default"
                               id="edit_is_default_check"
                               class="rounded border-gray-300 text-terracotta-600 focus:ring-terracotta-500" />
                        <label for="edit_is_default_check" class="font-bold text-gray-800 cursor-pointer">
                            Jadikan skema ini sebagai Skema Default untuk seluruh tutor di tenant
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Perbarui Skema
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-owner-layout>
