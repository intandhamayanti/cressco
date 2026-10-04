<x-owner-layout :tenant="$tenant" title="Detail Tagihan & Pembayaran">
    <x-slot:breadcrumbSub>Detail Pembayaran</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto" x-data="{ editModalOpen: false, copied: false }">
        
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
                @if ($payment->status !== 'lunas')
                    <form method="POST" action="{{ route('owner.payments.verify', $payment) }}" onsubmit="return confirm('Verifikasi dan tandai tagihan ini sebagai LUNAS?');">
                        @csrf
                        @method('PATCH')
                        <x-cressco.button variant="primary" type="submit">
                            <x-cressco.icon-helper name="check" class="w-4 h-4 mr-1.5" />
                            <span>Verifikasi Lunas</span>
                        </x-cressco.button>
                    </form>
                @endif

                <x-cressco.button variant="outline" @click="editModalOpen = true">
                    <x-cressco.icon-helper name="edit" class="w-4 h-4 mr-1.5" />
                    <span>Edit Tagihan</span>
                </x-cressco.button>

                <form method="POST" action="{{ route('owner.payments.destroy', $payment) }}" onsubmit="return confirm('Yakin ingin menghapus tagihan ini? Data yang dihapus tidak dapat dipulihkan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 transition shadow-2xs" title="Hapus Tagihan">
                        <x-cressco.icon-helper name="trash" class="w-4 h-4" />
                    </button>
                </form>
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
                            <span class="text-gray-400">Mata Pelajaran</span>
                            <p class="font-bold text-gray-900 mt-0.5">
                                {{ $payment->enrollment?->classModel?->subject ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Payment Reminder Generator & Actions -->
            <div class="space-y-6">
                
                <!-- Reminder Generator Card -->
                <div class="bg-white rounded-2xl border border-terracotta-100 p-6 shadow-sm space-y-4">
                    <div class="flex items-center gap-2 text-terracotta-600 font-bold text-sm">
                        <x-cressco.icon-helper name="mail" class="w-4 h-4" />
                        Payment Reminder Generator
                    </div>
                    <p class="text-xs text-gray-500">
                        Pesan pengingat otomatis yang dipersonalisasi untuk orang tua/wali siswa.
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

                        @if ($waLink)
                            <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm5.8 14.18c-.24.68-1.4 1.25-1.94 1.33-.51.08-1.18.11-1.9-.12-.44-.14-1.02-.34-1.78-.67-3.13-1.36-5.16-4.54-5.32-4.75-.16-.21-1.28-1.7-1.28-3.25s.81-2.31 1.1-2.62c.28-.31.62-.39.83-.39.21 0 .42 0 .6.01.2.01.46-.07.72.55.27.64.91 2.22.99 2.38.08.16.14.35.03.56-.11.21-.17.34-.33.53-.16.19-.35.43-.5.58-.16.16-.33.34-.14.67.19.33.84 1.38 1.8 2.24 1.24 1.1 2.29 1.45 2.62 1.61.33.16.52.14.72-.09.2-.23.83-.97 1.05-1.3.22-.33.44-.28.74-.17.3.11 1.9.9 2.22 1.06.32.16.54.24.62.37.08.13.08.76-.16 1.44z"/></svg>
                                Buka di WhatsApp
                            </a>
                        @else
                            <div class="p-3 bg-amber-50 rounded-xl text-[11px] text-amber-700 border border-amber-200">
                                Nomor telepon orang tua tidak ditemukan. Silakan lengkapi kontak siswa di menu Siswa.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Tips Card -->
                <div class="bg-gray-50 rounded-2xl border border-gray-200/80 p-5 space-y-2">
                    <h4 class="text-xs font-bold text-gray-900">Catatan Bisnis Pembayaran:</h4>
                    <ul class="text-[11px] text-gray-600 space-y-1.5 list-disc list-inside">
                        <li>Status Lunas secara otomatis mengunci tanggal penerimaan kas.</li>
                        <li>Outstanding mencakup tagihan belum bayar, menunggu verifikasi, dan terlambat.</li>
                        <li>Seluruh perubahan data pembayaran tercatat dalam riwayat audit log.</li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Modal Edit Tagihan -->
        <div x-show="editModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs">
            <div @click.outside="editModalOpen = false"
                 class="w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-gray-100 p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">Edit Data Tagihan</h3>
                    <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                        <x-cressco.icon-helper name="close" class="w-4 h-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.payments.update', $payment) }}" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Cabang <span class="text-rose-500">*</span></label>
                        <select name="branch_id" required class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}" {{ $payment->branch_id === $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Siswa <span class="text-rose-500">*</span></label>
                        <select name="student_id" required class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                            @foreach ($students as $st)
                                <option value="{{ $st->id }}" {{ $payment->student_id === $st->id ? 'selected' : '' }}>
                                    {{ $st->name }} (Wali: {{ $st->parent_name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Kelas Terkait</label>
                        <select name="enrollment_id" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                            <option value="">-- Tagihan Umum --</option>
                            @foreach ($enrollments as $en)
                                <option value="{{ $en->id }}" {{ $payment->enrollment_id === $en->id ? 'selected' : '' }}>
                                    {{ $en->classModel->name ?? 'Kelas' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Periode Tagihan <span class="text-rose-500">*</span></label>
                            <input type="text" name="period" value="{{ old('period', $payment->period) }}" required class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Nominal (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="amount" value="{{ old('amount', $payment->amount) }}" required min="0" step="5000" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Jatuh Tempo <span class="text-rose-500">*</span></label>
                            <input type="date" name="due_date" value="{{ old('due_date', $payment->due_date ? $payment->due_date->format('Y-m-d') : '') }}" required class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Status Pembayaran <span class="text-rose-500">*</span></label>
                            <select name="status" required class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                                <option value="belum_bayar" {{ $payment->status === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                                <option value="menunggu_verifikasi" {{ $payment->status === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                <option value="lunas" {{ $payment->status === 'lunas' ? 'selected' : '' }}>Lunas</option>
                                <option value="terlambat" {{ $payment->status === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full p-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">{{ old('notes', $payment->notes) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition">
                            Batal
                        </button>
                        <x-cressco.button variant="primary" type="submit">
                            Simpan Perubahan
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-owner-layout>
