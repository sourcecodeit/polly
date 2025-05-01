<x-filament::section class="w-full">
    <x-slot name="heading">
        Lessons Calendar
    </x-slot>

    <x-slot name="headerEnd">
        <x-filament::tabs>
            @foreach ($this->getFilters() as $value => $label)
                <x-filament::tabs.item
                    :active="$filter === $value"
                    wire:click="$set('filter', '{{ $value }}')"
                >
                    {{ $label }}
                </x-filament::tabs.item>
            @endforeach
        </x-filament::tabs>
    </x-slot>

    <div class="space-y-4 w-full">
        <div class="grid grid-cols-7 gap-2 text-center font-medium w-full">
            <div>Monday</div>
            <div>Tuesday</div>
            <div>Wednesday</div>
            <div>Thursday</div>
            <div>Friday</div>
            <div>Saturday</div>
            <div>Sunday</div>
        </div>

        <div class="grid grid-cols-7 gap-2 overflow-x-auto w-full">
            @foreach ($dates as $date)
                <div class="border rounded-lg p-2 h-48 overflow-y-auto min-w-[150px] {{ $date->isToday() ? 'border-primary-500 bg-primary-50 dark:bg-primary-950' : '' }}">
                    <div class="text-sm font-medium mb-1">
                        {{ $date->format('j M') }}
                    </div>

                    @if (isset($lessons[$date->format('Y-m-d')]))
                        <div class="space-y-1">
                            @foreach ($lessons[$date->format('Y-m-d')] as $lesson)
                                <a 
                                    href="{{ route('filament.admin.resources.lessons.edit', $lesson) }}"
                                    class="block p-2 rounded-md text-xs 
                                        @if (str_contains($lesson->classRoom->name, '4A'))
                                            bg-blue-50 hover:bg-blue-100 dark:bg-blue-950 dark:hover:bg-blue-900
                                        @elseif (str_contains($lesson->classRoom->name, '5B'))
                                            bg-green-50 hover:bg-green-100 dark:bg-green-950 dark:hover:bg-green-900
                                        @else
                                            bg-primary-50 hover:bg-primary-100 dark:bg-primary-950 dark:hover:bg-primary-900
                                        @endif
                                    "
                                >
                                    <div class="font-medium">{{ $lesson->subject }}</div>
                                    <div>{{ $lesson->classRoom->name }}</div>
                                    <div>{{ \Carbon\Carbon::parse($lesson->start_time)->format('H:i') }} ({{ $lesson->hours }} h)</div>
                                    <div class="mt-1 flex items-center justify-between">
                                        <span>{{ $lesson->teacher_name }}</span>
                                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-primary-100 dark:bg-primary-800">
                                            {{ $lesson->attendances->count() }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="h-full flex items-center justify-center text-gray-400 text-xs">
                            No lessons
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-filament::section>
