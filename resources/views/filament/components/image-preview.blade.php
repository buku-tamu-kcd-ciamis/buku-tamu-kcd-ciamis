<div class="flex w-full min-h-screen items-center justify-center p-4">
    <div class="w-full max-w-5xl space-y-4">
        <div class="relative group overflow-hidden rounded-xl border-4 border-white bg-gray-100 shadow-2xl">
            <div class="flex w-full items-center justify-center" style="min-height: 50vh;">
                <img
                    src="{{ $url }}"
                    alt="Preview {{ $name ?? 'Foto' }}"
                    class="block max-h-[70vh] max-w-full object-contain transition-all duration-500"
                />
            </div>
        </div>
        @if (!empty($name))
            <div class="text-center">
                <p class="text-base font-semibold text-white drop-shadow-md">{{ $name }}</p>
            </div>
        @endif
    </div>
</div>
