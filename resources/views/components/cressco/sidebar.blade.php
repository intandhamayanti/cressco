@props([
    'activeItem' => 'Dashboard',
    'projectsExpanded' => true,
    'showStorageCard' => true,
    'showBottomTenant' => false,
    'tenantName' => 'Cressco',
    'tenantEmail' => 'hello@cressco.app',
    'interactive' => false,
    'id' => null,
])

@php
    $id = $id ?? 'sidebar-' . uniqid();
@endphp

<aside {{ $attributes->merge(['class' => 'w-64 bg-white border-r border-gray-200/90 flex flex-col justify-between h-full font-sans select-none shrink-0 shadow-xs']) }}
       @if ($interactive) x-data="{ active: '{{ $activeItem }}', projectsOpen: {{ $projectsExpanded ? 'true' : 'false' }} }" @endif>

    <div class="p-4 space-y-5 overflow-y-auto flex-1">
        
        <!-- Workspace / Tenant Header -->
        <div class="flex items-center justify-between p-2 rounded-xl hover:bg-gray-50 transition border border-transparent hover:border-gray-200 cursor-pointer">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-lg bg-terracotta-500 flex items-center justify-center text-white shrink-0 shadow-2xs">
                    <!-- Cressco Logo Geometric Mark -->
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-gray-900 truncate leading-tight">{{ $tenantName }}</div>
                    <div class="text-xs text-gray-500 truncate leading-tight">{{ $tenantEmail }}</div>
                </div>
            </div>
            <!-- Tenant Switcher Up/Down Chevron -->
            <div class="text-gray-400 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                </svg>
            </div>
        </div>

        <!-- Search Input -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text"
                   placeholder="Search"
                   class="w-full pl-9 pr-3 py-2 text-xs bg-white border border-gray-200 rounded-xl placeholder-gray-400 text-gray-800 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
            />
        </div>

        <!-- Group 1: MAIN MENU -->
        <div class="space-y-1">
            <div class="px-3 py-1.5 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                Main Menu
            </div>

            <!-- Dashboard -->
            @if ($interactive)
                <button type="button"
                        @click="active = 'Dashboard'"
                        :class="active === 'Dashboard' ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-medium'"
                        class="w-full group flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all duration-150 select-none text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </div>
                </button>
            @else
                <x-cressco.nav-item label="Dashboard" icon="dashboard" :active="$activeItem === 'Dashboard'" />
            @endif

            <!-- Projects with nested items -->
            <div>
                @if ($interactive)
                    <button type="button"
                            @click="projectsOpen = !projectsOpen; active = 'Projects'"
                            :class="active === 'Projects' ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-medium'"
                            class="w-full group flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all duration-150 select-none text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            <span>Projects</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-150" :class="projectsOpen ? 'rotate-90 text-gray-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @else
                    <x-cressco.nav-item label="Projects" icon="projects" :active="$activeItem === 'Projects'" :hasSubmenu="true" :isOpen="$projectsExpanded" />
                @endif

                <!-- Nested Submenu items -->
                <div class="mt-1 ml-5 pl-3 border-l-2 border-gray-200 space-y-1"
                     @if ($interactive) x-show="projectsOpen" x-transition @elseif (!$projectsExpanded) style="display: none;" @endif>
                    
                    @if ($interactive)
                        <button type="button"
                                @click="active = 'Prioritask Dashboard'"
                                :class="active === 'Prioritask Dashboard' ? 'text-gray-900 font-bold bg-gray-100/70' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-normal'"
                                class="w-full text-left py-1.5 px-3 rounded-lg text-xs transition-colors truncate">
                            Prioritask Dashboard
                        </button>
                        <button type="button"
                                @click="active = 'Fintrack Mobile App'"
                                :class="active === 'Fintrack Mobile App' ? 'text-gray-900 font-bold bg-gray-100/70' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-normal'"
                                class="w-full text-left py-1.5 px-3 rounded-lg text-xs transition-colors truncate">
                            Fintrack Mobile App
                        </button>
                        <button type="button"
                                @click="active = 'Redesign Majoo'"
                                :class="active === 'Redesign Majoo' ? 'text-gray-900 font-bold bg-gray-100/70' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-normal'"
                                class="w-full text-left py-1.5 px-3 rounded-lg text-xs transition-colors truncate">
                            Redesign Majoo
                        </button>
                    @else
                        <x-cressco.nav-item label="Prioritask Dashboard" :isNested="true" :active="$activeItem === 'Prioritask Dashboard'" />
                        <x-cressco.nav-item label="Fintrack Mobile App" :isNested="true" :active="$activeItem === 'Fintrack Mobile App'" />
                        <x-cressco.nav-item label="Redesign Majoo" :isNested="true" :active="$activeItem === 'Redesign Majoo'" />
                    @endif
                </div>
            </div>

            <!-- Message -->
            @if ($interactive)
                <button type="button"
                        @click="active = 'Message'"
                        :class="active === 'Message' ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-medium'"
                        class="w-full group flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all duration-150 select-none text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Message</span>
                    </div>
                </button>
            @else
                <x-cressco.nav-item label="Message" icon="message" :active="$activeItem === 'Message'" />
            @endif

            <!-- Schedule -->
            @if ($interactive)
                <button type="button"
                        @click="active = 'Schedule'"
                        :class="active === 'Schedule' ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-medium'"
                        class="w-full group flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all duration-150 select-none text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Schedule</span>
                    </div>
                </button>
            @else
                <x-cressco.nav-item label="Schedule" icon="schedule" :active="$activeItem === 'Schedule'" />
            @endif

            <!-- Document -->
            @if ($interactive)
                <button type="button"
                        @click="active = 'Document'"
                        :class="active === 'Document' ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-medium'"
                        class="w-full group flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all duration-150 select-none text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Document</span>
                    </div>
                </button>
            @else
                <x-cressco.nav-item label="Document" icon="document" :active="$activeItem === 'Document'" />
            @endif

            <!-- Teams -->
            @if ($interactive)
                <button type="button"
                        @click="active = 'Teams'"
                        :class="active === 'Teams' ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-medium'"
                        class="w-full group flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all duration-150 select-none text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Teams</span>
                    </div>
                </button>
            @else
                <x-cressco.nav-item label="Teams" icon="teams" :active="$activeItem === 'Teams'" />
            @endif
        </div>

        <!-- Group 2: SUPPORT -->
        <div class="space-y-1 pt-2">
            <div class="px-3 py-1.5 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                Support
            </div>

            <!-- Help -->
            <x-cressco.nav-item label="Help" icon="help" :active="$activeItem === 'Help'" />

            <!-- Setting -->
            <x-cressco.nav-item label="Setting" icon="setting" :active="$activeItem === 'Setting'" />
        </div>

    </div>

    <!-- Bottom Section (Storage Info Card & Optional Tenant Switcher) -->
    <div class="p-4 space-y-3">
        @if ($showStorageCard)
            <!-- Storage Card Matching Figma -->
            <div class="bg-gray-50/80 border border-gray-200/80 rounded-2xl p-4 space-y-3">
                <div>
                    <h4 class="text-xs font-bold text-gray-900 leading-snug">Storage Almost Full!</h4>
                    <p class="text-[11px] text-gray-500 leading-tight mt-1">
                        You've used <strong class="text-gray-800">75 GB</strong> of your <strong class="text-gray-800">100 GB</strong> limit. Upgrade to Pro for more room to store your projects.
                    </p>
                </div>

                <!-- Multi-bar Rainbow / Gradient Progress Meter -->
                <div class="flex items-center gap-1">
                    <!-- Segment 1 to 5: Fuchsia / Purple -->
                    <span class="h-4.5 w-1.5 rounded-full bg-[#D946EF]"></span>
                    <span class="h-4.5 w-1.5 rounded-full bg-[#C026D3]"></span>
                    <span class="h-4.5 w-1.5 rounded-full bg-[#E11D48]"></span>
                    <span class="h-4.5 w-1.5 rounded-full bg-[#F43F5E]"></span>
                    <!-- Segment 6 to 10: Orange / Amber -->
                    <span class="h-4.5 w-1.5 rounded-full bg-[#FB923C]"></span>
                    <span class="h-4.5 w-1.5 rounded-full bg-[#F97316]"></span>
                    <span class="h-4.5 w-1.5 rounded-full bg-[#FBBF24]"></span>
                    <span class="h-4.5 w-1.5 rounded-full bg-[#F59E0B]"></span>
                    <span class="h-4.5 w-1.5 rounded-full bg-[#EAB308]"></span>
                    <!-- Segment 11 to 14: Inactive Gray bars -->
                    <span class="h-4.5 w-1.5 rounded-full bg-gray-200"></span>
                    <span class="h-4.5 w-1.5 rounded-full bg-gray-200"></span>
                    <span class="h-4.5 w-1.5 rounded-full bg-gray-200"></span>
                    <span class="h-4.5 w-1.5 rounded-full bg-gray-200"></span>
                </div>

                <!-- Dark Upgrade Pro Button using x-cressco.button -->
                <x-cressco.button variant="dark" size="sm" trailingIcon="arrow-right" class="w-full text-xs font-semibold py-2">
                    Upgrade Pro
                </x-cressco.button>
            </div>
        @endif

        @if ($showBottomTenant)
            <!-- Bottom Tenant Profile Switcher (Figma Column 2) -->
            <div class="flex items-center justify-between p-2 rounded-xl border border-gray-200 bg-white">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-7 h-7 rounded-lg bg-terracotta-500 flex items-center justify-center text-white shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-gray-900 truncate leading-tight">{{ $tenantName }}</div>
                        <div class="text-[10px] text-gray-500 truncate leading-tight">{{ $tenantEmail }}</div>
                    </div>
                </div>
                <div class="text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                    </svg>
                </div>
            </div>
        @endif
    </div>

</aside>
