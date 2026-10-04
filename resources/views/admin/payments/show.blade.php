<x-admin-layout :tenant="$tenant" title="Detail Tagihan & Pembayaran">
    <x-slot:breadcrumbSub>Detail Pembayaran</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto" x-data="{ editModalOpen: false, copied: false }">
        
        <!-- Top Navigation & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.payments.index') }}" class="p-2 rounded-xl bg-white border border-gray-200/80 text-gray-600 hover:text-gray-900 transition shadow-2xs">
                    <x-cressco.icon-helper name="arrow-left" class="w-4 h-4" />
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Invoice #{{ substr($payment->id, 0, 8) }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Periode {{ $payment->period }} &bull; Siswa: {{ $payment->student->name ?? '-' }} &bull; Cabang: {{ $payment->branch->name ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if ($payment->status !== 'lunas')
                    <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" onsubmit="return confirm('Verifikasi dan tandai tagihan ini sebagai LUNAS?');">
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

                <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}" onsubmit="return confirm('Yakin ingin menghapus tagihan ini? Data yang dihapus tidak dapat dipulihkan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 transition shadow-2xs cursor-pointer" title="Hapus Tagihan">
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
                            <span class="text-gray-400">Dicatat Oleh</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $payment->recordedBy->name ?? 'System' }}</p>
                        </div>
                    </div>

                    @if ($payment->notes)
                        <div class="pt-4 text-xs">
                            <span class="text-gray-400 font-medium">Catatan / Keterangan:</span>
                            <p class="text-gray-700 mt-1 bg-gray-50 p-3 rounded-xl border border-gray-100 leading-relaxed">{{ $payment->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Student & Academic Info Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Siswa & Akademik</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-gray-400">Nama Siswa</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $payment->student->name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400">Cabang</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $payment->branch->name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400">Nama Orang Tua / Wali</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $payment->student->parent_name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400">No. Telepon Ortu</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $payment->student->parent_phone ?? '-' }}</p>
                        </div>
                        @if ($payment->enrollment && $payment->enrollment->classModel)
                            <div class="sm:col-span-2 bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <span class="text-gray-400 block mb-0.5">Kelas Terdaftar:</span>
                                <span class="font-bold text-gray-900">{{ $payment->enrollment->classModel->name }}</span>
                                @if ($payment->enrollment->classModel->subject)
                                    <span class="text-gray-500">({{ $payment->enrollment->classModel->subject->name }})</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Right Column: Reminder Generator & Actions -->
            <div class="space-y-6">
                
                <!-- Parent Reminder Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <x-cressco.icon-helper name="message-square" class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Reminder Orang Tua</h3>
                            <p class="text-[11px] text-gray-500">Kirim pesan WhatsApp tagihan</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Teks Pesan Template:</label>
                        <textarea readonly rows="8" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 font-mono leading-relaxed focus:outline-hidden resize-none select-all" id="reminderTextarea">{{ $reminderMessage }}</textarea>
                    </div>

                    <div class="space-y-2 pt-2">
                        <button type="button"
                                @click="
                                    navigator.clipboard.writeText(document.getElementById('reminderTextarea').value);
                                    copied = true;
                                    setTimeout(() => copied = false, 2500);
                                "
                                class="w-full py-2.5 px-4 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-800 text-xs font-semibold flex items-center justify-center gap-2 transition shadow-2xs cursor-pointer">
                            <x-cressco.icon-helper name="copy" class="w-4 h-4 text-gray-500" />
                            <span x-text="copied ? '✓ Berhasil Disalin!' : 'Salin Pesan Reminder'"></span>
                        </button>

                        @if ($waLink)
                            <a href="{{ $waLink }}" target="_blank" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold flex items-center justify-center gap-2 transition shadow-2xs">
                                <x-cressco.icon-helper name="message-square" class="w-4 h-4" />
                                <span>Buka di WhatsApp Web / App</span>
                            </a>
                        @else
                            <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200/80 text-[11px] text-amber-800 text-center">
                                Nomor telepon orang tua tidak valid untuk tautan langsung WhatsApp. Silakan gunakan tombol salin di atas.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Verification Action Card if pending -->
                @if ($payment->status !== 'lunas')
                    <div class="bg-gradient-to-br from-terracotta-50 to-orange-50/40 rounded-2xl border border-terracotta-200/80 p-6 shadow-xs space-y-3">
                        <h4 class="text-xs font-bold text-terracotta-900 uppercase tracking-wider">Aksi Verifikasi Kasir / Admin</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Jika orang tua siswa telah melakukan transfer atau menyerahkan uang kas, klik tombol di bawah untuk mencatat pelunasan invoice.
                        </p>
                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full py-2.5 px-4 bg-terracotta-600 hover:bg-terracotta-700 text-white font-bold text-xs rounded-xl transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                <x-cressco.icon-helper name="check" class="w-4 h-4" />
                                <span>Verifikasi Pembayaran Lunas</span>
                            </button>
                        </form>
                    </div>
                @endif

            </div>

        </div>

        <!-- Edit Payment Modal -->
        <div x-show="editModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs"
             x-transition>
            <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-100 space-y-4 max-h-[90vh] overflow-y-auto"
                 @click.outside="editModalOpen = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Edit Data Tagihan #{{ substr($payment->id, 0, 8) }}</h3>
                    <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Cabang <span class="text-rose-500">*</span></label>
                        <select name="branch_id" required class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}" {{ $payment->branch_id === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Siswa <span class="text-rose-500">*</span></label>
                        <select name="student_id" required class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                            @foreach ($students as $s)
                                <option value="{{ $s->id }}" {{ $payment->student_id === $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Kelas / Enrollment (Opsional)</label>
                        <select name="enrollment_id" class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                            <option value="">-- Tidak Terkait Kelas Spesifik --</option>
                            @foreach ($enrollments as $e)
                                <option value="{{ $e->id }}" {{ $payment->enrollment_id === $e->id ? 'selected' : '' }}>
                                    {{ $e->classModel->name ?? 'Kelas' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Periode Tagihan <span class="text-rose-500">*</span></label>
                            <input type="text" name="period" value="{{ $payment->period }}" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Nominal Tagihan (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="amount" value="{{ (int) $payment->amount }}" min="0" step="1000" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Batas Jatuh Tempo <span class="text-rose-500">*</span></label>
                            <input type="date" name="due_date" value="{{ $payment->due_date ? $payment->due_date->format('Y-m-d') : '' }}" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
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
                        <label class="block font-semibold text-gray-700 mb-1">Catatan / Keterangan</label>
                        <textarea name="notes" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">{{ $payment->notes }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <x-cressco.button variant="secondary" type="button" @click="editModalOpen = false">
                            Batal
                        </x-cressco.button>
                        <x-cressco.button variant="primary" type="submit">
                            Simpan Perubahan
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
