@props([
    'breadcrumbs' => [],
    'showSearch' => true,
    'searchPlaceholder' => 'Search anything in workspace...',
    'notificationsCount' => 3,
    'userName' => 'Sarah Connor',
    'userRole' => 'Owner',
    'userAvatar' => null,
    'tenantName' => 'Acme Education Group',
    'class' => '',
])

<header {{ $attributes->merge(['class' => 'h-16 bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 sticky top-0 z-20 ' . $class]) }}>
    
    <!-- Left Area: Mobile Toggle & Breadcrumbs -->
    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
        <!-- Mobile Drawer Toggle -->
        <button
            type="button"
            class="md:hidden p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-hidden"
            aria-label="Toggle Sidebar"
            @click="$dispatch('toggle-sidebar')"
        >
            <x-cressco.icon-helper name="menu" class="w-5 h-5" />
        </button>

        <!-- Breadcrumb -->
        @if (!empty($breadcrumbs))
            <x-cressco.breadcrumb :items="$breadcrumbs" class="hidden sm:flex" />
        @else
            {{ $slot }}
        @endif
    </div>

    <!-- Center/Right Search Bar (Optional) -->
    @if ($showSearch)
        <div class="hidden lg:flex items-center flex-1 max-w-md mx-4">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <x-cressco.icon-helper name="search" class="w-4 h-4" />
                </div>
                <input
                    type="text"
                    placeholder="{{ $searchPlaceholder }}"
                    class="w-full pl-9 pr-4 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-400 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 focus:outline-hidden transition"
                />
            </div>
        </div>
    @endif

    <!-- Right Action Area -->
    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
        
        <!-- Action Button 1: Help & Docs -->
        <x-cressco.button variant="tertiary" size="sm" iconOnly aria-label="Help and Documentation" class="text-gray-500 hover:text-gray-700">
            <x-cressco.icon-helper name="help-circle" class="w-5 h-5" />
        </x-cressco.button>

        <!-- Action Button 2: Notification with Badge -->
        <div class="relative">
            <x-cressco.button variant="tertiary" size="sm" iconOnly aria-label="Notifications" class="text-gray-500 hover:text-gray-700">
                <x-cressco.icon-helper name="bell" class="w-5 h-5" />
            </x-cressco.button>
            @if ($notificationsCount > 0)
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-terracotta-500 rounded-full ring-2 ring-white"></span>
            @endif
        </div>

        <!-- Action Button 3: Settings -->
        <x-cressco.button variant="tertiary" size="sm" iconOnly aria-label="Settings" class="text-gray-500 hover:text-gray-700">
            <x-cressco.icon-helper name="settings" class="w-5 h-5" />
        </x-cressco.button>

        <!-- Vertical Divider -->
        <div class="h-6 w-px bg-gray-200 mx-1"></div>

        <!-- Profile / Account Trigger -->
        <div class="flex items-center gap-2.5 pl-1 cursor-pointer group">
            <div class="relative w-8 h-8 rounded-full bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-xs shrink-0 border border-terracotta-200">
                @if ($userAvatar)
                    <img src="{{ $userAvatar }}" alt="{{ $userName }}" class="w-full h-full rounded-full object-cover">
                @else
                    {{ strtoupper(substr($userName, 0, 2)) }}
                @endif
                <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full bg-success-500 border-2 border-white"></span>
            </div>
            <div class="hidden md:block text-left min-w-0">
                <div class="text-xs font-bold text-gray-900 leading-tight group-hover:text-terracotta-600 transition">{{ $userName }}</div>
                <div class="text-[11px] text-gray-400 leading-tight">{{ $userRole }}</div>
            </div>
            <x-cressco.icon-helper name="chevron-down" class="hidden md:block w-3.5 h-3.5 text-gray-400 group-hover:text-gray-600 transition" />
        </div>

    </div>

</header>
