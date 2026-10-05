<x-owner-layout :tenant="$tenant" title="Detail Sesi Presensi">
    <x-slot:breadcrumbSub>Detail Sesi Presensi</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto">
        <!-- Back Navigation & Header -->
        <div class="flex items-center gap-3">
            <a href="{{ route('owner.attendances.index') }}" class="p-2 rounded-xl bg-white border border-gray-200/80 text-gray-600 hover:text-gray-900 transition shadow-2xs">
                <x-cressco.icon-helper name="arrow-left" class="w-4 h-4" />
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 leading-tight">Detail Sesi: {{ $session->classModel?->name ?? 'Kelas' }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d F Y') }} • {{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} • {{ $session->branch?->name }}
                </p>
            </div>
        </div>

        <!-- Sesi Information Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs space-y-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Tutor Pengajar</span>
                <div class="text-base font-bold text-gray-900">
                    {{ $session->actualTutor?->name ?? $session->scheduledTutor?->name ?? '-' }}
                </div>
                @if ($session->actual_tutor_id && $session->scheduled_tutor_id && $session->actual_tutor_id !== $session->scheduled_tutor_id)
                    <div class="text-[11px] text-amber-600 font-medium">
                        (Tutor Pengganti dari jadwal utama: {{ $session->scheduledTutor?->name }})
                    </div>
                @else
                    <div class="text-[11px] text-gray-500">Tutor Utama Sesuai Jadwal</div>
                @endif
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs space-y-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Status Sesi & Ruangan</span>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $session->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ ucfirst($session->status) }}
                    </span>
                    <span class="text-xs text-gray-600 font-semibold">{{ $session->room ?: 'Ruang Belajar' }}</span>
                </div>
                <p class="text-[11px] text-gray-500">Materi: {{ $session->material ?: 'Belum diisi' }}</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs space-y-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Rekap Kehadiran Sesi</span>
                @php
                    $totalInSession = $session->studentAttendances->count();
                    $presentInSession = $session->studentAttendances->whereIn('status', ['hadir', 'present'])->count();
                    $ratio = $totalInSession > 0 ? round(($presentInSession / $totalInSession) * 100) : 0;
                @endphp
                <div class="text-xl font-bold text-gray-900">
                    {{ $presentInSession }} / {{ $totalInSession }} Siswa ({{ $ratio }}%)
                </div>
                <p class="text-[11px] text-gray-500">Tingkat kehadiran kelas pada pertemuan ini</p>
            </div>
        </div>

        <!-- Student Attendance List -->
        <div class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-gray-200/75">
                <h2 class="text-base font-bold text-gray-900">Daftar Presensi Siswa</h2>
                <p class="text-xs text-gray-500 mt-0.5">Daftar siswa yang terdaftar di kelas dan status kehadirannya</p>
            </div>

            @if ($session->studentAttendances->isEmpty())
                <div class="p-8 text-center text-xs text-gray-400">
                    Belum ada presensi yang tercatat untuk sesi ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-700 uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Nama Siswa</th>
                                <th class="py-3 px-4">Status Kehadiran</th>
                                <th class="py-3 px-4">Catatan Presensi</th>
                                <th class="py-3 px-4">Waktu Rekam</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/70">
                            @foreach ($session->studentAttendances as $att)
                                <tr>
                                    <td class="py-3 px-4 font-bold text-gray-900">
                                        {{ $att->student?->name ?? 'Siswa' }}
                                    </td>
                                    <td class="py-3 px-4">
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
                                    <td class="py-3 px-4 text-gray-500">
                                        {{ $att->note ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-400">
                                        {{ $att->recorded_at ? \Carbon\Carbon::parse($att->recorded_at)->format('H:i:s d/m/Y') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-owner-layout>
