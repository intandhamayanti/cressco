<x-owner-layout :tenant="$tenant" title="Detail Tagihan & Pembayaran">
    <x-slot:breadcrumbSub>Detail Pembayaran</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto" x-data="{ copied: false }">
        
        <!-- Top Navigation & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3">
                <a href="{{ route('owner.payments.index') }}" class="p-2 rounded-xl bg-white border border-gray-200/80 text-gray-600 hover:text-gray-900 transition shadow-2xs">
                    <x-cressco.icon-helper name="arrow-left" class="w-4 h-4" />
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Invoice #{{ substr($payment->id, 0, 8) }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Periode {{ $payment->period }} &bull; Siswa: {{ $payment->student->name ?? '-' }} &bull; {{ $tenant->name ?? 'Prime Academy' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                    <x-cressco.icon-helper name="shield" class="w-3.5 h-3.5 text-gray-500" />
                    Read-Only (Oversight)
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Invoice & Student Details -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Main Invoice Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-gray-100 gap-4">
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Tagihan</span>
                            <div class="text-3xl font-extrabold text-gray-900 mt-1">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            @if ($payment->status === 'lunas')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    <x-cressco.icon-helper name="check" class="w-3.5 h-3.5" />
                                    LUNAS
                                </span>
                            @elseif ($payment->status === 'menunggu_verifikasi')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    <x-cressco.icon-helper name="clock" class="w-3.5 h-3.5" />
                                    MENUNGGU VERIFIKASI
                                </span>
                            @elseif ($payment->status === 'terlambat')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                    <x-cressco.icon-helper name="close" class="w-3.5 h-3.5" />
                                    TERLAMBAT
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                    <x-cressco.icon-helper name="clock" class="w-3.5 h-3.5" />
                                    BELUM BAYAR
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-6 border-b border-gray-100 text-xs">
                        <div>
                            <span class="text-gray-400">Periode Tagihan</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $payment->period }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400">Jatuh Tempo</span>
                            <p class="font-bold {{ $payment->status !== 'lunas' && $payment->due_date && $payment->due_date->isPast() ? 'text-rose-600' : 'text-gray-900' }} mt-0.5">
                                {{ $payment->due_date ? $payment->due_date->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-400">Waktu Pembayaran</span>
                            <p class="font-bold text-gray-900 mt-0.5">
                                {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : 'Belum dibayar' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-400">Dicatat / Dikonfirmasi Oleh</span>
                            <p class="font-bold text-gray-900 mt-0.5">
                                {{ $payment->recordedBy->name ?? 'Sistem' }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-6">
                        <span class="text-xs text-gray-400 block mb-1">Catatan Tagihan:</span>
                        <p class="text-xs text-gray-700 bg-gray-50 p-3 rounded-xl border border-gray-100">
                            {{ $payment->notes ?: 'Tidak ada catatan khusus untuk tagihan ini.' }}
                        </p>
                    </div>
                </div>

                <!-- Student & Academic Details Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <x-cressco.icon-helper name="academic" class="w-4 h-4 text-terracotta-500" />
                        Informasi Siswa & Akademik
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-gray-400">Nama Lengkap Siswa</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $payment->student->name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400">Cabang Belajar</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $payment->branch->name ?? '-' }} ({{ $payment->branch->city ?? '-' }})</p>
                        </div>
                        <div>
                            <span class="text-gray-400">Nama Orang Tua / Wali</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $payment->student->parent_name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400">Kontak Orang Tua</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $payment->student->parent_phone ?: ($payment->student->phone ?: '-') }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400">Kelas Terdaftar</span>
                            <p class="font-bold text-gray-900 mt-0.5">
                                {{ $payment->enrollment?->classModel?->name ?? 'Tagihan Umum' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-400">Program Kursus</span>
                            <p class="font-bold text-gray-900 mt-0.5">
                                {{ $payment->enrollment?->classModel?->subject ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Payment Reminder Preview & Guidelines -->
            <div class="space-y-6">
                
                <!-- Reminder Preview Card -->
                <div class="bg-white rounded-2xl border border-terracotta-100 p-6 shadow-sm space-y-4">
                    <div class="flex items-center gap-2 text-terracotta-600 font-bold text-sm">
                        <x-cressco.icon-helper name="mail" class="w-4 h-4" />
                        Template Pengingat Pembayaran (Payment Reminder Generator)
                    </div>
                    <p class="text-xs text-gray-500">
                        Pesan template WhatsApp yang dikirimkan oleh Admin Cabang kepada wali murid.
                    </p>

                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Isi Pesan WhatsApp</label>
                        <textarea id="reminderText" readonly rows="9" class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50/75 text-xs text-gray-800 focus:ring-0 focus:outline-hidden resize-none font-mono leading-relaxed">{{ $reminderMessage }}</textarea>
                    </div>

                    <div class="space-y-2 pt-2">
                        <button type="button"
                                @click="navigator.clipboard.writeText(document.getElementById('reminderText').value); copied = true; setTimeout(() => copied = false, 2500);"
                                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-800 text-xs font-bold transition">
                            <x-cressco.icon-helper name="check" class="w-4 h-4 text-emerald-600" x-show="copied" />
                            <span x-text="copied ? 'Berhasil Disalin ke Clipboard!' : 'Salin Pesan Reminder'"></span>
                        </button>
                    </div>
                </div>

                <!-- Quick Tips Card -->
                <div class="bg-gray-50 rounded-2xl border border-gray-200/80 p-5 space-y-2">
                    <h4 class="text-xs font-bold text-gray-900">Peran & Batasan Akses:</h4>
                    <ul class="text-[11px] text-gray-600 space-y-1.5 list-disc list-inside">
                        <li>Owner memantau kepatuhan pembayaran dan performa keuangan bimbel.</li>
                        <li>Penerimaan kas, verifikasi kwitansi, dan penagihan lapangan dikelola oleh Admin Cabang.</li>
                        <li>Perubahan data pembayaran dicatat otomatis dalam sistem audit log.</li>
                    </ul>
                </div>

            </div>
        </div>

    </div>
</x-owner-layout>
