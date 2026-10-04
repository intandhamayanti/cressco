<x-tutor-layout :tenant="$tenant" title="Sesi Mengajar & Presensi">
    <x-slot:breadcrumbSub>Sesi Mengajar</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto">
        
        <!-- Header & Top Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Sesi Mengajar & Presensi</h1>
                <p class="text-xs text-gray-500">Kelola riwayat sesi mengajar, input materi pembelajaran, dan isi presensi kehadiran siswa.</p>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-center">
                <a href="{{ route('tutor.schedules.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-xl shadow-2xs transition">
                    <x-cressco.icon-helper name="calendar" class="w-4 h-4 text-gray-500" />
                    <span>Lihat Jadwal Planner</span>
                </a>
            </div>
        </div>

        <!-- Filter Toolbar -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('tutor.sessions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div class="relative">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari materi / ruang / kelas..."
                           class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl px-3 py-2 pl-9 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                        <x-cressco.icon-helper name="search" class="w-3.5 h-3.5" />
                    </div>
                </div>

                <!-- Branch Filter -->
                <div>
                    <select name="branch_id"
                            class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl px-3 py-2 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer">
                        <option value="all">Semua Cabang</option>
                        @foreach ($tutorBranches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranch === $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Class Filter -->
                <div>
                    <select name="class_id"
                            class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl px-3 py-2 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer">
                        <option value="all">Semua Kelas</option>
                        @foreach ($tutorClasses as $cls)
                            <option value="{{ $cls->id }}" {{ $selectedClass === $cls->id ? 'selected' : '' }}>
                                {{ $cls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <select name="status"
                            class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl px-3 py-2 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer">
                        <option value="all">Semua Status</option>
                        <option value="scheduled" {{ $selectedStatus === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                        <option value="completed" {{ $selectedStatus === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ $selectedStatus === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <!-- Submit / Reset Action -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs transition shadow-xs cursor-pointer">
                        Filter
                    </button>
                    @if ($search || $selectedBranch !== 'all' || $selectedClass !== 'all' || $selectedStatus !== 'all')
                        <a href="{{ route('tutor.sessions.index') }}" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 text-xs font-bold transition" title="Reset Filter">
                            <x-cressco.icon-helper name="refresh" class="w-4 h-4" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Sessions List Table -->
        <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
            @if ($sessions->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="p-4">Tanggal & Waktu</th>
                                <th class="p-4">Kelas & Cabang</th>
                                <th class="p-4">Tutor Pengajar</th>
                                <th class="p-4">Materi Tercover</th>
                                <th class="p-4">Status Sesi</th>
                                <th class="p-4 text-center">Presensi Siswa</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @foreach ($sessions as $session)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <!-- Tanggal & Waktu -->
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900">
                                            {{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d M Y') : '-' }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 font-medium mt-0.5">
                                            {{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB
                                        </div>
                                    </td>

                                    <!-- Kelas & Cabang -->
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900">
                                            {{ $session->classModel?->name ?? 'Kelas' }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1.5">
                                            <span>{{ $session->branch?->name ?? '-' }}</span>
                                            <span>•</span>
                                            <span>Ruang: {{ $session->room ?? '-' }}</span>
                                        </div>
                                    </td>

                                    <!-- Tutor Pengajar -->
                                    <td class="p-4">
                                        <div class="font-semibold text-gray-900">
                                            {{ $session->actualTutor?->name ?? ($session->scheduledTutor?->name ?? '-') }}
                                        </div>
                                    </td>

                                    <!-- Materi -->
                                    <td class="p-4 max-w-xs">
                                        @if ($session->material)
                                            <p class="text-xs text-gray-800 line-clamp-1 font-medium">{{ $session->material }}</p>
                                        @else
                                            <span class="text-[11px] text-gray-400 italic">Belum ada materi</span>
                                        @endif
                                    </td>

                                    <!-- Status Sesi -->
                                    <td class="p-4">
                                        @if ($session->status === 'completed')
                                            <x-cressco.badge variant="success" dot>Selesai</x-cressco.badge>
                                        @elseif ($session->status === 'cancelled')
                                            <x-cressco.badge variant="danger" dot>Dibatalkan</x-cressco.badge>
                                        @else
                                            <x-cressco.badge variant="info" dot>Terjadwal</x-cressco.badge>
                                        @endif
                                    </td>

                                    <!-- Presensi Siswa -->
                                    <td class="p-4 text-center">
                                        @if ($session->student_attendances_count > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-800">
                                                {{ $session->student_attendances_count }} Tercatat
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                Belum Diisi
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="p-4 text-right">
                                        <a href="{{ route('tutor.sessions.show', $session->id) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-terracotta-50 text-terracotta-700 font-bold hover:bg-terracotta-100 transition shadow-2xs">
                                            <span>Kelola / Presensi</span>
                                            <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($sessions->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $sessions->links() }}
                    </div>
                @endif
            @else
                <div class="p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 mx-auto flex items-center justify-center">
                        <x-cressco.icon-helper name="document" class="w-6 h-6" />
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-gray-900">Belum ada sesi mengajar</h4>
                        <p class="text-xs text-gray-500">Tidak ada sesi mengajar yang sesuai dengan filter pencarian Anda.</p>
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-tutor-layout>
