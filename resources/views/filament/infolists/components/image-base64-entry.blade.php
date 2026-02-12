<div>
    @if($getState())
        <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-800">
            <img
                src="{{ $getState() }}"
                alt="{{ $getLabel() }}"
                class="w-full h-auto"
                style="max-height: 320px; object-fit: contain; display: block; margin: auto;"
            />
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-8 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50">
            <x-heroicon-o-photo class="w-10 h-10 text-gray-400 dark:text-gray-500 mb-2" />
            <p class="text-sm text-gray-400 dark:text-gray-500">Tidak ada gambar</p>
        </div>
    @endif
</div>
