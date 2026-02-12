@php
    $properties = $getState();
    $attributes = $properties['attributes'] ?? [];
    $old = $properties['old'] ?? [];
@endphp

@if($attributes || $old)
    <div class="space-y-3">
        @foreach($attributes as $key => $newValue)
            @php
                $oldValue = $old[$key] ?? null;
                $hasChanged = $oldValue !== $newValue;
            @endphp
            
            @if($hasChanged)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        {{ ucfirst(str_replace('_', ' ', $key)) }}
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-gray-500 font-medium">Sebelum</span>
                            <div class="mt-1 px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded text-sm">
                                {{ $oldValue ?: '(kosong)' }}
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 font-medium">Sesudah</span>
                            <div class="mt-1 px-3 py-2 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded text-sm">
                                {{ $newValue ?: '(kosong)' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@else
    <p class="text-sm text-gray-500">Tidak ada perubahan data yang tercatat.</p>
@endif
