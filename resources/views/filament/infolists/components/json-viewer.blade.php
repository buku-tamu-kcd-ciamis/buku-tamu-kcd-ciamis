@php
    $state = $getState();
    $properties = is_string($state) ? json_decode($state, true) : (is_array($state) ? $state : []);
@endphp

<div class="space-y-2">
    @if(empty($properties))
        <div class="text-sm text-gray-500 dark:text-gray-400 italic">
            Tidak ada properties
        </div>
    @else
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 font-mono text-xs overflow-x-auto">
            <pre class="text-gray-700 dark:text-gray-300">{{ json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif
</div>
