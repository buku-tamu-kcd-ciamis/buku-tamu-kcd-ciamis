<div class="flex flex-col items-center justify-center w-full gap-3 p-4">
    <div class="relative group flex items-center justify-center overflow-hidden rounded-xl border-4 border-white bg-gray-100 shadow-2xl" style="max-width: 90%; aspect-ratio: auto; min-height: 450px;">
        <img
            src="{{ $url }}"
            alt="Preview {{ $name ?? 'Foto' }}"
            style="max-height: 100%; max-width: 100%; object-fit: contain; display: block; margin: auto;"
        />
    </div>
    @if (!empty($name))
        <p class="text-center text-sm font-semibold text-white drop-shadow-lg px-4">{{ $name }}</p>
    @endif
</div>
