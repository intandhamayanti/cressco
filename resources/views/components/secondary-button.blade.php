<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 shadow-xs hover:bg-gray-50 hover:text-gray-900 active:bg-gray-100 focus:outline-hidden focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
