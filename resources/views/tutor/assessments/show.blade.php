<x-tutor-layout :tenant="$tenant" title="Input & Detail Nilai Penilaian">
    <x-slot:breadcrumbSub>Input Nilai</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto font-sans">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3">
                <a href="{{ route('tutor.assessments.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 transition shadow-2xs">
                    <x-cressco.icon-helper name="chevron-left" class="w-4 h-4" />
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ $assessment->type === 'ujian' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($assessment->type === 'quiz' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                            {{ ucfirst($assessment->type) }}
                        </span>
                        <span class="text-xs text-gray-400">•</span>
                        <span class="text-xs font-bold text-gray-600">{{ $assessment->class?->name ?? 'Kelas' }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight mt-0.5">
                        {{ $assessment->name }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-center">
                <a href="{{ route('tutor.assessments.edit', $assessment->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition shadow-2xs">
                    <x-cressco.icon-helper name="edit" class="w-3.5 h-3.5 text-gray-500" />
                    <span>Edit Informasi Penilaian</span>
                </a>
            </div>
        </div>

        <!-- Metric & Detail Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <!-- Max Score -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-5 shadow-xs">
                <span class="text-[11px] font-semibold text-gray-500 block uppercase tracking-wider">Nilai Maksimal</span>
                <span class="text-2xl font-extrabold text-gray-900 mt-1 block">{{ number_format($assessment->max_score, 0) }}</span>
                <span class="text-[11px] text-gray-400 mt-1 block">Skala Penilaian</span>
            </div>

            <!-- Average Score -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-5 shadow-xs">
                <span class="text-[11px] font-semibold text-gray-500 block uppercase tracking-wider">Rata-rata Kelas</span>
                <span class="text-2xl font-extrabold text-terracotta-700 mt-1 block">{{ $avgScore ? number_format($avgScore, 1) : '-' }}</span>
                <span class="text-[11px] text-gray-400 mt-1 block">Dari {{ $assessment->results->count() }} siswa dinilai</span>
            </div>

            <!-- Highest Score -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-5 shadow-xs">
                <span class="text-[11px] font-semibold text-gray-500 block uppercase tracking-wider">Nilai Tertinggi</span>
                <span class="text-2xl font-extrabold text-emerald-600 mt-1 block">{{ $highestScore !== null ? number_format($highestScore, 1) : '-' }}</span>
                <span class="text-[11px] text-gray-400 mt-1 block">Capaian Maksimum</span>
            </div>

            <!-- Lowest Score -->
            <div class="bg-white rounded-3xl border border-gray-200/80 p-5 shadow-xs">
                <span class="text-[11px] font-semibold text-gray-500 block uppercase tracking-wider">Nilai Terendah</span>
                <span class="text-2xl font-extrabold text-rose-600 mt-1 block">{{ $lowestScore !== null ? number_format($lowestScore, 1) : '-' }}</span>
                <span class="text-[11px] text-gray-400 mt-1 block">Capaian Minimum</span>
            </div>
        </div>

        <!-- Details Strip if Material/Notes Exist -->
        @if ($assessment->material || $assessment->notes)
            <div class="bg-white rounded-3xl border border-gray-200/80 p-5 shadow-xs space-y-2">
                @if ($assessment->material)
                    <div class="text-xs">
                        <span class="font-bold text-gray-700">Materi / Bab:</span>
                        <span class="text-gray-600 ml-1">{{ $assessment->material }}</span>
                    </div>
                @endif
                @if ($assessment->notes)
                    <div class="text-xs">
                        <span class="font-bold text-gray-700">Catatan:</span>
                        <span class="text-gray-600 ml-1">{{ $assessment->notes }}</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Student Score Input Table Form -->
        <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/40">
                <div>
                    <h2 class="text-sm font-extrabold text-gray-900">Daftar Nilai Siswa</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Input atau perbarui skor evaluasi untuk siswa di kelas ini (0 - {{ number_format($assessment->max_score, 0) }})</p>
                </div>

                <span class="text-xs font-semibold text-gray-500">
                    {{ $allStudentRows->count() }} Total Siswa
                </span>
            </div>

            @if ($allStudentRows->isNotEmpty())
                <form method="POST" action="{{ route('tutor.assessments.results.store', $assessment->id) }}">
                    @csrf

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <th class="p-4 w-12 text-center">No</th>
                                    <th class="p-4">Nama Siswa</th>
                                    <th class="p-4 w-44 text-center">Nilai (0 - {{ number_format($assessment->max_score, 0) }})</th>
                                    <th class="p-4">Catatan Evaluasi / Umpan Balik</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                @foreach ($allStudentRows as $index => $item)
                                    @php
                                        $student = $item['student'];
                                        $scoreVal = old("results.{$index}.score", $item['score']);
                                        $noteVal = old("results.{$index}.notes", $item['notes']);
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <!-- No -->
                                        <td class="p-4 text-center text-gray-400 font-medium">
                                            {{ $loop->iteration }}
                                        </td>

                                        <!-- Nama Siswa -->
                                        <td class="p-4">
                                            <input type="hidden" name="results[{{ $index }}][student_id]" value="{{ $student->id }}">
                                            <div class="font-bold text-gray-900">{{ $student->name }}</div>
                                            <div class="text-[11px] text-gray-400 font-medium mt-0.5">
                                                NIS: {{ $student->nis ?? '-' }} • {{ $student->grade ?? '-' }}
                                            </div>
                                        </td>

                                        <!-- Input Nilai -->
                                        <td class="p-4 text-center">
                                            <div class="max-w-[140px] mx-auto">
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       max="{{ $assessment->max_score }}"
                                                       name="results[{{ $index }}][score]"
                                                       value="{{ $scoreVal }}"
                                                       placeholder="0.00"
                                                       class="w-full text-center text-xs font-bold bg-gray-50/80 border border-gray-200 rounded-xl p-2.5 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                            </div>
                                            @error("results.{$index}.score")
                                                <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </td>

                                        <!-- Catatan / Umpan Balik -->
                                        <td class="p-4">
                                            <input type="text"
                                                   name="results[{{ $index }}][notes]"
                                                   value="{{ $noteVal }}"
                                                   placeholder="Contoh: Pemahaman konsep sangat baik, perlu teliti di hitungan aljabar"
                                                   class="w-full text-xs bg-gray-50/80 border border-gray-200 rounded-xl px-3 py-2 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                            @error("results.{$index}.notes")
                                                <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Submit Action -->
                    <div class="p-5 border-t border-gray-100 bg-gray-50/60 flex items-center justify-end gap-3">
                        <button type="submit" class="px-7 py-2.5 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center gap-2">
                            <x-cressco.icon-helper name="check" class="w-4 h-4" />
                            <span>Simpan Nilai Siswa</span>
                        </button>
                    </div>

                </form>
            @else
                <div class="p-12 text-center text-xs text-gray-500">
                    Belum ada siswa yang terdaftar dalam kelas ini.
                </div>
            @endif
        </div>

    </div>
</x-tutor-layout>
