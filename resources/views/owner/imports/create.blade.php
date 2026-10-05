<x-owner-layout :tenant="$tenant" :title="$title">
    <x-slot:breadcrumbSub>{{ $title }}</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-4xl mx-auto">
        <!-- Back Navigation & Header -->
        <div class="flex items-center gap-3">
            <a href="{{ $type === 'students' ? route('owner.students.index') : route('owner.tutors.index') }}" class="p-2 rounded-xl bg-white border border-gray-200/80 text-gray-600 hover:text-gray-900 transition shadow-2xs">
                <x-cressco.icon-helper name="arrow-left" class="w-4 h-4" />
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $title }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
            </div>
        </div>

        <!-- Flow Explanation Card (Step 1 -> Step 2 -> Step 3 -> Step 4) -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Langkah Onboarding Import Data</h2>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                <div class="p-3.5 rounded-xl bg-gray-50/80 border border-gray-100 space-y-1">
                    <span class="w-5 h-5 rounded-full bg-terracotta-500 text-white font-bold flex items-center justify-center text-[10px]">1</span>
                    <div class="font-bold text-gray-900 pt-1">Download Template</div>
                    <p class="text-[11px] text-gray-500">Unduh template CSV yang sudah sesuai format kolom sistem.</p>
                </div>

                <div class="p-3.5 rounded-xl bg-gray-50/80 border border-gray-100 space-y-1">
                    <span class="w-5 h-5 rounded-full bg-terracotta-500 text-white font-bold flex items-center justify-center text-[10px]">2</span>
                    <div class="font-bold text-gray-900 pt-1">Isi Data Spreadsheet</div>
                    <p class="text-[11px] text-gray-500">Buka di Excel / Google Sheets, isi data, dan simpan sebagai CSV (.csv).</p>
                </div>

                <div class="p-3.5 rounded-xl bg-gray-50/80 border border-gray-100 space-y-1">
                    <span class="w-5 h-5 rounded-full bg-terracotta-500 text-white font-bold flex items-center justify-center text-[10px]">3</span>
                    <div class="font-bold text-gray-900 pt-1">Upload File</div>
                    <p class="text-[11px] text-gray-500">Unggah file CSV ke form di bawah untuk divalidasi sistem.</p>
                </div>

                <div class="p-3.5 rounded-xl bg-gray-50/80 border border-gray-100 space-y-1">
                    <span class="w-5 h-5 rounded-full bg-terracotta-500 text-white font-bold flex items-center justify-center text-[10px]">4</span>
                    <div class="font-bold text-gray-900 pt-1">Preview & Konfirmasi</div>
                    <p class="text-[11px] text-gray-500">Review hasil validasi sebelum data benar-benar dimasukkan.</p>
                </div>
            </div>

            <!-- Download Template Button -->
            <div class="pt-2 flex items-center justify-between border-t border-gray-100">
                <div class="text-xs text-gray-500">
                    Format file harus berupa CSV (Comma Separated Values) dengan encoding UTF-8.
                </div>
                <a href="{{ route('owner.imports.template', $type) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold shadow-2xs transition">
                    <x-cressco.icon-helper name="document" class="w-4 h-4" />
                    <span>Download Template CSV</span>
                </a>
            </div>
        </div>

        <!-- Upload Form Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Unggah File CSV</h2>

            <form method="POST" action="{{ route('owner.imports.preview', $type) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="border-2 border-dashed border-gray-200 hover:border-terracotta-400 rounded-2xl p-8 text-center transition cursor-pointer bg-gray-50/50">
                    <input type="file" name="file" id="fileInput" accept=".csv,text/csv" required class="hidden" onchange="document.getElementById('fileNameDisplay').textContent = this.files[0]?.name || '';" />
                    <label for="fileInput" class="cursor-pointer block space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-terracotta-50 text-terracotta-600 flex items-center justify-center mx-auto">
                            <x-cressco.icon-helper name="document" class="w-6 h-6" />
                        </div>
                        <div class="text-sm font-bold text-gray-900">Klik untuk memilih file CSV</div>
                        <p class="text-xs text-gray-400">Maksimal ukuran file 5 MB (.csv)</p>
                        <div id="fileNameDisplay" class="text-xs font-bold text-terracotta-600 pt-2"></div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <a href="{{ $type === 'students' ? route('owner.students.index') : route('owner.tutors.index') }}" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition">
                        Batal
                    </a>
                    <x-cressco.button variant="primary" type="submit">
                        Lanjut ke Preview Validasi
                    </x-cressco.button>
                </div>
            </form>
        </div>
    </div>
</x-owner-layout>
