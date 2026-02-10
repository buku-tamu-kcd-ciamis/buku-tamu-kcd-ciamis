<div>
    @if($getState())
        <div class="rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600">
            <img 
                src="{{ $getState() }}" 
                alt="{{ $getLabel() }}"
                class="w-full h-auto max-w-md"
                style="max-height: 400px; object-fit: contain;"
            />
        </div>
    @else
        <p class="text-sm text-gray-500">Tidak ada gambar</p>
    @endif
</div>
