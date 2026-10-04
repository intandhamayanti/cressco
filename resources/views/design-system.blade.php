<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cressco UI Kit - Design System & Component Library</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google / Bunny Fonts: DM Sans -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Vite Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#FAFBFB] text-gray-900 selection:bg-terracotta-500 selection:text-white min-h-screen flex"
      x-data="{ mobileSidebarOpen: false }">

    @php
        $activeSection = $activeSection ?? request()->query('page', request()->query('tab', request()->query('section', 'button')));
        if ($activeSection === 'chart-card') {
            $activeSection = 'card-chart';
        }
        if (!in_array($activeSection, ['color', 'typography', 'text-field', 'button', 'navigation', 'card-chart', 'other'])) {
            $activeSection = 'button';
        }
    @endphp

    <!-- DESKTOP INTERNAL UI KIT SIDEBAR -->
    <div class="hidden lg:block">
        <x-cressco.ui-kit-sidebar :activeSection="$activeSection" />
    </div>

    <!-- MOBILE SIDEBAR OVERLAY & DRAWER -->
    <div x-show="mobileSidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden"
         @click="mobileSidebarOpen = false"
         style="display: none;">
    </div>

    <div x-show="mobileSidebarOpen"
         x-transition:enter="transition ease-in-out duration-200 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-50 w-64 bg-white lg:hidden shadow-xl"
         style="display: none;">
        <x-cressco.ui-kit-sidebar :activeSection="$activeSection" />
    </div>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden min-h-screen">

        <!-- Mobile Top Navbar -->
        <header class="lg:hidden bg-white border-b border-gray-200 sticky top-0 z-30 shadow-xs">
            <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button type="button" @click="mobileSidebarOpen = true" class="p-1.5 rounded-lg text-gray-600 hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <img src="{{ asset('images/logo.png') }}" alt="Cressco Logo" class="h-6 w-auto">
                    <span class="font-bold text-gray-900 text-sm">Documentation</span>
                </div>
                <span class="text-xs font-semibold uppercase px-2.5 py-0.5 rounded-md bg-terracotta-50 text-terracotta-700 border border-terracotta-200">
                    {{ str_replace('-', ' ', $activeSection) }}
                </span>
            </div>
        </header>

        <!-- Top Status Bar for Desktop (Aligned with max-w-7xl Content) -->
        <div class="hidden lg:block border-b border-gray-200 bg-white sticky top-0 z-10 shadow-2xs">
            <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <a href="/design-system" class="font-bold text-gray-900 hover:text-terracotta-600 transition">Documentation</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-500 font-medium capitalize">{{ in_array($activeSection, ['color', 'typography']) ? 'Style Guide' : 'Components' }}</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-terracotta-600 font-semibold capitalize">{{ str_replace('-', ' ', $activeSection) }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Figma Synced
                    </span>
                    <span class="text-xs font-medium text-gray-400">Cressco UI Kit</span>
                </div>
            </div>
        </div>

        <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10 flex-1">

            <!-- ========================================================================= -->
            <!-- 1. BUTTON COMPONENT SHOWCASE (Figma Reference Image 1)                   -->
            <!-- ========================================================================= -->
            @if ($activeSection === 'button')
            <div class="space-y-10 animate-fadeIn">
                <!-- Clean Solid Terracotta Hero Banner matching Figma -->
                <div class="bg-terracotta-500 rounded-2xl p-8 sm:p-10 text-white relative overflow-hidden shadow-md">
                    <div class="relative z-10 max-w-2xl space-y-3">
                        <span class="inline-block bg-white/20 backdrop-blur-xs text-white text-xs font-semibold px-3 py-1 rounded-full border border-white/25 uppercase tracking-wider">
                            Component
                        </span>
                        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">Button</h1>
                        <p class="text-terracotta-100 text-sm sm:text-base leading-relaxed">
                            Guidelines to delve into the Badge to learn how to utilize the component the best.
                        </p>
                    </div>
                </div>

                <!-- SECTION 1: BUTTON GRID MATRIX (Primary & Outline, All Sizes, States, Icons) -->
                <div class="space-y-4">
                    <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Button</h2>

                    <div class="bg-white border-2 border-dashed border-indigo-200/80 rounded-2xl p-6 sm:p-10 shadow-xs overflow-x-auto">
                        <div class="min-w-[860px] space-y-8">
                            
                            <!-- PRIMARY SOLID TERRACOTTA BUTTONS -->
                            <div class="space-y-4">
                                <!-- Size: XL (Row 1) -->
                                <div class="grid grid-cols-6 gap-6 items-center">
                                    <x-cressco.button variant="primary" size="xl" leadingIcon="arrow-left" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="xl" leadingIcon="arrow-left">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="xl" :disabled="true" leadingIcon="arrow-left">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="xl" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="primary" size="xl" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="primary" size="xl" :iconOnly="true" :disabled="true" leadingIcon="arrow-left" />
                                </div>

                                <!-- Size: LG (Row 2) -->
                                <div class="grid grid-cols-6 gap-6 items-center">
                                    <x-cressco.button variant="primary" size="lg" leadingIcon="arrow-left" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="lg" leadingIcon="arrow-left">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="lg" :disabled="true" leadingIcon="arrow-left">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="lg" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="primary" size="lg" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="primary" size="lg" :iconOnly="true" :disabled="true" leadingIcon="arrow-left" />
                                </div>

                                <!-- Size: MD (Row 3) -->
                                <div class="grid grid-cols-6 gap-6 items-center">
                                    <x-cressco.button variant="primary" size="md" leadingIcon="arrow-left" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="md" leadingIcon="arrow-left">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="md" :disabled="true" leadingIcon="arrow-left">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="md" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="primary" size="md" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="primary" size="md" :iconOnly="true" :disabled="true" leadingIcon="arrow-left" />
                                </div>

                                <!-- Size: SM (Row 4) -->
                                <div class="grid grid-cols-6 gap-6 items-center">
                                    <x-cressco.button variant="primary" size="sm" leadingIcon="arrow-left" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="sm" leadingIcon="arrow-left">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="sm" :disabled="true" leadingIcon="arrow-left">Button</x-cressco.button>
                                    <x-cressco.button variant="primary" size="sm" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="primary" size="sm" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="primary" size="sm" :iconOnly="true" :disabled="true" leadingIcon="arrow-left" />
                                </div>
                            </div>

                            <!-- SECONDARY / OUTLINE BUTTONS -->
                            <div class="space-y-4 pt-4 border-t border-gray-100">
                                <!-- Size: XL (Row 5) -->
                                <div class="grid grid-cols-6 gap-6 items-center">
                                    <x-cressco.button variant="outline" size="xl" leadingIcon="arrow-left" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="xl" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="xl" :disabled="true" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="xl" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="outline" size="xl" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="outline" size="xl" :iconOnly="true" :disabled="true" leadingIcon="arrow-left" />
                                </div>

                                <!-- Size: LG (Row 6) -->
                                <div class="grid grid-cols-6 gap-6 items-center">
                                    <x-cressco.button variant="outline" size="lg" leadingIcon="arrow-left" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="lg" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="lg" :disabled="true" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="lg" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="outline" size="lg" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="outline" size="lg" :iconOnly="true" :disabled="true" leadingIcon="arrow-left" />
                                </div>

                                <!-- Size: MD (Row 7) -->
                                <div class="grid grid-cols-6 gap-6 items-center">
                                    <x-cressco.button variant="outline" size="md" leadingIcon="arrow-left" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="md" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="md" :disabled="true" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="md" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="outline" size="md" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="outline" size="md" :iconOnly="true" :disabled="true" leadingIcon="arrow-left" />
                                </div>

                                <!-- Size: SM (Row 8) -->
                                <div class="grid grid-cols-6 gap-6 items-center">
                                    <x-cressco.button variant="outline" size="sm" leadingIcon="arrow-left" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="sm" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="sm" :disabled="true" trailingIcon="arrow-right">Button</x-cressco.button>
                                    <x-cressco.button variant="outline" size="sm" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="outline" size="sm" :iconOnly="true" leadingIcon="arrow-left" />
                                    <x-cressco.button variant="outline" size="sm" :iconOnly="true" :disabled="true" leadingIcon="arrow-left" />
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- SECTION 2: CHECK BOX, RADIO BUTTON, TOGGLE BUTTON -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- CHECK BOX SECTION -->
                    <div class="space-y-4">
                        <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Check Box</h2>
                        <div class="bg-white border-2 border-dashed border-indigo-200/80 rounded-2xl p-6 shadow-xs flex flex-col gap-4">
                            <!-- Matrix of Checkboxes from Figma: Sizes & States -->
                            <div class="grid grid-cols-4 gap-4 items-center">
                                <x-cressco.checkbox size="md" state="default" />
                                <x-cressco.checkbox size="md" state="checked" />
                                <x-cressco.checkbox size="md" state="indeterminate" />
                                <x-cressco.checkbox size="md" state="disabled" />
                            </div>
                            <div class="grid grid-cols-4 gap-4 items-center">
                                <x-cressco.checkbox size="md" state="checked" />
                                <x-cressco.checkbox size="md" state="checked" />
                                <x-cressco.checkbox size="md" state="indeterminate" />
                                <x-cressco.checkbox size="md" state="disabled-checked" />
                            </div>
                            <div class="grid grid-cols-4 gap-4 items-center">
                                <x-cressco.checkbox size="sm" state="default" />
                                <x-cressco.checkbox size="sm" state="checked" />
                                <x-cressco.checkbox size="sm" state="indeterminate" />
                                <x-cressco.checkbox size="sm" state="disabled" />
                            </div>
                            <div class="grid grid-cols-4 gap-4 items-center">
                                <x-cressco.checkbox size="sm" state="checked" />
                                <x-cressco.checkbox size="sm" state="checked" />
                                <x-cressco.checkbox size="sm" state="indeterminate" />
                                <x-cressco.checkbox size="sm" state="disabled-checked" />
                            </div>
                        </div>
                    </div>

                    <!-- RADIO BUTTON SECTION -->
                    <div class="space-y-4">
                        <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Radio Button</h2>
                        <div class="bg-white border-2 border-dashed border-indigo-200/80 rounded-2xl p-6 shadow-xs flex flex-col gap-4">
                            <!-- Matrix of Radio Buttons from Figma -->
                            <div class="grid grid-cols-4 gap-4 items-center">
                                <x-cressco.radio size="md" state="default" />
                                <x-cressco.radio size="md" state="selected" />
                                <x-cressco.radio size="md" state="selected" />
                                <x-cressco.radio size="md" state="disabled" />
                            </div>
                            <div class="grid grid-cols-4 gap-4 items-center">
                                <x-cressco.radio size="md" state="selected" />
                                <x-cressco.radio size="md" state="selected" />
                                <x-cressco.radio size="md" state="selected" />
                                <x-cressco.radio size="md" state="disabled-selected" />
                            </div>
                            <div class="grid grid-cols-4 gap-4 items-center">
                                <x-cressco.radio size="sm" state="default" />
                                <x-cressco.radio size="sm" state="selected" />
                                <x-cressco.radio size="sm" state="selected" />
                                <x-cressco.radio size="sm" state="disabled" />
                            </div>
                        </div>
                    </div>

                    <!-- TOGGLE BUTTON SECTION -->
                    <div class="space-y-4">
                        <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Toggle Button</h2>
                        <div class="bg-white border-2 border-dashed border-indigo-200/80 rounded-2xl p-6 shadow-xs flex flex-col gap-4">
                            <!-- Matrix of Toggle Switches from Figma -->
                            <div class="grid grid-cols-3 gap-4 items-center">
                                <x-cressco.toggle size="md" state="default" />
                                <x-cressco.toggle size="md" state="default" />
                                <x-cressco.toggle size="md" state="disabled" />
                            </div>
                            <div class="grid grid-cols-3 gap-4 items-center">
                                <x-cressco.toggle size="md" state="checked" />
                                <x-cressco.toggle size="md" state="checked" />
                                <x-cressco.toggle size="md" state="disabled-checked" />
                            </div>
                            <div class="grid grid-cols-3 gap-4 items-center">
                                <x-cressco.toggle size="sm" state="checked" />
                                <x-cressco.toggle size="sm" state="checked" />
                                <x-cressco.toggle size="sm" state="disabled-checked" />
                            </div>
                        </div>
                    </div>

                </div>

                <!-- SECTION 3: SOCIAL BUTTON -->
                <div class="space-y-4">
                    <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Social Button</h2>

                    <div class="bg-white border-2 border-dashed border-indigo-200/80 rounded-2xl p-6 sm:p-8 shadow-xs">
                        <div class="flex flex-wrap items-center gap-6">
                            <!-- Google Sign In Variants -->
                            <x-cressco.social-button provider="google" variant="default" />
                            <x-cressco.social-button provider="google" variant="filled-gray" />
                            <x-cressco.social-button provider="google" variant="icon-only" />
                            <x-cressco.social-button provider="google" variant="icon-only-gray" />

                            <div class="w-full h-px bg-gray-100 my-1"></div>

                            <!-- Apple Sign In Variants -->
                            <x-cressco.social-button provider="apple" variant="default" />
                            <x-cressco.social-button provider="apple" variant="filled-gray" />
                            <x-cressco.social-button provider="apple" variant="icon-only" />
                            <x-cressco.social-button provider="apple" variant="icon-only-gray" />
                        </div>
                    </div>
                </div>

            </div>
            @endif


            <!-- ========================================================================= -->
            <!-- 2. NAVIGATION COMPONENT SHOWCASE (Figma Reference Image 2)                -->
            <!-- ========================================================================= -->
            @if ($activeSection === 'navigation')
            <div class="space-y-12 animate-fadeIn">
                <!-- Clean Solid Terracotta Hero Banner matching Figma -->
                <div class="bg-terracotta-500 rounded-2xl p-8 sm:p-10 text-white relative overflow-hidden shadow-md">
                    <div class="relative z-10 max-w-2xl space-y-3">
                        <span class="inline-block bg-white/20 backdrop-blur-xs text-white text-xs font-semibold px-3 py-1 rounded-full border border-white/25 uppercase tracking-wider">
                            Component
                        </span>
                        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">Navigation</h1>
                        <p class="text-terracotta-100 text-sm sm:text-base leading-relaxed">
                            Guidelines to delve into the Badge to learn how to utilize the component the best.
                        </p>
                    </div>
                </div>

                <!-- SECTION 1: SIDEBAR VARIATIONS (5 Columns matching Figma Image 2) -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Sidebar (5 State Specifications)</h2>
                        <span class="text-xs text-gray-500">Scroll horizontally to view all states</span>
                    </div>

                    <div class="bg-white border-2 border-dashed border-indigo-200/80 rounded-2xl p-6 shadow-xs overflow-x-auto">
                        <div class="flex gap-6 min-w-[1340px] items-start">
                            
                            <!-- Column 1: State 1 - Dashboard Active -->
                            <div class="space-y-2">
                                <span class="text-[11px] font-semibold text-gray-400">1. Dashboard Active</span>
                                <div class="h-[680px] rounded-2xl overflow-hidden border border-gray-200 shadow-xs">
                                    <x-cressco.sidebar activeItem="Dashboard" :projectsExpanded="false" />
                                </div>
                            </div>

                            <!-- Column 2: State 2 - Projects Expanded + Bottom Switcher -->
                            <div class="space-y-2">
                                <span class="text-[11px] font-semibold text-gray-400">2. Projects Expanded</span>
                                <div class="h-[680px] rounded-2xl overflow-hidden border border-gray-200 shadow-xs">
                                    <x-cressco.sidebar activeItem="Projects" :projectsExpanded="true" :showBottomTenant="true" />
                                </div>
                            </div>

                            <!-- Column 3: State 3 - Prioritask Dashboard Subitem Active -->
                            <div class="space-y-2">
                                <span class="text-[11px] font-semibold text-gray-400">3. Nested Subitem Active</span>
                                <div class="h-[680px] rounded-2xl overflow-hidden border border-gray-200 shadow-xs">
                                    <x-cressco.sidebar activeItem="Prioritask Dashboard" :projectsExpanded="true" />
                                </div>
                            </div>

                            <!-- Column 4: State 4 - Message Active -->
                            <div class="space-y-2">
                                <span class="text-[11px] font-semibold text-gray-400">4. Message Active</span>
                                <div class="h-[680px] rounded-2xl overflow-hidden border border-gray-200 shadow-xs">
                                    <x-cressco.sidebar activeItem="Message" :projectsExpanded="false" />
                                </div>
                            </div>

                            <!-- Column 5: State 5 - Schedule Active -->
                            <div class="space-y-2">
                                <span class="text-[11px] font-semibold text-gray-400">5. Schedule Active</span>
                                <div class="h-[680px] rounded-2xl overflow-hidden border border-gray-200 shadow-xs">
                                    <x-cressco.sidebar activeItem="Schedule" :projectsExpanded="false" />
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- SECTION 2: TOP BAR & NAVIGATION ITEM -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- TOP BAR SECTION (2 cols) -->
                    <div class="lg:col-span-2 space-y-4">
                        <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Top Bar (Breadcrumb Hierarchies)</h2>
                        
                        <div class="bg-white border-2 border-dashed border-indigo-200/80 rounded-2xl p-6 shadow-xs space-y-4">
                            <!-- Level 1 -->
                            <x-cressco.top-bar :breadcrumbs="['Main Menu']" />

                            <!-- Level 2 -->
                            <x-cressco.top-bar :breadcrumbs="['Main Menu', 'Submenu']" />

                            <!-- Level 3 -->
                            <x-cressco.top-bar :breadcrumbs="['Main Menu', 'Submenu', 'Submenu']" />

                            <!-- Level 4: Truncated -->
                            <x-cressco.top-bar :breadcrumbs="['Main Menu', '...', 'Submenu']" />
                        </div>
                    </div>

                    <!-- NAVIGATION ITEM SECTION (1 col) -->
                    <div class="space-y-4">
                        <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Navigation Item</h2>
                        
                        <div class="bg-white border-2 border-dashed border-indigo-200/80 rounded-2xl p-6 shadow-xs space-y-4">
                            <div>
                                <span class="text-[11px] font-semibold text-gray-400 mb-1 block">Default State</span>
                                <x-cressco.nav-item label="Message" icon="message" :active="false" />
                            </div>

                            <div>
                                <span class="text-[11px] font-semibold text-gray-400 mb-1 block">Active State</span>
                                <x-cressco.nav-item label="Message" icon="message" :active="true" />
                            </div>

                            <div>
                                <span class="text-[11px] font-semibold text-gray-400 mb-1 block">With Submenu Caret</span>
                                <x-cressco.nav-item label="Projects" icon="projects" :hasSubmenu="true" :isOpen="false" />
                            </div>
                        </div>
                    </div>

                </div>

                <!-- SECTION 3: TITLE / PAGE HEADER PATTERNS -->
                <div class="space-y-4">
                    <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Title / Page Header</h2>

                    <div class="bg-white border-2 border-dashed border-indigo-200/80 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">
                        <!-- State 1: Title + Subtitle -->
                        <x-cressco.page-header title="Title" subtitle="Subtitle" />

                        <!-- State 2: Title + Subtitle + Avatars Stack -->
                        <x-cressco.page-header title="Title" subtitle="Subtitle" :avatars="true">
                            <x-cressco.button variant="outline" size="sm" leadingIcon="filter">
                                Filter
                            </x-cressco.button>
                        </x-cressco.page-header>

                        <!-- State 3: Title + Subtitle + Search + Filter -->
                        <x-cressco.page-header title="Title" subtitle="Subtitle" :search="true" :filter="true" />

                        <!-- State 4: Title + Subtitle + + Add Task Primary Terracotta Button -->
                        <x-cressco.page-header title="Title" subtitle="Subtitle" buttonText="Add Task" buttonIcon="plus" buttonVariant="primary" />
                    </div>
                </div>

                <!-- SECTION 4: INTERACTIVE LIVE WORKSPACE DEMO -->
                <div class="space-y-4">
                    <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Interactive Live Navigation Demo</h2>
                    
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden flex h-[600px]">
                        <!-- Live Interactive Sidebar with Alpine.js -->
                        <x-cressco.sidebar :interactive="true" activeItem="Dashboard" :projectsExpanded="true" />

                        <!-- Demo Content Body -->
                        <div class="flex-1 bg-[#FAFBFB] flex flex-col overflow-y-auto">
                            <!-- Live Top Bar -->
                            <div class="p-6 pb-0">
                                <x-cressco.top-bar :breadcrumbs="['Workspace', 'Prioritask', 'Overview']" />
                            </div>

                            <div class="p-6 space-y-6 flex-1">
                                <x-cressco.page-header title="Project Management" subtitle="Manage your tasks, projects, and tenant resources smoothly." :search="true" :filter="true" buttonText="Add Task" buttonIcon="plus" />

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-2xs space-y-2">
                                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total Projects</div>
                                        <div class="text-3xl font-bold text-gray-900">24</div>
                                        <div class="text-xs text-emerald-600 font-medium flex items-center gap-1">
                                            <span>↑ 12%</span> vs last month
                                        </div>
                                    </div>
                                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-2xs space-y-2">
                                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Active Tasks</div>
                                        <div class="text-3xl font-bold text-gray-900">142</div>
                                        <div class="text-xs text-gray-500 font-medium">8 completed today</div>
                                    </div>
                                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-2xs space-y-2">
                                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Team Members</div>
                                        <div class="text-3xl font-bold text-gray-900">18</div>
                                        <div class="text-xs text-terracotta-600 font-medium">3 invites pending</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            @endif


            <!-- ========================================================================= -->
            <!-- 3. TEXT FIELD COMPONENT SHOWCASE (Phase 3.2.1 Component Library)          -->
            <!-- ========================================================================= -->
            @if ($activeSection === 'text-field')
            <div class="space-y-12 animate-fadeIn">
                <!-- Clean Solid Terracotta Hero Banner -->
                <div class="bg-terracotta-500 rounded-2xl p-8 sm:p-10 text-white relative overflow-hidden shadow-md">
                    <div class="relative z-10 max-w-2xl space-y-3">
                        <span class="inline-block bg-white/20 backdrop-blur-xs text-white text-xs font-semibold px-3 py-1 rounded-full border border-white/25 uppercase tracking-wider">
                            Component
                        </span>
                        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">Text Field</h1>
                        <p class="text-terracotta-100 text-sm sm:text-base leading-relaxed">
                            Guidelines to delve into the Badge to learn how to utilize the component the best.
                        </p>
                    </div>
                </div>

                <!-- 1. TEXT INPUT SECTION -->
                <div class="space-y-4">
                    <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Text Input</h2>

                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 sm:p-8 shadow-xs overflow-x-auto">
                        <div class="min-w-[800px] space-y-6">
                            
                            <!-- State 1: Default / Placeholder -->
                            <div class="grid grid-cols-4 gap-6">
                                <x-cressco.text-input label="Label" placeholder="Placeholder" helper="This is a hint text to help user." leadingIcon="mail" trailingIcon="help" state="default" />
                                <x-cressco.text-input label="Label" placeholder="Placeholder" helper="This is a hint text to help user." leadingIcon="document" trailingIcon="sort" state="default" />
                                <x-cressco.text-input label="Label" placeholder="Phone number" helper="This is a hint text to help user." countryCode="+1" countryFlag="🇺🇸" state="default" />
                                <x-cressco.text-input label="Label" placeholder="Phone number" helper="This is a hint text to help user." countryCode="+1" countryFlag="🇺🇸" state="default" />
                            </div>

                            <!-- State 2: Focus / Active -->
                            <div class="grid grid-cols-4 gap-6">
                                <x-cressco.text-input label="Label" placeholder="Placeholder" helper="This is a hint text to help user." leadingIcon="mail" trailingIcon="help" state="focus" />
                                <x-cressco.text-input label="Label" placeholder="Placeholder" helper="This is a hint text to help user." leadingIcon="document" trailingIcon="sort" state="focus" />
                                <x-cressco.text-input label="Label" placeholder="Phone number" helper="This is a hint text to help user." countryCode="+1" countryFlag="🇺🇸" state="focus" />
                                <x-cressco.text-input label="Label" placeholder="Phone number" helper="This is a hint text to help user." countryCode="+1" countryFlag="🇺🇸" state="focus" />
                            </div>

                            <!-- State 3: Filled -->
                            <div class="grid grid-cols-4 gap-6">
                                <x-cressco.text-input label="Label" value="Placeholder" helper="This is a hint text to help user." leadingIcon="mail" trailingIcon="help" state="filled" />
                                <x-cressco.text-input label="Label" value="Placeholder" helper="This is a hint text to help user." leadingIcon="document" trailingIcon="sort" state="filled" />
                                <x-cressco.text-input label="Label" value="Phone number" helper="This is a hint text to help user." countryCode="+1" countryFlag="🇺🇸" state="filled" />
                                <x-cressco.text-input label="Label" value="Phone number" helper="This is a hint text to help user." countryCode="+1" countryFlag="🇺🇸" state="filled" />
                            </div>

                            <!-- State 4: Disabled -->
                            <div class="grid grid-cols-4 gap-6">
                                <x-cressco.text-input label="Label" placeholder="Placeholder" helper="This is a hint text to help user." leadingIcon="mail" trailingIcon="help" state="disabled" :disabled="true" />
                                <x-cressco.text-input label="Label" placeholder="Placeholder" helper="This is a hint text to help user." leadingIcon="document" trailingIcon="sort" state="disabled" :disabled="true" />
                                <x-cressco.text-input label="Label" placeholder="Phone number" helper="This is a hint text to help user." countryCode="+1" countryFlag="🇺🇸" state="disabled" :disabled="true" />
                                <x-cressco.text-input label="Label" placeholder="Phone number" helper="This is a hint text to help user." countryCode="+1" countryFlag="🇺🇸" state="disabled" :disabled="true" />
                            </div>

                            <!-- State 5: Error -->
                            <div class="grid grid-cols-4 gap-6">
                                <x-cressco.text-input label="Label" value="Placeholder" error="This is a hint text to help user." leadingIcon="mail" trailingIcon="help" state="error" />
                                <x-cressco.text-input label="Label" value="Placeholder" error="This is a hint text to help user." leadingIcon="document" trailingIcon="sort" state="error" />
                                <x-cressco.text-input label="Label" value="Phone number" error="This is a hint text to help user." countryCode="+1" countryFlag="🇺🇸" state="error" />
                                <x-cressco.text-input label="Label" value="Phone number" error="This is a hint text to help user." countryCode="+1" countryFlag="🇺🇸" state="error" />
                            </div>

                        </div>
                    </div>
                </div>

                <!-- 2. DROP DOWN SECTION -->
                <div class="space-y-4">
                    <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Drop Down</h2>

                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 sm:p-8 shadow-xs overflow-x-auto">
                        <div class="min-w-[800px] space-y-12">
                            <!-- Row 1: Single Select Dropdown -->
                            <div class="grid grid-cols-4 gap-6 items-start">
                                <x-cressco.dropdown label="Label" placeholder="Placeholder" helper="This is a hint text to help user." variant="single" state="default" />
                                <x-cressco.dropdown label="Label" placeholder="Placeholder" variant="single" state="open" :isOpen="true" selected="Option 2" />
                                <x-cressco.dropdown label="Label" helper="This is a hint text to help user." variant="single" state="selected" selected="Option 2" />
                                <x-cressco.dropdown label="Label" helper="This is a hint text to help user." variant="single" state="disabled" :disabled="true" selected="Option 2" />
                            </div>

                            <!-- Row 2: Multi-Select with Chips Dropdown -->
                            <div class="grid grid-cols-4 gap-6 items-start">
                                <x-cressco.dropdown label="Label" placeholder="Placeholder" helper="This is a hint text to help user." variant="multi" state="default" />
                                <x-cressco.dropdown label="Label" variant="multi" state="open" :isOpen="true" :selected="['Option 2', 'Option 3']" />
                                <x-cressco.dropdown label="Label" helper="This is a hint text to help user." variant="multi" state="selected" :selected="['Option 2', 'Option 3']" />
                                <x-cressco.dropdown label="Label" helper="This is a hint text to help user." variant="multi" state="disabled" :disabled="true" :selected="['Option 2', 'Option 3']" />
                            </div>

                            <!-- Row 3: Radio Selection Dropdown -->
                            <div class="grid grid-cols-4 gap-6 items-start">
                                <x-cressco.dropdown label="Label" placeholder="Placeholder" helper="This is a hint text to help user." variant="radio" state="default" />
                                <x-cressco.dropdown label="Label" variant="radio" state="open" :isOpen="true" selected="Option 2" />
                                <x-cressco.dropdown label="Label" helper="This is a hint text to help user." variant="radio" state="selected" selected="Option 2" />
                                <x-cressco.dropdown label="Label" helper="This is a hint text to help user." variant="radio" state="disabled" :disabled="true" selected="Option 2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. PIN NUMBER SECTION -->
                <div class="space-y-4">
                    <h2 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Pin Number</h2>

                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 sm:p-8 shadow-xs space-y-8">
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Figma State Specification</span>
                            <div class="mt-3">
                                <x-cressco.pin-input :length="4" state="preview_figma" />
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100 space-y-3">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Interactive Live Component</span>
                            <x-cressco.pin-input :length="4" label="Enter Verification Code" helper="We sent a 4-digit code to your mobile phone." />
                        </div>
                    </div>
                </div>

            </div>
            @endif


            <!-- ========================================================================= -->
            <!-- 4. TYPOGRAPHY STYLE GUIDE (Phase 3.1 Design System Tokens)               -->
            <!-- ========================================================================= -->
            @if ($activeSection === 'typography')
            <div class="space-y-8 animate-fadeIn">
                <div class="bg-terracotta-500 rounded-2xl p-8 sm:p-10 text-white relative overflow-hidden shadow-md">
                    <div class="relative z-10 max-w-2xl space-y-3">
                        <span class="inline-block bg-white/20 backdrop-blur-xs text-white text-xs font-semibold px-3 py-1 rounded-full border border-white/25 uppercase tracking-wider">
                            Foundation
                        </span>
                        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">Typography</h1>
                        <p class="text-terracotta-100 text-sm sm:text-base leading-relaxed">
                            DM Sans typographic scale, weights, line heights, and design tokens for Cressco.
                        </p>
                    </div>
                </div>

                <div class="bg-white border border-gray-200/80 rounded-2xl p-6 sm:p-10 shadow-xs divide-y divide-gray-200 space-y-10">
                    @php
                        $typographyTokens = [
                            ['name' => 'Display-2xl', 'class' => 'text-display-2xl', 'size' => '72px', 'lineHeight' => '90px', 'sample' => 'The quick brown fox jumps over the lazy dog.'],
                            ['name' => 'Display-xl',  'class' => 'text-display-xl',  'size' => '60px', 'lineHeight' => '72px', 'sample' => 'The quick brown fox jumps over the lazy dog.'],
                            ['name' => 'Display-lg',  'class' => 'text-display-lg',  'size' => '48px', 'lineHeight' => '60px', 'sample' => 'The quick brown fox jumps over the lazy dog.'],
                            ['name' => 'Display-md',  'class' => 'text-display-md',  'size' => '36px', 'lineHeight' => '44px', 'sample' => 'The quick brown fox jumps over the lazy dog.'],
                            ['name' => 'Display-sm',  'class' => 'text-display-sm',  'size' => '30px', 'lineHeight' => '38px', 'sample' => 'The quick brown fox jumps over the lazy dog.'],
                            ['name' => 'Display-xs',  'class' => 'text-display-xs',  'size' => '24px', 'lineHeight' => '32px', 'sample' => 'The quick brown fox jumps over the lazy dog.'],
                            ['name' => 'Text-xl',     'class' => 'text-text-xl',     'size' => '20px', 'lineHeight' => '30px', 'sample' => 'The quick brown fox jumps over the lazy dog.'],
                            ['name' => 'Text-lg',     'class' => 'text-text-lg',     'size' => '18px', 'lineHeight' => '28px', 'sample' => 'The quick brown fox jumps over the lazy dog.'],
                            ['name' => 'Text-md',     'class' => 'text-text-md',     'size' => '16px', 'lineHeight' => '24px', 'sample' => 'The quick brown fox jumps over the lazy dog.'],
                            ['name' => 'Text-sm',     'class' => 'text-text-sm',     'size' => '14px', 'lineHeight' => '20px', 'sample' => 'The quick brown fox jumps over the lazy dog.'],
                            ['name' => 'Text-xs',     'class' => 'text-text-xs',     'size' => '12px', 'lineHeight' => '18px', 'sample' => 'The quick brown fox jumps over the lazy dog.'],
                        ];
                    @endphp

                    @foreach ($typographyTokens as $index => $item)
                        <div class="{{ $index > 0 ? 'pt-10' : '' }} space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-gray-400 tracking-wide">{{ $item['name'] }}</span>
                                <div class="flex items-center gap-4 text-xs text-gray-400">
                                    <span class="font-normal text-gray-500">Regular</span>
                                    <span class="font-medium text-gray-600">Medium</span>
                                    <span class="font-semibold text-gray-700">Semi Bold</span>
                                    <span class="font-bold text-gray-900">Bold</span>
                                </div>
                            </div>

                            <div class="{{ $item['class'] }} text-gray-900 font-normal tracking-tight overflow-x-auto py-1">
                                {{ $item['sample'] }}
                            </div>

                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="px-2.5 py-1 bg-gray-100 rounded-md text-gray-600 font-mono">Size: <strong class="text-gray-900">{{ $item['size'] }}</strong></span>
                                <span class="px-2.5 py-1 bg-gray-100 rounded-md text-gray-600 font-mono">Line Height: <strong class="text-gray-900">{{ $item['lineHeight'] }}</strong></span>
                                <span class="px-2.5 py-1 bg-terracotta-50 border border-terracotta-200 rounded-md text-terracotta-700 font-mono">Token: <code class="font-bold">{{ $item['class'] }}</code></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif


            <!-- ========================================================================= -->
            <!-- 5. COLOR SYSTEM STYLE GUIDE (Phase 3.1 Design System Tokens)              -->
            <!-- ========================================================================= -->
            @if ($activeSection === 'color')
            <div class="space-y-8 animate-fadeIn">
                <div class="bg-terracotta-500 rounded-2xl p-8 sm:p-10 text-white relative overflow-hidden shadow-md">
                    <div class="relative z-10 max-w-2xl space-y-3">
                        <span class="inline-block bg-white/20 backdrop-blur-xs text-white text-xs font-semibold px-3 py-1 rounded-full border border-white/25 uppercase tracking-wider">
                            Foundation
                        </span>
                        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">Color</h1>
                        <p class="text-terracotta-100 text-sm sm:text-base leading-relaxed">
                            Guidelines and reusable design tokens for Cressco palettes, semantic colors, grays, and surfaces.
                        </p>
                    </div>
                </div>

                <!-- Group 1: Background & Surface -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-xs space-y-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Background</h3>
                            <p class="text-xs text-gray-500">Tokens for application canvas and page backgrounds</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <div class="h-20 rounded-xl bg-bg-primary border border-gray-200 shadow-inner flex items-center justify-center">
                                    <span class="text-xs font-mono text-gray-400">bg-bg-primary</span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-900">Background/Primary</div>
                                    <div class="text-xs text-gray-500 font-mono">HEX: #FFFFFF</div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="h-20 rounded-xl bg-bg-secondary border border-gray-200 shadow-inner flex items-center justify-center">
                                    <span class="text-xs font-mono text-gray-400">bg-bg-secondary</span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-900">Background/Secondary</div>
                                    <div class="text-xs text-gray-500 font-mono">HEX: #F8F9FA</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-xs space-y-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Surface</h3>
                            <p class="text-xs text-gray-500">Tokens for cards, elevated surfaces, and interactive states</p>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="space-y-2">
                                <div class="h-20 rounded-xl bg-surface-default border border-gray-200 shadow-inner flex items-center justify-center">
                                    <span class="text-[10px] font-mono text-gray-400">default</span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-900 truncate">Surface/Default</div>
                                    <div class="text-xs text-gray-500 font-mono">#FFFFFF</div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="h-20 rounded-xl bg-surface-hover border border-gray-200 shadow-inner flex items-center justify-center">
                                    <span class="text-[10px] font-mono text-gray-400">hover</span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-900 truncate">Surface/Hover</div>
                                    <div class="text-xs text-gray-500 font-mono">#F4F5F6</div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="h-20 rounded-xl bg-surface-active border border-gray-200 shadow-inner flex items-center justify-center">
                                    <span class="text-[10px] font-mono text-gray-400">active</span>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-900 truncate">Surface/Active</div>
                                    <div class="text-xs text-gray-500 font-mono">#EEF0F2</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Group 2: Terracotta (Primary) -->
                <div class="bg-white border border-gray-200/80 rounded-2xl p-6 sm:p-8 shadow-xs space-y-5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Terracotta (Primary)</h3>
                        <p class="text-xs text-gray-500">Brand identity color scale for Cressco</p>
                    </div>

                    @php
                        $terracottaColors = [
                            ['name' => 'Terracotta-900', 'hex' => '#3B110B', 'bg' => 'bg-terracotta-900'],
                            ['name' => 'Terracotta-800', 'hex' => '#611C10', 'bg' => 'bg-terracotta-800'],
                            ['name' => 'Terracotta-700', 'hex' => '#852918', 'bg' => 'bg-terracotta-700'],
                            ['name' => 'Terracotta-600', 'hex' => '#B5381B', 'bg' => 'bg-terracotta-600'],
                            ['name' => 'Terracotta-500', 'hex' => '#CC4420', 'bg' => 'bg-terracotta-500'],
                            ['name' => 'Terracotta-400', 'hex' => '#DE6544', 'bg' => 'bg-terracotta-400'],
                            ['name' => 'Terracotta-300', 'hex' => '#E88C74', 'bg' => 'bg-terracotta-300'],
                            ['name' => 'Terracotta-200', 'hex' => '#F3B9AA', 'bg' => 'bg-terracotta-200'],
                            ['name' => 'Terracotta-100', 'hex' => '#FAE0DA', 'bg' => 'bg-terracotta-100'],
                            ['name' => 'Terracotta-50',  'hex' => '#FCF5F3', 'bg' => 'bg-terracotta-50'],
                        ];
                    @endphp

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                        @foreach ($terracottaColors as $color)
                            <div class="space-y-2">
                                <div class="h-20 rounded-xl {{ $color['bg'] }} border border-black/5 shadow-xs transition hover:scale-[1.02]"></div>
                                <div>
                                    <div class="text-xs font-bold text-gray-900">{{ $color['name'] }}</div>
                                    <div class="text-xs text-gray-500 font-mono">HEX: {{ $color['hex'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Group 3: Gray Colors -->
                <div class="bg-white border border-gray-200/80 rounded-2xl p-6 sm:p-8 shadow-xs space-y-5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Gray Colors</h3>
                        <p class="text-xs text-gray-500">Neutral grays for text, borders, and structure</p>
                    </div>

                    @php
                        $grayColors = [
                            ['name' => 'Gray-900', 'hex' => '#101828', 'bg' => 'bg-gray-900'],
                            ['name' => 'Gray-800', 'hex' => '#1D2939', 'bg' => 'bg-gray-800'],
                            ['name' => 'Gray-700', 'hex' => '#344054', 'bg' => 'bg-gray-700'],
                            ['name' => 'Gray-600', 'hex' => '#475467', 'bg' => 'bg-gray-600'],
                            ['name' => 'Gray-500', 'hex' => '#667085', 'bg' => 'bg-gray-500'],
                            ['name' => 'Gray-400', 'hex' => '#98A2B3', 'bg' => 'bg-gray-400'],
                            ['name' => 'Gray-300', 'hex' => '#D0D5DD', 'bg' => 'bg-gray-300'],
                            ['name' => 'Gray-200', 'hex' => '#EAECF0', 'bg' => 'bg-gray-200'],
                            ['name' => 'Gray-100', 'hex' => '#F2F4F7', 'bg' => 'bg-gray-100'],
                            ['name' => 'Gray-50',  'hex' => '#F8F9FA', 'bg' => 'bg-gray-50'],
                            ['name' => 'Gray-25',  'hex' => '#FCFCFD', 'bg' => 'bg-gray-25'],
                            ['name' => 'Gray-0',   'hex' => '#FFFFFF', 'bg' => 'bg-gray-0'],
                        ];
                    @endphp

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                        @foreach ($grayColors as $color)
                            <div class="space-y-2">
                                <div class="h-20 rounded-xl {{ $color['bg'] }} border border-gray-200 shadow-xs transition hover:scale-[1.02]"></div>
                                <div>
                                    <div class="text-xs font-bold text-gray-900">{{ $color['name'] }}</div>
                                    <div class="text-xs text-gray-500 font-mono">HEX: {{ $color['hex'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
            @endif


            <!-- ========================================================================= -->
            <!-- 6. CARD & CHART COMPONENT SHOWCASE (Figma Reference Image 2)              -->
            <!-- ========================================================================= -->
            <!-- ========================================================================= -->
            <!-- 6. CARD & CHART COMPONENT SHOWCASE (Figma Reference Image 2)              -->
            <!-- ========================================================================= -->
            @if ($activeSection === 'card-chart')
            <div class="space-y-12 animate-fadeIn">
                <!-- Clean Solid Terracotta Hero Banner -->
                <div class="bg-terracotta-500 rounded-2xl p-8 sm:p-10 text-white relative overflow-hidden shadow-md">
                    <div class="relative z-10 max-w-2xl space-y-3">
                        <span class="inline-block bg-white/20 backdrop-blur-xs text-white text-xs font-semibold px-3 py-1 rounded-full border border-white/25 uppercase tracking-wider">
                            Component
                        </span>
                        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">Card & Chart</h1>
                        <p class="text-terracotta-100 text-sm sm:text-base leading-relaxed">
                            Guidelines to delve into the Badge to learn how to utilize the component the best.
                        </p>
                    </div>
                </div>

                <!-- 1. CHART SECTION -->
                <div class="space-y-4">
                    <h2 class="text-sm font-bold tracking-wider text-gray-900 uppercase">Chart</h2>

                    <div class="space-y-6">
                        <!-- Top Row: Academic Overview & Payment Overview (Distribution) -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2">
                                <x-cressco.chart-project-overview
                                    title="Academic Overview"
                                    subtitle="Track class completions and active tutoring across branches"
                                />
                            </div>
                            <div>
                                <x-cressco.chart-task-distribution
                                    title="Payment Overview"
                                    subtitle="Monitor payment status and transactions"
                                    centerLabel="TOTAL PAYMENTS"
                                    :total="200"
                                    unit="Payments"
                                />
                            </div>
                        </div>

                        <!-- Bottom Row: Session Overview Stacked Bar Chart -->
                        <div>
                            <x-cressco.chart-task-overview
                                title="Session Overview"
                                subtitle="Track teaching sessions and monitor activity over time."
                                unit="Sessions"
                            />
                        </div>
                    </div>
                </div>

                <!-- 2. CARD PROJECT, SESSION SCHEDULE & FOLDER -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Card Project -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Card Project</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <x-cressco.project-card
                                title="Active Classes"
                                value="48"
                                trend="+25%"
                                subtitle="10 new classes this week"
                            />
                            <x-cressco.project-card
                                title="Active Tutors"
                                value="46"
                                trend="-12%"
                                trendType="negative"
                                subtitle="Across 8 branches"
                            />
                        </div>
                    </div>

                    <!-- Session Schedule (Replacing subscription Upgrade Plan with tutoring schedule) -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Session Schedule</h3>
                        <x-cressco.schedule-card
                            title="Fisika Dasar - UTBK"
                            color="terracotta"
                            description="Prime Academy Malang • Ruang 204 • Dr. Hendra"
                            time="03:30 PM - 05:30 PM"
                        />
                    </div>

                    <!-- Folder -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Folder</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <x-cressco.folder-card title="Modul & Silabus" filesCount="254 files" size="456 MB" />
                            <x-cressco.folder-card title="Bank Soal Tryout" filesCount="128 files" size="1.2 GB" />
                        </div>
                    </div>
                </div>

                <!-- 3. REMINDER, TASK & BUBBLE CHAT -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Reminder -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Reminder</h3>
                        <x-cressco.reminder-card
                            title="Evaluasi Tryout SNBT Kelas 12"
                            time="08.00 AM - 10.30 AM"
                        />
                    </div>

                    <!-- Task -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Task</h3>
                        <x-cressco.task-card
                            title="Review Modul UTBK - Matematika IPA"
                            description="Review kelengkapan kunci jawaban dan pembahasan soal TPS SNBT."
                            priority="High Priority"
                            dueDate="2 days left"
                            :commentsCount="8"
                            :attachmentsCount="4"
                        />
                    </div>

                    <!-- Bubble Chat -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Bubble Chat</h3>
                        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-4">
                            <x-cressco.chat-bubble sender="Dr. Hendra Saputra (Tutor Fisika)" time="04:30 PM">
                                Halo Admin! Modul pembahasan Tryout Fisika untuk kelas 12 Cabang Malang sudah saya upload ke sistem ya. ✌️
                            </x-cressco.chat-bubble>
                            <x-cressco.chat-bubble sender="Sara Lance (Admin Cabang)" time="04:32 PM" :isSender="true">
                                Terima kasih banyak Dok! Sudah kami verifikasi dan siap didistribusikan ke siswa.
                            </x-cressco.chat-bubble>
                        </div>
                    </div>
                </div>

                <!-- 4. CALENDAR, DATE, MESSAGE, DOCUMENT & FILES -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Calendar & Date -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Calender & Date</h3>
                        <x-cressco.calendar />
                        <div class="pt-2">
                            <x-cressco.date-strip />
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Message</h3>
                        <div class="space-y-3">
                            <x-cressco.message-item
                                sender="Dr. Hendra Saputra"
                                message="Confirmed attendance for tomorrow's intensive session."
                                time="10 mins ago"
                                :unreadCount="2"
                            />
                            <x-cressco.message-item
                                sender="Rina Wijaya (Cabang Semarang)"
                                message="Laporan absensi dan jadwal kelas baru sudah sinkron."
                                time="11:20 AM"
                                :unreadCount="1"
                            />
                        </div>
                    </div>

                    <!-- Document -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Document</h3>
                        <div class="space-y-3">
                            <x-cressco.document-card title="Silabus_Fisika_Intensif_SNBT.pdf" size="1.8 MB" type="PDF" />
                            <x-cressco.document-card title="Laporan_Akademik_Cabang_Bandung.xlsx" size="2.4 MB" type="Document" />
                        </div>
                    </div>

                    <!-- Files -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Files</h3>
                        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs">
                            <div class="grid grid-cols-4 gap-2.5">
                                <x-cressco.file-item ext="pdf" />
                                <x-cressco.file-item ext="fig" />
                                <x-cressco.file-item ext="png" />
                                <x-cressco.file-item ext="doc" />
                                <x-cressco.file-item ext="zip" />
                                <x-cressco.file-item ext="xls" />
                                <x-cressco.file-item ext="jpg" />
                                <x-cressco.file-item ext="txt" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            @endif


            <!-- ========================================================================= -->
            <!-- 7. OTHER COMPONENT SHOWCASE (Figma Reference Image 1)                     -->
            <!-- ========================================================================= -->
            @if ($activeSection === 'other')
            <div class="space-y-12 animate-fadeIn" x-data="{ openDemoModal: false }">
                <!-- Clean Solid Terracotta Hero Banner -->
                <div class="bg-terracotta-500 rounded-2xl p-8 sm:p-10 text-white relative overflow-hidden shadow-md">
                    <div class="relative z-10 max-w-2xl space-y-3">
                        <span class="inline-block bg-white/20 backdrop-blur-xs text-white text-xs font-semibold px-3 py-1 rounded-full border border-white/25 uppercase tracking-wider">
                            Component
                        </span>
                        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">Other</h1>
                        <p class="text-terracotta-100 text-sm sm:text-base leading-relaxed">
                            Guidelines to delve into the Badge to learn how to utilize the component the best.
                        </p>
                    </div>
                </div>

                <!-- 1. TABLE & BULK ACTION BAR -->
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <h2 class="text-sm font-bold tracking-wider text-gray-900 uppercase">Table & Bulk Action</h2>
                        <x-cressco.bulk-action-bar :count="1" />
                    </div>
                    <x-cressco.table />
                </div>

                <!-- 2. TAGS, ALERT, MODAL & PROFILE -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Tags -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Tags</h3>
                        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs flex flex-wrap gap-2">
                            <x-cressco.badge variant="terracotta" dot>Terracotta</x-cressco.badge>
                            <x-cressco.badge variant="success" dot>Success</x-cressco.badge>
                            <x-cressco.badge variant="warning" dot>Warning</x-cressco.badge>
                            <x-cressco.badge variant="info" dot>Info</x-cressco.badge>
                            <x-cressco.badge variant="error" dot>Error</x-cressco.badge>
                            <x-cressco.badge variant="purple" dot>Purple</x-cressco.badge>
                            <x-cressco.badge variant="gray">Neutral</x-cressco.badge>
                        </div>
                    </div>

                    <!-- Alert -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Alert</h3>
                        <div class="space-y-3">
                            <x-cressco.alert type="success" title="Success Alert">
                                Transaction processed successfully. All records updated.
                            </x-cressco.alert>
                            <x-cressco.alert type="error" title="Error Alert">
                                Failed to connect to server. Please try again.
                            </x-cressco.alert>
                        </div>
                    </div>

                    <!-- Modal & Profile -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Modal & Profile</h3>
                        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-700">Interactive Modal:</span>
                                <button type="button" @click="openDemoModal = true" class="px-3 py-1.5 rounded-lg bg-terracotta-500 hover:bg-terracotta-600 text-white text-xs font-bold shadow-xs transition">
                                    Open Demo Modal
                                </button>
                            </div>
                            <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-700">Avatar Stack:</span>
                                <x-cressco.avatar-group />
                            </div>
                        </div>

                        <!-- Live Modal Instance -->
                        <div x-show="openDemoModal" style="display: none;">
                            <x-cressco.modal
                                name="demo-modal"
                                :show="true"
                                title="Delete Task"
                                description="Are you sure you want to delete this task? This action cannot be undone and will remove it permanently."
                            />
                        </div>
                    </div>
                </div>

                <!-- 3. DATES AGENDA -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Dates Agenda</h3>
                    <x-cressco.dates-agenda />
                </div>

                <!-- 4. NOTIFICATION -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Notification</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-cressco.notification-item
                            title="Jane Cooper"
                            action="requested access to Meta Pixel on iOS project."
                            time="10 mins ago"
                            :unread="true"
                        />
                        <x-cressco.notification-item
                            title="Alex Morgan"
                            action="assigned you a new task in Cressco Dashboard."
                            time="25 mins ago"
                            :unread="false"
                            snippet="Review student attendance and grade submissions for Class 10-A."
                        />
                        <x-cressco.notification-item
                            title="Robert Fox"
                            action="requested permission for branch timetable updates."
                            time="1 hour ago"
                            :isActionable="true"
                        />
                        <x-cressco.notification-item
                            title="Emily Watson"
                            action="completed course assessment upload for Grade 12."
                            time="2 hours ago"
                            :isActionable="true"
                        />
                    </div>
                </div>

                <!-- 5. TIMELINE PROJECT & SCHEDULE CATEGORIES -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Timeline Project -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Timeline Project / Task</h3>
                        <div class="space-y-3">
                            <x-cressco.timeline-item priority="High Priority" priorityVariant="error" title="Cressco Dashboard MVP" date="October 12, 2026" />
                            <x-cressco.timeline-item priority="Medium Priority" priorityVariant="warning" title="Student Attendance Mobile App" date="October 18, 2026" />
                            <x-cressco.timeline-item priority="Low Priority" priorityVariant="info" title="Parent Portal Notification Gateway" date="October 25, 2026" />
                        </div>
                    </div>

                    <!-- Schedule Categories -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Schedule Categories</h3>
                        <div class="space-y-3">
                            <x-cressco.schedule-card title="UI Module" color="blue" description="Discussion on responsive layout & mobile navigation structure." time="07:00 PM - 09:00 PM" />
                            <x-cressco.schedule-card title="Backend API Architecture" color="purple" description="Reviewing multi-tenant database foreign keys and policy checks." time="09:00 PM - 10:30 PM" />
                            <x-cressco.schedule-card title="Deployment Pipeline" color="emerald" description="Configuring automated CI test runner and container builds." time="11:00 PM - 12:00 AM" />
                        </div>
                    </div>
                </div>

                <!-- 6. LOGO & BRAND ASSETS -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Logo / Brand Assets</h3>
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs">
                        <x-cressco.brand-logos />
                    </div>
                </div>

                <!-- 7. INTEGRATION / CONNECTOR VISUAL -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase">Integration / Connector Visual</h3>
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs">
                        <x-cressco.integration-hub />
                    </div>
                </div>

            </div>
            @endif

        </main>

        <!-- Footer matching Figma Style Guide -->
        <footer class="border-t border-gray-200 bg-white py-6 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
                <div>
                    Cressco UI Kit &copy; Copyright 2026
                </div>
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="Cressco Logo" class="h-5 w-auto object-contain">
                </div>
                <div>
                    Made with <span class="text-terracotta-500">&hearts;</span> Cressco
                </div>
            </div>
        </footer>

    </div>

</body>
</html>
