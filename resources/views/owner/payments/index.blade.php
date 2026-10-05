<x-owner-layout :tenant="$tenant" title="Monitoring Pembayaran & Tagihan">
    <x-slot:breadcrumbSub>Pembayaran</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Monitoring Pembayaran & Tagihan</h1>
                <p class="text-xs text-gray-500 mt-1">Pantau penerimaan kas, status invoice siswa, dan total piutang di seluruh cabang {{ $tenant->name ?? 'Prime Academy' }}.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                    <x-cressco.icon-helper name="shield" class="w-3.5 h-3.5 text-gray-500" />
                    Mode Pengawasan Keuangan
                </span>
            </div>
        </div>

        <!-- Summary KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Total Tagihan"
                icon="billing"
                iconColor="text-gray-500"
                value="{{ number_format($summary['totalInvoices']) }}"
                trend="Invoice"
                trendType="neutral"
                subtitle="Seluruh tagihan yang diterbitkan"
            />

            <x-cressco.project-card
                title="Penerimaan (Lunas)"
                icon="check-circle"
                iconColor="text-emerald-500"
                value="Rp {{ number_format($summary['totalRevenue'], 0, ',', '.') }}"
                trend="Lunas"
                trendType="positive"
                subtitle="Kas masuk terverifikasi"
            />

            <x-cressco.project-card
                title="Outstanding"
                icon="clock"
                iconColor="text-amber-500"
                value="Rp {{ number_format($summary['totalOutstanding'], 0, ',', '.') }}"
                trend="Pending"
                trendType="warning"
                subtitle="Belum lunas / menunggu bayar"
            />

            <x-cressco.project-card
                title="Tagihan Terlambat"
                icon="help-circle"
                iconColor="text-rose-500"
                value="Rp {{ number_format($summary['totalOverdue'], 0, ',', '.') }}"
                trend="Jatuh Tempo"
                trendType="error"
                subtitle="Invoice melewati batas waktu"
            />
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('owner.payments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div class="col-span-1 sm:col-span-2">
                    <input
                        type="text"
                        name="search"
                        placeholder="Cari siswa, orang tua, no telp, catatan..."
                        value="{{ $filters['search'] ?? '' }}"
                        class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition"
                    />
                </div>

                <div>
                    <select name="branch_id" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition">
                        <option value="">Semua Cabang</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" {{ ($filters['branch_id'] ?? '') === $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="status" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition">
                        <option value="">Semua Status</option>
                        <option value="belum_bayar" {{ ($filters['status'] ?? '') === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="menunggu_verifikasi" {{ ($filters['status'] ?? '') === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="lunas" {{ ($filters['status'] ?? '') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="terlambat" {{ ($filters['status'] ?? '') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <select name="period" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition">
                        <option value="">Semua Periode</option>
                        @foreach ($periods as $p)
                            <option value="{{ $p }}" {{ ($filters['period'] ?? '') === $p ? 'selected' : '' }}>
                                {{ $p }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="h-10 px-4 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold shrink-0 transition flex items-center justify-center">
                        <x-cressco.icon-helper name="filter" class="w-3.5 h-3.5" />
                    </button>

                    @if(!empty($filters['search']) || !empty($filters['branch_id']) || !empty($filters['status']) || !empty($filters['period']))
                        <a href="{{ route('owner.payments.index') }}" class="h-10 px-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold shrink-0 transition flex items-center justify-center" title="Reset Filter">
                            <x-cressco.icon-helper name="close" class="w-3.5 h-3.5" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/75 border-b border-gray-200/80 text-gray-500 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Invoice & Periode</th>
                            <th class="px-5 py-3.5">Siswa & Kontak</th>
                            <th class="px-5 py-3.5">Cabang & Kelas</th>
                            <th class="px-5 py-3.5">Nominal</th>
                            <th class="px-5 py-3.5">Jatuh Tempo</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse ($payments as $payment)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900">Periode: {{ $payment->period }}</div>
                                    <div class="text-[11px] text-gray-500 font-mono">#{{ substr($payment->id, 0, 8) }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-gray-900">{{ $payment->student->name ?? '-' }}</div>
                                    <div class="text-[11px] text-gray-500">
                                        Wali: {{ $payment->student->parent_name ?? '-' }}
                                        @if($payment->student->parent_phone)
                                            ({{ $payment->student->parent_phone }})
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">{{ $payment->branch->name ?? '-' }}</div>
                                    <div class="text-[11px] text-gray-500">
                                        {{ $payment->enrollment?->classModel?->name ?? 'Tagihan Umum' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-bold text-gray-900">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="{{ $payment->status === 'terlambat' || ($payment->status !== 'lunas' && $payment->due_date && $payment->due_date->isPast()) ? 'text-rose-600 font-semibold' : 'text-gray-600' }}">
                                        {{ $payment->due_date ? $payment->due_date->format('d/m/Y') : '-' }}
                                    </div>
                                    @if ($payment->paid_at)
                                        <div class="text-[10px] text-emerald-600">Lunas: {{ $payment->paid_at->format('d/m/Y H:i') }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if ($payment->status === 'lunas')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                            Lunas
                                        </span>
                                    @elseif ($payment->status === 'menunggu_verifikasi')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">
                                            Menunggu Verifikasi
                                        </span>
                                    @elseif ($payment->status === 'terlambat')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                            Belum Bayar
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('owner.payments.show', $payment) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                                        <x-cressco.icon-helper name="search" class="w-3.5 h-3.5" />
                                        <span>Detail Invoice</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-gray-500">
                                    <p class="font-medium">Belum ada data tagihan / pembayaran yang cocok.</p>
                                    <p class="text-[11px] text-gray-400 mt-1">Pencatatan dan pembuatan invoice baru dilakukan oleh Administrator Cabang.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payments->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>

    </div>
</x-owner-layout>
