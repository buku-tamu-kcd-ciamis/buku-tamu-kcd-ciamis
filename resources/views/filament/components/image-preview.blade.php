<div class="flex flex-col items-center justify-center w-full gap-4 p-4">
    <div class="relative group w-full overflow-hidden rounded-xl border-4 border-white bg-gray-100 shadow-2xl">
        <div class="flex items-center justify-center w-full" style="min-height: 400px;">
            <img
                src="{{ $url }}"
                alt="Preview {{ $name ?? 'Foto' }}"
                class="h-auto max-h-[60vh] w-auto object-contain transition-all duration-500"
            />
        </div>
    </div>
    @if (!empty($name))
        <p class="text-center text-sm font-semibold text-white drop-shadow-lg">{{ $name }}</p>
    @endif
</div>
