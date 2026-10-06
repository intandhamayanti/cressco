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
<body class="h-full font-sans antialiased text-gray-900 bg-slate-50" x-data="{ mobileSidebarOpen: false, aiQuizModalOpen: false }">
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
                <!-- Group 1: MENU UTAMA -->
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
                </div>

                <!-- Group 2: MENGAJAR -->
                <div class="space-y-1">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        Mengajar
                    </div>

                    <!-- Jadwal Mengajar -->
                    <x-cressco.nav-item
                        label="Jadwal Mengajar"
                        icon="calendar"
                        :href="route('tutor.schedules.index')"
                        :active="request()->routeIs('tutor.schedules*')"
                    />

                    <!-- Sesi Mengajar & Presensi -->
                    <x-cressco.nav-item
                        label="Sesi Mengajar & Presensi"
                        icon="user-check"
                        :href="route('tutor.sessions.index')"
                        :active="request()->routeIs('tutor.sessions*')"
                    />

                    <!-- Kelas yang Diampu -->
                    <x-cressco.nav-item
                        label="Kelas yang Diampu"
                        icon="book-open"
                        :href="route('tutor.classes.index')"
                        :active="request()->routeIs('tutor.classes*')"
                    />

                    <!-- Riwayat Mengajar -->
                    <x-cressco.nav-item
                        label="Riwayat Mengajar"
                        icon="clock"
                        :href="route('tutor.history.index')"
                        :active="request()->routeIs('tutor.history*')"
                    />
                </div>

                <!-- Group 3: SISWA -->
                <div class="space-y-1">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        Siswa
                    </div>

                    <!-- Penilaian Siswa -->
                    <x-cressco.nav-item
                        label="Penilaian Siswa"
                        icon="award"
                        :href="route('tutor.assessments.index')"
                        :active="request()->routeIs('tutor.assessments*')"
                    />
                </div>

                <!-- Group 4: TOOLS -->
                <div class="space-y-1">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        Tools
                    </div>

                    <!-- AI Quiz (Coming Soon) -->
                    <button type="button"
                            @click="aiQuizModalOpen = true"
                            class="w-full group flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all duration-150 select-none text-left cursor-pointer text-gray-600 hover:text-gray-900 hover:bg-gray-100/60 font-medium">
                        <span class="truncate">AI Quiz</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-terracotta-50 text-terracotta-700 border border-terracotta-200/60">
                            Coming Soon
                        </span>
                    </button>
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
                <!-- Group 1: MENU UTAMA -->
                <div class="space-y-1">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Menu Utama</div>
                    <x-cressco.nav-item label="Dashboard" icon="dashboard" :href="route('tutor.dashboard')" :active="request()->routeIs('tutor.dashboard*')" />
                </div>

                <!-- Group 2: MENGAJAR -->
                <div class="space-y-1">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Mengajar</div>
                    <x-cressco.nav-item label="Jadwal Mengajar" icon="calendar" :href="route('tutor.schedules.index')" :active="request()->routeIs('tutor.schedules*')" />
                    <x-cressco.nav-item label="Sesi Mengajar & Presensi" icon="user-check" :href="route('tutor.sessions.index')" :active="request()->routeIs('tutor.sessions*')" />
                    <x-cressco.nav-item label="Kelas yang Diampu" icon="book-open" :href="route('tutor.classes.index')" :active="request()->routeIs('tutor.classes*')" />
                    <x-cressco.nav-item label="Riwayat Mengajar" icon="clock" :href="route('tutor.history.index')" :active="request()->routeIs('tutor.history*')" />
                </div>

                <!-- Group 3: SISWA -->
                <div class="space-y-1">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Siswa</div>
                    <x-cressco.nav-item label="Penilaian Siswa" icon="award" :href="route('tutor.assessments.index')" :active="request()->routeIs('tutor.assessments*')" />
                </div>

                <!-- Group 4: TOOLS -->
                <div class="space-y-1">
                    <div class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tools</div>
                    <button type="button"
                            @click="aiQuizModalOpen = true; mobileSidebarOpen = false"
                            class="w-full group flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all duration-150 select-none text-left cursor-pointer text-gray-600 hover:text-gray-900 hover:bg-gray-100/60 font-medium">
                        <span class="truncate">AI Quiz</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-terracotta-50 text-terracotta-700 border border-terracotta-200/60">
                            Coming Soon
                        </span>
                    </button>
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

                <!-- Right Action / User Profile & Notifications -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0" x-data="{ userMenuOpen: false, notifOpen: false }">
                    
                    @php
                        $tutorUser = auth()->user();
                        $tutorTenant = $tenant ?? ($tutorUser ? $tutorUser->tenant : null);
                        
                        $tutorNotifs = collect();
                        if ($tutorUser && $tutorTenant) {
                            $todaySess = \App\Models\TeachingSession::query()
                                ->where('tenant_id', $tutorTenant->id)
                                ->where(function($q) use ($tutorUser) {
                                    $q->where('actual_tutor_id', $tutorUser->id)
                                      ->orWhere('scheduled_tutor_id', $tutorUser->id);
                                })
                                ->whereDate('session_date', now()->toDateString())
                                ->where('status', 'scheduled')
                                ->with(['classModel', 'branch'])
                                ->get();
                                
                            foreach ($todaySess as $ts) {
                                $tutorNotifs->push([
                                    'title' => 'Sesi Mengajar Hari Ini',
                                    'message' => ($ts->classModel?->name ?? 'Kelas') . ' (' . substr($ts->start_time, 0, 5) . ' - ' . substr($ts->end_time, 0, 5) . ' WIB)',
                                    'time' => 'Hari ini',
                                    'icon' => 'clock',
                                    'color' => 'amber',
                                    'url' => route('tutor.sessions.index'),
                                    'is_urgent' => true,
                                ]);
                            }
                            
                            $upcomingSess = \App\Models\TeachingSession::query()
                                ->where('tenant_id', $tutorTenant->id)
                                ->where(function($q) use ($tutorUser) {
                                    $q->where('actual_tutor_id', $tutorUser->id)
                                      ->orWhere('scheduled_tutor_id', $tutorUser->id);
                                })
                                ->whereDate('session_date', '>', now()->toDateString())
                                ->where('status', 'scheduled')
                                ->orderBy('session_date')
                                ->with(['classModel'])
                                ->take(3)
                                ->get();
                                
                            foreach ($upcomingSess as $us) {
                                $tutorNotifs->push([
                                    'title' => 'Sesi Mendatang',
                                    'message' => ($us->classModel?->name ?? 'Kelas') . ' • ' . \Carbon\Carbon::parse($us->session_date)->translatedFormat('d M Y'),
                                    'time' => \Carbon\Carbon::parse($us->session_date)->diffForHumans(),
                                    'icon' => 'calendar',
                                    'color' => 'blue',
                                    'url' => route('tutor.schedules.index'),
                                    'is_urgent' => false,
                                ]);
                            }
                        }
                        $unreadCount = $tutorNotifs->where('is_urgent', true)->count();
                    @endphp

                    <!-- Notification Bell Dropdown -->
                    <div class="relative">
                        <button type="button"
                                @click="notifOpen = !notifOpen; userMenuOpen = false"
                                class="p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition relative focus:outline-hidden cursor-pointer"
                                title="Notifikasi Tutor">
                            <x-cressco.icon-helper name="bell" class="w-5 h-5" />
                            @if($unreadCount > 0)
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-terracotta-500 ring-2 ring-white animate-pulse"></span>
                            @endif
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div x-show="notifOpen"
                             x-cloak
                             @click.outside="notifOpen = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white shadow-2xl border border-gray-100 py-2 z-50 text-xs font-sans">
                            
                            <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                <div class="font-bold text-gray-900">Notifikasi Jadwal & Sesi</div>
                                <span class="text-[10px] font-semibold text-terracotta-600 uppercase tracking-wider bg-terracotta-50 px-2 py-0.5 rounded-md border border-terracotta-200/60">Tutor Portal</span>
                            </div>

                            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                                @forelse($tutorNotifs as $notif)
                                    <a href="{{ $notif['url'] }}"
                                       @click="notifOpen = false"
                                       class="block p-3.5 hover:bg-gray-50 transition group">
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center {{ $notif['color'] === 'amber' ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600' }}">
                                                <x-cressco.icon-helper :name="$notif['icon']" class="w-4 h-4" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="font-bold text-gray-900 group-hover:text-terracotta-600 transition truncate">{{ $notif['title'] }}</span>
                                                    <span class="text-[10px] text-gray-400 shrink-0">{{ $notif['time'] }}</span>
                                                </div>
                                                <p class="text-gray-500 text-[11px] leading-snug mt-0.5">{{ $notif['message'] }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-6 text-center text-gray-400 text-xs">
                                        Tidak ada jadwal mendesak saat ini. Semua sesi mengajar Anda tercatat rapi.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

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

    <!-- AI Quiz Coming Soon Modal -->
    <div x-show="aiQuizModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="aiQuizModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
             @click="aiQuizModalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="aiQuizModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.outside="aiQuizModalOpen = false"
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 font-sans">
                
                <!-- Header Gradient -->
                <div class="bg-gradient-to-br from-terracotta-50 via-amber-50/40 to-white p-6 border-b border-gray-100">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-terracotta-500 to-amber-500 text-white flex items-center justify-center shadow-md shrink-0">
                                <x-cressco.icon-helper name="sparkles" class="w-6 h-6" />
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-terracotta-100/80 text-terracotta-800 border border-terracotta-200 mb-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-terracotta-500 animate-pulse"></span>
                                    Fitur Mendatang (Coming Soon)
                                </span>
                                <h3 class="text-lg font-bold text-gray-900 leading-tight">AI Quiz Generator & Latihan</h3>
                            </div>
                        </div>
                        <button type="button"
                                @click="aiQuizModalOpen = false"
                                class="rounded-xl p-1.5 text-gray-400 hover:text-gray-600 hover:bg-white/80 transition cursor-pointer">
                            <x-cressco.icon-helper name="close" class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 text-sm text-gray-600">
                    <p class="leading-relaxed">
                        Fitur <strong class="text-gray-900 font-semibold">AI Quiz Generator</strong> dirancang untuk membantu Tutor menyusun latihan soal, kuis interaktif, dan materi evaluasi siswa secara instan berbantuan kecerdasan buatan (AI).
                    </p>

                    <div class="space-y-2.5 bg-gray-50/80 rounded-2xl p-4 border border-gray-100">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-lg bg-terracotta-100 text-terracotta-700 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">
                                <x-cressco.icon-helper name="check" class="w-3.5 h-3.5" />
                            </div>
                            <div class="text-xs">
                                <strong class="text-gray-900 font-semibold block">Generasi Soal Otomatis</strong>
                                Buat soal pilihan ganda, esai, beserta kunci jawaban dan pembahasan dari topik materi dalam hitungan detik.
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-lg bg-terracotta-100 text-terracotta-700 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">
                                <x-cressco.icon-helper name="check" class="w-3.5 h-3.5" />
                            </div>
                            <div class="text-xs">
                                <strong class="text-gray-900 font-semibold block">Penyesuaian Tingkat Kesulitan</strong>
                                Sesuaikan level kesulitan soal (Mudah, Sedang, Sulit) sesuai dengan jenjang dan pemahaman siswa.
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-lg bg-terracotta-100 text-terracotta-700 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">
                                <x-cressco.icon-helper name="check" class="w-3.5 h-3.5" />
                            </div>
                            <div class="text-xs">
                                <strong class="text-gray-900 font-semibold block">Integrasi Langsung ke Evaluasi Siswa</strong>
                                Hasil kuis dan lembar kerja otomatis terhubung dengan modul Penilaian Siswa.
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 italic">
                        Fitur ini akan segera tersedia pada pembaruan rilis sistem platform selanjutnya.
                    </p>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                    <button type="button"
                            @click="aiQuizModalOpen = false"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-white font-bold text-xs shadow-xs hover:shadow transition cursor-pointer">
                        Mengerti, Terima Kasih
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
