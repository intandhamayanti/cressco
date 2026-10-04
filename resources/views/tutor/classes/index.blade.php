<x-tutor-layout :tenant="$tenant" title="Kelas yang Diampu">
    <x-slot:breadcrumbSub>Kelas yang Diampu</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto">
        
        <!-- Header & Stats -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Kelas yang Diampu</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Daftar rombongan belajar dan kelas bimbingan yang secara resmi ditugaskan kepada Anda.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-terracotta-50 border border-terracotta-200 text-xs font-bold text-terracotta-800">
                    <x-cressco.icon-helper name="folder" class="w-4 h-4 text-terracotta-600" />
                    <span>{{ $totalAssignedClasses }} Kelas Diampu</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-800">
                    <x-cressco.icon-helper name="academic" class="w-4 h-4 text-slate-600" />
                    <span>{{ $totalActiveStudents }} Total Siswa</span>
                </span>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <form method="GET" action="{{ route('tutor.classes.index') }}" class="flex-1 flex flex-col sm:flex-row sm:items-center gap-3">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <x-cressco.icon-helper name="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari nama kelas, mata pelajaran, tingkat..."
                           class="w-full text-xs bg-gray-50/70 border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-gray-900 placeholder-gray-400 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                </div>

                <!-- Branch Filter -->
                @if ($tutorBranches->count() > 1)
                    <select name="branch_id"
                            onchange="this.form.submit()"
                            class="text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl px-3 py-2.5 shadow-2xs focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer shrink-0">
                        <option value="all" {{ $selectedBranch === 'all' ? 'selected' : '' }}>Semua Cabang ({{ $tutorBranches->count() }})</option>
                        @foreach ($tutorBranches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranch === $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                    Cari
                </button>

                @if ($search || $selectedBranch !== 'all')
                    <a href="{{ route('tutor.classes.index') }}" class="text-xs text-rose-600 hover:text-rose-700 font-semibold px-2 py-1">
                        Reset
                    </a>
                @endif
            </form>

        </div>

        <!-- Class Cards Grid -->
        @if ($classes->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($classes as $class)
                    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs hover:border-terracotta-300 hover:shadow-md transition flex flex-col justify-between overflow-hidden">
                        
                        <div class="p-5 space-y-4">
                            <!-- Class Top Header -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="space-y-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-700">
                                            {{ $class->branch?->name ?? 'Cabang' }}
                                        </span>
                                        @if ($class->level)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-terracotta-50 text-terracotta-800 border border-terracotta-200">
                                                {{ $class->level }}
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900 truncate">
                                        {{ $class->name }}
                                    </h3>
                                    <p class="text-xs text-gray-500 font-medium">
                                        Mapel: <span class="text-gray-800 font-semibold">{{ $class->subject ?? 'Umum' }}</span>
                                    </p>
                                </div>

                                <div class="w-10 h-10 rounded-xl bg-terracotta-50 text-terracotta-700 flex items-center justify-center font-bold shrink-0 border border-terracotta-200/70">
                                    <x-cressco.icon-helper name="academic" class="w-5 h-5" />
                                </div>
                            </div>

                            <!-- Enrollment Capacity Bar -->
                            <div class="space-y-1.5 pt-2 border-t border-gray-100">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500">Kapasitas Siswa</span>
                                    <span class="font-bold text-gray-900">
                                        {{ $class->active_students_count }} / {{ $class->capacity ?? 0 }} Siswa
                                    </span>
                                </div>
                                @php
                                    $capacityPercent = $class->capacity > 0 ? min(100, round(($class->active_students_count / $class->capacity) * 100)) : 0;
                                @endphp
                                <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-terracotta-500 rounded-full transition-all duration-300" style="width: {{ $capacityPercent }}%"></div>
                                </div>
                            </div>

                            <!-- Regular Schedule Badges -->
                            @if ($class->schedules->isNotEmpty())
                                <div class="space-y-1 pt-2 border-t border-gray-100">
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jadwal Reguler</div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($class->schedules as $s)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-semibold bg-gray-50 text-gray-700 border border-gray-200">
                                                <x-cressco.icon-helper name="clock" class="w-3 h-3 text-gray-400" />
                                                <span>{{ $days[$s->day_of_week] ?? '' }} {{ substr($s->start_time, 0, 5) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Card Action Footer -->
                        <div class="p-3 bg-gray-50/70 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-[11px] text-gray-500">
                                Status: <strong class="text-slate-800">Aktif</strong>
                            </span>
                            <a href="{{ route('tutor.classes.show', $class) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold transition shadow-2xs">
                                <span>Detail Kelas & Siswa</span>
                                <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5" />
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if ($classes->hasPages())
                <div class="pt-4">
                    {{ $classes->links() }}
                </div>
            @endif
        @else
            <div class="bg-white rounded-3xl border border-gray-200/80 p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-200 text-gray-400 mx-auto flex items-center justify-center mb-3">
                    <x-cressco.icon-helper name="folder" class="w-6 h-6 text-gray-400" />
                </div>
                <h3 class="text-xs font-bold text-gray-800">Tidak Ada Kelas Ditemukan</h3>
                <p class="text-[11px] text-gray-500 mt-1 max-w-sm mx-auto">
                    Tidak ditemukan kelas yang cocok dengan kata kunci atau filter yang Anda pilih.
                </p>
            </div>
        @endif

    </div>
</x-tutor-layout>
