<x-admin-layout :tenant="$tenant" title="Manajemen Pembayaran & Tagihan">
    <x-slot:breadcrumbSub>Pembayaran</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto" x-data="{
        reminderModalOpen: false,
        copied: false,
        activeReminder: {
            id: '',
            name: '',
            className: '',
            parent: '',
            phone: '',
            amount: '',
            period: '',
            dueDate: '',
            statusLabel: '',
            statusBadgeClass: '',
            message: '',
            waLink: ''
        },
        openReminder(data) {
            this.activeReminder = data;
            this.copied = false;
            this.reminderModalOpen = true;
        }
    }">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Manajemen Tagihan & Pembayaran</h1>
                <p class="text-xs text-gray-500 mt-1">Pantau status pembayaran siswa, periksa tagihan jatuh tempo, dan kirim pengingat WhatsApp kepada orang tua.</p>
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
                subtitle="Belum lunas / menunggu verifikasi"
            />

            <x-cressco.project-card
                title="Tagihan Terlambat"
                icon="alert-triangle"
                iconColor="text-rose-500"
                value="Rp {{ number_format($summary['totalOverdue'], 0, ',', '.') }}"
                trend="Jatuh Tempo"
                trendType="error"
                subtitle="Invoice melewati batas waktu"
            />
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <!-- Search Input -->
                <div class="lg:col-span-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <x-cressco.icon-helper name="search" class="w-4 h-4" />
                        </div>
                        <input
                            type="text"
                            name="search"
                            placeholder="Cari siswa, orang tua, no telepon..."
                            value="{{ $filters['search'] ?? '' }}"
                            class="w-full h-10 pl-9 pr-3.5 rounded-xl border border-gray-200 bg-white text-xs text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition shadow-2xs"
                        />
                    </div>
                </div>

                <!-- Class Filter -->
                <div class="lg:col-span-3">
                    <select name="class_id" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs text-gray-700 focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition shadow-2xs">
                        <option value="">Semua Kelas</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" {{ ($filters['class_id'] ?? '') === $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="lg:col-span-2">
                    <select name="status" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs text-gray-700 focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition shadow-2xs">
                        <option value="">Semua Status</option>
                        <option value="belum_bayar" {{ ($filters['status'] ?? '') === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="menunggu_verifikasi" {{ ($filters['status'] ?? '') === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="lunas" {{ ($filters['status'] ?? '') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="terlambat" {{ ($filters['status'] ?? '') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    </select>
                </div>

                <!-- Period Filter & Action Buttons -->
                <div class="lg:col-span-3 flex items-center gap-2">
                    <select name="period" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs text-gray-700 focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition shadow-2xs">
                        <option value="">Semua Periode</option>
                        @foreach ($periods as $p)
                            <option value="{{ $p }}" {{ ($filters['period'] ?? '') === $p ? 'selected' : '' }}>
                                {{ $p }}
                            </option>
                        @endforeach
                    </select>
                    
                    <button type="submit" class="h-10 px-4 bg-terracotta-600 hover:bg-terracotta-700 text-white rounded-xl text-xs font-semibold shrink-0 transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                        <x-cressco.icon-helper name="filter" class="w-3.5 h-3.5" />
                        <span>Filter</span>
                    </button>

                    @if (array_filter($filters))
                        <a href="{{ route('admin.payments.index') }}" class="h-10 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold flex items-center justify-center shrink-0 transition" title="Reset Filter">
                            <x-cressco.icon-helper name="refresh" class="w-3.5 h-3.5" />
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
                        <tr class="bg-gray-50/80 border-b border-gray-200/80 text-gray-500 font-bold text-[11px] uppercase tracking-wider">
                            <th class="py-3.5 px-4 sm:px-6">Siswa</th>
                            <th class="py-3.5 px-4">Kelas</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Nominal</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Jatuh Tempo</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Status Pembayaran</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Reminder</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Aksi</th>
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

                                // Class representation
                                $assignedClassName = $payment->enrollment?->classModel?->name;
                                if (! $assignedClassName && $student) {
                                    $activeClasses = $student->enrollments->where('status', 'active')->map(fn($e) => $e->classModel?->name)->filter()->unique();
                                    if ($activeClasses->isNotEmpty()) {
                                        $assignedClassName = $activeClasses->join(', ');
                                    } else {
                                        $assignedClassName = $student->enrollments->first()?->classModel?->name;
                                    }
                                }
                                $displayClassName = $assignedClassName ?: 'Umum / Semua Kelas';

                                $tenantName = $payment->tenant?->name ?? 'Cressco Bimbel';
                                $branchName = $payment->branch?->name ?? 'Cabang';
                                $formattedAmount = 'Rp ' . number_format($payment->amount, 0, ',', '.');
                                $formattedDueDate = $payment->due_date ? $payment->due_date->translatedFormat('d M Y') : '-';
                                $isOverdue = $payment->status === 'terlambat' || ($payment->status === 'belum_bayar' && $payment->due_date && $payment->due_date->isPast());
                                
                                // Auto-generated Reminder message based on status
                                $parentSalutation = $student?->parent_name ?: 'Orang Tua Siswa';
                                $studentName = $student?->name ?? 'Siswa';

                                if ($payment->status === 'lunas') {
                                    $statusLabel = 'LUNAS';
                                    $statusBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200/70';
                                    $rawMsg = "Halo Bapak/Ibu {$parentSalutation},\n\nTerima kasih, pembayaran bimbingan belajar di {$tenantName} ({$branchName}) untuk ananda {$studentName}:\n- Periode: {$payment->period}\n- Nominal: {$formattedAmount}\n- Status: LUNAS\n\nTelah kami terima dan diverifikasi. Terima kasih atas kerja samanya.";
                                } elseif ($isOverdue) {
                                    $statusLabel = 'TERLAMBAT';
                                    $statusBadgeClass = 'bg-rose-50 text-rose-700 border-rose-200/70';
                                    $rawMsg = "Halo Bapak/Ibu {$parentSalutation},\n\nKami dari {$tenantName} ({$branchName}) menginformasikan bahwa tagihan bimbingan belajar untuk ananda {$studentName}:\n- Periode: {$payment->period}\n- Nominal: {$formattedAmount}\n- Batas Waktu (Jatuh Tempo): {$formattedDueDate}\n- Status: TERLAMBAT\n\nTagihan ini telah melewati batas waktu jatuh tempo. Mohon untuk segera menyelesaikan pembayaran melalui transfer atau langsung ke cabang. Jika sudah melakukan transfer, mohon konfirmasi dengan mengirimkan bukti pembayaran.\n\nTerima kasih atas perhatian dan kerja samanya.";
                                } elseif ($payment->status === 'menunggu_verifikasi') {
                                    $statusLabel = 'MENUNGGU VERIFIKASI';
                                    $statusBadgeClass = 'bg-amber-50 text-amber-700 border-amber-200/70';
                                    $rawMsg = "Halo Bapak/Ibu {$parentSalutation},\n\nKami dari {$tenantName} ({$branchName}) telah mencatat pengajuan pembayaran bimbingan belajar untuk ananda {$studentName}:\n- Periode: {$payment->period}\n- Nominal: {$formattedAmount}\n- Status: MENUNGGU VERIFIKASI\n\nPembayaran Anda sedang dalam proses verifikasi oleh tim kasir/admin kami. Kami akan menginformasikan kembali setelah diverifikasi.\n\nTerima kasih.";
                                } else {
                                    $statusLabel = 'BELUM BAYAR';
                                    $statusBadgeClass = 'bg-slate-100 text-slate-700 border-slate-200/70';
                                    $rawMsg = "Halo Bapak/Ibu {$parentSalutation},\n\nKami dari {$tenantName} ({$branchName}) menginformasikan tagihan bimbingan belajar untuk ananda {$studentName}:\n- Periode: {$payment->period}\n- Nominal: {$formattedAmount}\n- Batas Waktu: {$formattedDueDate}\n- Status: Belum Bayar\n\nPembayaran dapat ditransfer atau diserahkan langsung ke petugas cabang. Jika sudah melakukan pembayaran, mohon abaikan pesan ini.\n\nTerima kasih atas perhatian dan kerja samanya.";
                                }

                                $waUrl = $cleanPhone ? 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($rawMsg) : '';
                            @endphp
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <!-- Siswa (Hanya Nama Siswa & Avatar) -->
                                <td class="py-3.5 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gray-50 text-gray-700 flex items-center justify-center font-bold text-xs shrink-0 border border-gray-200/60">
                                            {{ strtoupper(substr($student->name ?? 'S', 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.payments.show', $payment) }}" class="font-bold text-gray-900 hover:text-terracotta-600 transition block leading-snug">
                                                {{ $student->name ?? '-' }}
                                            </a>
                                            <span class="text-[10px] font-mono text-gray-400">#{{ substr($payment->id, 0, 8) }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kelas -->
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80 max-w-[190px] truncate" title="{{ $displayClassName }}">
                                        <span class="truncate">{{ $displayClassName }}</span>
                                    </span>
                                </td>

                                <!-- Nominal -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <span class="font-bold text-gray-900 text-xs tracking-tight">
                                        {{ $formattedAmount }}
                                    </span>
                                </td>

                                <!-- Jatuh Tempo -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($payment->status !== 'lunas' && $isOverdue)
                                        <div class="font-semibold text-rose-600">{{ $formattedDueDate }}</div>
                                    @else
                                        <div class="text-gray-700 font-medium">{{ $formattedDueDate }}</div>
                                    @endif
                                </td>

                                <!-- Status Pembayaran -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($payment->status === 'lunas')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Lunas
                                        </span>
                                    @elseif ($payment->status === 'menunggu_verifikasi')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/70">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Menunggu Verifikasi
                                        </span>
                                    @elseif ($payment->status === 'terlambat' || $isOverdue)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/70">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200/70">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Belum Bayar
                                        </span>
                                    @endif
                                </td>

                                <!-- Reminder (Ada di Semua Status) -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <button type="button"
                                        @click="openReminder({
                                            id: '{{ $payment->id }}',
                                            name: '{{ addslashes($student->name ?? '') }}',
                                            className: '{{ addslashes($displayClassName) }}',
                                            parent: '{{ addslashes($student->parent_name ?? '') }}',
                                            phone: '{{ $parentPhone ?? '' }}',
                                            amount: '{{ $formattedAmount }}',
                                            period: '{{ $payment->period }}',
                                            dueDate: '{{ $formattedDueDate }}',
                                            statusLabel: '{{ $statusLabel }}',
                                            statusBadgeClass: '{{ $statusBadgeClass }}',
                                            message: `{{ addslashes($rawMsg) }}`,
                                            waLink: '{{ $waUrl }}'
                                        })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl {{ $payment->status === 'lunas' ? 'bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200/90' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }} text-xs font-semibold shadow-2xs hover:shadow-xs transition duration-150 cursor-pointer"
                                        title="{{ $payment->status === 'lunas' ? 'Kirim Konfirmasi Pembayaran ke WhatsApp' : 'Kirim Pengingat Tagihan ke WhatsApp' }}">
                                        <!-- WhatsApp icon -->
                                        <svg class="w-3.5 h-3.5 fill-current {{ $payment->status === 'lunas' ? 'text-emerald-600' : 'text-white' }}" viewBox="0 0 24 24">
                                            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.28-2.42 5.84a8.17 8.17 0 01-5.82 2.41c-1.47 0-2.93-.39-4.21-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.18 8.18 0 01-1.26-4.39c0-4.54 3.7-8.24 8.24-8.24zm4.8 11.66c-.2-.1-.12-.06-.39-.19-.27-.13-1.6-.79-1.85-.88-.25-.09-.43-.13-.62.13-.18.27-.72.88-.88 1.06-.16.18-.33.2-.6.07-.27-.13-1.15-.42-2.18-1.34-.81-.72-1.36-1.61-1.52-1.88-.16-.27-.02-.42.12-.55.12-.12.27-.31.4-.47.13-.16.18-.27.27-.45.09-.18.04-.33-.02-.47-.07-.13-.62-1.5-.85-2.06-.23-.54-.46-.47-.63-.48-.16-.01-.35-.01-.54-.01-.18 0-.49.07-.75.35-.25.29-.98.96-.98 2.34 0 1.38 1.01 2.71 1.15 2.9.14.18 1.98 3.03 4.8 4.25.67.29 1.2.46 1.61.59.68.22 1.3.19 1.79.11.55-.08 1.69-.69 1.93-1.36.24-.67.24-1.24.17-1.36-.07-.12-.25-.19-.45-.29z"/>
                                        </svg>
                                        <span>{{ $payment->status === 'lunas' ? 'Kirim Bukti' : 'Kirim Reminder' }}</span>
                                    </button>
                                </td>

                                <!-- Aksi (Tandai Lunas / Verifikasi) -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center">
                                        @if ($payment->status !== 'lunas')
                                            <!-- Quick Verify / Tandai Lunas -->
                                            <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" onsubmit="return confirm('Verifikasi pembayaran untuk {{ addslashes($student->name ?? 'siswa') }} dan tandai sebagai LUNAS?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold border border-emerald-200/80 transition shadow-2xs cursor-pointer"
                                                        title="Tandai pembayaran telah diterima (LUNAS)">
                                                    <x-cressco.icon-helper name="check" class="w-3.5 h-3.5 text-emerald-600" />
                                                    <span>Tandai Lunas</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-400">
                                                <x-cressco.icon-helper name="check" class="w-3.5 h-3.5 text-emerald-500" />
                                                <span>Terverifikasi</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                                            <x-cressco.icon-helper name="billing" class="w-6 h-6 text-gray-400" />
                                        </div>
                                        <p class="font-semibold text-gray-700 text-sm">Tidak ada data tagihan/pembayaran yang sesuai kriteria.</p>
                                        <p class="text-xs text-gray-400 mt-1">Coba sesuaikan filter kelas, status, atau periode di atas.</p>
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

        <!-- WhatsApp Reminder Modal -->
        <div x-show="reminderModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
             x-transition>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 space-y-4"
                 @click.outside="reminderModalOpen = false">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.28-2.42 5.84a8.17 8.17 0 01-5.82 2.41c-1.47 0-2.93-.39-4.21-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.18 8.18 0 01-1.26-4.39c0-4.54 3.7-8.24 8.24-8.24zm4.8 11.66c-.2-.1-.12-.06-.39-.19-.27-.13-1.6-.79-1.85-.88-.25-.09-.43-.13-.62.13-.18.27-.72.88-.88 1.06-.16.18-.33.2-.6.07-.27-.13-1.15-.42-2.18-1.34-.81-.72-1.36-1.61-1.52-1.88-.16-.27-.02-.42.12-.55.12-.12.27-.31.4-.47.13-.16.18-.27.27-.45.09-.18.04-.33-.02-.47-.07-.13-.62-1.5-.85-2.06-.23-.54-.46-.47-.63-.48-.16-.01-.35-.01-.54-.01-.18 0-.49.07-.75.35-.25.29-.98.96-.98 2.34 0 1.38 1.01 2.71 1.15 2.9.14.18 1.98 3.03 4.8 4.25.67.29 1.2.46 1.61.59.68.22 1.3.19 1.79.11.55-.08 1.69-.69 1.93-1.36.24-.67.24-1.24.17-1.36-.07-.12-.25-.19-.45-.29z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Kirim Pesan WhatsApp</h3>
                            <p class="text-[11px] text-gray-500">Pesan WhatsApp otomatis siap kirim ke nomor orang tua</p>
                        </div>
                    </div>
                    <button type="button" @click="reminderModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <!-- Summary Info Box -->
                <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-200/80 space-y-2 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Siswa:</span>
                        <span class="font-bold text-gray-900" x-text="activeReminder.name"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Kelas:</span>
                        <span class="font-semibold text-gray-800" x-text="activeReminder.className"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Orang Tua / No. WA:</span>
                        <span class="font-bold text-gray-900" x-text="activeReminder.parent + (activeReminder.phone ? ' (' + activeReminder.phone + ')' : ' -')"></span>
                    </div>
                    <div class="flex justify-between items-center pt-1 border-t border-gray-200/60">
                        <span class="text-gray-500">Nominal Tagihan:</span>
                        <span class="font-bold text-emerald-700" x-text="activeReminder.amount"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Batas Waktu:</span>
                        <span class="font-semibold text-gray-700" x-text="activeReminder.dueDate"></span>
                    </div>
                </div>

                <!-- Message Preview -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Teks Template Pesan (Otomatis):</label>
                    <textarea readonly rows="7" x-text="activeReminder.message" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 font-mono leading-relaxed focus:outline-hidden resize-none select-all"></textarea>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button"
                            @click="navigator.clipboard.writeText(activeReminder.message); copied = true; setTimeout(() => copied = false, 2500);"
                            class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold flex items-center gap-1.5 transition shadow-2xs cursor-pointer">
                        <x-cressco.icon-helper name="copy" class="w-4 h-4 text-gray-500" />
                        <span x-text="copied ? '✓ Tersalin' : 'Salin Teks'"></span>
                    </button>

                    <template x-if="activeReminder.waLink">
                        <a :href="activeReminder.waLink" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-2xs cursor-pointer">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.28-2.42 5.84a8.17 8.17 0 01-5.82 2.41c-1.47 0-2.93-.39-4.21-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.18 8.18 0 01-1.26-4.39c0-4.54 3.7-8.24 8.24-8.24zm4.8 11.66c-.2-.1-.12-.06-.39-.19-.27-.13-1.6-.79-1.85-.88-.25-.09-.43-.13-.62.13-.18.27-.72.88-.88 1.06-.16.18-.33.2-.6.07-.27-.13-1.15-.42-2.18-1.34-.81-.72-1.36-1.61-1.52-1.88-.16-.27-.02-.42.12-.55.12-.12.27-.31.4-.47.13-.16.18-.27.27-.45.09-.18.04-.33-.02-.47-.07-.13-.62-1.5-.85-2.06-.23-.54-.46-.47-.63-.48-.16-.01-.35-.01-.54-.01-.18 0-.49.07-.75.35-.25.29-.98.96-.98 2.34 0 1.38 1.01 2.71 1.15 2.9.14.18 1.98 3.03 4.8 4.25.67.29 1.2.46 1.61.59.68.22 1.3.19 1.79.11.55-.08 1.69-.69 1.93-1.36.24-.67.24-1.24.17-1.36-.07-.12-.25-.19-.45-.29z"/>
                            </svg>
                            <span>Buka WhatsApp</span>
                        </a>
                    </template>
                    <template x-if="!activeReminder.waLink">
                        <span class="text-[11px] text-amber-700 bg-amber-50 px-2.5 py-1.5 rounded-xl border border-amber-200/80">
                            No telepon tidak valid
                        </span>
                    </template>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
