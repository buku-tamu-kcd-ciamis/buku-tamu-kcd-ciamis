<div class="w-full p-4">
    <div class="grid w-full place-items-center gap-3">
        <div class="relative group w-full max-w-4xl overflow-hidden rounded-xl border-4 border-white bg-gray-50 shadow-2xl">
            <div class="flex min-h-[40vh] w-full items-center justify-center bg-gray-100">
                <img
                    src="{{ $url }}"
                    alt="Preview {{ $name ?? 'Foto' }}"
                    class="block max-h-[75vh] max-w-full object-contain transition-all duration-500"
                />
            </div>
            @if (!empty($name))
                <div class="absolute inset-x-0 bottom-0 p-4 bg-linear-to-t from-black/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                    <p class="text-center text-sm font-medium text-white">{{ $name }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
