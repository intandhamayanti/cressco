<x-admin-layout :tenant="$tenant" title="Manajemen Pembayaran & Tagihan">
    <x-slot:breadcrumbSub>Pembayaran</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto" x-data="{ createModalOpen: false, reminderModalOpen: false, activeReminder: { name: '', parent: '', phone: '', amount: '', period: '', dueDate: '', message: '', waLink: '' } }">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Manajemen Tagihan & Pembayaran</h1>
                <p class="text-xs text-gray-500 mt-1">Kelola invoice siswa, catat penerimaan kas, verifikasi pembayaran, dan kirim reminder orang tua di cabang Anda.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <x-cressco.button variant="primary" size="md" @click="createModalOpen = true">
                    <x-cressco.icon-helper name="plus" class="w-4 h-4 mr-1.5" />
                    <span>Buat Tagihan Baru</span>
                </x-cressco.button>
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
                subtitle="Tagihan dalam cabang akses Anda"
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
            <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
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
                        <option value="">Semua Cabang Anda</option>
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
                    
                    <button type="submit" class="h-10 px-4 bg-terracotta-600 hover:bg-terracotta-700 text-white rounded-xl text-xs font-semibold shrink-0 transition">
                        Filter
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('admin.payments.index') }}" class="h-10 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold flex items-center justify-center shrink-0 transition" title="Reset Filter">
                            <x-cressco.icon-helper name="refresh-cw" class="w-3.5 h-3.5" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200/80 text-gray-500 font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">Invoice / Periode</th>
                            <th class="py-3 px-4">Siswa & Kontak</th>
                            <th class="py-3 px-4">Cabang</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                            <th class="py-3 px-4">Jatuh Tempo</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Aksi & Reminder</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($payments as $payment)
                            @php
                                $student = $payment->student;
                                $parentPhone = $student?->parent_phone ?: $student?->phone;
                                $cleanPhone = '';
                                if ($parentPhone) {
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $parentPhone);
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $cleanPhone = '62'.substr($cleanPhone, 1);
                                    }
                                }
                                $tenantName = $payment->tenant?->name ?? 'Cressco';
                                $branchName = $payment->branch?->name ?? 'Cabang';
                                $formattedAmount = 'Rp '.number_format($payment->amount, 0, ',', '.');
                                $formattedDueDate = $payment->due_date ? $payment->due_date->format('d/m/Y') : '-';
                                $rawMsg = "Halo Bapak/Ibu " . ($student->parent_name ?? '') . ",\n\nKami dari {$tenantName} ({$branchName}) menginformasikan tagihan bimbingan belajar untuk ananda " . ($student->name ?? '') . ":\n- Periode: {$payment->period}\n- Nominal: {$formattedAmount}\n- Batas Waktu: {$formattedDueDate}\n- Status: " . ucwords(str_replace('_', ' ', $payment->status)) . "\n\nPembayaran dapat ditransfer atau diserahkan langsung ke petugas cabang. Jika sudah melakukan pembayaran, mohon abaikan pesan ini.\n\nTerima kasih atas perhatian dan kerja samanya.";
                                $waUrl = $cleanPhone ? 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($rawMsg) : '';
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-3 px-4">
                                    <a href="{{ route('admin.payments.show', $payment) }}" class="font-bold text-gray-900 hover:text-terracotta-600 transition block">
                                        #{{ substr($payment->id, 0, 8) }}
                                    </a>
                                    <span class="text-[11px] text-gray-400 block">{{ $payment->period }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-gray-900">{{ $payment->student->name ?? '-' }}</div>
                                    <div class="text-[11px] text-gray-400">
                                        Ortu: {{ $payment->student->parent_name ?? '-' }}
                                        @if ($payment->student?->parent_phone)
                                            ({{ $payment->student->parent_phone }})
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-600">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-700">
                                        {{ $payment->branch->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-gray-900">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="{{ $payment->status !== 'lunas' && $payment->due_date && $payment->due_date->isPast() ? 'text-rose-600 font-bold' : 'text-gray-600' }}">
                                        {{ $payment->due_date ? $payment->due_date->format('d/m/Y') : '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
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
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="p-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-600 transition" title="Lihat Detail">
                                            <x-cressco.icon-helper name="eye" class="w-4 h-4" />
                                        </a>

                                        @if ($payment->status !== 'lunas')
                                            <button type="button"
                                                @click="activeReminder = {
                                                    name: '{{ addslashes($student->name ?? '') }}',
                                                    parent: '{{ addslashes($student->parent_name ?? '') }}',
                                                    phone: '{{ $parentPhone ?? '' }}',
                                                    amount: '{{ $formattedAmount }}',
                                                    period: '{{ $payment->period }}',
                                                    dueDate: '{{ $formattedDueDate }}',
                                                    message: `{{ addslashes($rawMsg) }}`,
                                                    waLink: '{{ $waUrl }}'
                                                }; reminderModalOpen = true;"
                                                class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 transition"
                                                title="Reminder WhatsApp Orang Tua">
                                                <x-cressco.icon-helper name="message-square" class="w-4 h-4" />
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-cressco.icon-helper name="billing" class="w-10 h-10 text-gray-300 mb-2" />
                                        <p class="font-medium text-gray-500">Tidak ada data tagihan/pembayaran yang sesuai filter.</p>
                                        <p class="text-[11px] text-gray-400 mt-1">Buat tagihan baru atau ubah kriteria filter di atas.</p>
                                    </div>
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

        <!-- Reminder Modal -->
        <div x-show="reminderModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
             x-transition>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 space-y-4"
                 @click.outside="reminderModalOpen = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <x-cressco.icon-helper name="message-square" class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Reminder Tagihan Orang Tua</h3>
                            <p class="text-[11px] text-gray-500">Kirim pesan reminder manual via WhatsApp</p>
                        </div>
                    </div>
                    <button type="button" @click="reminderModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <div class="bg-gray-50 rounded-xl p-3 border border-gray-200/80 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Siswa:</span>
                        <span class="font-bold text-gray-900" x-text="activeReminder.name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Orang Tua:</span>
                        <span class="font-bold text-gray-900" x-text="activeReminder.parent + ' (' + activeReminder.phone + ')'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Nominal:</span>
                        <span class="font-bold text-emerald-700" x-text="activeReminder.amount"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Teks Template Pesan Reminder:</label>
                    <textarea readonly rows="7" x-text="activeReminder.message" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 font-mono leading-relaxed focus:outline-hidden resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button"
                            @click="navigator.clipboard.writeText(activeReminder.message); alert('Teks reminder berhasil disalin ke clipboard!');"
                            class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold flex items-center gap-1.5 transition shadow-2xs">
                        <x-cressco.icon-helper name="copy" class="w-4 h-4 text-gray-500" />
                        <span>Salin Teks</span>
                    </button>

                    <template x-if="activeReminder.waLink">
                        <a :href="activeReminder.waLink" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-2xs">
                            <x-cressco.icon-helper name="message-square" class="w-4 h-4" />
                            <span>Buka WhatsApp</span>
                        </a>
                    </template>
                </div>
            </div>
        </div>

        <!-- Create Payment Modal -->
        <div x-show="createModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
             x-transition>
            <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-100 space-y-4 max-h-[90vh] overflow-y-auto"
                 @click.outside="createModalOpen = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Buat Tagihan Pembayaran Baru</h3>
                    <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.payments.store') }}" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pilih Cabang <span class="text-rose-500">*</span></label>
                        <select name="branch_id" required class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pilih Siswa <span class="text-rose-500">*</span></label>
                        <select name="student_id" required class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach ($students as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->branch->name ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pilih Kelas / Enrollment (Opsional)</label>
                        <select name="enrollment_id" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                            <option value="">-- Tidak Terkait Kelas Spesifik --</option>
                            @foreach ($enrollments as $e)
                                <option value="{{ $e->id }}">
                                    {{ $e->student->name ?? 'Siswa' }} - {{ $e->classModel->name ?? 'Kelas' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Periode Tagihan <span class="text-rose-500">*</span></label>
                            <input type="text" name="period" value="{{ now()->translatedFormat('F Y') }}" placeholder="Contoh: Oktober 2026" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Nominal Tagihan (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="amount" min="0" step="1000" placeholder="Contoh: 500000" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Batas Jatuh Tempo <span class="text-rose-500">*</span></label>
                            <input type="date" name="due_date" value="{{ now()->addDays(7)->format('Y-m-d') }}" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Status Pembayaran <span class="text-rose-500">*</span></label>
                            <select name="status" required class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                                <option value="belum_bayar">Belum Bayar</option>
                                <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                                <option value="lunas">Lunas (Langsung Terima Kas)</option>
                                <option value="terlambat">Terlambat</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Catatan / Keterangan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan opsional mengenai pembayaran..." class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <x-cressco.button variant="secondary" type="button" @click="createModalOpen = false">
                            Batal
                        </x-cressco.button>
                        <x-cressco.button variant="primary" type="submit">
                            Simpan Tagihan
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
