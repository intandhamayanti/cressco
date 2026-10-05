<x-admin-layout :tenant="$tenant" :title="'Presensi Sesi - ' . ($session->classModel?->name ?? 'Kelas')">
    <x-slot:breadcrumbSub>
        <a href="{{ route('admin.classes.index') }}" class="hover:text-primary-600 transition-colors">Kelas & Jadwal</a>
        <span class="text-gray-300">/</span>
        @if($session->classModel)
            <a href="{{ route('admin.classes.show', $session->classModel) }}" class="hover:text-primary-600 transition-colors">{{ $session->classModel->name }}</a>
            <span class="text-gray-300">/</span>
        @endif
        <span>Detail Sesi ({{ \Carbon\Carbon::parse($session->session_date)->translatedFormat('d M Y') }})</span>
    </x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             editModalOpen: false,
             replaceModalOpen: false,
             activeAttendance: { id: '', student_name: '', status: 'hadir', note: '' },
             openEdit(att) {
                 this.activeAttendance = {
                     id: att.id,
                     student_name: att.student_name,
                     status: att.status,
                     note: att.note || ''
                 };
                 this.editModalOpen = true;
             }
         }">

        <!-- Back Button & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
            <div class="flex items-center gap-3">
                @if($session->classModel)
                    <a href="{{ route('admin.classes.show', $session->classModel) }}"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-xs"
                       title="Kembali ke Detail Kelas">
                        <x-cressco.icon-helper name="arrow-left" class="w-4 h-4" />
                    </a>
                @else
                    <a href="{{ route('admin.attendances.index') }}"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-xs"
                       title="Kembali ke Presensi">
                        <x-cressco.icon-helper name="arrow-left" class="w-4 h-4" />
                    </a>
                @endif
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Presensi Sesi: {{ $session->classModel?->name ?? 'Kelas' }}</h1>
                        @if($session->status === 'completed')
                            <x-cressco.badge variant="success" size="sm" dot>Selesai</x-cressco.badge>
                        @elseif($session->status === 'cancelled')
                            <x-cressco.badge variant="danger" size="sm" dot>Dibatalkan</x-cressco.badge>
                        @else
                            <x-cressco.badge variant="info" size="sm" dot>Terjadwal</x-cressco.badge>
                        @endif

                        @if($session->actual_tutor_id && $session->scheduled_tutor_id && $session->actual_tutor_id !== $session->scheduled_tutor_id)
                            <x-cressco.badge variant="warning" size="sm">Tutor Pengganti Aktif</x-cressco.badge>
                        @elseif(is_null($session->actual_tutor_id))
                            <x-cressco.badge variant="danger" size="sm">Belum Ada Tutor Pengajar</x-cressco.badge>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Tanggal: <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d F Y') }}</span> &bull; 
                        Waktu: <span class="font-medium text-gray-700">{{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB</span> &bull; 
                        Cabang: <span class="font-medium text-gray-700">{{ $session->branch?->name ?? '-' }}</span> &bull; 
                        Ruang: <span class="font-medium text-gray-700">{{ $session->room ?? '-' }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($session->classModel)
                    <a href="{{ route('admin.classes.show', $session->classModel) }}">
                        <x-cressco.button variant="outline" size="sm" leadingIcon="layers">
                            Lihat Kelas
                        </x-cressco.button>
                    </a>
                @endif
                <x-cressco.button variant="primary" size="sm" leadingIcon="user-check" @click="replaceModalOpen = true">
                    Atur Tutor Pengganti
                </x-cressco.button>
            </div>
        </div>

        <!-- Alert if Tutor Reported Absence -->
        @if ($session->notes && str_contains($session->notes, '[TUTOR BERHALANGAN]'))
            <div class="p-4 rounded-xl bg-amber-50 border-2 border-amber-300 flex items-start gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0 font-bold">
                    <x-cressco.icon-helper name="alert-circle" class="w-4 h-4" />
                </div>
                <div class="space-y-1">
                    <h3 class="text-xs font-bold text-amber-950">Laporan Ketidakhadiran Tutor</h3>
                    <p class="text-xs text-amber-900 leading-relaxed">
                        {{ $session->notes }}
                    </p>
                    @if(is_null($session->actual_tutor_id))
                        <div class="pt-1">
                            <button type="button"
                                    @click="replaceModalOpen = true"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition shadow-2xs">
                                <span>Tugaskan Tutor Pengganti Sekarang</span>
                                <x-cressco.icon-helper name="arrow-right" class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Session Detail Info Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Tutor Terjadwal -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
                <div class="text-xs text-gray-500 font-medium">Tutor Terjadwal (Rutin)</div>
                <div class="text-base font-bold text-gray-900 mt-1">
                    {{ $session->scheduledTutor?->name ?? 'Belum Ditentukan' }}
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">Tutor utama kelas</div>
            </div>

            <!-- Card 2: Tutor Aktual (Mengajar) -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
                <div class="text-xs text-gray-500 font-medium">Tutor Aktual (Sesi Ini)</div>
                <div class="text-base font-bold text-gray-900 mt-1">
                    {{ $session->actualTutor?->name ?? 'Belum Ditentukan' }}
                </div>
                @if($session->actual_tutor_id && $session->scheduled_tutor_id && $session->actual_tutor_id !== $session->scheduled_tutor_id)
                    <div class="text-[11px] text-amber-600 font-semibold mt-0.5">
                        Tutor Pengganti (Dasar Honor)
                    </div>
                @elseif(is_null($session->actual_tutor_id))
                    <div class="text-[11px] text-red-600 font-bold mt-0.5">
                        Kosong (Perlu Pengganti)
                    </div>
                @else
                    <div class="text-[11px] text-emerald-600 font-medium mt-0.5">
                        Sesuai Tutor Terjadwal
                    </div>
                @endif
            </div>

            <!-- Card 3: Ruangan & Materi -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
                <div class="text-xs text-gray-500 font-medium">Ruangan & Topik</div>
                <div class="text-base font-bold text-gray-900 mt-1">
                    {{ $session->room ?? 'Ruangan Reguler' }}
                </div>
                <div class="text-xs text-gray-500 mt-0.5 truncate">
                    Materi: {{ $session->material ?? $session->notes ?? 'Materi Reguler' }}
                </div>
            </div>

            <!-- Card 4: Ringkasan Presensi -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
                <div class="text-xs text-gray-500 font-medium">Ringkasan Kehadiran</div>
                @php
                    $totalInSession = $session->studentAttendances->count();
                    $presentInSession = $session->studentAttendances->filter(fn($a) => in_array($a->status, ['hadir', 'present']))->count();
                @endphp
                <div class="text-base font-bold text-gray-900 mt-1">
                    {{ $presentInSession }} / {{ $totalInSession }} Siswa Hadir
                </div>
                <div class="text-xs text-gray-400 mt-0.5">
                    {{ $totalInSession > 0 ? round(($presentInSession / $totalInSession) * 100) : 0 }}% Tingkat Kehadiran
                </div>
            </div>
        </div>

        <!-- Tutor Replacement History (Audit Trail) -->
        @if ($session->tutorReplacements->isNotEmpty())
            <div class="bg-white rounded-xl border border-amber-200 overflow-hidden shadow-xs">
                <div class="p-4 bg-amber-50/70 border-b border-amber-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-cressco.icon-helper name="history" class="w-4 h-4 text-amber-700" />
                        <h2 class="text-sm font-bold text-amber-950">Riwayat Penggantian Tutor Sesi Ini</h2>
                    </div>
                    <span class="text-xs text-amber-800">{{ $session->tutorReplacements->count() }} Kali Pergantian</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-700">
                        <thead class="bg-gray-50/50 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Waktu Perubahan</th>
                                <th class="py-3 px-4">Tutor Semula</th>
                                <th class="py-3 px-4">Tutor Pengganti</th>
                                <th class="py-3 px-4">Alasan</th>
                                <th class="py-3 px-4">Diubah Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($session->tutorReplacements as $rep)
                                <tr>
                                    <td class="py-3 px-4 font-mono text-gray-600">
                                        {{ $rep->changed_at ? \Carbon\Carbon::parse($rep->changed_at)->translatedFormat('d M Y H:i') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-gray-800">
                                        {{ $rep->previousActualTutor?->name ?? ($rep->scheduledTutor?->name ?? '-') }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-amber-700">
                                        {{ $rep->replacementTutor?->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ $rep->reason }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-500">
                                        {{ $rep->changedBy?->name ?? 'Admin' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Student Attendance List -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-gray-200/75 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900">Daftar Kehadiran Siswa pada Sesi Ini</h2>
                <span class="text-xs text-gray-500">{{ $session->studentAttendances->count() }} Siswa Tercatat</span>
            </div>

            @if($session->studentAttendances->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="users" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Belum ada data presensi pada sesi ini</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Presensi biasanya dicatat oleh tutor saat sesi belajar berlangsung.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50/75 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4">Nama Siswa</th>
                                <th class="py-3.5 px-4">Nomor Induk</th>
                                <th class="py-3.5 px-4">Waktu Presensi</th>
                                <th class="py-3.5 px-4">Status Kehadiran</th>
                                <th class="py-3.5 px-4">Catatan</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/70">
                            @foreach($session->studentAttendances as $att)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-gray-900">{{ $att->student?->name ?? 'Siswa' }}</div>
                                        <div class="text-xs text-gray-500">{{ $att->student?->email ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs font-mono text-gray-700">
                                        {{ $att->student?->nis ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-gray-600">
                                        {{ $att->recorded_at ? \Carbon\Carbon::parse($att->recorded_at)->translatedFormat('d M Y H:i') : '-' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if(in_array($att->status, ['hadir', 'present']))
                                            <x-cressco.badge variant="success" size="sm" dot>Hadir</x-cressco.badge>
                                        @elseif(in_array($att->status, ['izin', 'excused']))
                                            <x-cressco.badge variant="info" size="sm" dot>Izin</x-cressco.badge>
                                        @elseif($att->status === 'sakit')
                                            <x-cressco.badge variant="warning" size="sm" dot>Sakit</x-cressco.badge>
                                        @elseif(in_array($att->status, ['alpa', 'absent']))
                                            <x-cressco.badge variant="danger" size="sm" dot>Alpa</x-cressco.badge>
                                        @else
                                            <x-cressco.badge variant="neutral" size="sm" dot>{{ ucfirst($att->status) }}</x-cressco.badge>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-gray-600">
                                        {{ $att->note ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <button type="button"
                                                @click="openEdit({
                                                    id: '{{ $att->id }}',
                                                    student_name: '{{ addslashes($att->student?->name ?? '') }}',
                                                    status: '{{ in_array($att->status, ['present', 'hadir']) ? 'hadir' : (in_array($att->status, ['excused', 'izin']) ? 'izin' : (in_array($att->status, ['absent', 'alpa']) ? 'alpa' : $att->status)) }}',
                                                    note: '{{ addslashes($att->note ?? '') }}'
                                                })"
                                                class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs inline-flex items-center justify-center"
                                                title="Koreksi Presensi">
                                            <x-cressco.icon-helper name="pencil" class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- ATUR TUTOR PENGGANTI MODAL -->
        <div x-show="replaceModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="replaceModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                                <x-cressco.icon-helper name="user-check" class="w-4 h-4" />
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Atur Tutor Pengganti</h3>
                        </div>
                        <button type="button" @click="replaceModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form action="{{ route('admin.attendances.sessions.replace-tutor', $session) }}" method="POST" class="mt-4 space-y-4">
                        @csrf

                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1.5">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Kelas:</span>
                                <span class="font-bold text-gray-900">{{ $session->classModel?->name ?? 'Kelas' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tanggal & Waktu:</span>
                                <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d M Y') }} ({{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tutor Terjadwal:</span>
                                <span class="font-semibold text-gray-900">{{ $session->scheduledTutor?->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tutor Aktual Saat Ini:</span>
                                <span class="font-bold text-amber-700">{{ $session->actualTutor?->name ?? 'Belum Ditentukan' }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                                Pilih Tutor Pengganti *
                            </label>
                            <select name="replacement_tutor_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                <option value="">-- Pilih Tutor Pengganti --</option>
                                @foreach($availableTutors as $tut)
                                    <option value="{{ $tut->id }}" {{ $session->actual_tutor_id === $tut->id ? 'selected' : '' }}>
                                        {{ $tut->name }} ({{ ucfirst($tut->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                                Alasan Penggantian *
                            </label>
                            <textarea name="reason" rows="3" placeholder="Contoh: Tutor utama sedang sakit demam / berhalangan hadir..."
                                      class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required></textarea>
                        </div>

                        <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-200 text-[11px] text-blue-900 flex items-start gap-2">
                            <x-cressco.icon-helper name="info" class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                            <span>
                                <strong>Catatan:</strong> Pergantian tutor hanya berlaku untuk sesi tanggal ini saja. Tutor utama pada jadwal kelas tidak akan berubah, dan honor sesi ini akan dihitung untuk tutor pengganti yang mengajar.
                            </span>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="replaceModalOpen = false">
                                Batal
                            </x-cressco.button>
                            <x-cressco.button variant="primary" size="md" type="submit">
                                Simpan Tutor Pengganti
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- EDIT / CORRECT ATTENDANCE MODAL -->
        <div x-show="editModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="editModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Koreksi Presensi Siswa</h3>
                        <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form :action="'{{ url('admin/attendances') }}/' + activeAttendance.id" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <div class="text-xs text-gray-500">Nama Siswa</div>
                            <div class="text-sm font-bold text-gray-900 mt-0.5" x-text="activeAttendance.student_name"></div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status Presensi *</label>
                            <select name="status" x-model="activeAttendance.status" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                <option value="hadir">Hadir</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpa">Alpa</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Catatan / Alasan Perubahan</label>
                            <textarea name="note" x-model="activeAttendance.note" rows="3" placeholder="Masukkan alasan koreksi presensi atau keterangan siswa..."
                                      class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"></textarea>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="editModalOpen = false">
                                Batal
                            </x-cressco.button>
                            <x-cressco.button variant="primary" size="md" type="submit">
                                Simpan Koreksi
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
