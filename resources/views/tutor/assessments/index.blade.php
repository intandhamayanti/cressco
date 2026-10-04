<x-tutor-layout :tenant="$tenant" title="Penilaian Siswa">
    <x-slot:breadcrumbSub>Penilaian Siswa</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto font-sans">
        
        <!-- Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Penilaian Siswa</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola tugas, quiz, ujian, dan evaluasi hasil belajar siswa pada kelas yang Anda ampu.
                </p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-center">
                <a href="{{ route('tutor.assessments.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                    <x-cressco.icon-helper name="plus" class="w-4 h-4" />
                    <span>Buat Penilaian Baru</span>
                </a>
            </div>
        </div>

        <!-- Metric Summary Cards (Standard Cressco Design) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-cressco.project-card
                title="Total Penilaian"
                icon="award"
                iconColor="text-terracotta-500"
                value="{{ $totalAssessments }}"
                trend="Aktif"
                trendType="terracotta"
                subtitle="Total tugas, quiz & ujian dibuat"
                layout="4-row"
            />

            <x-cressco.project-card
                title="Rata-rata Nilai"
                icon="trending-up"
                iconColor="text-slate-600"
                value="{{ $overallAvg ? number_format($overallAvg, 1) : '-' }}"
                trend="Skor Rerata"
                trendType="neutral"
                subtitle="Rata-rata nilai seluruh siswa"
                layout="4-row"
            />

            <x-cressco.project-card
                title="Lembar Nilai Terisi"
                icon="check-circle"
                iconColor="text-terracotta-500"
                value="{{ $totalResultsSubmitted }}"
                trend="Tersubmit"
                trendType="positive"
                subtitle="Hasil penilaian siswa masuk"
                layout="4-row"
            />
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('tutor.assessments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div class="relative lg:col-span-2">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari judul penilaian, materi, kelas..."
                           class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl px-3 py-2 pl-9 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                        <x-cressco.icon-helper name="search" class="w-3.5 h-3.5" />
                    </div>
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

                <!-- Type Filter -->
                <div>
                    <select name="type"
                            class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl px-3 py-2 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer">
                        <option value="all">Semua Jenis</option>
                        <option value="tugas" {{ $selectedType === 'tugas' ? 'selected' : '' }}>Tugas</option>
                        <option value="quiz" {{ $selectedType === 'quiz' ? 'selected' : '' }}>Quiz</option>
                        <option value="ujian" {{ $selectedType === 'ujian' ? 'selected' : '' }}>Ujian</option>
                    </select>
                </div>

                <!-- Submit / Reset -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                        Filter
                    </button>
                    @if ($search || $selectedClass !== 'all' || $selectedType !== 'all')
                        <a href="{{ route('tutor.assessments.index') }}" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 text-xs font-bold transition" title="Reset Filter">
                            <x-cressco.icon-helper name="refresh" class="w-4 h-4" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Assessments Grid / List -->
        @if ($assessments->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($assessments as $assessment)
                    <div class="bg-white rounded-3xl border border-gray-200/80 p-5 shadow-xs hover:border-terracotta-300 hover:shadow-md transition flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <!-- Top Badge & Date -->
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider
                                    {{ $assessment->type === 'ujian' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($assessment->type === 'quiz' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                                    {{ ucfirst($assessment->type) }}
                                </span>
                                <span class="text-[11px] font-semibold text-gray-400">
                                    {{ $assessment->assessment_date ? \Carbon\Carbon::parse($assessment->assessment_date)->translatedFormat('d M Y') : '-' }}
                                </span>
                            </div>

                            <!-- Title & Class -->
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 leading-snug">
                                    {{ $assessment->name }}
                                </h3>
                                <div class="text-xs text-gray-500 font-medium mt-1">
                                    Kelas: <strong class="text-gray-800">{{ $assessment->class?->name ?? '-' }}</strong>
                                </div>
                                @if ($assessment->material)
                                    <p class="text-[11px] text-gray-600 line-clamp-1 mt-1 bg-gray-50 p-1.5 rounded-lg border border-gray-100">
                                        Materi: {{ $assessment->material }}
                                    </p>
                                @endif
                            </div>

                            <!-- Metrics Strip -->
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100 text-xs">
                                <div class="bg-gray-50/70 p-2.5 rounded-xl border border-gray-100">
                                    <span class="text-[10px] font-semibold text-gray-400 block uppercase">Max Nilai</span>
                                    <span class="font-extrabold text-gray-900">{{ number_format($assessment->max_score, 0) }}</span>
                                </div>
                                <div class="bg-gray-50/70 p-2.5 rounded-xl border border-gray-100">
                                    <span class="text-[10px] font-semibold text-gray-400 block uppercase">Rata-rata</span>
                                    <span class="font-extrabold text-terracotta-700">{{ $assessment->results_avg_score ? number_format($assessment->results_avg_score, 1) : '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action Footer -->
                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-gray-500">
                                {{ $assessment->results_count }} Siswa Dinilai
                            </span>
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('tutor.assessments.edit', $assessment->id) }}" class="p-1.5 rounded-xl hover:bg-gray-100 text-gray-500 transition" title="Edit Data Penilaian">
                                    <x-cressco.icon-helper name="edit" class="w-3.5 h-3.5" />
                                </a>
                                <a href="{{ route('tutor.assessments.show', $assessment->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-terracotta-50 text-terracotta-700 hover:bg-terracotta-100 font-bold text-xs transition shadow-2xs">
                                    <span>Input & Lihat Nilai</span>
                                    <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5" />
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($assessments->hasPages())
                <div class="pt-4">
                    {{ $assessments->links() }}
                </div>
            @endif
        @else
            <div class="bg-white rounded-3xl border border-gray-200/80 p-12 text-center space-y-3 shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-200 text-gray-400 mx-auto flex items-center justify-center">
                    <x-cressco.icon-helper name="award" class="w-6 h-6" />
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-gray-900">Belum ada penilaian siswa</h3>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">
                        Mulai buat tugas, quiz, atau ujian baru untuk kelas yang Anda ampu.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('tutor.assessments.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                        <x-cressco.icon-helper name="plus" class="w-3.5 h-3.5" />
                        <span>Buat Penilaian Sekarang</span>
                    </a>
                </div>
            </div>
        @endif

    </div>
</x-tutor-layout>
