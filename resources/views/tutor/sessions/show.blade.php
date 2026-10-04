<x-tutor-layout :tenant="$tenant" title="Detail Sesi Mengajar & Presensi">
    <x-slot:breadcrumbSub>Detail Sesi</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto" x-data="{
        markAll(status) {
            document.querySelectorAll('input[type=radio][value=' + status + ']').forEach(el => {
                el.checked = true;
            });
        }
    }">
        
        <!-- Header & Breadcrumb / Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('tutor.sessions.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 transition shadow-2xs">
                    <x-cressco.icon-helper name="chevron-left" class="w-4 h-4" />
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                        {{ $session->classModel?->name ?? 'Sesi Mengajar' }}
                    </h1>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d F Y') : '-' }} • {{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-center">
                @if ($session->class_id)
                    <a href="{{ route('tutor.classes.show', $session->class_id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-xs font-bold text-gray-700 transition shadow-2xs">
                        <x-cressco.icon-helper name="academic" class="w-3.5 h-3.5 text-gray-500" />
                        <span>Lihat Detail Kelas</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Session Overview Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- LEFT COLUMN: Sesi Info & Tutor Presensi (1 Col on LG) -->
            <div class="space-y-6">
                
                <!-- Card 1: Informasi Sesi -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Ringkasan Sesi</h3>
                        @if ($session->status === 'completed')
                            <x-cressco.badge variant="success" dot>Selesai</x-cressco.badge>
                        @elseif ($session->status === 'cancelled')
                            <x-cressco.badge variant="danger" dot>Dibatalkan</x-cressco.badge>
                        @else
                            <x-cressco.badge variant="info" dot>Terjadwal</x-cressco.badge>
                        @endif
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Mata Pelajaran:</span>
                            <span class="font-bold text-gray-900">{{ $session->classModel?->subject ?? 'Umum' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Jenjang:</span>
                            <span class="font-semibold text-gray-900">{{ $session->classModel?->level ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Cabang:</span>
                            <span class="font-semibold text-gray-900">{{ $session->branch?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Ruangan:</span>
                            <span class="font-semibold text-gray-900">{{ $session->room ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Tutor Terjadwal:</span>
                            <span class="font-semibold text-gray-900">{{ $session->scheduledTutor?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Tutor Aktual:</span>
                            <span class="font-bold text-slate-900">{{ $session->actualTutor?->name ?? ($session->scheduledTutor?->name ?? '-') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Materi & Catatan Mengajar -->
                <div class="bg-white rounded-3xl border border-gray-200/80 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Jurnal / Materi Mengajar</h3>
                    </div>

                    <form method="POST" action="{{ route('tutor.sessions.update', $session->id) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Materi / Topik yang Diajarkan</label>
                            <textarea name="material"
                                      rows="3"
                                      placeholder="Contoh: Pembahasan Persamaan Kuadrat & Latihan Soal Bab 3"
                                      class="w-full text-xs bg-gray-50/70 border border-gray-200 rounded-xl p-3 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">{{ old('material', $session->material) }}</textarea>
                            @error('material')
                                <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Tambahan Sesi</label>
                            <textarea name="notes"
                                      rows="2"
                                      placeholder="Contoh: Sebagian besar siswa memahami materi, PR diberikan hal 45"
                                      class="w-full text-xs bg-gray-50/70 border border-gray-200 rounded-xl p-3 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">{{ old('notes', $session->notes) }}</textarea>
                            @error('notes')
                                <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status Sesi</label>
                            <select name="status"
                                    class="w-full text-xs bg-gray-50/70 border border-gray-200 rounded-xl p-2.5 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer">
                                <option value="scheduled" {{ $session->status === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                                <option value="completed" {{ $session->status === 'completed' ? 'selected' : '' }}>Selesai</option>
                                <option value="cancelled" {{ $session->status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                            Simpan Materi & Catatan
                        </button>
                    </form>
                </div>

            </div>

            <!-- RIGHT COLUMN: Input & Koreksi Presensi Siswa (2 Cols on LG) -->
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
                    
                    <!-- Header Section with Quick Actions -->
                    <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/40">
                        <div>
                            <h2 class="text-sm font-extrabold text-gray-900">Presensi Kehadiran Siswa</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Input kehadiran untuk seluruh siswa yang terdaftar di kelas ini</p>
                        </div>

                        <!-- Quick Mark All Buttons -->
                        <div class="flex items-center gap-1.5 self-start sm:self-center">
                            <span class="text-[11px] font-semibold text-gray-400 mr-1">Cepat:</span>
                            <button type="button" @click="markAll('hadir')" class="px-2.5 py-1 rounded-lg bg-terracotta-50 hover:bg-terracotta-100 text-terracotta-700 font-bold text-[11px] transition cursor-pointer">
                                Semua Hadir
                            </button>
                            <button type="button" @click="markAll('izin')" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] transition cursor-pointer">
                                Semua Izin
                            </button>
                        </div>
                    </div>

                    @if ($allStudentRows->isNotEmpty())
                        <form method="POST" action="{{ route('tutor.sessions.attendances.store', $session->id) }}">
                            @csrf

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                            <th class="p-4 w-12 text-center">No</th>
                                            <th class="p-4">Nama Siswa</th>
                                            <th class="p-4 text-center">Status Kehadiran</th>
                                            <th class="p-4">Catatan / Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-gray-700">
                                        @foreach ($allStudentRows as $index => $item)
                                            @php
                                                $student = $item['student'];
                                                $att = $item['attendance'];
                                                $currentStatus = old("attendances.{$index}.status", $att?->status ?? 'hadir');
                                                $currentNote = old("attendances.{$index}.note", $att?->note ?? '');
                                            @endphp
                                            <tr class="hover:bg-gray-50/60 transition">
                                                <!-- No -->
                                                <td class="p-4 text-center text-gray-400 font-medium">
                                                    {{ $loop->iteration }}
                                                </td>

                                                <!-- Nama Siswa -->
                                                <td class="p-4">
                                                    <input type="hidden" name="attendances[{{ $index }}][student_id]" value="{{ $student->id }}">
                                                    <div class="font-bold text-gray-900">{{ $student->name }}</div>
                                                    <div class="text-[11px] text-gray-400 font-medium mt-0.5">NIS: {{ $student->nis ?? '-' }} • {{ $student->grade ?? '-' }}</div>
                                                </td>

                                                <!-- Status Kehadiran Radio Buttons -->
                                                <td class="p-4">
                                                    <div class="flex items-center justify-center gap-1 sm:gap-2">
                                                        <!-- Hadir -->
                                                        <label class="cursor-pointer">
                                                            <input type="radio"
                                                                   name="attendances[{{ $index }}][status]"
                                                                   value="hadir"
                                                                   class="peer sr-only"
                                                                   {{ $currentStatus === 'hadir' ? 'checked' : '' }}>
                                                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-xl text-[11px] font-bold border border-gray-200 text-gray-600 peer-checked:bg-terracotta-500 peer-checked:text-white peer-checked:border-terracotta-500 peer-checked:shadow-2xs transition">
                                                                Hadir
                                                            </span>
                                                        </label>

                                                        <!-- Izin -->
                                                        <label class="cursor-pointer">
                                                            <input type="radio"
                                                                   name="attendances[{{ $index }}][status]"
                                                                   value="izin"
                                                                   class="peer sr-only"
                                                                   {{ $currentStatus === 'izin' ? 'checked' : '' }}>
                                                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-xl text-[11px] font-bold border border-gray-200 text-gray-600 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500 peer-checked:shadow-2xs transition">
                                                                Izin
                                                            </span>
                                                        </label>

                                                        <!-- Sakit -->
                                                        <label class="cursor-pointer">
                                                            <input type="radio"
                                                                   name="attendances[{{ $index }}][status]"
                                                                   value="sakit"
                                                                   class="peer sr-only"
                                                                   {{ $currentStatus === 'sakit' ? 'checked' : '' }}>
                                                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-xl text-[11px] font-bold border border-gray-200 text-gray-600 peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500 peer-checked:shadow-2xs transition">
                                                                Sakit
                                                            </span>
                                                        </label>

                                                        <!-- Alpa -->
                                                        <label class="cursor-pointer">
                                                            <input type="radio"
                                                                   name="attendances[{{ $index }}][status]"
                                                                   value="alpa"
                                                                   class="peer sr-only"
                                                                   {{ $currentStatus === 'alpa' ? 'checked' : '' }}>
                                                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-xl text-[11px] font-bold border border-gray-200 text-gray-600 peer-checked:bg-rose-500 peer-checked:text-white peer-checked:border-rose-500 peer-checked:shadow-2xs transition">
                                                                Alpa
                                                            </span>
                                                        </label>
                                                    </div>
                                                </td>

                                                <!-- Note / Alasan -->
                                                <td class="p-4">
                                                    <input type="text"
                                                           name="attendances[{{ $index }}][note]"
                                                           value="{{ $currentNote }}"
                                                           placeholder="Catatan (opsional)"
                                                           class="w-full text-xs bg-gray-50/70 border border-gray-200 rounded-xl px-2.5 py-1.5 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Footer Submit Actions -->
                            <div class="p-5 border-t border-gray-100 bg-gray-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700">
                                    <input type="checkbox" name="mark_session_completed" value="1" {{ $session->status === 'completed' ? 'checked' : '' }} class="rounded-md text-terracotta-600 focus:ring-terracotta-500 border-gray-300">
                                    <span>Tandai status sesi mengajar ini sebagai <strong>Selesai</strong></span>
                                </label>

                                <div class="flex items-center gap-3">
                                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-2">
                                        <x-cressco.icon-helper name="check-circle" class="w-4 h-4" />
                                        <span>Simpan Presensi Siswa</span>
                                    </button>
                                </div>
                            </div>

                        </form>
                    @else
                        <div class="p-12 text-center space-y-3">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 mx-auto flex items-center justify-center">
                                <x-cressco.icon-helper name="alert-circle" class="w-6 h-6" />
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-gray-900">Tidak ada siswa terdaftar</h4>
                                <p class="text-xs text-gray-500">Belum ada siswa aktif yang terdaftar dalam kelas ini.</p>
                            </div>
                        </div>
                    @endif

                </div>

            </div>

        </div>

    </div>
</x-tutor-layout>
