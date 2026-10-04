@props([
    'columns' => [
        ['key' => 'name', 'label' => 'Task Name'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'priority', 'label' => 'Priority'],
        ['key' => 'assignee', 'label' => 'Assignee'],
        ['key' => 'dueDate', 'label' => 'Due Date'],
    ],
    'rows' => [
        [
            'name' => 'UI Kit Dashboard Redesign',
            'status' => 'In Progress',
            'statusType' => 'info',
            'priority' => 'High',
            'priorityType' => 'error',
            'avatars' => [
                'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80',
            ],
            'dueDate' => 'Oct 14, 2026',
        ],
        [
            'name' => 'Student Attendance API Integration',
            'status' => 'Done',
            'statusType' => 'success',
            'priority' => 'Medium',
            'priorityType' => 'warning',
            'avatars' => [
                'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80',
            ],
            'dueDate' => 'Oct 10, 2026',
        ],
        [
            'name' => 'Payment Gateway Scaffolding',
            'status' => 'Pending',
            'statusType' => 'gray',
            'priority' => 'Low',
            'priorityType' => 'gray',
            'avatars' => [
                'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
            ],
            'dueDate' => 'Oct 22, 2026',
        ],
    ],
    'selectable' => true,
])

<div class="w-full bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs font-sans">
            <!-- Table Header -->
            <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider font-semibold">
                <tr>
                    @if ($selectable)
                        <th class="py-3 px-4 w-10">
                            <input type="checkbox" class="rounded border-gray-300 text-terracotta-600 focus:ring-terracotta-500 cursor-pointer">
                        </th>
                    @endif
                    @foreach ($columns as $col)
                        <th class="py-3 px-4 font-bold text-gray-600">
                            <div class="flex items-center gap-1.5 cursor-pointer hover:text-gray-900 transition">
                                <span>{{ $col['label'] }}</span>
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </div>
                        </th>
                    @endforeach
                    <th class="py-3 px-4 text-right">Action</th>
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @foreach ($rows as $row)
                    <tr class="hover:bg-gray-50/70 transition">
                        @if ($selectable)
                            <td class="py-3.5 px-4">
                                <input type="checkbox" class="rounded border-gray-300 text-terracotta-600 focus:ring-terracotta-500 cursor-pointer">
                            </td>
                        @endif
                        <td class="py-3.5 px-4 font-bold text-gray-900">
                            {{ $row['name'] }}
                        </td>
                        <td class="py-3.5 px-4">
                            <x-cressco.badge :variant="$row['statusType'] ?? 'terracotta'" dot>
                                {{ $row['status'] }}
                            </x-cressco.badge>
                        </td>
                        <td class="py-3.5 px-4">
                            <x-cressco.badge :variant="$row['priorityType'] ?? 'gray'">
                                {{ $row['priority'] }}
                            </x-cressco.badge>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex -space-x-1.5 overflow-hidden">
                                @foreach ($row['avatars'] ?? [] as $avatar)
                                    <img class="inline-block h-6 w-6 rounded-full ring-2 ring-white object-cover" src="{{ $avatar }}" alt="">
                                @endforeach
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-gray-500">
                            {{ $row['dueDate'] }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
