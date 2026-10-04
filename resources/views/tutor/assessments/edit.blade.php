<x-tutor-layout :tenant="$tenant" title="Edit Data Penilaian">
    <x-slot:breadcrumbSub>Edit Penilaian</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-3xl mx-auto font-sans">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3">
                <a href="{{ route('tutor.assessments.show', $assessment->id) }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 transition shadow-2xs">
                    <x-cressco.icon-helper name="chevron-left" class="w-4 h-4" />
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">Edit Data Penilaian</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Ubah informasi judul, jenis, tanggal, atau nilai maksimal penilaian.</p>
                </div>
            </div>

            <!-- Delete Form -->
            <form method="POST" action="{{ route('tutor.assessments.destroy', $assessment->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data penilaian ini beserta seluruh nilai siswa di dalamnya?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition cursor-pointer">
                    Hapus Penilaian
                </button>
            </form>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-8 shadow-xs">
            <form method="POST" action="{{ route('tutor.assessments.update', $assessment->id) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Judul Penilaian & Jenis -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Judul Penilaian <span class="text-rose-500">*</span></label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $assessment->name) }}"
                               class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl p-3 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                        @error('name')
                            <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Jenis Penilaian <span class="text-rose-500">*</span></label>
                        <select name="type"
                                class="w-full text-xs font-semibold bg-gray-50/70 border border-gray-200 rounded-xl p-3 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer">
                            <option value="tugas" {{ old('type', $assessment->type) === 'tugas' ? 'selected' : '' }}>Tugas</option>
                            <option value="quiz" {{ old('type', $assessment->type) === 'quiz' ? 'selected' : '' }}>Quiz</option>
                            <option value="ujian" {{ old('type', $assessment->type) === 'ujian' ? 'selected' : '' }}>Ujian</option>
                        </select>
                        @error('type')
                            <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Tanggal & Max Score -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Tanggal Penilaian <span class="text-rose-500">*</span></label>
                        <input type="date"
                               name="assessment_date"
                               value="{{ old('assessment_date', $assessment->assessment_date ? $assessment->assessment_date->toDateString() : '') }}"
                               class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl p-3 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                        @error('assessment_date')
                            <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Nilai Maksimal <span class="text-rose-500">*</span></label>
                        <input type="number"
                               name="max_score"
                               step="1"
                               min="1"
                               max="1000"
                               value="{{ old('max_score', $assessment->max_score) }}"
                               class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl p-3 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                        @error('max_score')
                            <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Materi / Bab -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Materi / Bab yang Diujikan</label>
                    <input type="text"
                           name="material"
                           value="{{ old('material', $assessment->material) }}"
                           class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl p-3 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                    @error('material')
                        <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Catatan / Petunjuk -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Catatan / Instruksi Tambahan</label>
                    <textarea name="notes"
                              rows="3"
                              class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl p-3 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">{{ old('notes', $assessment->notes) }}</textarea>
                    @error('notes')
                        <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <a href="{{ route('tutor.assessments.show', $assessment->id) }}" class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-tutor-layout>
