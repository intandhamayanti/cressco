<x-owner-layout :tenant="$tenant" :title="$title">
    <x-slot:breadcrumbSub>{{ $title }}</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-5xl mx-auto">
        <!-- Back Navigation & Header -->
        <div class="flex items-center gap-3">
            <a href="{{ route('owner.imports.create', $type) }}" class="p-2 rounded-xl bg-white border border-gray-200/80 text-gray-600 hover:text-gray-900 transition shadow-2xs">
                <x-cressco.icon-helper name="arrow-left" class="w-4 h-4" />
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $title }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Periksa baris data yang valid dan baris yang mengalami galat sebelum disimpan.</p>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-1">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Data Siap Diimpor</span>
                <div class="text-2xl font-bold text-emerald-900">{{ count($validRows) }} Baris Valid</div>
            </div>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 space-y-1">
                <span class="text-xs font-bold text-rose-800 uppercase tracking-wider">Data Gagal Validasi</span>
                <div class="text-2xl font-bold text-rose-900">{{ count($errors) }} Baris Error</div>
            </div>
        </div>

        <!-- Error List if Any -->
        @if (!empty($errors))
            <div class="bg-white p-5 rounded-2xl border border-rose-200 shadow-xs space-y-3">
                <div class="flex items-center gap-2 text-rose-700 font-bold text-sm">
                    <x-cressco.icon-helper name="alert-triangle" class="w-4 h-4" />
                    <span>Daftar Baris yang Dilewati (Error)</span>
                </div>
                <div class="divide-y divide-gray-100 text-xs">
                    @foreach ($errors as $err)
                        <div class="py-2 flex items-center justify-between">
                            <span class="font-medium text-gray-700">Baris ke-{{ $err['row'] }} ({{ $err['name'] }})</span>
                            <span class="text-rose-600 font-semibold">{{ $err['reason'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Valid Rows Preview Table -->
        <div class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-gray-200/75 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Preview Data yang Akan Diimpor</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Hanya baris valid yang akan disimpan ke database</p>
                </div>
            </div>

            @if (empty($validRows))
                <div class="p-8 text-center text-xs text-rose-600 font-medium">
                    Tidak ada baris data yang valid untuk diimpor. Silakan perbaiki file CSV Anda lalu unggah ulang.
                </div>
            @else
                <div class="overflow-x-auto max-h-96">
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-700 uppercase tracking-wider sticky top-0">
                            <tr>
                                @if ($type === 'students')
                                    <th class="py-3 px-4">Nama Siswa</th>
                                    <th class="py-3 px-4">Cabang</th>
                                    <th class="py-3 px-4">Gender</th>
                                    <th class="py-3 px-4">Telepon / WA</th>
                                    <th class="py-3 px-4">Orang Tua</th>
                                    <th class="py-3 px-4">Status</th>
                                @else
                                    <th class="py-3 px-4">Nama Tutor</th>
                                    <th class="py-3 px-4">Email</th>
                                    <th class="py-3 px-4">Telepon</th>
                                    <th class="py-3 px-4">Skema Honor</th>
                                    <th class="py-3 px-4">Status</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/70">
                            @foreach ($validRows as $row)
                                <tr>
                                    @if ($type === 'students')
                                        <td class="py-3 px-4 font-bold text-gray-900">{{ $row['name'] }}</td>
                                        <td class="py-3 px-4">{{ $row['branch_name'] }}</td>
                                        <td class="py-3 px-4">{{ $row['gender'] }}</td>
                                        <td class="py-3 px-4">{{ $row['phone'] ?: '-' }}</td>
                                        <td class="py-3 px-4">{{ $row['parent_name'] ?: '-' }}</td>
                                        <td class="py-3 px-4"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Siap diimpor</span></td>
                                    @else
                                        <td class="py-3 px-4 font-bold text-gray-900">{{ $row['name'] }}</td>
                                        <td class="py-3 px-4">{{ $row['email'] }}</td>
                                        <td class="py-3 px-4">{{ $row['phone'] ?: '-' }}</td>
                                        <td class="py-3 px-4 font-semibold text-terracotta-700">{{ $row['honor_scheme_name'] }}</td>
                                        <td class="py-3 px-4"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Siap diimpor</span></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Action Form -->
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('owner.imports.create', $type) }}" class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold shadow-2xs transition">
                Upload Ulang File Lain
            </a>

            @if (!empty($validRows))
                <form method="POST" action="{{ route('owner.imports.commit', $type) }}">
                    @csrf
                    <x-cressco.button variant="primary" type="submit">
                        Konfirmasi & Impor {{ count($validRows) }} Data
                    </x-cressco.button>
                </form>
            @endif
        </div>
    </div>
</x-owner-layout>
