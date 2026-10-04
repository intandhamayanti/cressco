<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - ' : '' }}{{ $tenant->name ?? config('app.name', 'Cressco') }} Tutor Portal</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    <!-- Scripts and Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-900 bg-slate-50" x-data="{ mobileSidebarOpen: false }">
    <div class="flex h-full min-h-screen overflow-hidden">
        
        <!-- Desktop Sidebar -->
        <aside class="hidden md:flex flex-col w-64 bg-white border-r border-gray-200/90 h-full font-sans select-none shrink-0 shadow-xs z-30">
            <!-- Workspace / Tenant Header -->
            <div class="p-4 border-b border-gray-100">
                <div class="flex items-center gap-3 p-2 rounded-xl bg-gray-50/70 border border-gray-200/80">
                    <div class="w-9 h-9 rounded-lg bg-terracotta-500 flex items-center justify-center text-white shrink-0 shadow-2xs font-bold text-sm">
                        {{ strtoupper(substr($tenant->name ?? 'PA', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-gray-900 truncate leading-tight">{{ $tenant->name ?? 'Prime Academy' }}</div>
                        <div class="text-[11px] text-terracotta-700 font-semibold truncate leading-tight">Portal Pengajar</div>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <div class="p-4 space-y-5 overflow-y-auto flex-1">
                <!-- Group: MAIN MENU -->
                <div class="space-y-1">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        Menu Utama
                    </div>

                    <!-- Dashboard -->
                    <x-cressco.nav-item
                        label="Dashboard"
                        icon="dashboard"
                        :href="route('tutor.dashboard')"
                        :active="request()->routeIs('tutor.dashboard*')"
                    />

                    <!-- Jadwal Mengajar -->
                    <x-cressco.nav-item
                        label="Jadwal Mengajar"
                        icon="calendar"
                        :href="route('tutor.schedules.index')"
                        :active="request()->routeIs('tutor.schedules*')"
                    />

                    <!-- Sesi Mengajar -->
                    <x-cressco.nav-item
                        label="Sesi Mengajar & Presensi"
                        icon="document"
                        :href="route('tutor.sessions.index')"
                        :active="request()->routeIs('tutor.sessions*')"
                    />

                    <!-- Penilaian Siswa -->
                    <x-cressco.nav-item
                        label="Penilaian Siswa"
                        icon="award"
                        :href="route('tutor.assessments.index')"
                        :active="request()->routeIs('tutor.assessments*')"
                    />

                    <!-- Riwayat Mengajar -->
                    <x-cressco.nav-item
                        label="Riwayat Mengajar"
                        icon="clock"
                        :href="route('tutor.history.index')"
                        :active="request()->routeIs('tutor.history*')"
                    />

                    <!-- Kelas Saya -->
                    <x-cressco.nav-item
                        label="Kelas yang Diampu"
                        icon="academic"
                        :href="route('tutor.classes.index')"
                        :active="request()->routeIs('tutor.classes*')"
                    />
                </div>
            </div>

            <!-- Sticky Bottom Tutor Info Card -->
            <div class="p-3 border-t border-gray-100 bg-white shrink-0">
                <div class="bg-terracotta-50/70 border border-terracotta-200/80 rounded-2xl p-3 space-y-1.5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-terracotta-950 leading-snug">Teaching Scope</h4>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-terracotta-100 text-terracotta-800 border border-terracotta-200">
                            Tutor
                        </span>
                    </div>
                    <p class="text-[11px] text-terracotta-800/80 leading-tight">
                        Akses khusus pada kelas, jadwal, dan sesi pengajaran yang Anda ampu.
                    </p>
                </div>
            </div>
        </aside>

        <!-- Mobile Drawer Backdrop & Sidebar -->
        <div x-show="mobileSidebarOpen"
             x-cloak
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs z-40 md:hidden"
             @click="mobileSidebarOpen = false"></div>

        <div x-show="mobileSidebarOpen"
             x-cloak
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 w-64 bg-white z-50 flex flex-col shadow-2xl md:hidden">
            
            <div class="p-4 flex items-center justify-between border-b border-gray-100">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-terracotta-500 flex items-center justify-center text-white shrink-0 font-bold text-xs">
                        {{ strtoupper(substr($tenant->name ?? 'PA', 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-gray-900 truncate leading-tight">{{ $tenant->name ?? 'Prime Academy' }}</div>
                        <div class="text-[10px] text-terracotta-700 font-semibold">Portal Pengajar</div>
                    </div>
                </div>
                <button type="button" @click="mobileSidebarOpen = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 cursor-pointer">
                    <x-cressco.icon-helper name="close" class="w-5 h-5" />
                </button>
            </div>

            <div class="p-4 space-y-5 overflow-y-auto flex-1">
                <div class="space-y-1">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Menu Utama</div>
                    
                    <x-cressco.nav-item label="Dashboard" icon="dashboard" :href="route('tutor.dashboard')" :active="request()->routeIs('tutor.dashboard*')" />
                    <x-cressco.nav-item label="Jadwal Mengajar" icon="calendar" :href="route('tutor.schedules.index')" :active="request()->routeIs('tutor.schedules*')" />
                    <x-cressco.nav-item label="Sesi Mengajar" icon="document" :href="route('tutor.sessions.index')" :active="request()->routeIs('tutor.sessions*')" />
                    <x-cressco.nav-item label="Penilaian Siswa" icon="award" :href="route('tutor.assessments.index')" :active="request()->routeIs('tutor.assessments*')" />
                    <x-cressco.nav-item label="Riwayat Mengajar" icon="clock" :href="route('tutor.history.index')" :active="request()->routeIs('tutor.history*')" />
                    <x-cressco.nav-item label="Kelas yang Diampu" icon="academic" :href="route('tutor.classes.index')" :active="request()->routeIs('tutor.classes*')" />
                </div>
            </div>
        </div>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto h-full">
            
            <!-- Topbar Header -->
            <header class="h-16 bg-white border-b border-gray-200/90 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 sticky top-0 z-20 shrink-0 font-sans">
                <!-- Mobile Menu Button & Breadcrumb Context -->
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <button type="button"
                            @click="mobileSidebarOpen = true"
                            class="md:hidden p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition focus:outline-hidden">
                        <x-cressco.icon-helper name="menu" class="w-5 h-5" />
                    </button>

                    <div class="flex items-center gap-1.5 text-xs text-gray-500 min-w-0">
                        <span class="text-gray-400 font-medium">Main Menu</span>
                        <span class="text-gray-300">/</span>
                        <span class="text-gray-900 font-bold truncate">{{ $breadcrumbSub ?? 'Dashboard' }}</span>
                    </div>
                </div>

                <!-- Right Action / User Profile & Header Actions -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0" x-data="{ userMenuOpen: false }">
                    
                    <!-- Share Button -->
                    <button type="button" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold text-gray-700 transition shadow-2xs cursor-pointer">
                        <x-cressco.icon-helper name="share" class="w-3.5 h-3.5 text-gray-500" />
                        <span>Share</span>
                    </button>

                    <!-- Notification Bell -->
                    <button type="button" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition relative focus:outline-hidden cursor-pointer" title="Notifikasi">
                        <x-cressco.icon-helper name="bell" class="w-4 h-4" />
                        <span class="w-2 h-2 rounded-full bg-terracotta-500 absolute top-2 right-2 border-2 border-white"></span>
                    </button>

                    <div class="relative">
                        <button type="button"
                                @click="userMenuOpen = !userMenuOpen"
                                class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-gray-50 transition border border-transparent hover:border-gray-200 focus:outline-hidden text-left cursor-pointer">
                            <div class="w-8 h-8 rounded-full bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-xs shrink-0 border border-terracotta-200/60">
                                {{ strtoupper(substr(auth()->user()->name ?? 'T', 0, 2)) }}
                            </div>
                            <div class="hidden sm:block text-left min-w-0">
                                <div class="text-xs font-bold text-gray-900 leading-tight">{{ auth()->user()->name ?? 'Tutor' }}</div>
                                <div class="text-[11px] text-terracotta-700 leading-tight font-medium">Tutor / Pengajar</div>
                            </div>
                            <x-cressco.icon-helper name="chevron-down" class="hidden sm:block w-3.5 h-3.5 text-gray-400" />
                        </button>

                        <!-- User Profile Dropdown -->
                        <div x-show="userMenuOpen"
                             x-cloak
                             @click.outside="userMenuOpen = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 rounded-2xl bg-white shadow-xl border border-gray-100 py-1.5 z-50 text-xs font-medium text-gray-700">
                            
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="font-bold text-gray-900 truncate">{{ auth()->user()->name ?? 'Tutor' }}</p>
                                <p class="text-[11px] text-gray-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                            </div>

                            <a href="{{ route('tutor.profile') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 text-gray-700 transition">
                                <x-cressco.icon-helper name="user" class="w-4 h-4 text-gray-500" />
                                <span>Profil Saya</span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 hover:bg-red-50 text-red-600 transition text-left cursor-pointer">
                                    <x-cressco.icon-helper name="log-out" class="w-4 h-4 text-red-500" />
                                    <span>Keluar (Logout)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Alerts & Flash Messages -->
            @if (session('success'))
                <div class="px-4 sm:px-6 lg:px-8 pt-4">
                    <x-cressco.alert type="success" title="Berhasil">
                        {{ session('success') }}
                    </x-cressco.alert>
                </div>
            @endif

            @if (session('error'))
                <div class="px-4 sm:px-6 lg:px-8 pt-4">
                    <x-cressco.alert type="error" title="Perhatian">
                        {{ session('error') }}
                    </x-cressco.alert>
                </div>
            @endif

            @if ($errors->any())
                <div class="px-4 sm:px-6 lg:px-8 pt-4">
                    <x-cressco.alert type="error" title="Terjadi Kesalahan Input">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-cressco.alert>
                </div>
            @endif

            <!-- Main Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>

    </div>

</body>
</html>
